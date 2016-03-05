<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleModel extends BaseModel{
    protected $table='article_base';  //定义数据表
    
    /**
     * 获取文章列表
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getArticleList($start=0,$end=14,$type='all'){
    	$list=$this->S('redis')->zRevRange('article:list:cat:'.$type,$start,$end);
    	
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
     * 获取文章列表总长度
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getArticleListLength($type='all'){
    	$length=$this->S('redis')->zSize('article:list:cat:'.$type);
    	return $length;   	 
    }
    
    /**
     * 获取推荐文章列表
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getRecommendList(){
        $list=$this->S('redis')->get('article:recommend:list');
        if ($list){
            $field1=['id','title','cover','cover_360','click_num','comment_num','price','category_id','category_name','add_time'];
            $field2=['id','title','cover','cover_360','desc','article_num','click_num','add_time'];
            
            $articleList=false;
            $subjectList=false;
            
            foreach ($list as $key=>$v){
                if ($v['type']==1){
                    $articleList[$key]['id']=$v['recommend_id'];
                }
                elseif ($v['type']==2){
                    $subjectList[$key]['id']=$v['recommend_id'];
                }
            }
            
            if ($articleList){
                $this->S('redis')->multi();
                foreach ($articleList as $key => $v){
                    $this->S('redis')->hmget('article:detail:'.$v['id'],$field1);
                }
                $result=$this->S('redis')->exec();
                
                //dump($result);
                $i=0;
                foreach ($articleList as $key => $v){
                    $articleList[$key]=$result[$i];
                    $i++;
                }
                
                //dump($articleList);
            }
            
           
            if ($subjectList){
                $this->S('redis')->multi();
                
                foreach ($subjectList as $key => $v){
                    $this->S('redis')->hmget('article:subject:'.$v['id'],$field2);
                }
                
                $result=$this->S('redis')->exec();
                
                $i=0;
                foreach ($subjectList as $key => $v){
                    $subjectList[$key]=$result[$i];
                    $i++;
                }
            }
            
            foreach ($list as $key=>$v){
                if ($v['type']==1){
                    $list[$key]['article']=$articleList[$key];
                }
                elseif ($v['type']==2){
                    $list[$key]['subject']=$subjectList[$key];
                }
            }
            //dump($list);
        }
        else{
            $list=[];
        }
        
        return $list;
    }
    
    /**
     * 获取文章
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getArticle($id=0,$where=null,$fields=null){
    	//如果只通过id查询，则尝试读缓存
    	if ($id && !$where){
    		$isArticle=$this->S('redis')->hgetall('article:detail:'.$id);
    		if ($isArticle){
    			return $isArticle;
    		}
    	}
    	
    	$id?$where['id']=$id:'';
    	
		$isArticle=$this->where($where)->get($fields,true);
		
		if (!$isArticle){
			return false;
		}
		
		//如果只通过id查询，则写入缓存
		$this->S('redis')->hmset('article:detail:'.$id,$isArticle,60*60*24*90);
        return $isArticle;
    }
    
    /**
     * 获取多篇文章
     * @param number $id
     * @param array $where
     * @param array $fields
     * @return \System\Ambigous
     */
    public function getMutiArticle($ids,$fields){
        $this->S('redis')->multi();
        foreach ($ids as $v){
            $this->S('redis')->hmget('article:detail:'.$v,$fields);
        }
        $result=$this->S('redis')->exec();
        
        return $result;
    }
    
    /**
     * 一篇文章的阅读量+1
     * @param number $id
     * @return \System\Ambigous
     */
    public function incArticleClickNum($id){
    	$this->S('redis')->multi();
    	//文章hash
    	$this->S('redis')->hincrby('article:detail:'.$id,'click_num',1);
    	
    	//文章有序列表
    	$this->S('redis')->zincrby('articleClickNum',$id,1);
    	$result=$this->S('redis')->exec();
    	
    	return $result;
    }     
    
    /**
     * 一篇文章的收藏数+1
     * @param number $id
     * @param number $userID
     * @return \System\Ambigous
     */
    public function incArticleCollectNum($id,$userID,$num=1){
        //文章hash
        $result=$this->S('redis')->hincrby('getArticle:'.$id,'collect_num',$num);
        //用户收藏数+1
        $this->S('redis')->hincrby('user'.$userID,'collect_num',$num);
        return $result;
    }
    
    /**
     * 添加文章
     * @param array $data
     * @return boolean
     */
    public function addArticle($data,$tags_arr){    
    	//dump($data);exit;	
    	$this->model->startTrans();
    	//保存数据库
    	$result=$this->save($data);
    	if (!$result){
    		$this->model->rollback();
    		return false;
    	}
    	$articleID=$result;
    	
    	//标签关联
    	foreach ($tags_arr as $v){
    		//插入数据库
    		$relaData=[
    			'tag_id' => $v['id'],
    			'article_id' => $articleID,
    			'add_time' => time()
    		];
    		$addRela=$this->table('article_tag_relation')->save($relaData);
    		if (!$addRela){
    			$this->model->rollback();
    			return false;
    		}
    		//更新标签文章数
    		$update=$this->table('article_tag')->where(['id'=>$v['id']])->setFieldCalcValue(['article_num'], ['+'], [1]);
    		if (!$update){
    			$this->model->rollback();
    			return false;
    		}
    		
    		//保存在redis
    		$this->S('redis')->zadd('articleList:tag:all', $result, $result);
    		$this->S('redis')->zadd('articleList:tag:'.$v['id'], $result, $result);
    	}
    	
    	//更新分类文章数
    	$update=$this->table('article_category')->where(['id'=>$data['category_id']])->setFieldCalcValue(['article_num'], ['+'], [1]);
    	if (!$update){
    		$this->model->rollback();
    		return false;
    	}
    	
    	//保存在redis
    	$this->S('redis')->zadd('articleList:cat:all', $result, $result);
    	$this->S('redis')->zadd('articleList:cat:'.$data['category_id'], $result, $result);
    	
    	$data['id']=$articleID;
    	$data['click_num']=0;
    	$data['comment_num']=0;
    	$data['add_time']=time();
    	
    	$this->S('redis')->hmset('getArticle:'.$result, $data);
    	$this->model->commit();
    	return true;
    }
    
    /**
     * 评论文章
     */
    public function commentArticle($data){
        $data['add_time']=time();
        $result=$this->table('article_comment')->save($data);
        
        //评论数+1
        $this->S('redis')->hincrby('getArticle:'.$data['article_id'],'comment_num',1);
        
        return $result;
    }
    
}