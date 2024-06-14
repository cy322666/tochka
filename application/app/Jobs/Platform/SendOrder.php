<?php

namespace App\Jobs\Platform;

use App\Models\Account;
use App\Models\Platform\Order;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Contacts;
use App\Services\amoCRM\Models\Leads;
use App\Services\amoCRM\Models\Notes;
use App\Services\amoCRM\Models\Tasks;
use App\Services\amoCRM\NoteHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 90;

    public function __construct(public \App\Models\Platform\Order $order) {

        $this->onQueue('platform-send-order');
    }

    public function tags(): array
    {
        return ['platform', 'send-order'];
    }

    public function handle(): void
    {
        $account = Account::query()
            ->where('subdomain', 'matematikandrei')
            ->first();

        $amoApi = (new Client($account))->init();

        if ($this->order->status) return;

        $contact = Contacts::search([
            'Телефон' => Contacts::clearPhone($this->order->phone),
            'Почта' => $this->order->email ?? null,
        ], $amoApi);

        if (!$contact) {
            $contact = Contacts::create($amoApi, $this->order->name);

            $contact = Contacts::update($contact, [
                'Почта' => $this->order->email,
                'Телефоны' => [$this->order->phone],
            ]);

        } else {
            //уже есть синхронизация по сделке, значит пришла рассрочка
            if ($this->order->lead_id) {

                $lead = $amoApi->service->leads()->find($this->order->lead_id);
                //если не получили ее по апи, то склеена или удалена
                if (!$lead) {

                    $this->order->lead_id = null;
                    $this->order->save();

                    //ишем активные сделки
                    $lead = Leads::searchActive($contact, $amoApi, [
                        Order::OP_PIPELINE_ID,
                        Order::SERVICE_PIPELINE_ID,
                        Order::KVAL_PIPELINE_ID,
                        Order::DOP_PIPELINE_ID,
                    ]);
                }
            }
        }

        //нет активной сделки и не рассрочка
        if (empty($lead) || !$lead) {
            //ищем успешные сделки в оп. в сервисе не ищем, тк вывод по оп однозначный
            // - если есть -> сервис
            // - если нет -> оп
            $lead = Leads::searchInStatus($contact, $amoApi, [
                Order::OP_PIPELINE_ID,
            ], 142);

            //если есть в оп успешная сделка
            if ($lead) {
                //значит повторник -> создание в сервисе в одном из 2 этапов
                //в любом, потому что склейка должна быть
                //тут мало условий для сервиса (инит или активный)
                //сделка там скорее всего будет уже в ур, поискать

                $lead = Leads::searchInStatus($contact, $amoApi, [
                    Order::SERVICE_PIPELINE_ID,
                ], 142);

                //обновляем сделку в ур сервисе
                if ($lead)
                    $lead = $this->order->updateLead($lead);
                else
                    $lead = $this->order->createLead($contact, $this->order->matchStatusBySuccess(), Order::SERVICE_PIPELINE_ID);
            //если нет, то просто оп
            } else
                $lead = $this->order->createLead($contact, $this->order->matchStatusNoSuccess(), Order::OP_PIPELINE_ID);//тут много условий возможно и не инит
        } else
            //есть активная обновляем ранее полученным статусом и данными
            $this->order->updateLead($lead, $this->order->matchStatusByStateActive($lead));

        Notes::addOne($lead, NoteHelper::createNoteOrder($this->order));

        $this->order->lead_id = $lead->id;
        $this->order->status_id = $lead->status_id;
        $this->order->pipeline_id = $lead->pipeline_id;
        $this->order->contact_id = $contact->id;
        $this->order->staff = $lead->cf('Оплата менеджера ОП')->getValue();
        $this->order->status = true;
        $this->order->save();
    }
}
