<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

#Mobile APIs
Route::get('v1/form_options','Api\FormoptionController@index');
Route::post('v1/email_login', 'Api\AuthController@email_login');
Route::post('v1/facebook_login', 'Api\AuthController@facebook_login');
Route::post('v1/register', 'Api\AuthController@register');
Route::post('v1/forgot_password', 'Api\AuthController@forgot_password');
Route::post('v1/change_password', 'Api\AuthController@change_password');
Route::post('v1/add_child', 'Api\ChildController@add_child');
Route::get('v1/get_children', 'Api\ChildController@get_children');

Route::get('v1/get_child', 'Api\ChildController@get_child');

Route::post('v1/delete_child', 'Api\ChildController@delete_child');
Route::post('v1/edit_child', 'Api\ChildController@edit_child');
Route::post('v1/set_deviceuuid', 'Api\ChildController@set_deviceuuid');
Route::post('v1/remove_deviceuuid', 'Api\ChildController@remove_deviceuuid');

Route::post('v1/sync_scheduled_sessions', 'Api\SyncController@sync_scheduled_sessions');
Route::post('v1/sync_scheduled_appointments', 'Api\SyncController@sync_scheduled_appointments');
Route::post('v1/sync_calendar', 'Api\SyncController@sync_calendar');
Route::post('v1/sync_spacer_dataline', 'Api\SyncController@sync_spacer_dataline');
Route::post('v1/sync_spacer_sessions', 'Api\SyncController@sync_spacer_sessions');
Route::post('v1/sync_meattack', 'Api\SyncController@sync_meattack');
Route::post('v1/delete_meattack', 'Api\SyncController@delete_meattack');
Route::post('v1/updatesessiondata', 'Api\SyncController@updatesessiondata');



Route::post('v1/add_reward', 'Api\RewardController@add_reward');
Route::post('v1/delete_reward', 'Api\RewardController@delete_reward');
Route::post('v1/send_feedback', 'Api\RewardController@send_feedback');
Route::post('v1/sync_rewards', 'Api\RewardController@sync_rewards');