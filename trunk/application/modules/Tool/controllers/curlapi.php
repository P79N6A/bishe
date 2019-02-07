<?php

/*  请不要执行这段代码，这段代码是提供给调用API的业务方专用
 *  请求楼盘库API接口算法
 *  @param   noncestr  必填
 *  @param   signature 必填
 *  @param   other     选填（与楼盘相关的请填写site和hid参数）
 *  @author  jingfu@leju.com
 *  @create  2016/09/06
 */

/****************************参数**********************************/

$city = 'bj';
$hid = '129423';
$noncestr = encode( time().':'.generate_noncestr() );
$arrdata = array(
    'noncestr'=>$noncestr,
    'city'=>$city,
    'hid'=>$hid
);

$signature = get_signature($arrdata);

/************************请求楼盘库API*****************************/
$api_url = "http://new.data.house.sina.com.cn/Api/Fangshou/getHouseInfo?city=bj&hid=129423&noncestr={$noncestr}&signature={$signature}&return=json&encode=utf-8&test=1";
$result = http_get($api_url);
var_dump($result);
//get api_url to get data

/**
 * 获取签名
 * @param array $arrdata 签名数组
 * @param string $method 签名方法
 * @return boolean|string 签名值
 */
function get_signature($arrdata,$method="sha1") 
{
    if (!function_exists($method)) return false;
    $new_array = array();
    foreach($arrdata as $key => $value)
    {
        array_push($new_array,(string)$value);
    }
    sort($new_array,SORT_STRING);//value值进行字符串的字典序排序
 
    ksort($arrdata);//按照键值排序
    $paramstring = "";
    foreach($arrdata as $key => $value)
    {
        if(strlen($paramstring) == 0)
            $paramstring .= $key . "=" . $value;
        else
            $paramstring .= "&" . $key . "=" . $value;
    }
    
    $sign = $method($paramstring.implode($new_array));
    return $sign;
}

/**
 * 生成随机字串
 * @param number $length 长度，默认为16，最长为32字节
 * @return string
 */
function generate_noncestr($length=6){
    // 密码字符集，可任意添加你需要的字符
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for($i = 0; $i < $length; $i++)
    {
        $str .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $str;
}

/**
* 
* 解析客户端参数
* @param string [$app_key:$app_module:$timestamp:generate_noncestr()]
* @return string
* @author jingfu@leju.com/1662
* @create 2016/09/05
*/
function encode($str)
{
    if(!$str){
        return '';
    }
    return base64_encode(substr(md5($str),0,8).base64_encode($str).substr(md5($str),10,4));
}

/**
* 
* 解析客户端参数
* @param  url 请求地址
* @return string
* @author jingfu@leju.com/1662
* @create 2016/09/05
*/
function http_get($url)
{
    $o_curl = curl_init();
    if (stripos($url,"https://")!==FALSE) 
    {
        curl_setopt($o_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($o_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($o_curl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (strpos($url,'api.mg.cric.com') !== false) 
    {//克而瑞
        $url = str_replace('api.mg.cric.com','114.80.163.182',$url);
        curl_setopt($o_curl, CURLOPT_TIMEOUT, 50);
        curl_setopt($o_curl, CURLOPT_URL, $url);
        curl_setopt($o_curl, CURLOPT_HTTPHEADER,array("Host: api.mg.cric.com"));
    } elseif (strpos($url,'admin.kft.house.sina.com.cn') !== false) 
    {//看房团
        $url = str_replace('admin.kft.house.sina.com.cn','58.83.214.228',$url);
        curl_setopt($o_curl, CURLOPT_TIMEOUT, 50);
        curl_setopt($o_curl, CURLOPT_URL, $url);
        curl_setopt($o_curl, CURLOPT_HTTPHEADER,array("Host: admin.kft.house.sina.com.cn"));
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





/*****************************************************/
/*****************************************************/
/*****************************************************/
/*****************************************************/
/*****************外部公司API专用加密********************/
/*****************************************************/
/*****************************************************/
/*****************************************************/
/*****************************************************/
/*****************************************************/


/*  提供给外部公司调用API的业务方专用
 *  请求楼盘库API接口算法
 *  @param   noncestr  必填
 *  @param   signature 必填
 *  @param   other     选填（与楼盘相关的请填写site和hid参数）
 *  @author  jingfu@leju.com
 *  @create  2017/07/18
 */

/****************************参数**************************/

//时区设置
ini_set('date.timezone','Asia/Shanghai');

//今日头条appkey
$appkey = '90f456b81afce808';
$page = 2;
$limit = 100;
$city = 'beihai';

//密钥生成
$noncestr = encode(time().':'.$appkey);

//参数拼装
$arrdata = array(
    'appkey'=>$appkey,
    'noncestr'=>$noncestr,
    'page'=>$page,
    'limit'=>$limit,
    'city'=>$city
);
//签名生成
$signature = get_signature($arrdata, 'md5', $appkey);


/************************请求楼盘库API*********************/


//$api_url = "http://api.house.leju.com/api/Toutiao/getHouseinfoByHid?appkey=$appkey&city=$city&page=$page&limit=$limit&signature={$signature}&noncestr=$noncestr&return=json&encode=utf-8&clearm=1";

$api_url = "http://api.house.leju.com/api/Toutiao/getHouseinfoByHid?appkey=$appkey&city=$city&page=$page&limit=100&signature={$signature}&noncestr=$noncestr&return=json&encode=utf-8&clearm=1";

//输出调用api url
echo $api_url;

//curl请求api
$result = http_get($api_url);

//输出返回json串
echo $result;

//打印返回结果
var_dump(json_decode($result,true));


/**
 * 获取签名
 * @param  $arrdata  array   签名数组
 * @param  $method   string  签名方法
 * @param  $appkey   string  签名方法
 * @return $data     string  签名值
 * @author jingfu@leju.com
 * @create 2017/07/20
 */
function get_signature($arrdata, $method = 'md5', $appkey = '' ) 
{
    //判断该系统函数是否存在
    if (!function_exists($method)) return false;
    //如果appkey不存在返回false
    if (empty($appkey)) return false;
    $new_array = array();
    //遍历数组中的所有，并且根据数组内容进行排序
    foreach($arrdata as $key => $value)
    {
        array_push($new_array,(string)$value);
    }
    //value值进行字符串的字典序排序
    sort($new_array,SORT_STRING);
    //按照键值排序
    ksort($arrdata);
    //拼装请求参数
    $paramstring = '';
    foreach ($arrdata as $key => $value) {
        if (strlen($paramstring) == 0) {
            $paramstring .= $key . '=' . $value;
        } else {
            $paramstring .= '&' . $key . '=' . $value;
        }
    }
    //生成签名
    $sign = $method($paramstring.implode($new_array).$appkey);
    return $sign;
}


/**
*
* 解析客户端参数
* @param   $str   string  加密密钥参数
* @return  $data  string  返回加密密钥
* @author  jingfu@leju.com
* @create  2017/07/20
*/
function encode($str)
{
    if(!$str){
        return '';
    }
    return base64_encode(substr(md5($str),0,8).base64_encode($str).substr(md5($str),10,4));
}

/**
* 
* 通过get方式请求API
* @param    $url     string   请求地址
* @return   $data    string   api返回数据
* @author   jingfu@leju.com
* @create   2017/07/20
*/
function http_get($url)
{
    $o_curl = curl_init();
   
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








?>