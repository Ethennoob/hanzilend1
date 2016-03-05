<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleDryGoodsQuestionModel extends BaseModel{
    protected $table='article_dry_goods_question';  //定义数据表
    
    public function addQuestion($data){
        //添加到数据库
        $add=$this->save($data);
        if (!$add){
            return false;
        }
        
        //问题数+1
        $result=$this->incQuestionNum($data['dry_id']);
        
        return $add;
    }
    
    /**
     * 获取干货提问浏览量数
     * @param number $id
     * @return \System\Ambigous
     */
    public function getClickNum($id){
        //案例hash
        $result=$this->S('redis')->hget('article:dryGoods:question:clickNum','questionClick_'.$id);
        if (!$result){
            $result=0;
        }
        
        return $result;
    }
    
    /**
     * 干货提问浏览量数+1
     * @param number $id
     * @return \System\Ambigous
     */
    public function incClickNum($id){
        //案例hash
        $result=$this->S('redis')->hincrby('article:dryGoods:question:clickNum','questionClick_'.$id,1);
        return $result;
    }
    
    /**
     * 一个干货的提问数+1
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
     * 获取干货的提问数
     * @param number $id
     * @return \System\Ambigous
     */
    public function getQuestionNum($id){
        //案例hash
        $result=$this->S('redis')->hget('article:dryGoods:'.$id,'question_num');
        return $result?$result:0;
    }
    

       
}