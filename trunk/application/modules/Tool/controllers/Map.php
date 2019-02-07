<?php
use App\Controllers\Controller;
class MapController extends Controller
{
    
    public function indexAction()
    {
    	$cities = \App\Models\City::getInstance()->getCityList();
    	$this->getView()->assign("cities",$cities);
    	$this->getView()->display($this->template_name);
    }
    
    public function  saveStationAction()
    {
    	$data = json_decode($_REQUEST['data'],true);
    	$pid = input('post.pid');
    	$city = input('post.city');
    	if (empty($data)) {
    		return;
    	}
    	
    	$station_model = \App\Models\SubwayStation::getInstance();
    	foreach ($data as $one){
    		$name = $one['name'];
    		$x = $one['location']['lng'];
    		$y = $one['location']['lat'];
    		$data = array();
    		$data['city'] = $city;
    		$data['subway_id'] = $pid;
    		$data['name'] = $name;
    		$data['coordx'] = $x;
    		$data['coordy'] = $y;
    		$data['updatetime'] = $data['createtime'] = time();
    		try {
    			$r = $station_model->insert($data);
    		} catch (Exception $e) {
    		}
    	}
    	

    	
//     	var_dump($data);
    }
    
    /**
     * 清理数据临时使用
     * @logic 逻辑
     * @author  jiebo@leju.com
     * @return_type
     * @date   2017年5月5日
     */
    public function delAllAction()
    {
//     	$station_model = \App\Models\SubwayStation::getInstance()->where("id",">",0)->delete();
//    		var_dump($station_model);exit;
    }
    
}