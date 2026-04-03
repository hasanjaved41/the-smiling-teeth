<?php

use Illuminate\Http\Request;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::group([
    "namespace" => "V1",
    "prefix" => "v1/admin",
    "middleware" => "auth.admin",
], function () {
    Route::post('login', 'AdminController@login');
    Route::post('attendance/approve', 'SubuserController@approveAttendance');
    
});



/*
|--------------------------------------------------------------------------
| SUBUSERS ROUTES
|--------------------------------------------------------------------------
*/
Route::group([
    "namespace" => "V1",
    "prefix" => "v1/subusers",
    "middleware" => "auth.user",
], function () {
    Route::post('attendance/mark', 'SubuserController@markAttendance');
    Route::post('attendance/approve', 'SubuserController@approveAttendance');
    Route::get('attendance/last', 'SubuserController@lastAttendance');
    Route::get('details', 'SubuserController@getSubuserDetails');
    Route::post('attendance/pending', 'SubuserController@pendingAttendance')->name('pending-attendance');
    Route::post('attendance/status', 'SubuserController@attendanceStatus')->name('attendance_status');
    Route::get('my-list', 'CollectionsController@myList');
    Route::get('my-list-panel', 'CollectionsController@myListPanel');
    Route::get('search-my-list', 'CollectionsController@searchMyList');
    Route::post('add-call-log','SubuserController@addCallCenterLog');
    Route::get('search-my-list-panel', 'CollectionsController@searchMyListPanel');
    Route::get('qc-call-logs','SubuserController@getQcCallLogs');
    Route::post('add-qc-ratings','SubuserController@addQcRatings');
    Route::get('case-allocation-details/{loan_collection_reference_number}', 'CollectionsController@getCaseAllocationDetails');
    Route::post('deposit-initiate','PaymentController@depositInitiate');
    Route::post('deposit-verify','PaymentController@depositVerify');
});
