<?php

namespace App\Http\Controllers\Rbac;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\BasicsController as Base;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;

class MenuController extends Base
{

	// 获取菜单列表
	public function checkMenus(Request $request){
		$res = $request->all();
		$where= [];
		if( !empty($res['name']) ){
        	$where[] = ['name','like','%'.$res['name'].'%'];
        }
		$list = Menu::where($where)
                ->whereIn('status',['0','1'])
                ->get()->toArray();
        Base::successReturn('查询成功', 0, $list);
	}

    // 获取顶级菜单列表
    public function checkTopMenus(Request $request){
        $res = $request->all();
        $list = Menu::where(['pid'=>0])
            ->whereIn('status',['0','1'])
            ->get()->toArray();
        Base::successReturn('查询成功', 0, $list);
    }

	// 创建菜单
	public function  createMenu(Request $request){
		$res = $request->post();
        // 字段验证规则
        $validator = Validator::make($res, [
            'address' => 'required',
            'name' => 'required|max:10',
            'remark'=>'max:200'
        ],[
            'address.required' => '地址为必填项',
            'name.required' => '菜单名为必填项',
            'name.max' => '菜单名称长度不能大于于10位',
            'remark.max' => '菜单备注长度不能大于200',
        ]);
        // 字段验证以后 返回相应的错误
        if( $validator->fails() ){
            Base::errorReturn($validator->errors(), 400);
        }
        $menu = new Menu();
		//设定数据
        $menu->pid = intval($res['pid']);
        $menu->status = $res['status'];
        $menu->address = $res['address'];
        $menu->name = $res['name'];
        $menu->remark = $res['remark'];
		if( $menu->save() ){
            Base::successReturn('添加成功');
        }else{
            Base::errorReturn('添加失败', 500);
        }
	}

    // 获取单个角色
    public function  checkRole($id){

        $user = Roles::whereIn('status',['0','1'])->find(intval($id));
        if( empty($user) ){
            Base::errorReturn('角色不存在', 400);
        }
        $info = $user->toArray();
        Base::successReturn('查询成功', 0, $info);
    }

    // 修改角色 
    public function  updateRole(Request $request){

        $res = $request->post();
        // 字段验证规则
        $validator = Validator::make($res, [
            'id' => 'required',
            'name' => 'required|max:10',
            'remark'=>'max:200'
        ],[
            'name.max' => '用户名长度不能超过10位',
            'remark.max' => '备注长度不能超过200',
            'required' => ':attribute 手机号格式不对'
        ]);
        // 字段验证以后 返回相应的错误
        if( $validator->fails() ){
            Base::errorReturn($validator->errors()->first(), 400);
        }
        $bool = Roles::find(intval($res['id']));
        $bool->name = trim($res['name']);
        $bool->remark = trim($res['remark']);
        $bool->status = strval($res['status']);
        if( $bool->save() ){
            Base::successReturn('修改成功');
        }
        Base::errorReturn('修改失败', 400);
    }

    // 删除角色
    public function delRole($id){
        
        $del = Roles::find(intval($id));
        $del->status = '999';
        $del->deleted_at = time();
        if( $del->save() ){
            Base::successReturn('删除成功');
        }
        Base::errorReturn('删除失败', 500);
    }
}