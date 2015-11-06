<?php
/**
 * 盛世分销系统---会员类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-10-26 17:43:13
 * @version $Id$
 */

namespace AppMain\controller\Api;
use \System\BaseClass;

class UserController extends Baseclass {

	/*
	会员PC注册方法
	 */
	public function register(){
		//-----------字段验证-----------
        $rule = [
            'mobile_phone'   =>['mobile'],
            'password'       =>[],
        ];
        $this->V($rule);
    
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $data['password']     =md5($data['password']);
        $data['update_time']  = time();
        $data['add_time']     = time();
    
        $user = $this->table('user')->save($data);
        if(!$user){
            $this->R('',40001);
        }
    
        $this->R();
    }
    /*
    添加会员个人信息
     */
    public function userInfoAdd(){
    	//-----------字段验证-----------
        $rule = [
            'user_name'       =>[],
            'email'           =>['email'],
            'sex'             =>[],
            'birthday'        =>[],
            'nickname'        =>[],
            'qq'              =>['egNum'],
            'office_phone'    =>['telephone'],
            //'user_img'       =>[],//图片
        ];
        $this->V($rule);
    
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }

        //图片上传
        $pictureName = $_FILES['user_img'];
        $imgarray = $this->H('PictureUpload')->pictureUpload($pictureName,'avatar',false);


        $data['user_img'] = $imgarray['a1'];
        $data['add_time']     = time();
    
        $user = $this->table('User')->save($data);
        if(!$user){
            $this->R('',40001);
        }
    
        $this->R();
    }

    /**
     * 会员列表___查询全列表
     */
    public function userAllList(){
        $this->V(['is_show'=>['in',[0,1],false]]);
        $where=['is_on'=>1];
        //$this->queryFilter，拼接查询字段
        $whereFilter=$this->queryFilter($where,['is_show']);

        $pageInfo = $this->P();

        $class = $this->table('user')->where($whereFilter)->order('add_time desc');
        //查询并分页
        $userlist = $this->getOnePageData($pageInfo,$class,'get','getListLength',null,false);
        if($userlist){
            foreach ($userlist as $k=>$v){
                $userlist[$k]['last_ip'] = long2ip($v['last_ip']);
                $userlist[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $userlist[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
        }else{
            $userlist = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['userlist'=>$userlist,'pageInfo'=>$pageInfo]);
    }
    /*
    后台会员列表信息
     */
    public function adminUserList(){
        $this->V(['is_show'=>['in',[0,1],false]]);
        $where=['is_on'=>1];
        //$this->queryFilter，拼接查询字段
        $whereFilter=$this->queryFilter($where,['is_show']);

        $pageInfo = $this->P();
        $file = ['id','user_name','status','user_img','mobile_phone','add_time','mall_own_private','is_follow'];

        $class = $this->table('user')->where($whereFilter)->order('add_time desc');

        //查询并分页
        $userpage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],false);
        if($userpage ){
            foreach ($userpage  as $k=>$v){
                $userpage [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $status = $this->table('user')->where(['is_on'=>1,'id'=>$v['mall_own_private']])->get(['user_name','status'],true);
                $userpage [$k]['mall_status'] = $status['status'];
                $userpage [$k]['mall_name'] = $status['user_name'];
            }
        }else{
            $userpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['userpage '=>$userpage,'pageInfo'=>$pageInfo]);
    }
    /*
    后台查询单条会员信息
     */
    public function adminUserOneDetail(){
       $this->V(['id'=>['egNum',null,true]]);

        $id = intval($_POST['id']);

            //查询一条数据
            $dataClass = $this->H('User');
            $where='A.id='.$id;
            $user = $dataClass->getUser($where);
            foreach ($user as $k => $v) {
                $status = $this->table('user')->where(['is_on'=>1,'id'=>$v['mall_own_private']])->get(['user_name','status'],true);
                $user['mall_status'] = $status['status'];
                $user['mall_name'] = $status['user_name'];
            }
            if(!$user){
                $this->R('',70009);
            }
            $this->R(['user'=>$user]);

    }
    /*
    后台显示会员关系分页列表
     */
    public function userRelationList(){
        $where=['is_on'=>1];
        $pageInfo = $this->P();
        //查询一条数据
        $dataClass = $this->H('UserRelation');
        $order='add_time desc';
        $userlist = $dataClass->getUserRelation(null,null,null,false,$order);

        //查询并分页
        $userpage = $this->getOnePageData($pageInfo,$dataClass,'getUserRelation','getUserRelationListLength',[null,null,null,false,$order],true);
 
        if($userpage ){
            foreach ($userpage  as $k=>$v){
                $userpage [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $status = $this->table('user')->where(['is_on'=>1,'id'=>$v['mall_own_private']])->get(['user_name','status'],true);
                $userpage [$k]['mall_status'] = $status['status'];
                $userpage [$k]['mall_name'] = $status['user_name'];
            }   
        }else{
            $userpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['userpage '=>$userpage,'pageInfo'=>$pageInfo]);

    }
    /*
    查看个人信息__一条全数据
     */
    public function userOneAllDetail(){
    
        $this->V(['id'=>['egNum',null,true]]);

        $id = intval($_POST['id']);

            $user = $this->table('user')->where(['is_on'=>1,'id'=>$id])->get(null,true);
            if(!$user){
                $this->R('',70009);
            }
            $user['last_ip'] = long2ip($user['last_ip']);
            $user['update_time'] = date('Y-m-d H:i:s',$user['update_time']);
            $user['add_time'] = date('Y-m-d H:i:s',$user['add_time']);

            $this->R(['user'=>$user]);

        
        //$this->R(['user'=>$user]);
    }
    /**
     * 冻结此账户
     */
    public function userFroze(){
       $rule = [
            'id'       =>['egNum'],
        ];
        $this->V($rule);
        $id = intval($_POST['id']);

        $user = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
        if(!$user){
            $this->R('',70009);
        }
        unset($rule['id']);
        foreach ($rule as $k=>$v){
            if(isset($_POST[$k])){
                $data[$k] = $_POST[$k];
            }
        }
        $data['is_froze'] = 1;
        $user = $this->table('user')->where(['id'=>$id])->update($data);
        if(!$user){
            $this->R('',40001);
        }
        //活动更改了内容，删除活动信息的memcache,缓存
        $this->S()->delete('user_'.$id);
        $this->R();
    }
    /**
     * 修改个人信息
     */
    public function userOneEdit(){
       $rule = [
            'user_name'       =>[],
            'email'           =>['email'],
            'sex'             =>[],
            'birthday'        =>[],
            'nickname'        =>[],
            'qq'              =>['egNum'],
            'office_phone'    =>['telephone'],
            //'user_img'       =>[],//图片
        ];
        $this->V($rule);
        $id = intval($_POST['id']);

        $user = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
        if(!$user){
            $this->R('',70009);
        }

        unset($rule['id']);
        foreach ($rule as $k=>$v){
            if(isset($_POST[$k])){
                $data[$k] = $_POST[$k];
            }
        }
       //图片上传
        $pictureName = $_FILES['user_img'];
        $imgarray = $this->H('PictureUpload')->pictureUpload($pictureName,'avatar',false);
        //删除图片文件
        $pic_url = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['user_img'],true);
        foreach ($pic_url as $key => $v) {
             $delete = unlink("../html".$v);
         }
        if (!$delete) {
            $this->R('',40020);
        }
        $data['user_img'] = $imgarray['a1'];

        $user = $this->table('user')->where(['id'=>$id])->update($data);
        if(!$user){
            $this->R('',40001);
        }
        //活动更改了内容，删除活动信息的memcache,缓存
        $this->S()->delete('user_'.$id);
        $this->R();
    }
    /*
    修改密码
     */
    public function resetPassword(){
        $rule = [
            'oldPassword'    =>[],
            'newPassword'       =>[],
        ];
        $this->V($rule);
        $id = intval($_POST['id']);

        $user = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
            if(!$user){
                $this->R('',70009);
            }

        $password = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['password'],false);//拿数据库密码
        $oldPassword = md5($_POST['oldPassword']);

            if ($oldPassword === $password) {//与表单旧密码匹配
            $data['password'] = md5($_POST['newPassword']);
            $resetPassword = $this->table('user')->where(['id'=>$id])->update($data);//插入新密码
            if(!$resetPassword){
                $this->R('',40001);
            }
               $this->R();
            }else{
                $this->R('',70010);
            }

    }
    /*
    搜索会员--查询条件(名称，商城，微店，注册时间，手机号，区域)
     */
    public function userSearch1(){
        $rule = [
            'search'       =>[],
        ];
        $this->V($rule);
         foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $a = $_POST['search'];
        $search = 'A.is_on = "1" and concat(A.user_name,A.add_time,A.mobile_phone,A.city,B.shop_name) like "%'.$a.'%"';
        $pageInfo = $this->P();
        $dataClass = $this->H('User');
        //$file = ['id','user_name','status','user_img','mobile_phone','add_time','mall_own_private','is_follow'];

        //$order = 'add_time desc';
        
        $user = $dataClass->getUserSearch($search);

        //查询并分页
        $userpage = $this->getOnePageData($pageInfo,$dataClass,'getUserSearch','getUserSearchListLength',[$search],true);
        if($userpage ){
            foreach ($userpage  as $k=>$v){
                $userpage [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $status = $this->table('user')->where(['is_on'=>1,'id'=>$v['mall_own_private']])->get(['user_name','status'],true);
                $userpage [$k]['mall_status'] = $status['status'];
            }
        }else{
            $userpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['userpage '=>$userpage,'pageInfo'=>$pageInfo]);
    }
    /*
    搜索会员--查询条件(名称，商城，微店，注册时间，手机号，区域)
     */
    public function userSearch(){
        $rule = [
            'search'       =>[],
        ];
        $this->V($rule);
         foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $a = $_POST['search'];
        $search = 'is_on = "1" and concat(user_name,add_time,mobile_phone,city) like "%'.$a.'%"';
        $pageInfo = $this->P();

        $file = ['id','user_name','status','user_img','mobile_phone','add_time','mall_own_private'];

        $class = $this->table('user')->where($search)->order('add_time asc');
        //查询并分页
        $userpage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$file],true);
        if($userpage ){
            foreach ($userpage  as $k=>$v){
                $userpage [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $status = $this->table('distributor_apply')->where(['is_on'=>1,'user_id'=>$v['mall_own_private']])->get(['shop_name'],true);
                $userpage [$k]['parent_mall'] = $status['shop_name'];
                if ($v['status']!=0) {
                    $where = 'user_id ='.$v['id'];
                    $class = $this->table('distributor_apply')->where($where)->get(['shop_name'],true);
                    $userpage [$k]['shop_name'] = $class['shop_name'];
                }
                unset($userpage[$k]['mall_own_private']);
            }
        }else{
            $userpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['userpage '=>$userpage,'pageInfo'=>$pageInfo]);
    }
	/*
    删除一条会员信息数据（设置数据库字段为0，相当于回收站）
     */
    public function userOneDelete(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $user = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
    
        if(!$user){
            $this->R('',70009);
        }
    
        $user = $this->table('user')->where(['id'=>$id])->update(['is_on'=>0]);
        if(!$user){
            $this->R('',40001);
        }
        $this->S()->delete('user_'.$id);
        $this->R();
    }
    /**
     *删除一条用户信息（清除数据）
     */
    public function userOneDeleteconfirm(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $user = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
    
        if(!$user){
            $this->R('',70009);
        }
        //删除图片文件
        $pic_url = $this->table('user')->where(['id'=>$id,'is_on'=>1])->get(['Pic'],true);
        foreach ($pic_url as $key => $v) {
             $delete = unlink("../html".$v);
         }
        if (!$delete) {
            $this->R('',40020);
        }

        $user = $this->table('user')->where(['id'=>$id])->delete();
        if(!$user){
            $this->R('',40001);
        }
        $this->S()->delete('user_'.$id);
        $this->R();
    }
    
}