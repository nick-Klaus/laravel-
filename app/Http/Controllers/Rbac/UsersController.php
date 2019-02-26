<?php

namespace App\Http\Controllers\Rbac;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\BasicsController as Base;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Excel;
use PHPExcel_Worksheet_Drawing;

class UsersController extends Base
{
    
    /**
     * 获取(管理员)列表
     * @param int $id
     * @return Response
     */
    public function checkUserList(Request $request){

        $userInfo = $request->post();
        $page = isset($userInfo['page']) ? $userInfo['page']  : 1;
 		$pageSize = isset($userInfo['limit']) ? $userInfo['limit'] : 10;
 		$offset = ($page - 1) * $pageSize;
        $where = [];
        if( !empty($userInfo['start']) && !empty($userInfo['end']) ){
        	$where[] = ['e_user.created_at','>=', strtotime($userInfo['start'])-8*3600];
        	$where[] = ['e_user.created_at','<=', strtotime($userInfo['end'])-8*3600];
        }
        if( !empty($userInfo['username']) ){
        	$where[] = ['e_user.username','like','%'.$userInfo['username'].'%'];
        }
        // 数据列表
        // $list = User::where($where)
        //         ->whereIn('status',['0','1'])
        //         ->offset($offset)->limit($pageSize)
        //         ->get()
        //         ->toArray();
        $list = User::where($where)
                ->whereIn('e_user.status',['0','1'])
                ->offset($offset)->limit($pageSize)
                ->leftJoin("e_roles", function ($join){
                    $join->on("e_roles.id", "=", "e_user.role_id");
                })->get(array('e_user.*','e_roles.name'))
                ->toArray();
        // 数据条数
        $count = User::where($where)
                ->whereIn('status',['0','1'])
                ->count();
        Base::successReturn('查询成功', $count, $list);
    }

    /**
     * 添加管理员
     * @return Response
     */
    public function crateUser(Request $request){
        $userInfo = $request->post();
        // 字段验证规则
        $validator = Validator::make($userInfo, [
            'role_id' => 'required',
            'username' => 'required|max:10',
            'password1' => 'required|min:6|max:10',
            'password2' => 'required|min:6|max:10',
            'phone'=>'regex:/^1[34578][0-9]{9}$/',
            'remark'=>'max:200'
        ],[
            'required' => ':attribute 为必填项',
            'min' => ':attribute 长度不能小于6位',
            'max' => ':attribute 长度不能大于于10位',
            'regex' => ':attribute 不正确'
        ],[
            'role_id' => '权限组',
            'password1' => '密码一',
            'password2' => '密码二',
            'phone' => '手机号码',
            'remark' => '备注',
            'username' => '用户名'
        ]);
        // 字段验证以后 返回相应的错误
        if( $validator->fails() ){
            Base::errorReturn($validator->errors(), 400);
        }
        // 两个密码不一样
        if( $userInfo['password1'] !== $userInfo['password2'] ){
           Base::errorReturn('两次密码不相同', 400);
        }
        $user = User::firstOrNew(
            ['phone' => strval($userInfo['phone'])],
            [
                'role_id' => intval($userInfo['role_id']),
                'username' => strval(trim($userInfo['username'])),
                'password' => sha1($userInfo['password1'].config('global.salt')),
                'sex' => strval($userInfo['sex']),
                'remark' => strval($userInfo['remark']),
                'status' => strval($userInfo['status']),
                'head_url' => strval($userInfo['head_url']),
                'ip' => $request->getClientIp(),
            ]
        );
        $info = $user->toArray();
        if( !empty($info['id']) ){
            // '该手机已经被注册'
            Base::errorReturn('该手机已经被注册', 400);
        }
        if( $user->save() ){
            Base::successReturn('添加成功');
        }else{
            Base::errorReturn('添加失败', 500);
        }
    }

    /*
	 * 根据id 获取单个(管理员)
     * @param int $id
     * @return Response
    */
    public function checkUser($id){
    	
    	$user = User::whereIn('status',['0','1'])->find(intval($id));
        if( empty($user) ){
        	Base::errorReturn('用户不存在', 400);
        }
        $info = $user->toArray();
        Base::successReturn('查询成功', 0, $info);
    }

    /*
     * 根据token 获取单个(管理员)
     * @param int $id
     * @return Response
    */
    public function tokenCheckUser($token){
        
        $user = DB::table('e_user')->where([ 'token' => $token ])->first();
        if( empty($user) || $user->status == '999' ){
            Base::errorReturn('用户不存在', 400);
        }
        if(  $user->status == '0' ){
            Base::errorReturn('用户被禁用', 400);
        }
        Base::successReturn('查询成功', 0, $user);
    }

    /*
	* 修改管理员信息
	* @param int $id
	* @return Response
    */
    public function updateUser(Request $request){
     	
		$userInfo = $request->post();
		// 字段验证规则
        $validator = Validator::make($userInfo, [
            'role_id' => 'required',
            'id' => 'required',
            'username' => 'required|max:10',
            'phone'=>'regex:/^1[34578][0-9]{9}$/',
            'remark'=>'max:200'
        ],[
        	'required' => ':attribute 为必填项',
        	'username.max' => '用户名长度不能超过10位',
        	'remark.max' => '备注长度不能超过200',
        	'phone.regex' => '手机号格式不对'
        ]);
        // 字段验证以后 返回相应的错误
        if( $validator->fails() ){
        	Base::errorReturn($validator->errors()->first(), 400);
        }
        $bool = User::find(intval($userInfo['id']));
		$bool->phone = $userInfo['phone'];
		$bool->remark = $userInfo['remark'];
		$bool->sex = strval($userInfo['sex']);
		$bool->username = trim($userInfo['username']);
		$bool->role_id = $userInfo['role_id'];
		$bool->status = $userInfo['status'];
		if( $bool->save() ){
			Base::successReturn('修改成功');
		}
		Base::errorReturn('修改失败', 400);
    }

    /*
	* 删除管理员信息
	* @param int $id
	* @return Response
    */
    public function delUser($id){

    	$del = User::find(intval($id));
    	$del->status = '999';
    	$del->deleted_at = time();
    	if( $del->save() ){
    		Base::successReturn('删除成功');
    	}
    	Base::errorReturn('删除失败', 500);
    }

    /*
	* 批量删除管理员信息
	* @param int $ids
	* @return Response
    */
    public function delUsers(Request $request){

    	$ids = $request->post();
		// 字段验证规则
        $validator = Validator::make($ids, [
            'ids' => 'array',
        ],[
        	'ids.array' => '需要删除的用户不存在',
        ]);
        // 字段验证以后 返回相应的错误
        if( $validator->fails() ){
        	Base::errorReturn($validator->errors()->first(), 400);
        }
        $dels = User::whereIn('id',$ids['ids'])->update(['status'=>'999']);
        if( $dels ){
        	Base::successReturn('删除成功');
        }
        Base::errorReturn('删除失败', 500);
    }

    /*
    * 图片上传
    * @param int $ids
    * @return Response
    */
    public function uploadImg(Request $request){

        if ($request->isMethod('post')) {

            $file = $request->file('file');
            // 文件是否上传成功
            if ($file->isValid()) {
                // 获取文件相关信息
                $originalName = $file->getClientOriginalName(); // 文件原名
                $ext = $file->getClientOriginalExtension();     // 扩展名
                $realPath = $file->getRealPath();   // 临时文件的绝对路径
                $type = $file->getClientMimeType();     // image/jpeg

                // 上传文件
                $filename = date('Y-m-d').'/'.time().uniqid() . '.' . $ext;
                // 使用我们新建的uploads本地存储空间（目录）
                // 这里的uploads是配置文件的名称
                $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
                if($bool){
                    Base::successReturn('上传成功',0, $filename);
                }
                Base::errorReturn('上传失败', 500);
            }

        }

    }

    /*
    * 导出数据到excel（不带图片）
    */
    public function export(){
        ini_set('memory_limit','500M');
        set_time_limit(0);//设置超时限制为0分钟
        $cellData = User::select('username','token','remark')->limit(5)->get()->toArray();
        $cellData[0] = array('昵称','token','备注');
        for($i=0;$i<count($cellData);$i++){
            $cellData[$i] = array_values($cellData[$i]);
            $cellData[$i][0] = str_replace('=',' '.'=',$cellData[$i][0]);
        }

        Excel::create('用户信息',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');
        die;
    }

    /*
    * 导出带图片的数据到excel
    */
    public function export1(){
        ini_set('memory_limit','500M');
        set_time_limit(0);//设置超时限制为0分钟
        $cellData = User::select('username','token','remark','head_url')->limit(5)->get()->toArray();
        $cellData[0] = array('昵称','token','备注');
        for($i=0;$i<count($cellData);$i++){
            $cellData[$i] = array_values($cellData[$i]);
            $cellData[$i][0] = str_replace('=',' '.'=',$cellData[$i][0]);
        }
        
        Excel::create('用户信息',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                //init列
                $title_array = ['A', 'B', 'C', 'D'];
                //遍历数据
                for($i=0;$i<sizeof($cellData);$i++){
                    foreach($cellData[$i] as $k=>$v){
                        //设置图片列高度
                        $i>0 && $sheet->setHeight($i+1, 65);
                        //设置图片列宽度
                        $sheet->setWidth(array('F'=>12));
                        //判断图片列，如果是则放图片
                        

                        if($k == 3 && $i>0){
                            $objDrawing = new PHPExcel_Worksheet_Drawing;
                            $objDrawing->setPath(storage_path('/app/public/uploads/').$v);
                            $objDrawing->setCoordinates($title_array[$k] . ($i+1));
                            $objDrawing->setHeight(80);
                            $objDrawing->setOffsetX(1);
                            $objDrawing->setRotation(1);
                            $objDrawing->setWorksheet($sheet);
                            continue;
                        }
                        //否则放置文字数据
                        $sheet->cell($title_array[$k] . ($i+1), function ($cell) use ($v) {
                            $cell->setValue($v);
                        });    
                    }                    
                }     
            });
        })->export('xls');
        die;
    }





}