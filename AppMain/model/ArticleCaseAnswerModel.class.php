<?php
namespace AppMain\model;
use System\BaseModel;
class ArticleCaseAnswerModel extends BaseModel{
    protected $table='article_case_answer';  //定义数据表
    
    /**
     * 添加提问答案
     * @param array $data
     * @param int $isPro 是否首次专家回答
     * @return boolean
     */
    public function addAnswer($data,$isFristPro=0){
        $this->model->startTrans();
        
        //添加到数据库
        $add=$this->save($data);
        if (!$add){
            $this->model->rollback();
            return false;
        }
        
        //处理首次专家回答,问题回答数+1
        if ($isFristPro==1){
            $quData=[
                'answer_id' => $add,
                'answer' => $data['content'],
                'answer_user_id' => $data['user_id'],
                'is_answer' => 1,
                'answer_time' => time(),
            ];
            $updateQuestion=$this->table('article_case_question')->where(['id'=>$data['question_id']])->update($quData);
            //->sqlFunction('answer_num=answer_num+1')
            if (!$updateQuestion){
                $this->model->rollback();
                return false;
            }
        }
//         else{
//             $updateQuestion=$this->table('article_case_question')->where(['id'=>$data['question_id']])->setFieldCalcValue(['answer_num'], ['+'], [1]);
//         }
        
        //案例提问回答数+1
        $result=$this->incAnswerNum($data['question_id']);
        
        $this->model->commit();
        return $add;
    }
    
    /**
     * 答案点赞
     * @param array $data
     * @return boolean
     */
    public function praiseAnswer($data,$isFristPro=0){
        $this->model->startTrans();
        
        //添加到数据库
        $add=$this->table('log_user_article_case_answer_praise')->save($data);
        if (!$add){
            $this->model->rollback();
            return false;
        }
        
        //处理首次专家回答,问题回答数+1
        if ($isFristPro==1){
            $updateQuestion=$this->table('article_case_question')->where(['id'=>$data['question_id']])->setFieldCalcValue(['answer_praise_num'],['+'],[1]);
            if (!$updateQuestion){
                $this->model->rollback();
                return false;
            }
        }
        
        //更新回答
        $updateAnswer=$this->table('article_case_answer')->where(['id'=>$data['answer_id']])->setFieldCalcValue(['praise_num'],['+'],[1]);
        if (!$updateAnswer){
            $this->model->rollback();
            return false;
        }
        
        $this->model->commit();
        return true;
    }
    
    /**
     * 答案取消点赞
     * @param array $data
     * @return boolean
     */
    public function delPraiseAnswer($userID,$questionID,$answerID,$isFristPro=0){
        $this->model->startTrans();
    
        //更新到数据库
        $update=$this->table('log_user_article_case_answer_praise')->where(['user_id'=>$userID,'answer_id'=>$answerID])->update(['update_time'=>time(),'is_on'=>0]);
        if (!$update){
            $this->model->rollback();
            return false;
        }
    
        //处理首次专家回答,问题回答数+1
        if ($isFristPro==1){
            $updateQuestion=$this->table('article_case_question')->where(['id'=>$questionID])->setFieldCalcValue(['answer_praise_num'],['-'],[1]);
            if (!$updateQuestion){
                $this->model->rollback();
                return false;
            }
        }
    
        //更新回答
        $updateAnswer=$this->table('article_case_answer')->where(['id'=>$answerID])->setFieldCalcValue(['praise_num'],['-'],[1]);
        if (!$updateAnswer){
            $this->model->rollback();
            return false;
        }
    
        $this->model->commit();
        return true;
    }
    
    /**
     * 一个案例的回答数+1
     * @param number $id
     * @param number $num  传负数为减
     * @return \System\Ambigous
     */
    public function incAnswerNum($id,$num=1){
        //案例hash
        $result=$this->S('redis')->hincrby('article:case:question:answerNum','answerNum_'.$id,$num);
        return $result;
    }
    
    /**
     * 获取案例的提问数
     * @param number|array $id
     * @return \System\Ambigous
     */
    public function getAnswerNum($id){
        if (is_array($id)){
            foreach ($id as $v){
                $ids[]='answerNum_'.$v;
            }
            $get=$this->S('redis')->hmget('article:case:question:answerNum',$ids);
            foreach ($get as $key => $v){
                $result[]=!$v?0:$v;
            }
            
        }
        else{
            //案例hash
            $result=$this->S('redis')->hget('article:case:question:answerNum','answerNum_'.$id);
            if (!$result){
                $result=0;
            }
        }
        
        return $result;
    }
       
}