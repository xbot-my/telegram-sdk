<?php

declare(strict_types=1);

use XBot\Telegram\Exceptions\TelegramException;
use XBot\Telegram\Exceptions\ApiException;
use XBot\Telegram\Exceptions\HttpException;
use XBot\Telegram\Exceptions\ConfigurationException;
use XBot\Telegram\Exceptions\ValidationException;
use XBot\Telegram\Exceptions\InstanceException;

describe('异常处理体系测试', function () {
    
    describe('TelegramException 基类测试', function () {
        
        it('应该正确创建基础异常', function () {
            $exception = new class('Test message', 100, null, ['key' => 'value'], 'test-bot') extends TelegramException {};
            
            expect($exception->getMessage())->toBe('Test message');
            expect($exception->getCode())->toBe(100);
            expect($exception->getContext())->toBe(['key' => 'value']);
            expect($exception->getBotName())->toBe('test-bot');
        });

        it('应该支持设置和获取上下文信息', function () {
            $exception = new class() extends TelegramException {};
            
            $context = ['request_id' => 'req_123', 'endpoint' => '/getMe'];
            $exception->setContext($context);
            
            expect($exception->getContext())->toBe($context);
        });

        it('应该支持设置和获取 Bot 名称', function () {
            $exception = new class() extends TelegramException {};
            
            $exception->setBotName('main-bot');
            
            expect($exception->getBotName())->toBe('main-bot');
        });

        it('应该正确转换为数组', function () {
            $exception = new class('Test error', 500, null, ['debug' => true], 'test') extends TelegramException {};
            
            $array = $exception->toArray();
            
            expect($array)->toHaveKeys([
                'exception', 'message', 'code', 'file', 'line', 
                'context', 'bot_name', 'trace'
            ]);
            expect($array['message'])->toBe('Test error');
            expect($array['code'])->toBe(500);
            expect($array['context'])->toBe(['debug' => true]);
            expect($array['bot_name'])->toBe('test');
        });

        it('应该正确转换为 JSON', function () {
            $exception = new class('JSON test', 200) extends TelegramException {};
            
            $json = $exception->toJson();
            $decoded = json_decode($json, true);
            
            expect($decoded['message'])->toBe('JSON test');
            expect($decoded['code'])->toBe(200);
        });

        it('应该正确的字符串表示', function () {
            $exception = new class('String test', 0, null, [], 'string-bot') extends TelegramException {};
            
            $string = (string) $exception;
            
            expect($string)->toContain('String test');
            expect($string)->toContain('[Bot: string-bot]');
        });
    });

    describe('ApiException 测试', function () {
        
        it('应该正确创建 API 异常', function () {
            $exception = new ApiException(
                'Bad Request: invalid chat_id',
                400,
                ['chat_id' => 'invalid'],
                null,
                ['request_data' => ['chat_id' => 'invalid']],
                'api-bot'
            );
            
            expect($exception->getErrorCode())->toBe(400);
            expect($exception->getDescription())->toBe('Bad Request: invalid chat_id');
            expect($exception->getParameters())->toBe(['chat_id' => 'invalid']);
            expect($exception->getBotName())->toBe('api-bot');
        });

        it('应该正确处理速率限制异常', function () {
            $exception = new ApiException(
                'Too Many Requests: retry after 30',
                429,
                ['retry_after' => 30]
            );
            
            expect($exception->isRateLimited())->toBeTrue();
            expect($exception->getRetryAfter())->toBe(30);
            expect($exception->isBadRequest())->toBeFalse();
        });

        it('应该正确处理群组迁移异常', function () {
            $exception = new ApiException(
                'Bad Request: group chat was upgraded to a supergroup chat',
                400,
                ['migrate_to_chat_id' => -1001234567890]
            );
            
            expect($exception->isChatMigrated())->toBeTrue();
            expect($exception->getMigrateToChatId())->toBe(-1001234567890);
        });

        it('应该正确识别各种 HTTP 状态码', function () {
            $testCases = [
                [400, 'isBadRequest'],
                [401, 'isUnauthorized'],
                [403, 'isForbidden'],
                [404, 'isNotFound'],
                [409, 'isConflict'],
                [429, 'isRateLimited'],
            ];
            
            foreach ($testCases as [$code, $method]) {
                $exception = new ApiException("Error {$code}", $code);
                expect($exception->$method())->toBeTrue();
                
                // 确保其他方法返回 false
                foreach ($testCases as [$otherCode, $otherMethod]) {
                    if ($otherMethod !== $method) {
                        expect($exception->$otherMethod())->toBeFalse();
                    }
                }
            }
        });

        it('应该正确格式化异常消息', function () {
            $exception = new ApiException(
                'Invalid parameter',
                400,
                ['field' => 'chat_id', 'value' => 'invalid']
            );
            
            $message = $exception->getMessage();
            
            expect($message)->toContain('Telegram API Error [400]');
            expect($message)->toContain('Invalid parameter');
            expect($message)->toContain('Parameters:');
        });

        it('应该支持静态创建方法', function () {
            $rateLimited = ApiException::rateLimited(60, 'rate-bot');
            expect($rateLimited->isRateLimited())->toBeTrue();
            expect($rateLimited->getRetryAfter())->toBe(60);
            expect($rateLimited->getBotName())->toBe('rate-bot');
            
            $chatMigrated = ApiException::chatMigrated(-1001111111111, 'migrate-bot');
            expect($chatMigrated->isChatMigrated())->toBeTrue();
            expect($chatMigrated->getMigrateToChatId())->toBe(-1001111111111);
            expect($chatMigrated->getBotName())->toBe('migrate-bot');
            
            $forbidden = ApiException::forbidden('Bot was blocked by user', 'block-bot');
            expect($forbidden->isForbidden())->toBeTrue();
            expect($forbidden->getDescription())->toBe('Bot was blocked by user');
            expect($forbidden->getBotName())->toBe('block-bot');
            
            $notFound = ApiException::notFound('Chat not found', 'notfound-bot');
            expect($notFound->isNotFound())->toBeTrue();
            expect($notFound->getBotName())->toBe('notfound-bot');
            
            $badRequest = ApiException::badRequest('Invalid parameter', ['param' => 'invalid'], 'bad-bot');
            expect($badRequest->isBadRequest())->toBeTrue();
            expect($badRequest->getParameters())->toBe(['param' => 'invalid']);
            expect($badRequest->getBotName())->toBe('bad-bot');
            
            $unauthorized = ApiException::unauthorized('Invalid bot token', 'unauth-bot');
            expect($unauthorized->isUnauthorized())->toBeTrue();
            expect($unauthorized->getBotName())->toBe('unauth-bot');
            
            $conflict = ApiException::conflict('Webhook already set', 'conflict-bot');
            expect($conflict->isConflict())->toBeTrue();
            expect($conflict->getBotName())->toBe('conflict-bot');
        });

        it('应该正确转换为数组包含额外字段', function () {
            $exception = new ApiException(
                'Test error',
                400,
                ['test' => 'param'],
                null,
                [],
                'array-bot'
            );
            
            $array = $exception->toArray();
            
            expect($array)->toHaveKeys([
                'error_code', 'description', 'parameters', 
                'retry_after', 'migrate_to_chat_id',
                'is_rate_limited', 'is_chat_migrated'
            ]);
            expect($array['error_code'])->toBe(400);
            expect($array['description'])->toBe('Test error');
            expect($array['parameters'])->toBe(['test' => 'param']);
        });
    });

    describe('HttpException 测试', function () {
        
        it('应该正确创建 HTTP 异常', function () {
            $exception = new HttpException(
                'Connection timeout',
                0,
                null,
                [
                    'url' => 'https://api.telegram.org/bot123/getMe',
                    'timeout' => 30,
                    'status_code' => 0
                ],
                'http-bot'
            );
            
            expect($exception->getMessage())->toBe('Connection timeout');
            expect($exception->getBotName())->toBe('http-bot');
        });

        it('应该正确处理超时异常', function () {
            $exception = HttpException::timeout('https://api.telegram.org/bot123/getMe', 30, 'timeout-bot');
            
            expect($exception->isTimeout())->toBeTrue();
            expect($exception->getRequestUrl())->toBe('https://api.telegram.org/bot123/getMe');
            expect($exception->getMessage())->toContain('timeout');
        });

        it('应该正确处理连接异常', function () {
            $exception = HttpException::connectionError('https://api.telegram.org/bot123/getMe', 'conn-bot');
            
            expect($exception->isConnectionError())->toBeTrue();
            expect($exception->getRequestUrl())->toBe('https://api.telegram.org/bot123/getMe');
        });

        it('应该正确处理 SSL 异常', function () {
            $exception = HttpException::sslError('https://api.telegram.org/bot123/getMe', 'SSL certificate verification failed', 'ssl-bot');
            
            expect($exception->isSslError())->toBeTrue();
            expect($exception->getRequestUrl())->toBe('https://api.telegram.org/bot123/getMe');
            expect($exception->getMessage())->toContain('SSL certificate verification failed');
        });

        it('应该正确处理 HTTP 状态码异常', function () {
            $exception = HttpException::httpStatus(500, 'https://api.telegram.org/bot123/getMe', 'Internal Server Error', 'status-bot');
            
            expect($exception->getStatusCode())->toBe(500);
            expect($exception->getRequestUrl())->toBe('https://api.telegram.org/bot123/getMe');
            expect($exception->isServerError())->toBeTrue();
        });
    });

    describe('ConfigurationException 测试', function () {
        
        it('应该正确创建配置异常', function () {
            $exception = new ConfigurationException(
                'Missing bot token configuration',
                0,
                null,
                ['config_key' => 'telegram.bots.main.token'],
                'config-bot'
            );
            
            expect($exception->getMessage())->toBe('Missing bot token configuration');
            expect($exception->getBotName())->toBe('config-bot');
        });

        it('应该支持缺失配置异常', function () {
            $exception = ConfigurationException::missing('telegram.bots.main.token', 'missing-bot');
            
            expect($exception->getConfigKey())->toBe('telegram.bots.main.token');
            expect($exception->getBotName())->toBe('missing-bot');
            expect($exception->getMessage())->toContain('missing');
        });

        it('应该支持无效配置异常', function () {
            $exception = ConfigurationException::invalid('token', 'invalid-token', 'Invalid token format', 'invalid-bot');
            
            expect($exception->getConfigKey())->toBe('token');
            expect($exception->getConfigValue())->toBe('invalid-token');
            expect($exception->getBotName())->toBe('invalid-bot');
        });

        it('应该支持缺失 Bot Token 异常', function () {
            $exception = ConfigurationException::missingBotToken('token-bot');
            
            expect($exception->getBotName())->toBe('token-bot');
            expect($exception->getMessage())->toContain('Bot token is required');
        });

        it('应该支持无效 Bot Token 异常', function () {
            $exception = ConfigurationException::invalidBotToken('123:invalid', 'invalid-token-bot');
            
            expect($exception->getBotName())->toBe('invalid-token-bot');
            expect($exception->getMessage())->toContain('Invalid bot token format');
        });
    });

    describe('ValidationException 测试', function () {
        
        it('应该正确创建验证异常', function () {
            $exception = new ValidationException(
                'Validation failed',
                ['field1' => ['Required field'], 'field2' => ['Invalid format']],
                'validation-bot'
            );
            
            expect($exception->getMessage())->toBe('Validation failed');
            expect($exception->getErrors())->toBe(['field1' => ['Required field'], 'field2' => ['Invalid format']]);
            expect($exception->getBotName())->toBe('validation-bot');
        });

        it('应该支持添加错误', function () {
            $exception = new ValidationException('Initial error');
            
            $exception->addError('field1', 'Error 1');
            $exception->addError('field1', 'Error 2');
            $exception->addError('field2', 'Error 3');
            
            expect($exception->getErrors())->toBe([
                'field1' => ['Error 1', 'Error 2'],
                'field2' => ['Error 3']
            ]);
        });

        it('应该获取第一个错误', function () {
            $exception = new ValidationException('Test', [
                'field1' => ['First error', 'Second error'],
                'field2' => ['Third error']
            ]);
            
            expect($exception->getFirstError())->toBe('First error');
        });

        it('应该获取字段的第一个错误', function () {
            $exception = new ValidationException('Test', [
                'field1' => ['Field1 Error1', 'Field1 Error2'],
                'field2' => ['Field2 Error1']
            ]);
            
            expect($exception->getFirstError('field2'))->toBe('Field2 Error1');
            expect($exception->getFirstError('field1'))->toBe('Field1 Error1');
            expect($exception->getFirstError('nonexistent'))->toBeNull();
        });

        it('应该检查字段是否有错误', function () {
            $exception = new ValidationException('Test', [
                'field1' => ['Error'],
                'field2' => []
            ]);
            
            expect($exception->hasError('field1'))->toBeTrue();
            expect($exception->hasError('field2'))->toBeFalse();
            expect($exception->hasError('field3'))->toBeFalse();
        });

        it('应该支持静态创建方法', function () {
            $required = ValidationException::required('username', 'required-bot');
            expect($required->getBotName())->toBe('required-bot');
            expect($required->getFirstError('username'))->toContain('required');
            
            $invalidType = ValidationException::invalidType('age', 'integer', 'string', 'type-bot');
            expect($invalidType->getBotName())->toBe('type-bot');
            expect($invalidType->getFirstError('age'))->toContain('integer');
            
            $invalidFormat = ValidationException::invalidFormat('email', 'test@invalid', 'format-bot');
            expect($invalidFormat->getBotName())->toBe('format-bot');
            expect($invalidFormat->getFirstError('email'))->toContain('invalid format');
            
            $outOfRange = ValidationException::outOfRange('count', 150, 1, 100, 'range-bot');
            expect($outOfRange->getBotName())->toBe('range-bot');
            expect($outOfRange->getFirstError('count'))->toContain('range');
            
            $tooLong = ValidationException::tooLong('message', 'very long text', 10, 'long-bot');
            expect($tooLong->getBotName())->toBe('long-bot');
            expect($tooLong->getFirstError('message'))->toContain('too long');
        });

        it('应该正确转换为数组', function () {
            $exception = new ValidationException(
                'Validation failed',
                ['field' => ['Error message']],
                'array-bot'
            );
            
            $array = $exception->toArray();
            
            expect($array)->toHaveKey('errors');
            expect($array['errors'])->toBe(['field' => ['Error message']]);
        });
    });

    describe('InstanceException 测试', function () {
        
        it('应该正确创建实例异常', function () {
            $exception = new InstanceException(
                'Bot instance not found',
                0,
                null,
                ['instance_name' => 'missing-bot', 'instance_type' => 'TelegramBot'],
                'instance-bot'
            );
            
            expect($exception->getMessage())->toBe('Bot instance not found');
            expect($exception->getBotName())->toBe('instance-bot');
        });

        it('应该支持实例不存在异常', function () {
            $exception = InstanceException::notFound('missing-bot', 'notfound-bot');
            
            expect($exception->getInstanceName())->toBe('missing-bot');
            expect($exception->getBotName())->toBe('notfound-bot');
            expect($exception->getMessage())->toContain('not found');
        });

        it('应该支持实例已存在异常', function () {
            $exception = InstanceException::alreadyExists('existing-bot', 'exists-bot');
            
            expect($exception->getInstanceName())->toBe('existing-bot');
            expect($exception->getBotName())->toBe('exists-bot');
            expect($exception->getMessage())->toContain('already exists');
        });

        it('应该支持创建失败异常', function () {
            $exception = InstanceException::creationFailed('failed-bot', 'Invalid configuration', 'creation-bot');
            
            expect($exception->getInstanceName())->toBe('failed-bot');
            expect($exception->getBotName())->toBe('creation-bot');
            expect($exception->getMessage())->toContain('creation failed');
            expect($exception->getMessage())->toContain('Invalid configuration');
        });

        it('应该支持无法移除默认实例异常', function () {
            $exception = InstanceException::cannotRemoveDefault('main', 'default-bot');
            
            expect($exception->getInstanceName())->toBe('main');
            expect($exception->getBotName())->toBe('default-bot');
            expect($exception->getMessage())->toContain('Cannot remove default');
        });
    });

    describe('异常继承关系测试', function () {
        
        it('所有异常都应该继承自 TelegramException', function () {
            $exceptions = [
                new ApiException('Test'),
                new HttpException('Test'),
                new ConfigurationException('Test'),
                new ValidationException('Test'),
                new InstanceException('Test'),
            ];
            
            foreach ($exceptions as $exception) {
                expect($exception)->toBeInstanceOf(TelegramException::class);
            }
        });

        it('所有异常都应该支持基类方法', function () {
            $exceptions = [
                new ApiException('Test API', 400, [], null, ['key' => 'value'], 'test-bot'),
                new HttpException('Test HTTP', 0, null, ['key' => 'value'], 'test-bot'),
                new ConfigurationException('Test Config', 0, null, ['key' => 'value'], 'test-bot'),
                new ValidationException('Test Validation', [], 'test-bot'),
                new InstanceException('Test Instance', 0, null, ['key' => 'value'], 'test-bot'),
            ];
            
            foreach ($exceptions as $exception) {
                expect($exception->getBotName())->toBe('test-bot');
                expect($exception->toArray())->toBeArray();
                expect($exception->toJson())->toBeString();
            }
        });
    });

    describe('异常处理最佳实践测试', function () {
        
        it('应该能够通过类型捕获特定异常', function () {
            $caught = false;
            
            try {
                throw new ApiException('Test API error', 400);
            } catch (ApiException $e) {
                $caught = true;
                expect($e->getErrorCode())->toBe(400);
            }
            
            expect($caught)->toBeTrue();
        });

        it('应该能够通过基类捕获所有异常', function () {
            $exceptions = [
                new ApiException('API'),
                new HttpException('HTTP'),
                new ConfigurationException('Config'),
                new ValidationException('Validation'),
                new InstanceException('Instance'),
            ];
            
            foreach ($exceptions as $exception) {
                $caught = false;
                
                try {
                    throw $exception;
                } catch (TelegramException $e) {
                    $caught = true;
                }
                
                expect($caught)->toBeTrue();
            }
        });

        it('应该提供有用的调试信息', function () {
            $exception = new ApiException(
                'Detailed error',
                400,
                ['param' => 'value'],
                null,
                ['request_id' => 'req_123', 'timestamp' => time()],
                'debug-bot'
            );
            
            $array = $exception->toArray();
            
            expect($array)->toHaveKeys([
                'exception', 'message', 'code', 'file', 'line',
                'context', 'bot_name', 'trace', 'error_code',
                'description', 'parameters'
            ]);
            
            $json = $exception->toJson();
            expect(json_decode($json, true))->toBeArray();
        });
    });
});