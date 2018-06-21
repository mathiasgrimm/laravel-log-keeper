<?php

namespace LifeOnScreen\LaravelLogKeeper\Repos;

interface LogsRepoInterface
{
    public function getLogs();

    public function getCompressed();

    public function delete($log);

    public function compress($log, $compressedName);

    public function get($log);

    public function put($log, $content);
}