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

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    clean("<scpt>alert('1')</scpt>");
    return view('welcome');
});

Route::post('OAuth/login', 'OAuth\AuthController@login');
Route::view('/index', 'index');
Route::view('/admin', 'admin');
Route::get('/MessageManage/GetMeassageController','MessageManage\GetMeassageController@adminGetmessage');
Route::get('/MessageManage/AdminDeleteController','MessageManage\AdminDeleteController@adminDelete');
