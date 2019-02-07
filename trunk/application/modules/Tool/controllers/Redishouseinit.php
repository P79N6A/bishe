<?php
use App\Tool\Controllers\Controller;

/**
 * Class RedishouseinitController
 * @author tinglei
 * @date   2016-09-06
 */
class RedishouseinitController extends Controller
{

    /**
     * redis楼盘数据初始化页面展示
     * @return bool
     */
    public function indexAction()
    {
        //1.获取城市列表
        $city_list = $this->filerCityList(true, 'city_en');

        $this->getView()->assign('city_list',$city_list);
        $this->getView()->display('redis/house_init.html');
        return false;
    }


    /**
     * 获取处理过的城市列表
     * @param   bool   $is_pre_en   是否以英文字母+中文为城市名称，方便html中select元素的查询
     * @param   string $sort_fields　排序字段,city_en|city_cn
     * @return  array
     */
    public function filerCityList($is_pre_en = false, $sort_fields = 'city_en'){
        $return = array();

        $city_model = \App\Models\City::getInstance();
        //1.首先获取城市列表
        $res = $city_model->getCityList();
        if ($res) {
            //2.遍历下数据以英文字母开头
            if ($is_pre_en == true ){
                foreach ($res as $k=>$v) {
                    $city_en_arr[] = $v['city_en'];
                    $city_cn_arr[] = $v['city_cn'];
                    $res[$k]['city_en_cn'] = $v['city_code'].$v['city_cn'];
                }
            }
            //3.根据指定字段排序
            if ($sort_fields == 'city_en') {
                array_multisort($city_en_arr, $res);
            } elseif ($sort_fields == 'city_cn'){
                array_multisort($city_cn_arr, $res);
            }
        }

        $res && $return = $res;
        return $return;
    }

}