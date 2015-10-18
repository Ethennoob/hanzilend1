<?php
 namespace AppMain\data\DB_MASTER;
use System\database\BaseTable;
 class user extends BaseTable{
protected function initTable(){ $this->fields=[
'id'=> ['type' => 'i', 'value' => null],
'username'=> ['type' => 's', 'value' => null],
'password'=> ['type' => 's', 'value' => null],
'email'=> ['type' => 's', 'value' => null],
'brithday'=> ['type' => 's', 'value' => null],
'authKey'=> ['type' => 's', 'value' => null],
'accessToken'=> ['type' => 's', 'value' => null],
'password_reset_token'=> ['type' => 's', 'value' => null],
];
$this->tableName = 'user';
$this->AIField = 'id';
}
}
