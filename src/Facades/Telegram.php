<?php

declare(strict_types=1);

namespace XBot\Telegram\Facades;

use Illuminate\Support\Facades\Facade;
use XBot\Telegram\BotManager;

/**
 * Telegram Bot SDK Facade
 * 
 * 提供静态方法访问 Telegram Bot 功能
 * 
 * @method static \XBot\Telegram\TelegramBot bot(string $name = null)
 * @method static \XBot\Telegram\TelegramBot createBot(string $name, array $config)
 * @method static bool hasBot(string $name)
 * @method static void removeBot(string $name)
 * @method static array getAllBots()
 * @method static \XBot\Telegram\TelegramBot getDefaultBot()
 * @method static void setDefaultBot(string $name)
 * @method static string getDefaultBotName()
 * @method static array getBotNames()
 * @method static int getBotCount()
 * @method static void clear()
 * @method static \XBot\Telegram\TelegramBot reloadBot(string $name)
 * @method static void reloadAllBots()
 * @method static array getStats()
 * @method static array healthCheck()
 * 
 * @method static \XBot\Telegram\Models\DTO\User getMe()
 * @method static \XBot\Telegram\Models\DTO\Message sendMessage(int|string $chatId, string $text, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message editMessageText(int|string $chatId, int $messageId, string $text, array $options = [])
 * @method static bool deleteMessage(int|string $chatId, int $messageId)
 * @method static \XBot\Telegram\Models\DTO\Message forwardMessage(int|string $chatId, int|string $fromChatId, int $messageId, array $options = [])
 * @method static int copyMessage(int|string $chatId, int|string $fromChatId, int $messageId, array $options = [])
 * @method static array getUpdates(array $options = [])
 * @method static bool setWebhook(string $url, array $options = [])
 * @method static bool deleteWebhook(bool $dropPendingUpdates = false)
 * @method static array getWebhookInfo()
 * @method static \XBot\Telegram\Models\DTO\Message sendPhoto(int|string $chatId, string $photo, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendVideo(int|string $chatId, string $video, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendAudio(int|string $chatId, string $audio, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendDocument(int|string $chatId, string $document, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendSticker(int|string $chatId, string $sticker, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendAnimation(int|string $chatId, string $animation, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendVoice(int|string $chatId, string $voice, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendLocation(int|string $chatId, float $latitude, float $longitude, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendContact(int|string $chatId, string $phoneNumber, string $firstName, array $options = [])
 * @method static \XBot\Telegram\Models\DTO\Message sendPoll(int|string $chatId, string $question, array $options, array $settings = [])
 * @method static array getChat(int|string $chatId)
 * @method static array getChatMember(int|string $chatId, int $userId)
 * @method static int getChatMemberCount(int|string $chatId)
 * @method static bool banChatMember(int|string $chatId, int $userId, array $options = [])
 * @method static bool unbanChatMember(int|string $chatId, int $userId, array $options = [])
 * @method static bool restrictChatMember(int|string $chatId, int $userId, array $permissions, array $options = [])
 * @method static bool promoteChatMember(int|string $chatId, int $userId, array $options = [])
 * @method static bool answerCallbackQuery(string $callbackQueryId, array $options = [])
 * @method static bool answerInlineQuery(string $inlineQueryId, array $results, array $options = [])
 * @method static \XBot\Telegram\Models\Response\TelegramResponse call(string $method, array $parameters = [])
 * @method static bool healthCheck()
 * @method static array getStats()
 * 
 * @see \XBot\Telegram\BotManager
 */
class Telegram extends Facade
{
    /**
     * 获取组件的注册名称
     */
    protected static function getFacadeAccessor(): string
    {
        return 'telegram';
    }

    /**
     * 获取指定 Bot 实例的快捷方法
     */
    public static function bot(string $name): \XBot\Telegram\TelegramBot
    {
        return static::getFacadeRoot()->bot($name);
    }

    /**
     * 发送消息的快捷方法
     */
    public static function to(int|string $chatId): TelegramMessageBuilder
    {
        return new TelegramMessageBuilder(static::getFacadeRoot()->getDefaultBot(), $chatId);
    }

    /**
     * 发送消息给指定 Bot 的快捷方法
     */
    public static function via(string $botName): TelegramBotBuilder
    {
        return new TelegramBotBuilder(static::getFacadeRoot()->bot($botName));
    }
}

/**
 * Telegram 消息构建器
 * 
 * 提供链式调用的消息发送接口
 */
class TelegramMessageBuilder
{
    protected \XBot\Telegram\TelegramBot $bot;
    protected int|string $chatId;
    protected array $options = [];

    public function __construct(\XBot\Telegram\TelegramBot $bot, int|string $chatId)
    {
        $this->bot = $bot;
        $this->chatId = $chatId;
    }

    /**
     * 设置消息选项
     */
    public function options(array $options): static
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * 设置回复标记
     */
    public function replyMarkup(array $markup): static
    {
        $this->options['reply_markup'] = $markup;
        return $this;
    }

    /**
     * 设置内联键盘
     */
    public function keyboard(array $keyboard): static
    {
        $this->options['reply_markup'] = ['inline_keyboard' => $keyboard];
        return $this;
    }

    /**
     * 设置解析模式
     */
    public function parseMode(string $mode): static
    {
        $this->options['parse_mode'] = $mode;
        return $this;
    }

    /**
     * 启用 HTML 解析模式
     */
    public function html(): static
    {
        return $this->parseMode('HTML');
    }

    /**
     * 启用 Markdown 解析模式
     */
    public function markdown(): static
    {
        return $this->parseMode('Markdown');
    }

    /**
     * 禁用通知
     */
    public function silent(): static
    {
        $this->options['disable_notification'] = true;
        return $this;
    }

    /**
     * 保护内容
     */
    public function protect(): static
    {
        $this->options['protect_content'] = true;
        return $this;
    }

    /**
     * 回复消息
     */
    public function replyTo(int $messageId): static
    {
        $this->options['reply_to_message_id'] = $messageId;
        return $this;
    }

    /**
     * 发送文本消息
     */
    public function message(string $text): \XBot\Telegram\Models\DTO\Message
    {
        return $this->bot->sendMessage($this->chatId, $text, $this->options);
    }

    /**
     * 发送照片
     */
    public function photo(string $photo, string $caption = ''): \XBot\Telegram\Models\DTO\Message
    {
        $options = $this->options;
        if ($caption) {
            $options['caption'] = $caption;
        }
        return $this->bot->sendPhoto($this->chatId, $photo, $options);
    }

    /**
     * 发送视频
     */
    public function video(string $video, string $caption = ''): \XBot\Telegram\Models\DTO\Message
    {
        $options = $this->options;
        if ($caption) {
            $options['caption'] = $caption;
        }
        return $this->bot->sendVideo($this->chatId, $video, $options);
    }

    /**
     * 发送文档
     */
    public function document(string $document, string $caption = ''): \XBot\Telegram\Models\DTO\Message
    {
        $options = $this->options;
        if ($caption) {
            $options['caption'] = $caption;
        }
        return $this->bot->sendDocument($this->chatId, $document, $options);
    }

    /**
     * 发送位置
     */
    public function location(float $latitude, float $longitude): \XBot\Telegram\Models\DTO\Message
    {
        return $this->bot->sendLocation($this->chatId, $latitude, $longitude, $this->options);
    }

    /**
     * 发送联系人
     */
    public function contact(string $phoneNumber, string $firstName, string $lastName = ''): \XBot\Telegram\Models\DTO\Message
    {
        $options = $this->options;
        if ($lastName) {
            $options['last_name'] = $lastName;
        }
        return $this->bot->sendContact($this->chatId, $phoneNumber, $firstName, $options);
    }

    /**
     * 发送投票
     */
    public function poll(string $question, array $options): \XBot\Telegram\Models\DTO\Message
    {
        return $this->bot->sendPoll($this->chatId, $question, $options, $this->options);
    }
}

/**
 * Telegram Bot 构建器
 * 
 * 提供指定 Bot 实例的链式调用接口
 */
class TelegramBotBuilder
{
    protected \XBot\Telegram\TelegramBot $bot;

    public function __construct(\XBot\Telegram\TelegramBot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * 选择聊天
     */
    public function to(int|string $chatId): TelegramMessageBuilder
    {
        return new TelegramMessageBuilder($this->bot, $chatId);
    }

    /**
     * 获取 Bot 信息
     */
    public function getMe(): \XBot\Telegram\Models\DTO\User
    {
        return $this->bot->getMe();
    }

    /**
     * 设置 Webhook
     */
    public function setWebhook(string $url, array $options = []): bool
    {
        return $this->bot->setWebhook($url, $options);
    }

    /**
     * 删除 Webhook
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): bool
    {
        return $this->bot->deleteWebhook($dropPendingUpdates);
    }

    /**
     * 获取更新
     */
    public function getUpdates(array $options = []): array
    {
        return $this->bot->getUpdates($options);
    }

    /**
     * 健康检查
     */
    public function healthCheck(): bool
    {
        return $this->bot->healthCheck();
    }

    /**
     * 获取统计信息
     */
    public function getStats(): array
    {
        return $this->bot->getStats();
    }

    /**
     * 执行原始 API 调用
     */
    public function call(string $method, array $parameters = []): \XBot\Telegram\Models\Response\TelegramResponse
    {
        return $this->bot->call($method, $parameters);
    }
}