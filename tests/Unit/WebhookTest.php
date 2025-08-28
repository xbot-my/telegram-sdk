<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Exceptions\ApiException;
use XBot\Telegram\Exceptions\HttpException;
use XBot\Telegram\Exceptions\ValidationException;
use XBot\Telegram\Http\GuzzleHttpClient;

beforeEach(function () {
    // 创建模拟 HTTP 客户端
    $this->mockHttpClient = $this->mock(GuzzleHttpClient::class);
    
    // 创建 Bot 实例
    $this->bot = new TelegramBot('test', $this->mockHttpClient);
    
    // 有效的 Webhook URL
    $this->validWebhookUrl = 'https://example.com/webhook';
    $this->invalidWebhookUrl = 'http://example.com/webhook'; // 非 HTTPS
});

describe('Webhook 功能测试', function () {
    
    describe('设置 Webhook 测试', function () {
        
        it('应该成功设置有效的 HTTPS URL', function () {
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', ['url' => $this->validWebhookUrl])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->setWebhook($this->validWebhookUrl);
            
            expect($result)->toBeTrue();
        });

        it('应该支持传递额外选项', function () {
            $options = [
                'max_connections' => 50,
                'allowed_updates' => ['message', 'callback_query'],
                'secret_token' => 'my-secret-token',
            ];
            
            $expectedParams = array_merge(['url' => $this->validWebhookUrl], $options);
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', $expectedParams)
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->setWebhook($this->validWebhookUrl, $options);
            
            expect($result)->toBeTrue();
        });

        it('应该支持证书上传', function () {
            $options = [
                'certificate' => '/path/to/certificate.pem',
                'max_connections' => 100,
            ];
            
            $expectedParams = array_merge(['url' => $this->validWebhookUrl], $options);
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', $expectedParams)
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->setWebhook($this->validWebhookUrl, $options);
            
            expect($result)->toBeTrue();
        });

        it('应该拒绝非 HTTPS URL', function () {
            expect(function () {
                $this->bot->setWebhook($this->invalidWebhookUrl);
            })->toThrow(ValidationException::class);
        });

        it('应该拒绝空 URL', function () {
            expect(function () {
                $this->bot->setWebhook('');
            })->toThrow(ValidationException::class);
        });

        it('应该拒绝无效 URL 格式', function () {
            expect(function () {
                $this->bot->setWebhook('not-a-url');
            })->toThrow(ValidationException::class);
        });

        it('应该处理 API 错误响应', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Bad Request: invalid webhook URL',
                400
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', ['url' => $this->validWebhookUrl])
                ->once()
                ->andReturn($mockResponse);
            
            expect(function () {
                $this->bot->setWebhook($this->validWebhookUrl);
            })->toThrow(ApiException::class);
        });
    });

    describe('获取 Webhook 信息测试', function () {
        
        it('应该返回完整的 Webhook 信息', function () {
            $webhookInfo = [
                'url' => $this->validWebhookUrl,
                'has_custom_certificate' => false,
                'pending_update_count' => 0,
                'max_connections' => 40,
                'ip_address' => '192.168.1.1',
            ];
            
            $mockResponse = new TelegramResponse($this->createMockResponse($webhookInfo));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getWebhookInfo', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getWebhookInfo();
            
            expect($result)->toBe($webhookInfo);
            expect($result['url'])->toBe($this->validWebhookUrl);
            expect($result['has_custom_certificate'])->toBeFalse();
            expect($result['pending_update_count'])->toBe(0);
            expect($result['max_connections'])->toBe(40);
            expect($result['ip_address'])->toBe('192.168.1.1');
        });

        it('应该处理未设置 Webhook 的情况', function () {
            $webhookInfo = [
                'url' => '',
                'has_custom_certificate' => false,
                'pending_update_count' => 0,
            ];
            
            $mockResponse = new TelegramResponse($this->createMockResponse($webhookInfo));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getWebhookInfo', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getWebhookInfo();
            
            expect($result['url'])->toBe('');
        });

        it('应该包含错误信息（如果有）', function () {
            $webhookInfo = [
                'url' => $this->validWebhookUrl,
                'has_custom_certificate' => false,
                'pending_update_count' => 5,
                'last_error_date' => time() - 3600,
                'last_error_message' => 'Connection timeout',
                'max_connections' => 40,
            ];
            
            $mockResponse = new TelegramResponse($this->createMockResponse($webhookInfo));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getWebhookInfo', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getWebhookInfo();
            
            expect($result['last_error_message'])->toBe('Connection timeout');
            expect($result['pending_update_count'])->toBe(5);
        });

        it('应该包含允许的更新类型', function () {
            $webhookInfo = [
                'url' => $this->validWebhookUrl,
                'has_custom_certificate' => false,
                'pending_update_count' => 0,
                'allowed_updates' => ['message', 'callback_query', 'inline_query'],
            ];
            
            $mockResponse = new TelegramResponse($this->createMockResponse($webhookInfo));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getWebhookInfo', [])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->getWebhookInfo();
            
            expect($result['allowed_updates'])->toBe(['message', 'callback_query', 'inline_query']);
        });
    });

    describe('删除 Webhook 测试', function () {
        
        it('应该成功删除 Webhook', function () {
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('deleteWebhook', ['drop_pending_updates' => false])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->deleteWebhook();
            
            expect($result)->toBeTrue();
        });

        it('应该支持丢弃挂起的更新', function () {
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('deleteWebhook', ['drop_pending_updates' => true])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->deleteWebhook(true);
            
            expect($result)->toBeTrue();
        });

        it('应该处理删除失败的情况', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Bad Request: webhook is not set',
                400
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('deleteWebhook', ['drop_pending_updates' => false])
                ->once()
                ->andReturn($mockResponse);
            
            expect(function () {
                $this->bot->deleteWebhook();
            })->toThrow(ApiException::class);
        });
    });

    describe('Webhook 流程测试', function () {
        
        it('应该支持完整的设置-查看-删除流程', function () {
            // 1. 设置 Webhook
            $setResponse = new TelegramResponse($this->createMockResponse(true));
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', ['url' => $this->validWebhookUrl])
                ->once()
                ->andReturn($setResponse);
            
            $setResult = $this->bot->setWebhook($this->validWebhookUrl);
            expect($setResult)->toBeTrue();
            
            // 2. 获取 Webhook 信息
            $webhookInfo = [
                'url' => $this->validWebhookUrl,
                'has_custom_certificate' => false,
                'pending_update_count' => 0,
            ];
            $infoResponse = new TelegramResponse($this->createMockResponse($webhookInfo));
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getWebhookInfo', [])
                ->once()
                ->andReturn($infoResponse);
            
            $infoResult = $this->bot->getWebhookInfo();
            expect($infoResult['url'])->toBe($this->validWebhookUrl);
            
            // 3. 删除 Webhook
            $deleteResponse = new TelegramResponse($this->createMockResponse(true));
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('deleteWebhook', ['drop_pending_updates' => false])
                ->once()
                ->andReturn($deleteResponse);
            
            $deleteResult = $this->bot->deleteWebhook();
            expect($deleteResult)->toBeTrue();
        });

        it('应该支持更新 Webhook URL', function () {
            $newUrl = 'https://new-domain.com/webhook';
            
            // 设置新的 Webhook URL
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', ['url' => $newUrl])
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->setWebhook($newUrl);
            expect($result)->toBeTrue();
        });
    });

    describe('Webhook 安全验证测试', function () {
        
        it('应该支持设置密钥令牌', function () {
            $secretToken = 'very-secret-token-123';
            $options = ['secret_token' => $secretToken];
            
            $expectedParams = array_merge(['url' => $this->validWebhookUrl], $options);
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', $expectedParams)
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->setWebhook($this->validWebhookUrl, $options);
            expect($result)->toBeTrue();
        });

        it('应该验证密钥令牌长度', function () {
            $longToken = str_repeat('a', 257); // 超过 256 字符
            $options = ['secret_token' => $longToken];
            
            // 这应该在验证阶段被拒绝
            expect(function () {
                $this->bot->setWebhook($this->validWebhookUrl, $options);
            })->toThrow(ValidationException::class);
        });

        it('应该支持 IP 地址白名单', function () {
            $options = [
                'ip_address' => '192.168.1.100',
            ];
            
            $expectedParams = array_merge(['url' => $this->validWebhookUrl], $options);
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', $expectedParams)
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->setWebhook($this->validWebhookUrl, $options);
            expect($result)->toBeTrue();
        });
    });

    describe('Webhook 错误处理测试', function () {
        
        it('应该处理网络连接错误', function () {
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', ['url' => $this->validWebhookUrl])
                ->once()
                ->andThrow(new HttpException('Network timeout', 0, null, [], 'test'));
            
            expect(function () {
                $this->bot->setWebhook($this->validWebhookUrl);
            })->toThrow(HttpException::class);
        });

        it('应该处理无效令牌错误', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Unauthorized',
                401
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', ['url' => $this->validWebhookUrl])
                ->once()
                ->andReturn($mockResponse);
            
            expect(function () {
                $this->bot->setWebhook($this->validWebhookUrl);
            })->toThrow(ApiException::class);
        });

        it('应该处理服务器错误', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Internal Server Error',
                500
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('getWebhookInfo', [])
                ->once()
                ->andReturn($mockResponse);
            
            expect(function () {
                $this->bot->getWebhookInfo();
            })->toThrow(ApiException::class);
        });

        it('应该正确解析 API 错误信息', function () {
            $errorResponse = $this->createMockErrorResponse(
                'Bad Request: invalid webhook URL specified',
                400
            );
            $mockResponse = new TelegramResponse($errorResponse);
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', ['url' => $this->validWebhookUrl])
                ->once()
                ->andReturn($mockResponse);
            
            try {
                $this->bot->setWebhook($this->validWebhookUrl);
                $this->fail('Expected ApiException to be thrown');
            } catch (ApiException $e) {
                expect($e->getErrorCode())->toBe(400);
                expect($e->getDescription())->toBe('Bad Request: invalid webhook URL specified');
            }
        });
    });

    describe('Webhook 配置验证测试', function () {
        
        it('应该验证最大连接数范围', function () {
            $options = ['max_connections' => 150]; // 超过 100
            
            expect(function () {
                $this->bot->setWebhook($this->validWebhookUrl, $options);
            })->toThrow(ValidationException::class);
        });

        it('应该验证允许的更新类型', function () {
            $validUpdates = ['message', 'callback_query', 'inline_query'];
            $options = ['allowed_updates' => $validUpdates];
            
            $expectedParams = array_merge(['url' => $this->validWebhookUrl], $options);
            $mockResponse = new TelegramResponse($this->createMockResponse(true));
            
            $this->mockHttpClient
                ->shouldReceive('post')
                ->with('setWebhook', $expectedParams)
                ->once()
                ->andReturn($mockResponse);
            
            $result = $this->bot->setWebhook($this->validWebhookUrl, $options);
            expect($result)->toBeTrue();
        });

        it('应该拒绝无效的更新类型', function () {
            $invalidUpdates = ['invalid_update_type'];
            $options = ['allowed_updates' => $invalidUpdates];
            
            expect(function () {
                $this->bot->setWebhook($this->validWebhookUrl, $options);
            })->toThrow(ValidationException::class);
        });
    });
});