<?php

namespace App\Http\Controllers\Rbac;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\BasicsController as Base;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;


class RedisController extends Base
{

	// 获取缓存队列
	public function  checkRedisList(){

        $num = Redis::lLen('user_list');
        if( $num ){
        	$values = Redis::lrange('user_list',0,$num);
        	Base::successReturn('查询成功', 0, $values);
        }
        Base::errorReturn('redis没有数据', 400);
        // echo $values;
	}

	// 为redis写入列表
	public function  writeRedisList(Request $request){
		$data = $request->all();
		if( empty($data['name']) ){
			Base::errorReturn('表单为空！！！', 400);
		}
		$bool = Redis::lpush('user_list', $data['name']);
		Base::successReturn('添加成功', 0, $bool);
	}

	// 清除redis内的数据
	public function delRedisList(){

		$num = Redis::lLen('user_list');
		if( $num == 1 ){
			Redis::lpop('user_list');
		}else{
			Redis::ltrim('user_list',0,0);
		}
		Base::successReturn('删除成功', 0, '');
	}

	// 把redis内的数据写入数据库
	public function writeMysql(){
		// 查看队列中有多少条数聚
		$num = Redis::lLen('user_list');
		if( $num <= 0 ){
			Base::errorReturn('列表为空', 400);
		}
		$x = 0;
		while(1) {
		  	// 从右边取出一条数据 并删除
			$name = Redis::rpop('user_list');
			$bool = DB::table("e_redis_test")->insert(['redis_name'=>$name, 'create_time'=>date('Y-m-d H:i:s',time())]);
			// 添加失败 把数据重新push到列里面 并结束循环
			if( !$bool ){
				Redis::rpush('user_list', $name);
				break;
			}
		  	$x++;
		  	if( $x == $num ){
		  		Base::successReturn('添加成功', 0, '');
		  		break;
		  	}
		}
	}

	// 获取数据库数据
	public function checkMysql(){
		$list = DB::table("e_redis_test")->get();
		Base::successReturn('获取成功', 0, $list);
	}

}