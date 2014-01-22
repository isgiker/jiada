<?php

/**
 * @abstract 错误处理
 * @author 石维(shiwei)
 * @copyright 版权归Jframe所有
 * @version 1.0
 * @2011-01-14
 * 用PHP自定义函数调试页面;
 */
class Core_Error {
    /**
     * 设置自定义的错误处理函数
     * set_error_handler(error_function,error_types);
     * error_types 可选;规定在哪个错误报告级别会显示用户定义的错误,默认是 "E_ALL";
     * 1	E_ERROR
     * 2	E_WARNING
     * 4	E_PARSE
     * 8	E_NOTICE
     * 16	E_CORE_ERROR
     * 32	E_CORE_WARNING
     * 64	E_COMPILE_ERROR
     * 128	E_COMPILE_WARNING
     * 256	E_USER_ERROR
     * 512	E_USER_WARNING
     * 1024	E_USER_NOTICE
     * 2048	E_STRICT
     * 4096	E_RECOVERABLE_ERROR
     * 8191	E_ALL
     */
    public static function attachHandler($_setting) {
        //如果开启debug页面显示，否则记录日志;
        if($_setting->setting->debug){
            echo set_error_handler(array('Core_Error', 'showCustomError'), $_setting->setting->errorLevel);
        }else{
            echo set_error_handler(array('Core_Error', 'writeErrorLog'), $_setting->setting->errorLevel);
        }       
        
    }

    /**
     * 参数:error_function(error_level,error_message,error_file,error_line,error_context)
     * error_level必需。为用户定义的错误规定错误报告级别。必须是一个值数。
     * error_message	必需。为用户定义的错误规定错误消息。
     * error_file	可选。规定错误在其中发生的文件名。
     * error_line	可选。规定错误发生的行号。
     * error_context	可选。规定一个数组，包含了当错误发生时在用的每个变量以及它们的值。
     */
    public static function showCustomError($errno, $errstr, $errfile, $errline, $context = NULL) {
        //错误级别：一般,不暴露文件位置;
        $errormsg = "<b>Yaf-Jframework Error:</b> [$errno] $errstr<br /> ";
        //错误级别：高级,暴露文件位置;
        $errormsg .= "Error in $errfile  on line $errline<br />";
        echo("$errormsg");
    }

    public static function writeErrorLog($errno, $errstr, $errfile, $errline, $context = NULL) {
        $currentTime = date("Y-m-d H:i:s");
        $errortype = array(
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parsing Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Runtime Notice',
            E_RECOVERABLE_ERROR => 'Catchable Fatal Error'
        );

        $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

        //组织日志内容格式;
        $err = "[" . $currentTime . "]";
        $err .= " " . $errno;
        $err .= " " . $errortype[$errno];
        $err .= " " . $errstr;
        $err .= " " . $errfile;
        $err .= " " . $errline . "\n";

        //把一般变量以ＸＭＬ格式输出;
        if (in_array($errno, $user_errors)) {
            $err .= "\t<vartrace>" . wddx_serialize_value($context, "WDDX Packet(Yaf-Jframe Variables)") . "</vartrace>\n";
        }

        $currentDate = date('Ymd');
        $logPath = PUBLIC_PATH . DS . "log" . DS . $currentDate;

        File_Local::mkdir_r($logPath);

        //将错误写入日志文件;
        error_log($err, 3, $logPath . DS . "error_" . $currentDate . ".log");
    }


}