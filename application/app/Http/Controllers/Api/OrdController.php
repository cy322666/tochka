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

        Artisan::call('ord:create-person',   ['transaction' => $transaction->id]);
        Artisan::call('ord:create-contract', ['transaction' => $transaction->id]);
        Artisan::call('ord:create-pad',      ['transaction' => $transaction->id]);
        Artisan::call('ord:create-creative', ['transaction' => $transaction->id]);
    }
}
