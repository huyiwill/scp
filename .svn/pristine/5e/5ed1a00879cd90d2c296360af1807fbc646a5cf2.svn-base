<?php
namespace app\orgadmin\controller;

use app\common\controller\OrganBase;
use think\Cache;
use think\Session;
use think\Cookie;
use think\Config;
use think\Db;
use app\common\model\Organ as OrganModel;

class Index extends OrganBase
{
	protected $organ_model;

    protected function _initialize()
    {
        parent::_initialize();
		$this->organ_model = new OrganModel();
    }
	
    public function index()
    {
		$organ_id = $this->organ_id;
		
		$organInfo = $this->organ_model->where('org_id',$organ_id)->find();
		$organInfo['level_name'] = getLevelName($organInfo['level']);
		$organInfo['expiry_time_text'] = date('Y-m-d',$organInfo['expiry_time']);
		$organInfo['auth'] = getAuth($this->level);

		$this->assign('activeid','orgadmin-cog');
        return $this->fetch('index',['data'=>$organInfo]);
	}
	
	/**
     * 编辑机构
     * @param $id
     * @return mixed
     */
    public function edit()
    {
		if ($this->request->isPost()) {
            $data		= $this->request->post();
			$organInfo 	= $this->organ_model->where('id',$data['id'])->find();
			$auth 		= getAuth($this->level);
			$info		= array();
			$info['id'] = $data['id'];
			$info['name'] = $data['name'];
			$info['mobile'] = $data['mobile'];
			$info['pub_text'] = $data['pub_text'];
			$info['update_time'] = time();
			
			if($auth['edit_logo']){
				$info['logo'] = $data['logo'];
			}
			
			if ($this->organ_model->update($info) !== false) {
				$this->success('更新成功');
			} else {
				$this->error('更新失败');
			}
        }
    }
}
