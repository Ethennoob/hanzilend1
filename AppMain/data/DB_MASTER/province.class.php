<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class province extends BaseTable{
protected function initTable(){ $this->fields=[
'id'=> ['type' => 'i', 'value' => null],
'code'=> ['type' => 's', 'value' => null],
'name'=> ['type' => 's', 'value' => null],
];
$this->tableName = 'province';
$this->AIField = 'id';
}
}
