<?php
/**
 * name:redis_key
 * expire_time:过期时间
 * data_type:数据类型
 * prefix:是否用作前缀
 * @var array
 */
$rediskey = array(
    /********************  mysql 表数据  ***************************/

	'house'=>array('name'=>'datahouse_house_main', 'expire_time'=>0),//楼盘主表基本信息
	'house_broadcast'=>array('name'=>'datahouse_house_broadcast', 'expire_time'=>0),//楼盘附表之广播信息
	'house_direct_video'=>array('name'=>'datahouse_house_direct_video', 'expire_time'=>0),//楼盘附表之视频直播地址
	'house_impression'=>array('name'=>'datahouse_house_impression', 'expire_time'=>0),//楼盘附表之楼盘印象
	'house_video'=>array('name'=>'datahouse_house_video', 'expire_time'=>0),//楼盘附表之楼盘印象
	'house_score'=>array('name'=>'datahouse_house_score', 'expire_time'=>0),//楼盘附表之楼盘评分
	'house_price_suite'=>array('name'=>'datahouse_house_price_suite', 'expire_time'=>0),//楼盘附表之在售户型
	'house_price'=>array('name'=>'datahouse_house_price', 'expire_time'=>0),//楼盘附表之楼盘价格
	'house_licence'=>array('name'=>'datahouse_house_licence', 'expire_time'=>0),//楼盘附表之预售证列表
	'house_order'=>array('name'=>'datahouse_house_order', 'expire_time'=>0),//楼盘附表楼盘排序表
	'newhouse_salehouse'=>array('name'=>'newhouse_salehouse', 'expire_time'=>0),//楼盘经纪人关联关系
	'newhouse_saler'=>array('name'=>'newhouse_saler', 'expire_time'=>0),//楼盘经纪人信息 
    'city'=>array('name'=>'datahouse_city', 'expire_time'=>0),//城市表
    'newhouse_config'=>array('name'=>'datahouse_newhouse_config', 'expire_time'=>0),//城市配置
    'newhouse_options'=>array('name'=>'datahouse_newhouse_options', 'expire_time'=>0),//城市选项配置
    'picture' => array('name' => 'datahouse_picture', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => true),//以seq为hash_key存储pic信息
    'picture_house_relation' => array('name' => 'datahouse_picture_house_relation', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => true),//以楼盘site+hid为redis_key,pictypeid为hash_key存储图片的seq数组
    'house_field_conf'=>array('name'=>'datahouse_house_field_conf', 'expire_time'=>0),//楼盘经纪人信息
    'developer'=>array('name'=>'datahouse_developer', 'expire_time'=>0),

    /******************************************  第三方数据  *************************************************/
    'fangshou_topnav' => array('name' => 'datahouse_fangshou_topnav', 'expire_time' => 0, 'data_type' => 'String', 'prefix' => false),//页头
    'fangshou_city' => array('name' => 'datahouse_fangshou_city', 'expire_time' => 0, 'data_type' => 'String', 'prefix' => false),//房首下拉框城市
    'equan' => array('name' => 'datahouse_equan','expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//E金券 
    'kft_activity' => array('name' => 'datahouse_kft_activity', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//看房团
    'member91' => array('name' => 'datahouse_member91', 'expire_time' => 0, 'data_type'=>'Hash', 'prefix' => false),//91活动信息
    'member91_city' => array('name' => 'datahouse_member91_city', 'expire_time' => 0, 'data_type'=>'String', 'prefix' => false),//91活动城市列表
    'didi_city' => array('name' => 'datahouse_didi_city', 'expire_time' => 0, 'data_type' => 'String', 'prefix' => false),//滴滴城市
    'didi' => array('name' => 'datahouse_didi', 'expire_time' => 0, 'data_type' => 'String', 'prefix' => false),//滴滴专车信息
	'esf_city' => array('name' => 'datahouse_esf_city', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//有新房的二手房城市
    'esf_info' => array('name' => 'datahouse_esf_info', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => true),//跟新房有联系的详细二手房信息，hash_key为楼盘site+hid
    'im' => array('name' => 'datahouse_im', 'expire_time' => 0, 'data_type' => 'Set', 'prefix' => false),//IM,主要存储这个楼盘是否有IM
    'live' => array('name' => 'datahouse_live', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//直播
    'live_room_house_relation' => array('name' => 'datahouse_live_room_house_relation', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//直播房间和楼盘的对应关系
    'live_city' => array('name' => 'datahouse_live_city', 'expire_time' => 0, 'data_type' => 'String', 'prefix' => false),//直播城市
	'live_top' => array('name' => 'datahouse_live_top', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//直播排行
	'vr_hangpai' => array('name' => 'datahouse_vr_hangpai', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//航拍VR
	'fmt_video' => array('name' => 'datahouse_fmt_video', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//富媒体
	'admin_fmt' => array('name' => 'datahouse_admin_fmt_video', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//楼盘库后台上传的富媒体，以liveid为hashkey
    'tag' => array('name' => 'datahouse_tag', 'expire_time' => 0, 'data_type' => 'Hash', 'prefix' => false),//标签
    'special_tag' => array('name' => 'datahouse_special_tag', 'expire_time' => 0, 'data_type' => 'String', 'prefix' => false),//特色标签

	/********************************** 城区当月均价 ************************/
	'district_price' => array('name' => 'datahouse_district_price', 'expire_time' => 0, 'data_type' => 'SortedSet', 'prefix'=> false), //城区当月均价
	/************************ 　异步操作（楼盘点击量）　**********************************/
	'house_clicks'=>array('name'=>'datahouse_clicks', 'expire_time'=>0),
	'house_letter_clicks'=>array('name'=>'datahouse_letter_clicks', 'expire_time'=>0),

    'search_tool_clicks'=>array('name'=>'datahouse_search_tool_clicks', 'expire_time'=>0),//后台搜索工具 楼盘页面 点击量统计
    'search_tool_news_clicks'=>array('name'=>'datahouse_search_tool_news_clicks', 'expire_time'=>0),//后台搜索工具 快讯页面 点击量统计
    'search_tool_jingpin_clicks'=>array('name'=>'datahouse_search_tool_jingpin_clicks_clicks', 'expire_time'=>0),//后台楼盘修改页竞品点击统计

    /************************ 　jQuery文件缓存　**********************************/
	'cdn_jquery'=>array('name'=>'cdn_jquery_js', 'expire_time'=>0),

	/*********404************/
	'404'=>array('name'=>'datahouse_404', 'expire_time'=>0),
   /*****************神马搜索******************/
	'search_shenma'=>array('name'=>'datahouse_shenma_search_hash','time'=>0),//神马搜索
	'new_shenma'=>array('name'=>'datahouse_shenma_new_hash','time'=>0),//新版神马搜索
	'shenma_one_name'=>array('name'=>'datahouse_shenma_oname','time'=>86400),//神马搜索存储全国楼盘的信息，过滤全国重名专用
	'shenma_house_onameflag'=>array('name'=>'datahouse_shenma_onameflag','time'=>86400),//神马搜索存储的该楼盘是否有重名的标志
	/*************400分机号标识位***********************/
    'flag_bit_400' => array('name'=>'datahouse_flag_bit_400','data_type' => 'Hash', 'prefix' => false),
    /****************baidu_aladdin*********************/
    'baidu_aladdin_widely_index' => array('name'=>'datahouse_baidu_aladdin_widely_index','data_type' => 'Hash', 'prefix' => 86400),
    'baidu_aladdin_accurate_index' => array('name'=>'datahouse_baidu_aladdin_accurate_index','data_type' => 'Hash', 'prefix' => 86400),
    'baidu_aladdin_widely_hash' => array('name'=>'datahouse_baidu_aladdin_widely_hash', 'expire_time'=>0),
    'baidu_aladdin_accurate_hash' => array('name'=>'datahouse_baidu_aladdin_accurate_hash', 'expire_time'=>0),
	'baidu_aladdin_widely_count' => array('name'=>'datahouse_baidu_aladdin_widely_count','data_type' => 'Hash', 'prefix' => 86400),
	'baidu_aladdin_accurate_count' => array('name'=>'datahouse_baidu_aladdin_accurate_count','data_type' => 'Hash', 'prefix' => 86400),
	'baidu_aladdin_accurate_index_new' => array('name'=>'datahouse_baidu_aladdin_accurate_index_new','data_type' => 'Hash', 'prefix' => 86400),
	'baidu_aladdin_accurate_hash_new' => array('name'=>'datahouse_baidu_aladdin_accurate_hash_new', 'expire_time'=>0),
	'baidu_aladdin_accurate_count_new' => array('name'=>'datahouse_baidu_aladdin_accurate_count_new','data_type' => 'Hash', 'prefix' => 86400),
	/****************mobile_alading*********************/
	'mobile_alading_accurate_index' => array('name'=>'datahouse_mobile_alading_accurate_index','data_type' => 'Hash', 'prefix' => 86400),
	'mobile_alading_accurate_hash' => array('name'=>'datahouse_mobile_alading_accurate_hash', 'expire_time'=>0),
	'mobile_alading_accurate_count' => array('name'=>'datahouse_mobile_alading_accurate_count', 'data_type' => 'Hash', 'prefix' => 86400),
	'mobile_alading_accurate_index_new' => array('name'=>'datahouse_mobile_alading_accurate_index_new','data_type' => 'Hash', 'prefix' => 86400),
	'mobile_alading_accurate_hash_new' => array('name'=>'datahouse_mobile_alading_accurate_hash_new', 'expire_time'=>0),
	'mobile_alading_accurate_count_new' => array('name'=>'datahouse_mobile_alading_accurate_count_new', 'data_type' => 'Hash', 'prefix' => 86400),
	'mobile_alading_widely_index' => array('name'=>'datahouse_mobile_alading_widely_index','data_type' => 'Hash', 'prefix' => 86400),
	'mobile_alading_widely_hash' => array('name'=>'datahouse_mobile_alading_widely_hash', 'expire_time'=>0),
	'mobile_alading_widely_count' => array('name'=>'datahouse_mobile_alading_widely_count', 'data_type' => 'Hash', 'prefix' => 86400),
	/****************test_alading*********************/
	'test_alading_index' => array('name'=>'datahouse_test_alading_index','data_type' => 'Hash', 'prefix' => 86400),
	'test_alading_hash' => array('name'=>'datahouse_test_alading_hash', 'expire_time'=>0),
	'test_alading_count' => array('name'=>'datahouse_test_alading_count', 'data_type' => 'Hash', 'prefix' => 86400),
    /********************mock topclum*****************************/
    'mock_topcolumn' => array('name'=>'datahouse_mock_topcolumn', 'expire_time'=>86400),

    /***************************来客分机号***************************************************/
	'laike_phone' => array('name'=>'datahouse_laike_phone', 'expire_time'=>0,'data_type' => 'String'),
	/***************************验证码***************************************************/
	'captcha'=> array('name'=>'datahouse_captcha', 'expire_time'=>300,'data_type' => 'String'),
	/***************************短信验证码***************************************************/
	'sms'=> array('name'=>'datahouse_sms', 'expire_time'=>300,'data_type' => 'String'),
	/***************************批量推送(楼盘和图片)sphinx城市队列***************************************************/
	'picture_all_queue'=> array('name'=>'datahouse_picture_all_queue', 'expire_time'=>0,'data_type' => 'List'),
	'house_all_queue'=> array('name'=>'datahouse_house_all_queue', 'expire_time'=>0,'data_type' => 'List'),
	'juli_letter_all_queue'=> array('name'=>'juli_letter_all_queue', 'expire_time'=>0,'data_type' => 'List'),//居里快讯
	'letter_all_queue'=> array('name'=>'letter_all_queue', 'expire_time'=>0,'data_type' => 'List'),//楼盘快讯
	/***************************URL推送链接***************************************************/
	'house_pk_redis'=>array('name'=>'house_pk_url', 'expire_time'=>0),//楼盘PK URL集合
    'house_search_redis'=>array('name'=>'house_search_url', 'expire_time'=>0),//楼盘搜索 URL集合
	'city_price_redis'=>array('name'=>'city_price_url', 'expire_time'=>0),//城市价格 URL集合
    'district_price_redis'=>array('name'=>'district_price_url', 'expire_time'=>0),//城区价格 URL集合
    'house_price_redis'=>array('name'=>'house_price_url', 'expire_time'=>0),//楼盘价格 URL集合
	'house_info_redis'=>array('name'=>'house_info_url', 'expire_time'=>0),//楼盘信息 URL集合
	'house_pic_redis'=>array('name'=>'house_pic_url', 'expire_time'=>0),//楼盘图片 URL集合
	'house_siLian_redis'=>array('name'=>'house_siLian_url', 'expire_time'=>0),//楼盘死链 URL集合
	'city_mapping_redis'=>array('name'=>'city_mapping_url', 'expire_time'=>0),//城市映射 URL集合
	'house_mapping_redis'=>array('name'=>'house_mapping_url', 'expire_time'=>0),//楼盘映射 URL集合
	'search_mapping_redis'=>array('name'=>'search_mapping_url', 'expire_time'=>0),//搜索映射 URL集合
	'search_map_redis'=>array('name'=>'search_map_url', 'expire_time'=>0),//搜索映射 URL集合
);

?>
