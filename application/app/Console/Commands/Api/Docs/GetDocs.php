<?php

namespace App\Console\Commands\Api\Docs;

use App\Models\Account;
use App\Models\Docs\Doc;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GetDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-docs';

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
        $account = Account::query()
            ->where('subdomain', 'tochkaznanij')
            ->first();

//        $amoApi = (new Client($account))->init();

            $files = Http::withHeaders(['Authorization' => 'Bearer ' . $account->access_token])
                ->get('https://drive-b.amocrm.ru/v1.0/files?filter[term]=акт&limit=200&offset=0')
                ->object();

            foreach ($files->_embedded->files as $file) {

                $path = $file->sanitized_name.'.'.$file->metadata->extension;

                try {

                    if (Doc::query()
                        ->where('uuid', $file->uuid)
                        ->exists())

                        continue;

                    Storage::put($path, file_get_contents($file->_links->download->href));

                    Doc::query()->create([
                        'uuid' => $file->uuid,
                        'doc_id' => $file->id,
                        'name' => $file->name,
                        'path' => $path,
                        'metadata' => json_encode($file->metadata),
                        'type' => $file->metadata->extension,
                        'href' => $file->_links->download->href,
                        'created_at_doc' => Carbon::parse($file->created_at)->format('Y-m-d H:i:s'),
                    ]);

                } catch (\Throwable $e) {

                    dump($e->getMessage());
                }
//                    'request_at' => $file->,
//                    'lead_id' => $file->,
//                    'contact_id' => $file->,
//                    'company_id' => $file->,
        }
    }
}
