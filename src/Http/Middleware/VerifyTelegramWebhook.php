<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use XBot\Telegram\Utils\ValidationHelper;

class VerifyTelegramWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $verify = (bool) ((function_exists('config') ? config('telegram.webhook.verify_signature') : null) ?? true);
        if ($verify === false) {
            return $next($request);
        }

        $expected = (string) ((function_exists('config') ? config('telegram.webhook.secret_token') : null) ?? '');
        if ($expected === '') {
            try { if (function_exists('logger')) logger()->error('telegram.webhook.secret_missing'); } catch (\Throwable) {}
            return response('Webhook secret not configured', 500);
        }

        if (!ValidationHelper::validateSecretToken($expected)) {
            return response('Invalid webhook secret configuration', 500);
        }

        $provided = (string) ($request->headers->get('X-Telegram-Bot-Api-Secret-Token') ?? '');
        if ($provided === '' || !hash_equals($expected, $provided)) {
            try { if (function_exists('logger')) logger()->warning('telegram.webhook.secret_mismatch'); } catch (\Throwable) {}
            return response('Forbidden', 403);
        }

        try { if (function_exists('logger')) logger()->info('telegram.webhook.accepted'); } catch (\Throwable) {}

        return $next($request);
    }
}
