<?php

use LifeOnScreen\LaravelLogKeeper\Support\LogUtil;
use Carbon\Carbon;

class LogUtilTest extends TestCase
{

    /**
     * @test
     */
    public function it_gets_only_logs()
    {
        $logs = [
            'storage/logs/laravel.log.',
            'storage/logs/laravel-2016-03-11.log.',
            'storage/logs/laravel-2016-03-11.log.tar.gz',
            'storage/logs/laravel-2016-03-11.log.tgz',
            'storage/logs/laravel-2016-03-11.log.zip',
            'storage/logs/laravel-2016-03-11.logtmp',
            'storage/logs/laravel-2016-03-11.log',
            'storage/logs/laravel-2016-03-12.log',
            'storage/logs/laravel-2016-03-13.log',
            'storage/logs/laravel-2016-03-14.log',
        ];

        $expected = [
            'storage/logs/laravel-2016-03-11.log',
            'storage/logs/laravel-2016-03-12.log',
            'storage/logs/laravel-2016-03-13.log',
            'storage/logs/laravel-2016-03-14.log',
        ];

        $logs = LogUtil::getLogs($logs);

        $this->assertSame($expected, $logs);
    }

    /**
     * @test
     */
    public function it_gets_only_compressed_logs()
    {
        $logs = [
            'storage/logs/laravel.log.',
            'storage/logs/laravel-2016-03-11.log.',
            'storage/logs/laravel-2016-03-11.log.tar.gz',
            'storage/logs/laravel-2016-03-11.log.tgz',
            'storage/logs/laravel-2016-03-11.log.zip',
            'storage/logs/laravel-2016-03-11.logtmp',
            'storage/logs/laravel-2016-03-11.log',
            'storage/logs/laravel-2016-03-12.log',
            'storage/logs/laravel-2016-03-13.log',
            'storage/logs/laravel-2016-03-14.log.123123123123.tar.bz2',
            'storage/logs/laravel-2016-03-14.log.asdasd.tar.bz2',
            'storage/logs/laravel-2016-03-14.log..bz2',
            'storage/logs/laravel-2016-03-14.log.tar.bz2',
        ];

        $expected = [
            'storage/logs/laravel-2016-03-14.log.tar.bz2',
        ];

        $logs = LogUtil::getCompressed($logs);

        $this->assertSame($expected, $logs);
    }

    /**
     * @test
     */
    public function it_maps_basename()
    {
        $logs = [
            'storage/logs/laravel-2016-03-11.log',
            'storage/logs/laravel-2016-03-12.log',
            'storage/logs/laravel-2016-03-13.log',
            'storage/logs/laravel-2016-03-14.log',
        ];

        $expected = [
            'laravel-2016-03-11.log',
            'laravel-2016-03-12.log',
            'laravel-2016-03-13.log',
            'laravel-2016-03-14.log',
        ];

        $logs = LogUtil::mapBasename($logs);

        $this->assertSame($expected, $logs);
    }

    /**
     * @test
     */
    public function it_gets_the_date_as_carbon_or_throw_exception_if_it_is_not_valid_log()
    {
        $logs = [
            'storage/logs/laravel.log'                   => 'exception',
            'storage/logs/laravel.log.'                  => 'exception',
            'storage/logs/laravel-2016-03-11.log.'       => '2016-03-11',
            'storage/logs/laravel-2016-03-11.log.tar.gz' => '2016-03-11',
            'storage/logs/laravel-2016-03-11.log.tgz'    => '2016-03-11',
            'storage/logs/laravel-2016-03-11.log.zip'    => '2016-03-11',
            'storage/logs/laravel-2016-03-11.logtmp'     => '2016-03-11',
            'storage/logs/laravel-2016-03-11.log'        => '2016-03-11',
            'storage/logs/laravel-2016-03-12.log'        => '2016-03-12',
            'storage/logs/laravel-2016-03-13.log'        => '2016-03-13',
            'storage/logs/laravel-2016-03-14.log'        => '2016-03-14',
        ];

        foreach ($logs as $log => $result) {
            $e = null;

            try {
                $date = LogUtil::getDate($log);
                $date = $date->toDateString();
                $this->assertSame($result, $date);
            } catch (\Exception $e) {
                //
            }

            if ('exception' == $result) {
                $this->assertTrue((bool)$e);
            } else {
                $this->assertFalse((bool)$e, "Expected: {$result} Obtained: {$date}");
            }
        }
    }

    /**
     * @test
     */
    public function it_gets_the_diff()
    {
        $today = Carbon::today();
        $logDate = Carbon::today()->subDays(10);

        $log = "laravel-{$logDate->toDateString()}.log";

        $this->assertSame(10, LogUtil::diffInDays($log, $today));
    }
}
