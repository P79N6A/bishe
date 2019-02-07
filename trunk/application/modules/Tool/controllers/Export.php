<?php
use App\Tool\Controllers\Controller;
use App\Models\Picture;
use App\Models\OtherInfo;
use App\Models\City;
class ExportController extends Controller
{
    public function init() {
        //parent::init();
    }

    public function houseAction()
    {
        ini_set('memory_limit','400M');
        $file = fopen('/home/zhao/crm.csv', 'r');
        $data = array();
        $city = array();
        $house = array();

        $res  = \App\Models\City::getInstance()->getCityList();
        foreach ($res as $k=>$v) {
            $city[$v['city_cn']] = $v;
        }
        $city_site = $res;

        while ($line = fgetcsv($file)) {
            if (isset($city[$line[1]])) {
                $site = $city[$line[1]]['city_en'];
                $data[$site.'_'.$line[2]] = array();
            }

        }
        $res = \DB::select("select site,hid,name from ".HOUSE);
        $res = gbk2utf8($res);
        foreach ($res as $k=>$v) {
            $house[$v['site'].'_'.$v['name']] = $v;
            unset($v);
        }

        foreach ($data as $k=>$v) {
            if (isset($house[$k])) {
                $data[$k]['city_en'] = $house[$k]['site'];
                $data[$k]['city_code'] = $city_site[$house[$k]['site']]['city_code'];
                $data[$k]['city_cn'] = $city_site[$house[$k]['site']]['city_cn'];
                $data[$k]['hid'] = $house[$k]['hid'];
                $data[$k]['name'] = $house[$k]['name'];
            } else {
                $tmp = explode('_',$k);
                $data[$k]['city_en'] = $tmp[0];
                $data[$k]['city_code'] = $city_site[$tmp[0]]['city_code'];
                $data[$k]['city_cn'] = $city_site[$tmp[0]]['city_cn'];
                $data[$k]['hid'] = '未查询到';
                $data[$k]['name'] = $tmp[1];
            }
        }

        //开始导csv
        $str =  "city_en,city_code,city_cn,hid,name".PHP_EOL;
        foreach ($data as $v){
            $str .= "{$v['city_en']},{$v['city_code']},{$v['city_cn']},{$v['hid']},{$v['name']},".PHP_EOL;
        }
        $filename = '楼盘.csv'; //设置文件名

        header("Content-type:text/csv;charset=utf-8");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }

    public function house2Action()
    {
        ini_set('memory_limit','800M');
        $_GET['nocache'] = 1;

        $city_list = \App\Models\City::getInstance()->getCityList();


        $dict_housetype = \Yaf\Registry::get('dict')->house->salestate->toArray();


        $time = strtotime('2017-01-01');
        $str =  "城市|城市英文|楼盘名称|开发商|物业公司|销售状态|创建时间".PHP_EOL;
        $sql = "select site,hid,name,salestate,property_company,developerid,createtime from ".HOUSE." where status != -1 and createtime > {$time} order by createtime asc";
        $res = gbk2utf8(\DB::select($sql));
        foreach ($res as $k=>$v) {
            $str .= $city_list[$v['site']]['city_cn'].'|';
            $str .= "{$v['site']}|{$v['name']}|";
            $dev = \App\Models\Developer::getInstance()->getDeveloperNameBySiteId($v['site'],$v['developerid']);

            if (!empty($dev)) {
                $str .= $dev.'|';
            } else {
                $str .='|';
            }
            $str .= $v['property_company'].'|';
            $str .= $dict_housetype[$v['salestate']].'|';
            $str .= date('Y-m-d',$v['createtime']);
            $str .= PHP_EOL;

        }

        $str = utf82gbk($str);


        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }

    public function house3Action()
    {
        ini_set('memory_limit','800M');
        set_time_limit(0);
        $_GET['nocache'] = 1;

        //1.楼盘总数
        $sql = "select site,hid,name from ".HOUSE." where  site = 'tj' and salestate in (1,2,3,10) and status = 1";
        $res = \DB::select($sql);
        foreach ($res as $k=>$v) {
            $hids[] = $v['hid'];
        }

        $res2 = \DB::table(HOUSE_PRICE)->where(array('city'=>'tj','status'=>1))->whereIn('hid',$hids)->orderBy('hid','asc')->orderBy('price_time','asc')->get();
        $str = utf82gbk('city,hid,最小单价,平均单价,最大单价,最小总价,最大总价,价格时间,创建时间'.PHP_EOL);
        foreach ($res2 as $k=>$v) {
            $str .= $v['city'].','.$v['hid'].','.$v['price_min'].','.$v['price_avg'].','.$v['price_max'].','.$v['price_sum_min'].','.$v['price_sum_max'].','.date('Y-m-d H:i:s',$v['price_time']).','.date('Y-m-d H:i:s',$v['createtime']).PHP_EOL;
        }

        //p($str);exit;
        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;


    }

    public function house4Action()
    {
        ini_set('memory_limit','400M');
        set_time_limit(0);
        $_GET['nocache'] = 1;
        $file = fopen('d:/a.csv', 'r');


        $city_cn = array();
        $res  = \App\Models\City::getInstance()->getCityList();
        foreach ($res as $k=>$v) {
            $city[$v['city_cn']] = $v['city_en'];
        }

        $city['成都'] = 'sc';
        $city['南宁'] = 'gx';

        $site_hids = array();
        while ($line = fgetcsv($file)) {
            $line = gbk2utf8($line);
            if (isset($city[$line[1]])) {
                $site_hids[$city[$line[1]]][] = $line[0];
            }
        }

        $str = utf82gbk('城市,hid,名称,均价,城区,预售证,开盘时间,开发商,地址,标签,400,高德坐标x,高德坐标y').PHP_EOL;
        $fields = 'site,city_cn,hid,name,price_avg,district_name,licence,opentime,developer,address,tags_id,phone_extension,coordx2,coordy2';
        foreach ($site_hids as $site=>$hids) {
            $res = \Sphinx::type('house')->where('site','=',$site)->whereIn('hid',$hids)->limit(6000)->get($fields);
            foreach ($res['data'] as $k=>$v) {
                if (!empty($v['tags_id'])) {
                    $v['tags_id'] = \App\Models\OtherInfo::getInstance()->getTagsName($v['tags_id']);
                    $v['tags_id'] = str_replace(',','|',implode('|', $v['tags_id']));
                } else {
                    $v['tags_id'] = '暂无';
                }
                if (!empty($v['price_avg'])) {
                    $v['price_avg'] .= '元/平方米';
                } else {
                    $v['price_avg'] = '待定';
                }
                $res['data'][$k] = $v;
            }

            foreach ($res['data'] as $k=>$v) {
                $str .= utf82gbk("{$v['city_cn']},{$v['hid']},{$v['name']},{$v['price_avg']},{$v['district_name']},{$v['licence']},{$v['opentime']},{$v['developer']},{$v['address']},{$v['tags_id']},400-606-6969转{$v['phone_extension']},{$v['coordx2']},{$v['coordy2']}").PHP_EOL;
            }
        }


        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }

    public function house5Action()
    {
        ini_set('memory_limit','800M');
        ini_set('display_errors', 0);
        set_time_limit(0);
        /*$_GET['nocache'] = 1;
        $file = fopen('/home/zhao/a.csv', 'r');

        $city_cn = array();
        $res  = \App\Models\City::getInstance()->getCityList();
        foreach ($res as $k=>$v) {
            $city[$v['city_cn']] = $v['city_en'];
        }
        $city['成都'] = 'sc';
        $city['南宁'] = 'gx';

        $site_hids = array();
        while ($line = fgetcsv($file)) {
            if (isset($city[$line[0]])) {
                $site_hids[] = $city[$line[0]].'_'.$line[2];
            }
        }
        */

        //1.楼盘总数


        //2.
        $fields_total = array();
        $dbfields_conf = \Yaf\Registry::get('dbFields')->toArray();
        $dbfields_conf = $dbfields_conf['house'];
        $dbfields_conf = explode(',',$dbfields_conf);
        $ids_str = "816,2034,2148,2393,2558,2623,3832,4035,4640,4750,5203,6092,7361,7380,9648,10531,11513,11799,12132,12340,12621,14131,14940,15478,17823,19423,19985,156986,20218,20388,20723,22725,24148,24490,24618,24722,24776,157495,29165,30185,37613,38335,38750,39976,41141,41264,41549,43292,43394,43402,44144,156820,44900,44942,44967,45135,45148,45157,45351,60816,71758,71846,72083,76838,77042,84373,84704,84883,90433,90717,90925,91043,91255,91768,91769,156636,92210,92281,155790,92381,92444,92729,92767,92906,92940,93699,93973,94132,94175,94366,94652,94698,94809,94860,94865,94902,155414,95116,157149,95631,96057,96201,96529,96815,96931,97077,97229,97234,97238,97257,97435,97447,97454,98040,98106,98126,98374,98645,98858,102751,102854,103204,103364,103372,103970,104213,104459,104525,104585,104644,104931,156924,105166,105174,105404,105576,105618,105976,105977,106166,157033,107160,107331,107406,108647,109574,109586,109673,109689,110116,110236,110431,111776,111918,112013,112076,112147,112246,112370,112454,112641,112827,112896,113005,113046,113326,113394,113451,113773,113916,114097,114334,114345,114436,114819,114956,114964,115157,115159,115344,115435,115733,115994,116182,116223,116228,116292,116405,157121,116687,116763,116859,117031,117138,117236,117242,117299,117440,117517,117710,117771,117959,118206,118282,157021,118397,118473,118789,118877,119061,119145,119545,119765,119852,119896,119898,119935,120171,120246,120253,120311,120320,120420,120446,120487,120606,120689,120826,120956,121104,121246,121264,121284,121411,121510,121679,121777,121887,121928,122007,122027,122045,122080,122143,122160,122262,122272,125979,126000,126234,126275,126426,126666,126716,126859,126891,126951,126987,127072,127160,127200,127274,127312,127318,127562,127566,127603,127606,127616,127662,127701,127743,128016,128382,128391,128400,128427,128667,128679,128912,129151,129167,129249,129371,129695,129849,129852,130176,130563,155839,131289,131317,155885,131577,131626,131723,155557,132172,132213,132280,132458,132555,132706,132768,132925,133064,133091,133130,133136,133202,157945,133246,133257,156772,133399,133428,133597,133860,133886,133959,133973,134018,134066,134069,134248,134496,134578,134654,134689,134716,134780,155273,135082,135123,135137,135156,135206,135241,135262,135483,135526,135544,135606,135678,135705,135710,135778,135895,136149,136193,136296,136318,136459,136510,136536,136637,136687,136793,136889,136917,136975,137002,137015,137019,137059,137428,137496,137588,137614,137707,137736,137782,137836,137878,137951,137964,138063,138072,138104,138126,138136,138212,138334,138354,138355,138435,138449,138456,138488,138661,138735,138782,138867,138874,138907,138988,139013,139022,139467,139502,139543,139610,139736,139750,140061,140109,140671,140673,140697,140749,140765,140813,140942,141032,156885,141097,141131,141176,141181,141187,141293,141295,141304,141331,141419,141489,141541,141709,141722,141821,141854,141866,141955,141983,142123,142219,142285,142354,142382,142400,142409,142528,142550,142579,142616,142643,142785,142828,142897,142944,143034,143053,143079,143226,143265,143293,143484,143866,143898,144051,144509,144630,144752,144797,145162,145213,145219,145381,145471,145474,145767,145910,145993,146173,146206,146230,146233,146304,146468,146477,146509,146589,146635,156740,146666,146668,146672,146797,146895,146978,147041,147043,147070,147187,147202,147213,147330,147332,147339,147478,147507,147561,147585,147611,147638,147657,147681,147694,147695,147730,147734,147880,147906,147939,147953,147962,147994,148027,148028,148063,148082,148096,148113,148197,148199,148251,148469,148505,148555,148588,148594,148613,148626,148632,148668,148679,148703,148725,148726,148820,148844,148877,148895,148966,148984,149002,149004,149017,149055,149103,149125,149153,149224,149228,156175,149284,149291,149305,149349,149379,149385,149407,149468,149522,149599,149614,149634,149685,149709,149722,149735,149760,149770,149798,149800,149902,149921,149935,149951,149965,149992,150021,150175,150195,150206,150223,150261,150262,150280,150337,150370,150389,150420,150531,150569,150610,150612,150628,150742,150760,150772,150782,150786,150788,150801,150861,150870,150909,150912,150924,150938,150942,151006,151056,151095,151142,151193,151199,151226,151288,151366,151378,151387,151401,151477,151526,151529,151551,151590,151593,151669,151695,151756,151801,151828,151830,151832,151951,151953,151957,151997,152030,152033,152051,152070,152174,152307,152401,152421,152467,152475,152501,152502,152528,152546,152605,152622,152641,152651,152737,152743,152755,152759,152761,152794,152843,152869,152874,152885,152908,152919,152934,152960,152970,152972,153013,153020,153032,153045,153052,153069,153106,153107,153115,153118,153126,153175,153193,153230,153264,153372,153381,153421,153476,153490,153508,153531,153532,153540,153543,153545,153550,153556,153635,153683,153838,153859,153867,153935,153960,153974,153984,154056,154058,154065,154122,154129,154131,154136,154209,154210,154257,154265,154266,156777,154304,154307,154333,154349,154353,154356,154376,154381,154408,154412,154435,154443,154464,154468,156833,154506,154519,154531,154532,154533,154538,154539,154548,154556,154561,154562,154587,154621,154639,154647,154649,154650,154654,154672,154681,154707,154709,154731,154765,154768,154799,154821,154822,154830,154835,154841,154842,154850,154856,154869,154876,154886,154925,154943,154962,154971,154972,155036,155039,156962,155119,156179,156762,155151,155528,155830,155138,156544,155158,155163,155171,155189,155221,155236,155237,155402,155247,155260,155264,155267,155281,155301,155339,155346,155376,155410,155442,155449,155576,155631,155611,155627,155643,155646,155650,155654,155667,155795,156796,155711,155747,155774,155775,155778,155780,155786,155791,155803,156846,155844,155921,155934,156158,156488,156027,156978,156051,156075,156095,156112,156415,156181,157603,157572,156224,156226,156232,156233,156277,156297,156360,156313,156315,156322,156345,156353,156376,156381,156383,156391,156434,156448,156470,156471,156524,156768,156579,156582,156584,156597,156603,156620,156638,156727,157167,157187,157194,157289,157203,157213,157216,157259,157282,157287,157352,157361,157470,157474,157481,157524,157539,157560,157561,157584,157955,157616,157643,157674,157676,157991,157758,157763,157777,157798,157838,157843,157846,157852,157881,157905,157934,157939,157944,158066,157968,157972,157989,158028,158029,158053,158085,158090,158092,158098,158112,158122,158128,158129,158171,158176,158184,158222,158270,158572,158293,158327,158355,158378,158410,158440,158443,158448,158457,158467,158544,158574,158584,158629,158791,158910,158969,159026,159112";
        $sql = "select * from ".HOUSE." where id in ($ids_str)";
        $res = \DB::select($sql);
        $res = gbk2utf8($res);

        $fields_total = array();
        foreach ($res as $k=>$v) {
            foreach ($dbfields_conf as $field) {
                if ($v[$field] != '' || $v[$field] != 0) {
                    $fields_total[$field]++;
                }
            }
        }

        $total = count(explode(',',$ids_str));
        $str =  "字段名,有值楼盘数,楼盘总数,覆盖率".PHP_EOL;
        foreach ($fields_total as $k=>$v) {
            $per = sprintf("%0.2f", $v/$total);
            $str .= "{$k},{$v},{$total},{$per},".PHP_EOL;
        }

        header("Content-type:text/csv;charset=utf8");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }

    public function house6Action()
    {
        ini_set('memory_limit','800M');
        ini_set('display_errors', 0);
        set_time_limit(0);
        /*$_GET['nocache'] = 1;
        $file = fopen('/home/zhao/a.csv', 'r');

        $city_cn = array();
        $res  = \App\Models\City::getInstance()->getCityList();
        foreach ($res as $k=>$v) {
            $city[$v['city_cn']] = $v['city_en'];
        }
        $city['成都'] = 'sc';
        $city['南宁'] = 'gx';

        $site_hids = array();
        while ($line = fgetcsv($file)) {
            if (isset($city[$line[0]])) {
                $site_hids[] = $city[$line[0]].'_'.$line[2];
            }
        }
        */

        //1.楼盘总数


        //2.
        $fields_total = array();
        $dbfields_conf = \Yaf\Registry::get('dbFields')->toArray();
        $dbfields_conf = $dbfields_conf['house'];
        $dbfields_conf = str_replace(',','$',$dbfields_conf);
        $dbfields_conf_arr = explode('$',$dbfields_conf);

        $ids_str = "816,2034,2148,2393,2558,2623,3832,4035,4640,4750,5203,6092,7361,7380,9648,10531,11513,11799,12132,12340,12621,14131,14940,15478,17823,19423,19985,156986,20218,20388,20723,22725,24148,24490,24618,24722,24776,157495,29165,30185,37613,38335,38750,39976,41141,41264,41549,43292,43394,43402,44144,156820,44900,44942,44967,45135,45148,45157,45351,60816,71758,71846,72083,76838,77042,84373,84704,84883,90433,90717,90925,91043,91255,91768,91769,156636,92210,92281,155790,92381,92444,92729,92767,92906,92940,93699,93973,94132,94175,94366,94652,94698,94809,94860,94865,94902,155414,95116,157149,95631,96057,96201,96529,96815,96931,97077,97229,97234,97238,97257,97435,97447,97454,98040,98106,98126,98374,98645,98858,102751,102854,103204,103364,103372,103970,104213,104459,104525,104585,104644,104931,156924,105166,105174,105404,105576,105618,105976,105977,106166,157033,107160,107331,107406,108647,109574,109586,109673,109689,110116,110236,110431,111776,111918,112013,112076,112147,112246,112370,112454,112641,112827,112896,113005,113046,113326,113394,113451,113773,113916,114097,114334,114345,114436,114819,114956,114964,115157,115159,115344,115435,115733,115994,116182,116223,116228,116292,116405,157121,116687,116763,116859,117031,117138,117236,117242,117299,117440,117517,117710,117771,117959,118206,118282,157021,118397,118473,118789,118877,119061,119145,119545,119765,119852,119896,119898,119935,120171,120246,120253,120311,120320,120420,120446,120487,120606,120689,120826,120956,121104,121246,121264,121284,121411,121510,121679,121777,121887,121928,122007,122027,122045,122080,122143,122160,122262,122272,125979,126000,126234,126275,126426,126666,126716,126859,126891,126951,126987,127072,127160,127200,127274,127312,127318,127562,127566,127603,127606,127616,127662,127701,127743,128016,128382,128391,128400,128427,128667,128679,128912,129151,129167,129249,129371,129695,129849,129852,130176,130563,155839,131289,131317,155885,131577,131626,131723,155557,132172,132213,132280,132458,132555,132706,132768,132925,133064,133091,133130,133136,133202,157945,133246,133257,156772,133399,133428,133597,133860,133886,133959,133973,134018,134066,134069,134248,134496,134578,134654,134689,134716,134780,155273,135082,135123,135137,135156,135206,135241,135262,135483,135526,135544,135606,135678,135705,135710,135778,135895,136149,136193,136296,136318,136459,136510,136536,136637,136687,136793,136889,136917,136975,137002,137015,137019,137059,137428,137496,137588,137614,137707,137736,137782,137836,137878,137951,137964,138063,138072,138104,138126,138136,138212,138334,138354,138355,138435,138449,138456,138488,138661,138735,138782,138867,138874,138907,138988,139013,139022,139467,139502,139543,139610,139736,139750,140061,140109,140671,140673,140697,140749,140765,140813,140942,141032,156885,141097,141131,141176,141181,141187,141293,141295,141304,141331,141419,141489,141541,141709,141722,141821,141854,141866,141955,141983,142123,142219,142285,142354,142382,142400,142409,142528,142550,142579,142616,142643,142785,142828,142897,142944,143034,143053,143079,143226,143265,143293,143484,143866,143898,144051,144509,144630,144752,144797,145162,145213,145219,145381,145471,145474,145767,145910,145993,146173,146206,146230,146233,146304,146468,146477,146509,146589,146635,156740,146666,146668,146672,146797,146895,146978,147041,147043,147070,147187,147202,147213,147330,147332,147339,147478,147507,147561,147585,147611,147638,147657,147681,147694,147695,147730,147734,147880,147906,147939,147953,147962,147994,148027,148028,148063,148082,148096,148113,148197,148199,148251,148469,148505,148555,148588,148594,148613,148626,148632,148668,148679,148703,148725,148726,148820,148844,148877,148895,148966,148984,149002,149004,149017,149055,149103,149125,149153,149224,149228,156175,149284,149291,149305,149349,149379,149385,149407,149468,149522,149599,149614,149634,149685,149709,149722,149735,149760,149770,149798,149800,149902,149921,149935,149951,149965,149992,150021,150175,150195,150206,150223,150261,150262,150280,150337,150370,150389,150420,150531,150569,150610,150612,150628,150742,150760,150772,150782,150786,150788,150801,150861,150870,150909,150912,150924,150938,150942,151006,151056,151095,151142,151193,151199,151226,151288,151366,151378,151387,151401,151477,151526,151529,151551,151590,151593,151669,151695,151756,151801,151828,151830,151832,151951,151953,151957,151997,152030,152033,152051,152070,152174,152307,152401,152421,152467,152475,152501,152502,152528,152546,152605,152622,152641,152651,152737,152743,152755,152759,152761,152794,152843,152869,152874,152885,152908,152919,152934,152960,152970,152972,153013,153020,153032,153045,153052,153069,153106,153107,153115,153118,153126,153175,153193,153230,153264,153372,153381,153421,153476,153490,153508,153531,153532,153540,153543,153545,153550,153556,153635,153683,153838,153859,153867,153935,153960,153974,153984,154056,154058,154065,154122,154129,154131,154136,154209,154210,154257,154265,154266,156777,154304,154307,154333,154349,154353,154356,154376,154381,154408,154412,154435,154443,154464,154468,156833,154506,154519,154531,154532,154533,154538,154539,154548,154556,154561,154562,154587,154621,154639,154647,154649,154650,154654,154672,154681,154707,154709,154731,154765,154768,154799,154821,154822,154830,154835,154841,154842,154850,154856,154869,154876,154886,154925,154943,154962,154971,154972,155036,155039,156962,155119,156179,156762,155151,155528,155830,155138,156544,155158,155163,155171,155189,155221,155236,155237,155402,155247,155260,155264,155267,155281,155301,155339,155346,155376,155410,155442,155449,155576,155631,155611,155627,155643,155646,155650,155654,155667,155795,156796,155711,155747,155774,155775,155778,155780,155786,155791,155803,156846,155844,155921,155934,156158,156488,156027,156978,156051,156075,156095,156112,156415,156181,157603,157572,156224,156226,156232,156233,156277,156297,156360,156313,156315,156322,156345,156353,156376,156381,156383,156391,156434,156448,156470,156471,156524,156768,156579,156582,156584,156597,156603,156620,156638,156727,157167,157187,157194,157289,157203,157213,157216,157259,157282,157287,157352,157361,157470,157474,157481,157524,157539,157560,157561,157584,157955,157616,157643,157674,157676,157991,157758,157763,157777,157798,157838,157843,157846,157852,157881,157905,157934,157939,157944,158066,157968,157972,157989,158028,158029,158053,158085,158090,158092,158098,158112,158122,158128,158129,158171,158176,158184,158222,158270,158572,158293,158327,158355,158378,158410,158440,158443,158448,158457,158467,158544,158574,158584,158629,158791,158910,158969,159026,159112";
        $sql = "select * from ".HOUSE." where id in ($ids_str)";
        $res = \DB::select($sql);
        $res = gbk2utf8($res);

        $str =  "$dbfields_conf".PHP_EOL;
        foreach ($res as $k=>$v) {
            foreach ($dbfields_conf_arr as $field) {
                if ($v[$field]) {
                    $v[$field] = strip_tags($v[$field]);
                    $v[$field] = trim($v[$field]);
                    $v[$field] = str_replace(PHP_EOL,'',$v[$field]);
                    $v[$field] = str_replace(array("\r\n", "\r", "\n"), "", $v[$field]);
                } else {
                    $v[$field] = '空';
                }


                $str .= $v[$field].'$';
            }
            $str .= PHP_EOL;
        }

        header("Content-type:text/csv;charset=utf8");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }

    public function house7Action()
    {
        $_GET['nocache'] = 1;
        ini_set('memory_limit','800M');
        ini_set('display_errors', 0);
        set_time_limit(0);

        $keywords = [
        // 与“最”有关：
        '最佳',
        '最具',
        '最爱',
        '最赚',
        '最优秀',
        '最优',
        '最好',
        '最大程度',
        '最大',
        '最高级',
        '最高端',
        '最奢侈',
        '最高',
        '最低级',
        '最低价',
        '最底',
        '时尚最低价',
        '最低',
        '最便宜',
        '最流行',
        '最受欢迎',
        '最时尚',
        '最聚拢',
        '最符合',
        '最舒适',
        '最先进',
        '最新科学',
        '最先享受',
        '最先进科学',
        '最先进加工工艺',
        '最新技术',
        '最新',
        '最先',
        '最后',
        '最',

        // 与“一”有关：
        '中国第一',
        '唯一',
        'NO.1',
        'TOP.1',
        'TOP1',
        'top.1',
        'top1',
        '独一无二',
        '仅此一家',
        '一流',
        '一天',
        '仅此一次',
        '仅此',
        '最后一波',
        '大品牌之一',
        '全网第一',
        '第一品牌',
        '销量第一',
        '排名第一',
        '全国X大品牌之一',
        '第一',

        // 与“级/极”有关：
        '国家级产品',
        '国家级',
        '全球级',
        '宇宙级',
        '世界级',
        '顶尖',
        '顶级工艺',
        '顶级享受',
        '顶级',
        '尖端',
        '高级',
        '极品',
        '极佳',
        '绝佳',
        '绝对',
        '终极',
        '极致',
        '超甲级',
        '百万级',

        // 与“首/家/国”有关：
        '独家配方',
        '首个',
        '首选',
        '独家',
        '全国首发',
        '首发',
        '首次',
        '首款',
        '全国销量冠军',
        '国家级产品',
        '国家免检',
        '国家领导人',
        '中国驰名',
        '国际品质',

        // 与“品牌”有关：
        '大牌',
        '金牌',
        '名牌',
        '王牌',
        '世界领先',
        '遥遥领先',
        '领导者',
        '缔造者',
        '创新品牌',
        '领先上市',
        '巨星',
        '著名',
        '掌门人',
        '至尊',
        '巅峰',
        '奢侈',
        '优秀',
        '资深',
        '领袖',
        '之王',
        '王者',
        '冠军',
        '楼王',
        '地王',
        '寸土寸金',
        '绝无仅有',
        '无与伦比',
        '卓越',
        'loft',

        // 与“虚假”有关：
        '史无前例',
        '前无古人',
        '永久',
        '万能',
        '祖传',
        '特效',
        '无敌',
        '纯天然',
        '100%',
        '5A',
        '5a',
        '高档',
        '正品',
        '真皮',
        '超赚',
        '精确',
        '臻品',
        '至臻',
        '空前',
        '绝后',
        '绝版',
        '非此莫属',
        '前所未有',
        '无人能及',
        '鼎级',
        '鼎冠',
        '定鼎',
        '完美',





        // 与“权威”有关：
        '老字号',
        '中国驰名商标',
        '特供',
        '专供',
        '专家推荐',
        '质量免检',
        '无需国家质量检测',
        '免抽检',
        '领导人推荐',
        '机关推荐',

        // 涉嫌欺诈消费者的：
        '点击领奖',
        '恭喜获奖',
        '全民免单',
        '点击有惊喜',
        '点击获取',
        '点击转身',
        '点击试穿',
        '点击翻转',
        '领取奖品',

        // 涉嫌诱导消费者的：
        '秒杀',
        '抢爆',
        '再不抢就没了',
        '不会再便宜了',
        '没有他就',
        '没有她就',
        '没有它就',
        '错过就没机会了',
        '万人疯抢',
        '万人抢购',
        '全民疯抢',
        '全民抢购',
        '卖疯了',
        '抢疯了',

        // 与“时间”有关：
        '限今日',
        '倒计时',
        '趁现在',
        '仅限',
        '仅此一天',
        '随时结束',
        '随时涨价',
        '马上降价',
        '就',
        '周年庆',
        '特惠趴',
        '购物大趴',
        '闪购',
        '品牌团',
        '精品团',
        '随时结束',
        '随时涨价',
        '马上降价',

        //
        '学区房',		// 20150911
        '中心城',
        '顶级豪宅',
        '城市中心',
        '地铁上盖',



        // 模糊匹配, 要放在最后位置
        '最',
        '冠',
        '中心',
        '首席','认筹',
        '诚意登记',
        '登记优惠',
        '内部认筹',
        '电商',
        '抵',
        '劲销',
        '日光',
        '火爆',
        '投资',
        '投资回报',
        '投资前景',
        '前景',
        '潜力',
        '优惠',
        '核心',
        '升值',
        '楼王',
        '绝版',
        '售罄',
        '夜光',
        '时光',
        '首个',
        '唯一',
        '罕见',
        '豪装',
        '豪华',
        '精装',
        '精装修',
        '升值'

    ];


        /*$sql = "select site,hid,name,text from (select site,hid,name,CONCAT(nearby_commercial,nearby_hospital,nearby_park,nearby_peitao,nearby_school,nearby_traffic,nearby_view) as text  from house  where salestate in (1,2,3,10) and status = 1) as a where  ";
        foreach ($keywords as $keyword) {
            $sql .= " text like  '%{$keyword}%' or ";
        }
        $sql = rtrim($sql, '');
        $sql = rtrim($sql, 'or ');*/

        //$sql = "select site,hid,text,name from (select site,hid,name,CONCAT(nearby_commercial,nearby_hospital,nearby_park,nearby_peitao,nearby_school,nearby_traffic,nearby_view) as text from house where salestate in (1,2,3,10) and status = 1) as a where text like '%最佳%' or text like '%最具%'";
        //$sql = "select count(*) as count from house";
        $sql = "select site,hid,name,text from (select site,hid,name,CONCAT(nearby_commercial,nearby_hospital,nearby_park,nearby_peitao,nearby_school,nearby_traffic,nearby_view) as text from ".HOUSE." where salestate in (1,2,3,10) and status = 1) as a where text like '%最佳%' or text like '%最具%' or text like '%最爱%' or text like '%最赚%' or text like '%最优秀%' or text like '%最优%' or text like '%最好%' or text like '%最大程度%' or text like '%最大%' or text like '%最高级%' or text like '%最高端%' or text like '%最奢侈%' or text like '%最高%' or text like '%最低级%' or text like '%最低价%' or text like '%最底%' or text like '%时尚最低价%' or text like '%最低%' or text like '%最便宜%' or text like '%最流行%' or text like '%最受欢迎%' or text like '%最时尚%' or text like '%最聚拢%' or text like '%最符合%' or text like '%最舒适%' or text like '%最先进%' or text like '%最新科学%' or text like '%最先享受%' or text like '%最先进科学%' or text like '%最先进加工工艺%' or text like '%最新技术%' or text like '%最新%' or text like '%最先%' or text like '%最后%' or text like '%最%' or text like '%中国第一%' or text like '%唯一%' or text like '%NO.1%' or text like '%TOP.1%' or text like '%TOP1%' or text like '%top.1%' or text like '%top1%' or text like '%独一无二%' or text like '%仅此一家%' or text like '%一流%' or text like '%一天%' or text like '%仅此一次%' or text like '%仅此%' or text like '%最后一波%' or text like '%大品牌之一%' or text like '%全网第一%' or text like '%第一品牌%' or text like '%销量第一%' or text like '%排名第一%' or text like '%全国X大品牌之一%' or text like '%第一%' or text like '%国家级产品%' or text like '%国家级%' or text like '%全球级%' or text like '%宇宙级%' or text like '%世界级%' or text like '%顶尖%' or text like '%顶级工艺%' or text like '%顶级享受%' or text like '%顶级%' or text like '%尖端%' or text like '%高级%' or text like '%极品%' or text like '%极佳%' or text like '%绝佳%' or text like '%绝对%' or text like '%终极%' or text like '%极致%' or text like '%超甲级%' or text like '%百万级%' or text like '%独家配方%' or text like '%首个%' or text like '%首选%' or text like '%独家%' or text like '%全国首发%' or text like '%首发%' or text like '%首次%' or text like '%首款%' or text like '%全国销量冠军%' or text like '%国家级产品%' or text like '%国家免检%' or text like '%国家领导人%' or text like '%中国驰名%' or text like '%国际品质%' or text like '%大牌%' or text like '%金牌%' or text like '%名牌%' or text like '%王牌%' or text like '%世界领先%' or text like '%遥遥领先%' or text like '%领导者%' or text like '%缔造者%' or text like '%创新品牌%' or text like '%领先上市%' or text like '%巨星%' or text like '%著名%' or text like '%掌门人%' or text like '%至尊%' or text like '%巅峰%' or text like '%奢侈%' or text like '%优秀%' or text like '%资深%' or text like '%领袖%' or text like '%之王%' or text like '%王者%' or text like '%冠军%' or text like '%楼王%' or text like '%地王%' or text like '%寸土寸金%' or text like '%绝无仅有%' or text like '%无与伦比%' or text like '%卓越%' or text like '%loft%' or text like '%史无前例%' or text like '%前无古人%' or text like '%永久%' or text like '%万能%' or text like '%祖传%' or text like '%特效%' or text like '%无敌%' or text like '%纯天然%' or text like '%100%%' or text like '%5A%' or text like '%5a%' or text like '%高档%' or text like '%正品%' or text like '%真皮%' or text like '%超赚%' or text like '%精确%' or text like '%臻品%' or text like '%至臻%' or text like '%空前%' or text like '%绝后%' or text like '%绝版%' or text like '%非此莫属%' or text like '%前所未有%' or text like '%无人能及%' or text like '%鼎级%' or text like '%鼎冠%' or text like '%定鼎%' or text like '%完美%' or text like '%老字号%' or text like '%中国驰名商标%' or text like '%特供%' or text like '%专供%' or text like '%专家推荐%' or text like '%质量免检%' or text like '%无需国家质量检测%' or text like '%免抽检%' or text like '%领导人推荐%' or text like '%机关推荐%' or text like '%点击领奖%' or text like '%恭喜获奖%' or text like '%全民免单%' or text like '%点击有惊喜%' or text like '%点击获取%' or text like '%点击转身%' or text like '%点击试穿%' or text like '%点击翻转%' or text like '%领取奖品%' or text like '%秒杀%' or text like '%抢爆%' or text like '%再不抢就没了%' or text like '%不会再便宜了%' or text like '%没有他就%' or text like '%没有她就%' or text like '%没有它就%' or text like '%错过就没机会了%' or text like '%万人疯抢%' or text like '%万人抢购%' or text like '%全民疯抢%' or text like '%全民抢购%' or text like '%卖疯了%' or text like '%抢疯了%' or text like '%限今日%' or text like '%倒计时%' or text like '%趁现在%' or text like '%仅限%' or text like '%仅此一天%' or text like '%随时结束%' or text like '%随时涨价%' or text like '%马上降价%' or text like '%就%' or text like '%周年庆%' or text like '%特惠趴%' or text like '%购物大趴%' or text like '%闪购%' or text like '%品牌团%' or text like '%精品团%' or text like '%随时结束%' or text like '%随时涨价%' or text like '%马上降价%' or text like '%学区房%' or text like '%中心城%' or text like '%顶级豪宅%' or text like '%城市中心%' or text like '%地铁上盖%' or text like '%最%' or text like '%冠%' or text like '%中心%' or text like '%首席%' or text like '%认筹%' or text like '%诚意登记%' or text like '%登记优惠%' or text like '%内部认筹%' or text like '%电商%' or text like '%抵%' or text like '%劲销%' or text like '%日光%' or text like '%火爆%' or text like '%投资%' or text like '%投资回报%' or text like '%投资前景%' or text like '%前景%' or text like '%潜力%' or text like '%优惠%' or text like '%核心%' or text like '%升值%' or text like '%楼王%' or text like '%绝版%' or text like '%售罄%' or text like '%夜光%' or text like '%时光%' or text like '%首个%' or text like '%唯一%' or text like '%罕见%' or text like '%豪装%' or text like '%豪华%' or text like '%精装%' or text like '%精装修%' or text like '%升值%'";
        //$sql = "select count(*) as count from house";
        $sql = utf82gbk($sql);
        $res = \DB::select($sql);
        $res = gbk2utf8($res);

        $city_list = \App\Models\City::getInstance()->getCityList();
        $str = '';
        foreach ($res as $k=>$v) {
            $text = strip_tags($v['text']);
            $text = trim($text);
            $text = str_replace(PHP_EOL,'',$text);
            $text = str_replace(array("\r\n", "\r", "\n"), "", $text);
            $str .= "{$city_list[$v['site']]['city_cn']}|{$v['name']}|{$text}".PHP_EOL;
        }

        header("Content-type:text/csv;charset=utf8");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }


    public function house9Action()
    {
        $sql = "select site,hid,name,id from ".HOUSE." where status = 1";
        $res = \DB::select($sql);

        $city_list = \App\Models\City::getInstance()->getCityList();
        $str = 'id,city_en,hid,name,city_cn';
        foreach ($res as $k=>$v) {
            $str .= "{}";
        }

        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }

    public function house10Action()
    {
        set_time_limit(0);
        $res  = \App\Models\City::getInstance()->getCityList();
        foreach ($res as $k=>$v) {
            $city[$v['city_en']] = $v['city_cn'];
        }

        $limit = 36;
        $all = array();
        for ($i = 1;$i<=35;$i++) {
            $res = \Sphinx::type('house')->where('status','=',1)->where('salestate','=','1|2|3|10')->forPage($i,1000)->get('site,hid,name,price_display');
            $all = array_merge($all,$res['data']);
        }

        $str = '';
        foreach ($all as $k=>$v) {
            if ($v['price_display'] == '待定' && isset($city[$v['site']])) {
                $str .= utf82gbk($city[$v['site']]).','.$v['hid'].','.utf82gbk($v['name']).','.utf82gbk($v['price_display']).PHP_EOL;
            }
        }

        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;

        return false;
    }


    public function house11Action()
    {
        ini_set('memory_limit','400M');
        $sql = "select site,hid,status,name from ".HOUSE." ";

        $res  = \App\Models\City::getInstance()->getCityList();
        foreach ($res as $k=>$v) {
            $city[$v['city_en']] = $v['city_code'];
        }

        $house = \DB::select($sql);
        $str = "site,hid,name,url,desc".PHP_EOL;
        foreach ($house as $k=>$v) {
            $str .= "{$v['site']},{$v['hid']},{$v['name']},";
            if (isset($city[$v['site']])) {
                $url = ITEM_URL."/{$city[$v['site']]}{$v['hid']}/";
            } else {
                $url = ITEM_URL."/{$v['hid']}/";
            }

            $str .= $url.',';
            $desc = '';
            if ($v['status'] == -1) {
                $desc .= utf82gbk('楼盘已经删除|');
            }
            if (!isset($city[$v['site']])) {
                $desc .= utf82gbk("城市已经删除|");
            }
            if (empty($v['hid'])) {
                $desc .= utf82gbk("hid为空");
            }
            $str .= $desc.PHP_EOL;
        }

        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;

        return false;
    }


    public function house12Action()
    {
        ini_set('memory_limit','800M');
        set_time_limit(0);
        $flag = input('get.flag',0);
        $count = 2000;
        $page = $flag*$count;
        $sql = "select site,hid,status,name,district,area,tags_id,hometype,archtype,fitment,opentime,delivertime,salestate,address,property_duration,developer,property_company,subway,nearby_commercial,nearby_view,nearby_traffic,nearby_park,nearby_hospital,nearby_school from ".HOUSE." where site = 'bj' and status =1  order by id asc limit ${page},{$count}";

        $house = \DB::select($sql);
        $house = gbk2utf8($house);
        $dict_housetype = \Yaf\Registry::get('dict')->pic->housetype->toArray();
        $dict_salestate = \Yaf\Registry::get('dict')->house->salestate->toArray();
        if (!empty($_GET['test'])) {
            p($house);exit;
        }
        
        $str = '';
        foreach ($house as $k=>$v) {
            foreach ($v as $key => $value) {
                $v[$key] = strip_tags($value);
                $v[$key] = trim($value);
                $v[$key] = str_replace(PHP_EOL,'',$value);
                $v[$key] = str_replace(array("\r\n", "\r", "\n"), "", $value);
            }
            $str .= "{$v['name']}\t";
            $str .= "{$v['district']}\t";
            $str .= "{$v['area']}\t";
            $str .= "{$v['hometype']}\t";
            $str .= "{$v['archtype']}\t";
            $str .= "{$v['fitment']}\t";
            $str .= "{$v['opentime']}\t";
            $str .= "{$v['delivertime']}\t";
            $v['salestate'] = $dict_salestate[$v['salestate']];
            $str .= "{$v['salestate']}\t";
            $str .= "{$v['address']}\t";
            $str .= "{$v['property_duration']}\t";
            $str .= "{$v['developer']}\t";
            $str .= "{$v['property_company']}\t";
            $str .= "{$v['subway']}\t";
            $str .="{$v['nearby_commercial']}\t";
            $str .= "{$v['nearby_view']}\t";
            $str .= "{$v['nearby_traffic']}\t";
            $str .= "{$v['nearby_park']}\t";
            $str .= "{$v['nearby_hospital']}\t";
            $str .= "{$v['nearby_school']}\t";
            if (!empty($v['tags_id'])) {
                $v['tags_id'] = \App\Models\OtherInfo::getInstance()->getTagsName($v['tags_id']);
                $v['tags_id'] = implode(',', $v['tags_id']);
            } else {
                $v['tags_id']='';
            }

            $str .= "{$v['tags_id']}\t";
            $price_display = \App\Models\HousePrice::getInstance()->getPriceDisplay($v['site'], $v['hid']);
            $str .= "{$price_display['price_display']}\t";
            $pic_housetype = Picture::getInstance()->getPicList($v['site'], $v['hid'], Picture::HOUSETYPE);
            $housetype_str = '';
            $housetype_area = '';

            foreach ($pic_housetype as $k => $v) {
                if (!empty($v)) {
                     $pic_txt = Picture::getInstance()->getRoomParlorToiletDesc($v);
                }
                if (empty($pic_txt)) {
                    $pic_txt = $dict_housetype[$v['housetype']];
                }
                if (!empty($pic_txt)) {
                    $housetype_str.=$pic_txt.'|';
                    $housetype_area .= $v['area'].'|';
                 } 

            }

            $str .= "{$housetype_str}\t{$housetype_area}";
            $str .= PHP_EOL;
        }
        // echo ($str);exit;
        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=datahouse_fid.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        // header("Content-type:text/csv;charset=gbk");
        // header("Content-Disposition:attachment;filename=house.csv");
        // header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        // header('Expires:0');
        // header('Pragma:public');
        echo $str;exit;
        return false;
    }


    public function house13Action()
    {
        ini_set('memory_limit','800M');
        set_time_limit(0);
        $flag = input('get.flag',0);
        $count = 2000;
        $page = $flag*$count;
        $sql = "select site,hid,status,name,district,area,tags_id,hometype,archtype,fitment,opentime,delivertime,salestate,address,property_duration from ".HOUSE." where site = 'sh' and status =1 and salestate in (1,2,3,10) order by id asc limit ${page},{$count}";

        $house = \DB::select($sql);
        $house = gbk2utf8($house);
        $dict_housetype = \Yaf\Registry::get('dict')->pic->housetype->toArray();
        $dict_salestate = \Yaf\Registry::get('dict')->house->salestate->toArray();
        if (!empty($_GET['test'])) {
            p($house);exit;
        }
        $need_show_tab_pictypeids = array(Picture::TRAFFIC, Picture::COMMUNITY, Picture::SHOWROOM, Picture::EFFECT, Picture::AROUND, Picture::HOUSERESOURCE);
        $str ='';
        
        $str = "楼盘名称\t区域\t住宅物业类型\t建筑类型\t装修\t销售状态\t地址\t产权\t标签\t价格\t楼盘封面\t位置交通\t社区实景\t样板间\t效果图\t周边配套\t楼层平面图\t户型图片\t户型信息\t面积\t";
        $str .= PHP_EOL;
        foreach ($house as $k=>$v) {
            foreach ($v as $key => $value) {
                $v[$key] = strip_tags($value);
                $v[$key] = trim($value);
                $v[$key] = str_replace(PHP_EOL,'',$value);
                $v[$key] = str_replace(array("\r\n", "\r", "\n"), "", $value);
                $v[$key] = str_replace(array(","), "|", $value);
            }
            $str .= "{$v['name']}\t";
            $str .= "{$v['district']}\t";
            $str .= "{$v['hometype']}\t";
            $str .= "{$v['archtype']}\t";
            $str .= "{$v['fitment']}\t";
            $v['salestate'] = $dict_salestate[$v['salestate']];
            $str .= "{$v['salestate']}\t";
            $str .= "{$v['address']}\t";
            $str .= "{$v['property_duration']}\t";
            
            if (!empty($v['tags_id'])) {

                $v['tags_id'] = \App\Models\OtherInfo::getInstance()->getTagsName($v['tags_id']);
                $v['tags_id'] = implode('|', $v['tags_id']);

            } else {
                $v['tags_id']='';
            }

            $str .= "{$v['tags_id']}\t";
            $price_display = \App\Models\HousePrice::getInstance()->getPriceDisplay($v['site'], $v['hid']);
            $str .= "{$price_display['price_display']}\t";

            $pic_cover = Picture::getInstance()->getHouseCover($v['site'], $v['hid'],'800*600');
            $str .= "{$pic_cover['pic_s800']}\t";

            $pic_covers = Picture::getInstance()->getPicListCover($v['site'], $v['hid']);
            $pic_covers = pic_op($pic_covers, '800*600');
            foreach ($need_show_tab_pictypeids as $key => $pictypeids) {
                if (!empty($pic_covers[$pictypeids])) {
                    $str .= $pic_covers[$pictypeids]['pic_s800']."\t";
                }else{
                    $str .= "\t";
                }
               
            }
            

            $pic_housetype = Picture::getInstance()->getPicList($v['site'], $v['hid'], Picture::HOUSETYPE);

            $main_housetype_arr = array();
            $housetype_arr = array();
            $pic_housetype = pic_op($pic_housetype, '800*600');
            foreach ($pic_housetype as $k => $v) {

                if ($v['sale_state'] !=1) {
                    $pic_txt = Picture::getInstance()->getRoomParlorToiletDesc($v);
                    
                    if ($v['main_housetype'] == 1) {

                        $main_housetype_arr[$k]['url'] = $v['pic_s800'];
                        $main_housetype_arr[$k]['pic_txt'] = $pic_txt;
                        $main_housetype_arr[$k]['area'] = $v['area'];
                     } else {
                        $housetype_arr[$k]['url'] = $v['pic_s800'];
                        $housetype_arr[$k]['pic_txt'] = $pic_txt;
                        $housetype_arr[$k]['area'] = $v['area'];
                    }
               }
                
            }
            $housetype_arr = array_merge($main_housetype_arr, $housetype_arr);
            $housetype_url = '';
            $housetype_str = '';
            $housetype_area = '';
            $n=0;
            foreach ($housetype_arr as $kk => $vv) {
                if ($n>=3) {
                    break;
                }
                $housetype_url .= $vv['url']."|";
                $housetype_str .= $vv['pic_txt']."|";
                $housetype_area.= $vv['area']."|";
                $n++;
            }

            $str .= "{$housetype_url}\t";

            $str .= "{$housetype_str}\t{$housetype_area}\t";

            $str .= PHP_EOL;
        }

        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=datahouse_fid.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $str;exit;
        return false;
    }

    private function filterData($str) {
        $text = strip_tags($str);
        $text = trim($text);
        $text = str_replace(PHP_EOL,'',$text);
        $text = str_replace(array("\r\n", "\r", "\n", "\t"), "", $text);
        return $text;
    }

    public function house14Action()
    {
        $page = $_GET['page'];
        $limit = 50000;
        $page_start = ($page-1)*$limit;
        ini_set('memory_limit','800M');
        set_time_limit(0);
        $flag = input('get.flag',0);
        $sql = "select city.city_cn,house.site,house.hid,house.name,house.district,house.price_avg,house.archtype,house.archtype2,house.hometype,house.hometype2,house.fitment,house.tags_id from ".HOUSE." as house INNER join ".CITY." as city on house.site=city.city_en order by house.site limit $page_start ,$limit";
        

        $house = \DB::select($sql);

        $house = gbk2utf8($house);

        $tags_name = '';
        $str = '';
        foreach($house as $key=>$value) {
            $tags_name = '';
            if(!empty($value['tags_id'])) {
               $tags_name = OtherInfo::getInstance()->getTagsName($value['tags_id']);
               $tags_name = implode(',',$tags_name);
               $data[$key]['tags_name'] = $tags_name;
            }

            $value['city_cn'] = $this->filterData($value['city_cn']);
            $value['site'] = $this->filterData($value['site']);
            $value['hid'] = $this->filterData($value['hid']);
            $value['name'] = $this->filterData($value['name']);
            $value['district'] = $this->filterData($value['district']);
            $value['price_avg'] = $this->filterData($value['price_avg']);
            $value['archtype'] = $this->filterData($value['archtype']);
            $value['hometype'] = $this->filterData($value['hometype']);
           
           $value['fitment'] = $this->filterData($value['fitment']);

            $str.=$value['city_cn']."\t".$value['site']."\t".$value['hid']."\t".$value['name']."\t".$value['district']."\t".$value['price_avg']."\t".$value['archtype']."\t".$value['hometype']."\t".$value['fitment']."\t".$tags_name.PHP_EOL;
            
        }
        
        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=datahouse_fid.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $str;exit;
        
        return false;

        /*$filename = 'house.txt';
        header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header("Pragma: no-cache" );
        header("Expires: 0" ); 
        exit($str);*/

    }

    public function tongjiAction()
    {
        $fields = 'id,site,hid,name,district,delivertime,opentime,updatetime,createtime,salestate,clicks,status,price_avg,price_sum,district_name,clicks,fitment,hometag,payment_monthly';

        $res_sphinx = \Sphinx::type('house')->where('salestate','=','1|2|3|10|11')->where('status','=','1')->where('price_avg','!=','')->limit(1)->get($fields);
        $res_sphinx2 = \Sphinx::type('house')->where('salestate','=','1|2|3|10|11')->where('status','=','1')->where('price_sum','!=','')->limit(1)->get($fields);
        p($res_sphinx);
        p($res_sphinx2);exit;
    }

    public function list1Action()
    {
        $city_list = \App\Admin\Models\City::getInstance()->getCityList();
        $str = '';
        foreach ($city_list as $k=>$v) {
            $city_code = \App\Admin\Models\City::getInstance()->site2CityCode($k);
            $str .= ITEM_URL.'/'.$city_code.'/search/'.'|'.$v['city_cn'].PHP_EOL;
        }

        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=datahouse_fid.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $str;exit;

        return false;
    }

    public function list2Action()
    {
        $sql = "select id,city,type,status,value from ".HOUSE_OPTIONS." where type = 'district' and status = 1";
        $res = \DB::select($sql);

        $city_list = \App\Admin\Models\City::getInstance()->getCityList();
        $city_list = utf82gbk($city_list);
        $str = '';
        foreach ($res as $k=>$v) {
            $city_cn = $city_list[$v['city']]['city_cn'];
            $city_code = \App\Admin\Models\City::getInstance()->site2CityCode($v['city']);
            $str .= ITEM_URL.'/'.$city_code.'/search/a'.$v['id'].'.html'.'|'.$v['value'].'|'.$city_cn.PHP_EOL;
        }

        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=datahouse_fid.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $str;exit;

        return false;
    }

    public function list3Action()
    {
        ini_set('memory_limit','800M');
        set_time_limit(0);

        $data = array();
        //1.获取富媒体视频
        $res = utf82gbk(\Sphinx\Searcher::getInstance()->type('fmt')->limit(6000)->get('city_en,house_h_id'));
        foreach ($res['data'] as $k=>$v) {
            if (!isset($data[$v['city_en']][$v['house_h_id']]['fmt_count'])) {
                $data[$v['city_en']][$v['house_h_id']]['fmt_count'] = 1;
            } else {
                $data[$v['city_en']][$v['house_h_id']]['fmt_count']++;
            }
        }

        //获取楼盘直播live
        $res = utf82gbk(\Sphinx\Searcher::getInstance()->type('live')->limit(6000)->get('house_city,house_h_id'));
        foreach ($res['data'] as $k=>$v) {
            if (!isset($data[$v['house_city']][$v['house_h_id']]['live_count'])) {
                $data[$v['house_city']][$v['house_h_id']]['live_count'] = 1;
            } else {
                $data[$v['house_city']][$v['house_h_id']]['live_count']++;
            }
        }

        //获取楼盘视频
        $res = \DB::table(HOUSE_VIDEO)->where(array('status'=>1))->where('video_createtime','>',0)->get(array('city','hid'));
        foreach ($res as $k=>$v) {
            if (!isset($data[$v['city']][$v['hid']]['video_count'])) {
                $data[$v['city']][$v['hid']]['video_count'] = 1;
            } else {
                $data[$v['city']][$v['hid']]['video_count']++;
            }
        }


        //获取城市列表
        $city = array();
        $res  = utf82gbk(\App\Models\City::getInstance()->getDirectCityList());
        foreach ($res as $k=>$v) {
            $city[$v['city_en']] = $v['city_cn'];
        }

        //获取楼盘列表
        $house = array();
        $conf = utf82gbk(\Yaf\Registry::get('dict')->offsetGet('house')->offsetGet('house_level')->toArray());
        $res = \DB::table(HOUSE)->get(array('site','hid','name','house_level'));
        foreach ($res as $k=>$v) {
            $house[$v['site']][$v['hid']]['name'] = str_replace(',','',$v['name']);
            $house[$v['site']][$v['hid']]['house_level'] = $conf[$v['house_level']];
        }


        $str = utf82gbk('城市,楼盘名称,hid,楼盘等级,中国好楼盘数量,乐居直播数量,新浪视频数量').PHP_EOL;
        foreach ($data as $k=>$v) {
            if (isset($city[$k])) {
                foreach ($v as $kk=>$vv) {

                    if (!isset($vv['fmt_count'])) {
                        $vv['fmt_count'] = 0 ;
                    }
                    if (!isset($vv['video_count'])) {
                        $vv['video_count'] = 0 ;
                    }
                    if (!isset($vv['live_count'])) {
                        $vv['live_count'] = 0 ;
                    }
                    $str .= "{$city[$k]},{$house[$k][$kk]['name']},{$kk},{$house[$k][$kk]['house_level']},{$vv['fmt_count']},{$vv['live_count']},{$vv['video_count']}".PHP_EOL;
                }
            }
        }


        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=datahouse_fid.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $str;exit;

        return false;
    }


    /*美团点评数据*/
    public function houseLsitMeiTuanAction()
    {
        ini_set('memory_limit','800M');
        set_time_limit(0);
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        $start_key=input('start_key',0);
        $end_key=input('end_key',56);
        $data = array();
        //获取城市列表
        $city = array();
        $res  = \App\Models\City::getInstance()->getDirectCityList();
        $i = 0;
        foreach ($res as $k=>$v) {
            $city[$v['city_en']] = utf82gbk($v['city_cn']);
            if($i>=$start_key && $i<=$end_key){
                $site_arr[] = $v['city_en'];
            }
            $i ++;            
        }
        // p($site_arr);exit;
        //获取楼盘列表
        $house = array();
        $data = \DB::table(HOUSE)->whereIn('site',$site_arr)->where(array('status'=>1))->whereIn('salestate', array(1,2,3,10))->get(array('id','site','hid','name','district','hometype','archtype','coordx2','coordy2','saleaddress','phone_extension','address','salephone','salestate'));

        // $str = utf82gbk('id,唯一索引,城市,楼盘名称,住宅物业类型,建筑类型,装修,销售状态,地址,产权,标签,价格,楼盘封面,位置交通,社区实景,样板间,效果图,周边配套,楼层平面图,户型图片1,户型图片2,户型图片3,户型信息1,户型信息2,户型信息3,面积1,面积2,面积3').PHP_EOL;
        $str = utf82gbk('id,唯一索引,城市,楼盘名称,经度,维度,地址,电话').PHP_EOL;
        foreach ($data as $k=>$v) {
            $phone = App\Models\House::getInstance()->getPhoneArr($v);
            $phone = utf82gbk($phone);
            $v['address'] =str_replace(",","-", $v['address']);
            $str .= "{$v['id']},{$v['site']}{$v['hid']},{$city[$v['site']]},{$v['name']},{$v['coordx2']},{$v['coordy2']},{$v['address']},{$phone['phone_text']}".PHP_EOL;            
        }
        //p($str);exit;
        header('Content-Type: text/x-csv');
        header("Content-Disposition: attachment; filename=datahouse_fid-{$start_key}-{$end_key}-.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $str;exit;

        return false;
    }



    /*----------------------------根据excel开发商导出楼盘-------------------------------------------------------*/
    /**
     * [insertNewDeveloperAction description]
     * @Author   zlc
     * @DateTime 2017-10-10
     * @return   [type]     [description]
     */
    public function exporHouseInfoExcelAction()
    {
        ini_set('memory_limit','400M');
        set_time_limit(7200);//两小时
        
        if (empty($_FILES)) {

            echo '<form action="" method="post" id="form_pc_widely" enctype="multipart/form-data">
                    <input type="file" name="developer" i accept=".xls,.xlsx" style="padding-top: 8px;">
                    <input type="text" name="num" value="0-57" class="ty_btn_confirm" >
                    <input type="submit" name="submit" value="导入" class="ty_btn_confirm" >
                 </form>';
                 exit;
        }  
       // exit('function is closed');       
         $data = $this->getExcelInfo($_FILES['developer']['tmp_name']);
         //$data = $this->getExcelInfo('10311.xlsx');
         $cities = \App\Models\City::getInstance()->getDirectCityList();
         // $house_model = \App\Models\House::getInstance();
        // p(count($cities));
        $num = input('post.num');//一次性导出的城市
        $num_arr = explode('-',$num);
        $m = 0; //第一个城市位置
        $offset= 0;//偏移量      
       // p($num_arr);exit;  
        foreach ($cities as $city) {
            $m++;            
            if($m<$num_arr[0]){
                continue;  
            }
            $offset++;
            if($offset >$num_arr[1]){
                break;
            }
            $i=0;   
            foreach ($data as $key => $value) {
                $data_develop_short[$city['city_en']][$i]['developer'] = $value[0];
                $data_develop_short[$city['city_en']][$i]['city_cn'] = $city['city_cn'];
                $data_develop_short[$city['city_en']][$i]['city_en'] = $city['city_en'];
                $i++;
            }
         }
         //p(count($data_develop_short));exit;  
         
         foreach ($data_develop_short as $key => $city_developer) {

            $developer = \DB::table('developer')->where(array('site'=>$key))->where(array('status'=>0))->get();
            //$developer = gbk2utf8($developer);
            $house_arr = \DB::table(HOUSE)
             ->where(array('site'=>$key))->where(array('status'=>1))->whereIn('salestate', array(1,2,3,10))
             ->get(array('hid','name','salestate','developer','developerid','site','house_level'));

            foreach ($city_developer as $kk => $value) {
                //2.匹配出关联的开发商
                foreach ($developer as $k => $v) {
                    $v['name'] = gbk2utf8($v['name']);
                    $status = strpos($v['name'], $value['developer']);
                    if (strpos($v['name'], $value['developer']) !== false) {
                        $data_develop_info[$key][$v['id']]['developerid'] = $v['id'];
                        //$data_develop_info[$key][$v['id']]['name'] = $v['name'];
                        $data_develop_info[$key][$v['id']]['short'] = $value['developer'];
                        $data_develop_info[$key][$v['id']]['city_en'] = $value['city_en'];
                        $data_develop_info[$key][$v['id']]['city_cn'] = $value['city_cn'];
                        $data_develop_id[$key][] = $v['id'];
                    }
                }

                //1.匹配出house name的楼盘
                foreach ($house_arr as  $info) {
                    $info = gbk2utf8($info);
                    if (strpos($info['name'], $value['developer']) !== false ) {
                        $data_develop_excel[$info['site']][$info['hid']]['name'] = $info['name'];
                        $data_develop_excel[$info['site']][$info['hid']]['site'] = $info['site'];
                        $data_develop_excel[$info['site']][$info['hid']]['hid'] = $info['hid'];
                        $data_develop_excel[$info['site']][$info['hid']]['salestate'] = $info['salestate'];
                        $data_develop_excel[$info['site']][$info['hid']]['developer'] = $info['developer'];
                        $data_develop_excel[$info['site']][$info['hid']]['developerid'] = $info['developerid'];
                        $data_develop_excel[$info['site']][$info['hid']]['house_level'] = $info['house_level'];
                        $data_develop_excel[$info['site']][$info['hid']]['short'] = $value['developer'];
                        $data_develop_excel[$info['site']][$info['hid']]['city_cn'] = $value['city_cn'];
                    }
                }
            }
         }

        // echo "--------------------------------------------------------------------------------";
         foreach ($data_develop_info as $kkk => $developer_detil) {
             $house = \DB::table(HOUSE)
             ->where(array('site'=>$kkk))->where(array('status'=>1))->whereIn('salestate', array(1,2,3,10))
             ->whereIn('developerid',$data_develop_id[$kkk])
             ->get(array('hid','name','salestate','developerid','developer','site','house_level'));

             foreach ($house as $house_key => $hosue_info) {
                if (empty($data_develop_excel[$hosue_info['site']][$hosue_info['hid']])) {
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['name'] = gbk2utf8($hosue_info['name']);
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['site'] = $hosue_info['site'];
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['hid'] = $hosue_info['hid'];
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['salestate'] = $hosue_info['salestate'];
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['developer'] = gbk2utf8($hosue_info['developer']);
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['developerid'] = $hosue_info['developerid'];
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['house_level'] = $hosue_info['house_level'];
                
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['short'] = $developer_detil[$hosue_info['developerid']]['short'];
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['city_cn'] = $developer_detil[$hosue_info['developerid']]['city_cn'];
                }
             }
         }
        $this->exportData('developer_house',array('开发商简称','城市','楼盘名称','site','hid','salestate','开发商全称','developerid'),$data_develop_excel);
    }


    /**
     * [getExcelInfo 获取excel信息]
     * @Author   zlc
     * @DateTime 2017-10-10
     * @return   [type]           [description]
     */
    private function getExcelInfo($name)
    {
        $objPHPExcel = PHPExcel_IOFactory::load($name);
        $excel_info = $objPHPExcel->getActiveSheet()->toArray();
        return $excel_info;
    }


    private function exportData($name, $title, $data)
    {
        $name = utf82gbk($name);
        $title = utf82gbk($title);
        $data = utf82gbk($data);
        $str = '';
        //输出表格头部
        foreach ($title as $v) {
            $str .= "{$v},";
        }
        $str .= PHP_EOL;

        if ($data) {
            foreach ($data as $v) {
                foreach ($v as $vv) {
                            $str .= "{$vv['short']},{$vv['city_cn']},{$vv['name']},{$vv['site']},{$vv['hid']},{$vv['salestate']},{$vv['developer']},{$vv['developerid']},{$vv['house_level']}";
                            // $str .= $vv['short'] . "\t". $vv['city_cn']."\t".$vv['name']."\t".$name;
                            $str .= PHP_EOL;
                    }

                }
        }
        
        $filename = $name . '.csv'; //设置文件名
        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
        exit;
    }


    public function picsAction()
    {
        $down = input('get.down');
        ini_set('memory_limit','400M');
        set_time_limit(7200);//两小时

        $start = strtotime('2014-09-01');
        $end = strtotime('2017-12-20')+24*3600;
        $sql = "select pictures.seq,pictures.site,pictures.hid,pictures.pic_url,pictures.area,house.name from ".PICTURES." as pictures left join ".HOUSE." on pictures.site=house.site and pictures.hid = house.hid where pictures.site = 'sc' and pictures.pictypeid = 3 and pictures.status =1 and pictures.createtime > {$start} and pictures.createtime < {$end} ";
        $res = \DB::select($sql);
        $res = pic_op($res,'ori',array('watermark'=>0,'cut_type'=>1));
        $res2 = array_chunk($res,50);
        if ($down) {
            foreach ($res2 as $k=>$v) {

                $tmp_arr = array();
                foreach ($v as $kk=>$vv) {
                    $arr = explode('.',$vv['pic_ori']);
                    $name = '/Users/zhao/Pictures/pic2/'.$vv['seq'].'.'.array_pop($arr);
                    $tmp_arr[$name] = $vv['pic_ori'];
                }

                $contens = curl_multi($tmp_arr, 600);

                foreach ($contens as $name=>$content) {
                    $fp = fopen("$name",'w');
                    fwrite($fp, $content);
                    fclose($fp);
                }


            }
            return false;
        } else{
            $str = '';
            $filename = 'pic.csv';
            foreach ($res as $k=>$v) {
                $str .= "{$v['name']},{$v['pic_ori']},{$v['area']},{$v['seq']}".PHP_EOL;
            }

            header("Content-type:text/csv;charset=gbk");
            header("Content-Disposition:attachment;filename=" . $filename);
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');
            echo $str;
            exit;
        }


    }


    public function house20Action()
    {
        ini_set('memory_limit','400M');

        $dict = \Yaf\Registry::get('dict')->toArray();
        $keyword = input('get.keyword');
        if(empty($keyword)) {
            echo '请输入关键字';exit;
        }

        $sql = "select house.id,house.site,house.hid,house.salestate,house.status,house.name as hname,developer.name as dname from house left join developer on house.developerid = developer.id where (house.name like '%{$keyword}%' or developer.name like '%{$keyword}%') and house.status = 1 ";

        $res  = \App\Models\City::getInstance()->getCityList();
        foreach ($res as $k=>$v) {
            $city[$v['city_en']] = $v['city_cn'];
        }

        $house = \DB::select($sql);
        $str = "id,城市,hid,楼盘名称,开发商名称,销售状态,有效性".PHP_EOL;
        foreach ($house as $k=>$v) {
            $str .= $v['id'].',';
            if (isset($city[$v['site']])) {
                $str .= $city[$v['site']].',';
            } else {
                $str .= $v['site'].",";
            }
            $str .= "{$v['hid']},{$v['hname']},{$v['dname']},";
            $str .= $dict['house']['salestate'][$v['salestate']].',';
            if ($v['status'] == 1) {
                $str .='正常';
            } else {
                $str .= '删除';
            }
            $str .= PHP_EOL;

        }

        $str = utf82gbk($str);
        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;

        return false;
    }


    public function house21Action()
    {
        $keyword = '恒大';
        $sql = "select house.id,house.site,house.hid,house.salestate,house.status,house.name as hname,developer.name as dname from house left join developer on house.developerid = developer.id where house.site in ('bj','sjz','tj') and ( house.name like '%{$keyword}%' or developer.name like '%{$keyword}%')  order by house.site asc ";
        $house = \DB::select($sql);

        $str = 'site,hid,最小单价,均价,最大单价,最小总价,最大总价,更新时间'.PHP_EOL;
        foreach ($house as $k=>$v) {
            $price = \App\Models\HousePrice::getInstance()->getLastPrice($v['site'],$v['hid']);
            $str .= "{$v['site']},{$v['hid']},";
            if (!empty($price)) {
                $price['updatetime'] = date('Y-m-d H:i:s',$price['updatetime']);
                $str .= "{$price['price_min']},{$price['price_avg']},{$price['price_max']},{$price['price_sum_min']},{$price['price_sum_max']},{$price['updatetime']}";
            }

            $str .= PHP_EOL;

        }

        header("Content-type:text/csv;charset=utf8");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;

        return false;

    }

    public function house22Action()
    {
        $sql = "select city,type,name from house_options where status = 1 and name != '' and (type = 'subway') ";
        $house = \DB::select($sql);

        $city_list = City::getInstance()->getCityList();
        $str = 'site,城市,type,值'.PHP_EOL;
        foreach ($house as $k=>$v) {

            $str .= "{$v['city']},{$city_list[$v['city']]['city_cn']},{$v['type']},{$v['name']}";


            $str .= PHP_EOL;

        }

        header("Content-type:text/csv;charset=utf8");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;

        return false;
    }

    public function recover400Action()
    {
        ini_set('memory_limit','400M');
        set_time_limit(7200);//两小时

        $fp_in = fopen('./mingdan.csv', "rb");
        while (!feof($fp_in)) {
            $line = fgets($fp_in);
            if ($line) {
                $arr = explode(',', $line);
                // 待插入数据格式化
                $site = $arr[2];
                $hid = $arr[3];

                $house_info = \App\Admin\Models\House::getInstance()->getHouseBySiteHid($site,$hid);

                if (!empty($house_info)) {

                    \App\Admin\Models\Phone400::getInstance()->recoverLogic($house_info,$house_info);
                }
            }
        }
        fclose($fp_in);

        return false;
    }

    public function recover400hidsAction()
    {
        ini_set('memory_limit','400M');
        set_time_limit(7200);//两小时

        $site = input('get.site');
        $hids = input('get.hids');

        if (!empty($site) && !empty($hids)) {
            $hids = explode(',',$hids);

            foreach ($hids as $hid) {
                $house_info = \App\Admin\Models\House::getInstance()->getHouseBySiteHid($site,$hid);

                if (!empty($house_info)) {

                    \App\Admin\Models\Phone400::getInstance()->recoverLogic($house_info,$house_info);
                }
            }
        }


        return false;
    }


    public function city11Action() {
        $city_list = City::getInstance()->getCityList();

        $str = 'pc,touch'.PHP_EOL;
        foreach ($city_list as $k=>$v) {
            $str .= "http://house.leju.com/{$v['city_code']}/price/,http://m.leju.com/house/price/{$v['city_en']}/";
            $str .= PHP_EOL;
        }

        header("Content-type:text/csv;charset=utf8");
        header("Content-Disposition:attachment;filename=city.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;

        return false;
    }

    public function house25Action()
    {
        ini_set('memory_limit','800M');

        $city_list = \App\Models\City::getInstance()->getCityList();


        $dict_housetype = \Yaf\Registry::get('dict')->house->toArray();


        $time = strtotime('2017-01-01');
        $str =  "id|site|hid|楼盘名称|楼盘等级|销售状态|预售证|主力户型|开盘时间|项目地址|物业类型|建筑类型".PHP_EOL;
        $sql = "select site,hid from house where site = 'bj' and salestate in (1,2,3,10) ";
        $res = gbk2utf8(\DB::select($sql));
        foreach ($res as $k=>$v) {
            $house_info = \App\Models\House::getInstance()->getHouseByHid($v['site'],$v['hid']);

            $str .= "{$house_info['id']}|{$house_info['site']}|{$house_info['hid']}|{$house_info['name']}|{$dict_housetype['house_level'][$house_info['house_level']]}|{$house_info['salestate_cn']}|{$house_info['licence']}";


            $data['all_house_type'] = $this->getHouseTypeList($v['site'], $v['hid']);
            $all_house_type_desc = '';
            if (!empty($data['all_house_type'])) {//主力户型整体描述如：二居室(91.6~132.22㎡)  三居室(103.03~157.68㎡)
                $tmp_arr = array();
                foreach ($data['all_house_type'] as $kk=>$vv) {
                    $tmp_arr[] = $vv['housetype_cn'].'('.$vv['area_range'].')';
                }
                $all_house_type_desc = implode('　', $tmp_arr);
            }


            $str .= "|{$all_house_type_desc}|{$house_info['opentime']}|{$house_info['address']}";

            $str .= "|{$house_info['hometype']}|{$house_info['archtype']}";

            $str .= PHP_EOL;

        }

        $str = utf82gbk($str);


        header("Content-type:text/csv;charset=gbk");
        header("Content-Disposition:attachment;filename=house.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
        return false;
    }

    private function getHouseTypeList($site = '', $hid = 0, $is_main = 0)
    {
        $return = array();
        if ($is_main) {
            $res = Picture::getInstance()->getMainHouseType($site, $hid, '208');
        } else {
            $res = Picture::getInstance()->getAllHouseType($site, $hid, '208');
        }



        if (!empty($res)) {
            $housetype_conf = \Yaf\Registry::get('dict')->offsetGet('pic')->offsetGet('housetype')->toArray();
            $hx_num_conf =  \Yaf\Registry::get('dict')->offsetGet('pic')->offsetGet('hx_number')->toArray();

            //算出主力户型每种居室的价格范围,以及转换字典项
            foreach ($res as $k=>$v) {
                $area_arr = array();

                $main_housetype = array();
                $seq = array();
                foreach ($v as $kk=>$vv){
                    $main_housetype[$kk] = $vv['main_housetype'];
                    $seq[$kk] = $vv['seq'];
                }
                array_multisort($main_housetype,SORT_DESC,$seq,SORT_DESC,$v);
                unset($main_housetype);unset($seq);

                foreach ($v as $kk=>$vv){
                    if ($vv['area']) {//剔除掉那些不填面积的
                        $area_arr[] = $vv['area'];
                    } elseif ($vv['site'] == 'cq' && $vv['hx_inside_area']) { //重庆特殊护理 用套内面积
                        $area_arr[] = $vv['hx_inside_area'];
                    }
                    $v[$kk]['pic_url'] = $vv['pic_s208'];
                    $v[$kk]['hx_room'] = $hx_num_conf[$vv['hx_room']];
                    $v[$kk]['hx_parlor'] = $hx_num_conf[$vv['hx_parlor']];
                    $v[$kk]['hx_toilet'] = $hx_num_conf[$vv['hx_toilet']];
                    $v[$kk]['huxing_comment'] = strip_tags($vv['comment']);
                    if ($hx_price_sum = Picture::getInstance()->getHouseTypePriceSumDisplay($vv)) {//剔除掉，那些总价为０的
                        $v[$kk]['hx_price_sum'] = $hx_price_sum;
                    }

                    $title = '';//展示的头部
                    if ($vv['descrip']) {
                        $title .= $vv['descrip'];
                    }
                    if ($vv['descrip'] && ($vv['hx_room'] || $vv['hx_parlor']  || $vv['hx_toilet']  || $vv['area'])) {
                        $title .= ',';
                    }

                    if ($vv['hx_room'])
                        $title .= $vv['hx_room'].'室';
                    if ($vv['hx_parlor'])
                        $title .= $vv['hx_parlor'].'厅';
                    if ($vv['hx_toilet'])
                        $title .= $vv['hx_toilet'].'卫';

                    if (($vv['hx_room'] || $vv['hx_parlor']  || $vv['hx_toilet']  || $vv['area']) && ($vv['area'] || $vv['hx_inside_area']))
                        $title .= ',';
                    if ($vv['area'])
                        $title .= '建筑面积约'.$vv['area'].'平米';
                    if ($vv['hx_inside_area'])
                        $title .= '套内面积约'.$vv['hx_inside_area'].'平米';

                    $v[$kk]['title'] = $title;

                    //删除掉那些售罄的户型图片 tinglei 2017-02-20
                    if ($vv['sale_state'] == 1) {
                        unset($v[$kk]);
                    }
                }

                if (!empty($v)) {
                    if ($area_arr) {
                        if (min($area_arr) != max($area_arr)) {
                            $row['area_range'] = min($area_arr).'~'.max($area_arr).'㎡';
                        } else {
                            $row['area_range'] = min($area_arr).'㎡';
                        }
                    } else {
                        $row['area_range'] = '待定';
                    }

                    $row['housetype_cn'] = $housetype_conf[$k];
                    $row['list'] = $v;
                    $return[] = $row;
                }
            }
        }

        return $return;
    }

}