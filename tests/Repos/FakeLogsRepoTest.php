<?php

use LifeOnScreen\LaravelLogKeeper\Repos\FakeLogsRepo;

class FakeLogsRepoTest extends TestCase
{
    private function getRepo()
    {
        $config = config('laravel-log-keeper');
        $repo = new FakeLogsRepo($config);

        return $repo;
    }

    /**
     * @test
     */
    public function it_gets_logs()
    {
        $repo = $this->getRepo();
        $logs = $repo->getLogs();

        $this->assertTrue(count($logs) > 0);

        foreach ($logs as $log) {
            $this->assertTrue((bool)preg_match('/.*?-\d{4}-\d{2}-\d{2}\.log/', $log));
        }
    }

    /**
     * @test
     */
    public function it_compresses()
    {
        $repo = $this->getRepo();
        $logs = $repo->getCompressed();

        $log = 'laravel-2016-01-10.log';
        $compressed = $log . '.tar.bz2';

        $this->assertFalse(in_array($compressed, $logs));

        $repo->compress($log, $compressed);

        $logs = $repo->getCompressed();

        $this->assertTrue(in_array($compressed, $logs));
    }

    /**
     * @test
     */
    public function it_deletes()
    {
        $repo = $this->getRepo();
        $logs = $repo->getLogs();

        $log = $logs[0];
        $repo->delete($log);

        $logs = $repo->getLogs();
        $this->assertFalse(in_array($log, $logs));
    }

}