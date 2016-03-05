<?php
class demo{
public function socket(){
		$config=array(
		  'address'=>'10.8.7.206',
		  'port'=>'8562',
		  'debug'=>true,
		  'dataClass'=>$this,
		  'function'=>'WSevent'
		);
		
		$config=array(
				'address'=>'127.0.0.1',//绑定地址
				'port'=>'8562',//绑定端口
				'event'=>'WSevent',//回调函数的函数名
				'class'=>$this,//回调函数的函数名
				'function'=>'WSevent',//回调函数的函数名
				'log'=>true,//命令行显示记录
		);
		
		$this->vendor('Websocket.Websocket');
		$websocket = new \websocket($config);
		$websocket->run();
		
		
    }
    
    public function WSevent($type,$user,$websocket){
    	
    	if('in'==$type){
    		$websocket->log('客户进入id:'.$user['k']);
    	}elseif('out'==$type){
    		$websocket->log('客户退出id:'.$user['k']);
    	}elseif('msg'==$type){
    		$websocket->log($user['k'].'消息:'.$user['msg']);
    		$this->roboot($user['sign'],$user['msg'],$websocket);
    	}
    }
    
    public function roboot($sign,$t,$websocket){
    	$websocket->log($t);
    	/* switch ($t)
    	{
    		case 'hello':
    			$show='hello,GIt @ OSC';
    			break;
    		case 'name':
    			$show='Robot';
    			break;
    		case 'time':
    			$show='当前时间:'.date('Y-m-d H:i:s');
    			break;
    		case '再见':
    			$show='( ^_^ )/~~拜拜';
    			$websocket->write($sign,'Robot:'.$show);
    			$websocket->close($sign);
    			return;
    			break;
    		case '天王盖地虎':
    			$array = array('小鸡炖蘑菇','宝塔震河妖','粒粒皆辛苦');
    			$show = $array[rand(0,2)];
    			break;
    		default:
    			$show='( ⊙o⊙?)不懂,你可以尝试说:hello,name,time,再见,天王盖地虎.';
    	}
    	$websocket->write($sign,'Robot:'.$show); */
    	$websocket->writeToAll($t);
    }
}
