<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Models\Api\Ord\Text;
use App\Models\Api\Ord\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\Ord\OrdService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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

        $partName = $contact->cf('Ник блогера')->getValue() ? $contact->cf('Ник блогера')->getValue() : $contact->cf('Название канала')->getValue();
        $partName = $lead->cf('Аккаунт')->getValue() ? $partName.' '.$lead->cf('Аккаунт')->getValue() : '';

        $creativeName = Carbon::parse($lead->cf('Дата рекламы план')->getValue())->format('d.m.Y').'_'.$partName;

        $template = Text::query()
            ->where('key', $lead->cf('Шаблон креатива')->getValue())
            ->first();

        if (!$template)
            Notes::addOne($lead, 'Такой шаблон не найден');

        $format = explode('.', $template->media)[1];

        $file = Storage::drive('public')->get($template->media);

        if (empty($file)) {

            Notes::addOne($lead, 'Креатив не загружен, нет медиа в сделке');

            exit;
        }

        try {

            $fileName = $lead->id.'_'.Carbon::now()->format('Y-m-d H:i:s').'.'.$format;

            Storage::put($fileName, $file);

            $media = $ordApi->media();
            $media->uuid  = Uuid::uuid4();
            $media->description = $fileName;
            $media->file_name = $fileName;
            $media->media_file = Storage::get($fileName);

            $result = $media->create();

            Log::debug(__METHOD__.' media response : '.$result);

            $transaction->media_sha = json_decode($result)->sha256;
            $transaction->media = $media->uuid;
            $transaction->save();

            Notes::addOne($lead, 'Успешная загрузка медиа : '.$transaction->media);

        } catch (\Throwable $e) {

            Notes::addOne($lead, 'Произошла ошибка при загрузке медиа : '.$e->getFile().' '.$e->getLine().' '.$e->getMessage());

            exit;
        }

        try {
            $creative = $ordApi->creative();
            $creative->uuid  = Uuid::uuid4();
            $creative->contract_external_id = $transaction->contract_uuid;
            $creative->name = $creativeName;
            $creative->brand = 'ООО "Точка знаний"';
            $creative->pay_type = 'cpm';
            $creative->form = $lead->cf('Форма креатива')->getValue() ?: 'text_graphic_block';
            $creative->url = $lead->cf('Уникальная ссылка')->getValue() ?: '-';
            $creative->texts = [Text::query()->where('key', $lead->cf('Шаблон креатива')->getValue())->first()->text];
            $creative->media_external_ids = [$media->uuid];

            $result = $creative->create();

            Log::debug(__METHOD__.' creative response : ', [$result]);

            if (empty($result->error) && $result) {

                $transaction->erid   = $result->erid;
                $transaction->marker = $result->marker;
                $transaction->creative_uuid = $creative->uuid;
                $transaction->save();

                Notes::addOne($lead, implode("\n", [
                    ' Успешное создание креатива : ',
                    ' erid : '.$transaction->erid,
                    ' market : '.$transaction->marker,
                ]));

                $lead->cf('Токен')->setValue($transaction->erid);
                $lead->save();

            } else {

                Notes::addOne($lead, 'Произошла ошибка при создании креатива');

                Log::error(__METHOD__, $result ? [$result->error] : ['Неизвестная ошибка']);
            }
        } catch (\Throwable $e) {

            Notes::addOne($lead, 'Произошла ошибка при создании креатива : '.$e->getFile().' '.$e->getLine().' '.$e->getMessage());
        }
    }
}
