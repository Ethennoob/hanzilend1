<?php
namespace AppMain\model;
use System\BaseModel;
class BanneModel extends BaseModel{
    protected $table='banner_base';  //定义数据表
    protected  $autoConnectDB=false;
    
	/**
	 * 保存
	 * @param unknown $data
	 * @return boolean
	 */
	public function addBanner($data){
		$this->connectDB();
		$result=$this->save($data);
		
		$this->setCache();
		return $result;
	}
	
	/**
	 * 修改
	 * @param unknown $data
	 * @param unknown $id
	 * @return boolean
	 */
	public function editBanner($data,$id){
		$this->connectDB();
		$result=$this->where(['id'=>$id])->save($data);
	
		$this->setCache();
		return $result;
	}
	
	/**
	 * 设置缓存
	 */
	public function setCache(){
		$list=$this->where(['is_on'=>1])->order('sort desc,id desc')->get(['title','desc','path','path_200','path_360','jump_type','jump_value']);
		if ($list){
			$this->S('redis')->set('bannerList', $list);
		}
	}
   
    
    
    
}