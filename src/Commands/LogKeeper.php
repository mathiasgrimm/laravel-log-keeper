<?php

namespace LifeOnScreen\LaravelLogKeeper\Commands;

use Exception;
use Illuminate\Console\Command;
use LifeOnScreen\LaravelLogKeeper\Factories\LogKeeperServiceFactory;
use Log;

class LogKeeper extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'laravel-log-keeper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload local logs, delete old logs both locally and remote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $service = LogKeeperServiceFactory::buildFromLaravelConfig();
            $service->getLogger();
            $service->work();
        } catch (Exception $e) {
            Log::critical("Something went wrong: {$e->getMessage()}");
        }
    }
}
