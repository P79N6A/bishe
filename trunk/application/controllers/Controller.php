<?php
namespace App\Controllers;
use App\Models\Business;
use App\Models\City;
use App\Models\House;
use App\Models\OtherInfo;

/**
 * 楼盘前台页的基础控制器
 * @author chenchen16@leju.com
 * @date 2016/08/10
 */
class Controller extends \Yaf\Controller_Abstract
{
    /** @var string smarty的cache_id */
    protected $cache_id = '';
    /** @var string 模板名称 */
    protected $template_name = '';
    /**@var string stie*/
    private $city_en;
    /**@var string stie*/
    private $options;
    /**
     * 初始化
     * @author chenchen16@leju.com
     * @date 2016/11/4
     */
    public function init()
    {
        /* 关闭自动渲染 使用display渲染*/
        \Yaf\Dispatcher::getInstance()->disableView();

        $this->initSpider();
        $this->initParams();
     
    }


    /**
     * 简单的反爬虫策略,注：谷歌或百度的爬虫不会被禁止
     * @author chenchen16@leju.com
     * @date 2017/1/4
     */
    public function initSpider()
    {
        if (isset($_GET['clears']) && $_GET['clears'] == '1') {//后台get请求清理缓存的除外
            return;
        }

        //获取UA信息
        $ua = $_SERVER['HTTP_USER_AGENT'];

        //将恶意USER_AGENT存入数组
        $malicious_ua = array('FeedDemon','CrawlDaddy','Java','Feedly','UniversalFeedParser','ApacheBench','Swiftbot','ZmEu','Indy Library','oBot','jaunty','YandexBot','AhrefsBot','MJ12bot','WinHttp','EasouSpider','HttpClient','Microsoft URL Control','YYSpider','jaunty','Python-urllib','lightDeckReports Bot');
        //禁止空USER_AGENT，dedecms等主流采集程序都是空USER_AGENT，部分sql注入工具也是空USER_AGENT
        if (!$ua) {
            header("http/1.1 403 Forbidden");
            exit();
        } else {
            if (strpos($ua, 'BOT/0.1 (BOT for JCE)') !== false) {//BOT/0.1 (BOT for JCE)用正则有问题，暂时先用strpos
                header("http/1.1 403 Forbidden");
                exit();
            }

            foreach($malicious_ua as $value) {
                //判断是否是数组中存在的UA
                if (preg_match('/'.$value.'/i', $ua)) {
                    header("http/1.1 403 Forbidden");
                    exit();
                }
            }
        }
    }

    /**
     * 过滤参数
     * @author chenchen16@leju.com
     * @date 2016/11/4
     */
    private function initParams()
    {
        //参数过滤
        $params = $this->getRequest()->getParams();
        foreach ($params as $param_name => $param_value) {
            $this->getRequest()->setParam($param_name, filter($param_value));
        }
    }
   

    /**
     * @param int $current_page integer 当前页
     * @param $per_page integer 每页数量
     * @param $total_count integer 总数
     * @param $url_prefix string url前缀
     * @param $url_suffix string url后缀
     * @return string
     * @author weidi@leju.com
     * @modify by chenchen16@leju.com
     * @date 2016/11/21
     */
    public function page($current_page, $per_page, $total_count, $url_prefix, $url_suffix = '', $is_show_total_count = true, $class = 'btn', $current_class = 'btn cur'){
        $str = '';

        if ($total_count <= $per_page) {
            return $str;
        }


        $total_page = ceil($total_count / $per_page);

        if ($current_page < 1) {
            $current_page = 1;
        }

        if ($current_page > $total_page) {
            $current_page = $total_page;
        }

        $pre_page = $current_page - 1;
        $next_page = $current_page + 1;

        if ($pre_page < 1) {
            $pre_page = 1;
        }

        if ($next_page > $total_page) {
            $next_page = $total_page;
        }

        if ($total_page > 10 && $current_page != 1) {
            $str .= '<a href="' . $url_prefix . 1 . $url_suffix . '"' . ' class="'.$class.'">首页</a>';
        }

        if ($current_page != 1) {
            $str .= '<a href="' . $url_prefix . $pre_page . $url_suffix . '"' . ' class="pre"><  上一页</a>';
        }

        for ($i = 1; $i <= $total_page; $i++) {
            if ($i == $current_page) {
                $str .= '<a href="' . $url_prefix . $i . $url_suffix . '"' . ' class="'.$current_class.'">' . $i . '</a>';
            } else {
                if ($total_page > 10) {
                    if ($current_page <= 5) {
                        if ($i >= 10) {
                            $str .= '<em>...</em>';
                            break;
                        } else {
                            $str .= '<a href="' . $url_prefix . $i . $url_suffix . '"' . ' class="'.$class.'">' . $i . '</a>';
                        }
                    } else {
                        if (abs($i - $current_page) == 5) {
                            $str .= '<em>...</em>';
                        }

                        if (abs($i - $current_page) < 5) {
                            $str .= '<a href="' . $url_prefix . $i . $url_suffix . '"' . ' class="'.$class.'">' . $i . '</a>';
                        }

                        if ($i - $current_page > 4) {
                            break;
                        }
                    }
                } else {
                    $str .= '<a href="' . $url_prefix . $i . $url_suffix . '"' . ' class="'.$class.'">' . $i . '</a>';
                }
            }
        }

        if ($current_page != $total_page) {
            $str .= '<a href="'.$url_prefix . $next_page .  $url_suffix . '"' . ' class="next">下一页  ></a>';
        }

        if ($total_page > 10 && $current_page != $total_page) {
            $str .= '<a href="' . $url_prefix . $total_page . $url_suffix . '"' . ' class="'.$class.'">尾页</a>';
        }

        if ($is_show_total_count) {
            $str .='<span>共' . $total_count . '条记录</span>';
        }

        return $str;
    }

}