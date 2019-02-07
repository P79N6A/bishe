<?php
use App\Tool\Controllers\Controller;
/**
 * Class RedisconsoleController
 * @author tinglei
 * @date   2016-09-06
 */
class SqlconsoleController extends Controller
{

    /**
     * 控制台默认面板
     * @return bool
     */
    public function indexAction()
    {
        $this->getView()->display('redis/sqlconsole.html');
        return false;
    }



    /**
     * 控制台命令执行方法
     */
    public function cmdAction()
    {
        $sql = trim(trim($_GET['cmd'],''), "　");
        $pre = strtolower(substr($sql,0,6));

        if ($pre == 'select') {
            $res = \DB::select($sql);
            print_r($res);exit;
        } elseif ($pre == 'update') {
            p(2);
              $res = \DB::update($sql);
              $res && $res = gbk2utf8($res);
              p($res);exit;
            echo '暂不支持select以外的sql';exit;
        } /*elseif ($pre == 'delete') {
            $res = \DB::delete($sql);
            print_r($res);exit;
            echo '暂不支持select以外的sql';exit;
        }*/else {
            $res = \DB::statement($sql);
            print_r($res);exit;
            echo '暂不支持select以外的sql';exit;
        }
        return false;
    }

    /**
     * [adjsQudaoAction 渠道站招商广告 sql批量插入]
     * @Author   zlc
     * @DateTime 2017-03-28
     * @return   [type]     [description]
     */
    public function adjsQudaoAction(){
        exit('shixiao');
        // $sql = "SELECT a.city_en,a.city_cn FROM city as a LEFT JOIN newhouse_config as b on a.city_en = b.city and b.`key` = 'adjs_qudao' WHERE b.id is NULL";
        // $result = \DB::select($sql);
        // $result = gbk2utf8 ($result);
        
        // $param['key']='adjs_qudao';
        // $param['value'] = "http://adm.leju.sina.com.cn/get_ad_list/PG_58C90CD480F1D8";
        // $param['type'] = 3;
        // $param['is_criclj'] = 0;
        // //p($result);exit;
        // foreach ($result as $key => $value) {
        // if (!empty($value['city_en'])) {
        //         $param['city']=$value['city_en'];
        //         $res = \DB::table('newhouse_config')->insert($param);
        //         echo $value['city_en'].'+++++'.'/n';
        //         p($res);
        //     }    
        // }
        // $sql = "select * from city where isdirect=1";
        // $result = \DB::select($sql);
        // $result = gbk2utf8 ($result);
        // p($result);
        // foreach ($result as $key => $value) {
        //     $res =\DB::table('newhouse_config')->where(array('city'=>$value['city_en'],'key'=>'adjs_qudao','type'=>3))->update(array('value'=>''));
        //      echo $value['city_en'].'+++++'.'/n';
        //      p($res);
        // }
       

    }

        //修改
        // public function ext_typeAction(){

        //     $db = \DB::table(PHONE_400_EXTENSION)->where(array('ext_num'=>66868))->update(array('ext_type'=>2));
        //     p($db);
        // }
}