<?php
/**
 * 盛世分销系统---分销商管理类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-11-06 15:07:35
 * @version $Id$
 */

namespace AppMain\controller\Api;
use \System\BaseClass;

class DistributorController extends BaseClass {
	
    /**
     * 查看分销商信息
     * (通过的分销商)
     * 头像，真实姓名，可用余额，可用积分，手机号码，openid
     */
    public function distributorOneDetail(){
    	$this->V(['id'=>['egNum',null,true]]);

        $id = intval($_POST['id']);

            //查询一条数据
            $dataClass = $this->H('Distributor');

            $where = 'A.id = '.$id;
            $distributor = $dataClass->getDistributor($where);

        $this->R(['distributor'=>$distributor]);
    }
    /**
     * 分销商信息修改
     */
    public function distributorOneEdit(){
    	$rule = [
    	    'id'              =>['egNum'],
            'name'            =>[],
            'phone'           =>['mobile']
        ];
        $this->V($rule);
        $id = intval($_POST['id']);

        $distributor = $this->table('distributor_apply')->where(['id'=>$id,'is_on'=>1,'is_pass'=>1])->get(['id','user_id'],true);
        if(!$distributor){
            $this->R('',70009);
        }

       unset($rule['id']);
       foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
       $data['update_time'] = time();
       $user['update_time'] = time();

       $user['mobile_phone'] = $data['phone'];
       $user = $this->table('user')->where(['id'=>$id])->update($user);
       if(!$user){
            $this->R('',40001);
        }
        $distributor = $this->table('distributor_apply')->where(['id'=>$id])->update($data);
        if(!$distributor){
            $this->R('',40001);
        }
        //活动更改了内容，删除活动信息的memcache,缓存
        $this->S()->delete('user_'.$id);
        $this->R();
    }
    /**
     * 提现申请
     */
    public function withdrawApply(){
    	$rule = [
    	    'user_id'           =>['egNum'],//用户id
            'alipay'            =>[],//支付宝账号
            'getcash_password'  =>[],//是否是通过的分销商
            'withdrawals_name'  =>[],
            'money' =>['money'],
        ];
        $this->V($rule);
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $pwd = $this->table('user')->where(['id'=>$data['user_id'],'is_on'=>1])->get(['getcash_password','is_froze'],true);
        if ($pwd['is_froze']==1) {
        		$this->R('',70004);//账户冻结无法提现
        }
        if (md5($data['getcash_password'])!==$pwd['getcash_password']) {
        		$this->R('',70003);//提现密码错误
        }
        $info = $this->table('user')->where(['id'=>$data['user_id'],'is_on'=>1])->get(['openid_line'],true);
        if (!$info) {
        	$data['openid'] = null;
        }
        $data['add_time'] = time();
        $apply = $this->table('withdrawals_apply')->save($data);
        if (!$apply) {
            $this->R('',40001);
        }

        $this->R();
    }
    /**
     * 搜索提现表
     * 通过不通过都搜索出来
     * 搜索条件：(支付宝账号，姓名，金额)
     */
    public function searchWithdraw(){
        $rule = [
            'search'       =>[],
        ];
        $this->V($rule);
         foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }
        $a = $_POST['search'];
        $search = 'is_on = 1 and concat(alipay,withdrawals_name,money) like "%'.$a.'%"';
        $pageInfo = $this->P();

        $field = ['id','user_id','withdrawals_name','alipay','openid','money','add_time','confirm_time'];

        $class = $this->table('withdrawals_apply')->where($search)->order('add_time asc');
        //查询并分页
        $withdrawpage = $this->getOnePageData($pageInfo,$class,'get','getListLength',[$field],true);
        if($withdrawpage ){
            foreach ($withdrawpage  as $k=>$v){
                $withdrawpage [$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $withdrawpage [$k]['confirm_time'] = date('Y-m-d H:i:s',$v['confirm_time']);
            }
        }else{
            $withdrawpage  = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['userpage '=>$withdrawpage,'pageInfo'=>$pageInfo]);
    }
    /**
     * 分销商冻结
     * is_froze = 1 即冻结
     */
    public function distributorFroze(){
       $rule = [
            'id'       =>['egNum'],
        ];
        $this->V($rule);
        $id = intval($_POST['id']);

        $userID = $this->table('distributor_apply')->where(['id'=>$id,'is_on'=>1,'is_pass'=>1])->get(['user_id'],true);
        if(!$userID){
            $this->R('',70009);
        }
        
        $data['is_froze'] = 1;
        $user = $this->table('user')->where(['id'=>$userID['user_id']])->update($data);
        if(!$user){
            $this->R('',40001);
        }

        $this->R();
    }
    /**
     * 佣金明细
     * 变动金额，变动原因，变动时间，订单id
     * uid,1=获得佣金,2=提现
     * ////可能以后会佣金直接消费，待开发
     */
    public function commissionDetail(){
        $rule = [
            'id'       =>['egNum'],
        ];
        $this->V($rule);
        $id = intval($_POST['id']);
        $pageInfo = $this->P();
        //table()->query()直接执行语句
        $commissionpage = $this->table()->query('(select id ,money,uid,add_time from distribution_cission WHERE is_on=1 and user_id='.$id.')union all
            (select id ,money,uid,add_time  from withdrawals_apply WHERE is_on=1 and user_id='.$id.') ORDER BY add_time desc');

        foreach ($commissionpage as $k => $v) {
            $commissionpage[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            if ($v['uid']==1) {
                $sum = $sum + $v['money'];//总佣金=所获全部佣金(提现不减)
            }
        }
        $pageInfo->psize = isset($_REQUEST["psize"]) ? $_REQUEST["psize"] : 15;
        $pn = isset($_REQUEST["pn"]) ? $_REQUEST["pn"] : 1;
        if (!$pn)
            $pn = "1";
        $pageInfo->num = intval($pn);
        $pageInfo->dataSize = count($commissionpage);
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['commissionpage'=>$commissionpage,'pageInfo'=>$pageInfo,'总佣金'=>$sum ]);
    }
    /**
     * 确认提现
     * 生成confirm_time，支付宝提现
     */
    public function alipayWithdraw(){
         $rule = [
            'id'       =>['egNum'],
        ];
        $this->V($rule);
        $id = intval($_POST['id']);

        $withdraw = $this->table('withdrawals_apply')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
        if(!$withdraw){
            $this->R('',70009);
        }

        $withdraw['update_time'] = time();
        $withdraw['confirm_time'] = time();
        $withdraw = $this->table('withdrawals_apply')->where(['id'=>$id])->update($withdraw);
        if(!$withdraw){
            $this->R('',40001);
        }
        //支付宝接口
        $this->R();
    }
}