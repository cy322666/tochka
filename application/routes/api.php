<?php

use App\Http\Controllers\Api\MessagesController;
use App\Http\Controllers\Api\OrdController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\SalesBotController;
use App\Http\Controllers\Api\SheetsController;
use App\Http\Controllers\Api\SiteController;
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

Route::get('platform/orders', [PlatformController::class, 'order']);

Route::post('sheets/directories/links', [SheetsController::class, 'links']);//добавение ссылки в таблицу ссылок

Route::post('sheets/hook', [SheetsController::class, 'hook']);//хук от альбато с ссылкой

Route::post('sheets/check1', [SheetsController::class, 'check1']);//хук от альбато с данными первой проверки

Route::post('sheets/check2', [SheetsController::class, 'check2']);//хук от альбато с данными второй проверки

Route::post('sheets/subscribes', [SheetsController::class, 'subscribes']);//подписчики

Route::post('site/consultations', [SiteController::class, 'consultations']);
