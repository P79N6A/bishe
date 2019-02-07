<?php

use Resque\Queue;
use App\Tool\Controllers\Controller;

class ManageController extends Controller
{
	private $redis;
	private $MongoClient;
	private $mongo_client;

	private $user_info = array(
							  'tinglei'=>'5e31282b21bffb5d2e9e50dc20b4bfba', //5utXT6UO
							  'jingfu'=>'60ef51b0425bf9fa6b8d11f90d483b6e', //aq01Fxq3
							  'longchuan'=>'4206701c329bf35eb0c11c6d04f63db9', //1g2coqjE
							  'junfei' =>'771765831dd84096dbc2516fdae226d4',//G8omaSHt
							  'tinglei2' =>'771765831dd84096dbc2516fdae226d4',
							  'jinghui' =>'771765831dd84096dbc2516fdae226d4',
							  'wenshuai' =>'4206701c329bf35eb0c11c6d04f63db9',
							  );

	function init() {
		parent::init();
	 	$this->redis = \Cache\Redis::getInstance('queue');
	 	$this->MongoClientDb();
    }

    /**
	 * [indexAction description]
	 * @param  string $name [description]
	 * @return [bool]       
	 */
	public function indexAction($name = "Stranger") 
	{
		$this->getView()->display('ace/index.html');
        return false;
	}

	public function consoleAction(){
		$this->getView()->display('ace/console.html');
        return false;
	}

	public function loginAction(){	  
		$name=input('post.user');
		$pass=input('post.pass');

		if(!empty($pass)&&!empty($this->user_info[$name])){

			$error_times = $this->redis->get('PHPREDIS_SESSION_error_times:' . $name);//登陆失败次数
			//p($error_times);exit;
			if(!empty($this->user_info[$name])&&$this->user_info[$name]==md5($pass)){

				if ($error_times>=3) {//判断登陆失败的次数超过3次失败
					echo "登陆失败";
					exit;
				}

				$_SESSION['sessionid']['time'] = time();
		    	$_SESSION['sessionid']['name'] = $name;
		    	$this->redis->del('PHPREDIS_SESSION_error_times:' . $name);//登陆失败次数
		        Header("Location: http://".$_SERVER['SERVER_NAME']."/index.php/tool/Manage/index"); 
		        exit;

			 } else {

			 	$error_times = $this->redis->INCR('PHPREDIS_SESSION_error_times:' . $name);//登陆失败次数
			 	$this->redis->expire('PHPREDIS_SESSION_error_times:' . $name, 3600);
				echo '账号密码错误';
				exit;
			}

		} 

		$this->getView()->display('public/login.html');
		return false;
	}

	/**
	 * 
	 * [queueAddAction 添加队列]
	 * @return [bool] 
	 */
	public function queueAddAction(){
		$jobName=input('post.jobName');
		$arg=input('post.arg');
		//p($jobName);
		if (!empty($jobName) && !empty($arg)) {
				$jobName=input('post.jobName');
				$args=json_decode(trim($_POST['arg']),true);
				$res=Queue::queueAdd($jobName,$args);
			}
		$queueConfig=\Yaf\Registry::get('queue')->get('job')->toarray();
		foreach ($queueConfig as $key => $value) {
				foreach ($value as $k => $v) {
					$jobArr[]=$v;
				}
			}
		$this->getView()->assign("jobArr", $jobArr);	
		$this->getView()->display('ace/queueAdd.html');
		if (!empty($res)) {
				p($res);
			}
		return false;
	}

	/**
	 * [queueRunAction 队列进程的kill 和run]
	 * @return [bool] 
	 * 	kill -QUIT YOUR-WORKER-PID
	 */
	public function queueRunAction()
	{
		$opa=$this->curlQueue('curlCheck');
		if(empty($opa)){
			echo "代理失效:";
			exec('ps -ef |grep cli.php',$opa);
		}

		$run=trim(input('post.run'));
		$serverip = $_SERVER['SERVER_ADDR'];
		//1.队列启动
		if ($run == 1) {
			 $run_status = true;
			 foreach ($opa as $key => $value) {
			 	$arr=explode('/', $value);
			 	if (end($arr) == 'resqueRun') {
			 		$run_status = false;
			 		break;
			 	}
			 }
				 if ($run_status) {
		             $redisHost=\Yaf\Registry::get('CliConfig')->get('redis')->get('queue')->toarray();
		             if($redisHost['host']=='127.0.0.1'){
		             	$command = 'php5 cli.php request_uri="/Queue/Resque/resqueRun" >myout.file 2>&1 &';
		             }else{
		             	$command = '/usr/local/sinasrv2/bin/php cli.php request_uri="/Queue/Resque/resqueRun" >'.$_SERVER['SINASRV_CACHE_DIR'].'queueout.file 2>&1 &';
		             }
					echo $command;
					$re = exec($command, $op, $return_val);
					echo "启动返回值：";
					p($re);

				  	$this->redis->set('queue:server:ip',$serverip);
				 }else{
					 echo "<script>alert('操作失败，队列已经启动');</script>";
					 exit;

				 }
		  	}
		$stop=input('post.stop');
		 //2.队列停止 	
		if (!empty($stop)) {
				$command=$this->curlQueue('curlStop');
				p($command);

		  	}  	
		  	
		 p($opa);
		 $queueip=$this->redis->get('queue:server:ip');
		 echo '队列运行ip：'.$queueip;
		 echo '当前服务器ip：'.$serverip;
		 if (!empty($command)) {
		 	p($command);
		 }
		//$this->getView()->clearCache('ace/queueRun.html');
		$this->getView()->display('ace/queueRun.html');
		return false;
	}


	public function queueDebugAction()
	{
		if(!empty($_SERVER['SINASRV_CACHE_DIR'])){
			$file = $_SERVER['SINASRV_CACHE_DIR'].'queueout.file ';
			// $r=$this->DirList($_SERVER['SINASRV_CACHE_DIR']);
			// p($r);
		} else {
			$file = 'myout.file';

		}		
			$command = " cat ".$file;
			exec($command, $op, $return_val);
			p($op);

		// if(file_exists($file)){
		// 	$content = file_get_contents($file); //读取文件中的内容
		// 	p($content);
		// } else {
		// 	echo $file."不存在";
		// }

		return false;
	}

	/**
	 * [queueAction 队列列表]
	 * @return [bool] 
	 */
	public function queueAction()
	{
		
        //2获取配置文件
        $queueConfig=\Yaf\Registry::get('queue')->get('job')->toarray();

        $job=array();
        $status=array();
        //3.获取失败队列数组
        $fail['name'] = 'resque:failed';
        $fail['count']= $this->redis->lLEN($fail['name']);
        $fail['arr']  = $this->redis->lRange($fail['name'],'0','100');
        if (!empty($fail['arr'])) {
	        	foreach ($fail['arr'] as $kk => $vv) {
	        	$fail['arr'][$kk] = json_decode($vv,true);
	        	$jobName  = $fail['arr'][$kk]['payload']['class'];
	        	$queueFail= $this->GetQueueName($jobName);
	        	$status[$queueFail] = 'error';
	        }
        }

        $data=array();
        foreach ($queueConfig as $key => $value) {
        	$data[$key]['name']  = 'resque:queue:'.$key;
        	$data[$key]['count'] = $this->redis->lLEN('resque:queue:'.$key);
        	$data[$key]['job']   = implode(',',$value);
        	if (!empty($status)&&!empty($status[$key])) {
        		$data[$key]['status'] = $status[$key];
        	}else{
        		$data[$key]['status'] = 'normal';
        	}
        }

             // p($data);
        $this->getView()->assign("fail", $fail);
		$this->getView()->assign("data", $data);
		//$this->getView()->clearCache('ace/queue.html');
		$this->getView()->display('ace/queue.html');

        return false;
	}


	/**
	 * [queueDetails 队列列表详情]
	 */
	public function queueDetailsAction()
	{
		$name  = $_GET['queue'];
		$data  = $this->redis->lRange($name,'0','-1');
		foreach ($data as $key => $value) {
			$data[$key] = json_decode($value,true);
			$data[$key]['json'] = $value;
		}
		//p($data);
		$this->getView()->assign("data", $data);
		$this->getView()->display('ace/queueDetails.html');
		return false;
	}

	/**
	 * [queueFailAction 错误队列的详情]
	 * @return [bool] 
	 */
	
	public function queueFailAction()
	{
		$data  = $this->redis->lRange('resque:failed','0','-1');
		foreach ($data as $key => $value) {
			$data[$key] = json_decode($value,true);
			$data[$key]['json'] = $value;
			$data[$key]['source_data'] = json_encode($data[$key]['payload']);
			$data[$key]['key'] =$key;
			//var_dump($value);exit;
		}

		//p($data);
		$this->getView()->assign("data", $data);
		//$this->getView()->clearCache('ace/queueFail.html');
		$this->getView()->display('ace/queueFail.html');
		return false;

	}
	/**
	 * [FailInfoAction 错误详情]
	 */
	public function FailInfoAction()
	{	
		$key  = input('get.key');
		$data  = $this->redis->lRange('resque:failed',$key,$key);
		p(json_decode($data[0],true));
		return false;
	}

/**
 * [RecoveryAction 恢复数据,重新加入队列中]
 */
	public function RecoveryAction()
	{
		$key   = input('get.key');
		if($key == 'all'){
			$data  = $this->redis ->lRange('resque:failed','0','-1');
			p($data);
			foreach ($data as $key => $value) {
				$Arr = json_decode($value,true);
				$classArr=explode('\\',$Arr['payload']['class']);
				$classString=end($classArr);
				$res=Queue::queueAdd($classString,$Arr['payload']['args'][0]);
				if($res['status']){
					$status = $this->redis ->lrem('resque:failed',0,$value);
				}
			}

		} else {
			$data  = $this->redis ->lRange('resque:failed',$key,$key);
			$status = $this->redis ->lrem('resque:failed',0,$data[0]);
			p($status);
			if($status){
				$dataArr = json_decode($data[0],true);
				$classArr=explode('\\',$dataArr['payload']['class']);
				$classString=end($classArr);
				$res=Queue::queueAdd($classString,$dataArr['payload']['args'][0]);
				p($res);
			}
		}

		return false;
	}

	/**
	 * [DelAction description]
	 */
	public function DelAction(){
		$key   = input('get.key');
		$data  = $this->redis ->lRange('resque:failed',$key,$key);
		$status = $this->redis ->lrem('resque:failed',0,$data[0]);
		if ($status) {
			echo "<script>alert(删除成功);</script>";
		}
		return false; 

	}
	/**
	 * [WatchConfigAction 配置对比]
	 * ｚｌｃ
	 * 2018-10-28
	 */
	public function WatchConfigAction(){
		 $Config=\Yaf\Registry::get('config')->toarray();
 
		 $CliConfig=\Yaf\Registry::get('CliConfig')->toarray();

		 //将配置文件数组降维 到2维数组
		 foreach ($CliConfig as $key => $value) {
		 	$CliResult[$key]=array();
		 	$ConResult[$key]=array();
		 	$this->foo($value,$CliResult[$key]);
		 	$this->foo($Config[$key],$ConResult[$key]);
		 }
		 //比对数据
		 foreach ($CliResult as $key => $value) {
		 	foreach ($value as $kk => $vv) {
		 	    if(!empty($ConResult[$key][$kk])){
		 	    	 if ($vv==$ConResult[$key][$kk]) {
			 		 	$CliResult['con_'.$key][$kk] = $ConResult[$key][$kk];
			 		 } else {
			 		 	$CliResult['con_'.$key][$kk] = "<span style='color: #ac2925' >" .$ConResult[$key][$kk]."</span>";
			 		 }

		 	    } else {

		 	    	$CliResult[$key][$kk] = "<span style='color: #ac2925' >" .$vv."</span>"; 
		 	    	$CliResult['con_'.$key][$kk] = "空";
		 	    }	

		 	}
		 }
		$this->getView()->assign("data", $CliResult);
		//$this->getView()->clearCache('ace/WatchConfig.html');
		$this->getView()->display('ace/WatchConfig.html');
		return false; 
	}

	/**
	 * [queueLogAction 日志列表]
	 * @return [type] [description]
	 *  zlc　
	 * 2016-10-28
	 */
	public function queueLogAction()
	{
		$job = input('get.job');
		$startTime = input('get.startTime');
		$endTime = input('get.endTime');
		$status = input('get.status','0');
		$hid = input('get.hid');
		$city = input('get.city');
		$source = input('get.source');
		
		$queueConfig=\Yaf\Registry::get('queue')->get('job')->toarray();
		foreach ($queueConfig as $key => $value) {
				foreach ($value as $k => $v) {
					$jobArr[]=$v;
				}
			}
		//var_dump($startTime);exit;
		$MongoConfig=\Yaf\Registry::get('config')->get('mongodb')->get('read')->toarray();
		//p($MongoConfig);
		$collection = $this->mongo_client->$MongoConfig['database']->log; 
		// $collection->remove();
		$type='queue';
		$query['context.type'] = $type;

		if(empty($startTime)&&empty($endTime)){
			$startTime=date('Y-m-d',time());
		}


		if(!empty($job)){
				$query['context.trace.job'] = $job;
		}
		
		if(!empty($startTime)){
			$startTimeStamp=strtotime($startTime);
			$query['context.trace.time'] = array('$gt' => $startTimeStamp);
		}
		
		if(!empty($endTime)){
			$endTimeStamp=strtotime($endTime);
			$query['context.trace.time'] = array('$lte' => $endTimeStamp);
		}

		if(!empty($startTime)&&!empty($endTime)){
			$query['context.trace.time'] = array('$gt' => $startTimeStamp,'$lte' => $endTimeStamp);
		}

		if(!empty($status) || $status === '0'){
			$query['context.trace.status']=new MongoInt64($status);
		}



		if(!empty($hid)){
		  //$query['context.trace.content'] = new MongoRegex('/"hid":"'.$hid.'"/');
		  $query['context.log_index_hid'] = $hid;
		}

		if(!empty($city)){
		  //$query['context.trace.content'] = new MongoRegex('/:"'.$city.'"/');
		  $query['context.log_index_site'] = $city;
		}

		if(!empty($source)){
		//   $query['context.source'] = $source;
		  $query['context.source'] = new MongoInt64($source);


		}

		$count =input('get.count','10');
		$sort['context.trace.time']	= -1;
		
		if ($count == 'all') {
			$cursor = $collection->find($query)->sort($sort);
		} else {
			$cursor = $collection->find($query)->limit($count)->sort($sort);
		}
		
		$export =input('get.export');
		if ($export == 1) {//导出数据
		  foreach ($cursor as $key => $value) {
			// $cursor_arr[$key]['msg'] = $value['message'];
			$cursor_arr[$key]['content'] = $value['context']['trace']['content'];
			$cursor_arr[$key]['time'] = date('Y-m-d H:i:s',$value['context']['trace']['time']);
			$cursor_arr[$key]['res'] = $value['context']['trace']['res'];
			}
			if($export) export_txt(time().$job.'queuelog.txt',$cursor_arr);

		} else {//显示

		 foreach ($cursor as $key => $value) {
			$cursor_arr[$key]=$value;
			}
		}



		//$cursor=iterator_to_array($cursor);
		$this->getView()->assign("jobArr", $jobArr);
		$this->getView()->assign("startTime", $startTime);
		$this->getView()->assign("endTime", $endTime);
		$this->getView()->assign("city", $city);
		$this->getView()->assign("hid", $hid);
		$this->getView()->assign("source", $source);
		$this->getView()->assign("job", $job);
		$this->getView()->assign("count", $count);
		$this->getView()->assign("status", $status);
		$this->getView()->assign("data", $cursor_arr);
		$this->getView()->display('ace/queueLog.html');
		return false;
	}

	/**
	 * [queueLogInfoAction 日志详情]
	 * @return [type] [description]
	 * zlc　
	 * 2016-10-28
	 */
	public function queueLogInfoAction(){
		$id = input('get.id');
		$MongoConfig = \Yaf\Registry::get('config')->get('mongodb')->get('read')->toarray();
		$collection = $this->mongo_client->$MongoConfig['database']->log; 
		$query['_id'] = new MongoId(trim($id));
		$logOne = $collection->findOne($query);
		p($logOne);
		return false;
	}

	/**
	 * [queueRecoveryAction 失败队列重新消费]
	 * @Author   zlc
	 * @DateTime 2017-09-07
	 * @return   [type]     [description]
	 */
	public function queueRecoveryAction()
	{
		$mongoid=$_POST['mongoId'];
		$MongoConfig=\Yaf\Registry::get('config')->get('mongodb')->get('write')->toarray();
		$collection = $this->mongo_client->$MongoConfig['database']->log; 
		//array("_id"=>new MongoId("$id"))
		if(!empty($mongoid)){
			foreach ($mongoid as $key => $value) {
				$query['_id']=new MongoId(trim($value));
				$logOne=$collection->find($query);
				$logOne=iterator_to_array($logOne)[$value];
				$jobArr=json_decode($logOne['context']['trace']['content'],true);
				$res=Queue::queueAdd($jobArr['class'],$jobArr['arg']);
				if($res['status']){
					$where=array('_id'=>$query['_id']);
					$newdata['$set']['context.trace.status'] = 1;
					$b=$collection->update($where,$newdata);
				}
			}     
		}
		return false;
	}

	/**
	 * [curlStopAction description]
	 * @Author   zlc
	 * @DateTime 2016-12-21
	 * @return   [type]     [description]
	 */
	public function curlStopAction()
	{
		exec('ps -f -p '.input('post.pid'),$opp);
  		$arr = explode('/', $opp[1]);
  		if (end($arr) != 'resqueRun') {
  			echo "<script>alert('操作失败，进程id错误');</script>";
  			exit;
  		}
	 	if (input('post.stop') == 1) {//平滑停止
		$command='kill -QUIT '.input('post.pid'); 
	  	exec($command, $op, $return_val);

	  	}elseif(input('post.stop') == 2) {//立即停止
		  	$command='kill -9 '.input('post.pid'); 
		  	exec($command, $op, $return_val);
	  	}
		$serverip = $_SERVER['SERVER_ADDR'];
		echo json_encode($command);
		$status = TRUE;
		$opa=$this->curlQueue('curlCheck');
		foreach($opa as $process){//判断当前是否所有进程都停止
			if(stripos($process,"resqueRun")!== false){
				$status = FALSE;
			}
		}
		if($status){
			$this->redis->set('queue:server:ip','');
		}
		return false;
	}
	/**
	 * [curlCheckAction description]
	 * @Author   zlc
	 * @DateTime 2016-12-21
	 * @return   [type]     [description]
	 */
	public function curlCheckAction()
	{
		exec('ps -ef |grep cli.php',$opa);
		//$serverip = $_SERVER['SERVER_ADDR'];
		//echo "远程显示：".$serverip."<br>";
		echo json_encode($opa);
		return false;
	}
/*-------------------------------------------------以下为功能函数------------------------------------------------------------------------*/
	
	private static function GetQueueName($job)
    {
        $queueConfig=\Yaf\Registry::get('queue')->get('job')->toarray();

        foreach ($queueConfig as $key => $value) {
            if(in_array($job,$value)){
                return $key;
            }
        }
        return false;
    }  


    /**
     * [foo 数组降维处理]
     * @param  [type] $arr [description]
     * @param  [type] &$rt [description]
     * @return [type]      [description]
     */
   private function foo($arr, &$rt,$k='') 
   {
    if (is_array($arr)) {
        foreach ($arr as $key => $v) {
            if (is_array($v)) {
                $this->foo($v, $rt,$key);
            } else {
            	if(!empty($k) || $k===0){
            		$rt[$k.'_'.$key] = $v;
            	} else {
            		$rt[$key] = $v;
            	}
                
            }
        }
    }
    return $rt;
	}

	/**
	 * [DirList 列出目录下的所有文件]
	 * @param [type] $path [description]
	 * @param string $exts [description]
	 * @param array  $list [description]
	 */
	private function DirList($path, $exts = '', $list = array()) 
	{ 
		$path = str_replace('\\', '/', $path); 
		if (substr($path, -1) != '/') $path = $path . '/'; 
		$files = glob($path . '*'); 
		foreach($files as $v) { 
			if (!$exts || preg_match("/\.($exts)/i", $v)) { 
			$list[] = $v; 
				if (is_dir($v)) { 
				$list = $this->DirList($v, $exts, $list); 
				} 
			} 
		} 
		return $list; 
	} 

	/**
	 * [MongoClientDb mongo数据库链接]
	 */
	private function MongoClientDb()
	{

		$mongodb_config = \Yaf\Registry::get('config')->mongodb->toArray();
		$this->MongoClient  = new \Sokil\Mongo\Client($mongodb_config['dsn'], $mongodb_config['options']);

		$this->mongo_client = $this->MongoClient->getMongoClient();

	}

	/**
	 * [curlQueue 通过curl来指定当前运行queue的web服务器]
	 * @Author   zlc
	 * @DateTime 2016-12-21
	 * @return   [type]     [description]
	 */
	public function curlQueue($Action)
	{ 
		$post=input('post.');
		$sessionid=array('sessionid'=>$this->session_id);
		$post = array_merge($post, $sessionid);
		//p($post);
		//$remote_ip = "10.204.10.22";
		$remote_ip=$this->redis->get('queue:server:ip');
		if(Yaf\ENVIRON =='product'){
			$remote_ip = !empty($remote_ip)?$remote_ip:$_SERVER['SERVER_ADDR'];
		} else {
			$remote_ip = '123.59.190.247';
		}
		$redisHost=\Yaf\Registry::get('CliConfig')->get('redis')->get('queue')->toarray();
		if($redisHost['host']=='127.0.0.1'){
			$remote_ip = '127.0.0.1';
		}
		echo '远程ip：'.$remote_ip;
		$ch = curl_init();
		$PROXY_URL = 'http://'.$_SERVER['SERVER_NAME'].'/tool/Manage/'.$Action;
		// p($PROXY_URL);
		curl_setopt($ch, CURLOPT_URL,$PROXY_URL);
		curl_setopt($ch, CURLOPT_PROXY, $remote_ip.':80');//代理服务器地址
		//curl_setopt($ch, CURLOPT_PROXY, '10.204.10.65:80');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);  
		if (curl_errno($ch) != 0) {  
		    echo curl_error($ch);  
		} else {  
		    return json_decode($response,true);  
		}  
		curl_close($ch);
		//var_dump($response);
		

	}

	/**
	 * [syncMsgLogAction 读取推送日志,临时使用过后删除]
	 * @Author   zlc
	 * @DateTime 2016-12-30
	 * @return   [type]     [description]
	 */
	public function syncMsgLogAction()
	{
		$php_redis = \Cache\Redis::getInstance();
		$res = $php_redis->hGetAll('syncMsgLog');
		if ($res) {
            $res = $php_redis->dataOp($res);
            foreach ($res as $k=>$v) {
            	$a=explode('_', $k);
            	echo $a[0].'_'.$a[1].'_'.date('Y-m-d H:i:s',$a[2]).'<br/>';
            }
        }
       return false;
	}
}
