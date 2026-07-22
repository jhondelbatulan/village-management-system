<?php

use App\Http\Controllers\GisController;
use App\Models\Household;
use App\Models\PaymentType;
use Illuminate\Support\Facades\Route;

Route::get('/villages/{villageId}/households', function (int $villageId) {
    return Household::where('village_id', $villageId)
        ->where('status', 'active')
        ->where('approval_status', 'approved')
        ->orderBy('block_number')
        ->orderBy('lot_number')
        ->get(['id', 'household_number', 'household_head_name', 'block_number', 'lot_number']);
});

Route::get('/payment-config/{villageId}/payment-types', function (int $villageId) {
    return PaymentType::where('village_id', $villageId)
        ->where('is_active', true)
        ->get(['id', 'payment_name', 'default_amount', 'frequency']);
});

Route::get('/gis/search', [GisController::class, 'search']);
Route::get('/gis/filter', [GisController::class, 'filter']);
Route::get('/gis/village/{villageId}', [GisController::class, 'villageApi']);
Route::get('/gis/stats', [GisController::class, 'statsApi']);
Route::get('/gis/villages', [GisController::class, 'villagesApi']);
Route::get('/gis/village/{villageId}/dashboard-data', [GisController::class, 'dashboardDataApi']);
Route::get('/gis/system-overview', [GisController::class, 'systemOverviewApi']);


