<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class BasicsController extends Controller
{
    
    /**
     * 执行成功
     * @param string msg // 返回信息
     * @param int count // 数据条数
     * @param obj data // 数据集
     * @param int code 
     * @return 返回一个json对象
     */
    protected function successReturn($msg='', $count=0, $data=array())
    {
        $respons = [
          "code" => 0, 
          "msg" => $msg,
          "count" => $count,
          "data" => $data
        ];
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($respons);
        exit;
    }

    /**
     * 执行错误
     * @param string msg // 返回信息
     * @param int code 
     * @return 返回一个json对象
     */
    protected function errorReturn($msg='', $code=400)
    {
        $respons = [
          "code" => $code, 
          "msg" => $msg,
          "count" => 0,
          "data" => array()
        ];
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($respons);
        exit;
    }

    /**
     * 数组转xml
     * @access protected
     * @param  arr 需要转xml的数组
     * @return xml
     */
    protected function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key=>$val) {
            if (is_numeric($val)) {
                $xml.="<".$key.">".$val."</".$key.">";
            } else {
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 生成随机字符串，默认32位
     * @access protected
     * @param  $length 随机字符的长度
     * @return string
     */
 
    protected function createNoncestr($length=32) {
        //创建随机字符
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for($i=0;$i<$length;$i++) {
          $str.=substr($chars, mt_rand(0,strlen($chars)-1),1);
        }

        return $this->gbkToUtf8($str);  
    }

    /**
      *自动判断把gbk或gb2312编码的字符串转为utf8
      *能自动判断输入字符串的编码类，如果本身是utf-8就不用转换，否则就转换为utf-8的字符串
      *支持的字符编码类型是：utf-8,gbk,gb2312
      *@$str:string 字符串
      */
    private function gbkToUtf8($str){
        $charset = mb_detect_encoding($str,array('ASCII','UTF-8','GBK','GB2312'));
        $charset = strtolower($charset);
        if("utf-8" != $charset){
            $str = iconv('UTF-8',$charset,$str);
        }
        return $str;
    }

    /**
     * 无限极分类生成树
     * @access protected
     * @param $arr 需要格式的数组
     * @return json protected
     */
    protected function generateTree($arr,$id=0,$lev=1) {
        $subs = array(); //子孙数组
        foreach ($arr as $v) {
            if ($v['top_id'] == $id) {
                $v['lev'] = $lev;
                $subs[] = $v; //举例说array('id'=>1,'name'=>'安徽','parent'=>0),
                $subs = array_merge($subs, self::generateTree($arr,$v['id'],$lev+1));
            }
        }
        return $subs;
    }

    /**
     * 一个数组成树, 把子数组加入到父数组的children中
     * @access public
     * @param $data 需要格式的数组
     * @param $pid = 0 上级ID
     * @return json protected
     */
    protected function arrayTree($data, $pid = 0) {
        $tree = array();
        if ($data && is_array($data)) {
            foreach($data as $v) {
                if($v['top_id'] == $pid) {
                    $v['children'] = $this->arrayTree($data, $v['id']);
                    $tree[] = $v;
                }
            }
        }
        return $tree;
    }


}