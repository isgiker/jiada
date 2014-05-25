<?php

/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @see http://www.php.net/manual/en/yaf-dispatcher.catchexception.php
 * @author root
 */
class ErrorController extends Yaf_Controller_Abstract {

    //从2.1开始, errorAction支持直接通过参数获取异常
    public function errorAction($exception) {
        if (TJ_EVN == 'development') {
            switch ($exception->getCode()) {
                // case YAF_ERR_AUTOLOAD_FAILED:
                // case YAF_ERR_NOTFOUND_MODULE:
                // case YAF_ERR_NOTFOUND_CONTROLLER:
                // case YAF_ERR_NOTFOUND_ACTION:
                // case YAF_ERR_NOTFOUND_VIEW:
                //     header('HTTP/1.1 404 Not Found');
                //     break;
                default:
                    //print_r('expression');
                    Yaf_Dispatcher::getInstance()->enableView();
                    //App::logException($exception, TRUE);
                    $this->getView()->assign('exception', $exception);
                    break;
            }
        } else {
            Yaf_Dispatcher::getInstance()->disableView();
            switch ($exception->getCode()) {
                case YAF_ERR_AUTOLOAD_FAILED:
                case YAF_ERR_NOTFOUND_MODULE:
                case YAF_ERR_NOTFOUND_CONTROLLER:
                case YAF_ERR_NOTFOUND_ACTION:
                case YAF_ERR_NOTFOUND_VIEW:
                    header('HTTP/1.1 404 Not Found');
                    break;
                default:
                    header('HTTP/1.1 500 Internal Server Error');
                    App::logException($exception, TRUE);
                    break;
            }
        }
    }

    /**
     * 此时可通过$request->getException()获取到发生的异常
     */
    public function error2Action() {
        $exception = $this->getRequest()->getException();
        try {
            throw $exception;
        } catch (Yaf_Exception_LoadFailed $e) {
            //加载失败
        } catch (Yaf_Exception $e) {
            //其他错误
        }
    }

}
