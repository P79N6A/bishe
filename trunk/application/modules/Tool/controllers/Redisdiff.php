<?php
use App\Tool\Controllers\Controller;

/**
 * Class RedisdiffController
 * @author tinglei
 * @date   2016-09-05
 */

class RedisdiffController extends Controller
{
    /**@var 项目封装的redis类*/
    public $php_redis = null;

    /**@var 数据正常*/
    public $msg_suc = '<span style="color: #00be67">数量正常</span>';

    /**@var 数据不正常提示语*/
    public $msg_error = '<span style="color: #ac2925">不正常</span>';

    public function init()
    {
        parent::init();
        ini_set('memory_limit','800M');
        $this->getView()->caching = false;
        $this->php_redis = \Cache\Redis::getInstance();
    }


    /**
     * 比对ｈｏｕｓｅ表数据比对
     * @return bool
     */
    public function indexAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $mysql  = array();
        $res = \DB::select("select site,count(distinct hid ) as count from ".HOUSE." where status = 1 group by site ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['site'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['data'][$v['site']]['mysql'] = $v['count'];
                }
            }
        }


        //2.然后根据mysql获取redis数据
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house']['name'];
        foreach ($return['data'] as $site=>$v) {
            $return['data'][$site]['redis'] = (int)($this->php_redis->hLen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hLen($redsi_house_key_pre."_{$site}"));
            if ($return['data'][$site]['mysql'] == $return['data'][$site]['redis']) {
                $return['data'][$site]['msg'] = $this->msg_suc;
            } else {
                $return['data'][$site]['msg'] = $this->msg_error;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['data'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['data'] );
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/index.html');
        return false;
    }


    /**
     * 楼盘数据指定城市比对
     * @return bool
     */
    public function houseSiteAction()
    {
        $return = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘
        $mysql  = array();
        $res = \DB::select("select hid from ".HOUSE." where site = '{$site}' and status = 1");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                $mysql[] = $v['hid'];
            }
        }
        //2.然后取出redis中楼盘
        $redis = array();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house']['name'];
        $redis_key = $redsi_house_key_pre."_{$site}";
        $res = $this->php_redis->hKeys($redis_key);
        if ($res) {
            $redis = $res;
        }

        //3.合并数据
        $res_merge = array_values(array_unique(array_merge($mysql,$redis)));

        //3.然后比对mysql跟redis
        foreach ($res_merge as $hid) {
            $return[$hid]['hash_key'] = $hid;
            $return[$hid]['redis_key'] = $redis_key;
            if (in_array($hid,$mysql)) {
                $return[$hid]['mysql'] = $hid;
            } else {
                $return[$hid]['mysql'] = '';
            }

            if (in_array($hid,$redis)) {
                $return[$hid]['redis'] = $hid;
            } else {
                $return[$hid]['redis'] = '';
            }

            if ($return[$hid]['redis'] == $return[$hid]['mysql'] ) {
                $return[$hid]['msg'] = $this->msg_suc;
            } else {
                $return[$hid]['msg'] = $this->msg_error;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return);

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/house_site.html');
        return false;
    }



    /**
     * 楼盘印象数据比对
     * @return bool
     */
    public function impressionAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $mysql  = array();
        $res = \DB::select("select city,count(DISTINCT hid) as count from house_impression where status = 1 group by city ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['diff'][$v['city']]['mysql'] = $v['count'];
                }
            }
        }

        //2.然后根据mysql的site，从redis中取数据并进行比对
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_impression']['name'];
        foreach ($return['diff'] as $site=>$v) {
            $return['diff'][$site]['redis'] = (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            if ($return['diff'][$site]['redis'] != $return['diff'][$site]['mysql']) {
                $return['diff'][$site]['msg'] = $this->msg_error;
            } else {
                $return['diff'][$site]['msg'] = $this->msg_suc;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['diff'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['diff']);


        $this->getView()->assign('return',$return);
        $this->getView()->display('redis/impression_diff.html');
        return false;
    }


    /**
     * 楼盘印象数据指定城市比对
     * @return bool
     */
    public function impressionSiteAction()
    {
        $return = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘
        $mysql  = array();
        $res = \DB::select("select hid,count(id) as count from house_impression where city = '{$site}'  and status = 1 group by hid");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                $mysql[$v['hid']] = $v['count'];
            }
        }

        //2.然后取出redis中楼盘
        $redis = array();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_impression']['name'];
        $redis_key = $redsi_house_key_pre."_{$site}";
        $res = $this->php_redis->hGetAll($redis_key);
        if ($res) {
            $res = $this->php_redis->dataOp($res);
            foreach ($res as $k=>$v) {
                $redis[$k] = count($v);
            }
        }
        //3.合并数据
        foreach ($mysql as $hid=>$count) {
            $row = array();
            $row['hid'] = $hid;
            $row['mysql'] = $count;
            $row['redis'] = 0;
            if (isset($redis[$hid])) {
                $row['redis'] = $redis[$hid];
                unset($redis[$hid]);
            }
            $row['redis_key'] = $redis_key;
            if ($row['mysql'] == $row['redis']) {
                $row['msg'] = $this->msg_suc;
            } else {
                $row['msg'] = $this->msg_error;
            }
            $return[$hid] = $row;
        }

        if (count($redis) > 0) {
            foreach ($redis as $k=>$v) {
                $row = array();
                $row['hid'] = $k;
                $row['mysql'] = 0;
                $row['redis'] = $v;
                $row['redis_key'] = $redis_key;
                $row['msg'] = $this->msg_error;
                $return[$k] = $row;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return);

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/impression_site.html');
        return false;
    }

    /**
     * 楼盘印象数据比对
     * @return bool
     */
    public function priceSuiteAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $res = \DB::select("select city,count(DISTINCT hid) as count from house_price_suite group by city ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['diff'][$v['city']]['mysql'] = $v['count'];
                }
            }
        }

        //2.然后根据mysql的site，从redis中取数据并进行比对
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_price_suite']['name'];
        foreach ($return['diff'] as $site=>$v) {
            $return['diff'][$site]['redis'] = (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            if ($return['diff'][$site]['redis'] != $return['diff'][$site]['mysql']) {
                $return['diff'][$site]['msg'] = $this->msg_error;
            } else {
                $return['diff'][$site]['msg'] = $this->msg_suc;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['diff'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['diff']);


        $this->getView()->assign('return',$return);
        $this->getView()->display('redis/price_suite_diff.html');
        return false;
    }

    /**
     * 楼盘印象数据指定城市比对
     * @return bool
     */
    public function priceSuiteSiteAction()
    {
        $return = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘
        $mysql  = array();
        $res = \DB::select("select hid,count(id) as count from house_price_suite where city = '{$site}'  group by hid");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                $mysql[$v['hid']] = $v['count'];
            }
        }

        //2.然后取出redis中楼盘
        $redis = array();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_price_suite']['name'];
        $redis_key = $redsi_house_key_pre."_{$site}";
        $res = $this->php_redis->hGetAll($redis_key);
        if ($res) {
            $res = $this->php_redis->dataOp($res);
            foreach ($res as $k=>$v) {
                $redis[$k] = count($v);
            }
        }
        //3.合并数据
        foreach ($mysql as $hid=>$count) {
            $row = array();
            $row['hid'] = $hid;
            $row['mysql'] = $count;
            $row['redis'] = 0;
            if (isset($redis[$hid])) {
                $row['redis'] = $redis[$hid];
                unset($redis[$hid]);
            }
            $row['redis_key'] = $redis_key;
            if ($row['mysql'] == $row['redis']) {
                $row['msg'] = $this->msg_suc;
            } else {
                $row['msg'] = $this->msg_error;
            }
            $return[$hid] = $row;
        }

        if (count($redis) > 0) {
            foreach ($redis as $k=>$v) {
                $row = array();
                $row['hid'] = $k;
                $row['mysql'] = 0;
                $row['redis'] = $v;
                $row['redis_key'] = $redis_key;
                $row['msg'] = $this->msg_error;
                $return[$k] = $row;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return);

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/price_suite_site.html');
        return false;
    }

    /**
     * 楼盘视频数据比对
     * @return bool
     */
    public function videoAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $res = \DB::select("select city,count(DISTINCT hid) as count from ".HOUSE_VIDEO." where status = 1 and video_createtime != 0 group by city ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['diff'][$v['city']]['mysql'] = $v['count'];
                }
            }
        }

        //2.然后根据mysql的site，从redis中取数据并进行比对
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_video']['name'];
        foreach ($return['diff'] as $site=>$v) {
            $return['diff'][$site]['redis'] = (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            if ($return['diff'][$site]['redis'] != $return['diff'][$site]['mysql']) {
                $return['diff'][$site]['msg'] = $this->msg_error;
            } else {
                $return['diff'][$site]['msg'] = $this->msg_suc;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['diff'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['diff']);

        $this->getView()->assign('return',$return);
        $this->getView()->display('redis/video_diff.html');
        return false;
    }


    /**
     * 楼盘视频分城市比对
     * @return bool
     */
    public function videoSiteAction()
    {
        $return = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘
        $mysql  = array();
        $res = \DB::select("select hid,count(id) as count from ".HOUSE_VIDEO." where city = '{$site}'  and status = 1  and video_createtime != 0 group by hid");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                $mysql[$v['hid']] = $v['count'];
            }
        }

        //2.然后取出redis中楼盘
        $redis = array();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_video']['name'];
        $redis_key = $redsi_house_key_pre."_{$site}";
        $res = $this->php_redis->hGetAll($redis_key);
        if ($res) {
            $res = $this->php_redis->dataOp($res);
            foreach ($res as $k=>$v) {
                $redis[$k] = count($v);
            }
        }
        //3.合并数据
        foreach ($mysql as $hid=>$count) {
            $row = array();
            $row['hid'] = $hid;
            $row['mysql'] = $count;
            $row['redis'] = 0;
            if (isset($redis[$hid])) {
                $row['redis'] = $redis[$hid];
                unset($redis[$hid]);
            }
            $row['redis_key'] = $redis_key;
            if ($row['mysql'] == $row['redis']) {
                $row['msg'] = $this->msg_suc;
            } else {
                $row['msg'] = $this->msg_error;
            }
            $return[$hid] = $row;
        }

        if (count($redis) > 0) {
            foreach ($redis as $k=>$v) {
                $row = array();
                $row['hid'] = $k;
                $row['mysql'] = 0;
                $row['redis'] = $v;
                $row['redis_key'] = $redis_key;
                $row['msg'] = $this->msg_error;
                $return[$k] = $row;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return);

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/video_site.html');
        return false;
    }

    /**
     * 楼盘视频数据比对
     * @return bool
     */
    public function orderAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $res = \DB::select("select city,count(DISTINCT TYPE ) as count from ".HOUSE_ORDER." where status = 1  group by city ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['diff'][$v['city']]['mysql'] = $v['count'];
                }
            }
        }

        //2.然后根据mysql的site，从redis中取数据并进行比对
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_order']['name'];
        foreach ($return['diff'] as $site=>$v) {
            $return['diff'][$site]['redis'] = (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            if ($return['diff'][$site]['redis'] != $return['diff'][$site]['mysql']) {
                $return['diff'][$site]['msg'] = $this->msg_error;
            } else {
                $return['diff'][$site]['msg'] = $this->msg_suc;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['diff'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['diff']);

        $this->getView()->assign('return',$return);
        $this->getView()->display('redis/order_diff.html');
        return false;
    }

    public function orderSiteAction()
    {
        $return = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘
        $mysql  = array();
        $res = \DB::select("select type,count(id) as count from ".HOUSE_ORDER." where city = '{$site}'  and status = 1  group by type");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                $mysql[$v['type']] = $v['count'];
            }
        }

        //2.然后取出redis中楼盘
        $redis = array();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_order']['name'];
        $redis_key = $redsi_house_key_pre."_{$site}";
        $res = $this->php_redis->hGetAll($redis_key);
        if ($res) {
            $res = $this->php_redis->dataOp($res);
            foreach ($res as $k=>$v) {
                $redis[$k] = count($v);
            }
        }
        //3.合并数据
        foreach ($mysql as $type=>$count) {
            $row = array();
            $row['type'] = $type;
            $row['mysql'] = $count;
            $row['redis'] = 0;
            if (isset($redis[$type])) {
                $row['redis'] = $redis[$type];
                unset($redis[$type]);
            }
            $row['redis_key'] = $redis_key;
            if ($row['mysql'] == $row['redis']) {
                $row['msg'] = $this->msg_suc;
            } else {
                $row['msg'] = $this->msg_error;
            }
            $return[$type] = $row;
        }

        if (count($redis) > 0) {
            foreach ($redis as $k=>$v) {
                $row = array();
                $row['type'] = $k;
                $row['mysql'] = 0;
                $row['redis'] = $v;
                $row['redis_key'] = $redis_key;
                $row['msg'] = $this->msg_error;
                $return[$k] = $row;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return);
        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/order_site.html');
        return false;
    }


    /**
     * 楼盘价格数据比对
     * @return bool
     */
    public function priceAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $res = \DB::select("select city,count(DISTINCT hid) as count from ".HOUSE_PRICE." where status = 1 group by city ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['diff'][$v['city']]['mysql'] = $v['count'];
                }
            }
        }

        //2.然后根据mysql的site，从redis中取数据并进行比对
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_price']['name'];
        foreach ($return['diff'] as $site=>$v) {
            $return['diff'][$site]['redis'] = (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            if ($return['diff'][$site]['redis'] != $return['diff'][$site]['mysql']) {
                $return['diff'][$site]['msg'] = $this->msg_error;
            } else {
                $return['diff'][$site]['msg'] = $this->msg_suc;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['diff'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['diff']);

        $this->getView()->assign('return',$return);
        $this->getView()->display('redis/price_diff.html');
        return false;
    }


    /**
     * 楼盘价格分城市比对
     * @return bool
     */
    public function priceSiteAction()
    {
        $return = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘
        $mysql  = array();
        $res = \DB::select("select hid,count(id) as count from ".HOUSE_PRICE." where city = '{$site}'  and status = 1 group by hid");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                $mysql[$v['hid']] = $v['count'];
            }
        }

        //2.然后取出redis中楼盘
        $redis = array();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_price']['name'];
        $redis_key = $redsi_house_key_pre."_{$site}";
        $res = $this->php_redis->hGetAll($redis_key);
        if ($res) {
            $res = $this->php_redis->dataOp($res);
            foreach ($res as $k=>$v) {
                $redis[$k] = count($v);
            }
        }
        //3.合并数据
        foreach ($mysql as $hid=>$count) {
            $row = array();
            $row['hid'] = $hid;
            $row['mysql'] = $count;
            $row['redis'] = 0;
            if (isset($redis[$hid])) {
                $row['redis'] = $redis[$hid];
                unset($redis[$hid]);
            }
            $row['redis_key'] = $redis_key;
            if ($row['mysql'] == $row['redis']) {
                $row['msg'] = $this->msg_suc;
            } else {
                $row['msg'] = $this->msg_error;
            }
            $return[$hid] = $row;
        }

        if (count($redis) > 0) {
            foreach ($redis as $k=>$v) {
                $row = array();
                $row['hid'] = $k;
                $row['mysql'] = 0;
                $row['redis'] = $v;
                $row['redis_key'] = $redis_key;
                $row['msg'] = $this->msg_error;
                $return[$k] = $row;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return);

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/price_site.html');
        return false;
    }


    /**
     * 楼盘广播数据比对
     * @return bool
     */
    public function broadcastAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $res = \DB::select("select city,count(DISTINCT hid) as count from house_broadcast where status = 1 group by city ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['diff'][$v['city']]['mysql'] = $v['count'];
                }
            }
        }

        //2.然后根据mysql的site，从redis中取数据并进行比对
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_broadcast']['name'];
        foreach ($return['diff'] as $site=>$v) {
            $return['diff'][$site]['redis'] = (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            if ($return['diff'][$site]['redis'] != $return['diff'][$site]['mysql']) {
                $return['diff'][$site]['msg'] = $this->msg_error;
            } else {
                $return['diff'][$site]['msg'] = $this->msg_suc;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['diff'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['diff']);

        $this->getView()->assign('return',$return);
        $this->getView()->display('redis/broadcast_diff.html');
        return false;

    }

    /**
     * 按照城市比对广播数据
     * @return bool
     */
    public function broadcastsiteAction()
    {
        $return = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘
        $mysql  = array();
        $res = \DB::select("select hid,count(id) as count from house_broadcast where city = '{$site}'  and status = 1 group by hid");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                $mysql[$v['hid']] = $v['count'];
            }
        }

        //2.然后取出redis中楼盘
        $redis = array();
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_broadcast']['name'];
        $redis_key = $redsi_house_key_pre."_{$site}";
        $res = $this->php_redis->hGetAll($redis_key);
        if ($res) {
            foreach ($res as $k=>$v) {
                $redis[$k] = 1;
            }
        }

        //3.合并数据
        foreach ($mysql as $hid=>$count) {
            $row = array();
            $row['hid'] = $hid;
            $row['mysql'] = $count;
            $row['redis'] = 0;
            if (isset($redis[$hid])) {
                $row['redis'] = $redis[$hid];
                unset($redis[$hid]);
            }
            $row['redis_key'] = $redis_key;
            if ($row['mysql'] == $row['redis']) {
                $row['msg'] = $this->msg_suc;
            } else {
                $row['msg'] = $this->msg_error;
            }
            $return[$hid] = $row;
        }

        if (count($redis) > 0) {
            foreach ($redis as $k=>$v) {
                $row = array();
                $row['hid'] = $k;
                $row['mysql'] = 0;
                $row['redis'] = $v;
                $row['redis_key'] = $redis_key;
                $row['msg'] = $this->msg_error;
                $return[$k] = $row;
            }
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return);

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/broadcast_site.html');
        return false;
    }

    /**
     * 楼盘广播数据比对
     * @return bool
     */
    public function scoreAction()
    {
        $return = array();
        $return['msg'] = $this->msg_suc;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //1.取出mysql中楼盘各个城市的总数
        $res = \DB::select("select city,count(DISTINCT hid) as count from house_broadcast where status = 1 group by city ");
        if (!empty($res)) {
            $res = to_array($res);
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $return['diff'][$v['city']]['mysql'] = $v['count'];
                }
            }
        }

        //2.然后根据mysql的site，从redis中取数据并进行比对
        $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
        $redsi_house_key_pre = $redis_key_conf['house_broadcast']['name'];
        foreach ($return['diff'] as $site=>$v) {
            $return['diff'][$site]['redis'] = (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            $return['redis_total_count'] += (int)($this->php_redis->hlen($redsi_house_key_pre."_{$site}"));
            if ($return['diff'][$site]['redis'] != $return['diff'][$site]['mysql']) {
                $return['diff'][$site]['msg'] = $this->msg_error;
            } else {
                $return['diff'][$site]['msg'] = $this->msg_suc;
            }
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        //排序下不正常的在上边
        $msg_arr = array();
        foreach ($return['diff'] as $v) {
            $msg_arr[] = $v['msg'];
        }
        array_multisort($msg_arr, SORT_DESC, $return['diff']);

        $this->getView()->assign('return',$return);
        $this->getView()->display('redis/broadcast_diff.html');
        return false;

    }


    /**
     * 城市配置config比对
     * @return bool
     */
    public function configAction()
    {
        $return = array();
        $return['msg'] = $this->msg_error;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //１．首先统计mysql数量
        $mysql  = array();
        $res = \DB::select("select city,count(distinct `key`) as count from ".HOUSE_CONFIG."  group by city ");
        if (!empty($res)) {
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $mysql[$v['city']] = $v['count'];
                }
            }
        }

        //2.根据mysql统计redis
        if ($mysql) {
            $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
            $redsi_house_key_pre = $redis_key_conf['newhouse_config']['name'];
            foreach ($mysql as $site=>$count) {
                $arr[$site]['mysql_count'] = $count;
                $arr[$site]['redis_count'] = 0;
                $arr[$site]['msg'] = $this->msg_error;

                $arr[$site]['redis_count'] = $this->php_redis->hLen($redsi_house_key_pre."_{$site}");
                if ($arr[$site]['mysql_count'] == $arr[$site]['redis_count']) {
                    $arr[$site]['msg'] = $this->msg_suc;
                }
                $return['redis_total_count'] += $arr[$site]['redis_count'];
            }

            //排序下不正常的在上边
            $msg_arr = array();
            foreach ($arr as $v) {
                $msg_arr[] = $v['msg'];
            }
            array_multisort($msg_arr, SORT_DESC, $arr);
            $return['diff'] = $arr;
        }

        if ($return['mysql_total_count'] == $return['redis_total_count']) {
            $return['msg'] = $this->msg_suc;
        }


        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/config_diff.html');
        return false;
    }


    /**
     * 城市配置config指定城市比对
     * @return bool
     */
    public function configSiteAction()
    {
        $return = array();
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['msg'] = $this->msg_suc;
        $return['diff'] = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘各个城市的总数
        $mysql  = array();
        $res = \DB::select("select distinct `key`  from ".HOUSE_CONFIG." where city = '{$site}'");
        if (!empty($res)) {
            foreach ($res as $k=>$v){
                $mysql[$v['key']] = $v['key'];
                $return['mysql_total_count']++;
            }
        }

        //2.根据mysql中获取的数据然后取出redis中总数以及各个城市的总数
        if ($mysql) {
            $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
            $redsi_house_key_pre = $redis_key_conf['newhouse_config']['name'];
            foreach ($mysql as $key=>$key) {
                $arr[$key]['mysql'] = $key;
                $arr[$key]['redis'] = '';
                $arr[$key]['msg'] = $this->msg_suc;

                $redis_key = $redsi_house_key_pre."_{$site}";
                $arr[$key]['redis_key'] = $redis_key;
                $arr[$key]['hash_key'] = $key;
                if ($this->php_redis->hGet($redis_key, $key)) {
                    $arr[$key]['redis'] = $key;
                    $return['redis_total_count']++;
                }else{
                    $arr[$key]['msg'] = $this->msg_error;
                }
            }

            //排序下不正常的在上边
            $msg_arr = array();
            foreach ($arr as $v) {
                $msg_arr[] = $v['msg'];
            }
            array_multisort($msg_arr, SORT_DESC, $arr);

            $return['diff'] = $arr;
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/config_site.html');
        return false;
    }


    public function optionsSiteAction()
    {
        $return = array();
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['msg'] = $this->msg_suc;
        $return['diff'] = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘各个城市的总数
        $mysql  = array();
        $res = \DB::select("select id  from ".HOUSE_OPTIONS." where city = '{$site}' and status = 1");
        if (!empty($res)) {
            foreach ($res as $k=>$v){
                $mysql[$v['id']] = $v['id'];
                $return['mysql_total_count']++;
            }
        }

        //2.根据mysql中获取的数据然后取出redis中总数以及各个城市的总数
        if ($mysql) {
            $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
            $redsi_house_key_pre = $redis_key_conf['newhouse_options']['name'];
            foreach ($mysql as $id=>$id) {
                $arr[$id]['mysql'] = $id;
                $arr[$id]['redis'] = '';
                $arr[$id]['msg'] = $this->msg_suc;

                $redis_key = $redsi_house_key_pre."_{$site}";
                $arr[$id]['redis_key'] = $redis_key;
                $arr[$id]['hash_key'] = $id;
                if ($this->php_redis->hGet($redis_key, $id)) {
                    $arr[$id]['redis'] = $id;
                    $return['redis_total_count']++;
                }else{
                    $arr[$id]['msg'] = $this->msg_error;
                }
            }

            //排序下不正常的在上边
            $msg_arr = array();
            foreach ($arr as $v) {
                $msg_arr[] = $v['msg'];
            }
            array_multisort($msg_arr, SORT_DESC, $arr);

            $return['diff'] = $arr;
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/options_site.html');
        return false;
    }


    /**
     * 城市选项
     * @return bool
     */
    public function optionsAction()
    {
        $return = array();
        $return['msg'] = $this->msg_error;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //１．首先统计mysql数量
        $mysql  = array();
        $res = \DB::select("select city,count(id) as count from ".HOUSE_OPTIONS." where status = 1 group by city ");
        if (!empty($res)) {
            foreach ($res as $k=>$v){
                if (in_array($v['city'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $mysql[$v['city']] = $v['count'];
                }
            }
        }

        //2.根据mysql统计redis
        if ($mysql) {
            $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
            $redsi_house_key_pre = $redis_key_conf['newhouse_options']['name'];
            foreach ($mysql as $site=>$count) {
                $arr[$site]['mysql_count'] = $count;
                $arr[$site]['redis_count'] = 0;
                $arr[$site]['msg'] = $this->msg_error;

                $arr[$site]['redis_count'] = $this->php_redis->hLen($redsi_house_key_pre."_{$site}");
                if ($arr[$site]['mysql_count'] == $arr[$site]['redis_count']) {
                    $arr[$site]['msg'] = $this->msg_suc;
                }
                $return['redis_total_count'] += $arr[$site]['redis_count'];
            }

            //排序下不正常的在上边
            $msg_arr = array();
            foreach ($arr as $v) {
                $msg_arr[] = $v['msg'];
            }
            array_multisort($msg_arr, SORT_DESC, $arr);
            $return['diff'] = $arr;
        }

        if ($return['mysql_total_count'] == $return['redis_total_count']) {
            $return['msg'] = $this->msg_suc;
        }


        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/options_diff.html');
        return false;
    }

    /**
     * [developer description]
     * @Author   zlc
     * @DateTime 2017-10-11
     * @return   [type]     [description]
     */
    public function developerAction(){

        $return = array();
        $return['msg'] = $this->msg_error;
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['diff'] = array();
        $city_list = $this->getCityList();

        //１．首先统计mysql数量
        $mysql  = array();
        $res = \DB::select("select site,count(id) as count from developer where status = 0 group by site ");
        if (!empty($res)) {
            foreach ($res as $k=>$v){
                if (in_array($v['site'], $city_list)) {
                    $return['mysql_total_count'] += $v['count'];
                    $mysql[$v['site']] = $v['count'];
                }
            }
        }

        //2.根据mysql统计redis
        if ($mysql) {
            $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
            $redsi_house_key_pre = $redis_key_conf['developer']['name'];
            foreach ($mysql as $site=>$count) {
                $arr[$site]['mysql_count'] = $count;
                $arr[$site]['redis_count'] = 0;
                $arr[$site]['msg'] = $this->msg_error;

                $arr[$site]['redis_count'] = $this->php_redis->hLen($redsi_house_key_pre."_{$site}");
                if ($arr[$site]['mysql_count'] == $arr[$site]['redis_count']) {
                    $arr[$site]['msg'] = $this->msg_suc;
                }
                $return['redis_total_count'] += $arr[$site]['redis_count'];
            }

            //排序下不正常的在上边
            $msg_arr = array();
            foreach ($arr as $v) {
                $msg_arr[] = $v['msg'];
            }
            array_multisort($msg_arr, SORT_DESC, $arr);
            $return['diff'] = $arr;
        }

        if ($return['mysql_total_count'] == $return['redis_total_count']) {
            $return['msg'] = $this->msg_suc;
        }


        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/developer_diff.html');
        return false;

    }


    public function developerSiteAction()
    {
        $return = array();
        $return['mysql_total_count'] = $return['redis_total_count'] = 0;
        $return['msg'] = $this->msg_suc;
        $return['diff'] = array();
        $site = $this->getRequest()->getQuery('site');

        //1.取出mysql中楼盘各个城市的总数
        $mysql  = array();
        $res = \DB::select("select id  from developer where site = '{$site}' and status = 0");
        if (!empty($res)) {
            foreach ($res as $k=>$v){
                $mysql[$v['id']] = $v['id'];
                $return['mysql_total_count']++;
            }
        }

        //2.根据mysql中获取的数据然后取出redis中总数以及各个城市的总数
        if ($mysql) {
            $redis_key_conf = \Yaf\Registry::get('redisKey')->toArray();
            $redsi_house_key_pre = $redis_key_conf['developer']['name'];
            foreach ($mysql as $id=>$id) {
                $arr[$id]['mysql'] = $id;
                $arr[$id]['redis'] = '';
                $arr[$id]['msg'] = $this->msg_suc;

                $redis_key = $redsi_house_key_pre."_{$site}";
                $arr[$id]['redis_key'] = $redis_key;
                $arr[$id]['hash_key'] = $id;
                if ($this->php_redis->hGet($redis_key, $id)) {
                    $arr[$id]['redis'] = $id;
                    $return['redis_total_count']++;
                }else{
                    $arr[$id]['msg'] = $this->msg_error;
                }
            }

            //排序下不正常的在上边
            $msg_arr = array();
            foreach ($arr as $v) {
                $msg_arr[] = $v['msg'];
            }
            array_multisort($msg_arr, SORT_DESC, $arr);

            $return['diff'] = $arr;
        }
        if ($return['mysql_total_count'] != $return['redis_total_count']) {
            $return['msg'] = $this->msg_error;
        }

        $this->getView()->assign('site', $site);
        $this->getView()->assign('return', $return);
        $this->getView()->display('redis/developer_site.html');
        return false;
    }



    public function hashDetailAction(){
        $return = array();
        $redis_key = $this->getRequest()->getQuery('redis_key');
        $hash_key = $this->getRequest()->getQuery('hash_key');

        if ($hash_key) {
            $res = $this->php_redis->hGet($redis_key, $hash_key);
        }else{
            $res = $this->php_redis->hGetAll($redis_key);
        }

        if($res){
            $res = $this->php_redis->dataOp($res);
            $return = $res;
        }

        p($return);exit;
    }

    public function getCityList(){
        $return = array();

        $res = \App\Models\City::getInstance()->getCityList();
        if (!empty($res)) {
            $return = array_keys($res);
        }

        return $return;
    }
}