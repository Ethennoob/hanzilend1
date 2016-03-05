<?php
namespace AppMain\model;
use System\BaseModel;
class UserModel extends BaseModel{
    protected $table='user_base';  //定义数据表
    protected  $autoConnectDB=false;
    

    /**
     * 获取用户粉丝列表
     * @param number $start
     * @param number $end
     * @param unknown $userID
     * @return string
     */
    public function getFansList($start=0,$end=14,$userID){
		$pSize=($end+1)-$start;
        
        $get=['user*->nickname','user*->head_img','user*->id'];
       	$getField=['nickname','head_img','id'];
       	$getLength=count($get);
       
       	$option=array(
        // 	        'by' => '',
        		'limit' => [$start,$pSize],
        		'get' => $get,
        		'sort' => 'Desc',
        		// 	        'alpha' => '',
        // 	        'store' => '',
       	);
        
        $result=$this->S('redis')->sort('fans'.$userID, $option);
        //dump($result);
        if ($result){
        	$list=$this->H('Tools')->tranRedisSortField($result,$getLength,$getField);
        }
        else {
        	$list=false;
        }

        return $list;
    }
    
    /**
     * 获取用户粉丝长度
     * @param unknown $userID
     * @return unknown
     */
    public function getFansListLength($userID){
        $length=$this->S('redis')->sCard('fans'.$userID);
    
        return $length;
    }
    
    /**
     * 获取用户粉丝列表
     * @param number $start
     * @param number $end
     * @param unknown $userID
     * @return string
     */
    public function getFollowList($start=0,$end=14,$userID){
       $pSize=($end+1)-$start;
        
        
       $get=['user*->nickname','user*->head_img','user*->id'];
       $getField=['nickname','head_img','id'];
       $getLength=count($get);
       
       $option=array(
        // 	        'by' => '',
        		'limit' => [$start,$pSize],
        		'get' => $get,
        		'sort' => 'Desc',
        		// 	        'alpha' => '',
        // 	        'store' => '',
       );
        
        $result=$this->S('redis')->sort('follow'.$userID, $option);
        
        if ($result){
        	$list=$this->H('Tools')->tranRedisSortField($result,$getLength,$getField);
        }
        else {
        	$list=false;
        }

        return $list;
    }
    
    /**
     * 获取用户关注长度
     * @param unknown $userID
     * @return unknown
     */
    public function getFollowListLength($userID){
        $length=$this->S('redis')->sCard('follow'.$userID);
    
        return $length;
    }
    
    
    /**
     * 添加关注
     * @param unknown $userID
     * @param unknown $followID
     */
    public function addFollw($userID,$followID){
        $this->S('redis')->multi();
        
        //关注
        $this->S('redis')->sadd('follow'.$userID,$followID);
        //用户关注数+1
        $this->S('redis')->hincrby('user'.$userID, 'follow_num', 1);
        
        //被关注者添加粉丝
        $this->S('redis')->sadd('fans'.$followID,$userID);
        //用户粉丝数+1
        $this->S('redis')->hincrby('user'.$followID, 'fans_num', 1);
        
        $result=$this->S('redis')->exec();
        return $result;
    }
    
    /**
     * 取消关注
     * @param unknown $userID
     * @param unknown $followID
     */
    public function delFollw($userID,$followID){
        $this->S('redis')->multi();
    
        //取消关注
        $this->S('redis')->sRem('follow'.$userID,$followID);
        //用户关注数-1
        $this->S('redis')->hincrby('user'.$userID, 'follow_num', -1);
    
    
        //被关注者添加粉丝
        $this->S('redis')->sRem('fans'.$followID,$userID);
        //用户粉丝数+1
        $this->S('redis')->hincrby('user'.$followID, 'fans_num', -1);
    
        $result=$this->S('redis')->exec();

        return $result;
    }
    
    /**
     * 查询是否关注
     * @param int | array $userID
     * @param int | array  $followID
     * @return bool | array
     */
    public function isFollow($userID,$followID){
    	if (!is_array($userID) && !is_array($followID)){
    		$isFollow=$this->S('redis')->sisMembers('follow'.$userID,$followID);
    	}
    	elseif (is_array($userID)){
    		$this->S('redis')->multi();
    		foreach ($userID as $key => $v){
    			$this->S('redis')->sisMembers('follow'.$v,$followID);
    		}
    		$isFollow=$this->S('redis')->exec();
    	}
    	elseif(is_array($followID)){
    		$this->S('redis')->multi();
    		foreach ($followID as $key => $v){
    			$this->S('redis')->sisMembers('follow'.$userID,$v);
    		}
    		$isFollow=$this->S('redis')->exec();
    	}
    	else{
    		$this->S('redis')->multi();
    		foreach ($userID as $key => $v){
    			$this->S('redis')->sisMembers('follow'.$v,$followID[$key]);
    		}
    		$isFollow=$this->S('redis')->exec();
    	}
    	
    	return $isFollow;
    }
    
    
    
}