<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class article extends BaseTable{
protected function initTable(){ $this->fields=[
'id'=> ['type' => 'i', 'value' => null],
'cat_id'=> ['type' => 'i', 'value' => null],
'title'=> ['type' => 's', 'value' => null],
'content'=> ['type' => 's', 'value' => null],
'Pic'=> ['type' => 's', 'value' => null],
'Url'=> ['type' => 's', 'value' => null],
'add_time'=> ['type' => 'i', 'value' => null],
'update_time'=> ['type' => 'i', 'value' => null],
'is_on'=> ['type' => 'i', 'value' => null],
];
$this->tableName = 'article';
$this->AIField = 'id';
}
}
