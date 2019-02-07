<?php
/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author root
 */

class SamplePlugin extends \Yaf\Plugin_Abstract {

	public function routerStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
	}

	public function routerShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
		if ( PHP_SAPI !== 'cli') {
			//初始化视图
			$smarty_config=\Yaf\Registry::get("config")->get('smarty')->toArray();

            $module_name = $request->getModuleName();
            $action_name = $request->getActionName();

            if ($module_name == 'Index') {
                
                //如果是PC页面，则启用smarty缓存，以楼盘为维度缓存
                $params = $request->getParams();
                $controller = $request->controller;

                //详情页首页缓存半小时，后台同步更新，其他页面暂时不用smarty缓存
                if($controller == 'House' && $action_name == 'index') {
                    $smarty_config['caching'] = true;
                    $smarty_config['cache_lifetime'] = 1800;
                }

                if (isset($params['city_code']) && isset($params['hid'])) {
                    $smarty_config['compile_dir'] = $smarty_config['compile_dir'].$params['city_code'].$params['hid'].'/';
                    $smarty_config['cache_dir'] = $smarty_config['cache_dir'].$params['city_code'].$params['hid'].'/';
                }

            } else {
                //不是前台页，不使用缓存
                $smarty_config['caching'] = false;

                //根据不同的模块设置不同的模版路径
                $smarty_config['template_dir'] = APP_PATH . '/application/modules/' . $module_name . '/views';
                //$smarty_config['template_dir'] = APP_PATH . '/application/views/';
            }
            
            //后台使用旧的定界符，其他都使用定界符 {{ 和 }}
            if ($module_name == 'Tool') {
                $smarty_config['left_delimiter'] = '<!--{';
                $smarty_config['right_delimiter'] = '}-->';
            }

            $config = \Yaf\Registry::get("config")->toArray();
             
            $config['smarty'] = $smarty_config;
            $config = new \Yaf\Config\Simple($config);
            \Yaf\Registry::set('config', $config);

			$smarty = new \Smarty\Adapter(null, $smarty_config);//print_r($smarty);exit;
            \Yaf\Dispatcher::getInstance()->setView($smarty);
		}
	}

	public function dispatchLoopStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
	}

	public function preDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
	}

	public function postDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
        //删除后台flash
        $module_name = $request->getModuleName();
        if ($module_name == 'Admin') {
            setcookie("flash", '', time() - 3600, '/');
        }
	}

	public function dispatchLoopShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response) {
	}
}
