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
        GetHook::dispatch($request->all());
    }
}
