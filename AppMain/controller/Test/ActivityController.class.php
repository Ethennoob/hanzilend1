<?php
namespace AppMain\controller\Admin;
use \System\BaseClass;
class ActivityController extends BaseClass {
    /**
     *   数据的增，删，改，查；
     *   字段的验证；
     *   memcache添加，获取，删除
     *   获取配置文件信息
     *   常用方法/System/database/BaseTable.class.php(部分案例见本文件最后)
     */

    /**
     * 添加测试活动1
     */
    public function activityTest(){
        $activity = $this->table('user')->where(['username'=>'lxlxlx'])->get(null,true);
        $this->R(['activity'=>$activity]);
    }
}