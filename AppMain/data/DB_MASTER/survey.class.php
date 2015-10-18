<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class survey extends BaseTable{
protected function initTable(){ $this->fields=[
'id'=> ['type' => 'i', 'value' => null],
'name'=> ['type' => 's', 'value' => null],
'age'=> ['type' => 'i', 'value' => null],
'sex'=> ['type' => 's', 'value' => null],
'edu'=> ['type' => 's', 'value' => null],
'info'=> ['type' => 's', 'value' => null],
'create_time'=> ['type' => 's', 'value' => null],
];
$this->tableName = 'survey';
$this->AIField = 'id';
}
}
