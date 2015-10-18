<?php
namespace AppMain\helper;
use System\BaseHelper;

class ActivityHelper extends BaseHelper {
    
    /**
     * 获取活动信息
     * @param unknown $whereStmt
     * @param string $bindParams
     * @param string $bindTypes
     * @param string $getOne
     * @param string $order
     * @param string $sqlFunction
     * @return Ambigous <NULL, unknown, multitype:, \System\database\this>
     */
	
	/*--------活动信息---------*/
    public function getActivityOne($whereStmt, $bindParams = null, $bindTypes = null, $getOne = false, $order = null, $sqlFunction = null) {
        $this->setActivityOneCondition($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order, $sqlFunction);
        return $this->getMulti($multiSqlStmt, $fieldsName, $getOne);
    }
    
    public function getActivityOneListLength($whereStmt, $bindParams = null, $bindTypes = null, $getOne = true, $order = null) {
        $this->setActivityOneCondition($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order);
        return $this->getMultiLength($multiSqlStmt, $fieldsName, $getOne);
    }
    
    //用户邀请了的人的列表
    private function setActivityOneCondition(&$fieldsName, &$multiSqlStmt, $whereStmt, $bindParams = null, $bindTypes = null, $orderBy = null, $sqlFunction = null) {
        $fieldsName = array(
            'log_activity1_recommend as A' => 'id as log_id,user_id as recommendSendId,activity_id,recommended as recommendGetId,status,rate',
            'user_base as B' => 'nickname,realname,is_follow,idcard,is_bank_bind,phone,photo,photo_150,add_time', 
        );
        $multiSqlStmt = array(
            'joinType' => array('left join'),
            'joinOn' => array('A.recommended=B.id'),
            'whereStmt' => $whereStmt,
            'bindParams' => $bindParams,
            'bindTypes' => $bindTypes,
            'orderBy' => $orderBy,
            'sqlFunction' => $sqlFunction
        );
    }
}
