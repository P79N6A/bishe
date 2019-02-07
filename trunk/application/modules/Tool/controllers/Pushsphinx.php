<?php
use App\Tool\Controllers\Controller;

use \App\Models\City;


class PushsphinxController extends Controller {


    function init() {
        parent::init();
    }

    public function indexAction() 
    {

    }
    
    public function addjobAction()
    {
        $post = input('post.');
        if(!empty($post) && !empty($post['city']) && !empty($post['type'])){

            $redis = \Cache\Redis::getInstance();
            $type = $post['type'];
            $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
            $redis_key = $redis_key_conf[$type]['name'];
            if($post['city']=='all'){
                $sql = "select city_en,city_cn from city where status=1";
                $city = \DB::select($sql);
                foreach ($city as $key => $value) {
                    $redis->rPush($redis_key,$city['city_en']);
                }
            } else {
                $redis->rPush($redis_key,$post['city']);
            }

            echo "<script>alert('提交成功')</script>";
        }
        $type = array('house_all_queue'=>'楼盘','picture_all_queue'=>'图片');
        $sql = "select city_en,city_cn from city where status=1";
        $city = \DB::select($sql);
        $this->getView()->assign("city", $city);
        $this->getView()->assign("type", $type);
        $this->getView()->display('pushsphinx/addjob.html');
        return false;
    }


    //进程管理
    public function processAction()
    {

        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redis_key_house = $redis_key_conf['house_all_queue']['name'];
        $redis_key_pic = $redis_key_conf['picture_all_queue']['name'];
        
        //redis 城市count
        $redis = \Cache\Redis::getInstance();
        $house_count = $redis->lLen($redis_key_house);
        $pic_count = $redis->lLen($redis_key_pic);

        //进程状态
        $opa = $this->agent('curlCheck');
        //p($opa);

        $house_pid = 0;
        $picture_pid = 0;
        $house_status = 'error';
        $picture_status = 'error';

        foreach($opa as $process){//判断当前是否所有进程都停止

			if(stripos($process,"Pushsphinxbatch/house")!== false){
                $process_arr = explode(' ',$process);

                $house_pid = $process_arr[1];
                $house_status = 'success';
			}

            if(stripos($process,"Pushsphinxbatch/picture")!== false){
                $process_arr = explode(' ',$process);
                $picture_pid = $process_arr[1];
				$picture_status = 'success';
			}
		}

        $list['house_all_queue'] = array('function'=>'house','count'=>$house_count,'status'=>$house_status,'pid'=>$house_pid);
        $list['picture_all_queue'] = array('function'=>'picture','count'=>$pic_count,'status'=>$picture_status,'pid'=>$picture_pid);
        $this->getView()->assign("list", $list);

        $this->getView()->display('pushsphinx/process.html');
        return false;
    }

    public function startRunAction(){
        $param['type'] = input('get.type');
        if(!empty($param['type'])){
            $re = $this->agent('run',$param);
        }else{
            echo '类型不能为空';
        }
        var_dump($re);
        return false;
    }

    public function stopRunAction(){
        $param['pid'] = input('get.process');
        if(!empty($param['pid'])){
            $re = $this->agent('curlStop',$param);
        }else{
            echo '进程id为空';
        }
        var_dump($re);
        return false;
    }



	public function dbgAction()
	{
        p($_SERVER['SERVER_ADDR']);
		if(!empty($_SERVER['SINASRV_CACHE_DIR'])){
			$file = $_SERVER['SINASRV_CACHE_DIR'].'pushdbg.file ';
		} 	echo $file;
			$command = " cat ".$file;
			exec($command, $op, $return_val);
			p($op);

		return false;
	}
/*********************************代理显示公共函数***********************************************************/
	public function curlCheckAction()
	{
		exec('ps -ef |grep cli.php',$opa);
		echo json_encode($opa);
		return false;
    }
    
    public function runAction(){

            exec('ps -ef |grep cli.php',$opa);
            $type = input('post.type');
            $run_status = true;
            foreach ($opa as $key => $value) {
                $arr=explode('/', $value);
                if (end($arr) == $type) {
                    $run_status = false;
                    break;
                }
            }
            if ($run_status) {
                if(\Yaf\ENVIRON === 'develop'){
                    $command = "php5 cli.php request_uri='/Cron/Pushsphinxbatch/{$type}' >myout.file 2>&1 &";
                } else {
                    $command = "/usr/local/sinasrv2/bin/php cli.php request_uri='/Cron/Pushsphinxbatch/{$type}' >{$_SERVER['SINASRV_CACHE_DIR']}pushdbg.file 2>&1 &"; 
                }
                    echo json_encode($command);
                    exec($command, $op, $return_val);

            }else{
                //echo "<script>alert('操作失败，队列已经启动');</script>";
                echo json_encode('操作失败，队列已经启动');
                exit;
            }
            return false;

    }

	public function curlStopAction()
	{
		exec('ps -f -p '.input('post.pid'),$opp);
  		$arr = explode('/', $opp[1]);
        $command='kill -9 '.input('post.pid'); 
        exec($command, $op, $return_val);
		echo json_encode($command);
		return false;
	}
    /**
	 * [通过curl来指定后台运行的web服务器]
	 * @Author   zlc
	 * @DateTime 2016-12-21
	 * @return   [type]     [description]
	 */
	public function agent($action,$param = array())
	{ 

		$post=input('post.');
		$sessionid=array('sessionid'=>$this->session_id);
		$post = array_merge($post, $sessionid, $param);
		if(Yaf\ENVIRON =='product'){
            //$remote_ip = !empty($remote_ip)?$remote_ip:$_SERVER['SERVER_ADDR'];
            $remote_ip = "10.204.10.68";
		} else {
			$remote_ip = '127.0.0.1';
        }
        echo $remote_ip;
		$ch = curl_init();
		$PROXY_URL = 'http://'.$_SERVER['SERVER_NAME'].'/tool/Pushsphinx/'.$action;
		curl_setopt($ch, CURLOPT_URL,$PROXY_URL);
		curl_setopt($ch, CURLOPT_PROXY, $remote_ip.':80');//代理服务器地址
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);  
		if (curl_errno($ch) != 0) {  
		    echo curl_error($ch);  
		} else {  
            //var_dump(json_decode($response,true));
            //var_dump(json_decode($response,true));
		    return json_decode($response,true);  
		}  
		curl_close($ch);
	}



}