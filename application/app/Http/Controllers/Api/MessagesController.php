<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Api\Messages\Message;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function hook(Request $request)
    {
        Message::query()->create($request->toArray());
    }
}
