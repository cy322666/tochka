<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Ord\Person;
use App\Models\Api\Ord\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class OrdController extends Controller
{
    public function hook(Request $request)
    {
        $transaction = Transaction::query()->create([
            'lead_id' => $request->leads['status'][0]['id'],
        ]);

        $result1 = Artisan::call('ord:create-person',   ['transaction' => $transaction->id]);

        if ($result1)
            $result2 = Artisan::call('ord:create-contract', ['transaction' => $transaction->id]);
        if ($result2)
            $result3 = Artisan::call('ord:create-pad',      ['transaction' => $transaction->id]);
        if ($result3)
            $result4 = Artisan::call('ord:create-creative', ['transaction' => $transaction->id]);
    }

    public function invoice(Request $request)
    {
        $transaction = Transaction::query()
            ->where('lead_id',  $request->leads['status'][0]['id'])
            ->first();

        Artisan::call('ord:create-invoice',  [
            'transaction' => $transaction->id ?: null,
            'lead_id' => $request->leads['status'][0]['id'],
        ]);
    }
}
