<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use XBot\Telegram\BotManager;
use XBot\Telegram\Models\DTO\Update;
use XBot\Telegram\Exceptions\TelegramException;

/**
 * Webhook 控制器
 * 
 * 处理来自 Telegram 的 Webhook 请求
 */
class WebhookController extends Controller
{
    /**
     * Bot 管理器
     */
    protected BotManager $botManager;

    public function __construct(BotManager $botManager)
    {
        $this->botManager = $botManager;
    }

    /**
     * 处理 Webhook 请求
     */
    public function handle(Request $request, string $botName): JsonResponse
    {
        try {
            // 验证请求数据
            $data = $this->validateRequest($request);
            
            // 获取 Bot 实例
            if (!$this->botManager->hasBot($botName)) {
                return response()->json(['error' => 'Bot not found'], 404);
            }

            $bot = $this->botManager->bot($botName);

            // 创建 Update 对象
            $update = Update::fromArray($data);
            $update->validate();

            // 触发 Webhook 事件
            $this->fireWebhookEvent($botName, $update, $request);

            // 记录请求日志
            $this->logWebhookRequest($botName, $update, $request);

            return response()->json(['ok' => true]);

        } catch (TelegramException $e) {
            $this->logWebhookError($botName ?? 'unknown', $e, $request);
            
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ], 400);

        } catch (\Throwable $e) {
            $this->logWebhookError($botName ?? 'unknown', $e, $request);
            
            return response()->json([
                'ok' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * 验证请求数据
     */
    protected function validateRequest(Request $request): array
    {
        $data = $request->json()->all();
        
        if (empty($data)) {
            throw new \InvalidArgumentException('Request body is empty or invalid JSON');
        }

        if (!isset($data['update_id'])) {
            throw new \InvalidArgumentException('Missing update_id in request');
        }

        return $data;
    }

    /**
     * 触发 Webhook 事件
     */
    protected function fireWebhookEvent(string $botName, Update $update, Request $request): void
    {
        if (!function_exists('event')) {
            return;
        }

        // 触发通用 Webhook 事件
        event('telegram.webhook.received', [$botName, $update, $request]);

        // 根据更新类型触发特定事件
        $updateType = $update->getType();
        event("telegram.webhook.{$updateType}", [$botName, $update, $request]);

        // 根据 Bot 名称触发特定事件
        event("telegram.{$botName}.webhook.received", [$update, $request]);
        event("telegram.{$botName}.webhook.{$updateType}", [$update, $request]);
    }

    /**
     * 记录 Webhook 请求日志
     */
    protected function logWebhookRequest(string $botName, Update $update, Request $request): void
    {
        if (!function_exists('logger')) {
            return;
        }

        $logLevel = config("telegram.bots.{$botName}.logging.level", 'info');
        $logEnabled = config("telegram.bots.{$botName}.logging.enabled", true);

        if (!$logEnabled) {
            return;
        }

        logger()->log($logLevel, 'Telegram webhook received', [
            'bot_name' => $botName,
            'update_id' => $update->updateId,
            'update_type' => $update->getType(),
            'chat_id' => $update->getChat()?->id,
            'user_id' => $update->getUser()?->id,
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * 记录 Webhook 错误日志
     */
    protected function logWebhookError(string $botName, \Throwable $error, Request $request): void
    {
        if (!function_exists('logger')) {
            return;
        }

        logger()->error('Telegram webhook error', [
            'bot_name' => $botName,
            'error' => $error->getMessage(),
            'error_class' => get_class($error),
            'request_data' => $request->json()->all(),
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'trace' => $error->getTraceAsString(),
        ]);
    }
}