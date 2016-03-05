<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleRecommendModel extends BaseModel{
    protected $table='article_recommend';  //定义数据表
    
    /**
     * 添加推荐
     * @param unknown $data
     * @return boolean
     */
    public function addRecommend($data){
    	//查询最后的排序
    	$sort=$this->where(['is_on'=>1])->order('sort desc')->get(['sort'],true);
    	if (!$sort){
    		$sort=1;
    	}
    	else{
    		$sort=$sort['sort']+1;
    	}
    	$data['sort']=$sort;
    	
    	$result=$this->save($data);
    	
    	//重置推荐缓存
    	$this->setCache();
    	
    	return $result;
    }
    
    /**
     * 排序推荐
     * @param unknown $recommend
     * @param unknown $position
     * @return boolean
     */
    public function sortRecommend($recommend,$position){
    	$originalPosition=$recommend['sort'];

    	$this->model->startTrans();
    	if ($originalPosition > $position){
    		$startSort=$position;
    		$endSort=$originalPosition;
    		$update=$this->model->where(['sort'=>[['egt',$startSort],['lt',$endSort],'and'],'is_on'=>1])->setFieldCalcValue(['sort'], ['+'], [1]);
    		if (!$update){
    			$this->model->rollback();
    			return false;
    		}
    	}
    	elseif($originalPosition < $position){
    		$startSort=$originalPosition;
    		$endSort=$position;
    		$update=$this->model->where(['sort'=>[['gt',$startSort],['elt',$endSort],'and'],'is_on'=>1])->setFieldCalcValue(['sort'], ['-'], [1]);
    		if (!$update){
    			$this->model->rollback();
    			return false;
    		}
    	}
    	else{
    		$this->model->rollback();
    		return false;
    	}
    	
    	//改为原来的位置
    	$update=$this->where(['id'=>$recommend['id']])->update(['sort'=>$position]);
    	if (!$update){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$this->model->commit();
    	$this->setCache();
    	return true;
    }
    
    /**
     * 删除推荐
     * @param unknown $recommendID
     */
    public function delRecommend($recommend){
    	$originalPosition=$recommend['sort'];
    	$this->model->startTrans();
    	$update=$this->where(['id'=>$recommend['id']])->update(['is_on'=>0]);
    	if (!$update){
    		$this->model->rollback();
    		return false;
    	}
    	
    	$update=$this->model->where(['sort'=>['gt',$originalPosition],'is_on'=>1])->setFieldCalcValue(['sort'], ['-'], [1]);
    	//dump($update);
    	if (!$update){
    		$this->model->rollback();
    		return false;
    	}
    	$this->model->commit();
    	$this->setCache();
    	return true;
    }
    
    private function setCache(){
    	$list=$this->table('article_recommend')->where(['is_on'=>1])->order('sort asc')->get(['id','type','recommend_id','sort']);
    	$this->S('redis')->set('articleRecommendList', $list);
    	return true;
    }
       
}