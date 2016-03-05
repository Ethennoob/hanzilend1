<?php

namespace System\database\Mongo;
class Mongo{
    private $host = 'localhost';
    private $port = '27017';

    private $connect=null;  //连接对象
    private $db=null;   //数据库对象
    public $dbname;
    private $table = NULL;

    /**
     * 初始化类，得到mongo的实例对象
     */
    public function __construct($host, $port, $user,$password, $dbName)
    {
        //$m = new \MongoClient("mongodb://liang:224133@115.29.199.135");
        $mongo = new \MongoClient("mongodb://$host:$port",['db'=>$dbName,'username'=>$user,'password'=>$password]);
        //dump($mongo->getHosts());
        $this->connect=$mongo;
        $this->dbname=$dbName;
        dump($mongo);
        
        //dump($this->connect );
        //$this->dbname=$dbName;
        //dump($this->connect->$dbName);   
        //$db=$mongo->zhihu;
        //dump($db);
        //$collection = $mongo->selectCollection("zhihu", "test");;
        //dump($collection);
        return $this;
    }

    /**
     * 返回所有已打开连接的信息
     */
    public function getConnections(){
        return $this->connect->getConnections();
    }
    
    /**
     * 返回所有已打开连接的信息
     */
    public function table($tableName){
        return $this->connect->selectCollection($this->dbname,$tableName);
    }
    
    /**
     * 更新所有关联主机的状态信息
     * @return 返回集群中主机的信息数组。 包含了每个主机的主机名，它的健康程度（1 是很健康），它的状态（1 是活跃节点，2 是备用节点，0 是其他），ping 服务器所需的时间，以及最后一次 ping 的时间。 例如，在具有三个成员的集群中，它看上去可能是这样的：
     */
    public function getHosts(){
        return $this->connect->getHosts();
    }
      
    /**
     * 切换数据库
     */
    public function db($dbName){
        return $this->connect->$dbName;
    }

}