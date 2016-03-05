<?php
namespace AppMain\model;
use System\BaseModel;
class UserArticleCollectModel extends BaseModel{
    protected $table='user_article_collect';  //定义数据表
    
    /**
     * 添加文章收藏
     * @param unknown $data
     * @return boolean
     */
    public function addCollect($data){
        $result=$this->save($data);
        return $result;
    }
    
    /**
     * 获取一条
     * @param unknown $where
     */
    public function getOne($where){
        return $this->where($where)->get(null,true);
    }
    
    public function updateCollectByID($id,$data){
        return $this->where(['id'=>$id])->update($data);
    }
       
    /**
     * 查询用户是否收藏此文章
     * @param unknown $userID
     * @param unknown $articleID
     */
    public function isUserCollect($userID,$articleID){
    	$result=$this->where(['user_id'=>$userID,'article_id'=>$articleID,'is_on'=>1])->get(['id'],true);
    	
    	return $result;
    }
    
}