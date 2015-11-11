<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class city extends BaseTable{
protected function initTable(){ $this->fields=[
'id'=> ['type' => 'i', 'value' => null],
'code'=> ['type' => 's', 'value' => null],
'name'=> ['type' => 's', 'value' => null],
'provincecode'=> ['type' => 's', 'value' => null],
'status'=> ['type' => 'i', 'value' => null],
];
$this->tableName = 'city';
$this->AIField = 'id';
}
}
