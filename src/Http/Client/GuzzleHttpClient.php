<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Client;

use Throwable;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use XBot\Telegram\Exceptions\ApiException;
use XBot\Telegram\Exceptions\HttpException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use XBot\Telegram\Http\Response\TelegramResponse;
use XBot\Telegram\Contracts\Http\Client as HttpClient;
use XBot\Telegram\Contracts\Http\Client\Config as ConfigContract;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * 基于 Guzzle 的 HTTP 客户端实现
 *
 * 提供对 Telegram Bot API 的 HTTP 请求功能
 */
class GuzzleHttpClient implements HttpClient
{
    protected \GuzzleHttp\Client $client;

    /**
     * 客户端配置
     */
    protected ConfigContract $config;

    /**
     * 最后一次响应
     */
    protected ?TelegramResponse $lastResponse = null;

    /**
     * 最后一次错误
     */
    protected ?Throwable $lastError = null;

    /**
     * 请求统计
     */
    protected array $stats = [
        'total_requests'      => 0,
        'successful_requests' => 0,
        'failed_requests'     => 0,
        'retry_count'         => 0,
        'total_time'          => 0.0,
    ];

    /**
     * PSR-3 logger
     */
    protected LoggerInterface $logger;

    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
        $this->config->validate();
        $this->logger = $this->discoverLogger();
        $this->initializeClient();
    }

    /**
     * 初始化 Guzzle 客户端
     */
    protected function initializeClient(): void
    {
        $stack = HandlerStack::create();

        // 添加重试中间件
        if ($this->config->getRetryAttempts() > 0) {
            $stack->push($this->createRetryMiddleware());
        }

        // 添加统计中间件
        $stack->push($this->createStatsMiddleware());

        // 添加自定义中间件
        foreach ($this->config->getMiddleware() as $middleware) {
            if (is_callable($middleware)) {
                $stack->push($middleware);
            }
        }

        $options = [
            'handler'         => $stack,
            'base_uri'        => $this->config->getApiUrl(),
            'timeout'         => $this->config->getTimeout(),
            'connect_timeout' => $this->config->getConnectTimeout(),
            'read_timeout'    => $this->config->getReadTimeout(),
            'verify'          => $this->config->isVerifySSL(),
            'allow_redirects' => [
                'max'       => $this->config->getMaxRedirects(),
                'strict'    => true,
                'referer'   => true,
                'protocols' => ['https', 'http'],
            ],
            'headers'         => array_merge([
                'User-Agent' => $this->config->getUserAgent(),
                'Accept'     => 'application/json',
                'Connection' => 'keep-alive',
            ], $this->config->getHeaders()),
        ];

        // 配置代理
        if ($this->config->getProxy()) {
            $options['proxy'] = $this->config->getProxy();
        }

        // 配置调试模式
        if ($this->config->isDebug()) {
            $options['debug'] = true;
        }

        $this->client = new Client($options);
    }

    /**
     * 发送 GET 请求
     * @throws \Throwable
     */
    public function get(string $method, array $parameters = []): TelegramResponse
    {
        return $this->sendRequest('GET', $method, [
            RequestOptions::QUERY => $this->prepareParameters($parameters),
        ]);
    }

    /**
     * 发送 POST 请求
     * @throws \Throwable
     */
    public function post(string $method, array $parameters = []): TelegramResponse
    {
        return $this->sendRequest('POST', $method, [
            RequestOptions::JSON => $this->prepareParameters($parameters),
        ]);
    }

    /**
     * 上传文件
     * @throws \Throwable
     */
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse
    {
        $multipart = [];

        // 添加普通参数
        foreach ($this->prepareParameters($parameters) as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $multipart[] = [
                'name'     => $name,
                'contents' => (string)$value,
            ];
        }

        // 添加文件
        foreach ($files as $name => $file) {
            $multipart[] = $this->prepareFileUpload($name, $file);
        }

        return $this->sendRequest('POST', $method, [
            RequestOptions::MULTIPART => $multipart,
        ]);
    }

    /**
     * 发送 HTTP 请求
     * @throws \Throwable
     */
    protected function sendRequest(string $httpMethod, string $apiMethod, array $options = []): TelegramResponse
    {
        $this->lastResponse = null;
        $this->lastError = null;

        $startTime = microtime(true);

        try {
            $this->stats['total_requests']++;
            // Log request (without sensitive values)
            if ($this->config->isLoggingEnabled() && !$this->config->isSuppressInfoLogs()) {
                try {
                    $this->logger->info('telegram.request', [
                        'bot'     => $this->config->getBotName(),
                        'method'  => $apiMethod,
                        'http'    => $httpMethod,
                        'base'    => $this->redactToken($this->config->getBaseUrl()),
                        'params_keys' => array_keys($options[RequestOptions::JSON] ?? []),
                    ]);
                } catch (\Throwable) {}
            }

            $response = $this->client->request($httpMethod, $apiMethod, $options);

            $telegramResponse = $this->createTelegramResponse($response);
            $this->lastResponse = $telegramResponse;

            $this->stats['successful_requests']++;
            $this->stats['total_time'] += microtime(true) - $startTime;
            if ($this->config->isLoggingEnabled() && !$this->config->isSuppressInfoLogs()) {
                try {
                    $this->logger->info('telegram.response', [
                        'bot'     => $this->config->getBotName(),
                        'method'  => $apiMethod,
                        'status'  => $telegramResponse->getStatusCode(),
                        'elapsed_ms' => (int) round((microtime(true) - $startTime) * 1000),
                    ]);
                } catch (\Throwable) {}
            }
            return $telegramResponse;
        }
        catch (ClientException $e) {
            if ($this->config->isLoggingEnabled()) {
                try { $this->logger->warning('telegram.client_exception', ['bot'=>$this->config->getBotName(),'method'=>$apiMethod,'code'=>$e->getCode(),'msg'=>$e->getMessage()]); } catch (\Throwable) {}
            }
            $this->handleClientException($e);
        }
        catch (ServerException $e) {
            if ($this->config->isLoggingEnabled()) {
                try { $this->logger->error('telegram.server_exception', ['bot'=>$this->config->getBotName(),'method'=>$apiMethod,'code'=>$e->getCode(),'msg'=>$e->getMessage()]); } catch (\Throwable) {}
            }
            $this->handleServerException($e);
        }
        catch (ConnectException $e) {
            if ($this->config->isLoggingEnabled()) {
                try { $this->logger->warning('telegram.connect_exception', ['bot'=>$this->config->getBotName(),'method'=>$apiMethod,'code'=>$e->getCode(),'msg'=>$e->getMessage()]); } catch (\Throwable) {}
            }
            $this->handleConnectException($e);
        }
        catch (TooManyRedirectsException $e) {
            if ($this->config->isLoggingEnabled()) {
                try { $this->logger->warning('telegram.redirect_exception', ['bot'=>$this->config->getBotName(),'method'=>$apiMethod,'code'=>$e->getCode(),'msg'=>$e->getMessage()]); } catch (\Throwable) {}
            }
            $this->handleRedirectException($e);
        }
        catch (RequestException $e) {
            if ($this->config->isLoggingEnabled()) {
                try { $this->logger->warning('telegram.request_exception', ['bot'=>$this->config->getBotName(),'method'=>$apiMethod,'code'=>$e->getCode(),'msg'=>$e->getMessage()]); } catch (\Throwable) {}
            }
            $this->handleRequestException($e);
        }
        catch (Throwable $e) {
            if ($this->config->isLoggingEnabled()) {
                try { $this->logger->error('telegram.unexpected_exception', ['bot'=>$this->config->getBotName(),'method'=>$apiMethod,'code'=>$e->getCode(),'msg'=>$e->getMessage()]); } catch (\Throwable) {}
            }
            $this->handleGenericException($e, $httpMethod, $apiMethod);
        }
        finally {
            $this->stats['total_time'] += microtime(true) - $startTime;
        }

        throw $this->lastError;
    }

    /**
     * 创建 Telegram 响应对象
     * @throws \XBot\Telegram\Exceptions\HttpException
     */
    protected function createTelegramResponse(ResponseInterface $response): TelegramResponse
    {
        $statusCode = $response->getStatusCode();
        $headers = $response->getHeaders();
        $body = (string)$response->getBody();

        $data = json_decode($body, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(
                'Invalid JSON response: ' . json_last_error_msg(),
                $statusCode,
                $response->getReasonPhrase(),
                $headers,
                $body,
                null,
                null,
                null,
                ['json_error' => json_last_error_msg()],
                $this->config->getBotName()
            );
        }

        return new TelegramResponse($data, $statusCode, $headers, $this->config->getBotName());
    }

    /**
     * 处理客户端异常（4xx）
     */
    protected function handleClientException(ClientException $e): void
    {
        $this->stats['failed_requests']++;

        $response = $e->getResponse();
        $body = $response ? (string)$response->getBody() : '';
        $data = json_decode($body, true);

        if ($data && isset($data['error_code'], $data['description'])) {
            $this->lastError = new ApiException(
                $data['description'],
                $data['error_code'],
                $data['parameters'] ?? [],
                $e,
                ['response_body' => $body],
                $this->config->getBotName()
            );
        } else {
            $this->lastError = new HttpException(
                $e->getMessage(),
                $response ? $response->getStatusCode() : 0,
                $response ? $response->getReasonPhrase() : '',
                $response ? $response->getHeaders() : [],
                $body,
                $e->getRequest() ? (string)$e->getRequest()->getUri() : null,
                $e->getRequest() ? $e->getRequest()->getMethod() : null,
                $e,
                [],
                $this->config->getBotName()
            );
        }
    }

    /**
     * 处理服务器异常（5xx）
     */
    protected function handleServerException(ServerException $e): void
    {
        $this->stats['failed_requests']++;

        $response = $e->getResponse();
        $this->lastError = new HttpException(
            'Server error: ' . $e->getMessage(),
            $response ? $response->getStatusCode() : 500,
            $response ? $response->getReasonPhrase() : 'Internal Server Error',
            $response ? $response->getHeaders() : [],
            $response ? (string)$response->getBody() : null,
            $e->getRequest() ? (string)$e->getRequest()->getUri() : null,
            $e->getRequest() ? $e->getRequest()->getMethod() : null,
            $e,
            [],
            $this->config->botName
        );
    }

    /**
     * 处理连接异常
     */
    protected function handleConnectException(ConnectException $e): void
    {
        $this->stats['failed_requests']++;

        $this->lastError = HttpException::connectionError(
            'Connection failed: ' . $e->getMessage(),
            $e->getRequest() ? (string)$e->getRequest()->getUri() : null,
            $e->getRequest() ? $e->getRequest()->getMethod() : null,
            $this->config->botName
        );
    }

    /**
     * 处理重定向异常
     */
    protected function handleRedirectException(TooManyRedirectsException $e): void
    {
        $this->stats['failed_requests']++;

        $this->lastError = new HttpException(
            'Too many redirects: ' . $e->getMessage(),
            0,
            'Too Many Redirects',
            [],
            null,
            $e->getRequest() ? (string)$e->getRequest()->getUri() : null,
            $e->getRequest() ? $e->getRequest()->getMethod() : null,
            $e,
            [],
            $this->config->botName
        );
    }

    /**
     * 处理请求异常
     */
    protected function handleRequestException(RequestException $e): void
    {
        $this->stats['failed_requests']++;

        $this->lastError = new HttpException(
            'Request failed: ' . $e->getMessage(),
            0,
            'Request Failed',
            [],
            null,
            $e->getRequest() ? (string)$e->getRequest()->getUri() : null,
            $e->getRequest() ? $e->getRequest()->getMethod() : null,
            $e,
            [],
            $this->config->botName
        );
    }

    /**
     * 处理通用异常
     */
    protected function handleGenericException(Throwable $e, string $httpMethod, string $apiMethod): void
    {
        $this->stats['failed_requests']++;

        $this->lastError = new HttpException(
            'Unexpected error: ' . $e->getMessage(),
            0,
            'Unexpected Error',
            [],
            null,
            $this->config->getApiUrl() . $apiMethod,
            $httpMethod,
            $e,
            [],
            $this->config->botName
        );
    }

    /**
     * 准备请求参数
     */
    protected function prepareParameters(array $parameters): array
    {
        $prepared = [];

        foreach ($parameters as $key => $value) {
            if ($value === null) {
                continue;
            }

            // 将数组和对象转换为 JSON
            if (is_array($value) || is_object($value)) {
                $prepared[$key] = json_encode($value);
            } else {
                $prepared[$key] = $value;
            }
        }

        return $prepared;
    }

    /**
     * 准备文件上传
     */
    protected function prepareFileUpload(string $name, mixed $file): array
    {
        if (is_string($file)) {
            // 文件路径
            if (file_exists($file)) {
                return [
                    'name'     => $name,
                    'contents' => fopen($file, 'r'),
                    'filename' => basename($file),
                ];
            }

            // URL 或文件 ID
            return [
                'name'     => $name,
                'contents' => $file,
            ];
        }

        if (is_resource($file)) {
            return [
                'name'     => $name,
                'contents' => $file,
            ];
        }

        if (is_array($file) && isset($file['contents'])) {
            return array_merge(['name' => $name], $file);
        }

        throw new InvalidArgumentException("Invalid file format for parameter '$name'");
    }

    /**
     * 创建重试中间件
     */
    protected function createRetryMiddleware(): callable
    {
        return Middleware::retry(
            function (int $retries, Request $request, ?ResponseInterface $response = null, ?Throwable $exception = null): bool {
                // 超过重试次数
                if ($retries >= $this->config->getRetryAttempts()) {
                    return false;
                }

                // 服务器错误或连接错误需要重试
                if ($exception instanceof ConnectException || $exception instanceof ServerException) {
                    $this->stats['retry_count']++;
                    try { $this->logger->warning('telegram.retry', ['attempt'=>$retries + 1,'reason'=>$exception instanceof ConnectException ? 'connect' : 'server','url'=>(string)$request->getUri()]); } catch (\Throwable) {}

                    return true;
                }

                // 速率限制需要重试
                if ($response && $response->getStatusCode() === 429) {
                    $this->stats['retry_count']++;
                    try { $this->logger->warning('telegram.retry', ['attempt'=>$retries + 1,'reason'=>'rate_limit','url'=>(string)$request->getUri()]); } catch (\Throwable) {}

                    return true;
                }

                return false;
            },
            function (int $retries): int {
                // 指数退避算法
                $delay = $this->config->getRetryDelay() * (2 ** ($retries - 1));
                $delay = min($delay, 30000); // 最大 30 秒
                if ($this->config->isLoggingEnabled()) {
                    try { $this->logger->warning('telegram.retry.delay', ['attempt' => $retries + 1, 'delay_ms' => $delay]); } catch (\Throwable) {}
                }
                return $delay;
            }
        );
    }

    /**
     * 创建统计中间件
     */
    protected function createStatsMiddleware(): callable
    {
        return function (callable $handler): callable {
            return function (Request $request, array $options) use ($handler) {
                return $handler($request, $options);
            };
        };
    }

    /**
     * 获取 Bot Token
     */
    public function getToken(): string
    {
        return $this->config->getToken();
    }

    /**
     * 获取 API 基础 URL
     */
    public function getBaseUrl(): string
    {
        return $this->config->getBaseUrl();
    }

    /**
     * 获取客户端配置
     */
    public function getConfig(): array
    {
        return $this->config->toArray();
    }

    /**
     * Provide a PSR-3 logger instance.
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * 设置请求超时时间
     */
    public function setTimeout(int $timeout): static
    {
        $this->config = $this->config->with(['timeout' => $timeout]);
        $this->initializeClient();

        return $this;
    }

    /**
     * 设置重试次数
     */
    public function setRetryAttempts(int $attempts): static
    {
        $this->config = $this->config->with(['retry_attempts' => $attempts]);
        $this->initializeClient();

        return $this;
    }

    /**
     * 设置重试延迟
     */
    public function setRetryDelay(int $delay): static
    {
        $this->config = $this->config->with(['retry_delay' => $delay]);
        $this->initializeClient();

        return $this;
    }

    /**
     * 获取最后一次请求的响应
     */
    public function getLastResponse(): ?TelegramResponse
    {
        return $this->lastResponse;
    }

    /**
     * 获取最后一次请求的错误
     */
    public function getLastError(): ?Throwable
    {
        return $this->lastError;
    }

    /**
     * 获取请求统计信息
     */
    public function getStats(): array
    {
        return array_merge($this->stats, [
            'success_rate' => $this->stats['total_requests'] > 0
                ? ($this->stats['successful_requests'] / $this->stats['total_requests']) * 100
                : 0,
            'average_time' => $this->stats['total_requests'] > 0
                ? $this->stats['total_time'] / $this->stats['total_requests']
                : 0,
        ]);
    }

    /**
     * 重置统计信息
     */
    public function resetStats(): void
    {
        $this->stats = [
            'total_requests'      => 0,
            'successful_requests' => 0,
            'failed_requests'     => 0,
            'retry_count'         => 0,
            'total_time'          => 0.0,
        ];
    }

    /**
     * 获取 Guzzle 客户端实例
     *
     * @return \GuzzleHttp\Client
     */
    public function getGuzzleClient(): Client
    {
        return $this->client;
    }

    /**
     * 检查连接是否正常
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->get('getMe');

            return $response->isOk();
        }
        catch (Throwable) {
            return false;
        }
    }

    /**
     * 获取 Bot 信息
     */
    public function getBotInfo(): ?array
    {
        try {
            $response = $this->get('getMe');

            return $response->isOk() ? $response->getResult() : null;
        }
        catch (Throwable) {
            return null;
        }
    }

    /**
     * Discover a logger instance (Laravel logger() if available; otherwise NullLogger).
     */
    protected function discoverLogger(): LoggerInterface
    {
        try {
            // Prefer Laravel Log channel if configured
            $channel = method_exists($this->config, 'getLoggingChannel') ? $this->config->getLoggingChannel() : null;
            if ($channel && class_exists('Illuminate\\Support\\Facades\\Log')) {
                $lg = \Illuminate\Support\Facades\Log::channel($channel);
                if ($lg instanceof LoggerInterface) {
                    return $lg;
                }
            }
            if (function_exists('logger')) {
                $lg = logger();
                if ($lg instanceof LoggerInterface) {
                    return $lg;
                }
            }
        } catch (\Throwable) {
            // ignore
        }
        return new NullLogger();
    }

    protected function redactToken(string $url): string
    {
        return preg_replace('/(bot)\d+:[A-Za-z0-9_\-]+/i', '$1***', $url) ?? $url;
    }
}
