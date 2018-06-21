<?php

namespace LifeOnScreen\LaravelLogKeeper\Factories;

use LifeOnScreen\LaravelLogKeeper\Repos\LocalLogsRepo;
use LifeOnScreen\LaravelLogKeeper\Repos\RemoteLogsRepo;
use LifeOnScreen\LaravelLogKeeper\Services\LogKeeperService;
use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class LogKeeperServiceFactory
{
    /**
     * @param array $config
     * @return LogKeeperService
     * @throws \Exception
     */
    public static function buildFromConfig(array $config)
    {
        $logger = new Logger('laravel-log-keeper');

        if ($config['log']) {
            $logger->pushHandler(new RotatingFileHandler(storage_path('logs') . '/laravellogkeeper.log', 365, Logger::INFO));
        } else {
            $logger->pushHandler(new NullHandler());
        }

        $localRepo = new LocalLogsRepo($config);
        $remoteRepo = new RemoteLogsRepo($config);
        $service = new LogKeeperService($config, $localRepo, $remoteRepo, $logger);

        return $service;
    }

    /**
     * @return LogKeeperService
     * @throws \Exception
     */
    public static function buildFromLaravelConfig()
    {
        $config = config('laravel-log-keeper');

        return static::buildFromConfig($config);
    }
}