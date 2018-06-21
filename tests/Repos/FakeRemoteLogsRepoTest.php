<?php

use LifeOnScreen\LaravelLogKeeper\Repos\FakeRemoteLogsRepo;

class FakeRemoteLogsRepoTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_when_enable_remote_is_true_and_remote_disk_is_null()
    {
        $config = config('laravel-log-keeper');

        $config['enabled_remote'] = true;
        $config['remote_disk'] = null;

        try {
            $repo = new FakeRemoteLogsRepo($config);
            $this->fail('It should not get here');
        } catch (Exception $e) {
            $this->assertSame('remote_disk not configured for Laravel Log Keeper', $e->getMessage());
        }

    }

}