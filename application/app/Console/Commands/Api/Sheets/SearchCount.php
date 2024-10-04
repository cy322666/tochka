<?php

namespace App\Console\Commands\Api\Sheets;

use App\Models\Api\Sheets\Directories\Link;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:search-count {lead_id} {url}';

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
        $url = substr($this->argument('url'), 0, 22).'...';

        Log::debug(__METHOD__.' url : '.$url);

        $link = Link::query()
            ->where('url', $url)
            ->first();

        Log::info(__METHOD__.' searched : ', [
            'name' => $link->name,
            'lead_id' => $this->argument('lead_id'),
        ]);

        Http::post('https://h.albato.ru/wh/38/1lfh5q5/xTnl50b85iCsp9oFCfk9UL2N8iFZPTdZEEbAdNBGVeU/', [
            'name' => $link->name,
            'lead_id' => $this->argument('lead_id'),
        ]);
    }
}
