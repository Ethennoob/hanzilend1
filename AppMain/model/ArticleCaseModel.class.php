<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleCaseModel extends BaseModel{
    protected $table='article_case';  //定义数据表
    
    /**
     * 获取文章案例列表
     * @return boolean|string
     */
    public function getArticleCaseList($start,$end){
    	$list=$this->S('redis')->zRevRange('article:case:list',$start,$end);
    	
    	if ($list){
    		$field=['id','title','cover','cover_360','click_num','article_num','desc','add_time'];
    		
    		$this->S('redis')->multi();
    		foreach ($list as $v){
    			$this->S('redis')->hmget('article:case:'.$v,$field);
    		}
    		$result=$this->S('redis')->exec();
    	}
    	else{
    		$result='';
    	}
    	
    	return $result;
    }
    
    /**
     * 获取文章案例列表总长度
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getArticleCaseListLength(){
    	$length=$this->S('redis')->zSize('article:case:list');
    	return $length;
    }
    
    /**
     * 获取案例
     * @param number $id
     * @return \System\Ambigous
     */
    public function getCase($id){
        $field=['id','title','desc','cover','cover_360','click_num','article_num','add_time'];
        $isCase=$this->S('redis')->hmget('article:case:'.$id,$field);
        if (!$isCase){
            return false;
        }
        return $isCase;
    }
    
    /**
     * 一个案例的阅读量+1
     * @param number $id
     * @return \System\Ambigous
     */
    public function incClickNum($id){
        $this->S('redis')->multi();
        
        //案例hash
        $this->S('redis')->hincrby('article:case:'.$id,'click_num',1);
         
        //案例有序列表
        $this->S('redis')->zincrby('article:case:clickNum',$id,1);

        $result=$this->S('redis')->exec();
         
        return $result;
    }
    
    /**
     * 案例的文章数 累加
     * @param number $id
     * @param number $num  传负数为减
     * @return \System\Ambigous
     */
    public function incArticleNum($id,$num=1){
        //案例hash
        $result=$this->S('redis')->hincrby('article:case:'.$id,'article_num',$num);
        return $result;
    }
    
    
     
    
//--------------后台管理
    
    /**
     * 添加案例
     * @param array $data
     */
    public function addCase($data){
    	//dump($data);
    	 
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->save($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$data['id']=$result;
    	$data['add_time']=time();
    	$data['article_num']=0;    //文章数
    	$data['click_num']=0;      //阅读量
    	$data['question_num']=0;   //提问数
    	$data['answer_num']=0;     //回答数
    	
    	//保存在redis
    	$this->S('redis')->zadd('article:case:list', $result, $result);
    	$this->S('redis')->hmset('article:case:'.$result, $data);
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 编辑案例
     * @param array $data
     */
    public function editCase($data){
    	//dump($data);//exit;
    	$caseID=$data['case_id'];
    	unset($data['case_id']);
    	
    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->where(['id'=>$caseID])->update($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    
    	//保存在redis
    	//$this->S('redis')->zadd('articleSubjectList', $result, $result);
    	$data['id']=$caseID;
    	$this->S('redis')->hmset('article:case:'.$caseID, $data);
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 删除案例
     * @param int $subjectID
     */
    public function delCase($caseID){
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->where(['id'=>$caseID])->update(['is_on'=>0]);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//保存在redis
    	$this->S('redis')->zRem('article:case:list', $caseID );
    	$this->S('redis')->delete('article:case:'.$caseID);
    	$this->model->commit();
    	return true;
    }
       
}