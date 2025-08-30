<?php

declare(strict_types=1);

namespace XBot\Telegram\Utils;

use Psr\Container\ContainerInterface;
use XBot\Telegram\Contracts\UpdateHandler as UpdateHandlerContract;

final class UpdateDispatcher
{
    /** @var array<int, object|string> */
    private array $handlers;
    private ?ContainerInterface $container;

    /**
     * @param array<int, object|string> $handlers List of handler instances or class names
     */
    public function __construct(array $handlers = [], ?ContainerInterface $container = null)
    {
        $this->handlers = $handlers;
        $this->container = $container;
    }

    /**
     * Dispatch an update to all registered handlers.
     */
    public function dispatch(array $update): void
    {
        foreach ($this->resolveHandlers() as $handler) {
            try {
                // Optional supports() method
                if (method_exists($handler, 'supports') && $handler->supports($update) === false) {
                    continue;
                }

                if ($handler instanceof UpdateHandlerContract) {
                    $handler->handle($update);
                } elseif (is_callable($handler)) {
                    $handler($update);
                }
            } catch (\Throwable) {
                // Swallow to prevent webhook failures; apps can log via their handler
                continue;
            }
        }
    }

    /**
     * @return iterable<int, callable|UpdateHandlerContract>
     */
    private function resolveHandlers(): iterable
    {
        foreach ($this->handlers as $h) {
            if (is_object($h)) {
                yield $h;
                continue;
            }
            if (is_string($h)) {
                if ($this->container) {
                    try {
                        // Always try container get; Laravel can auto-resolve even if not explicitly bound.
                        yield $this->container->get($h);
                        continue;
                    } catch (\Throwable) {
                        // fall through
                    }
                }
                if (class_exists($h)) {
                    yield new $h();
                }
            }
        }
    }
}
