<?php
namespace app\common\controller;

use org\Auth;
use think\Loader;
use think\Cache;
use think\Controller;
use think\Db;
use think\Session;

/**
 * 后台公用基础控制器
 * Class OrganBase
 * @package app\common\controller
 */
class IndexBase extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
		
    }
}