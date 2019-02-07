<?php
if(!defined('HOUSE_VERSION')) exit;
/**
 * @name   Bootstrap
 * @author jingfu@leju.com
 * @create 2016/08/04
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */

use Yaf\Loader;
use Yaf\Registry;
use Yaf\Dispatcher;
use Yaf\Application;
use Yaf\Bootstrap_Abstract;

class Bootstrap extends Bootstrap_Abstract
{

    private $_config = null;
    public $start_time = 0;

    /**
     * 初始化错误
     * @author chenchen16@leju.com
     * @date 2016/10/16
     */
    public function _initErrors()
    {
        //如果为开发环境,打开所有错误提示
        if (\Yaf\ENVIRON === 'develop' || (isset($_SERVER['SINASRV_IS_LOCAL']) && $_SERVER['SINASRV_IS_LOCAL']==='1') || (isset($_GET['dbg']) && $_GET['dbg'] == '1')) {
            error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);//使用error_reporting来定义哪些级别错误可以触发
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        }
    }

    /*
     * 初始化自动加载
     * @function _initLoader
     * @author   chenchen16@leju.com
     * @create   2016/08/09
     */
    public function _initLoader(){
        if (file_exists(APP_PATH . '/vendor/autoload.php')) {
            Loader::import(APP_PATH . '/vendor/autoload.php');
        }
    }

    /*
     * @function _initConfig
     * @function 初始化配置，把配置保存起来
     * @author   jingfu@leju.com
     * @create   2016/08/04
     */
    public function _initConfig()
    {
        //INI配置
        //把配置保存起来
        $config = Application::app()->getConfig()->toArray();

        //判断环境为线上并且模式是cli的情况下载入该配置文件
        $CliConfig = array();
        if (PHP_SAPI === 'cli') {
            Loader::import(APP_PATH . '/conf/cli.config.php');
            $config = array_merge($config, $CliConfig);
        } else {
            Loader::import(APP_PATH . '/conf/application.config.php');
        }

        $config = new \Yaf\Config\Simple($config);
        $this->_config = $config;
        Registry::set('config', $this->_config);

        //楼盘库全局配置
        $dict = array();
		Loader::import(APP_PATH . '/conf/dict.config.php');
        $dict = new \Yaf\Config\Simple($dict);
        Registry::set("dict", $dict);

        //楼盘库数据表跟redis需要的字段
        $db_fields = array();
        Loader::import(APP_PATH . '/conf/dbfields.config.php');
        $db_fields = new \Yaf\Config\Simple($db_fields);
        Registry::set("dbFields", $db_fields);


        //获取cli环境配置,验证环境变量
        Loader::import(APP_PATH . '/conf/cli.config.php');
        $CliConfig = new \Yaf\Config\Simple($CliConfig);
        Registry::set("CliConfig", $CliConfig);

        //获取cli环境配置,验证环境变量
        $apikey = array();
        Loader::import(APP_PATH . '/conf/apikey.config.php');
        $apikey = new \Yaf\Config\Simple($apikey);
        Registry::set("apikey", $apikey);

        //楼盘库全局配置
        $city = array();
        Loader::import(APP_PATH . '/conf/city.config.php');
        $city = new \Yaf\Config\Simple($city);
        Registry::set("city", $city);

    }

    /*
     * @function _initConfig
     * @function 初始化常量配置，把常量保存起来
     * @author   jingfu@leju.com
     * @create   2016/08/04
     */
    public function _initConst()
    {
        Loader::import(APP_PATH . '/conf/const.config.php');
        Loader::import(APP_PATH . '/conf/dbtablename.config.php');
    }

    /*
     * @function _initPlugin
     * @function 初始化插件
     * @author   jingfu@leju.com
     * @create   2016/08/04
     */
	public function _initPlugin(Dispatcher $dispatcher)
    {
		//注册一个插件
		// $objSamplePlugin = new SamplePlugin();
		// $dispatcher->registerPlugin($objSamplePlugin);
	}

    /*
     * @function _initRoute
     * @function 初始化路由
     * @author   jingfu@leju.com
     * @create   2016/08/04
     */
	public function _initRoute(Dispatcher $dispatcher)
    {
        $router = $dispatcher->getInstance()->getRouter();
        $router->addConfig(Registry::get('config')->routes);

	}

    /*
    * 初始化数据库分发器
    * @function _initDefaultDbAdapter
    * @author   chenchen16@leju.com
    */
    public function _initDefaultDbAdapter()
    {
        //初始化 illuminate/database
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $capsule->addConnection($this->_config->database->toArray());
        $capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
        $capsule->setAsGlobal();
        //开启Eloquent ORM
        $capsule->bootEloquent();
    }


    /**
     * @function _initCache
     * @function 初始化缓存
     * @author   jingfu@leju.com
     * @create   2016/08/04
     */
    // public function _initCache()
    // {
    //     Registry::set('redis_server_status', 1);

    //     Registry::set('memcached_status', 1);
    // }

    /**
     * 初始化Facades，简单的facades，没有使用DI
     * @author chenchen16@leju.com
     * @date 2016/11/14
     */
    // public function _initFacades()
    // {
    //     $aliases = array();
    //     Loader::import(APP_PATH . '/conf/aliases.php');
    //     \Common\AliasLoader::getInstance($aliases)->register();
    // }


    /*
     * 载入公用函数
     * @function _initFunction
     * @author   chenchen16@leju.com
     */
    public function _initFunction()
    {
        Loader::import('Common/functions.php');
    }
}
