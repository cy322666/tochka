<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform\Order;
use App\Services\amoCRM\Models\Contacts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PlatformController extends Controller
{
    public function order(Request $request)
    {
        $order = Order::query()
            ->updateOrCreate(
                ['order_id' => $request->order_id],
                [
                    "name" => $request->user_first_name.' '.$request->user_last_name,
                    "phone" => Contacts::clearPhone($request->user_phone),
                    "email" => $request->user_email,
                    "positions" => $request->order_positions,
                    "status_order" => $request->order_status,
                    "cost_money" => $request->order_cost_money_value,
                    "left_cost_money" => $request->order_left_cost_money,
                    "payed_money" => $request->order_payed_money,

                    'utm_source' => $request->utm_source,
                    'utm_medium' => $request->utm_medium,
                    'utm_content' => $request->utm_content,
                    'utm_term' => $request->utm_term,
                    'utm_campaign' => $request->utm_campaign,

                    'body' => json_encode($request->toArray()),
                ]);

        Artisan::call('platform:send-order', ['order_id' => $order->id]);
    }
}
