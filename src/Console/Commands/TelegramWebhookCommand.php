<?php

declare(strict_types=1);

namespace XBot\Telegram\Console\Commands;

use Illuminate\Console\Command;
use XBot\Telegram\BotManager;

/**
 * Telegram Webhook 管理命令
 */
class TelegramWebhookCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'telegram:webhook 
                           {action : Action to perform (set|delete|info)}
                           {bot? : The bot name}
                           {--url= : Webhook URL (required for set action)}
                           {--all : Apply to all bots}
                           {--drop-pending : Drop pending updates when deleting webhook}
                           {--secret= : Webhook secret token}
                           {--certificate= : Path to certificate file}
                           {--max-connections=100 : Maximum allowed number of simultaneous connections}
                           {--allowed-updates=* : List of allowed update types}';

    /**
     * 命令描述
     */
    protected $description = 'Manage Telegram bot webhooks';

    /**
     * Bot 管理器
     */
    protected BotManager $botManager;

    public function __construct(BotManager $botManager)
    {
        parent::__construct();
        $this->botManager = $botManager;
    }

    /**
     * 执行命令
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $botName = $this->argument('bot');
        $applyToAll = $this->option('all');

        try {
            return match ($action) {
                'set' => $this->setWebhook($botName, $applyToAll),
                'delete' => $this->deleteWebhook($botName, $applyToAll),
                'info' => $this->getWebhookInfo($botName, $applyToAll),
                default => $this->invalidAction($action),
            };
        } catch (\Throwable $e) {
            $this->error("Failed to execute webhook action: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * 设置 Webhook
     */
    protected function setWebhook(?string $botName, bool $applyToAll): int
    {
        $url = $this->option('url');
        if (empty($url)) {
            $this->error('Webhook URL is required for set action. Use --url option.');
            return 1;
        }

        $options = $this->buildWebhookOptions();

        if ($applyToAll) {
            return $this->setAllWebhooks($url, $options);
        }

        $botName = $botName ?? $this->botManager->getDefaultBotName();
        
        if ($applyToAll) {
            // 为每个 Bot 使用不同的 URL 路径
            $results = $this->botManager->setAllWebhooks($url, $options);
            $this->displayBatchResults('Set Webhook', $results);
            return $this->hasFailures($results) ? 1 : 0;
        }

        $bot = $this->botManager->bot($botName);
        $webhookUrl = $this->buildBotWebhookUrl($url, $botName);

        $this->info("Setting webhook for bot: {$botName}");
        $this->info("Webhook URL: {$webhookUrl}");

        $success = $bot->setWebhook($webhookUrl, $options);

        if ($success) {
            $this->info("✅ Webhook set successfully for bot: {$botName}");
            return 0;
        } else {
            $this->error("❌ Failed to set webhook for bot: {$botName}");
            return 1;
        }
    }

    /**
     * 删除 Webhook
     */
    protected function deleteWebhook(?string $botName, bool $applyToAll): int
    {
        $dropPending = $this->option('drop-pending');

        if ($applyToAll) {
            $results = $this->botManager->deleteAllWebhooks($dropPending);
            $this->displayBatchResults('Delete Webhook', $results);
            return $this->hasFailures($results) ? 1 : 0;
        }

        $botName = $botName ?? $this->botManager->getDefaultBotName();
        $bot = $this->botManager->bot($botName);

        $this->info("Deleting webhook for bot: {$botName}");
        if ($dropPending) {
            $this->info("Dropping pending updates...");
        }

        $success = $bot->deleteWebhook($dropPending);

        if ($success) {
            $this->info("✅ Webhook deleted successfully for bot: {$botName}");
            return 0;
        } else {
            $this->error("❌ Failed to delete webhook for bot: {$botName}");
            return 1;
        }
    }

    /**
     * 获取 Webhook 信息
     */
    protected function getWebhookInfo(?string $botName, bool $applyToAll): int
    {
        if ($applyToAll) {
            return $this->getAllWebhooksInfo();
        }

        $botName = $botName ?? $this->botManager->getDefaultBotName();
        $bot = $this->botManager->bot($botName);

        $this->info("Webhook information for bot: {$botName}");
        $webhookInfo = $bot->getWebhookInfo();

        $this->displayWebhookInfo($webhookInfo);

        return 0;
    }

    /**
     * 设置所有 Bot 的 Webhook
     */
    protected function setAllWebhooks(string $baseUrl, array $options): int
    {
        $this->info("Setting webhooks for all bots...");
        $results = $this->botManager->setAllWebhooks($baseUrl, $options);
        $this->displayBatchResults('Set Webhook', $results);
        return $this->hasFailures($results) ? 1 : 0;
    }

    /**
     * 获取所有 Bot 的 Webhook 信息
     */
    protected function getAllWebhooksInfo(): int
    {
        $this->info("Webhook information for all bots:");
        $this->line('');

        $tableData = [];
        foreach ($this->botManager->getBotNames() as $botName) {
            try {
                $bot = $this->botManager->bot($botName);
                $info = $bot->getWebhookInfo();
                
                $tableData[] = [
                    $botName,
                    $info['url'] ?: 'Not set',
                    $info['has_custom_certificate'] ? 'Yes' : 'No',
                    $info['pending_update_count'] ?? 0,
                    $info['last_error_date'] ? 'Yes' : 'No',
                ];
            } catch (\Throwable $e) {
                $tableData[] = [
                    $botName,
                    'ERROR',
                    'N/A',
                    'N/A',
                    substr($e->getMessage(), 0, 30) . '...',
                ];
            }
        }

        $this->table(
            ['Bot Name', 'Webhook URL', 'Custom Cert', 'Pending Updates', 'Has Error'],
            $tableData
        );

        return 0;
    }

    /**
     * 构建 Webhook 选项
     */
    protected function buildWebhookOptions(): array
    {
        $options = [];

        if ($secret = $this->option('secret')) {
            $options['secret_token'] = $secret;
        }

        if ($certificate = $this->option('certificate')) {
            if (file_exists($certificate)) {
                $options['certificate'] = $certificate;
            } else {
                $this->warn("Certificate file not found: {$certificate}");
            }
        }

        if ($maxConnections = $this->option('max-connections')) {
            $options['max_connections'] = (int) $maxConnections;
        }

        if ($allowedUpdates = $this->option('allowed-updates')) {
            $options['allowed_updates'] = $allowedUpdates;
        }

        return $options;
    }

    /**
     * 构建 Bot 的 Webhook URL
     */
    protected function buildBotWebhookUrl(string $baseUrl, string $botName): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        
        // 如果 URL 已经包含了 Bot 名称，直接返回
        if (str_contains($baseUrl, $botName)) {
            return $baseUrl;
        }

        // 否则添加 Bot 名称作为路径
        return "{$baseUrl}/{$botName}";
    }

    /**
     * 显示 Webhook 信息
     */
    protected function displayWebhookInfo(array $info): void
    {
        $tableData = [
            ['URL', $info['url'] ?: 'Not set'],
            ['Has Custom Certificate', $info['has_custom_certificate'] ? 'Yes' : 'No'],
            ['Pending Update Count', $info['pending_update_count'] ?? 0],
            ['Max Connections', $info['max_connections'] ?? 'Default'],
        ];

        if (!empty($info['ip_address'])) {
            $tableData[] = ['IP Address', $info['ip_address']];
        }

        if (!empty($info['last_error_date'])) {
            $tableData[] = ['Last Error Date', date('Y-m-d H:i:s', $info['last_error_date'])];
            $tableData[] = ['Last Error Message', $info['last_error_message'] ?? 'Unknown'];
        }

        if (!empty($info['allowed_updates'])) {
            $tableData[] = ['Allowed Updates', implode(', ', $info['allowed_updates'])];
        }

        $this->table(['Property', 'Value'], $tableData);
    }

    /**
     * 显示批量操作结果
     */
    protected function displayBatchResults(string $operation, array $results): void
    {
        $this->line('');
        $this->info("{$operation} Results:");

        $tableData = [];
        foreach ($results as $result) {
            $status = $result['success'] ? '✅ Success' : '❌ Failed';
            $info = $result['success'] 
                ? ($result['webhook_url'] ?? 'N/A')
                : substr($result['error'], 0, 50) . '...';
            
            $tableData[] = [
                $result['name'],
                $status,
                $info,
            ];
        }

        $this->table(['Bot Name', 'Status', 'Info'], $tableData);

        $successful = array_filter($results, fn($r) => $r['success']);
        $failed = array_filter($results, fn($r) => !$r['success']);

        $this->line('');
        $this->info("Summary: " . count($successful) . " successful, " . count($failed) . " failed");
    }

    /**
     * 检查是否有失败的操作
     */
    protected function hasFailures(array $results): bool
    {
        return !empty(array_filter($results, fn($r) => !$r['success']));
    }

    /**
     * 处理无效操作
     */
    protected function invalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->info("Available actions: set, delete, info");
        return 1;
    }
}