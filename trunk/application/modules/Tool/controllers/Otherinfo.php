<?php
use App\Tool\Controllers\Controller;

use \App\Models\City;

/**
 * Class OtherinfoController
 * @author weidi
 * @date   2016-09-14
 */
class OtherinfoController extends Controller {

    /**
     * redis楼盘数据初始化页面展示
     * @return bool
     */
    public function indexAction() 
    {

        $apiList = array(
            'equan' => array(
                'name' => '',
                'url' => '/other/equan/index',
                'api' => array(
                    'equan' => 'E金券',
                ),
            ),
            'esf' => array(
                'name' => '',
                'url' => '/other/esf/index',
                'api' => array(
                    'esf_city' => '有新房的二手房城市',
                    'esf_info' => '跟新房有联系的详细二手房信息，hash_key为楼盘site+hid',
                ),
            ),
            'im' => array(
                'name' => '',
                'url' => '/other/im/index',
                'api' => array(
                    'im' => 'IM,主要存储这个楼盘是否有IM',
                ),
            ),
            'member91' => array(
                'name' => '',
                'url' => '/other/member91/index',
                'api' => array(
                    'member91' => '91活动信息',
                    'member91_city' => '91活动城市列表',
                ),
            ),
            'kft' => array(
                'name' => '',
                'url' => '/other/kft/index',
                'api' => array(
                    'kft_activity' => '看房团',
                ),
            ),
            'didi' => array(
                'name' => '',
                'url' => '/other/didi/index',
                'api' => array(
                    'didi_city' => '滴滴城市',
                    'didi' => '滴滴专车信息',
                ),
            ),
            'live' => array(
                'name' => '',
                'url' => '/other/live/index',
                'api' => array(
                    'live' => '直播',
                ),
            ),
            'vr' => array(
                'name' => '',
                'url' => '/other/vr/index',
                'api' => array(
                    'vr_hangpai' => '航拍VR',
                ),
            ),
            'fangshou' => array(
                'name' => '',
                'url' => '/other/fangshou/index',
                'api' => array(
                    'fangshou_topnav' => '公用顶部',
                ),
            ),
            'fmtvideo' => array(
                'name' => '',
                'url' => '/other/fmtvideo/index',
                'api' => array(
                    'fmt_video' => '富媒体视频（置业顾问直播）',
                ),
            ),
            'tag' => array(
                'name' => '',
                'url' => '/other/tag/index',
                'api' => array(
                    'tag' => '标签',
                ),
            ),
        );
        $type = array(
            'total',
            'update'
        );

        $city_model = City::getInstance();
        $city_list = $city_model->getCityList();
        $this->getView()->assign("city_list", $city_list);
        $this->getView()->assign("apiList", $apiList);
        $this->getView()->assign("type", $type);
        $this->getView()->display('redis/thirddata.html');
        return false;
    }

    /**
     * 请求api
     */
    public function apiAction() 
    {
        $url = 'http://new.data.house.sina.com.cn/index.php/other/' . $_POST['action'] . '/index?api=' . $_POST['api'] . '&type=' . $_POST['type'];
        if (isset($_POST['site'])) {
            $url .= '&site=' . $_POST['site'];
        }
        http_get($url);
        return FALSE;
    }

}