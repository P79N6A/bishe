<?php
use App\Tool\Controllers\Controller;
/**
 * Class RedisconsoleController
 * @author tinglei
 * @date   2016-09-06
 */
class RedisconsoleController extends Controller
{

    /**
     * 控制台默认面板
     * @return bool
     */
    public function indexAction()
    {
        //获得redis的配置
        $redis_config = \Yaf\Registry::get('config')->redis->toArray();
        foreach ($redis_config['cluster'] as $key => $value) {
            $servers[] = $value['host'].':'.$value['port'];
        }
        $this->getView()->assign('servers', $servers);
        $this->getView()->display('redis/console.html');
        return false;
    }

    /**
     * 控制台选择面板
     * @return bool
     */
    public function selectAction()
    {
        $this->getView()->display('redis/console_select.html');
        return false;
    }


    /**
     * 控制台命令执行方法
     */
    public function cmdAction()
    {
        $res = '';
        $cmd = urldecode($this->getRequest()->getQuery('cmd'));
        $host = urldecode($this->getRequest()->getQuery('host'));
        if ($host == 'cluster') {
            $redis = \Cache\Redis::getInstance();
        } else {
            $arr = explode(':', $host);
            $redis_conf = array('instance'=>array('host'=>$arr[0], 'port'=>$arr[1]));
            $redis = new \Cache\Redis($redis_conf, 'instance');
        }
        //$php_redis = $redis->getRedis();//使用原生php_redis扩展的方法，避免项目封装的方法不够全

        if ($cmd) {
            $arr = preg_split('/\s+/', $cmd);
            $args = array();
            foreach ($arr as $k=>$v) {
                if ($k == 0) {
                    $method = trim($v);
                }else{
                    $args[] = trim($v);
                }
            }
            if (!in_array($method,array('get','hget','hgetall','hlen','hkeys'))) {
                echo '暂时只支持get/hget命令';exit;
            }
            $res = call_user_func_array(array($redis, $method), $args);
            if ($res) {
                $res = $redis->dataOp($res);
            }
        }

        print_r($res);
        exit;
    }
}