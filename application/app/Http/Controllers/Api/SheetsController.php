<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Sheets\Directories\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SheetsController extends Controller
{
    public function subscribes(Request $request)
    {
        Log::channel('sheets')->info(__METHOD__, $request->toArray());
    }

    public function links(Request $request)
    {
        Log::channel('sheets')->info(__METHOD__, $request->toArray());

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
        Log::channel('sheets')->info(__METHOD__.' : '.$request->lead_id);

        $lastLeadId = Cache::get('last_lead_id');

        if ($lastLeadId !== $request->lead_id) {

            Cache::set('last_lead_id', $request->lead_id);

            Artisan::call('app:search-count', [
                'lead_id' => $request->lead_id,
                'url'  => $request->url,
            ]);
        }
    }
}
