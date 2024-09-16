<?php

namespace App\Models\Platform;

use App\Services\amoCRM\Models\Leads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Ufee\Amo\Models\Contact;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'email',
        'name',
        'order_id',
        'positions',
        'left_cost_money',
        'cost_money',
        'payed_money',
        'payment_link',
        'status_order',
        'status',

        'lead_id',
        'contact_id',
        'pipeline_id',
        'status_id',
        'staff',

        'utm_source',
        'utm_medium',
        'utm_content',
        'utm_term',
        'utm_campaign',

        'body',
        'is_first',
        'is_payed',
    ];

    const
        OP_PIPELINE_ID = 3738346,
        SERVICE_PIPELINE_ID = 4870069,
        KVAL_PIPELINE_ID = 6552278,
        DOP_PIPELINE_ID = 5674156,
        EXT_PIPELINE_ID = 8616182,

        INIT_OP_STATUS_ID = 50954683,
        INIT_SERVICE_STATUS_ID = 50885656,
        INIT_DOP_STATUS_ID = 49931782,
        INIT_EXT_STATUS_ID = 69891894,

        PAY_OP_STATUS_ID = 47314576,
        PAY_SERVICE_STATUS_ID = 50886724,
        PAY_DOP_STATUS_ID = 142,
        PAY_EXT_STATUS_ID = 142;

    //чекаем куда отправлять активную сделку
    public function matchStatusByStateActive($lead)
    {
        Log::info(__METHOD__.' '.$this->id.' есть активная');

        if ($this->status_order == 'Новый')

            return match ($lead->pipeline_id) {
                //активная в оп и новый заказ -> инициализация
                //активная в квале и новый заказ -> сделка в оп, склеется виджетом
                self::OP_PIPELINE_ID, self::KVAL_PIPELINE_ID => self::INIT_OP_STATUS_ID,
                //активная в сервисе и новый заказ -> инициализация
                self::SERVICE_PIPELINE_ID => self::INIT_SERVICE_STATUS_ID,
                //активная в допродажах и новый заказ -> оставляем как есть
                self::DOP_PIPELINE_ID => $lead->pipeline_id,
                self::EXT_PIPELINE_ID => $lead->pipeline_id,
            };

        if ($this->status_order == 'Оплачен' ||
            $this->status_order == 'Частично оплачен' ||
            $this->status_order == 'Завершен')

            return match ($lead->pipeline_id) {
                //активная в оп и оплаченный заказ -> инициализация
                //активная в квале и оплаченный заказ -> сделка в оп, склеется виджетом
                self::OP_PIPELINE_ID, self::KVAL_PIPELINE_ID => self::PAY_OP_STATUS_ID,
                //активная в сервисе и оплаченный заказ -> инициализация
                self::SERVICE_PIPELINE_ID => self::PAY_SERVICE_STATUS_ID,
                //активная в допродажах и оплаченный заказ -> оставляем как есть
                self::DOP_PIPELINE_ID => $lead->pipeline_id,
                self::EXT_PIPELINE_ID => $lead->pipeline_id,
            };

        if ($this->status_order == 'Отменен')

            return match ($lead->pipeline_id) {
                //активная и отмена -> закрываем в нереализ
                self::OP_PIPELINE_ID, self::KVAL_PIPELINE_ID, self::SERVICE_PIPELINE_ID => 143,

                self::DOP_PIPELINE_ID => $lead->pipeline_id,
                self::EXT_PIPELINE_ID => $lead->pipeline_id,
            };
    }

    //какой этап у новой сделки если есть успешная
    public function matchStatusBySuccess()
    {
        Log::info(__METHOD__.' '.$this->id.' есть успешная/ые');

        if ($this->status_order == 'Новый')

            return self::INIT_SERVICE_STATUS_ID;

        if ($this->status_order == 'Оплачен' ||
            $this->status_order == 'Частично оплачен' ||
            $this->status_order == 'Завершен')

            return self::PAY_SERVICE_STATUS_ID;

        if ($this->status_order == 'Отменен')

            return 143;
    }

    public function matchStatusNoSuccess()
    {
        Log::info(__METHOD__.' '.$this->id.' нет активных создание в ОП');

        if ($this->status_order == 'Новый')

            return self::INIT_OP_STATUS_ID;

        if ($this->status_order == 'Оплачен' ||
            $this->status_order == 'Частично оплачен' ||
            $this->status_order == 'Завершен')

            return self::PAY_OP_STATUS_ID;

        if ($this->status_order == 'Отменен')

            return 143;
    }

    public function createLead($contact, int $statusId, int $pipelineId)
    {
        Log::info(__METHOD__.' '.$this->id.' создание сделки');

        $lead = Leads::createPrepare($contact, [
            'responsible_user_id' => 5998951,//TODO
            'status_id' => $statusId,
            'pipeline_id' => $pipelineId,
            'sale' => $this->cost_money,
        ], $this->name);

        try {
            $lead->cf('GetCourse. Номер заказа')->setValue($this->order_id);
            $lead->cf('GetCourse. Оплачено')->setValue($this->payed_money);
            $lead->cf('GetCourse. Состав заказа')->setValue($this->positions);
            $lead->cf('GetCourse. Осталось оплатить')->setValue($this->left_cost_money);
            $lead->cf('GetCourse. Статус заказа')->setValue($this->status_order);

        } catch (\Throwable $e) {
            Log::alert(__METHOD__.' '.$this->id, [$e->getMessage()]);
        }

        $lead->sale = $this->cost_money;

        $lead->attachTags(['НоваяИнтеграция', 'Заказ']);

        $lead->save();

        return $lead;
    }

    public function updateLead($lead, $statusId = null)
    {
        Log::info(__METHOD__.' '.$this->id.' Обновление сделки');

        try {
            $lead->cf('GetCourse. Номер заказа')->setValue($this->order_id);
            $lead->cf('GetCourse. Оплачено')->setValue($this->payed_money);
            $lead->cf('GetCourse. Состав заказа')->setValue($this->positions);
            $lead->cf('GetCourse. Осталось оплатить')->setValue($this->left_cost_money);
            $lead->cf('GetCourse. Статус заказа')->setValue($this->status_order);
        } catch (\Throwable $e) {
            Log::alert(__METHOD__, [$e->getMessage()]);
        }

        $lead->sale = $this->cost_money;

        $lead->attachTags(['НоваяИнтеграция', 'Заказ']);

        if ($statusId)
            $lead->status_id = $statusId;

        $lead->save();

        return $lead;
    }

    public static function isFirst(Contact $contact, $amoApi) : bool
    {
        if (Leads::searchInPipeline($contact, $amoApi, Order::SERVICE_PIPELINE_ID) ||
            Leads::searchInPipeline($contact, $amoApi, Order::DOP_PIPELINE_ID) ||
            Leads::searchInPipeline($contact, $amoApi, Order::EXT_PIPELINE_ID))

            return false;
        else
            return true;
    }
}
