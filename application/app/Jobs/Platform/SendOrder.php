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
            'Телефоны' => [Contacts::clearPhone($this->order->phone)],
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
                }
            }
        }

        //не рассрочка
        if (empty($lead) || !$lead) {
            //ишем активные сделки в рабочих воронках
            $lead = Leads::searchActive($contact, $amoApi, [
                Order::OP_PIPELINE_ID,
                Order::DOP_PIPELINE_ID,
                Order::SERVICE_PIPELINE_ID,
            ]);

            if ($lead) {
                //есть активная сделка, двигаем ее в ур или инициализацию
                $lead->status_id = $this->order->matchStatusByStateActive($lead);
                $lead->save();
            } else {
                //ищем успешные в рабочих воронках
                $lead = Leads::searchInStatus($contact, $amoApi, [
                    Order::OP_PIPELINE_ID,
                    Order::DOP_PIPELINE_ID,
                    Order::SERVICE_PIPELINE_ID,
                ], 142);

                if ($lead &&
                   ($lead->pipeline_id == Order::OP_PIPELINE_ID ||
                    $lead->pipeline_id == Order::DOP_PIPELINE_ID)) {
                        //если нашли, то это повторник -> сервис
                        $lead = Leads::searchInPipeline($contact, $amoApi, Order::SERVICE_PIPELINE_ID);
                        //ищем успешную для обновления или создаем там новую
                        if ($lead)
                            $lead = $this->order->updateLead($lead, $this->order->matchStatusBySuccess());
                        else
                            $lead = $this->order->createLead($contact, $this->order->matchStatusBySuccess(), Order::SERVICE_PIPELINE_ID);

                //нет успешной в продажных, смотрим в сервисе
                } elseif ($lead && $lead->pipeline_id == Order::SERVICE_PIPELINE_ID)
                    //есть успешная в сервисе
                    $lead = $this->order->updateLead($lead, $this->order->matchStatusBySuccess());
                else
                    $lead = $this->order->createLead($contact, $this->order->matchStatusNoSuccess(), Order::OP_PIPELINE_ID);
            }
        } elseif($lead->status_id != 142) {

            $lead->status_id = $this->order->matchStatusByStateActive($lead);
            $lead->save();
        }

        Notes::addOne($lead, NoteHelper::createNoteOrder($this->order));

        $lead = $amoApi->service->leads()->find($lead->id);

        $this->order->lead_id = $lead->id;
        $this->order->status_id = $lead->status_id;
        $this->order->pipeline_id = $lead->pipeline_id;
        $this->order->contact_id = $contact->id;
        $this->order->staff = $lead->cf('Оплата менеджера ОП')->getValue();
        $this->order->status = true;
        $this->order->save();
    }
}
