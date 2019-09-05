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
//This route is for login
Route::post('login', 'API\UserController@login');
//This route is for register
Route::post('register', 'API\UserController@register');
//This middleware is  to check token
Route::group(['middleware' => 'auth:api'], function() {
    //This route is for update user info
    Route::post('update', 'API\UserController@update');
    //This route for get github detail
    Route::post('dashboard', 'API\GithubController@index');
    //This route for get github repo
    Route::post('repositories', 'API\GithubController@repositories');
    //This route for edit github repo
    Route::get('edit_file', 'API\GithubController@edit');

    //This route to get all commits
    Route::get('commits', 'API\GithubController@commits');
});
