<?php
/*
	@key job  key 队列list名,按业务命名(例如:sphinx,log,redis,OtherItems), 
	@value 队列任务,按 业务方-数据源/业务逻辑描述-Job命名(例如:SphinxHouseJob,OtherItemsPushNotifyJob) 
	-------------------------------------------------------------------------------------------
	sphinx 
	@SphinxHouseJob 楼盘推送sphinx
	@SphinxPicJob	图片推送sphinx
	@SphinxFangyuanJob 房源推送sphinx
	OtherItems 第三方
	@OtherItemsPushNotifyJob 向第三方推送修改通知
	@key groupjob 
 */
$queue = array (
	'job' => array(
		    "sphinx" 	  => array('SphinxHouseJob', 'SphinxPicJob', "SphinxFangyuanJob",'SphinxHouseClicksJob'),
		    "sphinxBusiness" => array('SphinxbusinessHousejob', 'SphinxbusinessPicjob'),
		    //"smarty" 	  => array('ClearSmartyCacheJob'),
		    "sphinxHouseletter" => array('SphinxHouseletterJob'),
		    "sphinxBusinessletter" => array('SphinxBusinessletterJob'),
		    "video" 	  => array('VideoUpdateJob'),
		    "syncMsg" 	  => array('SyncMsgJob'),
			"sync400Job"  => array('Sync400Job'),
		    "otherItems"  => array('OtherItemsPushNotifyJob'),
		    "setHouseLevel" => array('SetHouseLevelJob'),
		    "redis" 	  => array("RedisHouseChangeJob", "RedisPicChangeJob"),
			"log" 		  => array('SyncLogJob'), 
			"juli" 		  => array('JuliKuaiXunBindingJob','JuliPicBindingJob','JuliHouseBindingJob'),
		),
    // 'groupjob' => array(
	// 		    "house"  => array("HouseToSphinxJob", "HouseChangeJob"),
	// 		    "pic"	 => array("PicToSphinxJob", "PicChangeJob")
   	// 		 	)
);



?>