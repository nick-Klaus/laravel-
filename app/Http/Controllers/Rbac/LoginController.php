<?php

namespace App\Http\Controllers\Rbac;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\BasicsController as Base;
use Illuminate\Support\Facades\DB;
use GeetestLib;


class LoginController extends Base
{

	// 用户登录后台 并生成token 这是一个测试
	// 用户登录后台 并生成token 这是一个测试
	// 测试A
	// 测试B
	// 测试一下下 走走
	public function login(Request $request){
		$res = $request->post();
		// 字段验证规则
        $validator = Validator::make($res, [
            'phone'=>'regex:/^1[34578][0-9]{9}$/',
            'password'=>'required|min:6',
            'geetest_seccode'=>'required',
        ],[
        	'password.required' => '密码或用户名错误',
        	'password.min' => '密码或用户名错误',
        	'phone.regex' => '手机号格式不对',
        	'geetest_seccode.required' => '请点击验证条...'
        ]);
        // 字段验证以后 返回相应的错误
        if( $validator->fails() ){
        	Base::errorReturn($validator->errors()->first(), 400);
        }
        // 把用户传入的密码加密
        // 再去和数据库对比是否有此用户的数据
        $password = sha1($res['password'].config('global.salt'));
		$user =  DB::table('e_user')->where([
				    ['phone', '=', $res['phone']],
				    ['password', '=', $password]
				])->first();
		if( empty($user) ){
			Base::errorReturn('用户名和密码错误', 400);
		}
		if( $user->status != 1 ){
			Base::errorReturn('用户名和密码错误', 500);
		}
		// 生成token返回到前台
		$token = base64_encode(microtime(true).$user->id.uniqid());
		// 把token存入数据库中
		$bool = DB::table('e_user')->where('id', $user->id)->update([
					'token' => $token,
					'expires_time' => time()+1800
				]);
		if( $bool ){
			Base::successReturn('登录成功', 0, $token);
		}
		Base::errorReturn('用户名和密码错误', 400);
	}

	// 验证码的第一次验证
	public function apiVerif1(Request $request){
		// 极验类的库，传入CAPTCHA_ID 和 PRIVATE_KEY
		$GtSdk = new \GeetestLib(config('constants.CAPTCHA_ID'), config('constants.PRIVATE_KEY'));
		$data = array(
				"user_id" => uniqid(), # 网站用户id
				"client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
				"ip_address" => $request->getClientIp(), # 请在此处传输用户请求验证时所携带的IP
			);
		$status = $GtSdk->pre_process($data, 1); // 获取服务器的状态
		session(['gtserver' => $status]);// 状态存入session中
		session(['user_id' => $data['user_id']]);// 用户id存入session中
		echo $GtSdk->get_response_str(); // 传到前台 供initGeetest使用 data.gt, data.challenge, data.success
	}

	// 验证码的第二次验证
	public function apiVerif2(Request $request){
		$info = $request->all();
		// 判断服务器是否正常
		$gtserver = session()->pull('gtserver');
		// 用户的id  一个随机数
		$user_id  = session()->pull('user_id');
		$GtSdk = new \GeetestLib(config('constants.CAPTCHA_ID'), config('constants.PRIVATE_KEY'));
		$data = array(
		        "user_id" => $user_id, # 网站用户id
		        "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
		        "ip_address" => $request->getClientIp(), # 请在此处传输用户请求验证时所携带的IP
		    );
		if ( $gtserver == 1) {   // 服务器正常
		    $result = $GtSdk->success_validate($info['geetest_challenge'], $info['geetest_validate'], $info['geetest_seccode'], $data);
		    if ($result) {
		        Base::successReturn('验证成功', 0, "success"); // echo '{"status":"success"}';
		    }
		    Base::errorReturn('验证失败', 300);
		} else {  // 服务器宕机,走failback模式
		    if ($GtSdk->fail_validate($info['geetest_challenge'],$info['geetest_validate'],$info['geetest_seccode'])) {
		        Base::successReturn('验证成功', 0, "success");
		    }
		    Base::errorReturn('验证失败', 300);
		}
	}	
}
