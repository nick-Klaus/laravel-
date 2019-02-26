<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * 指定表名
     * @var string
     */
    protected $table = 'e_menu';

    /*
    * 自动维护时间戳
    * 返回当前时间戳
    */
    public $timestamps = true;
    protected function getDateFormat(){
        return time();
    }
    /**
     * 指定id
     * @var string
     */
    protected $primarykey = 'id';

//    public function belongsToUser(){
//        return $this->belongsTo('User', 'role_id', 'id');
//    }

    public function getStartTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['created_at']);
    }
}