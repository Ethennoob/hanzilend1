<?php
namespace System;
class Router{
    static $isView=false;
    static $isViewMuti=false;
    
    static $pathInfo=null;
    static $pathDomain=null;
    
    //---美化url参数
    static $module = "";
    static $class = "";
    static $function = "";
    static $groupModule=null;
    static $groupPrefix=null;
    
    static $requestData=null;  //请求参数
    
	public static function router(){
	    
	    //定义当前路径
	    $pathInfo=empty($_SERVER['PATH_INFO'])?'/':trim($_SERVER['PATH_INFO'],'/');
	    self::$pathInfo=$pathInfo;

	    //定义当前域名
	    $pathDomain=empty($_SERVER['HTTP_HOST'])?'':$_SERVER['HTTP_HOST'];
	    self::$pathDomain=$pathDomain;
	    
	    //初始定义路径   
	    $rewrite=self::filterPath($pathInfo);
	    if (count($rewrite) >= 3){
	        self::$module = $rewrite[0];
	        self::$class = $rewrite[1];
	        self::$function = $rewrite[2];
	    }
	    isset($_GET['m'])?self::$module=trim($_GET['m']):self::$module;
	    isset($_GET['c'])?self::$class = trim($_GET['c']):self::$class;
	    isset($_GET['f'])?self::$function = trim($_GET['f']):self::$function;	    
	    
	    //内部php调用
	    if (isset($_SERVER['argv'][1])){   //暂时不经过中间件
	        if ($_SERVER['argv'][1]=='task'){
	            Router::Controller("Task." . $_SERVER['argv'][2])->$_SERVER['argv'][3]();
	            exit;
	        }
	        else{
	            self::$module=$_SERVER['argv'][1];
	            self::$class=$_SERVER['argv'][2];
	            self::$function=$_SERVER['argv'][3];
	        }
	        self::go();
	        exit;
	    }
	    
	    //载入配置
	    $aa=require_once __ROOT__.'/router.php';
	    
	    if (count($rewrite) >= 3){
	        self::$module = $rewrite[0];
	        self::$class = $rewrite[1];
	        self::$function = $rewrite[2];
	    }
	    self::go();
	}

    /**
     * 运行
     */
    public static function go(){
        //扩展名分析
        if(!empty(self::$function)){
            if(strrpos(self::$function,'.htmlx') !== false){
                self::$function=str_replace('.htmlx','',self::$function);
            }
        }
         
        //美化url
        $module = self::$module;
        $class = self::$class;
        $function = self::$function;
        
        //载入中间件
        self::getClass("\\AppMain\\middleware\\HttpMiddleware");
        $data=self::$requestData;
        if (strlen($module) > 0 && strlen($class) > 0 && strlen($function) > 0) {
            if (!empty($data)){
                self::Controller($module . '.' . $class)->$function($data);
            }
            else{
                self::Controller($module . '.' . $class)->$function();
            }
            exit;
        } else {
            throw new \Exception('找不到类文件：'.$class, 500);
            exit();
        }
    }

    /**
     * 加载控制器
     */
    public static function Controller($class,$isView=false,$functionName=null) {
    	$class = str_replace(array('.', '#'), array('\\', '.'), $class);
    	
    	if ($isView){
    		self::$isView=true;
    	}
    	
    	BaseClass::$functionName=$functionName;
    	return self::getClass("\\AppMain\\controller\\" .$class . "Controller");
    }

    /**
     * 加载类
     */
    public static function getClass($class) {
        if (class_exists($class)) {
            return new $class();
        } else {
            throw new \Exception('找不到类文件：'.$class, 500);
        }
        exit;
    }
    
    /**
     * 路由直接请求
     * @param unknown $newPath
     * @param unknown $oldPath
     * @return boolean
     */
    public static function get($newPath,$oldPath){
        if(self::filterRouter($newPath, $oldPath)){
            self::go();
        }
        else{
            self::$requestData=null;
            return false;
        }
    }
    
    /**
     * 路由直接请求
     * @param unknown $config
     * @param unknown $function
     * @return boolean
     */
    public static function group($config,$function){
        
        //前缀,替代一个模块
        $prefix=$config['prefix']==='/'?$config['prefix']:trim($config['prefix'],'/');        
        $v=str_replace('/','\/',$prefix);
        
        if (preg_match('/^('.$v.').*/', self::$pathInfo) || $config['prefix']==='/'){
            self::$groupPrefix=$prefix;
            self::$groupModule=trim($config['module'],'/');
        }
        else{
            return false;
        }
        call_user_func($function);
        
        self::$groupModule=null;
        self::$groupPrefix=null;
        
        return false;
    }
    
    /**
     * 路由请求资源
     * @param unknown $config
     * @param unknown $function
     * @return boolean
     * 
     * @example
     * GET	/photo	index	photo.index
     * GET	/photo/create	create	photo.create
     * POST	/photo	store	photo.store
     * GET	/photo/{photo}	show	photo.show
     * GET	/photo/{photo}/edit	edit	photo.edit
     * PUT	/photo/{photo}	update	photo.update
     * DELETE	/photo/{photo}	destroy	photo.destroy
     */
    public static function resources($newPath,$class){
        if(self::filterRouter($newPath, $class,true)){
            self::go();
        }
        else{
            self::$requestData=null;
            return false;
        }
    }
    
    public static function filterRouter($newPath,$oldPath,$isResources=false){
        if ($newPath!=='/' ||  self::$groupPrefix!==null){
            $newPath=trim($newPath,'/');
        }
        
        if (self::$groupPrefix!==null && self::$groupPrefix !=='/'){
            $newPath=self::$groupPrefix.'/'.$newPath;
        }

        if (preg_match_all('/{(id)}/', $newPath)){
            $key=array_search('{id}',self::filterPath($newPath));
            if ($key){
                $filterPath=self::filterPath(self::$pathInfo);
                if (!isset($filterPath[$key])){
                    return false;
                }
                self::$requestData=$filterPath[$key];
                $newPath=str_replace('{id}', self::$requestData, $newPath);
            }
            else{
                return false;
            }
            
        }
        
        //RESTful请求
        if ($isResources==true){
            $method=$_SERVER['REQUEST_METHOD'];
            $pathInfo=explode('/',str_replace(self::$groupPrefix.'/', '', self::$pathInfo));
            //初始化
            $function=null;
            self::$requestData=null;
            
            switch ($method){
                case 'GET' :
                    
                    if (isset($pathInfo[1]) && $pathInfo[1] === 'create'){
                        $function='create';
                    }
                    elseif (isset($pathInfo[1]) && is_numeric($pathInfo[1])){
                        if (isset($pathInfo[2]) && $pathInfo[2] === 'edit'){
                            $function='edit'; 
                            $newPath=$newPath.'/'.$pathInfo[1].'/edit';
                            self::$requestData=$pathInfo[1];
                        }
                        else{
                            $function='show';
                            $newPath=$newPath.'/'.$pathInfo[1];
                            self::$requestData=$pathInfo[1];
                        }
                    }
                    else{
                        $function='index'; 
                    }
                    
                    break;
                case 'POST' :
                    $function='store';
                    
                    break;
                case 'DELETE' :
                    if (isset($pathInfo[1]) && is_numeric($pathInfo[1])){
                        $newPath=$newPath.'/'.$pathInfo[1];
                        $function='delete';
                        self::$requestData=$pathInfo[1];
                    }
                    break;
                case 'PUT' :
                    if (isset($pathInfo[1]) && is_numeric($pathInfo[1])){
                        $newPath=$newPath.'/'.$pathInfo[1];
                        $function='update';
                        self::$requestData=$pathInfo[1];
                    }
                    break;
            }
            
            if ($function==null){
                return false;
            }
            
            $oldPath=$oldPath.'/'.$function; 
        }        
        
        if ($newPath == self::$pathInfo){
            $rewrite=self::filterPath($oldPath);
        
            if (self::$groupModule!==null){
                self::$module = self::$groupModule;
                self::$class = $rewrite[0];
                self::$function = $rewrite[1];
            }
            else{
                self::$module = $rewrite[0];
                self::$class = $rewrite[1];
                self::$function = $rewrite[2];
            }
        
            return true;
        }
        else{
            return false;
        }
    }
    
    /**
     * 解析路径
     * @param unknown $path
     * @return multitype:
     */
    public static function filterPath($path){
        $filter=explode("/",trim($path,"/"));
        return $filter;
    }
}