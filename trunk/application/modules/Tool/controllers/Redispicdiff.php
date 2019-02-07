<?php
use App\Tool\Controllers\Controller;

ini_set('max_execution_time', 300);

/**
 * Class RedisPicdiffController·
 * @author weidi
 * @date   2016-10-20
 */
class RedispicdiffController extends Controller {

    public $php_redis = null;
    public $redis_house_key_pre = '';
    public $msg_suc = '<span style="color: #00be67">数量正常</span>';
    public $msg_error = '<span style="color: #ac2925">不正常</span>';

    public function init() 
    {
        $this->php_redis = \Cache\Redis::getInstance();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $this->redis_house_key_pre = $redis_key_conf['picture']['name'];
    }

    /**
     * 比对pictures表数据比对
     */
    public function indexAction() 
    {
        $result = array();
        $result['msg'] = $this->msg_suc;
        $result['mysql_total_count'] = $result['redis_total_count'] = 0;

        $cityList = \DB::select("select city_en,city_cn from ".CITY." where status = 1 and isforeign = 0 group by city_en ");

        foreach ($cityList as $key => $value) {
            $sql = "select site,count(site) as count from ".PICTURES." where site='" . $value['city_en'] . "' and status = 1 and hid != 0";
            $data = \DB::select($sql);
            if ($data[0]['count'] == 0) {
                continue;
            }
            $result['data'][$value['city_en']] = $data[0];
            $result['data'][$value['city_en']]['city_cn'] = $value['city_cn'];

            $result['mysql_total_count'] += $data[0]['count'];
            $result['data'][$value['city_en']]['mysql'] = $data[0]['count'];
            $result['data'][$value['city_en']]['redis'] = (int) ($this->php_redis->hLen($this->redis_house_key_pre . "_{$value['city_en']}"));
            $result['redis_total_count'] += (int) ($this->php_redis->hLen($this->redis_house_key_pre . "_{$value['city_en']}"));
            if ($result['data'][$value['city_en']]['mysql'] == $result['data'][$value['city_en']]['redis']) {
                $result['data'][$value['city_en']]['msg'] = $this->msg_suc;
            } else {
                $result['data'][$value['city_en']]['msg'] = $this->msg_error;
            }
        }

        if ($result['mysql_total_count'] != $result['redis_total_count']) {
            $result['msg'] = $this->msg_error;
        }
        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($result['data'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $result['data']);
        $this->getView()->assign('result', $result);
        $this->getView()->display('redis/pictures_diff.html');

        return false;
    }

    /**
     * 图片数据指定城市比对
     */
    public function houseSiteAction() 
    {
        ini_set('memory_limit','1024M');//1G
        //set_time_limit(7200);//两小时
        $result = array();
        $site = $this->getRequest()->getQuery('site');
        //取出mysql中seq
        $mysql_seqs = array();
        $seqs = \DB::select("select seq from ".PICTURES." where site = '{$site}' and status = 1");
        if (!empty($seqs)) {
            foreach ($seqs as $k => $v) {
                $mysql_seqs[$v['seq']] = $v['seq'];
            }
        }
        //取出redis中的图片
        $redis_key = $this->redis_house_key_pre . "_{$site}";
        $seqs = $this->php_redis->hKeys($redis_key);
        foreach ($seqs as $key => $seq) {
            $redis_seqs[$seq] = $seq; 
        }

        $res_merge = array_values(array_unique(array_merge($mysql_seqs, $redis_seqs)));

        //比对mysql跟redis
        foreach ($res_merge as $seq) {
            $result[$seq]['hash_key'] = $seq;
            $result[$seq]['redis_key'] = $redis_key;
            if (!empty($mysql_seqs[$seq])) {
                $result[$seq]['mysql'] = $seq;
            } else {
                $result[$seq]['mysql'] = '';
            }

            if (!empty($redis_seqs[$seq])) {
                $result[$seq]['redis'] = $seq;
            } else {
                $result[$seq]['redis'] = '';
            }

            if ($result[$seq]['redis'] == $result[$seq]['mysql']) {
                $result[$seq]['msg'] = $this->msg_suc;
            } else {
                $result[$seq]['msg'] = $this->msg_error;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($result as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $result);
        $this->getView()->assign('site', $site);
        $this->getView()->assign('result', $result);
        $this->getView()->display('redis/pictures_site.html');
        return false;
    }

    /**
     * 详细信息
     */
    public function hashDetailAction() 
    {
        $return = array();
        $redis_key = $this->getRequest()->getQuery('redis_key');
        $hash_key = $this->getRequest()->getQuery('hash_key');

        if ($hash_key) {
            $res = $this->php_redis->hGet($redis_key, $hash_key);
        } else {
            $res = $this->php_redis->hGetAll($redis_key);
        }

        if ($res) {
            $res = $this->php_redis->dataOp($res);
            $return = $res;
        }

        p($return);
        exit;
    }

}