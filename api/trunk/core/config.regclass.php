<?php

class JRegClass {

    function __construct() {
        
    }
    
    /*fatory.php 工厂类*/
    public function Factory(){return LIBRARY_PATH.DS.'factory.php';}

    /* Aes 加密 */

    public function Aes() {
        return LIBRARY_PATH . DS . 'aes.php';
    }
    
    /**@core --- begin
     * ===============================================================================================================
     */
    /* util.php 通用类 */
    public function Util() {
        return CORE_PATH . DS . 'util.php';
    }
    

    /**@Libraries --- begin
     * ===============================================================================================================
     */
    /*libraries/config/ 解析配置文件*/
    public function Parse(){return LIBRARY_PATH.DS.'config'.DS.'Parse.php';}
    public function Parse_ini(){return LIBRARY_PATH.DS.'config'.DS.'Parse_ini.php';}
    
    /* librarie/ 数据请接收 */
    public function Request() {
        return LIBRARY_PATH . DS . 'Request.php';
    }
    
    /* librarie/ 模板加载 */
    public function Template() {
        return LIBRARY_PATH . DS . 'template.php';
    }

    

    /* PHPMailer 邮件类 */

    public function PHPMailer() {
        return LIBRARY_PATH . DS . 'phpmailer' . DS . 'class.phpmailer.php';
    }

    /* Crypt3Des 加密 */

    public function Crypt3Des() {
        return LIBRARY_PATH . DS . 'crypt3des.php';
    }

}