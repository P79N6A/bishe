<?php
class DbgController extends \Yaf\Controller_Abstract
{
    public function indexAction()
    {
        ini_set('memory_limit','800M');
        set_time_limit(0);
        $sql = "select id,site,hid,name from a_house where id in (164580,164704,164706,164695)";
        $res = \DB::select($sql,array(),false);
        p($res);
    }


    public function delMlejuAction()
    {
        $url = 'http://m.leju.com/m.leju.com/index.php?site=api&ctl=UpdatePush&act=get_push_message&msg_type=ESTATE_DATA_SEND&clearm=1&app_key=b22fefa063bbe00f2f406a0ce2864910&msg=';
        $res = \DB::select("select site,hid,name from ".HOUSE ." where site = 'sjz' and status = 1");
        foreach ($res as $k=>$v) {
            $arr  = array('city'=>$v['site'],'hid'=>$v['hid'],'status'=>-1);
            $url_tmp = $url.json_encode($arr);
            $res1 = http_get($url_tmp);
            p($res1);
        }
    }
}