<?php

declare(strict_types=1);

use XBot\Telegram\Models\DTO\BaseDTO;
use XBot\Telegram\Models\DTO\User;
use XBot\Telegram\Models\DTO\Chat;
use XBot\Telegram\Models\DTO\Message;
use XBot\Telegram\Models\DTO\Update;
use XBot\Telegram\Exceptions\ValidationException;
use DateTime;

// 创建测试用的 DTO 类
class TestDTO extends BaseDTO
{
    public int $id;
    public string $name;
    public ?string $description = null;
    public bool $isActive = true;
    public array $tags = [];
    public ?DateTime $createdAt = null;
    public ?TestNestedDTO $nested = null;

    public function validate(): void
    {
        if (!isset($this->id) || $this->id <= 0) {
            throw ValidationException::required('id');
        }
        
        if (!isset($this->name) || empty($this->name)) {
            throw ValidationException::required('name');
        }
    }
}

class TestNestedDTO extends BaseDTO
{
    public string $value;
    public int $count = 0;
}

beforeEach(function () {
    $this->testData = [
        'id' => 123,
        'name' => 'Test DTO',
        'description' => 'Test description',
        'is_active' => true,
        'tags' => ['tag1', 'tag2'],
        'created_at' => time(),
        'nested' => [
            'value' => 'nested value',
            'count' => 5,
        ],
    ];
});

describe('DTO 基础功能测试', function () {
    
    describe('创建和填充测试', function () {
        
        it('应该通过 fromArray 正确创建 DTO 实例', function () {
            $dto = TestDTO::fromArray($this->testData);
            
            expect($dto)->toBeInstanceOf(TestDTO::class);
            expect($dto->id)->toBe(123);
            expect($dto->name)->toBe('Test DTO');
            expect($dto->description)->toBe('Test description');
            expect($dto->isActive)->toBeTrue();
            expect($dto->tags)->toBe(['tag1', 'tag2']);
        });

        it('应该正确处理蛇形命名和驼峰命名的转换', function () {
            $data = [
                'id' => 456,
                'name' => 'Snake case test',
                'is_active' => false, // 蛇形命名
                'created_at' => time(), // 蛇形命名
            ];
            
            $dto = TestDTO::fromArray($data);
            
            expect($dto->isActive)->toBeFalse();
            expect($dto->createdAt)->toBeInstanceOf(DateTime::class);
        });

        it('应该正确处理嵌套对象', function () {
            $dto = TestDTO::fromArray($this->testData);
            
            expect($dto->nested)->toBeInstanceOf(TestNestedDTO::class);
            expect($dto->nested->value)->toBe('nested value');
            expect($dto->nested->count)->toBe(5);
        });

        it('应该正确处理可选字段', function () {
            $minimalData = [
                'id' => 789,
                'name' => 'Minimal DTO',
            ];
            
            $dto = TestDTO::fromArray($minimalData);
            
            expect($dto->id)->toBe(789);
            expect($dto->name)->toBe('Minimal DTO');
            expect($dto->description)->toBeNull();
            expect($dto->isActive)->toBeTrue(); // 默认值
            expect($dto->tags)->toBe([]); // 默认值
            expect($dto->nested)->toBeNull();
        });

        it('应该正确处理日期时间转换', function () {
            $timestamp = time();
            $data = [
                'id' => 101,
                'name' => 'Date test',
                'created_at' => $timestamp,
            ];
            
            $dto = TestDTO::fromArray($data);
            
            expect($dto->createdAt)->toBeInstanceOf(DateTime::class);
            expect($dto->createdAt->getTimestamp())->toBe($timestamp);
        });

        it('应该处理日期字符串转换', function () {
            $dateString = '2024-01-15 10:30:00';
            $data = [
                'id' => 102,
                'name' => 'Date string test',
                'created_at' => $dateString,
            ];
            
            $dto = TestDTO::fromArray($data);
            
            expect($dto->createdAt)->toBeInstanceOf(DateTime::class);
            expect($dto->createdAt->format('Y-m-d H:i:s'))->toBe($dateString);
        });
    });

    describe('序列化测试', function () {
        
        it('应该正确转换为数组', function () {
            $dto = TestDTO::fromArray($this->testData);
            $array = $dto->toArray();
            
            expect($array)->toBeArray();
            expect($array['id'])->toBe(123);
            expect($array['name'])->toBe('Test DTO');
            expect($array['description'])->toBe('Test description');
            expect($array['is_active'])->toBeTrue();
            expect($array['tags'])->toBe(['tag1', 'tag2']);
            expect($array['created_at'])->toBeInt(); // 时间戳
            expect($array['nested'])->toBeArray();
            expect($array['nested']['value'])->toBe('nested value');
            expect($array['nested']['count'])->toBe(5);
        });

        it('应该正确转换为 JSON', function () {
            $dto = TestDTO::fromArray($this->testData);
            $json = $dto->toJson();
            
            expect($json)->toBeString();
            
            $decoded = json_decode($json, true);
            expect($decoded['id'])->toBe(123);
            expect($decoded['name'])->toBe('Test DTO');
        });

        it('应该支持 JSON 序列化选项', function () {
            $dto = TestDTO::fromArray($this->testData);
            $json = $dto->toJson(JSON_PRETTY_PRINT);
            
            expect($json)->toContain("\n"); // 应该包含换行符
        });

        it('应该正确处理空值', function () {
            $data = [
                'id' => 103,
                'name' => 'Null test',
                'description' => null,
                'nested' => null,
            ];
            
            $dto = TestDTO::fromArray($data);
            $array = $dto->toArray();
            
            // null 值应该被排除
            expect($array)->not->toHaveKey('description');
            expect($array)->not->toHaveKey('nested');
        });
    });

    describe('验证测试', function () {
        
        it('应该通过有效数据的验证', function () {
            $dto = TestDTO::fromArray($this->testData);
            
            expect(function () use ($dto) {
                $dto->validate();
            })->not->toThrow(ValidationException::class);
            
            expect($dto->isValid())->toBeTrue();
            expect($dto->getValidationErrors())->toBe([]);
        });

        it('应该拒绝无效数据', function () {
            $invalidData = [
                'id' => 0, // 无效 ID
                'name' => '', // 空名称
            ];
            
            $dto = TestDTO::fromArray($invalidData);
            
            expect(function () use ($dto) {
                $dto->validate();
            })->toThrow(ValidationException::class);
            
            expect($dto->isValid())->toBeFalse();
            expect($dto->getValidationErrors())->not->toBe([]);
        });

        it('应该返回验证错误详情', function () {
            $invalidData = [
                'id' => -1,
                'name' => '',
            ];
            
            $dto = TestDTO::fromArray($invalidData);
            $errors = $dto->getValidationErrors();
            
            expect($errors)->toBeArray();
            expect($errors)->not->toBe([]);
        });
    });

    describe('魔术方法测试', function () {
        
        it('应该支持 __toString()', function () {
            $dto = TestDTO::fromArray($this->testData);
            $string = (string) $dto;
            
            expect($string)->toBeString();
            expect($string)->toContain('"id"');
            expect($string)->toContain('123');
        });

        it('应该支持 __isset()', function () {
            $dto = TestDTO::fromArray($this->testData);
            
            expect(isset($dto->id))->toBeTrue();
            expect(isset($dto->name))->toBeTrue();
            expect(isset($dto->nonexistent))->toBeFalse();
        });

        it('应该支持 __get()', function () {
            $dto = TestDTO::fromArray($this->testData);
            
            expect($dto->id)->toBe(123);
            expect($dto->nonexistent)->toBeNull();
        });

        it('应该支持 __set()', function () {
            $dto = TestDTO::fromArray($this->testData);
            
            $dto->name = 'Updated name';
            expect($dto->name)->toBe('Updated name');
        });

        it('应该支持 __unset()', function () {
            $dto = TestDTO::fromArray($this->testData);
            
            unset($dto->description);
            expect($dto->description)->toBeNull();
        });
    });

    describe('克隆测试', function () {
        
        it('应该正确克隆 DTO 对象', function () {
            $dto = TestDTO::fromArray($this->testData);
            $cloned = clone $dto;
            
            expect($cloned)->not->toBe($dto);
            expect($cloned->id)->toBe($dto->id);
            expect($cloned->name)->toBe($dto->name);
            
            // 修改克隆对象不应影响原对象
            $cloned->name = 'Modified name';
            expect($dto->name)->toBe('Test DTO');
            expect($cloned->name)->toBe('Modified name');
        });

        it('应该深度克隆嵌套对象', function () {
            $dto = TestDTO::fromArray($this->testData);
            $cloned = clone $dto;
            
            expect($cloned->nested)->not->toBe($dto->nested);
            expect($cloned->nested->value)->toBe($dto->nested->value);
            
            // 修改嵌套对象
            $cloned->nested->value = 'Modified nested value';
            expect($dto->nested->value)->toBe('nested value');
            expect($cloned->nested->value)->toBe('Modified nested value');
        });
    });
});

describe('真实 DTO 测试', function () {
    
    describe('User DTO 测试', function () {
        
        it('应该正确创建 User 对象', function () {
            $userData = $this->createMockUser(123456, 'TestUser', false);
            $user = User::fromArray($userData);
            
            expect($user)->toBeInstanceOf(User::class);
            expect($user->id)->toBe(123456);
            expect($user->firstName)->toBe('TestUser');
            expect($user->isBot)->toBeFalse();
        });

        it('应该正确序列化 User 对象', function () {
            $userData = $this->createMockUser(789012, 'SerializeUser', true);
            $user = User::fromArray($userData);
            
            $array = $user->toArray();
            
            expect($array['id'])->toBe(789012);
            expect($array['first_name'])->toBe('SerializeUser');
            expect($array['is_bot'])->toBeTrue();
        });

        it('应该验证 User 对象', function () {
            $validUserData = $this->createMockUser(345678, 'ValidUser', false);
            $user = User::fromArray($validUserData);
            
            expect($user->isValid())->toBeTrue();
        });
    });

    describe('Chat DTO 测试', function () {
        
        it('应该正确创建 Chat 对象', function () {
            $chatData = $this->createMockChat(-123456, 'group');
            $chat = Chat::fromArray($chatData);
            
            expect($chat)->toBeInstanceOf(Chat::class);
            expect($chat->id)->toBe(-123456);
            expect($chat->type)->toBe('group');
        });

        it('应该处理不同类型的聊天', function () {
            $chatTypes = ['private', 'group', 'supergroup', 'channel'];
            
            foreach ($chatTypes as $type) {
                $chatData = $this->createMockChat(-100000 - array_search($type, $chatTypes), $type);
                $chat = Chat::fromArray($chatData);
                
                expect($chat->type)->toBe($type);
            }
        });
    });

    describe('Message DTO 测试', function () {
        
        it('应该正确创建 Message 对象', function () {
            $messageData = $this->createMockMessage(1, 123456, 'Hello World', 789012);
            $message = Message::fromArray($messageData);
            
            expect($message)->toBeInstanceOf(Message::class);
            expect($message->messageId)->toBe(1);
            expect($message->text)->toBe('Hello World');
            expect($message->from)->toBeInstanceOf(User::class);
            expect($message->chat)->toBeInstanceOf(Chat::class);
        });

        it('应该正确序列化嵌套对象', function () {
            $messageData = $this->createMockMessage(2, 234567, 'Nested test', 890123);
            $message = Message::fromArray($messageData);
            
            $array = $message->toArray();
            
            expect($array['message_id'])->toBe(2);
            expect($array['text'])->toBe('Nested test');
            expect($array['from'])->toBeArray();
            expect($array['chat'])->toBeArray();
            expect($array['from']['id'])->toBe(890123);
            expect($array['chat']['id'])->toBe(234567);
        });
    });

    describe('Update DTO 测试', function () {
        
        it('应该正确创建 Update 对象', function () {
            $updateData = $this->createMockUpdate(12345);
            $update = Update::fromArray($updateData);
            
            expect($update)->toBeInstanceOf(Update::class);
            expect($update->updateId)->toBe(12345);
            expect($update->message)->toBeInstanceOf(Message::class);
        });

        it('应该正确序列化复杂的嵌套结构', function () {
            $updateData = $this->createMockUpdate(23456);
            $update = Update::fromArray($updateData);
            
            $array = $update->toArray();
            
            expect($array['update_id'])->toBe(23456);
            expect($array['message'])->toBeArray();
            expect($array['message']['from'])->toBeArray();
            expect($array['message']['chat'])->toBeArray();
        });

        it('应该正确克隆复杂对象', function () {
            $updateData = $this->createMockUpdate(34567);
            $update = Update::fromArray($updateData);
            $cloned = clone $update;
            
            expect($cloned)->not->toBe($update);
            expect($cloned->message)->not->toBe($update->message);
            expect($cloned->message->from)->not->toBe($update->message->from);
            expect($cloned->message->chat)->not->toBe($update->message->chat);
            
            // 但数据应该相同
            expect($cloned->updateId)->toBe($update->updateId);
            expect($cloned->message->messageId)->toBe($update->message->messageId);
            expect($cloned->message->from->id)->toBe($update->message->from->id);
        });
    });
});

describe('DTO 边界情况测试', function () {
    
    describe('类型转换测试', function () {
        
        it('应该正确转换数字类型', function () {
            $data = [
                'id' => '123', // 字符串数字
                'name' => 'Type test',
            ];
            
            $dto = TestDTO::fromArray($data);
            
            expect($dto->id)->toBeInt();
            expect($dto->id)->toBe(123);
        });

        it('应该正确转换布尔类型', function () {
            $testCases = [
                ['is_active' => 1, 'expected' => true],
                ['is_active' => 0, 'expected' => false],
                ['is_active' => '1', 'expected' => true],
                ['is_active' => '', 'expected' => false],
            ];
            
            foreach ($testCases as $case) {
                $data = [
                    'id' => 1,
                    'name' => 'Bool test',
                    'is_active' => $case['is_active'],
                ];
                
                $dto = TestDTO::fromArray($data);
                expect($dto->isActive)->toBe($case['expected']);
            }
        });

        it('应该处理空数组', function () {
            $data = [
                'id' => 123,
                'name' => 'Empty array test',
                'tags' => [],
            ];
            
            $dto = TestDTO::fromArray($data);
            
            expect($dto->tags)->toBe([]);
        });

        it('应该处理无效日期', function () {
            $data = [
                'id' => 123,
                'name' => 'Invalid date test',
                'created_at' => 'invalid-date',
            ];
            
            expect(function () use ($data) {
                TestDTO::fromArray($data);
            })->not->toThrow();
        });
    });

    describe('异常情况测试', function () {
        
        it('应该处理空数据', function () {
            $dto = TestDTO::fromArray([]);
            
            expect($dto)->toBeInstanceOf(TestDTO::class);
            expect($dto->isActive)->toBeTrue(); // 默认值
            expect($dto->tags)->toBe([]); // 默认值
        });

        it('应该处理额外字段', function () {
            $data = array_merge($this->testData, [
                'extra_field' => 'should be ignored',
                'another_extra' => 999,
            ]);
            
            $dto = TestDTO::fromArray($data);
            
            expect($dto->id)->toBe(123);
            expect($dto->name)->toBe('Test DTO');
            // 额外字段应该被忽略
            expect(property_exists($dto, 'extra_field'))->toBeFalse();
        });

        it('应该处理类型不匹配', function () {
            $data = [
                'id' => 'not-a-number',
                'name' => 123, // 应该转为字符串
                'is_active' => 'true', // 应该转为布尔值
            ];
            
            expect(function () use ($data) {
                TestDTO::fromArray($data);
            })->not->toThrow();
        });
    });

    describe('性能测试', function () {
        
        it('应该快速处理大量数据', function () {
            $startTime = microtime(true);
            
            for ($i = 0; $i < 1000; $i++) {
                $data = [
                    'id' => $i,
                    'name' => "Item {$i}",
                    'is_active' => $i % 2 === 0,
                    'tags' => ["tag{$i}"],
                ];
                
                $dto = TestDTO::fromArray($data);
                $array = $dto->toArray();
            }
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // 转换为毫秒
            
            // 1000 次操作应该在合理时间内完成
            expect($executionTime)->toBeLessThan(1000); // 小于 1 秒
        });

        it('应该高效处理嵌套对象', function () {
            $complexData = [
                'update_id' => 1,
                'message' => [
                    'message_id' => 1,
                    'date' => time(),
                    'text' => 'Performance test',
                    'from' => $this->createMockUser(1, 'User', false),
                    'chat' => $this->createMockChat(-1, 'private'),
                ],
            ];
            
            $startTime = microtime(true);
            
            for ($i = 0; $i < 100; $i++) {
                $update = Update::fromArray($complexData);
                $array = $update->toArray();
                $json = $update->toJson();
            }
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            expect($executionTime)->toBeLessThan(500); // 小于 500ms
        });
    });
});