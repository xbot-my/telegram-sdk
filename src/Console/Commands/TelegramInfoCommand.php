<?php

declare(strict_types=1);

namespace XBot\Telegram\Console\Commands;

use Illuminate\Console\Command;
use XBot\Telegram\BotManager;

/**
 * Telegram Bot 信息查看命令
 */
class TelegramInfoCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'telegram:info 
                           {bot? : The bot name to show info for}
                           {--all : Show info for all bots}
                           {--json : Output as JSON}';

    /**
     * 命令描述
     */
    protected $description = 'Show Telegram bot information';

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
        try {
            $botName = $this->argument('bot');
            $showAll = $this->option('all');
            $outputJson = $this->option('json');

            if ($showAll) {
                $info = $this->getAllBotsInfo();
            } elseif ($botName) {
                $info = $this->getBotInfo($botName);
            } else {
                $info = $this->getBotInfo($this->botManager->getDefaultBotName());
            }

            if ($outputJson) {
                $this->info(json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->displayInfo($info, $showAll);
            }

            return 0;

        } catch (\Throwable $e) {
            $this->error("Failed to get bot info: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * 获取单个 Bot 信息
     */
    protected function getBotInfo(string $botName): array
    {
        $bot = $this->botManager->bot($botName);
        $botInfo = $bot->getMe();
        $stats = $bot->getStats();

        return [
            'name' => $botName,
            'bot_info' => $botInfo->toArray(),
            'stats' => $stats,
            'config' => $this->botManager->getBotConfig($botName),
        ];
    }

    /**
     * 获取所有 Bot 信息
     */
    protected function getAllBotsInfo(): array
    {
        $info = [];
        
        foreach ($this->botManager->getBotNames() as $botName) {
            try {
                $info[$botName] = $this->getBotInfo($botName);
                $info[$botName]['status'] = 'active';
            } catch (\Throwable $e) {
                $info[$botName] = [
                    'name' => $botName,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $info;
    }

    /**
     * 显示信息
     */
    protected function displayInfo(array $info, bool $showAll = false): void
    {
        if ($showAll) {
            $this->displayAllBotsInfo($info);
        } else {
            $this->displaySingleBotInfo($info);
        }
    }

    /**
     * 显示单个 Bot 信息
     */
    protected function displaySingleBotInfo(array $info): void
    {
        $this->info("Telegram Bot Information: {$info['name']}");
        $this->line('');

        // Bot 基本信息
        $botInfo = $info['bot_info'];
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $botInfo['id']],
                ['Username', $botInfo['username'] ?? 'N/A'],
                ['First Name', $botInfo['first_name']],
                ['Is Bot', $botInfo['is_bot'] ? 'Yes' : 'No'],
                ['Can Join Groups', $botInfo['can_join_groups'] ?? 'N/A'],
                ['Can Read All Group Messages', $botInfo['can_read_all_group_messages'] ?? 'N/A'],
                ['Supports Inline Queries', $botInfo['supports_inline_queries'] ?? 'N/A'],
            ]
        );

        // 统计信息
        $stats = $info['stats'];
        $this->line('');
        $this->info('Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Calls', $stats['total_calls']],
                ['Successful Calls', $stats['successful_calls']],
                ['Failed Calls', $stats['failed_calls']],
                ['Success Rate', number_format($stats['success_rate'], 2) . '%'],
                ['Uptime', $stats['uptime_formatted']],
                ['Last Call', $stats['last_call_time'] ? date('Y-m-d H:i:s', $stats['last_call_time']) : 'Never'],
            ]
        );

        // 配置信息（隐藏敏感信息）
        $config = $info['config'];
        $config['token'] = substr($config['token'], 0, 10) . '...';
        if (isset($config['webhook_secret'])) {
            $config['webhook_secret'] = '***';
        }

        $this->line('');
        $this->info('Configuration:');
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $this->line("  {$key}: {$value}");
        }
    }

    /**
     * 显示所有 Bot 信息
     */
    protected function displayAllBotsInfo(array $info): void
    {
        $this->info('All Telegram Bots Information');
        $this->line('');

        $tableData = [];
        foreach ($info as $botName => $botInfo) {
            if (isset($botInfo['status']) && $botInfo['status'] === 'error') {
                $tableData[] = [
                    $botName,
                    'ERROR',
                    'N/A',
                    'N/A',
                    substr($botInfo['error'], 0, 50) . '...',
                ];
            } else {
                $tableData[] = [
                    $botName,
                    'ACTIVE',
                    $botInfo['bot_info']['username'] ?? 'N/A',
                    $botInfo['stats']['total_calls'],
                    number_format($botInfo['stats']['success_rate'], 2) . '%',
                ];
            }
        }

        $this->table(
            ['Bot Name', 'Status', 'Username', 'Total Calls', 'Success Rate'],
            $tableData
        );

        // 管理器统计
        $managerStats = $this->botManager->getStats();
        $this->line('');
        $this->info('Manager Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Bots Configured', $managerStats['total_bots_configured']],
                ['Total Bots Loaded', $managerStats['total_bots_loaded']],
                ['Default Bot', $managerStats['default_bot']],
                ['Manager Uptime', $managerStats['uptime_formatted']],
                ['Memory Usage', $this->formatBytes($managerStats['memory_usage'])],
            ]
        );
    }

    /**
     * 格式化字节数
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}