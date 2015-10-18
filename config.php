<?php

/*
 * 	全局网站配置文件
 */


return [

    'IS_DB_DEBUG' => true,
    // 数据库(主库)(必须存在，请勿删除)
     "DB_MASTER" => array(
      "host" => "127.0.0.1",
      'user' => 'root',
      "password" => "123",
      "dbName" => "yii2basic"
      ),

    //测试
    "DB_MASTER" => array(
      "host" => "127.0.0.1",
      'user' => 'root',
      "password" => "123",
      "dbName" => "yii2basic"
    ),
    // 只读数据库
    "DB_READ" => array(
      "host" => "127.0.0.1",
      'user' => 'root',
      "password" => "123",
      "dbName" => "yii2basic"
    ),
	// 旧数据库
	"DB_OLD" => array(
			"host" => "",
			'user' => '',
			"password" => "",
			"dbName" => "",
	),

    // memcached配置
    /* 'MEMCACHE_CONFIG' => array(
      'host' => array(
      'host' => '',
      'port' => '11212'
      ),
      array(
      'host' => '',
      'port' => '11213'
      )
      ), */

    //测试
    'MEMCACHE_CONFIG' => array(
        array(
            'host' => '',
            'port' => ''
        ),
    ),
    //redis配置
    'REDIS_CONFIG' => array(
        'host' => '',
        'port' => '',
        'auth' => '',
        'connectType' => 'connect'    //pconnect && connect
    ),
    /*  // 微信配置
      'WEIXIN_CONFIG' => array(
      'token' => '61e303b6638e23dafc4713c43002b4b4', // 填写你设定的key
      'encodingaeskey' => '', // 填写加密用的EncodingAESKey
      'appid' => 'wxfd18311ddcb8de73', // 填写高级调用功能的app id
      'appsecret' => '8841c34efe9b69f56e3fb12f681b9017'
      ), // 填写高级调用功能的密钥 */

    // 测试
    'WEIXIN_CONFIG' => array(
        'token' => '', // 填写你设定的key
        'encodingaeskey' => '', // 填写加密用的EncodingAESKey
        'appid' => '', // 填写高级调用功能的app id
        'appsecret' => ''
    ), // 填写高级调用功能的密钥
    //微信支付配置
    'WEIXIN_PAY_CONFIG' => array(
        'appid' => 'wxfd18311ddcb8de73',
        'mchid' => '1243036302', //商户号
        'key' => '3ae00f86d1b1b07a73ff5903d2cab550', //key
        //证书路径,注意应该填写绝对路径
        'SSLCERT_PATH' => '/disk2/www/taoxue/cert/WechatPay/apiclient_cert.pem',
        'SSLKEY_PATH' => '/disk2/www/taoxue/cert/WechatPay/apiclient_key.pem',
        //异步通知url，商户根据实际开发过程设定
        'NOTIFY_URL' => gethost() . '/test.php?m=123',
        //curl超时设置
        'CURL_TIMEOUT' => 30,
    ),
    
];
