<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleCategoryModel extends BaseModel{
    protected $table='article_category';  //定义数据表
    
    /**
     * 获取文章分类列表
     * @return boolean|string
     */
    public function getArticleCategoryList(){
    	$isArticle=$this->S('redis')->get('article:category:list');
    	if (!$isArticle){
	    	$isArticle=$this->where(['is_on'=>1])->order('id desc')->get(['id','name','icon',]);
	    	
	    	if (!$isArticle){
	    		return false;
	    	}
	    	$this->S('redis')->set('article:category:list',$isArticle);
    	}
    	
    	foreach ($isArticle as $key => $v){
    		$isArticle[$key]['icon']=$this->config('IMG_PATH').$v['icon'];
    	}
    	
    	return $isArticle;
    }
    
    /**
     * 添加文章分类
     * @param unknown $data
     */
    public function addCategory($data){
    	$this->model->startTrans();
    	$add=$this->save($data);
    	if (!$add){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//重新生成缓存
    	$list=$this->where(['is_on'=>1])->order('id desc')->get(['id','name','icon']);
    	if ($list){
            $setCache=$this->setCache($list);
    		if (!$setCache){
    			$this->model->rollback();
    			return false;
    		}
    	}
    	
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 编辑文章分类
     * @param unknown $data
     */
    public function editCategory($data,$categoryID){
    	$this->model->startTrans();
    	$update=$this->where(['id'=>$categoryID])->update($data);
    	if (!$update){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//重新生成缓存
    	$list=$this->where(['is_on'=>1])->order('id desc')->get(['id','name','icon']);
    	if ($list){
    	    $setCache=$this->setCache($list);
    		if (!$setCache){
    			$this->model->rollback();
    			return false;
    		}
    	}
    	
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 重新生成缓存
     */
    public function setCache($list){
        foreach ($list as $key => $v){
            $list[$key]['id']=strval($v['id']);
        }
        $addRedis=$this->S('redis')->set('article:category:list',$list);
        if (!$addRedis){
            return false;
        }
        return true;
    }
       
}