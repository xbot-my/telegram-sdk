<?php

declare(strict_types=1);

use XBot\Telegram\Models\DTO\Update;
use XBot\Telegram\Models\DTO\Message;
use XBot\Telegram\Models\DTO\User;
use XBot\Telegram\Models\DTO\Chat;
use XBot\Telegram\Models\DTO\CallbackQuery;
use XBot\Telegram\Models\DTO\InlineQuery;
use XBot\Telegram\Models\DTO\ChosenInlineResult;
use XBot\Telegram\Models\DTO\Poll;
use XBot\Telegram\Models\DTO\PollAnswer;
use XBot\Telegram\Exceptions\ValidationException;

beforeEach(function () {
    $this->baseUpdateId = 12345;
    $this->baseUserId = 98765;
    $this->baseChatId = 54321;
    
    // 创建基础数据
    $this->userData = $this->createMockUser($this->baseUserId, 'TestUser', false);
    $this->chatData = $this->createMockChat($this->baseChatId, 'private');
    $this->messageData = $this->createMockMessage(1, $this->baseChatId, 'Hello World', $this->baseUserId);
});

describe('Update DTO 测试', function () {
    
    describe('基础功能测试', function () {
        
        it('应该正确创建 Message Update', function () {
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect($update)->toBeInstanceOf(Update::class);
            expect($update->updateId)->toBe($this->baseUpdateId);
            expect($update->message)->toBeInstanceOf(Message::class);
            expect($update->message->text)->toBe('Hello World');
            expect($update->getType())->toBe('message');
        });

        it('应该正确创建 Edited Message Update', function () {
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'edited_message' => array_merge($this->messageData, ['text' => 'Edited Text']),
            ];
            
            $update = Update::fromArray($updateData);
            
            expect($update->updateId)->toBe($this->baseUpdateId);
            expect($update->editedMessage)->toBeInstanceOf(Message::class);
            expect($update->editedMessage->text)->toBe('Edited Text');
            expect($update->getType())->toBe('edited_message');
            expect($update->isEditedMessage())->toBeTrue();
        });

        it('应该正确创建 Channel Post Update', function () {
            $channelData = $this->createMockChat(-1001234567890, 'channel');
            $channelData['title'] = 'Test Channel';
            
            $channelPostData = array_merge($this->messageData, [
                'chat' => $channelData,
                'text' => 'Channel announcement',
            ]);
            
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'channel_post' => $channelPostData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect($update->channelPost)->toBeInstanceOf(Message::class);
            expect($update->channelPost->chat->type)->toBe('channel');
            expect($update->getType())->toBe('channel_post');
            expect($update->isChannelPost())->toBeTrue();
        });

        it('应该正确创建 Callback Query Update', function () {
            $callbackData = [
                'id' => 'callback_123',
                'from' => $this->userData,
                'message' => $this->messageData,
                'chat_instance' => 'chat_instance_123',
                'data' => 'button_clicked',
            ];
            
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'callback_query' => $callbackData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect($update->callbackQuery)->toBeInstanceOf(CallbackQuery::class);
            expect($update->callbackQuery->id)->toBe('callback_123');
            expect($update->callbackQuery->data)->toBe('button_clicked');
            expect($update->getType())->toBe('callback_query');
            expect($update->isCallbackQuery())->toBeTrue();
        });

        it('应该正确创建 Inline Query Update', function () {
            $inlineQueryData = [
                'id' => 'inline_123',
                'from' => $this->userData,
                'query' => 'search term',
                'offset' => '0',
            ];
            
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'inline_query' => $inlineQueryData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect($update->inlineQuery)->toBeInstanceOf(InlineQuery::class);
            expect($update->inlineQuery->query)->toBe('search term');
            expect($update->getType())->toBe('inline_query');
            expect($update->isInlineQuery())->toBeTrue();
        });
    });

    describe('DTO 对象验证测试', function () {
        
        it('应该验证必填字段存在', function () {
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect(function () use ($update) {
                $update->validate();
            })->not->toThrow(ValidationException::class);
        });

        it('应该拒绝缺少 update_id 的数据', function () {
            $updateData = [
                'message' => $this->messageData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect(function () use ($update) {
                $update->validate();
            })->toThrow(ValidationException::class);
        });

        it('应该拒绝无效的 update_id', function () {
            $updateData = [
                'update_id' => -1,
                'message' => $this->messageData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect(function () use ($update) {
                $update->validate();
            })->toThrow(ValidationException::class);
        });

        it('应该拒绝没有任何更新类型的数据', function () {
            $updateData = [
                'update_id' => $this->baseUpdateId,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect(function () use ($update) {
                $update->validate();
            })->toThrow(ValidationException::class);
        });

        it('应该验证嵌套对象', function () {
            $invalidMessageData = [
                'message_id' => 1,
                'date' => time(),
                // 缺少必填的 'chat' 字段
            ];
            
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'message' => $invalidMessageData,
            ];
            
            $update = Update::fromArray($updateData);
            
            expect(function () use ($update) {
                $update->validate();
            })->toThrow(ValidationException::class);
        });
    });

    describe('类型识别测试', function () {
        
        it('应该正确识别消息类型', function () {
            $testCases = [
                ['message', 'message'],
                ['edited_message', 'edited_message'],
                ['channel_post', 'channel_post'],
                ['edited_channel_post', 'edited_channel_post'],
                ['callback_query', 'callback_query'],
                ['inline_query', 'inline_query'],
            ];
            
            foreach ($testCases as [$field, $expectedType]) {
                $updateData = ['update_id' => $this->baseUpdateId];
                
                switch ($field) {
                    case 'callback_query':
                        $updateData[$field] = [
                            'id' => 'test',
                            'from' => $this->userData,
                            'chat_instance' => 'test',
                        ];
                        break;
                    case 'inline_query':
                        $updateData[$field] = [
                            'id' => 'test',
                            'from' => $this->userData,
                            'query' => '',
                            'offset' => '0',
                        ];
                        break;
                    default:
                        $updateData[$field] = $this->messageData;
                }
                
                $update = Update::fromArray($updateData);
                expect($update->getType())->toBe($expectedType);
            }
        });

        it('应该正确使用类型检查方法', function () {
            $messageUpdate = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ]);
            
            expect($messageUpdate->isMessage())->toBeTrue();
            expect($messageUpdate->isEditedMessage())->toBeFalse();
            expect($messageUpdate->isCallbackQuery())->toBeFalse();
            expect($messageUpdate->isInlineQuery())->toBeFalse();
        });

        it('应该识别未知类型', function () {
            $update = new Update();
            $update->updateId = $this->baseUpdateId;
            
            expect($update->getType())->toBe('unknown');
        });
    });

    describe('数据提取测试', function () {
        
        it('应该从消息更新中提取消息', function () {
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ]);
            
            $message = $update->getMessage();
            expect($message)->toBeInstanceOf(Message::class);
            expect($message->text)->toBe('Hello World');
        });

        it('应该从编辑消息更新中提取消息', function () {
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'edited_message' => $this->messageData,
            ]);
            
            $message = $update->getMessage();
            expect($message)->toBeInstanceOf(Message::class);
        });

        it('应该从更新中提取聊天信息', function () {
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ]);
            
            $chat = $update->getChat();
            expect($chat)->toBeInstanceOf(Chat::class);
            expect($chat->id)->toBe($this->baseChatId);
        });

        it('应该从回调查询中提取聊天信息', function () {
            $callbackData = [
                'id' => 'callback_123',
                'from' => $this->userData,
                'message' => $this->messageData,
                'chat_instance' => 'chat_instance_123',
            ];
            
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'callback_query' => $callbackData,
            ]);
            
            $chat = $update->getChat();
            expect($chat)->toBeInstanceOf(Chat::class);
            expect($chat->id)->toBe($this->baseChatId);
        });

        it('应该从更新中提取用户信息', function () {
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ]);
            
            $user = $update->getUser();
            expect($user)->toBeInstanceOf(User::class);
            expect($user->id)->toBe($this->baseUserId);
        });

        it('应该从内联查询中提取用户信息', function () {
            $inlineQueryData = [
                'id' => 'inline_123',
                'from' => $this->userData,
                'query' => 'search',
                'offset' => '0',
            ];
            
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'inline_query' => $inlineQueryData,
            ]);
            
            $user = $update->getUser();
            expect($user)->toBeInstanceOf(User::class);
            expect($user->id)->toBe($this->baseUserId);
        });
    });

    describe('特殊类型更新测试', function () {
        
        it('应该处理投票更新', function () {
            $pollData = [
                'id' => 'poll_123',
                'question' => 'What is your favorite color?',
                'options' => [
                    ['text' => 'Red', 'voter_count' => 5],
                    ['text' => 'Blue', 'voter_count' => 3],
                ],
                'total_voter_count' => 8,
                'is_closed' => false,
                'is_anonymous' => true,
                'type' => 'regular',
                'allows_multiple_answers' => false,
            ];
            
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'poll' => $pollData,
            ]);
            
            expect($update->poll)->toBeInstanceOf(Poll::class);
            expect($update->poll->question)->toBe('What is your favorite color?');
            expect($update->getType())->toBe('poll');
            expect($update->isPoll())->toBeTrue();
        });

        it('应该处理投票答案更新', function () {
            $pollAnswerData = [
                'poll_id' => 'poll_123',
                'user' => $this->userData,
                'option_ids' => [0, 1],
            ];
            
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'poll_answer' => $pollAnswerData,
            ]);
            
            expect($update->pollAnswer)->toBeInstanceOf(PollAnswer::class);
            expect($update->pollAnswer->pollId)->toBe('poll_123');
            expect($update->getType())->toBe('poll_answer');
            expect($update->isPollAnswer())->toBeTrue();
        });

        it('应该识别商业更新', function () {
            $businessConnectionData = [
                'id' => 'business_123',
                'user' => $this->userData,
                'user_chat_id' => $this->baseUserId,
                'date' => time(),
                'can_reply' => true,
                'is_enabled' => true,
            ];
            
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'business_connection' => $businessConnectionData,
            ]);
            
            expect($update->getType())->toBe('business_connection');
            expect($update->isBusinessUpdate())->toBeTrue();
        });

        it('应该识别支付更新', function () {
            $shippingQueryData = [
                'id' => 'shipping_123',
                'from' => $this->userData,
                'invoice_payload' => 'invoice_payload_data',
                'shipping_address' => [
                    'country_code' => 'US',
                    'state' => 'CA',
                    'city' => 'San Francisco',
                    'street_line1' => '123 Main St',
                    'street_line2' => 'Apt 4B',
                    'post_code' => '94101',
                ],
            ];
            
            $update = Update::fromArray([
                'update_id' => $this->baseUpdateId,
                'shipping_query' => $shippingQueryData,
            ]);
            
            expect($update->getType())->toBe('shipping_query');
            expect($update->isPaymentUpdate())->toBeTrue();
        });
    });

    describe('CallbackQuery 测试', function () {
        
        it('应该正确处理带数据的回调查询', function () {
            $callbackData = [
                'id' => 'callback_123',
                'from' => $this->userData,
                'message' => $this->messageData,
                'chat_instance' => 'chat_instance_123',
                'data' => 'button_data',
            ];
            
            $callback = CallbackQuery::fromArray($callbackData);
            
            expect($callback->hasData())->toBeTrue();
            expect($callback->data)->toBe('button_data');
            expect($callback->isFromInlineMessage())->toBeFalse();
            expect($callback->isGameCallback())->toBeFalse();
        });

        it('应该正确处理内联消息回调', function () {
            $callbackData = [
                'id' => 'callback_123',
                'from' => $this->userData,
                'inline_message_id' => 'inline_msg_123',
                'chat_instance' => 'chat_instance_123',
            ];
            
            $callback = CallbackQuery::fromArray($callbackData);
            
            expect($callback->isFromInlineMessage())->toBeTrue();
            expect($callback->inlineMessageId)->toBe('inline_msg_123');
            expect($callback->hasData())->toBeFalse();
        });

        it('应该正确处理游戏回调', function () {
            $callbackData = [
                'id' => 'callback_123',
                'from' => $this->userData,
                'message' => $this->messageData,
                'chat_instance' => 'chat_instance_123',
                'game_short_name' => 'tetris',
            ];
            
            $callback = CallbackQuery::fromArray($callbackData);
            
            expect($callback->isGameCallback())->toBeTrue();
            expect($callback->gameShortName)->toBe('tetris');
        });

        it('应该验证 CallbackQuery 必填字段', function () {
            $incompleteData = [
                'id' => 'callback_123',
                'from' => $this->userData,
                // 缺少 chat_instance
            ];
            
            $callback = CallbackQuery::fromArray($incompleteData);
            
            expect(function () use ($callback) {
                $callback->validate();
            })->toThrow(ValidationException::class);
        });
    });

    describe('InlineQuery 测试', function () {
        
        it('应该正确处理内联查询', function () {
            $inlineQueryData = [
                'id' => 'inline_123',
                'from' => $this->userData,
                'query' => 'search term',
                'offset' => '10',
                'chat_type' => 'private',
            ];
            
            $inlineQuery = InlineQuery::fromArray($inlineQueryData);
            
            expect($inlineQuery->hasQuery())->toBeTrue();
            expect($inlineQuery->query)->toBe('search term');
            expect($inlineQuery->chatType)->toBe('private');
            expect($inlineQuery->hasLocation())->toBeFalse();
        });

        it('应该处理带位置的内联查询', function () {
            $inlineQueryData = [
                'id' => 'inline_123',
                'from' => $this->userData,
                'query' => '',
                'offset' => '0',
                'location' => [
                    'longitude' => -122.4194,
                    'latitude' => 37.7749,
                ],
            ];
            
            $inlineQuery = InlineQuery::fromArray($inlineQueryData);
            
            expect($inlineQuery->hasQuery())->toBeFalse();
            expect($inlineQuery->hasLocation())->toBeTrue();
        });

        it('应该验证 InlineQuery 必填字段', function () {
            $incompleteData = [
                'id' => 'inline_123',
                'from' => $this->userData,
                // 缺少 query 和 offset
            ];
            
            $inlineQuery = InlineQuery::fromArray($incompleteData);
            
            expect(function () use ($inlineQuery) {
                $inlineQuery->validate();
            })->toThrow(ValidationException::class);
        });
    });

    describe('序列化测试', function () {
        
        it('应该正确序列化 Update 对象', function () {
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ];
            
            $update = Update::fromArray($updateData);
            $serialized = $update->toArray();
            
            expect($serialized['update_id'])->toBe($this->baseUpdateId);
            expect($serialized['message'])->toBeArray();
            expect($serialized['message']['text'])->toBe('Hello World');
        });

        it('应该正确序列化为 JSON', function () {
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
            ];
            
            $update = Update::fromArray($updateData);
            $json = $update->toJson();
            
            expect($json)->toBeString();
            
            $decoded = json_decode($json, true);
            expect($decoded['update_id'])->toBe($this->baseUpdateId);
            expect($decoded['message']['text'])->toBe('Hello World');
        });
    });

    describe('边界情况测试', function () {
        
        it('应该处理空的回调查询数据', function () {
            $callbackData = [
                'id' => 'callback_123',
                'from' => $this->userData,
                'message' => $this->messageData,
                'chat_instance' => 'chat_instance_123',
                'data' => '',
            ];
            
            $callback = CallbackQuery::fromArray($callbackData);
            
            expect($callback->hasData())->toBeFalse();
        });

        it('应该处理空的内联查询', function () {
            $inlineQueryData = [
                'id' => 'inline_123',
                'from' => $this->userData,
                'query' => '',
                'offset' => '0',
            ];
            
            $inlineQuery = InlineQuery::fromArray($inlineQueryData);
            
            expect($inlineQuery->hasQuery())->toBeFalse();
        });

        it('应该处理多个更新类型（只有第一个生效）', function () {
            $updateData = [
                'update_id' => $this->baseUpdateId,
                'message' => $this->messageData,
                'edited_message' => array_merge($this->messageData, ['text' => 'Edited']),
            ];
            
            $update = Update::fromArray($updateData);
            
            // 应该优先识别 message 类型
            expect($update->getType())->toBe('message');
            expect($update->isMessage())->toBeTrue();
            expect($update->isEditedMessage())->toBeFalse();
        });
    });
});