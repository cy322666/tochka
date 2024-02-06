<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Api\SalesBot\GetHook;
use App\Models\Api\SalesBot\FilterContecst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SalesBotController extends Controller
{
    public function filterContecst(Request $request)
    {
        $hook = FilterContecst::query()->create([
            'list_id' => $request->list,
            'lead_id' => $request->amo_lead_id,
            'client_id'  => $request->client_id,
            'contact_id' => $request->amo_client_id,
        ]);

        GetHook::dispatch($hook);
    }
}
