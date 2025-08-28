<?php

declare(strict_types=1);

namespace XBot\Telegram\Console\Commands;

use Illuminate\Console\Command;
use XBot\Telegram\BotManager;

/**
 * Telegram Bot 健康检查命令
 */
class TelegramHealthCheckCommand extends Command
{
    /**
     * 命令签名
     */
    protected $signature = 'telegram:health 
                           {bot? : The bot name to check}
                           {--all : Check all bots}
                           {--json : Output as JSON}
                           {--timeout=10 : Health check timeout in seconds}';

    /**
     * 命令描述
     */
    protected $description = 'Check Telegram bot health status';

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
        $botName = $this->argument('bot');
        $checkAll = $this->option('all');
        $outputJson = $this->option('json');
        
        try {
            if ($checkAll) {
                $results = $this->botManager->healthCheck();
            } elseif ($botName) {
                $results = [$botName => $this->checkSingleBot($botName)];
            } else {
                $defaultBot = $this->botManager->getDefaultBotName();
                $results = [$defaultBot => $this->checkSingleBot($defaultBot)];
            }

            if ($outputJson) {
                $this->info(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->displayResults($results);
            }

            // 返回错误代码如果有任何 Bot 不健康
            $hasUnhealthy = !empty(array_filter($results, fn($r) => !$r['is_healthy']));
            return $hasUnhealthy ? 1 : 0;

        } catch (\Throwable $e) {
            $this->error("Health check failed: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * 检查单个 Bot
     */
    protected function checkSingleBot(string $botName): array
    {
        $startTime = microtime(true);
        
        try {
            $bot = $this->botManager->bot($botName);
            $isHealthy = $bot->healthCheck();
            $responseTime = microtime(true) - $startTime;

            return [
                'name' => $botName,
                'is_loaded' => true,
                'is_healthy' => $isHealthy,
                'response_time' => round($responseTime * 1000, 2), // ms
                'error' => null,
            ];

        } catch (\Throwable $e) {
            $responseTime = microtime(true) - $startTime;
            
            return [
                'name' => $botName,
                'is_loaded' => false,
                'is_healthy' => false,
                'response_time' => round($responseTime * 1000, 2), // ms
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 显示结果
     */
    protected function displayResults(array $results): void
    {
        $this->info('Telegram Bot Health Check Results');
        $this->line('');

        $tableData = [];
        $totalBots = count($results);
        $healthyBots = 0;

        foreach ($results as $result) {
            $status = $result['is_healthy'] ? '✅ Healthy' : '❌ Unhealthy';
            $responseTime = $result['response_time'] . 'ms';
            $error = $result['error'] ? substr($result['error'], 0, 50) . '...' : 'None';

            if ($result['is_healthy']) {
                $healthyBots++;
            }

            $tableData[] = [
                $result['name'],
                $result['is_loaded'] ? 'Loaded' : 'Not Loaded',
                $status,
                $responseTime,
                $error,
            ];
        }

        $this->table(
            ['Bot Name', 'Status', 'Health', 'Response Time', 'Error'],
            $tableData
        );

        // 总结
        $this->line('');
        $this->info("Summary: {$healthyBots}/{$totalBots} bots are healthy");

        if ($healthyBots === $totalBots) {
            $this->info('🎉 All bots are healthy!');
        } else {
            $unhealthyCount = $totalBots - $healthyBots;
            $this->warn("⚠️  {$unhealthyCount} bot(s) are unhealthy");
        }

        // 显示详细错误信息
        $unhealthyBots = array_filter($results, fn($r) => !$r['is_healthy']);
        if (!empty($unhealthyBots)) {
            $this->line('');
            $this->error('Unhealthy Bots Details:');
            foreach ($unhealthyBots as $bot) {
                $this->line("  • {$bot['name']}: {$bot['error']}");
            }
        }

        // 性能统计
        $responseTimes = array_column($results, 'response_time');
        if (!empty($responseTimes)) {
            $avgResponseTime = array_sum($responseTimes) / count($responseTimes);
            $maxResponseTime = max($responseTimes);
            $minResponseTime = min($responseTimes);

            $this->line('');
            $this->info('Performance Statistics:');
            $this->line("  Average Response Time: " . round($avgResponseTime, 2) . "ms");
            $this->line("  Min Response Time: " . round($minResponseTime, 2) . "ms");
            $this->line("  Max Response Time: " . round($maxResponseTime, 2) . "ms");
        }
    }
}