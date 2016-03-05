<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleSubjectModel extends BaseModel{
    protected $table='article_subject';  //定义数据表
    
    /**
     * 获取文章专题列表
     * @return boolean|string
     */
    public function getArticleSubjectList($start,$end){
    	$list=$this->S('redis')->zRevRange('article:subject:list',$start,$end);
    	
    	if ($list){
    		$field=['id','title','cover','cover_360','article_num','desc'];
    		
    		$this->S('redis')->multi();
    		foreach ($list as $v){
    			$this->S('redis')->hmget('article:subject:'.$v,$field);
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
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getArticleListLength(){
    	$length=$this->S('redis')->zSize('article:subject:list');
    	return $length;
    }
    
    /**
     * 获取文章专题列表
     * @return boolean|string
     */
    public function getSubjectArticleList($start,$end,$id){
        $list=$this->S('redis')->zRevRange('article:subject:'.$id.':articleList',$start,$end);

        if ($list){
            $field=['id','title','cover','cover_360','click_num','comment_num','desc','category_id','category_name','price'];
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
     * 获取文章列表总长度
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getSubjectArticleListLength($id){
        $length=$this->S('redis')->zSize('article:subject:'.$id.':articleList');
        return $length;
    }
    
    /**
     * 获取专题详情
     * @param unknown $id
     * @return unknown
     */
    public function subjectDetail($id){
        $field=['id','title','cover','click_num','cover_360','article_num','desc'];
        
        $data=$this->S('redis')->hmget('article:subject:'.$id,$field);
        return $data;
    }
    
    /**
     * 专题点击数+1
     * @param unknown $id
     * @param unknown $num
     */
    public function incSubjectClickNum($id,$num){
        //文章hash
        $this->S('redis')->hincrby('article:subject:'.$id,'click_num',1);
        return true;
    }
    
    /**
     * 专题的文章数 累加
     * @param number $id
     * @param number $num  传负数为减
     * @return \System\Ambigous
     */
    public function incArticleNum($id,$num=1){
        //案例hash
        $result=$this->S('redis')->hincrby('article:subject:'.$id,'article_num',$num);
        return $result;
    }
    
    //--------------管理后台
    
    public function getMaxSort(){
        $result=$this->where(['is_on'=>1])->order('sort desc')->get(['id','sort'],true);
        $sort=$result?$result['sort']+1:1;
        return $sort;
    }
    
    /**
     * 添加专题
     * @param array $data
     */
    public function addSubject($data,$sort){
        $data['sort']=$sort;
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->save($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$data['click_num']=0;
    	$data['article_num']=0;
    	$data['id']=$result;
    	$data['add_time']=time();
    	//保存在redis
    	$this->S('redis')->zadd('article:subject:list', $result, $sort);
    	$this->S('redis')->hmset('article:subject:'.$result, $data);
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 编辑专题
     * @param array $data
     */
    public function editSubject($data){
    	//dump($data);//exit;
    	$subjectID=$data['subject_id'];
    	unset($data['subject_id']);
    	
    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->where(['id'=>$subjectID])->update($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    
    	//保存在redis
    	//$this->S('redis')->zadd('articleSubjectList', $result, $result);
    	$data['id']=$subjectID;
    	
    	if (isset($data['sort'])){
    	    $this->S('redis')->zadd('article:subject:list', $result, $data['sort']);
    	}
    	$this->S('redis')->hmset('article:subject:'.$subjectID, $data);
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 删除专题
     * @param int $subjectID
     */
    public function delSubject($subjectID){
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->where(['id'=>$subjectID])->update(['is_on'=>0]);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//保存在redis
    	$this->S('redis')->zRem('article:subject:list', $subjectID );
    	$this->S('redis')->delete('article:subject:'.$subjectID);
    	$this->model->commit();
    	return true;
    }
       
}