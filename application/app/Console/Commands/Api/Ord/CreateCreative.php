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

        $ordApi = new OrdService();
        $amoApi = (new Client($account))->init();

        $contact = $amoApi->service->contacts()->find($transaction->contact_id);
        $lead    = $amoApi->service->leads()->find($transaction->lead_id);

        $creativeName = Carbon::now()->format('m.d').'_'.$contact->cf('Ник блогера')->getValue().'_'.$lead->cf('Шаблон креатива')->getValue();

        $file = Leads::getFileByType($amoApi, $account, $lead, ['png']);

        $fileName = $lead->id.'_'.Carbon::now()->format('Y-m-d H:i:s').'.png';

        Storage::put($fileName, $file);

        $media = $ordApi->media();
        $media->uuid  = Uuid::uuid4();
        $media->description = 'opisanie';
        $media->media_file = storage_path('app/'.$fileName);

        $result = $media->create();

        dd($result);

        $creative = $ordApi->creative();
        $creative->uuid  = Uuid::uuid4();
        $creative->contract_external_id = $transaction->contract_uuid;
        $creative->name = $creativeName;
        $creative->brand = 'ООО "Точка знаний"';
        $creative->pay_type = 'cpm';
        $creative->form = $lead->cf('Форма креатива')->getValue();
        $creative->target_urls = [$lead->cf('Аккаунт')->getValue()];
        $creative->texts = [$lead->cf('Текст креатива')->getValue()];
        $creative->media_external_ids = [Uuid::uuid4()];//$lead->cf('Ссылка на медиа')->getValue()
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
        } else
            dd(__METHOD__.' : '.$result->error);

        //TODO erid + market to lead
    }
}
