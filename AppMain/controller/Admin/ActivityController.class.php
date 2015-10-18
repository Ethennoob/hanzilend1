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
     * 添加活动1
     */
    public function activityOneAdd(){
        //-----------字段验证-----------
        //'cash'=>[null,null,null],第一个字段为类型，第二个为范围，第三个判断对否验证（默认true必须验证）
        $rule = [
            'sn'            =>[],
            'cash'          =>[],
            'limit_max_cash'=>[],
            'max_cash_count'=>[],
            'fine_rate'     =>[],
            'max_fine_rate' =>[],
            'fine_days'     =>['egNum'],
            'start_time'    =>[],
            'end_time'      =>[],
        ];
        $this->V($rule);
        //'object_name'=>[], 非空
        //+
        //'total_amount'=>['egNum',null,true], 类别（egNum大于0的整数，....）
        //'initial_rate'=>['in',[0,1,2,3,4,5,6],true], 范围
        //'email'=>['email'] 特殊字段
        //参见 System/BaseClass.class.php和System/MyVerify.class.php
        //-----------------------
    
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $data['start_time']   = strtotime($data['start_time']);
        $data['end_time']     = strtotime($data['end_time']);
        $data['add_time']     = time();
    
        $activity = $this->table('activity_share_1')->save($data);
        if(!$activity){
            $this->R('',40001);
        }
    
        $this->R();
    }
    /**
     * 活动1列表___查询列表
     */
    public function activityOneList(){
        $this->V(['is_show'=>['in',[0,1],false]]);
        $where=['is_on'=>1];
        //$this->queryFilter，拼接查询字段
        $whereFilter=$this->queryFilter($where,['is_show']);
    
        $pageInfo = $this->P();
        $file = ['id','sn','cash','limit_max_cash','max_cash_count','fine_rate','max_fine_rate','fine_days','start_time','end_time','is_show','add_time','update_time'];
        $class = $this->table('activity_share_1')->where($whereFilter)->order('update_time desc');
        //查询并分页
        $activity = $this->getOnePageData($pageInfo,$class,'get','getListLength',null,false);
        if($activity){
            foreach ($activity as $k=>$v){
                $activity[$k]['start_time'] = date('Y-m-d H:i:s',$v['start_time']);
                $activity[$k]['end_time'] = date('Y-m-d H:i:s',$v['end_time']);
                $activity[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $activity[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
        }else{
            $activity = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['activity'=>$activity,'pageInfo'=>$pageInfo]);
    }
    
    /**
     * 活动1细节内容___查询一条数据
     */
    public function activityOneDetail(){
    
        $this->V(['activity_id'=>['egNum']]);
        $activity_id = intval($_GET['activity_id']);
        //查找memcache缓存
        $activity1Detail=$this->S()->get('Activity1_'.$activity_id);
        if (!$activity1Detail){
            //查询一条数据
            $activity = $this->table('activity_share_1')->where(['is_on'=>1,'id'=>$activity_id])->get(null,true);
            if(!$activity){
                $this->R('',70009);
            }
            //$this->convertId() 加载系统convert.php文件的配置 ；$this->config()类似；
            $activity['is_show'] = $this->convertId('ACTIVITYSTUTUS',$activity['is_show']);
            $activity['start_time'] = date('Y-m-d H:i:s',$activity['start_time']);
            $activity['end_time'] = date('Y-m-d H:i:s',$activity['end_time']);
            $activity['update_time'] = date('Y-m-d H:i:s',$activity['update_time']);
            $activity['add_time'] = date('Y-m-d H:i:s',$activity['add_time']);
            //设置memcache缓存，serialize（序列化），兼容window和linux系统
            $this->S()->set('Activity1_'.$activityID, serialize($activity1Detail),60*60);
        }
        else{
            //设置memcache缓存，unserialize（反序列化），兼容window和linux系统
            $activity1Detail=unserialize($activity1Detail);
        }
        
        $this->R(['activity'=>$activity]);
    }

     /**
     * 活动2细节内容___查询一条数据
     */
    public function activityOneDetail2(){
    
        $this->V([
            'id'=>['egNum',null,false]
            ]);
        $id = intval($_GET['id']);
        //查找memcache缓存
        //$activity1Detail=$this->S()->get('Activity1_'.$id);
        /*if (!$activity1Detail){*/
            //查询一条数据
            $activity = $this->table('activity_share_1')->where(['is_on'=>1,'id'=>$id])->get(null,true);
            if(!$activity){
                $this->R('',70009);
            }
            //$this->convertId() 加载系统convert.php文件的配置 ；$this->config()类似；
            $activity['is_show'] = $this->convertId('ACTIVITYSTUTUS',$activity['is_show']);
            $activity['start_time'] = date('Y-m-d H:i:s',$activity['start_time']);
            $activity['end_time'] = date('Y-m-d H:i:s',$activity['end_time']);
            $activity['update_time'] = date('Y-m-d H:i:s',$activity['update_time']);
            $activity['add_time'] = date('Y-m-d H:i:s',$activity['add_time']);
            //设置memcache缓存，serialize（序列化），兼容window和linux系统
            //$this->S()->set('Activity1_'.$activityID, serialize($activity1Detail),60*60);
        //}
       /* }else{
            //设置memcache缓存，unserialize（反序列化），兼容window和linux系统
            $activity1Detail=unserialize($activity1Detail);
        }*/
        
        $this->R(['activity'=>$activity]);
    }
      
    
    /**
     * 修改活动1
     */
    public function activityOneEdit(){
        $rule = [
            'activity_id'   =>['egNum'],
            'sn'            =>[null,null,false],
            'cash'          =>[null,null,false],
            'limit_max_cash'=>[null,null,false],
            'max_cash_count'=>[null,null,false],
            'fine_rate'     =>[null,null,false],
            'max_fine_rate' =>[null,null,false],
            'fine_days'     =>['egNum',null,false],
            'start_time'    =>[null,null,false],
            'end_time'      =>[null,null,false],
            'update_time'   =>[null,null,false],
        ];
        $this->V($rule);
        $activity_id = intval($_POST['activity_id']);
        $activity = $this->table('activity_share_1')->where(['id'=>$activity_id,'is_on'=>1])->get(['id'],true);
        if(!$activity){
            $this->R('',70009);
        }
    
        unset($rule['activity_id']);
        foreach ($rule as $k=>$v){
            if(isset($_POST[$k])){
                $data[$k] = $_POST[$k];
            }
        }
        if(isset($data['start_time'])){
            $data['start_time'] = strtotime($data['start_time']);
        }
        if(isset($data['end_time'])){
            $data['end_time'] = strtotime($data['end_time']);
        }
        $data['update_time']  = time();
    
        $activity = $this->table('activity_share_1')->where(['id'=>$activity_id])->update($data);
        if(!$activity){
            $this->R('',40001);
        }
        //活动更改了内容，删除活动信息的memcache,缓存
        $this->S()->delete('A1_'.$activity_id);
        $this->R();
    }
    
    /**
     *删除活动1（设置数据库字段为0，相当于回收站）
     */
    public function activityOneDelete(){
    
        $this->V(['activity_id'=>['egNum']]);
        $activity_id = intval($_POST['activity_id']);
         
        $activity = $this->table('activity_share_1')->where(['id'=>$activity_id,'is_on'=>1])->get(['id'],true);
    
        if(!$activity){
            $this->R('',70009);
        }
    
        $activity = $this->table('activity_share_1')->where(['id'=>$activity_id])->update(['is_on'=>0]);
        if(!$activity){
            $this->R('',40001);
        }
        $this->S()->delete('A1_'.$activity_id);
        $this->R();
    }
    
    
    /**
     *删除活动1（清除数据）
     */
    public function activityOneDeleteconfirm(){
    
        $this->V(['activity_id'=>['egNum']]);
        $activity_id = intval($_POST['activity_id']);
         
        $activity = $this->table('activity_share_1')->where(['id'=>$activity_id,'is_on'=>1])->get(['id'],true);
    
        if(!$activity){
            $this->R('',70009);
        }
    
        $activity = $this->table('activity_share_1')->where(['id'=>$activity_id])->delete();
        if(!$activity){
            $this->R('',40001);
        }
        $this->S()->delete('A1_'.$activity_id);
        $this->R();
    }
    
    public function test(){
       //对数据进行四则运算
       $updateObjectPeriods=$this->table('object_periods')->where(['id'=>$isObject['periods_id']])->setFieldCalcValue(['buyed_amount','buyed_count'],['+','+'],[$amount,$periodsPeople]);
        
    
    }
    
      	
}
