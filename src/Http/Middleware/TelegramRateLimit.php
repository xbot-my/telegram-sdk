<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Telegram 速率限制中间件
 */
class TelegramRateLimit
{
    /**
     * 处理传入请求
     */
    public function handle(Request $request, Closure $next): Response
    {
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

        // 检查是否启用速率限制
        $rateLimitConfig = $botConfig['rate_limit'] ?? [];
        $enabled = $rateLimitConfig['enabled'] ?? false;
        
        if (!$enabled) {
            return $next($request);
        }

        $maxRequests = $rateLimitConfig['max_requests'] ?? 30;
        $perSeconds = $rateLimitConfig['per_seconds'] ?? 60;

        // 构建速率限制键
        $key = "telegram:rate_limit:{$botName}";
        
        // 执行速率限制检查
        $executed = RateLimiter::attempt(
            $key,
            $maxRequests,
            function () use ($next, $request) {
                return $next($request);
            },
            $perSeconds
        );

        if (!$executed) {
            // 获取重试时间
            $availableIn = RateLimiter::availableIn($key);
            
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $availableIn,
                'max_requests' => $maxRequests,
                'per_seconds' => $perSeconds,
            ], 429);
        }

        return $executed;
    }
}