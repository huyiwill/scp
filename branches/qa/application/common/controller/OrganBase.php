<?php
namespace app\common\controller;

use org\Auth;
use think\Loader;
use think\Cache;
use think\Controller;
use think\Db;
use think\Cookie;
use think\Config;

/**
 * 后台公用基础控制器
 * Class OrganBase
 * @package app\common\controller
 */
class OrganBase extends Controller
{
	protected $organ_id,$organ_name,$level_name;
    protected function _initialize()
    {
        parent::_initialize();
		$this->isLogin();
		$this->getConfig();
		
        // 输出当前请求控制器（配合后台侧边菜单选中状态）
		$organInfo = Db::name('organ')->field('*')->where('org_id', $this->organ_id)->find();
		$this->organ_name = $organInfo['name'];
		$this->level_name = getLevelName($organInfo['level']);
		
		$this->assign('organ_id',$this->organ_id);
		$this->assign('organ_name',$this->organ_name);
		$this->assign('level_name',$this->level_name);
        $this->assign('controller', Loader::parseName($this->request->controller()));
    }

    /**
     * 机构信息
     * @return bool
     */
    protected function isLogin()
    {
		$token = input('token');
		if($token){
			$this->redirect('orgAdmin/login/index',['token'=>$token]);
		}else{
			$this->init();
		}
    }
	
	function init(){
		if(Cookie::has('u')){
			$u = Cookie::get('u');
			$key = 'VWM3ME0P';
			$vi =  '3KM7WSU2';
			$event = controller('orgAdmin/Login', 'controller');
			$info = $event->decrypt($u,base64_encode($key),base64_encode($vi));
			$info_arr = explode('|',$info);
			if(count($info_arr) == 3){
				if(strtotime($info_arr[2]) < time()){
					//cookie过期
					Cookie::set('u',null);
					$this->redirect('orgAdmin/Login/noLogin');
				}else{
					$this->organ_id = $info_arr[0];
					$this->organ_name = $info_arr[1];
					$this->assign('organ_name',$this->organ_name);
					$this->assign('organ_id',$this->organ_id);
				}
			}else{
				Cookie::set('u',null);
				$this->redirect('orgAdmin/Login/noLogin');
			}
		}else{
			Cookie::set('u',null);
			$this->redirect('orgAdmin/Login/noLogin');
		}
	}
	
	protected function editOrgName($name){
		$this->organ_name = $name;
		$this->assign('organ_name',$this->organ_name);
	}
	
	/*
	获取系统配置
	*/
	protected function get_config()
	{
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		return $site_config;
	}
	
	/*
	获取配置项信息
	*/
	protected function getConfig()
	{
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		// 系统配置项
		Config::set($site_config);
		$this->assign('site_config', $site_config);
	}
}