<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Sheets\Directories\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SheetsController extends Controller
{
    public function subscribes(Request $request)
    {
        Log::channel('sheets')->info(__METHOD__, $request->toArray());
    }

    public function links(Request $request)
    {
        Link::query()->updateOrCreate([
            'url' => $request->url
        ], [
            'name' => $request->name,
            'type' => $request->type,
            'link_id' => $request->link_id,
        ]);
    }

    public function hook(Request $request)
    {
        Artisan::call('app:search-count', [
            'lead_id' => $request->lead_id,
            'url'  => $request->url,
        ]);
    }
}
