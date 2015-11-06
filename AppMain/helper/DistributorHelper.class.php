<?php
/**
 * 分销商信息Distributor联表查询helper类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-11-04 15:02:35
 * @version $Id$
 */

namespace AppMain\helper;
use System\BaseHelper;

class DistributorHelper extends BaseHelper {
    
    /*
        *获取分销商列表信息 
        * @param unknown $whereStmt
        * @param string $bindParams
        * @param string $bindTypes
        * @param string $getOne
        * @param string $order
        * @param string $sqlFunction
        * @return Ambigous <NULL, unknown, multitype:, \System\database\this>
         */
        public function  getDistributor($whereStmt, $bindParams = null, $bindTypes = null, $getOne = false, $order = null, $sqlFunction = null){
            $this->distributorLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order, $sqlFunction);
            return $this->getMulti($multiSqlStmt, $fieldsName, $getOne);
        }
        public function getDistributorListLength($whereStmt, $bindParams = null, $bindTypes = null, $getOne = true, $order = null) {
            $this->distributorLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order);
            return $this->getMultiLength($multiSqlStmt, $fieldsName, $getOne);
        }
        private function distributorLinkedTable(&$fieldsName, &$multiSqlStmt, $whereStmt, $bindParams = null, $bindTypes = null, $orderBy = null, $sqlFunction = null) {            
            $fieldsName = array(
                    'distributor_apply as A' => 'id as id,user_id,phone,apply_level,add_time',
                    'user as B' => 'user_name',

            );
            $multiSqlStmt = array(
                'joinType' => array('left join'),
                'joinOn' => array('A.user_id=B.id'),
                'whereStmt' => $whereStmt,
                'bindParams' => $bindParams,
                'bindTypes' => $bindTypes,
                'orderBy' => $orderBy,
                'sqlFunction' => $sqlFunction
            );
        }
}