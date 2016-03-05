<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleCaseRelationModel extends BaseModel{
    protected $table='article_case_relation';  //定义数据表
    
    /**
     * 获取案例文章列表
     * @param int $subjectID
     */
    public function getCaseArticleList($start,$end,$caseID){
        $list=$this->S('redis')->zRevRange('article:case:'.$caseID.':articleList',$start,$end);
        if ($list){
            $field=['id','title','cover','cover_360','click_num','comment_num','price','category_id','category_name','add_time'];
            $this->S('redis')->multi();
            foreach ($list as $v){
                $this->S('redis')->hmget('article:detail:'.$v,$field);
            }
            $result=$this->S('redis')->exec();
        }
        else{
            $result='';
        }
    
        return $result;
    }
    
    /**
     * 获取案例文章列表总长度
     * @return \System\Ambigous
     */
    public function getCaseArticleListLength($caseID){
        $length=$this->S('redis')->zSize('article:case:'.$caseID.':articleList');
        return $length;
    }
    
    //--------后台管理
    
    
    /**
     * 设置文章到专题
     * @return boolean|string
     */
    public function setArticleToCase($data){    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->save($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$updateSubject=$this->model('ArticleCase')->where(['id'=>$data['case_id']])->model->setFieldCalcValue(['article_num'],['+'],[1]);
    	if (!$updateSubject){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//保存在redis
    	$result=$this->S('redis')->zadd('article:case:'.$data['case_id'].':articleList', $data['article_id'], $result);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//文章数+1
    	$result=$this->model('ArticleCase')->incArticleNum($data['case_id']);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 移除案例中的一篇文章
     * @return boolean|string
     */
    public function delArticleFromCase($data){    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->model->where(['article_id'=>$data['article_id'],'case_id'=>$data['case_id']])->delete();
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$updateCase=$this->model('ArticleCase')->where(['id'=>$data['case_id']])->model->setFieldCalcValue(['article_num'],['-'],[1]);
    	if (!$updateCase){
    		$this->model->rollback();
    		return false;
    	}
    	    	
    	//文章数-1
    	$result=$this->model('ArticleCase')->incArticleNum($data['case_id'],-1);
    	if (!$result){
    	    $this->model->rollback();
    	    return false;
    	}
    	    	
    	//保存在redis
    	$result=$this->S('redis')->zRem('article:case:'.$data['case_id'].':articleList', $data['article_id']);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$this->model->commit();
    	return true;
    }
    
    
       
}