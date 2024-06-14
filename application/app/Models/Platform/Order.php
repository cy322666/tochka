<?php

namespace App\Models\Platform;

use App\Services\amoCRM\Models\Leads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
    ];

    const
        OP_PIPELINE_ID = 3738346,
        SERVICE_PIPELINE_ID = 4870069,
        KVAL_PIPELINE_ID = 6552278,
        DOP_PIPELINE_ID = 5674156,

        INIT_OP_STATUS_ID = 50954683,
        INIT_SERVICE_STATUS_ID = 50885656,
        INIT_DOP_STATUS_ID = 49931782,

        PAY_OP_STATUS_ID = 47314576,
        PAY_SERVICE_STATUS_ID = 50886724,
        PAY_DOP_STATUS_ID = 142;

    //чекаем куда отправлять активную сделку
    public function matchStatusByStateActive($lead)
    {
        Log::debug(__METHOD__.' есть активная');

        if ($this->status_order == 'Новый')

            return match ($lead->pipeline_id) {
                //активная в оп и новый заказ -> инициализация
                //активная в квале и новый заказ -> сделка в оп, склеется виджетом
                self::OP_PIPELINE_ID, self::KVAL_PIPELINE_ID => self::INIT_OP_STATUS_ID,
                //активная в сервисе и новый заказ -> инициализация
                self::SERVICE_PIPELINE_ID => self::INIT_SERVICE_STATUS_ID,
                //активная в допродажах и новый заказ -> счет выставлен
                self::DOP_PIPELINE_ID => self::INIT_DOP_STATUS_ID,
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
                //активная в допродажах и оплаченный заказ -> ур
                self::DOP_PIPELINE_ID => self::PAY_DOP_STATUS_ID,
            };

        if ($this->status_order == 'Отменен')

            return match ($lead->pipeline_id) {
                //активная и отмена -> закрываем в нереализ
                self::OP_PIPELINE_ID, self::KVAL_PIPELINE_ID, self::SERVICE_PIPELINE_ID, self::DOP_PIPELINE_ID => 143
            };
    }

    //какой этап у новой сделки если есть успешная
    public function matchStatusBySuccess()
    {
        Log::debug(__METHOD__.' есть успешная/ые');

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
        Log::debug(__METHOD__.' нет активных создание в ОП');

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
        Log::debug(__METHOD__.' создание сделки');

        $lead = Leads::createPrepare($contact, [
            'responsible_user_id' => 5998951,//TODO
            'status_id' => $statusId,
            'pipeline_id' => $pipelineId,
            'sale' => $this->cost_money,
        ], $this->name);

        $lead->cf('GetCourse. Номер заказа')->setValue($this->order_id);
        $lead->cf('GetCourse. Оплачено')->setValue($this->payed_money);
        $lead->cf('GetCourse. Состав заказа')->setValue($this->positions);
        $lead->cf('GetCourse. Осталось оплатить')->setValue($this->left_cost_money);
        $lead->cf('GetCourse. Статус заказа')->setValue($this->status_order);

        $lead->sale = $this->cost_money;

        $lead->attachTags(['НоваяИнтеграция', 'Заказ']);

        $lead->save();

        return $lead;
    }

    public function updateLead($lead, $statusId = null)
    {
        Log::debug(__METHOD__.' Обновление сделки');

        $lead->cf('GetCourse. Номер заказа')->setValue($this->order_id);
        $lead->cf('GetCourse. Оплачено')->setValue($this->payed_money);
        $lead->cf('GetCourse. Состав заказа')->setValue($this->positions);
        $lead->cf('GetCourse. Осталось оплатить')->setValue($this->left_cost_money);
        $lead->cf('GetCourse. Статус заказа')->setValue($this->status_order);

        $lead->sale = $this->cost_money;

        $lead->attachTags(['НоваяИнтеграция', 'Заказ']);

        if ($statusId)
            $lead->status_id = $statusId;

        $lead->save();

        return $lead;
    }
}
