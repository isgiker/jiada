<?php

/**
 * @description 新版乘友SSO API
 * @author shiwei 20121225
 */
class sso {

    public static $cryptKey = '*w.KLH^F,W6jIi%kXz+K_w3%';

    public function run($param = null) {
        //参数验证
        if (!$param['case']) {
            $msg = '[' . date('Y-m-d H:i:s') . '] ' . 'param case does not exist' . "\n";
            return self::errorMessage($msg);
        }

        return self::$param['case']($param);
    }

    /**
     * -> 注册用户(用邮箱注册,邮箱需要激活)
     * @param email 邮箱
     * @param password 密码
     * @param username 用户名
     * sso.reg {"email":"1468386898@qq.com","password":"111111","username":"shiwei"}
     */
    static public function reg($parameter) {
        global $db;

        if (gettype($parameter) != 'array')
            return self::errorMessage('参数类型错误！');
        if (!$parameter['username'])
            return self::errorMessage('用户名不能为空！');
        if (!$parameter['password'])
            return self::errorMessage('密码不能为空！');
        if (!$parameter['email'])
            return self::errorMessage('邮箱地址不能为空！');

        if ($checkUserResult = self::checkUser($parameter)) {
            if ($checkUserResult['checkUser']) {
                return self::errorMessage('此昵称太受欢迎，已被人抢了！');
            } elseif ($checkUserResult['checkEmail']) {
                return self::errorMessage('邮箱已被注册！');
            }
        }

        $registerTime = time();
        $passwd = md5($parameter[password]);
        /*
         * 昵称、邮箱、手机号必须唯一;
         * status状态为0未激活，1已激活;激活目的是为了判断用户的有效性。-1冻结此帐号,-2删除此帐号
         * 暂时取消邮件激活流程，注册成功默认为激活状态。
         */
        $sql = "insert member set username='$parameter[username]',password='$passwd',email='$parameter[email]',registerTime='$registerTime',status='1'";
        if (!$db->query($sql)) {
            return self::errorMessage('新建帐号失败！');
        }

        $userID = $db->insertid();

        //如果用邮箱注册，发送激活邮件，验证邮箱有效性;
        // if ($parameter[email]) {
        //     self::sendEmail(array('uid' => $userID, 'username' => $parameter[username], 'email' => $parameter[email], 'registerTime' => $registerTime));
        // }

        return self::returnData(array('uid' => $userID));
    }

    /*
     * 检查用户是否存在;
     */

    static public function checkUser($parameter) {
        global $db;

        //参数验证
        if (!$parameter) {
            $msg = '[' . date('Y-m-d H:i:s') . '] ' . 'no param' . "\n";
            return self::errorMessage($msg);
        }

        $data = array();

        //检查“昵称”、“邮箱”、“手机”是否已经注册;
        if (empty($data) && $parameter[username]) {
            $sql = "select a.uid from member a where a.username = '$parameter[username]'";
            $checkUserResult = $db->getone($sql);
            if ($checkUserResult)
                $data['checkUser'] = $checkUserResult;
        }
        if (empty($data) && $parameter[email]) {
            $sql = "select a.uid from member a where a.email = '$parameter[email]'";
            $checkEmailResult = $db->getone($sql);
            if ($checkEmailResult)
                $data['checkEmail'] = $checkEmailResult;
        }
        return $data;
    }

    /*
     * 发送激活邮件
     * 激活链接格式,userid=&key=随机
     */

    static public function sendEmail($parameter) {
        $key = md5($parameter[username] . $parameter[email] . $parameter[registerTime]);
        $url = "http://$_SERVER[HTTP_HOST]/_api/sso.php?case=active&uid=$parameter[uid]&key=$key";

        $mail = new phpmailer();

        $mail->IsSMTP();     // 启用SMTP
        $mail->Host = "smtp.163.com";   //SMTP服务器
        $mail->SMTPAuth = true;     //开启SMTP认证
        $mail->Username = "shiw0719@163.com";   // SMTP用户名
        $mail->Password = "shiwei190786";    // SMTP密码
        // tell the class to use Sendmail
//        $mail->IsSendmail();

        $mail->From = "shiw0719@163.com";   //发件人地址
        $mail->FromName = "乘友用户中心";    //发件人
        $mail->AddAddress($parameter[email]); //添加收件人
        $mail->AddReplyTo("shiw0719@163.com", "SSO"); //回复地址
        $mail->WordWrap = 500;     //设置每行字符长度
        /* 附件设置
          $mail->AddAttachment("/var/tmp/file.tar.gz");		// 添加附件
          $mail->AddAttachment("/tmp/image.jpg", "new.jpg");	// 添加附件,并指定名称
         */
        $mail->IsHTML(true);     // 是否HTML格式邮件
        $mail->CharSet = "utf8";    // 这里指定字符集！
        $mail->Encoding = "base64";

        $mail->Subject = "欢迎使用乘友 - 注册完成，请激活帐号";   //邮件主题
        //邮件内容
        $mail->Body = "亲爱的用户：" . '<br />';
        $mail->Body .= "欢迎你使用乘友，点击以下链接激活帐号完成注册" . '<br />';
        $mail->Body .= $url . '<br />';
        $mail->Body .= "如果你的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。
此邮件由系统自动产生，请勿回复。" . '<br />';

        $mail->Body .= "如有疑问，请拨打客服电话：010–65686888。" . '<br />';

        //邮件正文不支持HTML的备用显示
        $mail->AltBody = "亲爱的用户： \n\t";
        $mail->AltBody .= "欢迎你使用乘友，点击以下链接完成注册 \n\t";
        $mail->AltBody .= "$url \n\t";
        $mail->AltBody .= "如果你的邮箱不支持链接点击，请将以上链接地址拷贝到你的浏览器地址栏中。
此邮件由系统自动产生，请勿回复。 \n\t";
        $mail->AltBody .= "如有疑问，请拨打客服电话：010-58511234。 \n\t";

        if (!$mail->Send()) {
            return self::errorMessage($mail->ErrorInfo);
        }

        return self::returnData();
    }

    /*
     * -> 激活用户帐号
     * @param uid 用户ID
     * @param key 加密字符串
     * sso.active {"uid":"","key":""}
     */

    static public function active($parameter) {
        global $db;

        //参数验证
        if (!$parameter) {
            $msg = '[' . date('Y-m-d H:i:s') . '] ' . 'no param' . "\n";
            return self::errorMessage($msg);
        }

        $sql = "select a.uid,a.username,a.email,a.registerTime from member a where a.uid = '$parameter[uid]'";
        $uInfo = $db->getrow($sql);

        if ($uInfo) {
            $key = md5($uInfo[username] . $uInfo[email] . $uInfo[registerTime]);
            if ($key == $parameter[key]) {
                $sql = "update member set status=1 where uid=$parameter[uid]";
                if (!$db->query($sql)) {
                    return self::errorMessage('激活失败！');
                }

                return self::returnData();
            } else {
                return self::errorMessage('激活链接无效！');
            }
        } else {
            return self::errorMessage('帐号不存在！');
        }
    }

    /**
     * -> 用户登录(用邮箱或用户名登录)
     * @param email 邮箱
     * @param password 密码
     * sso.login {"email":"1468386898@qq.com","password":"111111"}
     */
    static public function login($parameter) {
        global $db;

        //验证参数;这里的用户名是邮箱或手机;
        if (!$parameter['email'])
            return self::errorMessage('用户名不能为空！');
        if (!$parameter['password'])
            return self::errorMessage('密码不能为空！');

        $passwd = md5($parameter['password']);

        $sql = "select a.uid,a.username,a.email,a.status,a.avatar from member a where a.password='$passwd' and (a.email='$parameter[email]' or a.username='$parameter[email]')";
        $userInfo = $db->getrow($sql);
        if (!$userInfo) {
            return self::errorMessage('用户名或密码错误！');
        } elseif ($userInfo[status] == 0) {
            return self::errorMessage('帐号未激活！');
        } elseif ($userInfo[status] == -1) {
            return self::errorMessage('帐号被冻结！');
        } elseif ($userInfo[status] == -2) {
            return self::errorMessage('帐号已删除！');
        }

        //记录登录用户，用于统计在线用户数;
        $loginTime = time();
        $expiry = $loginTime + (6 * 3600);

        //设置token
        $token = self::getToken(array('uid' => $userInfo['uid'], 'expiry' => $expiry));

        if (!self::onlineUsers(array('token' => $token, 'uid' => $userInfo['uid'], 'expiry' => $expiry, 'loginTime' => $loginTime))) {
            return self::errorMessage('Token写入失败！');
        }

        //返回用户ID和昵称，//（客户端需要把这些信息写入COOKIE）;
        return self::returnData(array('token' => $token, 'userID' => $userInfo['uid'], 'username' => $userInfo['username']));
    }

    /**
     * -> 第三方用户登录(新浪/腾讯微博、人人网...)
     * @param username 用户名
     * @param avatar 头像
     * @param type sina_weibo|tengxun_weibo
     * @author shiwei 20130227
     * sso.externalLogin {"username":"busap","avatar":"","type":"sina_weibo"}
     */
    static public function externalLogin($parameter) {
        global $db;

        //验证参数;
        if (!$parameter['username'])
            return self::errorMessage('用户名不能为空！');

        if (!$parameter['type'])
            return self::errorMessage('第三方名称不能为空！');

        $checkUserResult = self::checkUser($parameter);

        if ($checkUserResult['checkUser']) {
            //直接登录
            //记录登录用户，用于统计在线用户数;
            $loginTime = time();
            $expiry = $loginTime + (6 * 3600);

            //设置token
            $token = self::getToken(array('uid' => $userInfo['uid'], 'expiry' => $expiry));

            if (!self::onlineUsers(array('token' => $token, 'uid' => $checkUserResult, 'expiry' => $expiry, 'loginTime' => $loginTime))) {
                return self::errorMessage('Token写入失败！');
            }

            //返回用户ID和昵称，//（客户端需要把这些信息写入COOKIE）;
            return self::returnData(array('token' => $token, 'userID' => $checkUserResult['checkUser'], 'username' => $parameter['username']));
        } else {
            //自动注册一个用户
            $password = $email = $parameter['username'] . '@' . $parameter['type'].'.com';
            self::reg(array('email' => $email, 'username' => $parameter['username'], 'password' => $password));
            self::update(array('avatar' => $parameter['avatar']));
            return self::login(array('email' => $email, 'password' => $password));
        }
    }
    
    /**
     * -> 更新用户信息
     * @param uid 用户ID
     * @param avatar 头像
     * @param gender 性别
     * @author shiwei 20130227
     * sso.update {"uid":"busap","avatar":"","gender":""}
     */
    static public function update($parameter) {
        global $db;

        //验证参数;
        if (!$parameter['uid'])
            return self::errorMessage('ID不能为空！');

        $field = array('avatar', 'gender', 'birthday', 'location');

        $setField = array();
        foreach ($field as $val) {
            if ($parameter[$val]) {
                $setField[] = $val . '=' . '\'' . $parameter[$val] . '\'';
            }
        }
        if ($setField) {
            $setField = implode(',', $setField);

            $sql = "update member set $setField where uid=$parameter[uid]";
            if (!$db->query($sql)) {
                return self::errorMessage('用户信息更新失败！');
            }
        }
        
        //用户附加信息;
        $additionalField = array('intro');
        $seAdditionaltField = array();
        foreach ($additionalField as $val) {
            if ($parameter[$val]) {
                $seAdditionaltField[] = $val . '=' . '\'' . $parameter[$val] . '\'';
            }
        }
        
        if ($seAdditionaltField) {
            $seAdditionaltField = implode(',', $seAdditionaltField);

            $sql = "update member_profile set $seAdditionaltField where uid=$parameter[uid]";
            if (!$db->query($sql)) {
                return self::errorMessage('用户信息更新失败！');
            }
        }
        
        return self::returnData($parameter);
    }
    

    /*
     * 在线用户统计;
     * 非正常退出的用户过期时间默认设置为6个小时；超过6小时定时删除过期的用户。
     * 由于用户可能会非正常退出，而客户端cookie已经不存在，这时在线用户表里可能还会记录该用户是登录状态，这时如果用户再次登录，那么只需更新过期时间就OK。
     */

    static public function onlineUsers($parameter) {
        global $db;

        $sql = "REPLACE INTO member_token set uid='$parameter[uid]',token='$parameter[token]',expiry='$parameter[expiry]',loginTime='$parameter[loginTime]'";
        if (!$db->query($sql)) {
            return false;
        }

        return true;
    }

    static public function del($uids) {
        global $db;
        if (!$uids) {
            return self::errorMessage('参数错误！');
        }
        $uid = implode(',', $uids);
        $sql = "delete from member where uid in($uid) ";
        if (!$db->query($sql)) {
            return self::errorMessage('删除失败！');
        }
        return self::returnData();
    }

    static public function remove($uids) {
        global $db;
        if (!$uids) {
            return self::errorMessage('参数错误！');
        }
        $uid = implode(',', $uids);

        $sql = "update member set status=-2 where uid in($uid) ";
        if (!$db->query($sql)) {
            return self::errorMessage('删除失败！');
        }
        return self::returnData();
    }

    static public function errorMessage($msg = 'fail', $errorCode = null) {
        $errorCode = $errorCode ? $errorCode : 404;
        $returnResult = array(
            'result' => array(
                'status' => false,
                'message' => $msg,
                'code' => $errorCode
            )
        );
        return json_encode($returnResult);
    }

    static public function returnData($data = null) {
        $returnResult = array(
            'result' => array(
                'status' => true,
                'message' => 'success',
                'code' => 200
            )
        );
        if ($data)
            $returnResult['data'] = $data;
        return json_encode($returnResult);
    }

    /**
     * -> 用户退出
     * @param token
     * sso.logout {"token":""}
     */
    static public function logout($parameter) {
        global $db;

        if (!$token) {
            return self::errorMessage('参数错误！');
        }

        $token = urldecode($parameter['token']);

        $sql = "delete from member_token where token = '$token' ";
        if (!$db->query($sql)) {
            return self::errorMessage('退出失败！');
        }
        return self::returnData();
    }

    /*
     * 构建客户端唯一ID;
     */

    public function getToken($parameter) {
        if (!$parameter)
            return false;

        $ticket = self::setToken($parameter);
        $des = new Crypt3Des(self::$cryptKey);
        $ticket = $des->encrypt($ticket);
        return urlencode($ticket);
    }

    //构建原始票据结构;
    public function setToken($parameter) {
        if (!$parameter)
            return false;

        $ticket = $parameter['uid'] . '|' . $parameter['expiry'];
        return $ticket;
    }

    /**
     * 获取用户信息
     * @param beConcerned 用户(被关注者)/线路名称/站点名称
     * @param usersid 用户ID（1,2,3,4,5,6）
     * @param 1关注人2关注途经线路3关注途经站点4关注其它线路5关注其它站点
     * sso.getUserInfo {"usersid":"10001,10002"}
     * @author shiwei 20130107
     */
    static function getUserInfo($parameter) {
        global $db;

        //验证参数
        if (gettype($parameter) != 'array')
            return self::errorMessage('参数类型错误！');
        if (!$parameter['usersid'])
            return self::errorMessage('用户唯一标识参数不能为空！');

        $sql = "select uid,username,avatar,gender,birthday from member where uid in($parameter[usersid])";
        $result = $db->getall($sql);
        return self::returnData($result);
    }

    /**
     * 获取所有用户()
     * sso.getAllUsers {"":""}
     * @author shiwei 20130109
     */
    static function getAllUsers() {
        global $db;

        $sql = "select a.uid,a.username,a.avatar,a.gender,a.birthday,a.XCoord,a.YCoord,a.status,(select loginTime from member_token where uid=a.uid) as loginTime from member a order by a.registerTime asc";
        $result = $db->getall($sql);
        return self::returnData($result);
    }

    /**
     * 获取活跃用户
     * @author shiwei 20130219
     * sso.getActiveUsers {"":""}
     */
    static function getActiveUsers() {
        global $db;

        $sql = "select a.uid,a.username,a.avatar,a.gender,a.birthday,a.XCoord,a.YCoord,a.status,b.loginTime from member a, member_token b where a.uid=b.uid order by b.loginTime desc limit 0,1000";
        $result = $db->getall($sql);
        return self::returnData($result);
    }

    /**
     * -> 附近的人(1公里以内的人)
     * @param userCoord 用户当前坐标
     * sso.nearbyUser {"userCoord":"116.4199;39.8888"}
     * @author wanghui
     */
    static function nearbyUser($parameter){
        //定位
        list($lng2, $lat2) = explode(';', $parameter['userCoord']);
        self::setUserCoord($parameter);
        //获得一个点周围n千米的正方形的四个点
        $squares= Util::returnSquarePoint($lng2,$lat2,1);
        global $db;
	$sql="select a.uid,a.username,a.avatar,a.gender,a.birthday,a.XCoord,a.YCoord,a.status,b.loginTime 
        from member a, member_token b where a.uid=b.uid and a.YCoord<>0 and a.YCoord>{$squares['right-bottom']['lat']}
        and a.YCoord<{$squares['left-top']['lat']} and a.XCoord>{$squares['left-top']['lng']} and a.XCoord<{$squares['right-bottom']['lng']}";
        
        $users = $db->getall($sql);
        return self::returnData($users);
    }
        
    
    /*
     * -> 更新用户位置和登录时间
     * @param userCoord 用户当前坐标
     * @author shiwei 20130219
     * sso.setUserCoord {"uid":"10001","userCoord":"116.4199;39.8888"}
     */

    static function setUserCoord($parameter) {
        global $db;

        //验证参数
        if (gettype($parameter) != 'array')
            return self::errorMessage('参数类型错误！');
        if (!$parameter['uid'])
            return self::errorMessage('用户ID不能为空！');
        if (!$parameter['userCoord'])
            return self::errorMessage('用户位置错误！');

        list($lng2, $lat2) = explode(';', $parameter['userCoord']);

        //更新位置
        $sql = "update member set  XCoord='$lng2', YCoord='$lat2' where uid='$parameter[uid]'";

        if (!$db->query($sql)) {
            die($sql);
        }

        //更新时间
        $loginTime = time();
        $sql = "update member_token set loginTime='$loginTime' where uid='$parameter[uid]'";
        $db->query($sql);
    }

    /**
     * -> 用户认证(认证方法有两种，这里采用数据库认证方式！)
     * @param loginUid 当前登录用户ID
     * @param token 登录凭证
     * sso.authenticate {"loginUid":"10001","token":""}
     * @author shiwei 20130206
     */
    static public function authenticate($parameter) {
        global $db;

        //验证参数
        if (gettype($parameter) != 'array')
            return self::errorMessage('参数类型错误！');
        if (!$parameter['loginUid'])
            return self::errorMessage('当前登录用户参数不能为空！');
        if (!$parameter['token'])
            return self::errorMessage('Token不能为空！');

        $sql = "select uid,expiry from member_token where token = '$parameter[token]'";
        $result = $db->getrow($sql);

        if ($parameter['loginUid'] == $result['uid'] && time() <= $result['expiry']) {
            return self::returnData(array('checkin' => true));
        } else {
            return self::returnData(array('checkin' => false));
        }
    }

}

/* ==================================开始执行...================================ */
//签名验证：
//Util::checkSign();

$className = basename($_SERVER['PHP_SELF'], '.php');
if ($className == 'interface') {
    //连接采集数据库
    $db = getDBO($_config_db['sso2.0']);
} else {
    /* 加载全局文件 */
    require_once('../global.php');

//连接采集数据库
    $db = getDBO($_config_db['sso2.0']);

//获取参数;
    $param = Request::_addslashes($_REQUEST);
    eval('$result=' . $className . '::run($param);');
    die($result);
}
