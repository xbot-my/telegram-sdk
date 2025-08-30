<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Response;

/**
 * Backwards-compatible response model alias that follows Laravel-style naming.
 * Extends the HTTP layer's ServerResponse to keep a clean separation.
 */
class TelegramResponse extends ServerResponse
{
}
