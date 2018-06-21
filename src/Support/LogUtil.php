<?php

namespace LifeOnScreen\LaravelLogKeeper\Support;

use Carbon\Carbon;
use InvalidArgumentException;

class LogUtil
{
    /**
     * Get all logs that follow the daily format
     *
     * @param      $logs
     * @param bool $keepIndex
     * @return mixed
     */
    public static function getCompressed($logs, $keepIndex = false)
    {
        $logs = array_filter($logs, function ($item) {
            return (bool)preg_match('/^.*?\d{4}-\d{2}-\d{2}\.log\.tar\.bz2$/', $item);
        });

        if (!$keepIndex) {
            $logs = array_values($logs);
        }

        return $logs;
    }

    /**
     * Get all logs that follow the daily format
     *
     * @param      $logs
     * @param bool $keepIndex
     * @return mixed
     */
    public static function getLogs($logs, $keepIndex = false)
    {
        $logs = array_filter($logs, function ($item) {
            return (bool)preg_match('/^.*?\d{4}-\d{2}-\d{2}\.log$/', $item);
        });

        if (!$keepIndex) {
            $logs = array_values($logs);
        }

        return $logs;
    }

    /**
     * Map the full path to the filename.
     * /Users/mathiasgrimm/Dropbox/Mathias/projects/github/laravel-log-keeper-with-laravel/storage/logs/laravel-2016-03-11.log
     * will be mapped to laravel-2016-03-11.log
     *
     * @param $logs
     * @return mixed
     */
    public static function mapBasename($logs)
    {
        $logs = array_map(function ($item) {
            return basename($item);
        }, $logs);

        return $logs;
    }

    /**
     * Get a Carbon date from the file name
     *
     * @param $log
     * @return \Carbon\Carbon
     */
    public static function getDate($log)
    {
        if (preg_match('/(?<date>\d{4}-\d{2}-\d{2})\.log/', $log, $matches)) {
            return new Carbon($matches['date']);
        }

        throw new InvalidArgumentException('The provided log is not in the daily format');
    }

    /**
     * Get the diff in days for a given file and carbon date
     *
     * @param        $log
     * @param Carbon $date
     * @return int
     */
    public static function diffInDays($log, Carbon $date)
    {
        $logDate = static::getDate($log);
        $days = $logDate->diffInDays($date);

        return $days;
    }

}