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

Route::get('/', function () {
    return view('welcome');
});


// Rbac 板块
// Route::prefix('Rbac')->group(function () {
// 	Route::any('user', 'Rbac\UsersController@index');
// 	Route::post('crateUser', 'Rbac\UsersController@crateUser');
//     // Route::get('Users\index', function () {
//     //     echo 111213;
//     // })->name('user');
//     // Route::get('users', function () {
//     //     // 处理 /api/users 路由
//     // })->name('api.users');
// });

// Route::group( [ 'prefix' => 'Rbac' ] , function () {
// 	Route::any('user', 'Rbac\UsersController@qrCode');
// });
Route::any('index', 'IndexController@index');
Route::any('qrCode', 'IndexController@qrCode');