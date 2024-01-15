<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dashboard stats route
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    Route::group(['prefix'=>'drivers'], function() {
        Route::get('/', [DriverController::class, 'index']);
        Route::post('/create', [DriverController::class, 'create']);
        Route::post('/create/bulk', [DriverController::class, 'uploadBulk']);
        
    });    
});

