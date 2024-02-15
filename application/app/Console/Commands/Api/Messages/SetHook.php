<?php

namespace App\Console\Commands\Api\Messages;

use Illuminate\Console\Command;

class SetHook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //записываем инфу о сообщении в бд
    }
}
