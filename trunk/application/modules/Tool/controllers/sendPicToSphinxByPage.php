<?php
//nohup php5 sendPicToSphinxByPage.php > debug.file 2>&1 &


function http_get($url)
{
    $o_curl = curl_init();
    if (stripos($url,"https://")!==FALSE) 
    {
        curl_setopt($o_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($o_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($o_curl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($o_curl, CURLOPT_URL, $url);
    curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, 1 );
    $s_content = curl_exec($o_curl);
    $a_status = curl_getinfo($o_curl);
    curl_close($o_curl);
    if (intval($a_status["http_code"])==200) {
        return $s_content;
    } else {
        return false;
    }
}


$i = 1;
$count = 700;
$limit = 1000;
$path = __DIR__.'/log.dat';
$url ='http://house.leju.com/sphinx/Picture/index';

for ($i=1; $i < $count ; $i++) { 
    $httpurl = $url.'?page='.$i.'&limit='.$limit;
    $res = http_get($httpurl, 10);

    $msg = date('Y-m-d H:i:s').'--page--'.$i.'--status--:'.$res.PHP_EOL;
    //var_dump($res);
    file_put_contents($path,$msg,FILE_APPEND);
    if($res == 'finished'){
        break;
        exit;
    }
    usleep(100);
}




?>