<?php
if(Yaf\ENVIRON =='product'){
$server['mongodb']['SINASRV_MONGO_HOST_R'] = 's9300.yz.loupan.grid.house.sina.com.cn';
$server['mongodb']['SINASRV_MONGO_PORT_R'] = '9300';
$server['mongodb']['SINASRV_MONGO_HOST'] = 'm9300.yz.loupan.grid.house.sina.com.cn';
$server['mongodb']['SINASRV_MONGO_PORT'] = '9300';
$server['mongodb']['SINASRV_MONGO_USER_R'] = 'loupan';
$server['mongodb']['SINASRV_MONGO_PASS_R'] = '13n[uytiqhUlnJ';
$server['mongodb']['SINASRV_MONGO_DBNAME_R'] = 'loupan_leju_com';
$server['mongodb']['SINASRV_MONGO_USER'] = 'loupan';
$server['mongodb']['SINASRV_MONGO_PASS'] = '13n[uytiqhUlnJ';
$server['mongodb']['SINASRV_MONGO_DBNAME'] = 'loupan_leju_com';
} else {
$server['mongodb']['SINASRV_MONGO_HOST'] = 'm9001.yz.bchloupan.grid.house.sina.com.cn';
$server['mongodb']['SINASRV_MONGO_PORT'] = '9001';
$server['mongodb']['SINASRV_MONGO_HOST_R'] = 's9001.yz.bchloupan.grid.house.sina.com.cn';
$server['mongodb']['SINASRV_MONGO_PORT_R'] = '9001';
$server['mongodb']['SINASRV_MONGO_USER_R'] = 'bchloupan';
$server['mongodb']['SINASRV_MONGO_PASS_R'] = '13n[uytiqhUlnJ';
$server['mongodb']['SINASRV_MONGO_DBNAME_R'] = 'test_loupan_leju_com';
$server['mongodb']['SINASRV_MONGO_USER'] = 'bchloupan';
$server['mongodb']['SINASRV_MONGO_PASS'] = '13n[uytiqhUlnJ';
$server['mongodb']['SINASRV_MONGO_DBNAME'] = 'test_loupan_leju_com';


/*
$server['mongodb']['SINASRV_MONGO_HOST'] = 'm9001.yz.bchloupan.grid.house.sina.com.cn';
$server['mongodb']['SINASRV_MONGO_PORT'] = '9001';
$server['mongodb']['SINASRV_MONGO_HOST_R'] = 's9001.yz.bchloupan.grid.house.sina.com.cn';
$server['mongodb']['SINASRV_MONGO_PORT_R'] = '9001';
$server['mongodb']['SINASRV_MONGO_USER_R'] = 'bchloupan';
$server['mongodb']['SINASRV_MONGO_PASS_R'] = '13n[uytiqhUlnJ';
$server['mongodb']['SINASRV_MONGO_DBNAME_R'] = 'test_loupan_leju_com';
$server['mongodb']['SINASRV_MONGO_USER'] = 'bchloupan';
$server['mongodb']['SINASRV_MONGO_PASS'] = '13n[uytiqhUlnJ';
$server['mongodb']['SINASRV_MONGO_DBNAME'] = 'test_loupan_leju_com';*/
}



if(Yaf\ENVIRON =='product'){
        //数据库
    $CliConfig['database'] = array(
        'driver'=>'mysql',
        'read'=> array(
                'host'=> 's3353.yz.datahouse.grid.house.sina.com.cn',
                'port'=> '3353' ,
                'username' => 'datahouse',
                'password' => '55EF3Abvje@&a',
                'database' => 'data_house_sina_com_cn',
            ),
        'write'=> array(
                'host'=> 'm3353.yz.datahouse.grid.house.sina.com.cn',
                'port'=> '3353',
                'username' => 'datahouse',
                'password' => '55EF3Abvje@&a',
                'database' => 'data_house_sina_com_cn',
            ),
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'=> '',
        'strict'=> ''
    );

    $CliConfig['juli'] = array(
        'driver'=>'mysql',
        'read'=> array(
            'host'=> 's4119.bj.julive.grid.house.sina.com.cn',
            'port'=> '4119',
            'username' => 'julive_prod_user',
            'password' => 'i807aPUXyjV3lmny',
            'database' => 'julive',
        ),
        'write'=> array(
            'host'=> 'm4119.bj.julive.grid.house.sina.com.cn',
            'port'=> '4119',
            'username' => 'julive_prod_user',
            'password' => 'i807aPUXyjV3lmny',
            'database' => 'julive',
        ),
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'=> '',
        'strict'=> ''
        );

 } else {
            //数据库
    $CliConfig['database'] = array(
        'driver'=>'mysql',
        'read'=> array(
                 'host'=> 's3353.yz.bchdatahousenew.grid.house.sina.com.cn',
                 'port'=> '3353' ,
                // 'host'=> 'yz.mytest.leju.com',
                // 'port'=> '63353',
                'username' => 'loupantest',
                'password' => 'v0oMrlie7iV=hs', 
                'database' => 'data_house_sina_com_cn',
            ),
        'write'=> array(
                'host'=> 'm3353.yz.bchdatahousenew.grid.house.sina.com.cn',
                'port'=> '3353' ,
                // 'host'=> 'yz.mytest.leju.com',
                // 'port'=> '63353',
                'username' => 'loupantest',
                'password' => 'v0oMrlie7iV=hs',
                'database' => 'data_house_sina_com_cn',
            ),
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'=> '',
        'strict'=> ''
    );

    //JU LI
    //本地
    // $CliConfig['juli'] = array(
    //     'driver'=>'mysql',
    //     'read'=> array(
    //         'host'=> 'yz.mytest.leju.com',
    //         'port'=> '58111',
    //         'username' => 'julive_bch_user',
    //         'password' => 'ecg9rZo7o3aDiZLZ',
    //         'database' => 'bch_julive',
    //     ),
    //     'write'=> array(
    //         'host'=> 'yz.mytest.leju.com',
    //         'port'=> '58111',
    //         'username' => 'julive_bch_user',
    //         'password' => 'ecg9rZo7o3aDiZLZ',
    //         'database' => 'bch_julive',
    //     ),
    //     'charset' => 'utf8',
    //     'collation' => 'utf8_general_ci',
    //     'prefix'=> '',
    //     'strict'=> ''
    //     );
        //测试
        $CliConfig['juli'] = array(
            'driver'=>'mysql',
            'read'=> array(
                'host'=> '10.204.11.158',
                'port'=> '8111',
                'username' => 'julive_bch_user',
                'password' => 'ecg9rZo7o3aDiZLZ',
                'database' => 'bch_julive',
            ),
            'write'=> array(
                'host'=> '10.204.11.158',
                'port'=> '8111',
                'username' => 'julive_bch_user',
                'password' => 'ecg9rZo7o3aDiZLZ',
                'database' => 'bch_julive',
            ),
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'=> '',
            'strict'=> ''
            );
 }


//mongodb
$CliConfig['mongodb'] = array(
    'read'=> array(
        'host' => $server['mongodb']['SINASRV_MONGO_HOST_R'],
        'port' => $server['mongodb']['SINASRV_MONGO_PORT_R'],
        'username' => $server['mongodb']['SINASRV_MONGO_USER_R'],
        'password' => $server['mongodb']['SINASRV_MONGO_PASS_R'],
        'database' => $server['mongodb']['SINASRV_MONGO_DBNAME_R'],
    ),
    'write'=> array(
        'host'=> $server['mongodb']['SINASRV_MONGO_HOST'],
        'port'=> $server['mongodb']['SINASRV_MONGO_PORT'],
        'username' => $server['mongodb']['SINASRV_MONGO_USER'],
        'password' => $server['mongodb']['SINASRV_MONGO_PASS'],
        'database' => $server['mongodb']['SINASRV_MONGO_DBNAME'],
    ),
    'dsn' => 'mongodb://' . $server['mongodb']['SINASRV_MONGO_USER'] . ':' . $server['mongodb']['SINASRV_MONGO_PASS'] . '@' . $server['mongodb']['SINASRV_MONGO_HOST'] . ':' . $server['mongodb']['SINASRV_MONGO_PORT'] . ',' . $server['mongodb']['SINASRV_MONGO_USER_R'] . ':' . $server['mongodb']['SINASRV_MONGO_PASS_R'] . '@' . $server['mongodb']['SINASRV_MONGO_HOST_R'] . ':' . $server['mongodb']['SINASRV_MONGO_PORT_R'],
    'options' => array(
        "connect" => true,
        "db" => $server['mongodb']['SINASRV_MONGO_DBNAME']
    )
);

//本地测试
if ($server['mongodb']['SINASRV_MONGO_HOST'] == '127.0.0.1') {

    $CliConfig['mongodb']['dsn'] = 'mongodb://127.0.0.1';
}


//redis
if(Yaf\ENVIRON =='product'){
    $redis_cluster = array(
    '10.204.10.122:7503',
    '10.204.10.122:7504',
    '10.204.10.123:7503',
    '10.204.10.123:7504',
    '10.204.10.124:7503',
    '10.204.10.124:7504',
    // '10.204.10.125:7503',
    // '10.204.10.125:7504'
    );
} else {
    $redis_cluster = array(
      /*  '10.204.10.122:7501',
        '10.204.10.122:7502',
        '10.204.10.123:7501',
        '10.204.10.123:7502',
        '10.204.10.124:7501',
        '10.204.10.124:7502',*/
       // '127.0.0.1:6379'
     '10.204.10.122:7501',
     '10.204.10.122:7502'
    );

}
//$redis_cluster = explode(' ', $server['redis']['SINASRV_REDIS_CLUSTER_HOST']);
foreach ($redis_cluster as $key => $value) {
    $redis_cluster_host_post = explode(':', $value);
    $redis_cluster[$key] = array(
        'host' => $redis_cluster_host_post[0],
        'port' => $redis_cluster_host_post[1]
    );
}

$CliConfig['redis']['cluster'] = $redis_cluster;
//10.204.10.107:7531
if(Yaf\ENVIRON =='product'){
    $redis_queue_host_port = explode(':', '10.204.10.107:7531');
} else {
    $redis_queue_host_port = explode(':', '10.204.10.107:7581'); 
    //$redis_queue_host_port = explode(':', '127.0.0.1:6379');
}

$CliConfig['redis']['queue'] = array(
    'host' => $redis_queue_host_port[0],
    'port' => $redis_queue_host_port[1]
);

if(Yaf\ENVIRON =='product'){
    $memcached_config[0] = '10.204.10.113:7601';
} else {
    $memcached_config[0] = '10.204.14.191:17608';
}

foreach ($memcached_config as $key => $value) {
    $memcached_host_port = explode(':', $value);
    $memcached_config[$key] = array(
        'host' => $memcached_host_port[0],
        'port' => $memcached_host_port[1]
    );
}

$CliConfig['memcached'] = $memcached_config;
/*-------------------------以下是测试环境----------------------------------------*/
//数据库
// $CliConfig['database'] = array(
//     'driver'=>'mysql',
//     'read'=> array(
//             'host'=> '10.207.0.114',
//             'port'=> '3353' ,
//             'database' => 'data_house_sina_com_cn',
//             'username' => 'datahouseuser',
//             'password' => 'daTA@user123',
//         ),
//     'write'=> array(
//             'host'=> '10.207.0.114',
//             'port'=> '3353',
//             'database' => 'data_house_sina_com_cn',
//             'username' => 'datahouseuser',
//             'password' => 'daTA@user123',
//         ),

//     'charset' => 'latin1',
//     'collation' => 'latin1_bin',
//     'prefix'=> '',
//     'strict'=> ''
// );

// //mongodb
// $CliConfig['mongodb'] = array(
//     'host' => '127.0.0.1:27017',
//     'port' => '9001',
//     'database' => 'test_loupan_leju_com',
//     'username' => 'bchloupan',
//     'password' => '13n[uytiqhUlnJ'
// );

// //redis
// $redis_host_port = explode(':', '127.0.0.1:6379');
// $CliConfig['redis'] = array(
//     'host' => '127.0.0.1',
//     'port' => '6379'
// );
// $redis_queue_host_port = explode(':', '127.0.0.1:6379');

// $CliConfig['redis']['queue'] = array(
//     'host' => $redis_queue_host_port[0],
//     'port' => $redis_queue_host_port[1]
// );
// //memcached
// $CliConfig['memcached'] = array(

// );

