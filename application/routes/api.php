<?php

use App\Http\Controllers\Api\MessagesController;
use App\Http\Controllers\Api\OrdController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\SalesBotController;
use App\Http\Controllers\Api\SheetsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('ord/hook', [OrdController::class, 'hook']);

Route::post('ord/invoice', [OrdController::class, 'invoice']);

Route::post('msg/hook', [MessagesController::class, 'hook']);

Route::get('platform/orders', [PlatformController::class, 'order']);

Route::post('salesbot/filter/contects-hook', [SalesBotController::class, 'filterContecst']);

Route::post('sheets/directories/links', [SheetsController::class, 'links']);

Route::post('sheets/subscribes', [SheetsController::class, 'subscribes']);
