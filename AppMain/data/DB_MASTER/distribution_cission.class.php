<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class distribution_cission extends BaseTable{
protected function initTable(){ $this->fields=[
'id'=> ['type' => 'i', 'value' => null],
'parentid'=> ['type' => 'i', 'value' => null],
'uid'=> ['type' => 'i', 'value' => null],
'user_id'=> ['type' => 'i', 'value' => null],
'commission_ratio'=> ['type' => 's', 'value' => null],
'money'=> ['type' => 'd', 'value' => null],
'bill_id'=> ['type' => 'i', 'value' => null],
'bill_number'=> ['type' => 's', 'value' => null],
'add_time'=> ['type' => 'i', 'value' => null],
'update_time'=> ['type' => 'i', 'value' => null],
'is_on'=> ['type' => 'i', 'value' => null],
];
$this->tableName = 'distribution_cission';
$this->AIField = '';
}
}
