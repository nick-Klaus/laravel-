<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use QrCode;
use phpqrcode;
class IndexController extends Controller
{
	/*
    * 生成二维码
    */
	public  function index(Request $request){
		// size 二维码图片大小  color 二维码图片颜色 
		//  backgroundColor 二维码背景色  generate 二维码内容，跳转网页要加上https http
		$res = $request->get();
		 // 字段验证规则
        $validator = Validator::make($res, [
            'url' => 'required|active_url',
        ],[
            'required' => ':attribute 为必填项',
            'url.active_url' => '请检查网址是否正确（加上https http）',
        ]);
		$data = QrCode::size(100)->color(255,0,255)->backgroundColor(255,255,0)->generate( $res['url'] );
		$url = base64_encode($data);
		echo base64_decode($url);
	}

	public  function qrCode(){
		$img = new \QRcode();
		$value = 'http://www.learnphp.cn'; //二维码内容 
		$errorCorrectionLevel = 'L';//容错级别 
		$matrixPointSize = 6;//生成图片大小 
		//生成二维码图片 
		$img->png($value, 'qrcode.png', $errorCorrectionLevel, $matrixPointSize, 2); 
		$logo = public_path('qrcodes/qrcode.png'); // logo在框架的public目录中
		$QR = 'qrcode.png'; //已经生成的原始二维码图 
		if ($logo !== FALSE) {
			$QR = imagecreatefromstring(file_get_contents($QR)); 
			$logo = imagecreatefromstring(file_get_contents($logo)); 
			$QR_width = imagesx($QR);//二维码图片宽度 
			$QR_height = imagesy($QR);//二维码图片高度 
			$logo_width = imagesx($logo);//logo图片宽度 
			$logo_height = imagesy($logo);//logo图片高度 
			$logo_qr_width = $QR_width / 5; 
			$scale = $logo_width/$logo_qr_width; 
			$logo_qr_height = $logo_height/$scale; 
			$from_width = ($QR_width - $logo_qr_width) / 2; 
			//重新组合图片并调整大小 
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, 
			$logo_qr_height, $logo_width, $logo_height); 
		} 
		//输出图片 
		imagepng($QR, 'helloweba.png'); 
		echo '<img src="helloweba.png">'; 
	}


}