<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleTagModel extends BaseModel{
    protected $table='article_tag';  //定义数据表
    
    /**
     * 添加文章标签
     * @param unknown $data
     * @return boolean
     */
    public function addTag($data){
    	$result=$this->save($data);
    	
    	if ($data['is_hot']==1){
    		$this->setHotCache();
    	}
    	
    	return $result;
    }
    
    /**
     * 编辑文章标签
     * @param unknown $data
     * @param unknown $tagID
     */
    public function editTag($data,$tagID){
    	//修改文章中tag名称(暂时不做)
    	
    	$result=$this->where(['id'=>$tagID])->update($data);
    	
    	if ($data['is_hot']==1){
    		$this->setHotCache();
    	}
    	
    	return $result;
    }
    
    /**
     * 热门标签是否已满
     * @return boolean
     */
    public function isMaxHotTag(){
    	$result=$this->where(['is_on'=>1,'is_hot'=>1])->model->getListLength();
    	if ($result >= 10){
    		return false;
    	}
    	
    	return true;
    }
    
    /**
     * 设置热门标签缓存
     */
    public function setHotCache(){
    	$hotTagList=$this->where(['is_on'=>1,'is_hot'=>1])->get(['id','name']);
    	
    	if ($hotTagList){
    		$this->S('redis')->set('articleHotTag',$hotTagList);
    	}
    	
    }
       
}