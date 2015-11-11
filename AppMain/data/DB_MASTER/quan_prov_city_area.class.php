<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class quan_prov_city_area extends BaseTable{
protected function initTable(){ $this->fields=[
'no'=> ['type' => 'i', 'value' => null],
'areaname'=> ['type' => 's', 'value' => null],
'topno'=> ['type' => 'i', 'value' => null],
'areacode'=> ['type' => 's', 'value' => null],
'arealevel'=> ['type' => 'i', 'value' => null],
'typename'=> ['type' => 's', 'value' => null],
'id'=> ['type' => 'i', 'value' => null],
];
$this->tableName = 'quan_prov_city_area';
$this->AIField = 'id';
}
}
