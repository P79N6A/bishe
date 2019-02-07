<?php
use App\Controllers\Controller;

/**
 * Class IndexController
 */
class IndexController extends Controller
{

    public function indexAction()
    {
       echo 123333333333;
       $dict = \Yaf\Registry::get('dict')->toArray();
       p($dict);

        return false;
    }


}