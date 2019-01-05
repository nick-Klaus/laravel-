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


// Rbac 板块
Route::group( [ 'prefix' => 'Rbac' ] , function () {
	// 登录
	Route::post('login', 'Rbac\LoginController@login');
	// Route::get('apiVerif1', 'Rbac\LoginController@apiVerif1');
	// Route::post('apiVerif2', 'Rbac\LoginController@apiVerif2');
	//  管理员操作
	Route::post('checkUsers', 'Rbac\UsersController@checkUserList');
	Route::post('crateUser', 'Rbac\UsersController@crateUser');
	Route::post('updateUser', 'Rbac\UsersController@updateUser');
	Route::post('delUsers', 'Rbac\UsersController@delUsers');
	Route::get('checkUser/{id}', 'Rbac\UsersController@checkUser');
	Route::get('tokenCheckUser/{token}', 'Rbac\UsersController@tokenCheckUser');
	Route::get('delUser/{id}', 'Rbac\UsersController@delUser');
	Route::post('uploadImg', 'Rbac\UsersController@uploadImg');

	Route::get('export', 'Rbac\UsersController@export');
	Route::get('export1', 'Rbac\UsersController@export1');
	// 角色操作
	Route::get('checkRole/{id}', 'Rbac\RolesController@checkRole');
	Route::get('checkRoles', 'Rbac\RolesController@checkRoles');
	Route::get('delRole/{id}', 'Rbac\RolesController@delRole');
	Route::post('creatRole', 'Rbac\RolesController@creatRole');
	Route::post('updateRole', 'Rbac\RolesController@updateRole');
	// redis的使用  
	Route::get('checkRedisList', 'Rbac\RedisController@checkRedisList');
	Route::get('writeRedisList', 'Rbac\RedisController@writeRedisList');
	Route::get('delRedisList', 'Rbac\RedisController@delRedisList');
	Route::get('writeMysql', 'Rbac\RedisController@writeMysql');
	Route::get('checkMysql', 'Rbac\RedisController@checkMysql');
});


Route::group(['prefix' => 'Rbac','middleware' => ['web1']], function () {
    Route::get('apiVerif1', 'Rbac\LoginController@apiVerif1');
    Route::post('apiVerif2', 'Rbac\LoginController@apiVerif2');
});
