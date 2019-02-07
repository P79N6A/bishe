<?php
/*
 * 域名常量配置
 * @author   tinglei
 * @create   2016/09/22
 */
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'sina') !== false) {
    define("ITEM", "sina");
    define("ITEM_URL_ROOT", "house.sina.com.cn");
    define("ITEM_URL", "http://data.house.sina.com.cn");
    define("JULI_URL", "https://www.julive.com");
    define("FS_ITEM_URL_ROOT", "house.sina.com.cn");
    define("MAP_ITEM_URL", "http://map.house.sina.com.cn");
    define("MAP_ITEM_URL_NO_HTTP", "map.house.sina.com.cn");

} else {//默认乐居域名
    define("ITEM", "leju");
    define("ITEM_URL_ROOT", "leju.com");
    define("ITEM_URL", "http://house.leju.com");
    define("JULI_URL", "https://www.julive.com");
    define("FS_ITEM_URL_ROOT", "leju.com");

    if (strpos($_SERVER['HTTP_HOST'], 'house.leju') !== false){
        define("MAP_ITEM_URL", "http://map.house.leju.com");//地图路由跳转专用
        define("MAP_ITEM_URL_NO_HTTP", "map.house.leju.com");
    } else {
        define("MAP_ITEM_URL", "http://map.leju.com");
        define("MAP_ITEM_URL_NO_HTTP", "map.leju.com");
    }
}
define("ITEM_CDN", "http://cdn.leju.com");//cdn url

define("LIVE_URL", "http://live.leju.com");//直播url

define("MAP_URL", "http://map." . ITEM_URL_ROOT);//地图

define("ESF_URL","http://esf." . ITEM_URL_ROOT);//二手房

define("ESF_URL_NO_HTTP", "esf." . ITEM_URL_ROOT);

define("IMG_URL", "https://src." . ITEM_URL_ROOT);//图片url

define("MEDIA_URL", "https://media-src." . ITEM_URL_ROOT);//图库文件url

define("IMG_URL_SPHINX", "http://src." . ITEM_URL_ROOT);//图片url

define("SINA_HTTP_URL", "http://data.house.sina.com.cn");//新浪域

define("MEMBER91_URL", "http://f.leju.com");//91会员url

define("COMMENT_URL", "http://c.leju.com");//评论系统url

define("TAG_URL", "http://tag.leju.com");//评论系统url

define("TOUCH_URL", "http://m.leju.com");//触屏url

define("TOUCH_URL_PRO", "https://m.leju.com");//触屏url

define("COMMENT_APPKEY", "25b13220236a35bb5ad6901b0ac94905");//评论系统url

define("SWITCHBOARD_400", "400 606 6969");//400总机号

define("DEFAULT_HOUSE_COVER_IMG_URL", "https://cdn.leju.com/data_house/V2/build/images/default_m.jpg");//楼盘默认图片

define("DEFAULT_HOUSE_COVER_IMG_URL_SPHINX", "http://cdn.leju.com/data_house/V2/build/images/default_m.jpg");//楼盘默认

define("URL_404", "http://www.leju.com/404/");//404地址

/**------------------------------------以下后台常量----------------------------------------------------------**/


define("URL_ADMIN_LOGIN", "admin.house.sina.com.cn");

define('COOKIE_PREFIX', 'newhouse_');

define('COOKIE_KEY', 'Hwn6EUA56v' . DATE('Ym')); //hash

define('PHOTO_PKEY', "af7e5f19cb87a3cca23296b7b8707f83");//图库项目主密码，用于客户端与服务器端加密

define('PHOTO_MKEY', "2683010a90f938050dbb55c6c0b903ab");//图库分密钥，用于一个项目内不同应用或者不同权限的区分


define("SPECIAL_GROUP_ID", 255); //乌鲁木齐用户组 特殊处理，添加楼盘时候默认状态是已删除的

if(Yaf\ENVIRON =='develop' || (isset($_SERVER['SINASRV_IS_LOCAL']) && $_SERVER['SINASRV_IS_LOCAL']==='1')){
    define("ENV", 'develop');
} else {
    define("ENV", 'product');
}

if(ENV =='product'){//线上
    define("FMT_JS", "//cdn-zb.leju.com/js/upload_online_v1.0.0.min.js");//后台富媒体js插件正式地址
    define("FMT_INDEX_JS", "http://cdn-zb.leju.com/js/lejulive_online_v1.0.0.js");//前台台富媒体js插件正式地址
    define("RES_HOST",'res.leju.com');
    //搜索池线上地址
    define('SPHINX_SEND_DATA_URL','http://info.leju.com/accept/accept/index');
    define('SPHINX_SEARCH_DATA_URL','http://info.leju.com/search/default/index');
    //消息队列线上正式地址appid
    define('SEND_MESSAGE_APPKEY','d7de5d8d6f53f98d96fcc6e451f50822');

    //阳光购房小程序消息队列测试地址appid
    define('PRO_SEND_MESSAGE_APPKEY','eedd6520fa0334cac10a40240d7fe0b6');
    define('HABO_HOST','habo.leju.com');

    define('API_HOST', 'http://api.house.leju.com/');

    define('RES_LEJU', 'http://res.leju.com');
    define("URL_ADMIN_INDEX", "admin.house.leju.com");

} else {//线下/测试线
    define("FMT_JS", "//cdn-zb.leju.com/js/upload_v1.0.0.min.js");//后台富媒体js插件测试地址
    define("FMT_INDEX_JS", "http://cdn-zb.leju.com/js/lejulive_v1.0.0.js");//前台台富媒体js插件正式地址
    define("RES_HOST",'res.bch.leju.com');

    //搜索池测试地址
    define('SPHINX_SEND_DATA_URL','http://test-info.leju.com/accept/accept/index');
    define('SPHINX_SEARCH_DATA_URL','http://test-info.leju.com/search/default/index');
    //消息队列测试地址appid
    define('SEND_MESSAGE_APPKEY','2c422157dab120ce623ebb5f1dcd122f');

    //阳光购房小程序消息队列测试地址appid
    define('PRO_SEND_MESSAGE_APPKEY','eedd6520fa0334cac10a40240d7fe0b6');
    define('HABO_HOST','ad.bch.leju.com');

    define('API_HOST', 'http://api.house.bch.leju.com/');

    
    define('RES_LEJU', 'http://res.bch.leju.com');
    
    if(strpos($_SERVER['HTTP_HOST'], 'bch') !== false){
        define("URL_ADMIN_INDEX", "admin.house.bch.leju.com");
    }else{
        define("URL_ADMIN_INDEX", "admin.house.leju.com");
    }
}





