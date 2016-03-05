<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleDryGoodsModel extends BaseModel{
    protected $table='article_dry_goods';  //定义数据表
    
    /**
     * 获取干货列表
     * @return boolean|string
     */
    public function getArticleDryGoodsList($start,$end){
    	$list=$this->S('redis')->zRevRange('article:dryGoods:list',$start,$end);
    	
    	if ($list){
    		$field=['id','title','cover','cover_360','article_num','click_num','desc','add_time'];
    		
    		$this->S('redis')->multi();
    		foreach ($list as $v){
    			$this->S('redis')->hmget('article:dryGoods:'.$v,$field);
    		}
    		$result=$this->S('redis')->exec();
    	}
    	else{
    		$result='';
    	}
    	
    	return $result;
    }
    
    /**
     * 获取干货列表总长度
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getArticleDryGoodsListLength(){
    	$length=$this->S('redis')->zSize('article:dryGoods:list');
    	return $length;
    }
    
    /**
     * 获取干货
     * @param number $id
     * @return \System\Ambigous
     */
    public function getDryGoods($id){
        $field=['id','title','desc','cover','cover_360','click_num','article_num','add_time'];
        $isDry=$this->S('redis')->hmget('article:dryGoods:'.$id,$field);
        if (!$isDry){
            return false;
        }
        return $isDry;
    }
    
    /**
     * 一个干货的阅读量+1
     * @param number $id
     * @return \System\Ambigous
     */
    public function incClickNum($id){
        $this->S('redis')->multi();
    
        //干货hash
        $this->S('redis')->hincrby('article:dryGoods:'.$id,'click_num',1);
         
        //干货有序列表
        $this->S('redis')->zincrby('article:dryGoods:clickNum',$id,1);
        
        $result=$this->S('redis')->exec();
         
        return $result;
    }

    /**
     * 干货的文章数 累加
     * @param number $id
     * @param number $num  传负数为减
     * @return \System\Ambigous
     */
    public function incArticleNum($id,$num=1){
        //案例hash
        $result=$this->S('redis')->hincrby('article:dryGoods:'.$id,'article_num',$num);
        return $result;
    }
    
    /**
     * 干货的提问数+1
     * @param number $id
     * @param number $num  传负数为减
     * @return \System\Ambigous
     */
    public function incQuestionNum($id,$num=1){
        //案例hash
        $result=$this->S('redis')->hincrby('article:dryGoods:'.$id,'question_num',$num);
        return $result;
    }
    
    /**
     * 干货的回答数+1
     * @param number $id
     * @param number $num  传负数为减
     * @return \System\Ambigous
     */
    public function incAnswerNum($id,$num=1){
        //案例hash
        $result=$this->S('redis')->hincrby('article:dryGoods:'.$id,'answer_num',$num);
        return $result;
    }

    
    //------------后台管理
       
    /**
     * 添加干货
     * @param array $data
     */
    public function addDryGoods($data){
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
    	$this->S('redis')->zadd('article:dryGoods:list', $result, $result);
    	$this->S('redis')->hmset('article:dryGoods:'.$result, $data);
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 编辑专题
     * @param array $data
     */
    public function editDryGoods($data){
    	//dump($data);//exit;
    	$dryID=$data['dry_id'];
    	unset($data['dry_id']);
    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->where(['id'=>$dryID])->update($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    
    	//保存在redis
    	//$this->S('redis')->zadd('articleSubjectList', $result, $result);
    	$data['id']=$dryID;
    	$this->S('redis')->hmset('article:dryGoods:'.$dryID, $data);
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 删除干货
     * @param int $subjectID
     */
    public function delDryGoods($dryID){
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->where(['id'=>$dryID])->update(['is_on'=>0]);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//保存在redis
    	$this->S('redis')->zRem('article:dryGoods:list', $dryID );
    	$this->S('redis')->delete('article:dryGoods:'.$dryID);
    	$this->model->commit();
    	return true;
    }
       
}