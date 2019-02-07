<?php


class TestapiController extends \Yaf\Controller_Abstract
{
    public function indexAction()
    {   
        $res = \Resque\Queue::queueAdd('JuliPicBindingJob',array('id'=>1111111));
        $res = \Resque\Queue::queueAdd('JuliKuaiXunBindingJob',array('id'=>1111111));
        $res = \Resque\Queue::queueAdd('JuliHouseBindingJob',array('id'=>1111111));

        p($res);
        exit;
        $noncestr = encode( time().':'.generate_noncestr());
 
        //注：这里需要的参数为除signature的必填参数；选填参数（如return, encode, encrypt_method）无需加密。
        $param1 = 'city_en';
        $param2 = 'bj';
        //1、正常情况
        $arrdata = array(
            'noncestr'=>$noncestr,
            'type'=>$param1,
            'citys'=>$param2
            );
        
        //2、如果参数param为json格式，由于get方式传输后，json数据会转义，故param参数要先转义后再参与加密（url中不用转义），即
        $arrdata = array(
            'noncestr'=>$noncestr,
            'msg'=>htmlspecialchars(addslashes(trim('{"msg_type":"LIVE_STATUS","room_id":112434325}')))
        );
        
        $encrypt_method = 'sha1';//现在支持sha1和md5,默认为sha1
        $signature = $this->get_signature($arrdata, $encrypt_method);
        
        /************************请求楼盘库API*****************************/

        $api_url = "http://api.house.leju.com/Api/Fangshou/getCityInfo?citys={$param2}&type={$param1}&noncestr={$noncestr}&signature={$signature}&return=json&encode=utf-8&encrypt_method={$encrypt_method}";
        echo $api_url;
        //curl api_url to get data
        $result = http_get($api_url);
        echo json_encode($result);

    }

    /**
     * API 测试Ajax 接口
     */
    public function apitestresultAction() {
        $return = array('is_error'=>0,'data'=>'','exec_time'=>'','data_new'=>'','exec_time_new'=>'');
        $gets_old = array();
        $gets_new = array();

        // 处理GET 参数

        if (isset($_POST['getkey_new']) && $_POST['getkey_new']) {
            $i = 0;
            $getval = $_POST['getval_new'];
            foreach ($_POST['getkey_new'] As $k) {
                $gets_new[$k] = $getval[$i];
                $i++;
            }
        }

        $get_src = $gets_new;


        $apiurl_new = $this->getRequest()->getPost('apiurl_new');
        if ($gets_new) {
            $apiurl_new .= strpos($apiurl_new, "?") ? "&" . http_build_query($gets_new) : "?" . http_build_query($gets_new);
			//删除拼装加密数据
			$unset_arr = array('encode','return','signature','encrypt_method','clearm','nocache','apiurl','dbg');
			foreach($unset_arr as $value) {
				if (isset($gets_new[$value])) {
					unset($gets_new[$value]);
				}
			}

        } else {
            $apiurl_new .= '?';
        }

        //生成加密字符串
        $noncestr = encode( time().':'.generate_noncestr() );
        $gets_new['noncestr'] = $noncestr;
        $signature = get_signature($gets_new);
        $apiurl_new .= "&noncestr={$noncestr}&signature={$signature}";

        if (\Yaf\ENVIRON === 'develop' || (isset($_SERVER['SINASRV_IS_LOCAL']) && $_SERVER['SINASRV_IS_LOCAL']==='1') ) {
            $apiurl_new = str_replace('api.house.leju.com','123.59.190.247',$apiurl_new);
        }


        //计时信息新版本
        $start = microtime(true);
        if (\Yaf\ENVIRON === 'develop' || (isset($_SERVER['SINASRV_IS_LOCAL']) && $_SERVER['SINASRV_IS_LOCAL']==='1') ) {
            $data_new = http_get($apiurl_new, 3 , array("Host: api.house.leju.com"));
        }else{
            $data_new = http_get($apiurl_new);
        }

        $end = microtime(true);
        $return['exec_time_new'] = number_format($end-$start, 4);



        //返回格式为jsonp 新版本
        if(isset($get_src['return']) && $get_src['return'] == 'jsonp'){
            $time = mb_substr($data_new,0,10);
            $json_str = mb_substr($data_new,11,strlen($data_new)-12);
            if(json_decode($json_str)){
                $arr[$time] = json_decode($json_str);
                $return['data_new'] = prettyPrint($this->jsonencode($arr));
            }else{
                $return['data_new'] = prettyPrint($data_new);
            }
        }else{//返回格式为json

            if (json_decode($data_new)) {
                $return['data_new'] = prettyPrint($this->jsonencode(json_decode($data_new)));
            } else {
                $return['data_new'] = prettyPrint($data_new);
            }
        }

        if (isset($get_src['apiurl']) && $get_src['apiurl']){
            $return['apiurl_new'] = $apiurl_new;
        }

        echo json_encode($return);
        return false;
    }




    private static function jsonencode($str) {

        $code = json_encode($str ,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

        return $code;
    }



    public function getapilistAction() {
        // 服务器群组
        /*$server_group = array(
            array('name' => 'V1 线下 服务器', 'ver' => 'v1', 'l' => "http://data.house.sina.com.cn/api/api.agent.php",'desc'=>''),
            array('name' => 'V1 线上 服务器', 'ver' => 'v1', 'l' => "http://data.house.sina.com.cn/api/api.agent.php?",'desc'=>''),
            array('name' => 'V1 Local 服务器', 'ver' => 'v1', 'l' => "http://data.house.sina.com.cn/api/api.agent.php?",'desc'=>''),
        );*/


        $api_group = \Yaf\Registry::get('apiGroupList')->toArray();
        


        $api_list = \Yaf\Registry::get('apiList')->toArray();
        
        $echo_array = array();
        $source = $this->getRequest()->getQuery("source");
        
        $getapigroup = $this->getRequest()->getQuery("apigroup");
        switch ($source) {
            case "apigroup":
                $echo_array = $api_group;
                break;
            case "apilist":
                $echo_array = $api_list[$getapigroup];
                break;
            default :

        }
        echo json_encode(array('k' => $echo_array));
        return false;
    }
}



/**
 * @param $json
 * @return string
 * 格式化输出json字符串
 */
function prettyPrint($json) {
    $result = '';
    $level = 0;
    $prev_char = '';
    $in_quotes = false;
    $ends_line_level = NULL;
    $json_length = strlen($json);

    for ($i = 0; $i < $json_length; $i++) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if ($ends_line_level !== NULL) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ($char === '"' && $prev_char != '\\') {
            $in_quotes = !$in_quotes;
        } else if (!$in_quotes) {
            switch ($char) {
                case '}': case ']':
                $level--;
                $ends_line_level = NULL;
                $new_line_level = $level;
                break;

                case '{': case '[':
                $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                $char = "";
                $ends_line_level = $new_line_level;
                $new_line_level = NULL;
                break;
            }
        }
        if ($new_line_level !== NULL) {
            $result .= "\n" . str_repeat("\t", $new_line_level);
        }
        $result .= $char . $post;
        $prev_char = $char;
    }

    return $result;
}

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
