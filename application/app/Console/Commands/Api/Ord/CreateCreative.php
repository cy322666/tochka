<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Leads;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;

class CreateCreative extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ord:create-creative {transaction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle()
    {
        $transaction = Transaction::query()->find($this->argument('transaction'));

        $account = Account::query()
            ->where('subdomain', 'tochkaznanij')
            ->first();

        $ordApi = new OrdService(env('APP_ENV'));
        $amoApi = (new Client($account))->init();

        $contact = $amoApi->service->contacts()->find($transaction->contact_id);
        $lead    = $amoApi->service->leads()->find($transaction->lead_id);

        $creativeName = Carbon::now()->format('m.d').'_'.$contact->cf('Ник блогера')->getValue().'_'.$lead->cf('Шаблон креатива')->getValue();

        $file = Leads::getFileByType($amoApi, $account, $lead, ['jpg']);

        $fileName = $lead->id.'_'.Carbon::now()->format('Y-m-d H:i:s').'.png';

        Storage::put($fileName, $file);

        $media = $ordApi->media();
        $media->uuid  = Uuid::uuid4();
        $media->description = 'opisanie';//TODO
        $media->file_name = $fileName;
        $media->media_file = Storage::get($fileName);

        $result = $media->create();

        $transaction->media_sha = json_decode($result)->sha256;
        $transaction->save();

        $creative = $ordApi->creative();
        $creative->uuid  = Uuid::uuid4();
        $creative->contract_external_id = $transaction->contract_uuid;
        $creative->name = $creativeName;
        $creative->brand = 'ООО "Точка знаний"';
        $creative->pay_type = 'cpm';
        $creative->form = $lead->cf('Форма креатива')->getValue() ?? 'text_graphic_block';
        $creative->target_urls = [$lead->cf('Аккаунт')->getValue()  ?? '-'];
        $creative->texts = [$lead->cf('Текст креатива')->getValue()  ?? '-'];
        $creative->media_external_ids = [$media->uuid];//$lead->cf('Ссылка на медиа')->getValue()
        $creative->media_urls = [];

        $result = $creative->create();

        if (empty($result->error)) {

            $transaction->erid   = $result->erid;
            $transaction->marker = $result->marker;
            $transaction->creative_uuid = $creative->uuid;
            $transaction->status = true;
            $transaction->media = json_encode([
                'uuid' => $creative->uuid,
                'media_urls' => $creative->media_urls
            ]);
            $transaction->save();
        }
        //TODO erid + market to lead
    }
}
