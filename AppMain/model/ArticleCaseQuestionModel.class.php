<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleCaseQuestionModel extends BaseModel{
    protected $table='article_case_question';  //定义数据表
    
    public function addQuestion($data){
        //添加到数据库
        $add=$this->save($data);
        if (!$add){
            return false;
        }
        
        //问题数+1
        $result=$this->incQuestionNum($data['case_id']);
        
        return $add;
    }
    
    /**
     * 获取案例提问浏览量数
     * @param number|array $id
     * @return \System\Ambigous
     */
    public function getClickNum($id){
        if (is_array($id)){
            
            $this->S('redis')->multi();
            
            foreach ($id as $v){
                $this->S('redis')->zScore('article:case:question:clickNum',$v);
            }
            
            $result=$this->S('redis')->exec();
            
            foreach ($result as $key => $v){
                if (!$v){
                    $result[$key]=0;
                }
            }
        }
        else{
            //案例hash
            $result=$this->S('redis')->zScore('article:case:question:clickNum',$id);
            if (!$result){
                $result=0;
            }
        }
        
        return $result;
    }
    
    
    /**
     * 案例提问浏览量数+1
     * @param number $id
     * @return \System\Ambigous
     */
    public function incClickNum($id){
        //案例hash
        $result=$this->S('redis')->zincrby('article:case:question:clickNum', $id,1);
        return $result;
    }
    
    /**
     * 一个案例的提问数+1
     * @param number $id
     * @param number $num  传负数为减
     * @return \System\Ambigous
     */
    
    public function incQuestionNum($id,$num=1){
        //案例hash
        $result=$this->S('redis')->hincrby('article:case:'.$id,'question_num',$num);
        return $result;
    }
    
    /**
     * 获取案例的提问数
     * @param number $id
     * @return \System\Ambigous
     */
    public function getQuestionNum($id){
        //案例hash
        $result=$this->S('redis')->hget('article:case:'.$id,'question_num');
        return $result?$result:0;
    }

       
}