<?php
use Yaf\Controller_Abstract;
use Yaf\Registry;
use Yaf\Dispatcher;

/**
 * 数据导入类
 */
class ImportController extends Controller_Abstract
{
    /**
     * 方法执行前调取的函数
     * @author chenchen16@leju.com
     * @date 2017/7/17
     */
    public function init()
    {
        //设置超时时间
        set_time_limit(7200);//两小时
        //设置内存限制
        ini_set('memory_limit','2048M');//2G

        //关闭视图
        Dispatcher::getInstance()->disableView();
    }

    /**
     * 商业地产图片数据导入
     * @author chenchen16@leju.com
     * @date 2017/7/17
     */
    public function businessPictureAction()
    {
        exit('数据已导完');
        $business_res = DB::table(BUSINESS)
            ->where('site', '!=', '')
            ->where('hid', '!=', 0)
            ->where('id', '>', 3)
            ->get(['id', 'site', 'hid']);

        if (empty($business_res)) {
            exit('商业地产没有数据！');
        }
        $business_res = to_array($business_res);

        $business_data = $houses = array();
        foreach ($business_res as $key => $value) {
            $business_data[$value['site'] . '_' . $value['hid']] = $value['id'];

            $houses[$value['site']][] = $value['hid'];
        }

        unset($business_res);

        $insert_data = array();

        foreach ($houses as $site => $hids) {
            $picture_res = DB::table(PICTURES)
                ->where(['site' => $site, 'status' => 1])
                ->whereIn('hid', $hids)
                ->whereIn('pictypeid', [1, 2, 6, 9, 16])
                ->get([
                    'seq',
                    'site', 'hid', 'pictypeid', 'pic_url', 'pic_size', 'pic_width', 'pic_height', 'descrip',
                    'comment', 'creator', 'updator', 'createtime', 'updatetime'
                ]);
            if (empty($picture_res)) {
                continue;
            }

            foreach ($picture_res as $key => $value) {
                //$seqs[] = $value['seq'];
                $insert_data[$value['seq']]['business_id'] = $business_data[$value['site'] . '_' . $value['hid']];
                $insert_data[$value['seq']]['site'] = $value['site'];

                switch ($value['pictypeid']) {
                    case 1:
                        $insert_data[$value['seq']]['type'] = 2;
                        break;
                    case 2:
                        $insert_data[$value['seq']]['type'] = 3;
                        break;
                    case 6:
                        $insert_data[$value['seq']]['type'] = 1;
                        break;
                    case 9:
                        $insert_data[$value['seq']]['type'] = 4;
                        break;
                    case 16:
                        $insert_data[$value['seq']]['type'] = 5;
                        break;
                    default:
                        $insert_data[$value['seq']]['type'] = 0;
                        break;
                }

                $pic_url = explode('.', $value['pic_url']);
                $insert_data[$value['seq']]['pic_path'] = ltrim($pic_url[0], '/');
                $insert_data[$value['seq']]['pic_ext'] = $pic_url[1];

                $insert_data[$value['seq']]['pic_size'] = $value['pic_size'];
                $insert_data[$value['seq']]['pic_width'] = $value['pic_width'];
                $insert_data[$value['seq']]['pic_height'] = $value['pic_height'];
                $insert_data[$value['seq']]['status'] = 1;
                $insert_data[$value['seq']]['descrip'] = $value['descrip'];
                $insert_data[$value['seq']]['comment'] = $value['comment'];
                $insert_data[$value['seq']]['creator'] = $value['creator'];
                $insert_data[$value['seq']]['updator'] = $value['updator'];
                $insert_data[$value['seq']]['createtime'] = $value['createtime'];
                $insert_data[$value['seq']]['updatetime'] = $value['updatetime'];
            }

            echo $site . '导入成功！';

            unset($picture_res);

            //usleep(400);
        }

        ksort($insert_data);
        //$insert_data = utf82gbk($insert_data);

        $chunk_insert_data = array_chunk($insert_data, 4000);

        unset($insert_data);

        foreach ($chunk_insert_data as $key => $insert_data) {
            //DB::table('business_picture')->insert($insert_data);
            //usleep(400);
        }

        exit('导入完毕！');
    }


    public function businessAction()
    {
        exit('数据已导完');
        set_time_limit(0);
        ini_set('memory_limit','4000M');//1G


        $fields = array();
        $res = \DB::select('show FIELDS from '.BUSINESS.' ');
        foreach ($res as $k=>$v) {
            if ($v['Field'] != 'id') {
                $fields[] = $v['Field'];
            }
        }

        //1.
        $option_fields = array('district','circlelocation','subway','area','business_area','');
        $dict_fields = array('hometype','archtype','fitment','payment_type','officetype','shoptype','office_level','salemode','payment_type_rent','rent_type');

        $dict = utf82gbk(\Yaf\Registry::get('dict')->offsetGet('house')->toArray());
        //2.从house取数据
        $city_list = \App\Admin\Models\City::getInstance()->getCityList();
        foreach ($city_list as $item) {
            $site = $item['city_en'];
            //取出这个城市的配置
            $res = \App\Admin\Models\NewhouseOptions::getInstance()->getOptionsBySite($site);
            $options = array();
            foreach ($res as $option) {
                $options[$option['type']][$option['value']] = $option['id'];
            }

            $sql = "select * from ".HOUSE." where site='{$site}' and  status = 1  and ( hometype = '商铺') or hometype = '写字楼' or hometype = '商铺,写字楼' )";
            $res = \DB::select($sql);
            if (!empty($res)) {

                foreach ($res as $k=>$v) {
                    if (($v['site'] == 'sh' && $v['hid'] == 139646) ||($v['site'] == 'qd' && $v['hid'] == 132408)||($v['site'] == 'wu' && $v['hid'] == 135754)||($v['site'] == 'cq' && $v['hid'] == 60804)) {

                    } else {
                        $row = array();
                        foreach ($fields as $kk=>$vv) {
                            if (isset($v[$vv])) {
                                $row[$vv] = $v[$vv];
                            }
                        }

                        //1.处理区域
                        if (isset($options['district'][$row['district']])) {
                            $row['district'] = $options['district'][$row['district']];
                        } else {
                            $row['district'] = '';
                        }
                        //2.环线
                        if ($row['circlelocation']) {
                            if (isset($options['circlelocation'][$row['circlelocation']])) {
                                $row['circlelocation'] = $options['circlelocation'][$row['circlelocation']];
                            } else {
                                $row['circlelocation'] = '';
                            }
                        }

                        //轨道交通
                        if ($row['subway']) {
                            $tmp_arr = explode(',',$row['subway']);
                            $tmp_arr_2 = array();
                            foreach ($tmp_arr as $subway) {
                                if (isset($options['subway'][$subway])) {
                                    $tmp_arr_2[] = $options['subway'][$subway];
                                }
                            }
                            $row['subway'] = implode(',',$tmp_arr_2);
                        }

                        //商圈
                        if ($row['area']) {
                            if (isset($options['area'][$row['area']])) {
                                $row['area'] = $options['area'][$row['area']];
                            } else {
                                $row['area'] = '';
                            }
                        }

                        //商业中心
                        if ($row['business_area']) {
                            $tmp_arr = explode(',',$row['business_area']);
                            $tmp_arr_2 = array();
                            foreach ($tmp_arr as $subway) {
                                if (isset($options['business_area'][$subway])) {
                                    $tmp_arr_2[] = $options['business_area'][$subway];
                                }
                            }
                            $row['business_area'] = implode(',',$tmp_arr_2);
                        }

                        //字典项处理
                        foreach ($dict_fields as $dict_field){
                            if ($row[$dict_field]) {
                                $tmp_arr = explode(',',$row[$dict_field]);
                                $tmp_arr_2 = array();

                                foreach ($tmp_arr as $subway) {
                                    $dict_rev = array_flip($dict[$dict_field]);
                                    if (isset($dict_rev[$subway])) {
                                        $tmp_arr_2[] = $dict_rev[$subway];
                                    }
                                }
                                $row[$dict_field] = implode(',',$tmp_arr_2);
                            }
                        }

                        //salestat对应关系
                        if  ($v['salestate'] == 0||$v['salestate'] == 1 || $v['salestate'] == 5 || $v['salestate'] == 6 || $v['salestate'] == 9) {
                            $row['salestate'] = 1;
                        }
                        if ($v['salestate'] == 2 || $v['salestate'] == 7) {
                            $row['salestate'] = 2;
                        }
                        if ($v['salestate'] == 3 || $v['salestate'] == 8) {
                            $row['salestate'] = 3;
                        }
                        if ($v['salestate'] == 4 ||$v['salestate'] == 11 ) {
                            $row['salestate'] = 5;
                        }
                        if ($v['salestate'] == 10 ) {
                            $row['salestate'] = 4;
                        }


                        \DB::table(BUSINESS)->where(array('site'=>$v['site'],'hid'=>$v['hid']))->update($row);
                    }
                }
            }
        }
    }


    public function initLinkAction()
    {
        set_time_limit(0);
        ini_set('memory_limit','4000M');//1G
        
        $sql = "select cric_house.id,cric_house.building_name,cric_house.site,house.name,house.hid  from cric_house LEFT JOIN ".HOUSE." as house on cric_house.site=house.site and cric_house.building_name = house.name where house.hid != '' and house.status = 1 ";
        $res = \DB::select($sql);
        foreach ($res as $k=>$v) {
            \DB::table('cric_house')->where(array('id'=>$v['id']))->update(array('hid'=>$v['hid']));
        }
        p($res);exit;
    }


    public function priceAction()
    {

        error_reporting(E_ALL);
        //1。只填写了单价的数据 可以不做处理
        set_time_limit(0);
        ini_set('memory_limit','800M');

        //2。只填写了总价的数据 price_type 修改为2总价  price_unit 修改为2 万元/套
        $sql = "update house_price set price_type = 2,price_unit = 2 where (price_sum_min > 0 or price_sum_max >0) and (price_min = 0 and price_avg = 0 and price_max = 0)  ";
        \DB::update($sql);

        //3。单价/总价都填写了的数据 原数据将 price_sum_min,price_max置空，新增一条总价数据，设置price_sum_min,price_sum_max,price_type,price_unit

        $sql = "select * from house_price where (price_sum_min > 0 or price_sum_max >0) and (price_min > 0 or price_avg > 0 or price_max > 0)";

        $res = \DB::select($sql);
        $rows = array();
        foreach ($res as $k=>$v) {
            $row = array();
            $row['city'] = $v['city'];
            $row['hid'] = $v['hid'];
            $row['price_sum_max'] = (int)$v['price_sum_max'];
            $row['price_sum_min'] = (int)$v['price_sum_min'];
            $row['price_time'] = (int)$v['price_time'];
            $row['price_show'] = (string)$v['price_show'];
            $row['createtime'] = (int)$v['createtime'];
            $row['updatetime'] = (int)$v['updatetime'];
            $row['creator'] = (string)$v['creator'];
            $row['updator'] = (string)$v['updator'];
            $row['modify_timestamp'] = $v['modify_timestamp'];
            $row['status'] = (int)$v['status'];
            $row['itemid'] = (int)$v['itemid'];
            $row['is_criclj'] = (int)$v['is_criclj'];
            $row['HousePrice_main_id'] = (int)$v['HousePrice_main_id'];
            $row['price_hometype'] = (int)$v['price_hometype'];
            $row['price_type'] = 2;
            $row['price_sale_type'] = 1;
            $row['price_unit'] = 2;
            $rows[] = $row;
        }
        $chunk_rows = array_chunk($rows,1000);
        foreach ($chunk_rows as $insert) {
            \DB::table('house_price')->insert($insert);
        }

        $sql = "update house_price set price_sum_min = 0,price_sum_max = 0 where (price_sum_min > 0 or price_sum_max >0) and (price_min > 0 or price_avg > 0 or price_max > 0)";

        \DB::update($sql);


    }


    public function hometypeAction()
    {
        error_reporting(E_ALL);
        //1。只填写了单价的数据 可以不做处理
        set_time_limit(0);
        ini_set('memory_limit','800M');

        $sql = "select id,site,hid,hometype from house where FIND_IN_SET('6',hometype) or FIND_IN_SET('11',hometype)";
        $res = \DB::select($sql);
        //p($res);exit;
        if (!empty($res)) {
            foreach ($res as $k=>$v) {
                $tmp = explode(',',$v['hometype']);
                foreach ($tmp as $kk=>$vv) {
                    if ($vv == 6) {
                        $tmp[$kk] = 3;
                    }
                    if ($vv == 11) {
                        $tmp[$kk] = 5;
                    }
                }
                $tmp = array_unique($tmp);
                sort($tmp);
                $hometype = implode(',',$tmp);

                $sql_tmp = "update house set hometype = '{$hometype}' where id = {$v['id']}";
                \DB::update($sql_tmp);

                \App\Admin\Models\RedisDataUpdate::getInstance()->updateHouseMainByHid($v['site'],$v['hid']);
                //$res[$k] = $v;
            }
        }
        p($res);exit;
    }

}