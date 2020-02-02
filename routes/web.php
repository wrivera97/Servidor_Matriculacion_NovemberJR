<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use App\Matricula;


Route::get('/pruebas', 'PruebasController@get');
Route::get('/home', 'HomeController@index')->name('home')->middleware('auth');
Auth::routes();


