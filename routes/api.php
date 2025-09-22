<?php

use App\Http\Controllers\PurchaseOrderController;
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

// Authentication Routes...
// Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store']);
// Route::post('/reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store']);

Route::post('/login', 'AuthController@login');

  

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('/user', 'AuthController@getAuthUser');
    Route::get('/users/personal_information', 'AuthController@getAuthUserPersonalInformations');
    Route::get('/users/notification_settings', 'AuthController@getAuthUserNotificationSettings');
    Route::put('/users/profile_settings', 'AuthController@updateProfileSettings');
    Route::put('/users/notification_settings', 'AuthController@updateNotificationSettings');
    Route::post('/users/profile_picture_update', 'AuthController@updateProfilePicture');
    Route::delete('/users/profile_picture_remove', 'AuthController@deleteProfilePicture');
    Route::post('/logout', 'AuthController@logout');
    
    // completed works 
      Route::get('/parameter-report/{page}', 'ParameterReportController@getReportParameters');
      Route::post('/parameter-report/{page}', 'ParameterReportController@reportGenerator');
       Route::apiResource('countries', 'CountryController');

       Route::apiResource('states', 'StateController');
        Route::delete('/states/delete/multiple','StateController@deleteMultiple');

       Route::apiResource('districts', 'DistrictController');
        Route::delete('/districts/delete/multiple','DistrictController@deleteMultiple');

       Route::apiResource('addresses', 'AddressController');
       Route::delete('/addresses/delete/multiple','AddressController@deleteMultiple');

       Route::apiResource('permissions', 'PermissionController');
         Route::delete('/permissions/delete/multiple','PermissionController@deleteMultiple');
         Route::get('/permission-groups','PermissionController@getPermissionGroup');

       Route::apiResource('roles', 'RoleController');
       Route::delete('/roles/delete/multiple','RoleController@deleteMultiple');

       Route::apiResource('users', 'UserController');
        Route::delete('/users/delete/multiple','UserController@deleteMultiple');


        Route::apiResource('units', 'UnitController');
       Route::delete('/units/delete/multiple','UnitController@deleteMultiple');
   
    // end of completed works

    Route::apiResource('item-main-groups', App\Http\Controllers\ItemMainGroupController::class); // Add this
    Route::apiResource('item-sub-groups', App\Http\Controllers\ItemSubGroupController::class); // Add this
    Route::apiResource('measurement-units', 'MeasurementUnitController');
    Route::apiResource('items', App\Http\Controllers\ItemController::class); // Add this
    Route::apiResource('service-main-groups', App\Http\Controllers\ServiceMainGroupController::class);
    Route::apiResource('service-sub-groups', App\Http\Controllers\ServiceSubGroupController::class);
    Route::apiResource('services', App\Http\Controllers\ServiceController::class);
    Route::apiResource('sections', App\Http\Controllers\SectionController::class);
    Route::apiResource('sub-sections', App\Http\Controllers\SubSectionController::class);
    Route::apiResource('vehicle-types', App\Http\Controllers\VehicleTypeController::class);
    Route::apiResource('vehicles', App\Http\Controllers\VehicleController::class);
    Route::apiResource('party-types', App\Http\Controllers\PartyTypeController::class);
    Route::apiResource('party-type-categories', App\Http\Controllers\PartyTypeCategoryController::class);
    Route::apiResource('parties', App\Http\Controllers\PartyController::class);
    // In routes/api.php
  Route::apiResource('makes', App\Http\Controllers\MakeController::class);
  Route::apiResource('modes-of-transportation', App\Http\Controllers\ModeOfTransportationController::class);
  Route::apiResource('charge-deduction-heads', App\Http\Controllers\ChargeDeductionHeadController::class);
  
    // transactional apis
    Route::apiResource('tenders', App\Http\Controllers\TenderController::class);
  // end of transactional apis


    // Route::post('/confirm-password', [App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
    // Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy']);
      // Route::apiResource('units', 'UnitController');
    // Route::apiResource('stores', 'StoreController');
    // Route::apiResource('parties', 'PartyController');
    // Route::apiResource('party-types', 'PartyTypeController');
    // Route::apiResource('addresses', 'AddressController');
    // Route::apiResource('item-main-groups', 'ItemMainGroupController');
    // Route::apiResource('item-sub-groups', 'ItemSubGroupController');
    // Route::apiResource('items', 'ItemController');
    // Route::apiResource('service-main-groups', 'ServiceMainGroupController');
    // Route::apiResource('service-sub-groups', 'ServiceSubGroupController');
    // Route::apiResource('services', 'ServiceController');
   
    // Route::apiResource('vehicle-types', 'VehicleTypeController');
    // Route::apiResource('vehicles', 'VehicleController');

    // Route::apiResource('stores.invoices', 'InvoiceController');
    // Route::apiResource('stores.inwards', 'InwardController');
    // Route::apiResource('stores.purchase-orders', 'PurchaseOrderController');
    // Route::apiResource('stores.item-issues', 'ItemIssueController'); // This is the only route needed for item issues

    // Route::post('/stores/{store}/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'sendPurchaseOrder']);
});

// Sanctum Routes (Manually Defined for Laravel 7)
// Route::post('/sanctum/token', [App\Http\Controllers\Auth\NewAccessTokenController::class, 'store']);




//  RIS Admin Routes
Route::middleware(['auth:sanctum', 'is_ris_admin'])->group(function () {
    

    // Resource Routes (Protected by auth:sanctum)
    Route::prefix('ris-admin')->group(function () {
            Route::apiResource('organizations', 'OrganizationController');

        });
  
});

// End of Ris Admin Routes