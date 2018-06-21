<?php

use LifeOnScreen\LaravelLogKeeper\Repos\FakeLogsRepo;
use LifeOnScreen\LaravelLogKeeper\Services\LogKeeperService;
use LifeOnScreen\LaravelLogKeeper\Support\LogUtil;
use Carbon\Carbon;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

class LogKeeperServiceTest extends TestCase
{
    private function getLogger()
    {
        $logger = new Logger('laravel-log-keeper');
        $logger->pushHandler(new NullHandler());

        return $logger;
    }

    /**
     * @test
     */
    public function files_are_being_created_on_remote()
    {
        $today = Carbon::today();
        $config = config('laravel-log-keeper');
        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);
        $remoteRepo->setLogs([]);

        $logs = $localRepo->getLogs();

        $logsToMove = array_filter($logs, function ($log) use ($today, $config) {
            $date = LogUtil::getDate($log);
            $diff = $date->diffInDays($today);

            return (($diff > $config['localRetentionDays']) && ($diff <= $config['remoteRetentionDaysCalculated']));
        });

        $logsToMove = array_values(array_map(function ($item) {
            return $item . '.tar.bz2';
        }, $logsToMove));

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $logs = $remoteRepo->getCompressed();

        $this->assertSame($logsToMove, $logs);
    }

    /**
     * @test
     */
    public function it_has_no_local_files_older_than_the_local_retention()
    {
        $today = Carbon::today();
        $config = config('laravel-log-keeper');
        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);
        $remoteRepo->setLogs([]);

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $logs = $localRepo->getLogs();
        foreach ($logs as $log) {
            $date = LogUtil::getDate($log);
            $this->assertTrue($today->diffInDays($date) <= $config['localRetentionDays']);
        }
    }

    /**
     * @test
     */
    public function it_has_no_local_compressed_files()
    {
        $config = config('laravel-log-keeper');
        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);
        $remoteRepo->setLogs([]);

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $logs = $localRepo->getLogs();

        foreach ($logs as $log) {
            $this->assertFalse((bool)preg_match('/\.tar\.bz2$/', $log), $log);
        }
    }

    /**
     * @test
     */
    public function it_has_no_remote_files_newer_than_the_local_retention()
    {
        $today = Carbon::today();
        $config = config('laravel-log-keeper');
        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);
        $remoteRepo->setLogs([]);

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $logs = $remoteRepo->getLogs();

        foreach ($logs as $log) {
            $date = LogUtil::getDate($log);
            $diff = $today->diffInDays($date);
            $this->assertTrue($diff > $config['localRetentionDays'], "Diff: {$diff} days Log: $log");
        }
    }

    /**
     * @test
     */
    public function it_has_no_remote_files_older_than_the_remote_retention()
    {
        $today = Carbon::today();
        $config = config('laravel-log-keeper');
        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);
        $remoteRepo->setLogs([]);

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $logs = $remoteRepo->getLogs();

        foreach ($logs as $log) {
            $date = LogUtil::getDate($log);
            $diff = $today->diffInDays($date);
            $this->assertTrue($diff <= $config['remoteRetentionDaysCalculated'], "Diff: {$diff} days Log: $log");
        }
    }

    /**
     * @tests
     */
    public function it_deletes_old_remote_files()
    {
        $today = Carbon::today();
        $config = config('laravel-log-keeper');
        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);

        $localRepo->setLogs([]);

        $days = $config['remoteRetentionDaysCalculated'];
        $new = "/fake/storage/logs/laravel-new-{$today->addDays($days)->toDateString()}.log.tar.bz2";

        $remoteRepo->setLogs([
            '/fake/storage/logs/laravel-old-2010-01-01.log',
            '/fake/storage/logs/laravel-old-2010-01-02.log',
            $new,
        ]);

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $logs = $remoteRepo->getCompressed();

        $this->assertSame(LogUtil::mapBasename([$new]), $logs);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_enabled_is_false()
    {
        $config = config('laravel-log-keeper');

        $config['enabled'] = false;

        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);

        $localLogs = [
            '/fake/storage/logs/laravel-old-2010-01-01.log',
        ];

        $localRepo->setLogs($localLogs);

        $remoteRepo->setLogs([]);

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $this->assertSame(['laravel-old-2010-01-01.log'], $localRepo->getLogs());
        $this->assertSame([], $remoteRepo->getCompressed());
    }

    /**
     * @test
     */
    public function it_does_nothing_remotely_if_enabled_remote_is_false()
    {
        $today = Carbon::today();
        $config = config('laravel-log-keeper');

        $config['enabled_remote'] = false;

        $localRepo = new FakeLogsRepo($config);
        $remoteRepo = new FakeLogsRepo($config);

        $days = $config['localRetentionDays'] + 1;

        $localLogs = [
            '/fake/storage/logs/laravel-2010-01-01.log', // will be deleted
            "/fake/storage/logs/laravel-today-{$today->toDateString()}.log", // nothing
            "/fake/storage/logs/laravel-{$today->addDays($days)->toDateString()}.log", // upload if remote was enabled
        ];

        $localRepo->setLogs($localLogs);

        $remoteRepo->setLogs([]);

        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $this->getLogger());
        $service->work();

        $today = Carbon::today();
        $logs = $localRepo->getLogs();

        $this->assertSame(["laravel-today-{$today->toDateString()}.log"], $logs);
        $this->assertSame([], $remoteRepo->getCompressed());
    }
}
