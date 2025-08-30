<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use XBot\Telegram\Events\TelegramUpdateReceived;
use XBot\Telegram\Utils\UpdateDispatcher;

class WebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        // At this layer, middleware already validated Telegram secret header.
        // Decode update payload
        $payload = json_decode((string) $request->getContent(), true);
        $update = is_array($payload) ? $payload : [];

        // Fire Laravel event if available
        if (function_exists('event')) {
            try { event(new TelegramUpdateReceived($update)); } catch (\Throwable) { /* ignore */ }
        }

        // Dispatch to registered handlers
        $dispatcher = null;
        if (function_exists('app')) {
            try { $dispatcher = app(UpdateDispatcher::class); } catch (\Throwable) { $dispatcher = null; }
        }
        if (!$dispatcher instanceof UpdateDispatcher) {
            $dispatcher = new UpdateDispatcher();
        }
        $dispatcher->dispatch($update);

        // Acknowledge to avoid retries
        return response()->json(['ok' => true]);
    }
}
