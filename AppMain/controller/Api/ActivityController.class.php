<?php

namespace AppMain\controller\Api;
use \System\BaseClass;

class ActivityController extends  BaseClass {
     
    /**
     *  连表操作 调用AppMain/helper
     */
     
    /**
     * 获取用户活动推荐列表
     */
    public function getActivityRecommended(){	
        $this->V(['activity_id'=>['egNum']]);
        $activityID=intval($_POST['activity_id']);
    	$userID=$this->user['userid'];
 
    	$pageInfo=$this->P();
    	//调用helper类
    	$activityClass=$this->H('Activity');
    	$where='A.user_id='.$userID.' and B.is_on=1';
    	$userRecommended=$activityClass->getActivityOne($where);
    	$rate = 0;
    	
    	if(!$userRecommended){  	    
    	    $recommendCount = 0;
    	    $recommendList = null;
    	}
    	else{
	    	$recommendList = $userRecommended;
	    	$recommendCount = count($userRecommended);   		
    		foreach ($recommendList as $k=>$v){
    	    	if( $v['is_follow']!=0 &&  $v['realname'] != null && $v['idcard'] != null && $v['is_bank_bind'] !=0 && $v['phone'] !=null){
    	            $recommendList[$k]['isYes'] = 1;
    	            if($recommendList[$k]['status'] == 1){
    	                $rate += $recommendList[$k]['rate'];
    	            }
    	        }else{
    	            $recommendList[$k]['isYes'] = 0;
    	        }  	        
    	    }  
    	}   
    	$activitystatu = $this->table('activity_share_1')->where(['id'=>$activityID,'is_on'=>1])->get(['end_time','start_time','is_show'],true);
    	
        $this->R(['countRate'=>$rate,'recommendCount'=>$recommendCount,'recommendList'=>$recommendList,'activitystatu'=>$activitystatu]);
    }
    
    
}
