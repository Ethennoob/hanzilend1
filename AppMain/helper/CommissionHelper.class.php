<?php
/**
 * 盛世分销系统————佣金明细联表查询
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-11-09 17:54:56
 * @version $Id$
 */

namespace AppMain\helper;
use System\BaseHelper;

class CommissionHelper extends BaseHelper {
    
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
    public function getCommission($whereStmt, $bindParams = null, $bindTypes = null, $getOne = false, $order = null, $sqlFunction = null) {
        $this->setCommissionCondition($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order, $sqlFunction);
        return $this->getMulti($multiSqlStmt, $fieldsName, $getOne);
    }
    
    public function getCommissionListLength($whereStmt, $bindParams = null, $bindTypes = null, $getOne = true, $order = null) {
        $this->setCommissionCondition($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order);
        return $this->getMultiLength($multiSqlStmt, $fieldsName, $getOne);
    }
    
    
    private function setCommissionCondition(&$fieldsName, &$multiSqlStmt, $whereStmt, $bindParams = null, $bindTypes = null, $orderBy = null, $sqlFunction = null) {
        $fieldsName = array(
            'distributor_apply as A' => 'id as id,user_id',
            'distribution_cission as B' => 'id as income_commission,add_time,bill_id',
            'withdrawals_apply as C' => 'money',
        );
        $multiSqlStmt = array(
            'joinType' => array('left join','left join'),
            'joinOn' => array('A.user_id=B.user_id','A.user_id=C.user_id'),
            'whereStmt' => $whereStmt,
            'bindParams' => $bindParams,
            'bindTypes' => $bindTypes,
            'orderBy' => $orderBy,
            'sqlFunction' => $sqlFunction
        );
    }
}
