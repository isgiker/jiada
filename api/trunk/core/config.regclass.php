<?php

class JRegClass {

    function __construct() {
        
    }
    
    /* Aes 加密 */

    public function Aes() {
        return JPATH_LIBRARIES . DS . 'aes.php';
    }

    /**
     * @/core/ ---begin
     */
    /* util.php 通用类 */
    public function Util() {
        return JPATH_CORE . DS . 'util.php';
    }

    /**
     * @/Libraries/ ---begin
     */
    /* librarie/ 数据请接收 */
    public function Request() {
        return JPATH_LIBRARIES . DS . 'request.php';
    }
    
    /* librarie/ 模板加载 */
    public function Template() {
        return JPATH_LIBRARIES . DS . 'template.php';
    }

    /* PHPMailer 邮件类 */

    public function PHPMailer() {
        return JPATH_LIBRARIES . DS . 'phpmailer' . DS . 'class.phpmailer.php';
    }
    
    /* Crypt3Des 加密 */

    public function Crypt3Des() {
        return JPATH_LIBRARIES . DS . 'crypt3des.php';
    }

}