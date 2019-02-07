<?php
$server = array();
/*******************************  mysql配置  *****************************/
//线上
$server['database']['SINASRV_DB_HOST'] = isset($_SERVER['SINASRV_DB_HOST']) ? $_SERVER['SINASRV_DB_HOST'] : '127.0.0.1';
$server['database']['SINASRV_DB_PORT'] = isset($_SERVER['SINASRV_DB_PORT']) ? $_SERVER['SINASRV_DB_PORT'] : '3306';
$server['database']['SINASRV_DB_USER'] = isset($_SERVER['SINASRV_DB_USER']) ? $_SERVER['SINASRV_DB_USER'] : 'zlc';
$server['database']['SINASRV_DB_PASS'] = isset($_SERVER['SINASRV_DB_PASS']) ? $_SERVER['SINASRV_DB_PASS'] : '123';
$server['database']['SINASRV_DB_NAME'] = isset($_SERVER['SINASRV_DB_NAME']) ? $_SERVER['SINASRV_DB_NAME'] : 'bishe';
$server['database']['SINASRV_DB_HOST_R'] = isset($_SERVER['SINASRV_DB_HOST_R']) ? $_SERVER['SINASRV_DB_HOST_R'] : '127.0.0.1';
$server['database']['SINASRV_DB_PORT_R'] = isset($_SERVER['SINASRV_DB_PORT_R']) ? $_SERVER['SINASRV_DB_PORT_R'] : '3306';
$server['database']['SINASRV_DB_USER_R'] = isset($_SERVER['SINASRV_DB_USER_R']) ? $_SERVER['SINASRV_DB_USER_R'] : 'zlc';
$server['database']['SINASRV_DB_PASS_R'] = isset($_SERVER['SINASRV_DB_PASS_R']) ? $_SERVER['SINASRV_DB_PASS_R'] : '123';
$server['database']['SINASRV_DB_NAME_R'] = isset($_SERVER['SINASRV_DB_NAME_R']) ? $_SERVER['SINASRV_DB_NAME_R'] : 'bishe';


$config['database'] = array(
    'driver'=>'mysql',
    'read'=> array(
        'host'=> $server['database']['SINASRV_DB_HOST_R'],
        'port'=> $server['database']['SINASRV_DB_PORT_R'],
        'username' => $server['database']['SINASRV_DB_USER_R'],
        'password' => $server['database']['SINASRV_DB_PASS_R'],
        'database' => $server['database']['SINASRV_DB_NAME_R'],
    ),
    'write'=> array(
        'host'=> $server['database']['SINASRV_DB_HOST'],
        'port'=> $server['database']['SINASRV_DB_PORT'],
        'username' => $server['database']['SINASRV_DB_USER'],
        'password' => $server['database']['SINASRV_DB_PASS'],
        'database' => $server['database']['SINASRV_DB_NAME'],
    ),
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'=> '',
    'strict'=> ''
);

//除了config变量,其他变量全部unset
unset($server, $redis_cluster, $redis_cluster_host_post, $redis_queue_host_port, $memcached_config, $memcached_host_port);