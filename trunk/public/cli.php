<?php
/*
 * @index   楼盘库优化 HOUSE V1.0
 * @author  jingfu@leju.com
 * @creat   2016/08/04
 */
date_default_timezone_set("PRC");
//开启utf编码
header('content-type:text/html;charset=utf-8');

//定义楼盘库标识
define("HOUSE_VERSION","V1.0");

//定义目录
define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */

// 设置为“开发调试”模式
define ( "APP_DEBUG", true ); // 调试
//define("APP_DEBUG",false);//生产
if(APP_DEBUG){
    error_reporting(E_ALL);//使用error_reporting来定义哪些级别错误可以触发
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}
error_reporting(E_ALL);
// $request = new Yaf\Request\Simple();
// print_r($request);exit();
$application = new \Yaf\Application( APP_PATH . "/conf/application.ini");
$application->bootstrap()->getDispatcher()->dispatch(new Yaf\Request\Simple());

?>
