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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/auth/register', 'UserController@register');
Route::post('/auth/login', 'UserController@login');
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('user-info', 'UserController@getUserInfo');
    Route::put('user', 'UserController@update');
//    Route::delete('job/{id}', 'JobController@destroy');
    Route::post('job', 'JobController@store');
    Route::put('job/{id}', 'JobController@update');
    Route::get('job/user', 'JobController@getJobByUser');




});
//Route::post('job', 'JobController@store');
Route::delete('job/{id}', 'JobController@destroy');
Route::get('job/{id}', 'JobController@show');
Route::get('job', 'JobController@index');

Route::post('job/DB/test', 'JobController@create');

Route::post('user/login/facebook', 'SocialController@facebook');
Route::post('user/login/google', 'SocialController@google');



