<?php

namespace LifeOnScreen\LaravelLogKeeper\Repos;

use Carbon\Carbon;
use LifeOnScreen\LaravelLogKeeper\Support\LogUtil;

class FakeLogsRepo implements LogsRepoInterface
{
    protected $config;

    protected $logs;

    public function __construct(array $config)
    {
        $this->config = $config;

        $localRetentionMinus1 = Carbon::today()->subDays($this->config['localRetentionDays'] - 1);
        $localRetention = Carbon::today()->subDays($this->config['localRetentionDays']);
        $localRetentionPlus1 = Carbon::today()->subDays($this->config['localRetentionDays'] + 1);

        $remoteRetentionMinus1 = Carbon::today()->subDays($this->config['remoteRetentionDaysCalculated'] - 1);
        $remoteRetention = Carbon::today()->subDays($this->config['remoteRetentionDaysCalculated']);
        $remoteRetentionPlus1 = Carbon::today()->subDays($this->config['remoteRetentionDaysCalculated'] + 1);

        $this->logs = [
            '/fake/storage/logs/laravel-old-2010-01-01.log',
            '/fake/storage/logs/laravel-old-2010-01-02.log',

            "/fake/storage/logs/laravel-6days-{$localRetentionMinus1->toDateString()}.log",
            "/fake/storage/logs/laravel-7days-{$localRetention->toDateString()}.log",
            "/fake/storage/logs/laravel-8days-{$localRetentionPlus1->toDateString()}.log",

            "/fake/storage/logs/laravel-29days-{$remoteRetentionMinus1->toDateString()}.log",
            "/fake/storage/logs/laravel-30days-{$remoteRetention->toDateString()}.log",
            "/fake/storage/logs/laravel-31days-{$remoteRetentionPlus1->toDateString()}.log",
        ];
    }

    public function getLogs()
    {
        $logs = LogUtil::getLogs($this->logs);
        $logs = LogUtil::mapBasename($logs);

        return $logs;
    }

    public function setLogs(array $logs)
    {
        $this->logs = $logs;
    }

    public function getCompressed()
    {
        $logs = LogUtil::getCompressed($this->logs);
        $logs = LogUtil::mapBasename($logs);

        return $logs;
    }

    public function delete($log)
    {
        $this->logs = array_filter($this->logs, function ($item) use ($log) {
            return "/fake/storage/logs/{$log}" !== $item;
        });
    }

    public function compress($log, $compressedName)
    {
        $this->logs[] = '/fake/storage/logs/' . $compressedName;
        $this->delete($log);
    }

    public function get($log)
    {
        return 'dummy file content';
    }

    public function put($log, $content)
    {
        $this->logs[] = '/fake/storage/logs/' . $log;
    }
}