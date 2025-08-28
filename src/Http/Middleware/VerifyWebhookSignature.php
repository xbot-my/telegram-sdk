<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 验证 Webhook 签名中间件
 */
class VerifyWebhookSignature
{
    /**
     * 处理传入请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 获取配置的验证设置
        $verifySignature = config('telegram.webhook.verify_signature', true);
        
        if (!$verifySignature) {
            return $next($request);
        }

        // 获取 Bot 名称
        $botName = $request->route('botName');
        
        if (!$botName) {
            return response()->json(['error' => 'Bot name is required'], 400);
        }

        // 获取 Bot 配置
        $botConfig = config("telegram.bots.{$botName}");
        
        if (!$botConfig) {
            return response()->json(['error' => 'Bot configuration not found'], 404);
        }

        $secretToken = $botConfig['webhook_secret'] ?? null;
        
        if (!$secretToken) {
            // 如果没有配置密钥，跳过验证
            return $next($request);
        }

        // 获取签名头
        $signature = $request->header('X-Telegram-Bot-Api-Secret-Token');
        
        if (!$signature) {
            return response()->json(['error' => 'Missing signature header'], 401);
        }

        // 验证签名
        if (!hash_equals($secretToken, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}