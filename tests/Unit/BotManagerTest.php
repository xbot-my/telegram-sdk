<?php

declare( strict_types = 1 );

use XBot\Telegram\BotManager;
use XBot\Telegram\TelegramBot;
use XBot\Telegram\Exceptions\InstanceException;
use XBot\Telegram\Exceptions\ConfigurationException;

beforeEach(function () {
    $this->config = [
        'default' => 'test',
        'bots'    => [
            'test'  => [
                'token'    => '123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                'base_url' => 'https://api.telegram.org/bot',
                'timeout'  => 30,
            ],
            'test2' => [
                'token'    => '987654321:BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB',
                'base_url' => 'https://api.telegram.org/bot',
                'timeout'  => 15,
            ],
        ],
    ];
    
    $this->manager = new BotManager($this->config);
});

describe('BotManager', function () {
    it('can be instantiated with valid configuration', function () {
        expect($this->manager)->toBeInstanceOf(BotManager::class);
    });
    
    it('throws exception with empty configuration', function () {
        expect(fn() => new BotManager([]))->toThrow(ConfigurationException::class);
    });
    
    it('throws exception when default bot is not configured', function () {
        $config = [
            'default' => 'nonexistent',
            'bots'    => [
                'test' => [
                    'token' => '123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
                ],
            ],
        ];
        
        expect(fn() => new BotManager($config))->toThrow(ConfigurationException::class);
    });
    
    it('returns default bot name', function () {
        expect($this->manager->getDefaultBotName())->toBe('test');
    });
    
    it('returns all bot names', function () {
        expect($this->manager->getBotNames())->toBe([ 'test', 'test2' ]);
    });
    
    it('returns bot count', function () {
        expect($this->manager->getBotCount())->toBe(2);
    });
    
    it('checks if bot exists', function () {
        expect($this->manager->hasBot('test'))->toBeTrue();
        expect($this->manager->hasBot('nonexistent'))->toBeFalse();
    });
    
    it('creates and returns bot instance', function () {
        $bot = $this->manager->bot('test');
        
        expect($bot)->toBeInstanceOf(TelegramBot::class);
        expect($bot->getName())->toBe('test');
    });
    
    it('returns same instance on multiple calls', function () {
        $bot1 = $this->manager->bot('test');
        $bot2 = $this->manager->bot('test');
        
        expect($bot1)->toBe($bot2);
    });
    
    it('returns default bot when no name specified', function () {
        $bot = $this->manager->bot();
        
        expect($bot->getName())->toBe('test');
    });
    
    it('throws exception for non-existent bot', function () {
        expect(fn() => $this->manager->bot('nonexistent'))->toThrow(InstanceException::class);
    });
    
    it('can create new bot configuration', function () {
        $config = [
            'token'   => '111111111:CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC',
            'timeout' => 20,
        ];
        
        $bot = $this->manager->createBot('new_bot', $config);
        
        expect($bot)->toBeInstanceOf(TelegramBot::class);
        expect($bot->getName())->toBe('new_bot');
        expect($this->manager->hasBot('new_bot'))->toBeTrue();
    });
    
    it('throws exception when creating existing bot', function () {
        expect(fn() => $this->manager->createBot('test', [
            'token' => '111111111:CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC',
        ]))->toThrow(InstanceException::class);
    });
    
    it('can remove bot instance', function () {
        $this->manager->bot('test2'); // Load the bot first
        expect($this->manager->hasBot('test2'))->toBeTrue();
        
        $this->manager->removeBot('test2');
        expect($this->manager->hasBot('test2'))->toBeFalse();
    });
    
    it('prevents removing default bot', function () {
        expect(fn() => $this->manager->removeBot('test'))->toThrow(InstanceException::class);
    });
    
    it('can set new default bot', function () {
        $this->manager->setDefaultBot('test2');
        expect($this->manager->getDefaultBotName())->toBe('test2');
    });
    
    it('throws exception when setting non-existent default bot', function () {
        expect(fn() => $this->manager->setDefaultBot('nonexistent'))->toThrow(InstanceException::class);
    });
    
    it('can clear all bot instances', function () {
        $this->manager->bot('test');
        $this->manager->bot('test2');
        
        $this->manager->clear();
        
        // Should still have configurations but instances are cleared
        expect($this->manager->getBotCount())->toBe(2);
    });
    
    it('can reload bot instance', function () {
        $bot1 = $this->manager->bot('test');
        $bot2 = $this->manager->reloadBot('test');
        
        expect($bot1)->not->toBe($bot2);
        expect($bot2->getName())->toBe('test');
    });
    
    it('can reload all bot instances', function () {
        $this->manager->bot('test');
        $this->manager->bot('test2');
        
        $this->manager->reloadAllBots();
        
        // Should have same bots but new instances
        expect($this->manager->getBotCount())->toBe(2);
    });
    
    it('returns manager statistics', function () {
        $stats = $this->manager->getStats();
        
        expect($stats)->toHaveKeys([
            'default_bot',
            'total_bots_configured',
            'total_bots_loaded',
            'total_bots_created',
            'created_at',
            'uptime',
        ]);
        
        expect($stats['default_bot'])->toBe('test');
        expect($stats['total_bots_configured'])->toBe(2);
    });
    
    it('can perform health check on all bots', function () {
        // Mock the health check to avoid actual HTTP calls
        $this->mock(\XBot\Telegram\Http\GuzzleHttpClient::class, function ( $mock ) {
            $mock->shouldReceive('post')
                 ->with('getMe', [])
                 ->andReturn(new \XBot\Telegram\Models\Response\TelegramResponse([
                     'ok'     => true,
                     'result' => $this->createMockUser(123456789, 'Test Bot', true),
                 ]));
        });
        
        $results = $this->manager->healthCheck();
        
        expect($results)->toHaveKeys([ 'test', 'test2' ]);
        expect($results['test'])->toHaveKeys([ 'name', 'is_loaded', 'is_healthy' ]);
    });
    
    it('supports magic method access', function () {
        $bot = $this->manager->test;
        
        expect($bot)->toBeInstanceOf(TelegramBot::class);
        expect($bot->getName())->toBe('test');
    });
    
    it('supports magic method existence check', function () {
        expect(isset($this->manager->test))->toBeTrue();
        expect(isset($this->manager->nonexistent))->toBeFalse();
    });
});
