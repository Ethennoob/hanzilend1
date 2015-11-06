<?php
/**
 * 盛世分销系统---微信验证类
 * @authors Your Name (you@example.org)
 * @date    2015-10-30 14:58:35
 * @version $Id$
 */

namespace AppMain\controller\Api;
use \System\BaseClass;

class VerifyController extends Baseclass {


    /**
     * 授权
     */
    public function getOpenID(){
        if (isset($_GET['wechat_refer'])){  //回跳地址
            $_SESSION['wechat_refer']=urldecode($_GET['wechat_refer']);
        }

        $weObj = new \System\lib\Wechat\Wechat($this->config("WEIXIN_CONFIG"));
        $this->weObj = $weObj;
        if (empty($_GET['code']) && empty($_GET['state'])) {
            $callback = getHostUrl();
            $reurl = $weObj->getOauthRedirect($callback, "1");
            redirect($reurl, 0, '正在发送验证中...');
            exit(); 
        } elseif (intval($_GET['state']) == 1) {
                $accessToken = $weObj->getOauthAccessToken();
                
                // 是否有用户记录
                $isUser = $this->table('user')->where(["openid_line" => $accessToken['openid']])->get(null, true);
                
                if (!$isUser) {
                	$this->R('','2');//跳转至输入电话号码的页面
                }else{
                	$userID=$isUser['id'];
                }
                
                $isUser = $this->table('user')->where(['id'=>$userID])->update(['last_login'=>time(),'last_ip'=>ip2long(getClientIp())]);
                
                $_SESSION['openid'] = $isUser['openid_line'];
                $_SESSION['userid'] = $isUser['id'];
                $_SESSION['nickname']=$isUser['nickname'];
                $_SESSION['user_img']=$isUser['user_img'];
                
                //return $user;
                header("LOCATION:".$_SESSION['wechat_refer']);
        } else {
            //用户取消授权
            $this->R('','90006');
        }
    }
    /**
     * 新用户从微信注册
     */
    public function getNewOpenID(){
        if (isset($_GET['wechat_refer'])){  //回跳地址
            $_SESSION['wechat_refer']=urldecode($_GET['wechat_refer']);
        }

        $weObj = new \System\lib\Wechat\Wechat($this->config("WEIXIN_CONFIG"));
        $this->weObj = $weObj;
        if (empty($_GET['code']) && empty($_GET['state'])) {
            $callback = getHostUrl();
            $reurl = $weObj->getOauthRedirect($callback, "1");
            redirect($reurl, 0, '正在发送验证中...');
            exit(); 
        } elseif (intval($_GET['state']) == 1) {
                $accessToken = $weObj->getOauthAccessToken();
                
                $rule = [
                'mobile_phone'   =>['mobile'],
                ];
                $this->V($rule);
                foreach ($rule as $k=>$v){
                    $mobile[$k] = $_POST[$k];
                }
                $isUserId = $this->table('user')->where(["mobile_phone" => $mobile['mobile_phone']])->get(['id'], true);
                if (!$isUserId) {
                    //用户信息
                    $userInfo=$this->getUserInfo($accessToken);
                    $saveUser=$this->saveUser($userInfo);//插入新会员数据
                    if (!$saveUser) {
                            $this->R('','40001');
                    }
                }else{
                    foreach ($isUserId as $k=> $id) {
                        $saveUser=$this->saveUserById($userInfo,$id);//通过已注册手机号插入新会员数据
                        if (!$saveUser) {
                            $this->R('','40001');
                        }
                    }
                }
                //return $user;
                header("LOCATION:".$_SESSION['wechat_refer']);
        } else {
            //用户取消授权
            $this->R('','90006');
        }
    }
    /**
     * 获取用户信息
     */
    private function getUserInfo($user){
        $user_info = $this->weObj->getOauthUserinfo($user['access_token'], $user['openid']);

        if (!$user_info){
            die("系统错误，请稍后再试！");
        }

        //是否关注
        $isFollow=$this->weObj->getUserInfo($user['openid']);
        if ($isFollow['subscribe']==1){
        	$user_info['is_follow']=1;
        }
        else{
        	$user_info['is_follow']=0;
        }

        return $user_info;
    }
    /**
     * 保存用户
     */
    private function saveUser($user_info){

        $data = array(
            'openid_line' => $user_info['openid'],
            'sex' => $user_info['sex'],
            'mobile_phone' =>$mobile['mobile_phone'],
            'user_img' => $user_info['headimgurl'],
            'nickname' => $user_info['nickname'],
        	'is_follow'=>$user_info['is_follow'],
            'add_time' => time()
        );
        $result=$this->table('user')->save($data);
        if (!$result){
            die("系统错误，请稍后再试！");
        }

        return $data;
    }
    /**
     * 保存已有用户的微信信息
     */
    private function saveUserById($user_info,$id){

        $data = array(
            'openid_line' => $user_info['openid'],
            'sex' => $user_info['sex'],
            'user_img' => $user_info['headimgurl'],
            'nickname' => $user_info['nickname'],
        	'is_follow'=>$user_info['is_follow'],
            'add_time' => time()
        );
        $result=$this->table('user')->where(['id'=>$id])->update($data);
        if (!$result){
            die("系统错误，请稍后再试！");
        }
        return $data;
    }
}