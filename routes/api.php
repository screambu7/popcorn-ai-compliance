<?php

use App\Http\Controllers\Api\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// n8n Webhooks (protegidos por Sanctum token)
Route::middleware('auth:sanctum')->prefix('webhooks')->group(function () {
    Route::post('/kyc', [WebhookController::class, 'kyc']);
});
