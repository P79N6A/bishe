<?php
namespace App\Tool\Controllers;

class Controller extends \Yaf\Controller_Abstract
{
    private $session;
    protected $session_id;

    /**
     * [init 后台初始化，session]
     * @Author   zlc
     * @DateTime 2016-11-09
     * @return   [type]     [description]
     */
    public function init()
    {
        // $this->access();//登录的时候判断是否登录的楼盘库后台

        // $redisHost=\Yaf\Registry::get('CliConfig')->get('redis')->get('queue')->toarray();
        // //如果未修改php.ini下面两行注释去掉
        // ini_set('session.save_handler', 'redis');
        // ini_set('session.save_path', 'tcp://'.$redisHost['host'].':'.$redisHost['port']);
        // ini_set('default_socket_timeout', -1);
        // // ini_set('session.gc_maxlifetime', "14400"); // 秒
        // // ini_set("session.cookie_lifetime","14400"); // 秒
        // session_start();
        // $redis = new \redis();
        // $redis->connect($redisHost['host'], $redisHost['port']);
        // $session_id=session_id();
        // $post_sessionid=input('post.sessionid');
        // $this->session_id=!empty($post_sessionid)?$post_sessionid:$session_id;
        // //redis用session_id作为key并且是以string的形式存储
        // $this->session= $redis->get('PHPREDIS_SESSION:' . $this->session_id);

        // if(empty($this->session)){
        //     //获取当前模块
        //     $dispatcher = \Yaf\Dispatcher::getInstance();
        //     $arrRequest = $dispatcher->getRequest();
        //     $module=$arrRequest->module;
        //     $controller=$arrRequest->controller;
        //     $action=$arrRequest->action;
        //     $url=$module.'/'.$controller.'/'.$action;
        //    // p($url);exit;
        //     if($url!='Tool/Manage/login'){
        //         echo "跳转";
        //         Header("Location: http://".$_SERVER['SERVER_NAME']."/index.php/tool/Manage/login"); 
        //         exit;
        //     }
        // }
        

    }

    /**
     * [access 登录的时候判断是否登录的楼盘库后台]
     * @Author   zlc
     * @DateTime 2017-01-10
     * @return   [type]     [description]
     */
    private function access()
    {

        if ( $_SERVER['SERVER_NAME'] != URL_ADMIN_INDEX) {
            Header("Location: http://".URL_ADMIN_INDEX."/".$_SERVER['REQUEST_URI']); 
            exit;
        }

            $dispatcher = \Yaf\Dispatcher::getInstance();
            $arrRequest = $dispatcher->getRequest();
            $controller=$arrRequest->controller;
            $action=$arrRequest->action;

        if ($controller == 'Manage' && $action == 'login') {
            $this->cfg['userinfo'] = \App\Admin\Models\NewhouseMembers::getInstance()->getUserCookie();

            if (empty($this->cfg['userinfo']['sinauid']) || empty($this->cfg['userinfo']['username']) || empty($this->cfg['userinfo']['group_id']))
            {
                   $this->redirect('http://'.URL_ADMIN_LOGIN);
                   
            }
        }

    }


}