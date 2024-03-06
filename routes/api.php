<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

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
// auth routes
Route::post('/login', [UserController::class, 'login'])->name('loginApi');
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function(){
    // logout
    Route::post('/logout', [UserController::class, 'logout']);

    // Dashboard Routes
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    // Drivers Routes
    Route::group(['prefix'=>'drivers'], function() {
        // for mobile
        Route::post('/save-location', [DriverController::class, 'savelocation']);

        Route::get('/', [DriverController::class, 'index']);
        Route::post('/create', [DriverController::class, 'create']);
        Route::post('/create/bulk', [DriverController::class, 'uploadBulk']);     
        Route::get('/{id}', [DriverController::class, 'show']);
        Route::post('/{id}/update', [DriverController::class, 'update']);
        Route::post('/{id}/updateGeneralInformation', [DriverController::class, 'updateGeneralInformation']);
        Route::post('/{id}/updateDocuments', [DriverController::class, 'updateDocuments']);
        Route::post('/{id}/createTrucks', [DriverController::class, 'createTrucks']);
        Route::post('/{id}/updateTrucks/{vehicleId}', [DriverController::class, 'updateTrucks']);
        Route::get('/{id}/getVehicles', [DriverController::class, 'getVehicles']);
        Route::get('/{id}/getVehicles/{vehicleId}', [DriverController::class, 'showVehicle']);
        Route::post('/{id}/updateReminders', [DriverController::class, 'updateReminders']);
        Route::post('/updateDriverStatus/{driver}', [DriverController::class, 'updateStatus']);
        Route::post('/searchDrivers', [DriverController::class, 'searchDrivers']);
    });
    
    // Loads Routes
    Route::group(['prefix'=>'loads'], function() {
        Route::get('/', [LoadController::class, 'index']);
        Route::post('/', [LoadController::class, 'store']);
        Route::post('/filter-loads', [LoadController::class, 'filterLoads']);
    });    

    //mobile routes

    // Dashboard mobile app Routes
    Route::get('/dashboard-mobile', [DashboardController::class, 'dashboardMobile']);

    // orders routes for mobile app
    Route::group(['prefix'=>'orders'], function() {
        Route::get('/new-orders', [LoadController::class, 'newOrders']);
        Route::get('/active-orders', [LoadController::class, 'activeOrders']);
        Route::post('/accept-order/{orderId}', [LoadController::class, 'acceptOrder']);
    }); 

    Route::group(['prefix'=>'profile'], function() {
        Route::post('/update-driver-profile', [UserController::class, 'updateDriverProfile']);
        Route::post('/update-driver-image', [UserController::class, 'updateDriverImage']);
        Route::post('/update-driver-password', [UserController::class, 'updateDriverPassword']);
    }); 
    
});

Route::get('/images/{path}', [ImageController::class, 'show'])->where('path', '.*');

Route::post('/messages', [ChatController::class, 'store']);