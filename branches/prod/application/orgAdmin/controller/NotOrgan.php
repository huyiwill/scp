<?php
namespace app\orgAdmin\controller;

use think\Controller;
use think\Cache;
use think\Session;
use think\Config;
use think\Db;

class NotOrgan extends Controller
{
    public function index($type)
    {
		return $this->fetch('index', ['type' => $type]);
	}
}
