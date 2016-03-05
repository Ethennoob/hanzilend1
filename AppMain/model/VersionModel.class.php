<?php
namespace AppMain\model;
use System\BaseModel;
class VersionModel extends BaseModel{
    protected $table='version_base';  //定义数据表
    protected  $autoConnectDB=false;
    
    /**
     * 获取版本号
     * @param number $id
     * @param string $version
     * @param string $device
     * @param string $where
     * @return \System\Ambigous
     */
    public function getVersion($version,$device){
        
    	
        return $isVersion;
    }
    
}