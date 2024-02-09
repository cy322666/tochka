<?php

namespace App\Console\Commands\Api\Ord;

use App\Models\Account;
use App\Services\amoCRM\Client;
use App\Services\Ord\OrdService;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

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
        $transaction = $this->argument('transaction');

        $amoApi = (new Client(Account::query()->first()))->init();
        $ordApi = new OrdService();

        $lead     = $amoApi->service->leads()->find(44622459);
        $creative = $ordApi->creative();

        $creative->uuid  = Uuid::uuid4();
        $creative->contract_external_id = '11724b84-2ac2-4f09-9494-f0316a5313de';
        $creative->name = 'hz';
        $creative->brand = 'ООО "Точка знаний"';
        $creative->category = 'категория';
        $creative->description = 'description';//$lead->cf('Креатив')->getValue();
        $creative->pay_type = 'cpc';
        /*
         * cpa — Cost Per Action, цена за действие.
cpc — Cost Per Click, цена за клик.
cpm — Cost Per Millennium, цена за 1 000 показов.
other
         */
        $creative->form = 'text_block';
        /*
         * banner — баннер.
text_block — текстовый блок.
text_graphic_block — текстово-графический блок.
audio — аудиозапись.
video — видеоролик.
live_audio — аудиотрансляция в прямом эфире.
live_video — видеотрансляция в прямом эфире.
other — иное.
         */
        $creative->targeting = 'Весь интернет';
//        $creative->target_urls = 'https://product?article=3085223-childrens';
        $creative->target_urls = ['https://google.com'];
        $creative->texts = ['textextextextext'];
        $creative->media_external_ids = ['https://google.com'];
        $creative->media_urls = ['https://google.com'];

        $result = $creative->create();

        $transaction->erid   = $result->erid;
        $transaction->marker = $result->marker;
        $transaction->save();
//        $creative->flags = [];
    }
}
