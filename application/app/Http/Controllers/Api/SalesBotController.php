<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Api\SalesBot\GetHook;
use Illuminate\Http\Request;

class SalesBotController extends Controller
{
    public function filterContecst(Request $request)
    {
        GetHook::dispatch($request->all());
    }
}
