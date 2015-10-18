<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class test1 extends BaseTable{
protected function initTable(){ $this->fields=[
'id'=> ['type' => 'i', 'value' => null],
'title'=> ['type' => 's', 'value' => null],
];
$this->tableName = 'test1';
$this->AIField = 'id';
}
}
