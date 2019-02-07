<?php
/*
 * @index   楼盘库优化 HOUSE V1.0
 * @author  jingfu@leju.com
 * @creat   2016/08/04
 */
//开启utf编码
header('content-type:text/html;charset=utf-8');
ini_set('date.timezone','Asia/Shanghai');

//定义楼盘库标识
define("HOUSE_VERSION","V1.0");
//定义目录
define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向public的上一级 */
$application = new Yaf\Application( APP_PATH . "/conf/application.ini");

$application->bootstrap()->run();