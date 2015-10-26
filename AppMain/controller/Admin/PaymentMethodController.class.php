<?php
/**
 * 盛世分销系统---支付方式管理类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-10-25 11:06:54
 * @version $Id$
 */

namespace AppMain\controller\Admin;
use \System\BaseClass;

class PaymentMethodController extends BaseClass {

	/********************支付方式*******************************/
    /**
     *   支付方式数据的增，删，改，查；
     *   支付方式字段的验证；
     *   memcache添加，获取，删除
     *   获取配置文件信息
     *   常用方法/System/database/BaseTable.class.php(部分案例见本文件最后)
     */
    
    /*
    支付方式添加活动
     */
    public function paymentMethodAdd(){
        //-----------字段验证-----------
        $rule = [
            'pay_name'       =>[],
            'pay_desc'       =>[],
            'pay_mode'       =>[],
            //'pay_icon'       =>[],//图片
        ];
        $this->V($rule);
    
        foreach ($rule as $k=>$v){
            $data[$k] = $_POST[$k];
        }

        $fileName = $_FILES['pay_icon']['name'];
        $upload=new \System\lib\Upload\Upload();
        $upload->exts      =     array('icon','ico');// 设置附件上传类型
        $upload->rootPath = '../TempImg/';
        $upload->savePath  = '/pay_icon/';   // 设置附件上传（子）目录
        //$upload->saveName = explode('.', $saveName)[0];
        
        // 开启子目录保存 并以日期（格式为Ymd）为子目录
        $upload->autoSub = false;
        $upload->subName = array('date','Ymd');
        
        // 上传文件
        $info=$upload->uploadOne($_FILES['pay_icon']);
        if(!$info){
            $errorMsg=$upload->getError();
            $this->R(['errorMsg'=>$errorMsg],'40019');
        }

        $data['pay_icon']          = $info['savename'];
        $data['add_time']     = time();
    
        $payment_method = $this->table('payment_method')->save($data);
        if(!$payment_method){
            $this->R('',40001);
        }
    
        $this->R();
    }

    /**
     * 支付方式列表___查询列表
     */
    public function paymentMethodOneList(){
        $pageInfo = $this->P();

        $class = $this->table('payment_method')->where(['is_on'=>1])->order('add_time desc');
        //查询并分页
        $payment_methodlist = $this->getOnePageData($pageInfo,$class,'get','getListLength',null,false);
        if($payment_methodlist){
            foreach ($payment_methodlist as $k=>$v){
                $payment_methodlist[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
                $payment_methodlist[$k]['update_time'] = date('Y-m-d H:i:s',$v['update_time']);
            }
        }else{
            $payment_methodlist = null;
        }
        //返回数据，参见System/BaseClass.class.php方法
        $this->R(['payment_methodlist'=>$payment_methodlist,'pageInfo'=>$pageInfo]);
    }

     /**
     * 支付方式查询___一条数据
     */
    public function paymentMethodOneDetail(){
    
        $this->V(['id'=>['egNum',null,true]]);

        $id = intval($_POST['id']);
        //查找memcache缓存
        $payment_methodDetail=$this->S()->get('payment_method_'.$id);
        if (!$payment_methodDetail){
            //查询一条数据
            $payment_method = $this->table('payment_method')->where(['is_on'=>1,'id'=>$id])->get(null,true);
            if(!$payment_method){
                $this->R('',70009);
            }

            $payment_method['update_time'] = date('Y-m-d H:i:s',$payment_method['update_time']);
            $payment_method['add_time'] = date('Y-m-d H:i:s',$payment_method['add_time']);
            //设置memcache缓存，serialize（序列化），兼容window和linux系统
            //$this->S()->set('payment_method_'.$id , serialize($payment_methodDetail),60*60);
        }
        else{
            //设置memcache缓存，unserialize（反序列化），兼容window和linux系统
            $payment_methodDetail=unserialize($payment_methodDetail);
        }
        
        $this->R(['payment_method'=>$payment_method]);
    }
    /**
     * 修改一条支付方式数据
     */
    public function paymentMethodOneEdit(){
        $rule = [
            'pay_name'       =>[],
            'pay_desc'       =>[],
            'pay_mode'       =>[],
            //'pay_icon'       =>[],//图片
        ]; 
        $this->V($rule);
        $id = intval($_GET['id']);
        $payment_method = $this->table('payment_method')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
        if(!$payment_method){
            $this->R('',70009);
        }
    
        unset($rule['id']);
        foreach ($rule as $k=>$v){
            if(isset($_POST[$k])){
                $data[$k] = $_POST[$k];
            }
        }
        
        $data['update_time']  = time();
    
        $payment_method = $this->table('payment_method')->where(['id'=>$id])->update($data);
        if(!$payment_method){
            $this->R('',40001);
        }
        //活动更改了内容，删除活动信息的memcache,缓存
        $this->S()->delete('payment_method_'.$id);
        $this->R();
    }
    /**
     *删除一条文章数据（设置数据库字段为0，相当于回收站）
     */
    public function paymentMethodOneDelete(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $payment_method = $this->table('payment_method')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
    
        if(!$payment_method){
            $this->R('',70009);
        }
    
        $payment_method = $this->table('payment_method')->where(['id'=>$id])->update(['is_on'=>0]);
        if(!$payment_method){
            $this->R('',40001);
        }
        $this->S()->delete('payment_method_'.$id);
        $this->R();
    }
    /**
     *删除一条支付方式（清除数据）
     */
    public function paymentMethodOneDeleteconfirm(){
    
        $this->V(['id'=>['egNum',null,true]]);
        $id = intval($_POST['id']);
         
        $payment_method = $this->table('payment_method')->where(['id'=>$id,'is_on'=>1])->get(['id'],true);
    
        if(!$payment_method){
            $this->R('',70009);
        }
    
        $payment_method = $this->table('payment_method')->where(['id'=>$id])->delete();
        if(!$payment_method){
            $this->R('',40001);
        }
        $this->S()->delete('payment_method_'.$id);
        $this->R();
    }
}