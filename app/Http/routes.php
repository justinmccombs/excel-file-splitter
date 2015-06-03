<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'file-splitter'], function() {
    Route::post('/', 'ExcelFormController@store');
    Route::get('/', 'ExcelFormController@index');
});

Route::group([
    'prefix' => 'auth/google',
    'namespace' => 'Auth\GoogleRedirect'
], function () {
    Route::controller('/', 'GoogleRedirectController');
});

Route::get('/', 'HomeController@index');