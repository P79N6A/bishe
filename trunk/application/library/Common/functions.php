<?php
/**
 * 公用函数库
 */

function test_function()
{
    echo 'Hello new.data.house.sina.com.cn';
}

/**
 * 赵廷磊 定义个调试函数 2016-04-21
 */
function p($in=array())
{
    echo "<pre>";
    if (is_array($in)) {
        print_r($in);
    } else {
        var_dump($in);
    }
    echo "<hr/>";
}

function pr($data,$exit=0,$type='var_dump')
{
    if(isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
        if ($type=='echo') {
            echo $data;
        } else {
            $type($data);
        }

        if ($exit) {
            exit();
        }
    }
}

function gbk2utf8($s)
{
    return iconv_array("gbk", "utf-8", $s);
}

function  utf8togbk($s)
{
	return iconv_array("utf-8", "gbk", $s);
}
/**
 * 编码转换,utf8 -> gbk
 */
function utf82gbk($s)
{
    return iconv_array("utf-8", "gbk", $s);
}

/**
 * 对数组进行编码转换
 *
 * @param strint $in_charset  输入编码
 * @param string $out_charset 输出编码
 * @param array $arr          输入数组
 * @return array              返回数组
 */
function iconv_array($in_charset, $out_charset, $arr)
{
    if (strtolower($in_charset) == "utf8")
    {
        $in_charset = "UTF-8";
    }
    if (strtolower($out_charset) == "utf-8" || strtolower($out_charset) == 'utf8')
    {
        $out_charset = "UTF-8";
    }
    if (is_array($arr))
    {
        foreach ($arr as $key => $value)
        {
            $arr[iconv($in_charset, $out_charset, $key)] = $value;
            unset($arr[$key]);
            $arr[iconv($in_charset, $out_charset, $key)] = iconv_array($in_charset, $out_charset, $value);
        }
    }
    else
    {
        if (!is_numeric($arr) && !empty($arr))
        {
            //$arr = iconv($in_charset, $out_charset, $arr);
            #针对UTF8编码中含有特殊的字符做下替换，转换成正常的
            //$arr = str_replace(array("\xC2\xA0",chr(226)), '&nbsp;', $arr);
            $arr = mb_convert_encoding($arr, $out_charset, $in_charset);
        }
    }
    return $arr;
}

/**
 * PHP生成指定随机字符串的简单实现方法
 * @param $length
 * @param string $type
 * @return string
 * @author chenchen16@leju.com
 */
function get_nonce_str($length,$type="number,upper,lower")
{
    $valid_type = array('number','upper','lower');
    $case = explode(",",$type);
    $count = count($case);
    //根据交集判断参数是否合法
    if ($count !== count(array_intersect($case,$valid_type))) {
        return false;
    }
    $lower = "abcdefghijklmnopqrstuvwxyz";
    $upper = strtoupper($lower);
    $number = "0123456789";
    $str_list = "";
    for($i=0;$i<$count;++$i){
        $str_list .= $$case[$i];
    }
    return substr(str_shuffle($str_list),0,$length);
}

/**
 * 获取输入参数 支持过滤和默认值 From ThinkPHP 系统函数库(I函数)
 * 使用方法:
 * <code>
 * input('id',0); 获取id参数 自动判断get或者post
 * input('post.name','','htmlspecialchars'); 获取$_POST['name']
 * input('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @param mixed $datas 要获取的额外数据源
 * @return mixed
 * @author chenchen16@leju.com
 */
function input($name,$default='',$filter=null,$datas=null)
{
    static $_PUT	=	null;
    if(strpos($name,'/')){ // 指定修饰符
        list($name,$type) 	=	explode('/',$name,2);
    }else{ // 默认强制转换为字符串
        $type   =   's';
    }
    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :
            $input =& $_GET;
            break;
        case 'post'    :
            $input =& $_POST;
            break;
        case 'put'     :
            if(is_null($_PUT)){
                parse_str(file_get_contents('php://input'), $_PUT);
            }
            $input 	=	$_PUT;
            break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                case 'PUT':
                    if(is_null($_PUT)){
                        parse_str(file_get_contents('php://input'), $_PUT);
                    }
                    $input 	=	$_PUT;
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'path'    :
            $input  =   array();
            if(!empty($_SERVER['PATH_INFO'])){
                $depr   =   '/';
                $input  =   explode($depr,trim($_SERVER['PATH_INFO'],$depr));
            }
            break;
        case 'request' :
            $input =& $_REQUEST;
            break;
        case 'session' :
            $input =& $_SESSION;
            break;
        case 'cookie'  :
            $input =& $_COOKIE;
            break;
        case 'server'  :
            $input =& $_SERVER;
            break;
        case 'globals' :
            $input =& $GLOBALS;
            break;
        case 'data'    :
            $input =& $datas;
            break;
        default:
            return null;
    }
    if(''==$name) { // 获取全部变量
        $data       =   $input;
        $filters    =   isset($filter)?$filter:\Yaf\Registry::get('config')->user->default_filter;
        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];
        $filters    =   isset($filter)?$filter:\Yaf\Registry::get('config')->user->default_filter;
        if($filters) {
            if(is_string($filters)){
                if(0 === strpos($filters,'/')){
                    if(1 !== preg_match($filters,(string)$data)){
                        // 支持正则验证
                        return   isset($default) ? $default : null;
                    }
                }else{
                    $filters    =   explode(',',$filters);
                }
            }elseif(is_int($filters)){
                $filters    =   array($filters);
            }
            if(is_array($filters)){
                foreach($filters as $filter){
                    if(function_exists($filter)) {
                        $data   =   is_array($data) ? array_map_recursive($filter,$data) : $filter($data); // 参数过滤
                    }else{
                        $data   =   filter_var($data,is_int($filter) ? $filter : filter_id($filter));
                        if(false === $data) {
                            return   isset($default) ? $default : null;
                        }
                    }
                }
            }
        }
        if(!empty($type)){
            switch(strtolower($type)){
                case 'a':	// 数组
                    $data 	=	(array)$data;
                    break;
                case 'd':	// 数字
                    $data 	=	(int)$data;
                    break;
                case 'f':	// 浮点
                    $data 	=	(float)$data;
                    break;
                case 'b':	// 布尔
                    $data 	=	(boolean)$data;
                    break;
                case 's':   // 字符串
                default:
                    $data   =   (string)$data;
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:null;
    }
    is_array($data) && array_walk_recursive($data,'other_safe_filter');
    return $data;
}

/**
 * 简单的过滤数据
 * @param $data
 * @param $filter 过滤方法
 * @return $data 过滤后的数据
 * @author chenchen16@leju.com
 * @date 2016/8/18
 */
function filter($data, $filter=null)
{
    $filters    =   isset($filter) ? $filter : \Yaf\Registry::get('config')->user->default_filter;

    if ($filters) {
        if (is_string($filters)) {
            $filters    =   explode(',',$filters);
        }

        if (is_array($filters)) {
            foreach ($filters as $filter) {
                if (function_exists($filter)) {
                    $data   =   is_array($data) ? array_map_recursive($filter,$data) : $filter($data); // 参数过滤
                }
            }
        }
    }

    return $data;
}

/**
 * 用于input函数的递归
 * @param $filter
 * @param $data
 * @return array
 */
function array_map_recursive($filter, $data)
{
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val)
            ? array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
    }
    return $result;
}

/**
 * 其他安全过滤 From ThinkPHP 系统函数库 为input函数服务
 * @param $value
 */
function other_safe_filter(&$value)
{
    // 过滤查询特殊字符
    if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
        $value .= ' ';
    }
}

/**
 * 实现类似 Eloquent ORM 的 toArray 方法,也可实现 (array)$value 的功能
 * @param $data
 */
function to_array($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $data[$key] = (array) $value;
            }
        }
    } else {
        $data = (array) $data;
    }

    return $data;
}

/**
 * 获取客户端IP地址 FROM ThinkPHP 系统函数库
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 * @author chenchen16@leju.com
 */
function get_client_ip($type = 0,$adv=false)
{
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * XML编码 FROM ThinkPHP 系统函数库
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xml_encode($data, $root='root', $item='item', $attr='', $id='id', $encoding='utf-8') {
    if(is_array($attr)){
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";ss

/**
 * GET 请求 FROM wechat-php-sdk
 * @param string $url
 * @param int $curlopt_timeout 超时时间
 * @author chenchen16@leju.com
 */
function http_get($url, $curlopt_timeout = 50, $header = array())
{
    $oCurl = curl_init();
    if (stripos($url,"https://")!==FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }

    if (!empty($header)) {
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($oCurl, CURLOPT_TIMEOUT, $curlopt_timeout);
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);

    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * POST 请求 FROM wechat-php-sdk
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @param int $curlopt_timeout 超时时间
 * @return string content
 * @author chenchen16@leju.com
 */
function http_post($url, $param, $post_file = false, $curlopt_timeout = 50, $header = array())
{
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }

    curl_setopt($oCurl, CURLOPT_TIMEOUT, $curlopt_timeout);

    if (!empty($header)) {
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
    }

    if (is_string($param) || $post_file) {
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach($param as $key=>$val){
            $aPOST[] = $key."=".urlencode($val);
        }
        $strPOST =  join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        if (isset($_GET['print_row']) && $_GET['print_row'] == '1') {
            p($url);
            p($param);
            p($aStatus);
            p($sContent);
        }
        return false;
    }
}


/**
 * 发送post请求
 * @param url 请求地址
 * @param $data  参数 a=2&b=2
 * @author  jiebo@leju.com
 * @date   2017年3月29日
 */
function  quickPost($url, $data = "") {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}


/**
 * curl 批处理
 * @param array $data 如果为get形式,$data为url数组;如果为post形式,$data为数组,有两个键,url和post(post数据)
 * @param int $curlopt_timeout 超时时间
 * @param array $options cURL传输会话批量设置选项
 * @return array
 * @author chenchen16@leju.com
 */
function curl_multi($data, $curlopt_timeout = 50, $options = array())
{
    $handles = $contents = array();
    //初始化curl multi对象
    $mh = curl_multi_init();
    //添加curl 批处理会话
    foreach ($data as $key => $value) {
        $url = (is_array($value) && !empty($value['url'])) ? $value['url'] : $value;
        $handles[$key] = curl_init($url);
        curl_setopt($handles[$key], CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handles[$key], CURLOPT_TIMEOUT, $curlopt_timeout);
        if (strpos($url, 'api.mg.cric.com') !== false) {
            $url = str_replace('api.mg.cric.com', '114.80.163.182', $url);
            curl_setopt($handles[$key], CURLOPT_URL, $url);
            curl_setopt($handles[$key], CURLOPT_HTTPHEADER, array("Host: api.mg.cric.com"));
        }
        //判断是否是post
        if (is_array($value)) {
            if (!empty($value['post'])) {
                curl_setopt($handles[$key], CURLOPT_POST,       1);
                curl_setopt($handles[$key], CURLOPT_POSTFIELDS, $value['post']);
            }
        }

        //extra options?
        if (!empty($options)) {
            curl_setopt_array($handles[$key], $options);
        }

        curl_multi_add_handle($mh, $handles[$key]);
    }
    //======================执行批处理句柄=================================
    $active = null;
    do {
        $mrc = curl_multi_exec($mh, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    while ($active and $mrc == CURLM_OK) {
        if (curl_multi_select($mh) === -1) {
            usleep(100);
        }
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
    //====================================================================
    //获取批处理内容
    foreach ($handles as $i => $ch) {
        $content = curl_multi_getcontent($ch);
        $contents[$i] = curl_errno($ch) == 0 ? $content : '';
    }
    //移除批处理句柄
    foreach ($handles as $ch) {
        curl_multi_remove_handle($mh, $ch);
    }
    //关闭批处理句柄
    curl_multi_close($mh);
    return $contents;
}

/**
 * 看某个服务器是否ping通
 * @param $hostname
 * @param $port
 * @param int $timeout
 * @return bool
 * @author chenchen16@leju.com
 * @date 2016/09/28
 */
function ping($hostname, $port, $timeout = 2)
{
    $fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
    if ($fp) {
        return true;
    } else {
        return false;
    }
}

/**
 * 处理整形时间，返回*年*月*日格式
 * @param  $time 整型日期
 * @return string
 * @author  jiebo@leju.com
 * @date   2016年9月22日
 */
function process_int_time($time)
{
    $str = '';

    if (!is_numeric($time)) {
        $str = $time;
    } elseif (empty($time)) {
        $str = '尚未公布';
    }elseif (strlen($time) < 6) {
        $str = '尚未公布';
    } elseif(substr($time,4,2) == '00') {
        $str = '尚未公布';
    } else{
        $str .= substr($time, 0 ,4).'年';

        $str .= substr($time, 4 ,2).'月';

        if (strlen($time) >= 8) {
            $day= substr($time, 6 ,2);
            if ($day > 0) {
                $str .= $day.'日';
            }
        }
    }

	return $str;
}

/**
 * @logic   接口返回时候过滤掉不用的字段，减少数据传输时间
 * @param   array $arr_fields  需要留下的字段
 * @param   array $arr_data    过滤的数据，
 * @return  string
 * @author  赵廷磊 2016-06-29
 * @modify by chenchen16 2016/09/30
 */
function filter_fields($data, $filter_fields)
{
    if (is_string($filter_fields)) {
        $filter_fields = explode(',', $filter_fields);
    }

    foreach ($data as $key => $value) {
        if (!in_array($key, $filter_fields)) {
            unset($data[$key]);
        }
    }
    return $data;
}

/**
 * 图片生成url地址生成,支持一维及多维的数组
 * @see http://wiki.rd.intra.leju.com/#!pages/service/photolib/index.user.md
 * @param array $pictures 图片的信息数组,必须要有pic_url,pic_width,cut_type字段
 * @param string $sizes 支持只传宽和宽x高
 * @param array $ext_params 扩展参数,如果是多种size而且扩展参数不同,可以以size为下标
 * @param bool 是否使用sphinx推送数据 使用http
 * @return mixed
 * @author chenchen16@leju.com
 * @date 2016/10/11
 */
function pic_op(array $pictures, $sizes = 'ori', $ext_params = array(), $is_sphinx = false)
{
    if (empty($pictures) || !is_array($pictures)) {
        return array();
    }

    if (!$sizes) {
        $sizes = 'ori';
    }

    if (is_string($sizes) && strpos($sizes, ',') !== false) {
        $sizes = explode(',', $sizes);
    }

    if (is_string($sizes) || is_int($sizes)) {
        $sizes = array($sizes);
    }

    //是一维数组
    if (isset($pictures['pic_url']) || (isset($pictures['pic_path']) && isset($pictures['pic_ext']))) {
        $temp_pictures = array();
        $temp_pictures[0] = $pictures;
        $temp_pictures = pic_op($temp_pictures, $sizes, $ext_params,$is_sphinx);
        return $temp_pictures[0];
    }
    if($is_sphinx){
        $img_url_prefix = IMG_URL_SPHINX . '/imp/imp/deal';
    }else{
        $img_url_prefix = IMG_URL . '/imp/imp/deal';

    }

    //cut_type,watermark等参数不要在这里写死,请使用$ext_params参数
    //经常用到的尺寸，如果下面的宽重了或没有你想要的尺寸，使用$sizes参数传width*height
    $sizes_params = array(
        'ori' => array("resize_w" => 1, "resize_h" => 1),
        '90' => array("resize_w" => 90, "resize_h" => 67),
        '120' => array("resize_w" => 120, "resize_h" => 90),
        '126' => array("resize_w" => 126, "resize_h" => 73),
        '160' => array("resize_w" => 160, "resize_h" => 120),
        '186' => array("resize_w" => 186, "resize_h" => 140),
        '189' => array("resize_w" => 189, "resize_h" => 143),
        '206' => array("resize_w" => 206, "resize_h" => 154),
        '208' => array("resize_w" => 208, "resize_h" => 156),
        '220' => array("resize_w" => 220, "resize_h" => 160),
        '222' => array("resize_w" => 222, "resize_h" => 166),
        '226' => array("resize_w" => 226, "resize_h" => 170),
        '256' => array("resize_w" => 256, "resize_h" => 192),
        '263' => array("resize_w" => 263, "resize_h" => 197),
        '340' => array("resize_w" => 340, "resize_h" => 190),
        '358' => array("resize_w" => 358, "resize_h" => 269),
        '375' => array("resize_w" => 375, "resize_h" => 301),
        '386' => array("resize_w" => 386, "resize_h" => 289),
        '447' => array("resize_w" => 447, "resize_h" => 528),
        '600' => array("resize_w" => 600, "resize_h" => 800),
        '655' => array("resize_w" => 655, "resize_h" => 491),
        '698' => array("resize_w" => 698, "resize_h" => 523)
    );

    foreach ($pictures as $key => $picture) {
        //如果图片数组为多维数据,使用递归处理
        if (is_array(current($picture))) {
            $picture = pic_op($picture, $sizes, $ext_params);
            $pictures[$key] = $picture;
            continue;
        }

        if (!isset($picture['pic_url']) && !(isset($picture['pic_path']) && isset($picture['pic_ext']))) {
            continue;
        }

        if (isset($picture['pic_url']) && empty($picture['pic_url'])) {
            continue;
        }

        if ((isset($picture['pic_path']) && empty($picture['pic_path'])) || (isset($picture['pic_ext']) && empty($picture['pic_ext']))) {
            continue;
        }

        if (isset($picture['pic_path']) && isset($picture['pic_ext'])) {
            $file_id = '/' . $picture['pic_path'];
            $file_ext = $picture['pic_ext'];
        } else {
            $file_arr = explode('.', $picture['pic_url']);
            $file_id = $file_arr[0];
            $file_ext = $file_arr[1];
        }

        foreach ($sizes as $size) {
            $size = trim($size);//去除空格

            $params = array();
            if (strpos($size, '*') !== false || strpos($size, 'x') !== false) {
                if (strpos($size, '*') !== false) {
                    $delimiter = '*';
                } elseif (strpos($size, 'x') !== false) {
                    $delimiter = 'x';
                } else {
                    $delimiter = '*';
                }
                $size_arr = explode($delimiter, $size);
                $params = array("resize_w" => $size_arr[0], "resize_h" => $size_arr[1]);
            } else {
                if (!isset($sizes_params[$size])) {
                    continue;
                }
                $params = $sizes_params[$size];
            }

            if (!empty($ext_params)) {
                if (is_array(current($ext_params))) {
                    if (isset($ext_params[$size])) {
                        $params = array_merge($params, $ext_params[$size]);
                    }
                } else {
                    $params = array_merge($params, $ext_params);
                }
            }

            //水印设置
            if (!isset($params['watermark'])) {
                //宽度小于200不加水印
                if (isset($picture['pic_width']) && $picture['pic_width'] <= 200) {
                    $params['watermark'] = 0;
                } else {//默认加水印
                    $params['watermark'] = 1;
                }
            }

            if (!isset($params['cut_type'])) {
                //cut_type 1:中间截取,2:等比例缩放
                $params['cut_type'] = (isset($picture['cut_type']) && $picture['cut_type']) ? $picture['cut_type'] : 1; //默认中间截取
            }

            $img_url = $img_url_prefix . $file_id . '_p7_mk7';//p7和mk7为楼盘库的固定值

            /**
             * 3种图片常用宽高的处理方式,更多的见文档
             * cut_type=1 cm400X300 中间截取，原图居中截取，截取范围为400X300，400为截取宽度，300为截取高度
             * cut_type=2 s100X10 按尺寸等比例缩放，保持原图片比例（以数值大的一方为标准）100为宽，10为高（若要按宽或高缩放，把另一值设为0即可）
             * cut_type=3 sl715X0 按照宽度最大化缩放，保持原图比例(以数值小的一方为准）。如果原图宽度大于715，则缩放到宽度为715，如果原图宽度小于715，则保持原图大小不变。（若要按宽或高缩放，把另一值设为0即可）
             */
            if ($size != 'ori' && $params['resize_w'] != 1 && $params['resize_h'] != 1) {
                if ($params['cut_type'] == 2) {//等比例缩放
                    $img_url .= '_s' . $params['resize_w'] . 'X' . $params['resize_h'];
                } elseif ($params['cut_type'] == 3) {//按照宽度最大化缩放
                    $img_url .= '_sl' . $params['resize_w'] . 'X' . $params['resize_h'];
                } else {//中间截取
                    $img_url .= '_cm' . $params['resize_w'] . 'X' . $params['resize_h'];
                }
            }

            //水印处理
            if (isset($params['watermark']) && $params['watermark'] == 1) {//设置水印
                $img_url .= '_wm' . '47';//默认水印为新浪乐居logo 右下角id为61 背景为47
            } else {
                // 不用水印的时候，对原图访问加安全码
                $pkey = "af7e5f19cb87a3cca23296b7b8707f83";
                $md5_sum = md5($file_id . $pkey);
                $chars = str_split($md5_sum);
                $sec = '';
                for ($i = 0; $i < count($chars); $i += 6) {
                    $sec .= $chars[$i];
                }
                $img_url .= '_os' . $sec;
            }

            //设置自动补白
            if (isset($params['pt_type']) && $params['pt_type'] == 1) {
                $img_url .= '_pt' . '1';
            }

            //设置图片质量,可选择的值1-10
            if (isset($params['qa'])) {
                $img_url .= '_qa' . $params['qa'];
            }

            //添加后缀
            $img_url .= '.' . $file_ext;

            if ($size === 'ori' || ($params['resize_w'] == 1 && $params['resize_h'] == 1)) {
                //旧的图片url字段
                $pictures[$key]['pic_ori'] = $img_url;
                //新的图片url字段
                $pictures[$key]['pic_1x1'] = $img_url;
            } else {
                $resize_width  = $params['resize_w'];
                $resize_height = $params['resize_h'];
                $size          = $resize_width . 'x' . $resize_height;

                //兼容旧的图片url字段
                $pictures[$key]['pic_s' . $resize_width] = $img_url;

                //添加新的图片url字段,pic_ + 处理方式 + 宽x高
                if ($params['cut_type'] == 2) {//等比例缩放
                    $pictures[$key]['pic_s' . $size] = $img_url;
                } elseif ($params['cut_type'] == 3) {
                    $pictures[$key]['pic_sl' . $size] = $img_url;
                } else {//中间截取
                    $pictures[$key]['pic_cm' . $size] = $img_url;
                }
            }
        }
    }
    return $pictures;
}


/**
 * 对url进行额外的处理
 * @param string $url
 * @param int $width
 * @param int $height
 * @param int $cut_type
 * @param int $watermark
 * @return string
 */
function pic_url_op($url = '', $width = 50, $height = 50, $cut_type = 2, $watermark = 1)
{
    $return = '';

    if (strpos($url,'src.leju') === false && strpos($url,'src.house.sina') === false) {//不是图片的url不处理
        $return = $url;
    } else {
        $url_arr = explode('.', $url);
        $ext = array_pop($url_arr);
        $pre = implode('.',$url_arr);

        //图库系统: 1: 压缩补白s， 2：中间截取cm
        $return = $pre;
        if ($cut_type == 2) {
            $return .= '_cm' . $width. 'X' . $height;
        } else {
            $return .= '_s' . $width. 'X' . $height;
        }
        if ($watermark) {
            $return .= '_wm47';
        }

        $return .= '.'.$ext;
    }

    return $return;
}

/**
 * 获取某楼盘的域名
 * @param string $city_code
 * @param string $hid
 * @return string
 * @author  chenchen16@leju.com
 * @date   2016/9/29
 */
function get_house_url($city_code, $hid)
{
    return ITEM_URL . '/' . $city_code . $hid . '/';
}

/**
 * 截取字符串 ，from discuz
 * @param string $string 截取字符串
 * @param int    $length 截取长度
 * @param string $dot 后缀
 * @param        $charset $charset    编码,默认gbk
 * @return string                返回截取后的字符串
 */
function cut_str($string, $length, $dot = '', $charset = "utf-8")
{
    if (strlen($string) <= $length) {
        return $string;
    }

    $strcut = '';
    if (strtolower($charset) == 'utf-8') {

        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {

            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t < 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }

            if ($noc >= $length) {
                break;
            }

        }
        if ($noc > $length) {
            $n -= $tn;
        }

        $strcut = substr($string, 0, $n);

    } else {
        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) > 127) {
                $_i = $j = 0;
                $_i = $i;
                $j = ++$i;
                if ($j >= $length) break;
                $strcut .= $string[$_i] . $string[$j];
            } else {
                $strcut .= $string[$i];
            }
        }
    }

    return $strcut . $dot;
}

/**
 * @logic 工具方法,从array里面提取出某一列元素放入另一个数组
 * @param array $array
 * @param string $key
 * @author  jiebo@leju.com
 * @return array
 * @date   2016年11月25日
 */
function pluck($array = array(), $key = "")
{
	$values = array();
	if (is_array($array) && !empty($key)) {

		foreach ($array as $row) {
			if (isset($row[$key])) {
				// Found a value in this row
				$values[] = $row[$key];
			}
		}
	}
	return $values;
}


/**
 * [uploadapi 批量上传图片到cdn,接口url版]
 * @Author   zlc
 * @DateTime 2016-12-06
 * wiki：http://wiki.rd.intra.leju.com/#!pages/service/photolib/resource/from_url_upload.md
 * @param    [array]    $url  [原则上是10个以下，亲测5个成功，10个超时]
 * @return   [array]          [description]
 */
function cdn_image_api($url)
{
    $FileUrl='';
    define('PHOTO_MKEY', '2683010a90f938050dbb55c6c0b903ab');
    foreach ($url as $key => $value) {
      if (!empty($FileUrl)) {
        $FileUrl.=','.$value;
      } else {
        $FileUrl.=$value;
      }

    }

    $res= http_post('http://photo.leju.com/api/v3/upload',array('FileUrl'=>$FileUrl,'mkey'=>PHOTO_MKEY));
    $res=json_decode($res,true);

    return $res;

}


/**
 * [export_txt 导出文本]
 * @Author   zlc
 * @DateTime 2017-01-04
 * @param    [type]     $file_name [导出文本的名字]
 * @param    [type]     &$file_arr [要导出的数组,二维]
 * @return   [file]                [file]
 */
function export_txt($file_name,&$file_arr)
{
    $file_name=!empty($file_name) ? $file_name :'default.txt';//要导出的文件的文件名需要加上文件后缀

    $chars = ''; //需要导出的文件的内容

    foreach ($file_arr as $key => $value) {
        foreach ($value as $key => $v) {
            $chars.=$v.'   ';
        }

        $chars.="\r\n";
    }

    header('Content-Type: text/x-sql');

    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

    header('Content-Disposition: attachment; filename="' .$file_name. '"');

    $is_ie = 'IE';

       if ($is_ie == 'IE') {

           header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

           header('Pragma: public');

    } else {

           header('Pragma: no-cache');

           header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

       }

    echo $chars;
    exit();
}

/**
 * @logic 生成guid
 * @author  jiebo@leju.com
 * @return_type
 * @date   2017年1月11日
 */
function guid(){
	if (function_exists('com_create_guid')) {
		$uuid = com_create_guid();
		$uuid = substr($uuid,1,- 1);
		return $uuid;
	} else {
		mt_srand((double)microtime() * 10000); // optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(),true)));
		$hyphen = chr(45); // "-"
		$uuid = substr($charid,0,8) . $hyphen . substr($charid,8,4) . $hyphen . substr($charid,12,4) . $hyphen . substr($charid,16,4) . $hyphen . substr($charid,20,12);
		return $uuid;
	}
}

/**
 * @logic 导出csv
 * @param string $filename
 * @param string $data
 * @author  jiebo@leju.com
 * @date   2017年1月13日
 */
function export_csv($filename = '',$data = "") {
	header("Content-type:text/csv;charset=utf-8");
	header("Content-Disposition:attachment;filename=".$filename);
	header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	header('Expires:0');
	header('Pragma:public');
	echo $data;
}

/**
 * 兼容多个系统
 * @param $list
 * @param $head
 * @param $filename
 * @return bool
 * @author chenchen16@leju.com
 */
function export_csv_new($list, $head, $filename){
    if(empty($list)) return false;
    $Agent=$_SERVER['HTTP_USER_AGENT'];
    if(eregi('win',$Agent)){
        $is_windows = true;
    }else{
        $is_windows = false;
    }

    //写入标题
    $title = '';
    foreach ($head as $key)
    {
        $field = str_replace(',', '，', strip_tags($key));
        $field = str_replace("\r\n", '，', $field);
        $title .= $field . ',';
        $field = '';
    }
    $title = substr($title, 0, -1);
    if($is_windows) {
        $title = @iconv('UTF-8', 'GB2312//IGNORE', $title );
    }
    $str = $title.PHP_EOL;
    //写入内容
    foreach($list as $k => $v){
        $content = '';
        //过滤逗号,换行符,编码转换等
        foreach($v as $vk => $vv){
            $tmp = str_replace(PHP_EOL,'',$vv);
            $tmp = str_replace(array(',','，',chr(13)),'、',$tmp);
            if($is_windows) {
                $tmp = @iconv('UTF-8', 'GB2312//IGNORE', $tmp );
            }
            $content .= $tmp . ',';
        }
        $content = trim($content,',');
        $content = $content.PHP_EOL;
        $str .= $content;
    }
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=".$filename.'.csv');
    //header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    echo $str;
    ob_end_flush();
    exit();
}

/**
 * [text_display 返回dict中文释义,smarty修饰器]
 * @Author   zlc
 * @DateTime 2017-01-18
 * @param    [type]     $value [最底层键值]
 * @param    [type]     $type  [dict分类]
 * @param    [type]     $key   [dict二级分类]
 * @return   [type]            [description]
 */
function smarty_dict_text_display($value, $type, $key)
{
    $dict=\Yaf\Registry::get('dict')->get($type)->get($key)->toarray();

    return $dict[$value];

}

/*
 *判断是否是网络爬虫
 *@author jingfu@leju.com
 *@create 2017/08/29
 */
function is_spider() {
    return true;

    /*$return = false;
    $agent= strtolower($_SERVER['HTTP_USER_AGENT']); 
    if (isset($_GET['is_spider']) && $_GET['is_spider']){
        $return =  true;
    }elseif (!empty($agent)) {
        $spider_site= array(
                "Baiduspider",
                "Trident/5.0",
                "360spider",
                "Googlebot",
                "AdsBot-Google-Mobile",
                "bingbot",
                "Sosospider",
                "Sogou Pic Spider",
                "Sogou web spider",
        );
        foreach($spider_site as $val) {
            $str = strtolower($val);
            if (strpos($agent, $str) !== false) {
                $return = true;
                break;
            }
        }
    }
    return $return;*/
}

/**
 *求两个已知经纬度之间的距离,单位为米
 *@param lng1,lng2 经度
 *@param lat1,lat2 纬度
 *@return float 距离，单位米
 **/
function getDistance($lng1,$lat1,$lng2,$lat2){
    //将角度转为狐度
    $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
    $radLat2=deg2rad($lat2);
    $radLng1=deg2rad($lng1);
    $radLng2=deg2rad($lng2);
    $a=$radLat1-$radLat2;
    $b=$radLng1-$radLng2;
    $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
    return $s;
}

/**
 *求用户的真正IP地址
 *@return IP
 **/
function getIp(){
    $realip = '';
    $unknown = 'unknown';
    if (isset($_SERVER)){
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach($arr as $ip){
                $ip = trim($ip);
                if ($ip != 'unknown'){
                    $realip = $ip;
                    break;
                }
            }
        }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){
            $realip = $_SERVER['REMOTE_ADDR'];
        }else{
            $realip = $unknown;
        }
    }else{
        if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){
            $realip = getenv("HTTP_CLIENT_IP");
        }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){
            $realip = getenv("REMOTE_ADDR");
        }else{
            $realip = $unknown;
        }
    }
    $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
    return $realip;
}

	/**
     * @logic 从智投广告平台获取楼盘
     * WIKI:http://wiki.intra.eju.com/index.php/%E9%A6%96%E9%A1%B5#.E6.99.BA.E8.83.BD.E6.8A.95.E6.94.BE.E7.B3.BB.E7.BB.9F
	 * @param string $city
	 * @param number $hid
	 * @param number $uid
	 * @param number $mid
	 * @param array $ad_tag
	 * @return mixed
	 * @author  jiebo@leju.com
     * @date   2016年12月19日
     *zt_ad_preview 结构 $zt_ad_preview[{$ad_tag}]['key'] = $value;
     *例 $zt_ad_preview['XFAPP-001']['price'] = 200000
	 */
     function getAdvertList($city = "",$hid = 0,$uid = 0, $mid = 0,$ad_tag = array(),$zt_ad_preview = array())
     {
         if(empty($mid)){
            $mid = $_COOKIE['gatheruuid'];//获取mid
         }
         $ret = array();
         if (\Yaf\ENVIRON === 'develop' || (isset($_SERVER['SINASRV_IS_LOCAL']) && $_SERVER['SINASRV_IS_LOCAL']==='1')) {
             $api_url= "http://ad.bch.leju.com/api/advert/get_delivery_resource?";
            //$api_url = "http://habo.leju.com/api/zhitou/get_delivery_resource?";
         } else {
             $api_url = "http://habo.leju.com/api/zhitou/get_delivery_resource?";
         }
         if ($zt_ad_preview && $zt_ad_preview !="null") {

            if(is_array($zt_ad_preview)){
                foreach ($zt_ad_preview as $ad_tag_key=>$ad_str){
                    foreach ($ad_tag as &$one) {
                        if ($one['ad_tag'] == $ad_tag_key) {
                            foreach($ad_str as $zt_ad_preview_key => $ad_tag_set)
                            $one[$zt_ad_preview_key] = $ad_tag_set;
                        }
                    }
                }
            } else {
                //旧代码,仅仅作参考，改版后的智投接口已经不能兼容
                // $adt_arr = explode(",", $zt_ad_preview);
                // foreach ($adt_arr as $ad_str){
                //     $ad_arr1 = explode("|", $ad_str);
                //     $ad_tag_set = $ad_arr1[0];
                //     $material_id = $ad_arr1[1];
                //     foreach ($ad_tag as &$one) {
                //         if ($one['ad_tag'] == $ad_tag_set) {
                //             $one['material_id'] =$material_id;
                //         }
                //     }
                // }
            }

         }

         $params = array('uid' => $uid,
                         'mid' => $mid,
                         'hid' => $hid,
                         'city_en' => $city,
                         'appkey' => '2000000002', // 到appkey页面查看
                         'ad_tag_set' => $ad_tag );

         // 需绑定host：58.83.214.106 ad.bch.leju.com
         $url = $api_url.http_build_query($params);

         $res = http_get($url,2);

         //echo $url;exit;
         if (!$res) {
             return $ret;
         }
         $json = json_decode($res,true);

         if (array_key_exists("error_code", $json)){
             return $ret;
         }
         foreach ($json['entry'] as $one) {
             $info = !empty($one['ad_content'])?$one['ad_content']:null;
             if (empty($info)) {
                 continue;
             }

             $info['hid'] = $one['tongji']['params']['rel_id'];
             $info['site'] = $one['tongji']['params']['rel_city_en'];
             $info['tongji'] = $one['tongji'];
             $info['impression_url'] = $one['ad_content']['impression_url'];
             $ret[] = $info;
         }
         return  $ret;
     }

    /**
    *获取house表dict字典值对应的value值
    *@param lng1,lng2 经度
    *@param lat1,lat2 纬度
    *@return float 距离，单位米
    **/
     function getDictValueByKey($type,$key){
        $dict_house_field = array('hometype','archtype','payment_type','hometag','fitment');
        $resturn = array();
        if(in_array($type,$dict_house_field)){
            $dict = \Yaf\Registry::get('dict')->house->toArray();

            $key_arr = explode(',', $key);

            foreach ($key_arr as $value) {

                if(!empty($dict[$type][$value])){
                    $resturn[] = $dict[$type][$value];
                }
            }
        }
        $res = implode(',', $resturn);
        return $res;
     }

    /**
     * 加入任务管理系统队列
     * @param $url
     * @param $data
     * @param $header
     * @param $method
     * @author  junfei1@leju.com
     * @time    2018-11-13
     */
    function add_task($url, $data, $header = '', $method = 'GET')
    {
        // 提交异步处理api地址,此处以单个任务为例，如果任务较多可以使用批量添加接口
        $taskApiUrl = "http://i.task.leju.com/api/gearmanclient/manage";

        // 调用异步系统回调处理的url等参数信息
        $m_key = '4816196179084dea0ad73024afbc892e';
        $postData = array(
            'mkey'      => $m_key,
            'address'   => $url,
            'method'    => $method,
            'data'      => json_encode($data),
            'header'    => $header,
        );
        $postData['sign'] = get_sign($postData, $m_key);
        $curl_return = http_post($taskApiUrl, $postData);
        $return = $curl_return['code'] ? '' : $curl_return['data'];
        return $return;
    }

    /**
     * API签名生成规则
     * @param $data
     * @param string $mkey
     * @return string
     * @author  junfei1@leju.com
     * @time    2018-11-13
     */
     function get_sign($data, $mkey = '')
     {
         if(isset($data['sign'])){
             unset($data['sign']);
         }
         ksort($data);
         $string = getPostString($data);
         return md5($string.$mkey);
     }

     /**
      * API签名生成规则
      * @param $post
      * @return string
      * @author  junfei1@leju.com
      * @time    2018-11-13
      */
     function getPostString(&$post)
     {
         $string = '';
         if (is_array($post)) {
             foreach ($post as $item) {
                 if (is_array($item)) {
                     $string .= getPostString($item);
                 } else {
                     $string .= $item;
                 }
             }
         } else {
             $string = $post;
         }
         return $string;
     }

    if(!function_exists("array_column"))
    {

        function array_column($array,$column_name)
        {

            return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

        }

    }