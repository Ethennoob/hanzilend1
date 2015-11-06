<?php
    namespace AppMain\helper;
    use System\BaseHelper;
    /*
     *用户列表联表查询类 
     */
    class UserHelper extends BaseHelper{
        /*
        *获取用户信息 
        * @param unknown $whereStmt
        * @param string $bindParams
        * @param string $bindTypes
        * @param string $getOne
        * @param string $order
        * @param string $sqlFunction
        * @return Ambigous <NULL, unknown, multitype:, \System\database\this>
         */
        public function  getUser($whereStmt, $bindParams = null, $bindTypes = null, $getOne = false, $order = null, $sqlFunction = null){
            $this->userLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order, $sqlFunction);
            return $this->getMulti($multiSqlStmt, $fieldsName, $getOne);
        }
        public function getUserListLength($whereStmt, $bindParams = null, $bindTypes = null, $getOne = true, $order = null) {
            $this->userLinkedTable($fieldsName, $multiSqlStmt, $whereStmt, $bindParams, $bindTypes, $order);
            return $this->getMultiLength($multiSqlStmt, $fieldsName, $getOne);
        }
        private function userLinkedTable(&$fieldsName, &$multiSqlStmt, $whereStmt, $bindParams = null, $bindTypes = null, $orderBy = null, $sqlFunction = null) {            
            $fieldsName = array(
                    'user as A' => 'id as id,user_img,city,mobile_phone,user_money,pay_points,mall_own_private,is_follow,add_time',
                    'user_address as B' => 'id as user_id,province,city,area,street',
                    'withdrawals_apply as C' => 'id as user_id,withdrawals_name' ,

            );
            $multiSqlStmt = array(
                'joinType' => array('left join','left join'),
                'joinOn' => array('A.id=B.user_id','A.id=C.user_id'),
                'whereStmt' => $whereStmt,
                'bindParams' => $bindParams,
                'bindTypes' => $bindTypes,
                'orderBy' => $orderBy,
                'sqlFunction' => $sqlFunction
            );
        }
    }
?>