<?php
/**
 * 盛世分销系统---分销商申请类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-11-03 17:21:17
 * @version $Id$
 */

namespace AppMain\controller\Api;
use \System\BaseClass;

class DistributorApplyController extends BaseClass {
    
    /**
     *   分销商申请表的增，删，改，查；
     *   分销商申请表字段的验证；
     *   memcache添加，获取，删除
     *   获取配置文件信息
     *   常用方法/System/database/BaseTable.class.php(部分案例见本文件最后)
     */
    
    /*
    申请一级分销商
     */
    public function primaryApply(){
        //-----------字段验证-----------
        $rule = [
            'phone'             =>['mobile'],
            'money'       =>['money'],
            'city'              =>[],
            //'area'              =>[],
            'name'              =>[],
        ];
        $this->V($rule);
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        if (isset($_POST['referrer'])) {
        	$this->V(['referrer'=>['mobile']]);
        	 //查询推荐人user_id
        	if (isset($_POST['referrer'])) {
        	$rephone = $this->table('user')->where(['mobile_phone'=>$_POST['referrer']])->get(['id'],true);//查询推荐人id
        	if (!$rephone) {
        	$this->R('',90002);//无此分销商
            }

        	 $data['parent_id']= $rephone['id'];
        	}
        }
        if (isset($_POST['remark'])) {
        	$data['remark']= $_POST['remark'];
        }
        if (isset($_POST['area'])) {
        	$data['area']= $_POST['area'];
        	$data['shop_name'] = $data['city'].$data['area'];
        }else{
        	$data['shop_name'] = $data['city'];
        }
    
        
        $phone = $this->table('distributor_apply')->where(['phone'=>$data['phone']])->get(['id'],true);
        if ($phone) {
        	$this->R('',90001);//此手机号已存在
        }
        $userID = $this->table('user')->where(['mobile_phone'=>$data['phone']])->get(['id'],false);
        if (!$userID) {
        	$this->R('',90000);//用户信息中无此手机号
        }
        $data['user_id'] = $userID;
        $data['type'] = 2;
        $data['apply_level']  = 1;//一级分销商
        $data['add_time']     = time();
    
        $apply = $this->table('distributor_apply')->save($data);
        if(!$apply){ 
            $this->R('',40001);
        }
    
        $this->R();
    }
    /**
    *申请二级分销商
    *推荐人是一级分销商，则二级分销商自动变为其下级
    *推荐人是二级分销商，则为推荐
    */
    public function secApply(){
        //-----------字段验证-----------
        $rule = [
            'phone'             =>['mobile'],
            'shop_name'         =>[],
            'money'       =>['isMoney'],
            'name'              =>[],
        ];
        $this->V($rule);
         //判断是否有输入推荐人
        if (isset($_POST['referrer'])) {
        	$this->V(['referrer'=>['mobile']]);
        	$rephone = $this->table('distributor_apply')->where(['phone'=>$_POST['referrer']])->get(['id','apply_level'],true);//查询推荐人id
        	if (!$rephone) {

        	$this->R('',90002);//无此分销商
            }

            $data['parent_id']= $rephone['id'];

            if ($rephone['apply_level']==2) {
            	$data['type']= 2;//推荐
            }else{
            	$data['type']= 1;//下级
            }
        }
        if (isset($_POST['remark'])) {
        	$data['remark']= $_POST['remark'];
        }
    
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $phone = $this->table('distributor_apply')->where(['phone'=>$data['phone']])->get(['id'],true);
        if ($phone) {
        	$this->R('',90001);//此手机号已存在
        }
        $userID = $this->table('user')->where(['mobile_phone'=>$data['phone']])->get(['id'],true);
        if (!$userID) {
        	$this->R('',90000);//用户信息中无此手机号
        }
        $data['user_id'] = $userID;
        $data['apply_level']  = 2;//二级分销商
        $data['add_time']     = time();

        $apply = $this->table('distributor_apply')->save($data);
        if(!$apply){
            $this->R('',40001);
        }
    
        $this->R();
    }
    /*
    通过申请
     */
    public function applyPass(){
        //-----------字段验证-----------
        $rule = [
            'id'                =>['egNum']
        ];
        $this->V($rule);
    
        $id = intval($_POST['id']);
        $distributor = $this->table('distributor_apply')->where(['id'=>$id,'is_on'=>1,'is_pass'=>0])->get(['id','phone','city','area'],true);
        
        if(!$distributor){
            $this->R('',70009);
        }

        $user['status'] = 2;
        $status = $this->table('user')->where(['mobile_phone'=>$distributor['phone']])->update($user);
        if(!$status){
            $this->R('',40001);
        }
        $data['is_pass']      = 1;
        $data['update_time']  = time();
        $apply = $this->table('distributor_apply')->where(['id'=>$id])->update($data);
        if(!$apply){ 
            $this->R('',40001);
        }
        if ($distributor['area']=="") {
        	$city['status'] = 1;
        	$city = $this->table('city')->where(['name'=>$distributor['city']])->update($city);
        	if(!$city){ 
        		$this->R('',40001);
        	}
        }else{
        	$area['status'] = 1;
        	$area = $this->table('area')->where(['name'=>$distributor['area']])->update($area);
        	if(!$area){ 
        		$this->R('',40001);
        	}
        }
        $this->R();
    }
    /**
     *后台手动添加一级分销商/直接成为一级分销商,无审核
     *此方法为凭空添加一级分销商(会员表,分销商申请表表中无此信息)
     */
    public function addDistributor(){
    	//-----------字段验证-----------
        $rule = [
            'mobile_phone'   =>['mobile'],
            'name'           =>[],
            'city'           =>[],
            //'area'           =>[],//一级分销商shop_name就是地区名
            //'remark'         =>[null,null,false],//备注
        ];

        $this->V($rule);
        if (isset($_POST['referrer'])) {

        	$this->V(['referrer'=>['mobile']]);
        }
        if (isset($_POST['remark'])) {
        	$distributor['remark']= $_POST['remark'];
        }
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        if (isset($_POST['area'])) {
        	$distributor['area']= $_POST['area'];
        	$distributor['shop_name'] = $data['city'].$distributor['area'];
        }else{
        	$distributor['shop_name'] = $data['city'];
        }
        //判断是否有输入推荐人
        if (isset($_POST['referrer'])) {
        	$rephone = $this->table('distributor_apply')->where(['phone'=>$_POST['referrer']])->get(['id'],true);//查询推荐人id
        	if (!$rephone) {
        	$this->R('',90002);//无此分销商
            }
        }
        $phone = $this->table('distributor_apply')->where(['phone'=>$data['mobile_phone']])->get(['id'],true);
        if ($phone) {
        	$this->R('',90001);//此手机号已存在
        }
        $userID = $this->table('user')->where(['mobile_phone'=>$data['mobile_phone']])->get(['id'],true);
        if ($userID) {
        	$this->R('',90001);//此手机号已存在
        }
         //查询推荐人user_id
        if (isset($_POST['referrer'])) {
        	$rephone = $this->table('user')->where(['mobile_phone'=>$_POST['referrer']])->get(['id'],true);//查询推荐人id
        	$distributor['parent_id']= $rephone['id'];
        	$user['user_refere']= $rephone['id'];
        }
        //插入user表
        $user['mobile_phone'] = $data['mobile_phone'];
        $user['password']     = md5(123456);//密码默认设置
        $user['status']       = 2;
        $user['add_time']     = time();
        $user = $this->table('user')->save($user);
        if(!$user){
            $this->R('',40001);
        }
        //插入distributor_apply表
        //查询此用户的user_id
        $userID = $this->table('user')->where(['mobile_phone'=>$data['mobile_phone']])->get(['id'],true);
        //写入
        $distributor['phone']= $data['mobile_phone'];
        $distributor['city'] = $data['city'];
        $distributor['name']= $data['name'];
        $distributor['apply_level']= 1;
        $distributor['is_pass']= 1;
        $distributor['type']= 2;//推荐
        $distributor['user_id']= $userID['id'];
        $distributor['add_time']     = time();
        $distributor = $this->table('distributor_apply')->save($distributor);
        if(!$user){
            $this->R('',40001);
        }
        $this->R();
    }
    /**
     *后台手动添加二级分销商/直接成为一级分销商,无审核
     *此方法为凭空添加二级分销商(会员表,分销商申请表表中无此信息)
     *推荐人是一级分销商，则二级分销商自动变为其下级
     *推荐人是二级分销商，则为推荐
     */
    public function addSecDistributor(){
    	//-----------字段验证-----------
        $rule = [
            'mobile_phone'   =>['mobile'],
            'name'           =>[],
            'shop_name'      =>[],
            //'remark'         =>[null,null,false],//备注
        ];

        $this->V($rule);
        if (isset($_POST['referrer'])) {

        	$this->V(['referrer'=>['mobile']]);
        }
        if (isset($_POST['remark'])) {
        	$distributor['remark']= $_POST['remark'];
        }
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        //判断是否有输入推荐人
        if (isset($_POST['referrer'])) {
        	$rephone = $this->table('distributor_apply')->where(['phone'=>$_POST['referrer']])->get(['id','apply_level'],true);//查询推荐人id
        	if (!$rephone) {
        	$this->R('',90002);//无此分销商
            }
            if ($rephone['apply_level']==2) {
            	$distributor['type']= 2;//推荐
            }else{
            	$distributor['type']= 1;//下级
            }
        }
        $phone = $this->table('distributor_apply')->where(['phone'=>$data['mobile_phone']])->get(['id'],true);
        if ($phone) {
        	$this->R('',90001);//此手机号已存在
        }
        $userID = $this->table('user')->where(['mobile_phone'=>$data['mobile_phone']])->get(['id'],true);
        if ($userID) {
        	$this->R('',90001);//此手机号已存在
        }
         //查询推荐人user_id
        if (isset($_POST['referrer'])) {
        	$rephone = $this->table('user')->where(['mobile_phone'=>$_POST['referrer']])->get(['id'],true);//查询推荐人id
        	$distributor['parent_id']= $rephone['id'];
        	$user['user_refere']= $rephone['id'];
        }
        //插入user表
        $user['mobile_phone'] = $data['mobile_phone'];
        $user['password']     = md5(123456);//密码默认设置
        $user['status']       = 2;
        $user['add_time']     = time();
        $user = $this->table('user')->save($user);
        if(!$user){
            $this->R('',40001);
        }
        //插入distributor_apply表
        //查询此用户的user_id
        $userID = $this->table('user')->where(['mobile_phone'=>$data['mobile_phone']])->get(['id'],true);
        //写入
        $distributor['phone']= $data['mobile_phone'];
        $distributor['name']= $data['name'];
        $distributor['shop_name']= $data['shop_name'];
        var_dump($distributor['name']);
        exit();
        $distributor['apply_level']= 2;//二级分销商
        $distributor['is_pass']= 1;
        $distributor['user_id']= $userID['id'];
        $distributor['add_time']     = time();
        $distributor = $this->table('distributor_apply')->save($distributor);
        if(!$user){
            $this->R('',40001);
        }
        $this->R();
    }
    /**
     * 分销商申请列表___查询列表(一二级分销商分开显示/由前台传apply_level的值来区分)
     * is_pass=0:未通过的分销商、is_pass=1:分销商
     * 编号，会员名称，手机号码，级别，加入时间
     */
    public function distributorOneList(){
    	$rule = [
            'apply_level'        =>['egNum'],
            'is_pass'            =>['in',[0,1],true]//是否是通过的分销商
        ];
        $this->V($rule);
        $apply_level = intval($_POST['apply_level']);
        $is_pass = intval($_POST['is_pass']);
        $pageInfo = $this->P();
        $dataClass = $this->H('Distributor');
        //查询并分页
        $where = 'A.is_pass = '.$is_pass.' and A.apply_level = '.$apply_level;
        $distributorlist = $dataClass->getDistributorL($where);
        $distributorpage = $this->getOnePageData($pageInfo,$dataClass,'getDistributorL','getDistributorLListLength',[$where],true);
        if($distributorpage){
            foreach ($distributorpage as $k=>$v){
                $distributorpage[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }
        }else{
            $distributorpage = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['distributorpage'=>$distributorpage,'pageInfo'=>$pageInfo]);
    }
    /**
     * 搜索分销商
     * 通过不通过都搜索出来
     * 搜索条件：(手机号码，姓名，商店名称)
     */
    public function searchDistributor(){
        $rule = [
            'search'       =>[],
        ];
        $this->V($rule);
         foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $a = $_POST['search'];
        $search = 'is_on = 1 and concat(phone,name,shop_name) like "%'.$a.'%"';
        $pageInfo = $this->P();

        $file = ['id','name','phone','shop_name','apply_level','money','is_pass','city','area','type','add_time'];

        $class = $this->table('distributor_apply')->where($search)->order('add_time asc');
        //查询并分页
        $userpage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],true);
        if($userpage ){
            foreach ($userpage  as $k=>$v){
                $userpage [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }
        }else{
            $userpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['userpage '=>$userpage,'pageInfo'=>$pageInfo]);
    }
    /**
     * 一级分销商省市区三级联动设置
     */
    public function city(){
    	$rule = [
            'province'        =>[],//省份传进来
        ];
        $this->V($rule);
        $code = $this->table('province')->where(['name'=>$_POST['province']])->get(['code'],true);
        if(!$code){
            $this->R('',70009);
        }
        $city = $this->table('city')->where(['provincecode'=>$code['code']])->get(['name','status'],false);

        $this->R(['city'=>$city]);
    }
    public function area(){
    	$rule = [
            'city'        =>[],//城市传进来
        ];
        $this->V($rule);
        $code = $this->table('city')->where(['name'=>$_POST['city']])->get(['code'],true);
        if(!$code){
            $this->R('',70009);
        }
        $city = $this->table('area')->where(['citycode'=>$code['code']])->get(['name','status'],false);

        $this->R(['city'=>$city]);
    }
    /**
     *删除一条分销商（设置数据库字段为0，相当于回收站）
     */
    public function distributorOneDelete(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $distributor = $this->table('distributor_apply')->where(['id'=>$id,'is_on'=>1])->get(['id','city','area','is_pass'],true);

        if(!$distributor){
            $this->R('',70009);
        }
    
        $delete = $this->table('distributor_apply')->where(['id'=>$id])->update(['is_on'=>0]);
        if(!$delete){
            $this->R('',40001);
        }
        if ($distributor['is_pass'] == 1) {
        	if ($distributor['area']=="") {
        		$city['status'] = 0;
        		$city = $this->table('city')->where(['name'=>$distributor['city']])->update($city);
        		if(!$city){
        			$this->R('',40001);
        		}
        	}else{
        		$area['status'] = 0;
        		$area = $this->table('area')->where(['name'=>$distributor['area']])->update($area);
        		if(!$area){
        			$this->R('',40001);
        		}
        	}
        }
        $this->R();
    }
    /**
     *删除一条分销商（清除数据）
     */
    public function distributorOneDeleteconfirm(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $distributor = $this->table('distributor_apply')->where(['id'=>$id,'is_on'=>1])->get(['id','city','area','is_pass'],true);
    
        if(!$distributor){
            $this->R('',70009);
        }
    
        $deleter = $this->table('distributor_apply')->where(['id'=>$id])->delete();
        if(!$delete){
            $this->R('',40001);
        }
        if ($distributor['is_pass'] == 1) {
        	if ($distributor['area']=="") {
        		$city['status'] = 0;
        		$city = $this->table('city')->where(['name'=>$distributor['city']])->update($city);
        		if(!$city){
        			$this->R('',40001);
        		}
        	}else{
        		$area['status'] = 0;
        		$area = $this->table('area')->where(['name'=>$distributor['area']])->update($area);
        		if(!$area){
        			$this->R('',40001);
        		}
        }
        }
        $this->R();
    }
}