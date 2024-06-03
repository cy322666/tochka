<?php

namespace App\Services\amoCRM;

abstract class NoteHelper
{
    public static function createNoteOrder($order): string
    {
        $text = [
            'Информация по заказу клиента в GetCourse',
            'ID заказа: '.$order->order_id,
            'Состав заказа: '.$order->positions,
            'Стоимость заказа: '.$order->cost_money. ' руб.',
            'Оплачено: '.$order->payed_money. ' руб.',
            'Осталось оплатить: '.$order->left_cost_money. ' руб.',
            'Статус заказа: '.$order->status_order,
            '',
            'Информация по клиенту',
            'Имя: '.$order->name,
            'Телефон: '.$order->phone,
            'Email: '.$order->email,
        ];

        return implode("\n", $text);
    }
}
