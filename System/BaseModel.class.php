<?php
namespace System;
use \System\BaseClass;
abstract class BaseModel extends BaseClass {

    protected $table = null;  //定义表
    protected $model= null;   //数据库链接
    protected  $autoConnectDB=true;
    
    protected $autoUpdateTime =true;
    protected $autoAddTime =true;
    
    public function __construct(){
        if ($this->autoConnectDB==true){
            $this->connectDB();
        }
        
        return $this;
    }
    
    public function connectDB(){
        if ($this->table !== null && $this->model === null){
            $this->model = $this->table($this->table);
        }
    }
    
    /**
     * 更新操作
     * @param array $data
     */
    public function update($data){
        if ($this->autoUpdateTime){
            $data['update_time']=time();
        }
        
        return $this->model->update($data);
    }
    
    /**
     * 插入操作
     * @param array $data
     */
    public function save($data){
        if ($this->autoAddTime){
            $data['add_time']=time();
        }
    
        return $this->model->save($data);
    }
    
    /**
     * where条件
     * @param array $data
     * @return \System\BaseModel
     */
    public function where($data){
        $this->model->where($data);
        return $this;
    }
    
    /**
     * order条件
     * @param array $data
     * @return \System\BaseModel
     */
    public function order($data){
    	$this->model->order($data);
    	return $this;
    }
    
    /**
     * 获取数据
     * @param string $columns
     * @param string $isOne
     * @return Ambigous <multitype:, \System\database\this>
     */
    public function get($columns=null,$isOne=false){
        return $this->model->get($columns,$isOne);
    }

}
