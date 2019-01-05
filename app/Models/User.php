<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	 /**
     * 指定表名
     * @var string
     */
    protected $table = 'e_user';
	/**
     * 指定id
     * @var string
     */
    protected $primarykey = 'id';
    /*
    * 自动维护时间戳
    * 返回当前时间戳
    */ 
    public $timestamps = true;
    protected function getDateFormat(){
        return time();
    }

    // 定义隐藏的字段
    protected $hidden = ['updated_at','created_at','deleted_at'];

    protected $fillable = ['phone','head_url', 'role_id','username','password','sex','remark','status','ip'];

    public function hasOneAccount()
    {
      return $this->hasOne('App\Models\Roles', 'id', 'role_id');
    }

}