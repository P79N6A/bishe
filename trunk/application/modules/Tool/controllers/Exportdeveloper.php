<?php
class ExportdeveloperController extends \Yaf\Controller_Abstract
{

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
         $cities = \App\Models\City::getInstance()->getDirectCityList();
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
                $data_develop_short[$city['city_en']][$i]['wiki_id'] = $value[1];
                $data_develop_short[$city['city_en']][$i]['city_cn'] = $city['city_cn'];
                $data_develop_short[$city['city_en']][$i]['city_en'] = $city['city_en'];
                $i++;
            }
         }
         //p(count($data_develop_short));exit;  
         
         foreach ($data_develop_short as $key => $city_developer) {

            $developer = \DB::table('developer')
            ->where(array('site'=>$key))
            ->where(array('status'=>0))
            ->where(array('wiki_id'=>0))
            ->get();
            //$developer = gbk2utf8($developer);
            $sql = "select hid,h.name,salestate,developer,developerid,h.site,house_level,d.wiki_id from house as h   inner join developer as d on h.developerid=d.id where h.status =1  and h.salestate in (1,2,3,10) and d.wiki_id = 0 and h.site = '$key'";
            $house_arr = \DB::select($sql);
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
                        $data_develop_info[$key][$v['id']]['wiki_id'] = $value['wiki_id'];
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
                        $data_develop_excel[$info['site']][$info['hid']]['wiki_id'] = $value['wiki_id'];
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
                $data_develop_excel[$hosue_info['site']][$hosue_info['hid']]['wiki_id'] = $developer_detil[$hosue_info['developerid']]['wiki_id'];
                }
             }
         }
        $this->exportData('developer_house',array('开发商简称','城市','楼盘名称','site','hid','salestate','开发商全称','developerid','楼盘等级','wiki_id'),$data_develop_excel);
    }



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
                            $str .= "{$vv['short']},{$vv['city_cn']},{$vv['name']},{$vv['site']},{$vv['hid']},{$vv['salestate']},{$vv['developer']},{$vv['developerid']},{$vv['house_level']},{$vv['wiki_id']}";
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
}