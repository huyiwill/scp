<?php
namespace app\admin\controller;

use app\common\model\User as UserModel;
use app\common\model\Organ as OrganModel;
use app\common\model\Examination as ExaminationModel;
use app\common\model\DefaultTemplate as DefaultTemplateModel;
use app\common\controller\AdminBase;
use think\Config;
use think\Db;

/**
 * 机构管理
 * Class AdminUser
 * @package app\admin\controller
 */
class DefaultTemplate extends AdminBase
{
    protected $user_model,$organ_model,$examination_model,$default_template_model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->user_model = new UserModel();
		$this->organ_model = new OrganModel();
		$this->examination_model = new ExaminationModel();
		$this->default_template_model = new DefaultTemplateModel();
    }

    /**
     * 默认模版
     */
    public function index($level = 0, $validity = 0, $page = 1)
    {
        $data = $this->default_template_model->where('status',1)->find();
        return $this->fetch('index', ['data'=>$data]);
    }

    /**
     * 编辑模版
     * @param $id
     * @return mixed
     */
    public function edit()
    {
		if ($this->request->isPost()) {
            $data			= $this->request->post();
			
			if ($this->default_template_model->update($data) !== false) {
				$this->success('更新成功');
			} else {
				$this->error('更新失败');
			}
        }
    }
}