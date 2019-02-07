<?php
$memcachekey=array(
	'houseHot'=>array('name'=>'datahouse','expire_time'=>'86400'),//楼盘热度key
	'historyPrice'=>array('name'=>'datahouse_historyprice','expire_time'=>'86400'),//历史价格
	'districtPrice'=>array('name'=>'datahouse_districtPrice', 'expire_time'=>'86400'),//城市区域价格
	'cityPrice'=>array('name'=>'datahouse_historyprice','expire_time'=>'86400'),//历史价格
	'newsList'=>array('name'=>'datahouse_newslist','expire_time'=>'1800'),//新闻缓存News.php
	'houseAround'=>array('name'=>'datahouse_housearound','expire_time'=>'1800'),//推荐楼盘Touch.php
	'hxInfo'=>array('name'=>'datahouse_hxinfo','expire_time'=>'86400'),//户型列表信息Touch.php
	'SamePriceList'=>array('name'=>'datahouse_samepricelist','expire_time'=>'1800'),//价格页同价位楼盘zlc
	'DistrictHot'=>array('name'=>'datahouse_DistrictHot','expire_time'=>'1800'),//价格页区域热门楼盘zlc
	'HotList'=>array('name'=>'datahouse_HotList','expire_time'=>'1800'),//价格页城市热门楼盘zlc
	'circleHouseList'=>array('name'=>'datahouse_circleHouseList','expire_time'=>'1800'),//周边楼盘
	'friendLink'=>array('name'=>'datahouse_friendLink','expire_time'=>'14400'),//友情链接
);
?>