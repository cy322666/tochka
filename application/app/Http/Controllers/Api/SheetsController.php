<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SheetsController extends Controller
{
    public function subscribes(Request $request)
    {
        Log::channel('sheets')->info(__METHOD__, $request->toArray());
    }

    public function links()
    {
        Log::channel('sheets')->info(__METHOD__, $request->toArray());
    }
}
