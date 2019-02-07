<?php
/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @see http://www.php.net/manual/en/yaf-dispatcher.catchexception.php
 * @author root
 */
class ErrorController extends \Yaf\Controller_Abstract
{
	public function errorAction($exception)
    {
        //对于那些在新项目中还未完成的页面，重定向到sina域以便用户可以继续访问　add by tinglei 2016-11-10
        define('YAF_ERR_NOTFOUND_MODULE',       515); //找不到ｍodule
        define('YAF_ERR_NOTFOUND_CONTROLLER',   516); //找不到controller
        define('YAF_ERR_NOTFOUND_ACTION',       517); //找不到action
        $err_code = $exception->getCode();
        if (\Yaf\ENVIRON === 'develop' || (isset($_SERVER['SINASRV_IS_LOCAL']) && $_SERVER['SINASRV_IS_LOCAL']==='1') || (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1') || (isset($_GET['dbg']) && $_GET['dbg'] == '1')) {
            echo  $exception->getMessage();
            exit();
        } else {
            if ($err_code == 515 || $err_code == 516 || $err_code == 517) {
                //同时记录下404
                \App\Models\RedisDataQuery::getInstance()->log404($_SERVER['REQUEST_URI']);
                $this->redirect('http://www.leju.com/404');
                exit();
            }
        }
        return false;
	}
}
