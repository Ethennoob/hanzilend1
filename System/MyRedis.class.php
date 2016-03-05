<?php
namespace System;
class MyRedis {
    private static $reObj = null;
    
    public function __construct() {
        if (self::$reObj === null) {
            if(!extension_loaded('redis')){
                die('服务器不支持redis扩展！');
        	}
        	
        	$reObj=new \redis();
        	
        	$config=\System\Entrance::config('REDIS_CONFIG');
        	$host=$config['host'];
        	$port=$config['port'];
        	$auth=$config['auth'];
        	if ($config['connectType']=='connect'){
        	    $connect=$reObj->connect($host,$port);
        	}
        	else{
        	    $connect=$reObj->pconnect($host,$port);
        	}
        	
            
        	if (!empty($auth)){
        	    if (!$reObj->auth($auth)){
        	        die('redis密码错误！');
        	    }
        	}
        	
        	if (!$connect){
        	    die('redis服务器连接失败！');
        	}
        	
        	self::$reObj=$reObj;
        }
        return self::$reObj;
    }
    
    public function select($db){
    	self::$reObj->select($db);
    	return self::$reObj;
    }
    
    /** 
     * 设置值 
     * @param string $key KEY名称 
     * @param string|array $value 获取得到的数据 
     * @param int $timeOut 时间 
     */  
    public function set($key, $value, $timeOut = 0 ,$serialize=true) {  
        if ($serialize){
            $value = serialize($value);
        }
        
        $retRes = self::$reObj->set($key, $value);  
        if ($timeOut > 0) self::$reObj->setTimeout($key, $timeOut);  
        return $retRes;  
    }
    
    /**
     * 同时设置多值
     * 当发现同名的key存在时，用新值覆盖旧值
     * @param array $value 设置的键值和值组成的数组   ['aaa'=>'123'];
     * @param int $timeOut 时间
     */
    public function multiSet($value) {
        foreach ($value as $key=>$a){
            $value[$key]=serialize($a);
        }
        
        $value = serialize($value);
        $retRes = self::$reObj->mset($value);
        return $retRes;
    }
    
    /**
     * 追加字符串
     * @param str $key
     * @param str $valuse
     */
    public function appendStr($key,$value){
        $retRes = self::$reObj->append($key,$value);
        return $retRes;
    }
  
    /** 
     * 通过KEY获取数据 
     * @param string $key KEY名称 
     */  
    public function get($key) {  
        $result = self::$reObj->get($key); 
        
        if (@unserialize($result)){         //判断是否需要反序列
            return unserialize($result);
        }
        else{
            return $result;
        }
    }  
      
    /** 
     * 删除一条数据 
     * @param string｜array $key KEY名称 
     */  
    public function delete($key) {  
        return self::$reObj->delete($key);  
    }  
      
    /** 
     * 清空所有数据库数据 
     */  
    public function flushAll() {  
        return self::$reObj->flushAll();  
    }
    
    /**
     * 清空当前数据库数据
     */
    public function flushDB(){
        return self::$reObj->flushDB();
    }
      
    /** 
     * 数据入队列 
     * @param string $key KEY名称 
     * @param string|array $value 获取得到的数据 
     * @param bool $serialize 是否作序列化处理，默认true
     * @param bool $right 是否插到表尾，默认true
     */  
    public function push($key, $value, $serialize=true,$right = true) {
        if ($serialize){
            $value = serialize($value);
        }
        return $right ? self::$reObj->rPush($key, $value) : self::$reObj->lPush($key, $value);  
    }  
      
    /** 
     * 数据出队列 
     * @param string $key KEY名称 
     * @param bool $left 是否从左边开始出数据 
     */  
    public function pop($key , $left = true) {  
    	$val = $left ? self::$reObj->lPop($key) : self::$reObj->rPop($key);  
        return json_decode($val);  
    }  
    
    /**
     * 数据出队列（监听）
     * @param string $key KEY名称
     * @param int $timeout 超时
     */
    public function blPop($key,$timeout=0){
    	return self::$reObj->blPop($key,$timeout);
    }
      
    /** 
     * 数据自增 
     * @param string $key KEY名称 
     */  
    public function increment($key) {  
        return self::$reObj->incr($key);  
    }  
  
    /** 
     * 数据自减 
     * @param string $key KEY名称 
     */  
    public function decrement($key) {  
        return self::$reObj->decr($key);  
    }  
      
    /** 
     * key是否存在，存在返回ture 
     * @param string $key KEY名称 
     */  
    public function exists($key) {  
        return self::$reObj->exists($key);  
    }  
    
    /**
     * redis服务器信息
     */
    public function info() {
        return self::$reObj->info();
    }
    
    /**
     * 构建一个集合(无序集合)
     * @param string $key 集合Y名称
     * @param string|array $value  值
     */
    public function sadd($key,$value){
    	return self::$reObj->sadd($key,$value);
    }
    
    /**
     * 删除一个集合(无序集合)
     * @param string $key 集合名称
     * @param string|array $value  值
     */
    public function sRem($key,$value){
        return self::$reObj->sRem($key,$value);
    }
    
    /**
     * 返回集合key中的所有成员。
     * @param string $setName 集合名字
     */
    public function sMembers($setName){
        return self::$reObj->smembers($setName);
    }
    
    /**
     * 判断member元素是否是集合key的成员。
     * @param string $setName 集合名字
     * @param string $member 成员
     */
    public function sisMembers($setName,$member){
        return self::$reObj->sismember($setName,$member);
    }
    
    /**
     * 返回集合key的基数(集合中元素的数量)。
     * @param string $setName 集合名字
     */
    public function sCard($setName){
        return self::$reObj->scard($setName);
    }
    
    /**
     * 构建一个集合(有序集合)
     * @param string $key 集合名称
     * @param string|array $value  值
     */
    public function zadd($key,$value,$score){
    	return self::$reObj->zadd($key,$score,$value);
    }
    
    /**
     * 删除一个集合(有序集合)
     * @param string $key 集合名称
     * @param string|array $value  值
     */
    public function zRem($key,$value){
    	return self::$reObj->zRem($key,$value);
    }
    
    /**
     * 构建一个集合(有序集合),自增scorce
     * @param string $key 集合名称
     * @param string $value  键值
     * @param string $score  值 （score值可以是整数值或双精度浮点数。）
     * @return member成员的新score值，以字符串形式表示。
     */
    public function zincrby($key,$value,$score){
    	return self::$reObj->zincrby($key,$score,$value);
    }   
    
    /**
     * 返回一个有序集合中，value的score
     * @param string $key 集合名称
     * @param string $value  值
     */
    public function zScore($key,$value){
    	return self::$reObj->zScore($key,$value);
    }   
    
    /**
     * 返回一个有序集合中总数
     * @param string $key 集合名称
     */
    public function zSize($key){
    	return self::$reObj->zSize($key);
    }   
    
    /**
     * 返回名称为key的zset（元素已按score从大到小排序）中的index从start到end的所有元素.
     * @param unknown $key
     * @param unknown $start
     * @param unknown $end
     * @param string $withscores 是否输出socre的值，默认false，不输出
     */
    public function zRevRange($key,$start,$end,$withscores=false){
    	return self::$reObj->zRevRange($key,$start,$end,$withscores);
    }
    
    
    
    /** HASH类型 */
    
    /**
     * 设置hash 一个字段
     * @param string $key  表名字key
     * @param string $key  字段名字
     * @param sting $value  值
     */
    public function hset($key,$field,$value){
    	return self::$reObj->hset($key,$field,$value);
    }
    
    /**
     * 返回hash表 增加一个元素,但不能重复
     * @param string $key  表名字key
     */
    public function hsetnx($key,$field,$value){
    	return self::$reObj->hsetnx($key,$field,$value);
    }
    
    /**
     * hash表 增加一个元素多个字段
     * @param string $key  表名字key
     * @param array $fieldAndValue 
     * @example ('hash1',array('key3'=>'v3','key4'=>'v4')
     */
    public function hmset($key,$field){
    	return self::$reObj->hmset($key,$field);
    }    
    
    /**
     * 获取hash一个字段的值
     * @param string $key  表名字key
     * @param string $key  表名字key
     * @param string $key  字段名字
     */
    public function hget($key,$field){
    	return self::$reObj->hget($key,$field);
    }
    
    /**
     * 获取hash多个个字段的值
	 * @param array $key  字段名字
     * @example array(‘key3′,’key4′)
     */
    public function hmget($key,$field){
    	return self::$reObj->hmget($key,$field);
    }
    
    /**
     * 返回hash表中的指定$field是否存在
     * @param string $key  表名字key
     * @param string $key  字段名字
     */
    public function hexists($key,$field){
    	return self::$reObj->hexists($key,$field);
    }
    
    /**
     * 删除hash表中指定$field的元素
     * @param string $key  表名字key
     * @param string $key  字段名字
     */
    public function hdel($key,$field){
    	return self::$reObj->hdel($key,$field);
    }
    
    /**
    * 返回hash表元素个数
    * @param string $key  表名字key
	*/
    public function hlen($key){
    	return self::$reObj->hdel($key);
    }
    
    /**
     * hash 对指定key进行累加
     * @param string $key  表名字key
     * @param string $field  表名字key
     * @param Number $num  表名字key
     */
    public function hincrby($key,$field,$num){
    	return self::$reObj->hincrby($key,$field,$num);
    }
    
    /**
     * hash 返回hash表中的所有field
     * @param string $key  表名字key
     * @return 返回array(‘key1′,’key2′,’key3′,’key4′,’key5′)
     */
    public function hkeys($key){
    	return self::$reObj->hkeys($key);
    }
    
    /**
     * hash 返回hash表中的所有value
     * @param string $key  表名字key
     * @return //返回array(‘v1′,’v2′,’v3′,’v4′,13)
     */
    public function hvals($key){
    	return self::$reObj->hvals($key);
    }
    
    /**
     * hash 返回整个hash表元素
     * @param string $key  表名字key
     * @return //返回array(‘key1′=>’v1′,’key2′=>’v2′,’key3′=>’v3′,’key4′=>’v4′,’key5′=>13)
     */
    public function hgetall($key){
    	return self::$reObj->hgetall($key);
    }
      
    //--------订阅
    
    /**
     * 将信息 message 发送到指定的频道 channel 
     * @param unknown $channel 发送频道
     * @param unknown $msg  发送的消息
     */
    public function publish($channel,$msg){
        return self::$reObj->publish($channel,$msg);
    }
    
    /**
     * 将信息 message 发送到指定的频道 channel
     * @param array $channel 发送频道
     * @param function $msg  发送的消息
     */
    public function subscribe($channel,$function){
        $result=self::$reObj->subscribe($channel,$function);
        return $result;
    }
    
    /**
     * 开启事务
     */
    public function multi(){
    	return self::$reObj->multi();
    }
    
    /**
     * 提交事务
     */
    public function exec(){
    	return self::$reObj->exec();
    }
    
    /**
     * 放弃事务
     */
    public function discard(){
    	return self::$reObj->discard();
    }
    
    /**
     * @param unknown $key
     * @param unknown $option
     * array(
      *  ‘by’ => ‘some_pattern_*’,
      *  ‘limit’ => array(0, 1),
      *  ‘get’ => ‘some_other_pattern_*’ or an array of patterns,
      *  ‘sort’ => ‘asc’ or ‘desc’,
      *  ‘alpha’ => TRUE,
      *  ‘store’ => ‘external-key’
      *  )
     */
    public function sort($key,$option){
        return self::$reObj->sort($key,$option);
    }
    
    /** 
     * 返回redis对象 
     * redis有非常多的操作方法，我们只封装了一部分 
     * 拿着这个对象就可以直接调用redis自身方法 
     */  
    public function redis() {  
        return self::$reObj;  
    }  

}

?>
