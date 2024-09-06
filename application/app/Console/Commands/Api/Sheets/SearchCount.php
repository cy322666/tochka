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

        $link = Link::query()
            ->where('url', $url)
            ->first();

        Http::post('https://h.albato.ru/wh/38/1lfh5q5/ymqv4-g3P2kzL58uu4SONJUsKo5jX-yuD5GHv5PPYCo/', [
            'name' => $link->name,
            'lead_id' => $this->argument('lead_id'),
        ]);
    }
}
