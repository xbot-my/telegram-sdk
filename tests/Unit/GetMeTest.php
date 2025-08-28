<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Models\DTO\User;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Exceptions\ApiException;
use XBot\Telegram\Exceptions\HttpException;
use XBot\Telegram\Http\GuzzleHttpClient;

beforeEach(function () {
    // 创建模拟 HTTP 客户端
    $this->mockHttpClient = $this->mock(GuzzleHttpClient::class);
    
    // 创建 Bot 实例
    $this->bot = new TelegramBot('test', $this->mockHttpClient);
});

describe('getMe 功能测试', function () {
    
    describe('基础功能测试', function () {
        
        it('应该返回有效的 User DTO 对象', function () {
            // 准备模拟响应数据
            $userData = $this->createMockUser(123456789, 'TestBot', true);
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            // 配置模拟客户端
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            // 执行测试
            $result = $this->bot->getMe();
            
            // 验证结果
            expect($result)->toBeInstanceOf(User::class);
            expect($result->id)->toBe(123456789);
            expect($result->firstName)->toBe('TestBot');
            expect($result->isBot)->toBeTrue();
        });

        it('应该验证 Bot 基本信息正确性', function () {
            $userData = $this->createMockUser(987654321, 'MyBot', true);
            $userData['username'] = 'my_test_bot';
            $userData['can_join_groups'] = true;
            $userData['can_read_all_group_messages'] = false;
            $userData['supports_inline_queries'] = true;
            
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getMe();
            
            expect($result->id)->toBe(987654321);
            expect($result->isBot)->toBeTrue();
            expect($result->firstName)->toBe('MyBot');
            expect($result->username)->toBe('my_test_bot');
            expect($result->canJoinGroups)->toBeTrue();
            expect($result->canReadAllGroupMessages)->toBeFalse();
            expect($result->supportsInlineQueries)->toBeTrue();
        });

        it('应该正确处理必填字段', function () {
            $userData = [
                'id' => 111222333,
                'is_bot' => true,
                'first_name' => 'RequiredBot',
            ];
            
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getMe();
            
            expect($result->id)->toBe(111222333);
            expect($result->isBot)->toBeTrue();
            expect($result->firstName)->toBe('RequiredBot');
            expect($result->username)->toBeNull();
            expect($result->lastName)->toBeNull();
        });

        it('应该正确处理可选字段', function () {
            $userData = [
                'id' => 555666777,
                'is_bot' => true,
                'first_name' => 'FullBot',
                'last_name' => 'Assistant',
                'username' => 'full_test_bot',
                'language_code' => 'en',
                'can_join_groups' => false,
                'can_read_all_group_messages' => true,
                'supports_inline_queries' => false,
            ];
            
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getMe();
            
            expect($result->lastName)->toBe('Assistant');
            expect($result->languageCode)->toBe('en');
            expect($result->canJoinGroups)->toBeFalse();
            expect($result->canReadAllGroupMessages)->toBeTrue();
            expect($result->supportsInlineQueries)->toBeFalse();
        });
    });

    describe('缓存机制测试', function () {
        
        it('首次调用应该发起 HTTP 请求', function () {
            $userData = $this->createMockUser(111111111, 'CacheBot', true);
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getMe();
            
            expect($result)->toBeInstanceOf(User::class);
            expect($result->id)->toBe(111111111);
        });

        it('重复调用应该使用缓存数据', function () {
            $userData = $this->createMockUser(222222222, 'CacheBot2', true);
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            // 只应该调用一次 HTTP 请求
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            // 第一次调用
            $result1 = $this->bot->getMe();
            
            // 第二次调用（应该使用缓存）
            $result2 = $this->bot->getMe();
            
            // 第三次调用（应该使用缓存）
            $result3 = $this->bot->getMe();
            
            // 验证返回相同对象
            expect($result1)->toBe($result2);
            expect($result2)->toBe($result3);
            expect($result1->id)->toBe(222222222);
        });

        it('缓存数据应该与原始数据一致', function () {
            $userData = $this->createMockUser(333333333, 'ConsistentBot', true);
            $userData['username'] = 'consistent_bot';
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result1 = $this->bot->getMe();
            $result2 = $this->bot->getMe();
            
            expect($result1->id)->toBe($result2->id);
            expect($result1->firstName)->toBe($result2->firstName);
            expect($result1->username)->toBe($result2->username);
            expect($result1->isBot)->toBe($result2->isBot);
        });
    });

    describe('异常处理测试', function () {
        
        it('应该处理无效 Token 错误', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Unauthorized',
                401
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            expect(fn() => $this->bot->getMe())->toThrow(ApiException::class);
        });

        it('应该处理 API 限流错误', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Too Many Requests: retry after 30',
                429,
                ['retry_after' => 30]
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            expect(function () {
                $this->bot->getMe();
            })->toThrow(ApiException::class);
        });

        it('应该处理网络连接异常', function () {
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andThrow(new HttpException('Connection timeout', 0, null, [], 'test'));
            
            expect(fn() => $this->bot->getMe())->toThrow(HttpException::class);
        });

        it('应该处理服务器内部错误', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Internal Server Error',
                500
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            expect(fn() => $this->bot->getMe())->toThrow(ApiException::class);
        });

        it('应该正确解析错误信息', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Bad Request: invalid token format',
                400
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            try {
                $this->bot->getMe();
                $this->fail('Expected ApiException to be thrown');
            } catch (ApiException $e) {
                expect($e->getErrorCode())->toBe(400);
                expect($e->getDescription())->toBe('Bad Request: invalid token format');
            }
        });
    });

    describe('数据验证测试', function () {
        
        it('应该验证返回数据包含必要字段', function () {
            $userData = [
                'id' => 444444444,
                'is_bot' => true,
                'first_name' => 'ValidBot',
            ];
            
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getMe();
            
            expect(isset($result->id))->toBeTrue();
            expect(isset($result->isBot))->toBeTrue();
            expect(isset($result->firstName))->toBeTrue();
        });

        it('应该验证字段类型正确性', function () {
            $userData = $this->createMockUser(555555555, 'TypeBot', true);
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getMe();
            
            expect($result->id)->toBeInt();
            expect($result->isBot)->toBeBool();
            expect($result->firstName)->toBeString();
            if ($result->username !== null) {
                expect($result->username)->toBeString();
            }
        });

        it('应该验证 Bot 标识正确', function () {
            $userData = $this->createMockUser(666666666, 'BotFlagTest', true);
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getMe();
            
            expect($result->isBot)->toBeTrue();
        });
    });

    describe('性能测试', function () {
        
        it('应该在合理时间内完成调用', function () {
            $userData = $this->createMockUser(777777777, 'PerfBot', true);
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            $startTime = microtime(true);
            $result = $this->bot->getMe();
            $endTime = microtime(true);
            
            $executionTime = ($endTime - $startTime) * 1000; // 转换为毫秒
            
            expect($result)->toBeInstanceOf(User::class);
            expect($executionTime)->toBeLessThan(100); // 应该在 100ms 内完成
        });

        it('缓存调用应该极快完成', function () {
            $userData = $this->createMockUser(888888888, 'CachePerfBot', true);
            $mockResponse = new TelegramResponse($this->createMockResponse($userData));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getMe', [])
                ->once()
                ->andReturn($mockResponse);
            
            // 第一次调用（建立缓存）
            $this->bot->getMe();
            
            // 测试缓存调用性能
            $startTime = microtime(true);
            $result = $this->bot->getMe();
            $endTime = microtime(true);
            
            $executionTime = ($endTime - $startTime) * 1000; // 转换为毫秒
            
            expect($result)->toBeInstanceOf(User::class);
            expect($executionTime)->toBeLessThan(1); // 缓存调用应该在 1ms 内完成
        });
    });
});