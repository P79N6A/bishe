<?php

ini_set('memory_limit','64M');
set_time_limit(6000);

use Resque\Queue;
use App\Tool\Controllers\Controller;

/**
 * Class MongologController
 * @author weidi
 * @date   2016-10-15
 */


class MongologController extends Controller {

    private $redis;
    private $MongoClient;
    private $mongo_client;

    function init() {
        parent::init();
        //$this->redis = \Cache\Redis::getInstance('queue');
        $this->MongoClientDb();
    }

    /**
     * [indexAction description]
     */
    public function indexAction() 
    {
        ini_set('memory_limit','512M');

        $test = input('request.test');
        $page = input('request.page');
        $limit = 10;
        $type = input('request.type') ? trim(input('request.type')) : 'cron';
        $level_name = input('request.level_name') ? trim(input('request.level_name')) : 'INFO';
        $start_time = date('Y-m-d 00:00:00', time());
        $end_time = date('Y-m-d 23:59:59', time());
        $startTime = input('request.startTime') ? input('request.startTime') : $start_time;
        $endTime = input('request.endTime') ? input('request.endTime') : $end_time;

        $log_index_site = input('request.log_index_site') ? input('request.log_index_site') : '';
        $log_index_hid = input('request.log_index_hid') ? input('request.log_index_hid') : '';

        $page = $page?$page:1;
        //类型
        $MongoConfig=\Yaf\Registry::get('config')->get('mongodb')->get('read')->toarray();
        $collection = $this->mongo_client->$MongoConfig['database']->log;

        $types=\Yaf\Registry::get('dict')->get('log')->get('type')->toarray();
        $level_names = array(
            'INFO',
            'CRITICAL',
            'NOTICE',
            'EMERGENCY',
            'ALERT',
            'ERROR',
            'WARNING',
            'DEBUG',
        );
        //条件拼装
        if (!empty($type)) {
            $query['context.type'] = $type;
        }
        if (!empty($level)) {
            $query['level'] = $level;
        }
        if (!empty($level_name)) {
            $query['level_name'] = $level_name;
        }
        if (strtotime($startTime) <= strtotime($endTime)) {
            $startTime = date('Y-m-d 00:00:00', strtotime($startTime));
            $endTime = date('Y-m-d 23:59:59', strtotime($endTime));
        }

        if (!empty($log_index_site)) {
            $query['context.trace.args.city'] = $log_index_site;
        }
        if (!empty($log_index_hid)) {
            $query['context.trace.args.hid'] = $log_index_hid;
        }

        $query['datetime'] = array('$gt' => $startTime, "\$lte" => $endTime);
        $startTime = date('Y-m-d', strtotime($startTime));
        $endTime = date('Y-m-d', strtotime($endTime));
        $skip = ($page-1)*$limit;
        $sort = array('datetime'=>-1);
        $result_obj = $collection->find($query)->skip($skip)->limit($limit)->sort($sort);
        $totalcount = $collection->find($query)->count(1);
        foreach ($result_obj as $key => $value) {
          $result[$key]=$value;
        }
        $url = '/tool/mongolog/index';
        $pages = '';
        if($totalcount>$limit){
            if($type){
                $url .='?type='.$type;
            }
            if($level_name){
                $str_str = strstr($url, '?');
                $url .= $str_str?'&level_name='.$level_name:'?level_name='.$level_name;
            }
            if($startTime){
                $str_str = strstr($url, '?');
                $url .= $str_str?'&startTime='.$startTime:'?startTime='.$startTime;
            }
            if($endTime){
                $str_str = strstr($url, '?');
                $url .= $str_str?'&endTime='.$endTime:'?endTime='.$endTime;
            }

            //分页
            $pages = $this->page($page, $limit, $totalcount, $url);
        }
        $this->getView()->assign('log_index_site',$log_index_site);
        $this->getView()->assign("log_index_hid", $log_index_hid);
        $this->getView()->assign('pages',$pages);
        $this->getView()->assign("type", $type);
        $this->getView()->assign("level_name", $level_name);
        $this->getView()->assign("start_time", $startTime);
        $this->getView()->assign("end_time", $endTime);
        $this->getView()->assign("types", $types);
        $this->getView()->assign("level_names", $level_names);
        $this->getView()->assign("data", $result);
        $this->getView()->display('log/mongoLog.html');
        return false;
    }

    /**
     * [showLogsAction description]
     * @Author   zlc
     * @DateTime 2017-04-17
     * @return   [type]     [description]
     */
    public function showLogsAction()
    {
      $types = Array('cron','sphinx','redis','message','queue');
      //因生产环境列表展示mogo连接超时，丢失主机，所以现只能单个搜索，不能列表展示
      $type = input('get.type','queue');
      foreach ($types as $k => $v) {
        if ($type == $v) {
            $types_arr[] = $v;
        }
      }

      $MongoConfig=\Yaf\Registry::get('config')->get('mongodb')->get('read')->toarray();
      //admin_operate_log
      $collection = $this->mongo_client->$MongoConfig['database']->log; 
      foreach ($types_arr as $key => $value) {
          $query['context.type'] = $value;
          $data[$value] = $collection->find($query)->count(1);
      }

      $level_names = array('INFO','CRITICAL','NOTICE','EMERGENCY','ALERT','ERROR','WARNING','DEBUG');
      $this->getView()->assign("types", $types);
      $this->getView()->assign("type", $type);
      $this->getView()->assign("level_names", $level_names);
      $this->getView()->assign("data", $data);
      $this->getView()->display('log/showLogs.html');
      return false;
    }

    public function clearlogAction()
    {

        $type = input('request.type');
        $level_name = input('request.level_name','');
        $startTime = input('request.startTime','');
        $endTime = input('request.endTime','');
        $id = input('request.id','');
        if (!empty($type)) {
            $query['context.type'] = $type;
        } else {
            echo "{type} is empty !!";
            exit();
        }   
        if ($type == 'queue') {
            $query['context.trace.status']=new MongoInt64(1);
        }

        if (!empty($level_name)) {
           $query['level_name'] = $level_name;
        }

        if (!empty($startTime)) {
            $startTime = date('Y-m-d 00:00:00', strtotime($startTime));    
            $query['datetime'] = array('$gt' => $startTime);
        }

        if (!empty($endTime)) {
             $endTime = date('Y-m-d 23:59:59', strtotime($endTime));
             $query['datetime'] = array('$lte' => $endTime);
        }
        if (!empty($startTime)&&!empty($endTime)) {
           $query['datetime'] = array('$gt' => $startTime,'$lte' => $endTime);
        }
        
        if (!empty($id)) {
           $query['_id']= new MongoId(trim($id));
           //'59b10354a326d11ca6839f46';
        }

         
          
          $MongoConfig=\Yaf\Registry::get('config')->get('mongodb')->get('write')->toarray();
          //admin_operate_log
          $collection = $this->mongo_client->$MongoConfig['database']->log; 
          $count = $collection->find($query)->count(1);
          p($query);
          p($count);
          //$info['w']=true;
          //$info['socketTimeoutMS']=6000000000;
          if ($count) {
             $re = $collection->remove($query,array("w" => true,"socketTimeoutMS"=>6000000));//600秒
             //$num = $collection->count();
             p($re);
             if ($re) {
              echo "Success count:".$count;
             }
          } else {
            echo "需要删除的数据为空";
          }

          return false;

    }

    // public function ensureIndexAction(){

    //     $MongoConfig=\Yaf\Registry::get('config')->get('mongodb')->get('write')->toarray();
    //     $collection = $this->mongo_client->$MongoConfig['database']->log; 
    //     $query['context.type'] = 1;
    //     $query['datetime'] = -1;
    //     $re =  $collection->ensureIndex( $query );
    //     p($re);
    // }

    /**
     * [MongoClientDb mongo数据库链接]
     */
    private function MongoClientDb() 
    {

        $mongodb_config = \Yaf\Registry::get('config')->mongodb->toArray();
        $this->MongoClient = new \Sokil\Mongo\Client($mongodb_config['dsn'], $mongodb_config['options']);
        $this->mongo_client = $this->MongoClient->getMongoClient();

    }
    
    /**
     * @param int $current_page integer 当前页
     * @param $per_page integer 每页数量
     * @param $total_count integer 总数
     * @param $url_prefix string url前缀
     * @param $url_suffix string url后缀
     * @return string
     * @author weidi@leju.com
     * @modify by chenchen16@leju.com
     * @date 2016/11/21
     */
    public function page($current_page, $per_page, $total_count, $url_prefix , $url_suffix = '', $is_show_total_count = true, $class = 'btn', $current_class = 'btn active'){
        $str = '';

        if ($total_count <= $per_page) {
            return $str;
        }


        $total_page = ceil($total_count / $per_page);

        if ($current_page < 1) {
            $current_page = 1;
        }

        if ($current_page > $total_page) {
            $current_page = $total_page;
        }

        $pre_page = $current_page - 1;
        $next_page = $current_page + 1;

        if ($pre_page < 1) {
            $pre_page = 1;
        }

        if ($next_page > $total_page) {
            $next_page = $total_page;
        }

        if ($total_page > 10 && $current_page != 1) {
            $str .= '<a href="' . $url_prefix . '&page=1' . $url_suffix . '"' . ' class="'.$class.'">首页</a>';
        }

        $str .= '<a href="' . $url_prefix . $pre_page . $url_suffix . '"' . ' class="pre"><  上一页</a>';

        for ($i = 1; $i <= $total_page; $i++) {
            if ($i == $current_page) {
                $str .= '<a href="' . $url_prefix . '&page=' . $i . $url_suffix . '"' . ' class="'.$current_class.'">' . $i . '</a>';
            } else {
                if ($total_page > 10) {
                    if (abs($current_page - $i) < 5 || $i == $total_page) {
                        $str .= '<a href="' . $url_prefix . '&page=' . $i . $url_suffix . '"' . ' class="'.$class.'">' . $i . '</a>';
                    }

                    if ($i - $current_page == 5) {
                        $str .= '<a href="javascript:;"' . ' class="btn">...</a>';
                    }
                } else {
                    $str .= '<a href="' . $url_prefix . '&page=' . $i . $url_suffix . '"' . ' class="'.$class.'">' . $i . '</a>';
                }
            }
        }

        $str .= '<a href="'.$url_prefix . '&page=' . $next_page .  $url_suffix . '"' . ' class="next">下一页  ></a>';

        if ($total_page > 10 && $current_page != $total_page) {
            $str .= '<a href="' . $url_prefix . '&page='.$total_page . $url_suffix . '"' . ' class="'.$class.'">尾页</a>';
        }

        if ($is_show_total_count) {
            $str .='<span>共' . $total_count . '条记录</span>';
        }

        return $str;
    }




}
