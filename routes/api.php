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
    
    Route::put('/users/profile_settings', 'AuthController@updateProfileSettings');
    ;
    Route::post('/users/profile_picture_update', 'AuthController@updateProfilePicture');
    Route::delete('/users/profile_picture_remove', 'AuthController@deleteProfilePicture');
    Route::post('/logout', 'AuthController@logout');
    
   

       Route::apiResource('permissions', 'PermissionController');
         Route::delete('/permissions/delete/multiple','PermissionController@deleteMultiple');
         Route::get('/permission-groups','PermissionController@getPermissionGroup');

       Route::apiResource('roles', 'RoleController');
       Route::delete('/roles/delete/multiple','RoleController@deleteMultiple');

       Route::apiResource('users', 'UserController');
        Route::delete('/users/delete/multiple','UserController@deleteMultiple');


        Route::apiResource('tags', 'TagController');
        Route::delete('/tags/delete/multiple','TagController@deleteMultiple'); 
        
        Route::get('documents/search', 'DocumentController@search');
        Route::apiResource('documents', 'DocumentController');
      Route::get('documents-list', 'DocumentController@listForDropdown');

        Route::delete('/tags/delete/multiple','TagController@deleteMultiple');
Route::post('documents/upload-image', 'DocumentController@uploadImage');


       
  
});


//  RIS Admin Routes
Route::middleware(['auth:sanctum', 'is_ris_admin'])->group(function () {
    

    // Resource Routes (Protected by auth:sanctum)
    Route::prefix('ris-admin')->group(function () {
            Route::apiResource('organizations', 'OrganizationController');

        });
  
});

// End of Ris Admin Routes