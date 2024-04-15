<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LoadController;
use App\Http\Controllers\TruckController;
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
Route::post('/web-login', [UserController::class, 'webLogin']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
Route::post('/user-registration', [UserController::class, 'registration']);
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::post('reset-password-mobile', [UserController::class, 'resetPasswordMobile']);
Route::post('code-verification', [UserController::class, 'codeVerification']);
Route::post('forgot-password-mobile', [UserController::class, 'forgotPasswordMobile']);

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
        Route::post('/update-truck-detail', [DriverController::class, 'updateDriverTruckDetail']);
        Route::post('/search-driver-locations', [DriverController::class, 'searchDriverLocations']);
        Route::post('/{id}/block', [DriverController::class, 'blockDriver']);
    });
    
    // Loads Routes
    Route::group(['prefix'=>'loads'], function() {
        Route::get('/', [LoadController::class, 'index']);
        Route::post('/', [LoadController::class, 'store']);
        Route::post('/filter-loads', [LoadController::class, 'filterLoads']);
        Route::get('/get-on-going-loads', [LoadController::class, 'getOnGoingLoads']);
        Route::get('/{id}', [LoadController::class, 'show']);
    });    

    //mobile routes

    // Dashboard mobile app Routes
    Route::get('/dashboard-mobile', [DashboardController::class, 'dashboardMobile']);

    // get driver statuses
    Route::get('/get-driver-statuses', [DriverController::class, 'getDriverStatus']);
    Route::get('/get-load-statuses', [LoadController::class, 'getStatuses']);
    Route::get('/get-driver-dropdown', [DriverController::class, 'getDriverDropdown']);

    // orders routes for mobile app
    Route::group(['prefix'=>'orders'], function() {
        Route::get('/new-orders', [LoadController::class, 'newOrders']);
        Route::get('/active-orders', [LoadController::class, 'activeOrders']);
        Route::post('/accept-order/{orderId}', [LoadController::class, 'acceptOrder']);
        Route::post('/start-trip/{orderId}', [LoadController::class, 'startTrip']);
        Route::post('/send-track-location/{orderId}', [LoadController::class, 'sendTrackLocation']);
        Route::post('/order-delivered/{orderId}', [LoadController::class, 'orderDelivered']);
    }); 

    Route::group(['prefix'=>'profile'], function() {
        Route::post('/update-driver-profile', [UserController::class, 'updateDriverProfile']);
        Route::post('/update-driver-image', [UserController::class, 'updateDriverImage']);
        Route::post('/update-driver-password', [UserController::class, 'updateDriverPassword']);
    }); 

    Route::group(['prefix'=>'truck'], function() {
        Route::get('/get-truck-types', [TruckController::class, 'getTruckTypes']);
        Route::get('/get-truck-types-for-find-truck', [TruckController::class, 'getTruckTypesForFindTruck']);
    }); 
    
});

Route::get('/images/{path}', [ImageController::class, 'show'])->where('path', '.*');

Route::post('/messages', [ChatController::class, 'store']);