<?php 
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
                $isUser = $this->table('user_base')->where(["openid" => $accessToken['openid']])->get(null, true);
                
                if (!$isUser) {
                    //用户信息
                    $userInfo=$this->getUserInfo($accessToken);
                    $isUser=$this->saveUser($userInfo);
                    $userID=$isUser;
                }
                else{
                    $userID=$isUser['id'];
                }
                
                $updateUser = $this->table('user_base')->where(['id'=>$userID])->update(['last_login_time'=>time(),'last_ip'=>ip2long(getClientIp())]);
                
                $_SESSION['openid'] = $isUser['openid'];
                $_SESSION['userid'] = $isUser['id'];
                $_SESSION['nickname']=$isUser['nickname'];
                $_SESSION['photo']=$isUser['photo'];
                
                //dump($_SESSION);exit;
                
                //return $user;
                header("LOCATION:".$_SESSION['wechat_refer']);
        } else {
            //用户取消授权
            $this->R('','90006');
        }

 ?>