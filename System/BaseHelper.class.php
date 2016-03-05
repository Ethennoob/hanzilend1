<?php

namespace System;
use \System\BaseClass;
abstract class BaseHelper extends BaseClass {

    protected $code = null; // 此功能模块的字符串标识

    public function getMulti($multiSqlStmt, $fieldsName, $getOne = false) {
    	if ($getOne) {
    		$multiSqlStmt['size']=1;
    	} 
    	
    	\System\database\MultiBaseTable::setMultiSqlStmt($multiSqlStmt);
        $list = \System\database\MultiBaseTable::getMulti($fieldsName);
        //error_log(\System\database\MultiBaseTable::$lastSql);
        //dump(\System\database\MultiBaseTable::$lastSql);
        
        if(\System\Entrance::config('IS_DB_DEBUG')==true){
            $fileName = "database/sql_".date("Ymd");
            $content='查询语句:'.\System\database\MultiBaseTable::$lastSql.PHP_EOL.'错误信息：'.\System\database\MultiBaseTable::$error;
            \System\Log::write($content,$fileName);
        }
        
        
        if ($list) {
        	if ($getOne){
        		return $list[0];
        	}
        	else{
        		return $list;
        	}
        } else {
        	return null;
        }
        
    }

    public function getMultiLength($multiSqlStmt, $fieldsName) {
        \System\database\MultiBaseTable::setMultiSqlStmt($multiSqlStmt);
        
        $length=\System\database\MultiBaseTable::getMultiListLength($fieldsName);
        
        if(\System\Entrance::config('IS_DB_DEBUG')==true){
            $fileName = "database/sql_".date("Ymd");
        	$content='查询语句:'.\System\database\MultiBaseTable::$lastSql.PHP_EOL.'错误信息：'.\System\database\MultiBaseTable::$error;
            \System\Log::write($content,$fileName);
        }

        return $length;
    }

}
