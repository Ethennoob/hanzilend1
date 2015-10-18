<?php
namespace System;
class Entrance {
	static $loadConfig=null;
	static $loadConvert=null;

    static public function action() {
        // 注册AUTOLOAD方法
        spl_autoload_register('self::autoload');
        define('_DOMAIN_', empty($_SERVER["HTTP_HOST"])?'api.grlend.com':$_SERVER["HTTP_HOST"]);

        ini_set('date.timezone', 'Asia/Shanghai'); // 设置时区
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_ERROR | E_WARNING);
        header("Content-type:text/html;charset=utf-8");
        header("PowerBy: Han-zi,Liang");

        //载入系统函数
        require_once 'function.php';

        startSession();

        //内部php调用
        if (isset($_SERVER['argv'][1])){
        	if ($_SERVER['argv'][1]=='task'){
        		self::Controller("\\AppMain\\controller\\Task\\" . $_SERVER['argv'][2])->$_SERVER['argv'][3]();
        		exit;
        	}
        }

        //美化url
        $module = "";
        $class = "";
        $function = "";

        if(!empty($_SERVER['PATH_INFO'])){
            $rewrite=explode("/",trim($_SERVER['PATH_INFO'],"/"));
            if (count($rewrite) >= 3){
                $module = $rewrite[0];
                $class = $rewrite[1];
                $function = $rewrite[2];
            }

        }

        isset($_GET['m'])?$module=trim($_GET['m']):$module;
        isset($_GET['c'])?$class = trim($_GET['c']):$class;
        isset($_GET['f'])?$function = trim($_GET['f']):$function;

        if (strlen($module) > 0 && strlen($class) > 0 && strlen($function) > 0) {
            self::Controller("\\AppMain\\controller\\" . $module . "\\" . $class)->$function();
        } else {
            echo '非法访问';
            exit();
        }
    }

    static private function Controller($class) {
        return self::getClass($class . "Controller");
    }
    
    static public function getClass($class, $db = "") {
    	if ($db != "") {
    		$class = '\\AppMain\\data\\' . $db . '\\' . $class;
    	}
    	if (class_exists($class)) {
    		return new $class($db);
    	} else {
    		return false;
    	}
    }
    
    /**
     * 类库自动加载
     * @param string $class 对象类名
     * @return void
     */
    public static function autoload($class) {
        $filename = str_replace('\\', '/', $class);
        require_once __ROOT__.'/'.$filename . '.class.php';
    }

    /**
     * 网站配置参数
     */
    static public function config($name,$value=null) {
    	if (empty(self::$loadConfig)){
			$config=require __ROOT__.'/config.php';
			self::$loadConfig=$config;
    	}
    	else{
    		$config=self::$loadConfig;
    	}   	
		
    	if ($value===null){
    		return $config[$name];
    	}
    	else{
    		$config[$name]=$value;
    		return true;
    	}
    }
	   
    /**
     * 转换参数
     * @param string $name
     * @param string $id
     * @return Ambigous <boolean, unknown>
     */
    static public function convertId($name,$id=null){
    	if (empty(self::$loadConvert)){
    		$config=require __ROOT__.'/convert.php';
    		self::$loadConvert=$config;
    	}
    	else{
    		$config=self::$loadConvert;
    	}
    	
    	if ($id===null){
    		return $config[$name];
    	}
    	else{
    		return $config[$name][$id];
    	}
    }
    
}

