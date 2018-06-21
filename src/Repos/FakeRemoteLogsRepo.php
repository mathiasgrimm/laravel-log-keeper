<?php

namespace LifeOnScreen\LaravelLogKeeper\Repos;

use Exception;

class FakeRemoteLogsRepo extends FakeLogsRepo
{
    /**
     * FakeRemoteLogsRepo constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if ($this->config['enabled_remote'] && !$this->config['remote_disk']) {
            throw new Exception("remote_disk not configured for Laravel Log Keeper");
        }
    }
}