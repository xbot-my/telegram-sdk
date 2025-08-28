<?php

declare(strict_types=1);

namespace XBot\Telegram\Console\Commands;

use Illuminate\Console\Command;
use XBot\Telegram\BotManager;

/**
 * Telegram Bot 统计信息命令
 */
class TelegramStatsCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'telegram:stats 
                           {bot? : The bot name to show stats for}
                           {--all : Show stats for all bots}
                           {--json : Output as JSON}
                           {--reset : Reset statistics}';

    /**
     * 命令描述
     */
    protected $description = 'Show Telegram bot statistics';

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
            $resetStats = $this->option('reset');

            if ($resetStats) {
                return $this->resetStatistics($botName, $showAll);
            }

            if ($showAll) {
                $stats = $this->getAllStats();
            } elseif ($botName) {
                $stats = $this->getBotStats($botName);
            } else {
                $stats = $this->getBotStats($this->botManager->getDefaultBotName());
            }

            if ($outputJson) {
                $this->info(json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->displayStats($stats, $showAll);
            }

            return 0;

        } catch (\Throwable $e) {
            $this->error("Failed to get statistics: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * 获取单个 Bot 统计信息
     */
    protected function getBotStats(string $botName): array
    {
        $bot = $this->botManager->bot($botName);
        $botStats = $bot->getStats();
        
        // 获取 HTTP 客户端统计
        $httpStats = $botStats['http_client_stats'] ?? [];

        return [
            'bot_name' => $botName,
            'bot_stats' => $botStats,
            'http_stats' => $httpStats,
        ];
    }

    /**
     * 获取所有 Bot 统计信息
     */
    protected function getAllStats(): array
    {
        $allStats = [];
        
        foreach ($this->botManager->getBotNames() as $botName) {
            try {
                $allStats[$botName] = $this->getBotStats($botName);
                $allStats[$botName]['status'] = 'active';
            } catch (\Throwable $e) {
                $allStats[$botName] = [
                    'bot_name' => $botName,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // 添加管理器统计
        $allStats['_manager'] = $this->botManager->getStats();

        return $allStats;
    }

    /**
     * 显示统计信息
     */
    protected function displayStats(array $stats, bool $showAll = false): void
    {
        if ($showAll) {
            $this->displayAllStats($stats);
        } else {
            $this->displaySingleBotStats($stats);
        }
    }

    /**
     * 显示单个 Bot 统计信息
     */
    protected function displaySingleBotStats(array $stats): void
    {
        $botName = $stats['bot_name'];
        $botStats = $stats['bot_stats'];
        
        $this->info("Statistics for bot: {$botName}");
        $this->line('');

        // Bot 统计
        $this->info('Bot Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Calls', number_format($botStats['total_calls'])],
                ['Successful Calls', number_format($botStats['successful_calls'])],
                ['Failed Calls', number_format($botStats['failed_calls'])],
                ['Success Rate', number_format($botStats['success_rate'], 2) . '%'],
                ['Uptime', $botStats['uptime_formatted']],
                ['Created At', date('Y-m-d H:i:s', $botStats['created_at'])],
                ['Last Call', $botStats['last_call_time'] ? date('Y-m-d H:i:s', $botStats['last_call_time']) : 'Never'],
            ]
        );

        // HTTP 客户端统计
        if (!empty($stats['http_stats'])) {
            $httpStats = $stats['http_stats'];
            $this->line('');
            $this->info('HTTP Client Statistics:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Requests', number_format($httpStats['total_requests'] ?? 0)],
                    ['Successful Requests', number_format($httpStats['successful_requests'] ?? 0)],
                    ['Failed Requests', number_format($httpStats['failed_requests'] ?? 0)],
                    ['Retry Count', number_format($httpStats['retry_count'] ?? 0)],
                    ['Success Rate', number_format($httpStats['success_rate'] ?? 0, 2) . '%'],
                    ['Average Time', number_format($httpStats['average_time'] ?? 0, 3) . 's'],
                    ['Total Time', number_format($httpStats['total_time'] ?? 0, 3) . 's'],
                ]
            );
        }
    }

    /**
     * 显示所有 Bot 统计信息
     */
    protected function displayAllStats(array $allStats): void
    {
        $this->info('All Telegram Bots Statistics');
        $this->line('');

        // 分离管理器统计
        $managerStats = $allStats['_manager'] ?? [];
        unset($allStats['_manager']);

        // Bot 统计表格
        $tableData = [];
        $totalCalls = 0;
        $totalSuccessful = 0;
        $totalFailed = 0;

        foreach ($allStats as $botName => $stats) {
            if (isset($stats['status']) && $stats['status'] === 'error') {
                $tableData[] = [
                    $botName,
                    'ERROR',
                    'N/A',
                    'N/A',
                    'N/A',
                    substr($stats['error'], 0, 30) . '...',
                ];
            } else {
                $botStats = $stats['bot_stats'];
                $totalCalls += $botStats['total_calls'];
                $totalSuccessful += $botStats['successful_calls'];
                $totalFailed += $botStats['failed_calls'];

                $tableData[] = [
                    $botName,
                    'ACTIVE',
                    number_format($botStats['total_calls']),
                    number_format($botStats['successful_calls']),
                    number_format($botStats['success_rate'], 1) . '%',
                    $botStats['uptime_formatted'],
                ];
            }
        }

        $this->table(
            ['Bot Name', 'Status', 'Total Calls', 'Successful', 'Success Rate', 'Uptime'],
            $tableData
        );

        // 汇总统计
        $this->line('');
        $this->info('Summary Statistics:');
        $overallSuccessRate = $totalCalls > 0 ? ($totalSuccessful / $totalCalls) * 100 : 0;
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Bots', count($allStats)],
                ['Total Calls (All Bots)', number_format($totalCalls)],
                ['Total Successful (All Bots)', number_format($totalSuccessful)],
                ['Total Failed (All Bots)', number_format($totalFailed)],
                ['Overall Success Rate', number_format($overallSuccessRate, 2) . '%'],
            ]
        );

        // 管理器统计
        if (!empty($managerStats)) {
            $this->line('');
            $this->info('Manager Statistics:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Bots Configured', $managerStats['total_bots_configured']],
                    ['Total Bots Loaded', $managerStats['total_bots_loaded']],
                    ['Total Bots Created', $managerStats['total_bots_created']],
                    ['Total Bots Removed', $managerStats['total_bots_removed']],
                    ['Total Reload Count', $managerStats['total_reload_count']],
                    ['Default Bot', $managerStats['default_bot']],
                    ['Manager Uptime', $managerStats['uptime_formatted']],
                    ['Memory Usage', $this->formatBytes($managerStats['memory_usage'])],
                    ['Peak Memory', $this->formatBytes($managerStats['memory_peak'])],
                ]
            );
        }
    }

    /**
     * 重置统计信息
     */
    protected function resetStatistics(?string $botName, bool $resetAll): int
    {
        if ($resetAll) {
            if (!$this->confirm('Are you sure you want to reset statistics for ALL bots?')) {
                $this->info('Operation cancelled.');
                return 0;
            }

            $this->info('Resetting statistics for all bots...');
            foreach ($this->botManager->getBotNames() as $name) {
                try {
                    $this->botManager->reloadBot($name);
                    $this->info("✅ Reset statistics for bot: {$name}");
                } catch (\Throwable $e) {
                    $this->error("❌ Failed to reset statistics for bot {$name}: {$e->getMessage()}");
                }
            }
        } else {
            $botName = $botName ?? $this->botManager->getDefaultBotName();
            
            if (!$this->confirm("Are you sure you want to reset statistics for bot '{$botName}'?")) {
                $this->info('Operation cancelled.');
                return 0;
            }

            try {
                $this->botManager->reloadBot($botName);
                $this->info("✅ Reset statistics for bot: {$botName}");
            } catch (\Throwable $e) {
                $this->error("❌ Failed to reset statistics for bot {$botName}: {$e->getMessage()}");
                return 1;
            }
        }

        return 0;
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