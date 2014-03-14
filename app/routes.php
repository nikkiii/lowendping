<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::model('query', 'Query');

Route::get('/', 'HomeController@showHome');
Route::get('/update/{query}', 'HomeController@checkResponses');

Route::post('/submit', 'HomeController@submitQuery');
Route::post('/response', 'HomeController@serverResponse');