<?php

declare(strict_types=1);

namespace XBot\Telegram\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use XBot\Telegram\Contracts\HttpClientConfig;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Exceptions\ApiException;
use XBot\Telegram\Exceptions\HttpException;
use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * 基于 Guzzle 的 HTTP 客户端实现
 * 
 * 提供对 Telegram Bot API 的 HTTP 请求功能
 */
class GuzzleHttpClient implements HttpClientInterface
{
    /**
     * Guzzle HTTP 客户端
     */
    protected GuzzleClient $client;

    /**
     * 客户端配置
     */
    protected HttpClientConfig $config;

    /**
     * 最后一次响应
     */
    protected ?TelegramResponse $lastResponse = null;

    /**
     * 最后一次错误
     */
    protected ?\Throwable $lastError = null;

    /**
     * 请求统计
     */
    protected array $stats = [
        'total_requests' => 0,
        'successful_requests' => 0,
        'failed_requests' => 0,
        'retry_count' => 0,
        'total_time' => 0.0,
    ];

    public function __construct(HttpClientConfig $config)
    {
        $this->config = $config;
        $this->config->validate();
        $this->initializeClient();
    }

    /**
     * 初始化 Guzzle 客户端
     */
    protected function initializeClient(): void
    {
        $stack = HandlerStack::create();
        
        // 添加重试中间件
        if ($this->config->retryAttempts > 0) {
            $stack->push($this->createRetryMiddleware());
        }

        // 添加统计中间件
        $stack->push($this->createStatsMiddleware());

        // 添加自定义中间件
        foreach ($this->config->middleware as $middleware) {
            if (is_callable($middleware)) {
                $stack->push($middleware);
            }
        }

        $options = [
            'handler' => $stack,
            'base_uri' => $this->config->getApiUrl(),
            'timeout' => $this->config->timeout,
            'connect_timeout' => $this->config->connectTimeout,
            'read_timeout' => $this->config->readTimeout,
            'verify' => $this->config->verifySSL,
            'allow_redirects' => [
                'max' => $this->config->maxRedirects,
                'strict' => true,
                'referer' => true,
                'protocols' => ['https', 'http']
            ],
            'headers' => array_merge([
                'User-Agent' => $this->config->userAgent,
                'Accept' => 'application/json',
                'Connection' => 'keep-alive',
            ], $this->config->headers),
        ];

        // 配置代理
        if ($this->config->proxy) {
            $options['proxy'] = $this->config->proxy;
        }

        // 配置调试模式
        if ($this->config->debug) {
            $options['debug'] = true;
        }

        $this->client = new GuzzleClient($options);
    }

    /**
     * 发送 GET 请求
     */
    public function get(string $method, array $parameters = []): TelegramResponse
    {
        return $this->sendRequest('GET', $method, [
            RequestOptions::QUERY => $this->prepareParameters($parameters)
        ]);
    }

    /**
     * 发送 POST 请求
     */
    public function post(string $method, array $parameters = []): TelegramResponse
    {
        return $this->sendRequest('POST', $method, [
            RequestOptions::JSON => $this->prepareParameters($parameters)
        ]);
    }

    /**
     * 上传文件
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
                'name' => $name,
                'contents' => (string) $value,
            ];
        }

        // 添加文件
        foreach ($files as $name => $file) {
            $multipart[] = $this->prepareFileUpload($name, $file);
        }

        return $this->sendRequest('POST', $method, [
            RequestOptions::MULTIPART => $multipart
        ]);
    }

    /**
     * 发送 HTTP 请求
     */
    protected function sendRequest(string $httpMethod, string $apiMethod, array $options = []): TelegramResponse
    {
        $this->lastResponse = null;
        $this->lastError = null;

        $startTime = microtime(true);

        try {
            $this->stats['total_requests']++;

            $response = $this->client->request($httpMethod, $apiMethod, $options);
            
            $telegramResponse = $this->createTelegramResponse($response);
            $this->lastResponse = $telegramResponse;

            $this->stats['successful_requests']++;
            $this->stats['total_time'] += microtime(true) - $startTime;

            return $telegramResponse;

        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (ConnectException $e) {
            $this->handleConnectException($e);
        } catch (TooManyRedirectsException $e) {
            $this->handleRedirectException($e);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        } catch (\Throwable $e) {
            $this->handleGenericException($e, $httpMethod, $apiMethod);
        } finally {
            $this->stats['total_time'] += microtime(true) - $startTime;
        }

        throw $this->lastError;
    }

    /**
     * 创建 Telegram 响应对象
     */
    protected function createTelegramResponse(ResponseInterface $response): TelegramResponse
    {
        $statusCode = $response->getStatusCode();
        $headers = $response->getHeaders();
        $body = (string) $response->getBody();

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
                $this->config->botName
            );
        }

        return new TelegramResponse($data, $statusCode, $headers, $this->config->botName);
    }

    /**
     * 处理客户端异常（4xx）
     */
    protected function handleClientException(ClientException $e): void
    {
        $this->stats['failed_requests']++;
        
        $response = $e->getResponse();
        $body = $response ? (string) $response->getBody() : '';
        $data = json_decode($body, true);

        if ($data && isset($data['error_code'], $data['description'])) {
            $this->lastError = new ApiException(
                $data['description'],
                $data['error_code'],
                $data['parameters'] ?? [],
                $e,
                ['response_body' => $body],
                $this->config->botName
            );
        } else {
            $this->lastError = new HttpException(
                $e->getMessage(),
                $response ? $response->getStatusCode() : 0,
                $response ? $response->getReasonPhrase() : '',
                $response ? $response->getHeaders() : [],
                $body,
                $e->getRequest() ? (string) $e->getRequest()->getUri() : null,
                $e->getRequest() ? $e->getRequest()->getMethod() : null,
                $e,
                [],
                $this->config->botName
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
            $response ? (string) $response->getBody() : null,
            $e->getRequest() ? (string) $e->getRequest()->getUri() : null,
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
            $e->getRequest() ? (string) $e->getRequest()->getUri() : null,
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
            $e->getRequest() ? (string) $e->getRequest()->getUri() : null,
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
            $e->getRequest() ? (string) $e->getRequest()->getUri() : null,
            $e->getRequest() ? $e->getRequest()->getMethod() : null,
            $e,
            [],
            $this->config->botName
        );
    }

    /**
     * 处理通用异常
     */
    protected function handleGenericException(\Throwable $e, string $httpMethod, string $apiMethod): void
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
                    'name' => $name,
                    'contents' => fopen($file, 'r'),
                    'filename' => basename($file),
                ];
            }
            
            // URL 或文件 ID
            return [
                'name' => $name,
                'contents' => $file,
            ];
        }

        if (is_resource($file)) {
            return [
                'name' => $name,
                'contents' => $file,
            ];
        }

        if (is_array($file) && isset($file['contents'])) {
            return array_merge(['name' => $name], $file);
        }

        throw new \InvalidArgumentException("Invalid file format for parameter '{$name}'");
    }

    /**
     * 创建重试中间件
     */
    protected function createRetryMiddleware(): callable
    {
        return Middleware::retry(
            function (int $retries, Request $request, ?ResponseInterface $response = null, ?\Throwable $exception = null): bool {
                // 超过重试次数
                if ($retries >= $this->config->retryAttempts) {
                    return false;
                }

                // 服务器错误或连接错误需要重试
                if ($exception instanceof ConnectException || $exception instanceof ServerException) {
                    $this->stats['retry_count']++;
                    return true;
                }

                // 速率限制需要重试
                if ($response && $response->getStatusCode() === 429) {
                    $this->stats['retry_count']++;
                    return true;
                }

                return false;
            },
            function (int $retries): int {
                // 指数退避算法
                $delay = $this->config->retryDelay * (2 ** ($retries - 1));
                return min($delay, 30000); // 最大 30 秒
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
        return $this->config->token;
    }

    /**
     * 获取 API 基础 URL
     */
    public function getBaseUrl(): string
    {
        return $this->config->baseUrl;
    }

    /**
     * 获取客户端配置
     */
    public function getConfig(): array
    {
        return $this->config->toArray();
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
    public function getLastError(): ?\Throwable
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
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'retry_count' => 0,
            'total_time' => 0.0,
        ];
    }

    /**
     * 获取 Guzzle 客户端实例
     */
    public function getGuzzleClient(): GuzzleClient
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
        } catch (\Throwable) {
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
        } catch (\Throwable) {
            return null;
        }
    }
}