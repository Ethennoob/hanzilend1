<?php
/**
 * 用户关系UserRelation查询helper类
 * @authors 凌翔 (553299576@qq.com)
 * @date    2015-10-29 16:46:06
 * @version $Id$
 */
namespace AppMain\helper;
use System\BaseHelper;
    /*
     *用户关系联表查询类 
     */
    class UserRelationHelper extends BaseHelper{
        /*
        *获取商品信息 
        * @param unknown $whereStmt
        * @param string $bindParams
        * @param string $bindTypes
        * @param string $getOne
        * @param string $order
        * @param string $sqlFunction
        * @return Ambigous <NULL, unknown, multitype:, \System\database\this>
         */
        public function  getUserRelation($whereStmt, $bindParams = null, $bindTypes = null, $getOne = false, $order = null, $sqlFunction = null){
            $this->userRelationLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order, $sqlFunction);
            return $this->getMulti($multiSqlStmt, $fieldsName, $getOne);
        }
        public function getUserRelationListLength($whereStmt, $bindParams = null, $bindTypes = null, $getOne = true, $order = null) {
            $this->userRelationLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order);
            return $this->getMultiLength($multiSqlStmt, $fieldsName, $getOne);
        }
        private function userRelationLinkedTable(&$fieldsName, &$multiSqlStmt, $whereStmt, $bindParams = null, $bindTypes = null, $orderBy = null, $sqlFunction = null) {            
            $fieldsName = array(
                    'user as A' => 'id as id,user_name,mobile_phone,city,mall_own_private,status,commission,add_time',
                    'distributor_apply as B' => 'shop_name,type',
            );
            $multiSqlStmt = array(
                'joinType' => array('left join'),
                'joinOn' => array('A.id=B.user_id'),
                'whereStmt' => $whereStmt,
                'bindParams' => $bindParams,
                'bindTypes' => $bindTypes,
                'orderBy' => $orderBy,
                'sqlFunction' => $sqlFunction
            );

        } 
    }
?>