<?php

declare(strict_types=1);

use XBot\Telegram\BotManager;

class BotManagerMemoryUsageStub extends BotManager
{
    public function getUsage(object $obj): int
    {
        return $this->getObjectMemoryUsage($obj);
    }
}

it('handles objects with resources and closures when estimating memory', function () {
    $config = [
        'default' => 'test',
        'bots'    => [
            'test' => [
                'token'    => '1234567890:AA',
                'base_url' => 'https://api.telegram.org/bot',
                'timeout'  => 30,
            ],
        ],
    ];

    $manager = new BotManagerMemoryUsageStub($config);

    $resource = fopen('php://temp', 'r');
    $object   = new class($resource) {
        public $handle;
        public $closure;

        public function __construct($handle)
        {
            $this->handle  = $handle;
            $this->closure = fn () => null;
        }
    };

    $result = $manager->getUsage($object);

    expect($result)->toBeInt()->toBeGreaterThanOrEqual(0);
    fclose($resource);
});
