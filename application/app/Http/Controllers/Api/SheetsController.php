<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Sheets\Directories\Link;
use App\Models\Api\Sheets\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SheetsController extends Controller
{
    //new string table
    public function links(Request $request)
    {
        Link::query()->updateOrCreate([
            'link_id' => $request->link_id,
        ], [
            'name' => $request->name,
            'url' => $request->url
        ]);
    }

    //1 check count to albato
    public function check1(Request $request)
    {
        $transaction = Transaction::query()
            ->where(['lead_id', $request->lead_id])
            ->firstOr([], function () {
                throw new \Exception('Не найдена транзакция для сделки');
            });

        $transaction->count_1 = $request->count;
        $transaction->check_1 = true;
        $transaction->save();
    }

    //2 check count to albato -> cron
    public function check2(Request $request)
    {
        $transaction = Transaction::query()
            ->where(['lead_id', $request->lead_id])
            ->firstOr([], function () {
                throw new \Exception('Не найдена транзакция для сделки');
            });

        $transaction->count_2 = $request->count;
        $transaction->check_2 = $request->count !== null;
        $transaction->status  = $request->count !== null;
        $transaction->save();
    }

    //1. hook amocrm
    public function hook(Request $request)
    {
        if ($request->url == 'http://-') {

            return;
        }

        $transaction = Transaction::query()
            ->firstOrCreate(
                ['lead_id' => $request->lead_id],
                ['url'  => $request->url]
            );

        Artisan::call('app:check1-count', [
            'transaction' => $transaction
        ]);
    }
}
