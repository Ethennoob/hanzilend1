<?php
namespace AppMain\controller\Task;
use \System\BaseClass;
abstract class CommonController extends BaseClass{
	public function __construct(){
		//内部php调用
		if (!(isset($_SERVER['argv'][1])&&$_SERVER['argv'][1]=='task')){
			exit('非法访问！');
		}
	}
}
