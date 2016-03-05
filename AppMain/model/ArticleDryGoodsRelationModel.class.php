<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleDryGoodsRelationModel extends BaseModel{
    protected $table='article_dry_goods_relation';  //定义数据表
    
    /**
     * 获取案例文章列表
     * @param int $subjectID
     */
    public function getDryGoodsrticleList($start,$end,$dryID){
        $list=$this->S('redis')->zRevRange('article:dryGoods:'.$dryID.':articleList',$start,$end);
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
    public function getDryGoodsArticleListLength($dryID){
        $length=$this->S('redis')->zSize('article:dryGoods:'.$dryID.':articleList');
        return $length;
    }
    
    
    //管理后台
    
    /**
     * 设置文章到干货
     * @return boolean|string
     */
    public function setArticleToDry($data){    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->save($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$updateDry=$this->model('ArticleDryGoods')->where(['id'=>$data['dry_id']])->model->setFieldCalcValue(['article_num'],['+'],[1]);
    	if (!$updateDry){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//保存在redis
    	$result=$this->S('redis')->zadd('article:dryGoods:'.$data['dry_id'].':articleList', $data['article_id'], $result);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//文章数+1
    	$result=$this->model('ArticleDryGoods')->incArticleNum($data['dry_id']);
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
    public function delArticleFromDry($data){    	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->model->where(['article_id'=>$data['article_id'],'dry_id'=>$data['dry_id']])->delete();
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$updateCase=$this->model('ArticleCase')->where(['id'=>$data['dry_id']])->model->setFieldCalcValue(['article_num'],['-'],[1]);
    	if (!$updateCase){
    		$this->model->rollback();
    		return false;
    	}
    	    	   	
    	//文章数-1
    	$result=$this->model('ArticleDryGoods')->incArticleNum($data['dry_id'],-1);
    	if (!$result){
    	    $this->model->rollback();
    	    return false;
    	}
    	
    	//保存在redis
    	$result=$this->S('redis')->zRem('articleDryRelationList_'.$data['dry_id'], $data['article_id']);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$this->model->commit();
    	return true;
    }
    
}