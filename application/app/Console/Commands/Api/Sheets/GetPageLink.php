<?php

namespace App\Console\Commands\Api\Sheets;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetPageLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-page-link';

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
        Http::get('https://docs.google.com/spreadsheets/d/1VyBzO4-hoHmZN9yNKaVIUx4JpnbJdynNhw74imzamQ8/edit?gid=407630578#gid=407630578');
    }
}
