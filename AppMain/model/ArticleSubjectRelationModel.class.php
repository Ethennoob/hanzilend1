<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleSubjectRelationModel extends BaseModel{
    protected $table='article_subject_relation';  //定义数据表
    
    /**
     * 设置文章到专题
     * @return boolean|string
     */
    public function setArticleToSubject($data){    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->save($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$updateSubject=$this->model('ArticleSubject')->where(['id'=>$data['subject_id']])->model->setFieldCalcValue(['article_num'],['+'],[1]);
    	if (!$updateSubject){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//保存在redis
    	$result=$this->S('redis')->zadd('articleList:subject:'.$data['subject_id'], $data['article_id'], $result);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//文章数+1
    	$result=$this->model('ArticleSubject')->incArticleNum($data['subject_id']);
    	if (!$result){
    	    $this->model->rollback();
    	    return false;
    	}
    	
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 移除专题中的一篇文章
     * @return boolean|string
     */
    public function delArticleFromSubject($data){    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->model->where(['article_id'=>$data['article_id'],'subject_id'=>$data['subject_id']])->delete($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$updateSubject=$this->model('ArticleSubject')->where(['id'=>$data['subject_id']])->model->setFieldCalcValue(['article_num'],['-'],[1]);
    	if (!$updateSubject){
    		$this->model->rollback();
    		return false;
    	}   	    	
    	
    	//文章数-1
    	$result=$this->model('ArticleSubject')->incArticleNum($data['subject_id'],-1);
    	if (!$result){
    	    $this->model->rollback();
    	    return false;
    	}
    	
    	//保存在redis
    	$result=$this->S('redis')->zRem('articleSubjectRelationList_'.$data['subject_id'], $data['article_id']);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 获取专题文章列表
     * @param int $subjectID
     */
    public function getSubjectArticle($start,$end,$subjectID){
    	$list=$this->S('redis')->zRevRange('articleSubjectRelationList_'.$subjectID,$start,$end);
    	if ($list){
    		$field=['title','cover','click_num','price','category_id','category_name'];
    		$this->S('redis')->multi();
    		foreach ($list as $v){
    			$this->S('redis')->hmget('getArticle_'.$v,$field);
    		}
    		$result=$this->S('redis')->exec();
    	}
    	else{
    		$result='';
    	}
    	 
    	return $result;
    }
    
    /**
     * 获取文章列表总长度
     * @return \System\Ambigous
     */
    public function getSubjectArticleLength($subjectID){
    	$length=$this->S('redis')->zSize('articleSubjectRelationList_'.$subjectID);
    	return $length;
    }
       
}