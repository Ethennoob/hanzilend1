<?php
namespace AppMain\controller\Api;
use \System\BaseClass;

class LoginController extends Baseclass {

    /**
     * 登陆操作
     */
    public function login() {
        if (isset($_SESSION['id'])&&$_SESSION['id'] > 0){
            $this->R('','80000');
        }
        
        $post=[
            'mobile_phone' => $_POST['mobile_phone'],
            'password' => $_POST['password']
        ];
        
        $this->V([
            'mobile_phone'  =>[],
            'password'      =>[]
            ],$post);

        $user = $this->table('user')->where(['is_froze'=>0])->get(null,true);
        if (!$user) {
            $this->R('','70000');
        }
        $user = $this->table('user')->where(['password'=>md5($post['password'])])->get(null,true);
        if (!$user) {
            $this->R('','70002');
        }
        $user = $this->table('user')->where(['mobile_phone'=>$post['mobile_phone']])->get(null,true);
        if (!$user) {
            $this->R('','70001');
        }
        
        if (isset($_POST['is_remember'])&&$_POST['is_remember']==1){
            $isRemember=1;
        }
        else{
            $isRemember=0;
        }
        
        $this->loginAction($user,0,$isRemember);
    }
    
    /**
     * 自动登陆
     */
    public function autoLogin(){
        if (isset($_SESSION['userid'])&&$_SESSION['userid'] > 0){
            $this->R('','80000');
        }
        
        if (empty($_COOKIE['HANZI-AUTOLOGIN'])){
            $this->R('','70004');
        }
        
        $token=$_COOKIE['HANZI-AUTOLOGIN'];
        setcookie('HANZI-AUTOLOGIN','1',time()-3600,'/');  //删除cookie
        
        //查找token
        $isToken=$this->S()->get($token);
        
        if (!$isToken){
            $this->R('','70004');
        }
        
        //检查信息
        $user = $this->table('user')->where(['id'=>$isToken['user_id']])->get(null,true);
        
        if (!$user){
            $this->R('','70004');
        }
        
        if ($user['status'] != $isToken['status']){
            $this->R('','70004');
        }
        
        if ($isToken['timeout'] < time()){
            $this->R('','70004');
        }
        
        $this->S()->delete($token);
        $this->loginAction($user,1,1);
    }
    
    /**
     * 登录动作
     */
    private function loginAction($user,$loginStatus,$isAutoLogin){
        $_SESSION['userid'] = $user['id'];
        $_SESSION['user_name']=$user['user_name'];
        $_SESSION['status']=$user['status'];
        $_SESSION['autoLogin']=$loginStatus;
         
        if ($isAutoLogin==1){
            $expire=60 * 60 * 24 * 7;
            $timeout = time() + $expire;
            $token=md5(uniqid(rand(), TRUE));
             
            $autoLogin=[
                    'user_id'=> $user['id'],
                    'status' => $user['status'],
                    'timeout' => $timeout
            ];
            $this->S()->set($token,$autoLogin,60*60*24*7);
            setcookie('HANZI-AUTOLOGIN', $token,$timeout,'/');
        }
        
        //更新用户信息
        $data=[
            'last_ip'=>ip2long(getClientIp()),
            'last_login'=>time()
        ];
        $this->table('user')->where(['id'=>$user['id']])->update($data);

        $mall_own_private = $this->table('user')->where(['id'=>$user['id']])->get(['mall_own_private'],true);
        

        $this->R(['所属商城'=>$mall_own_private]);
    }
}
