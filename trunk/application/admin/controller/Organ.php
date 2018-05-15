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
class Organ extends AdminBase
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
     * 机构管理
     * @param string $keyword
     * @param int    $page
     * @return mixed
     */
    public function index($level = 0, $validity = 0, $page = 1)
    {
        $map['parent_id'] = 0;
        if ($level) {
            $map['level'] = $level;
        }
		if ($validity == 1) {
            $map['expiry_time'] = array('GT',time());
        }else if($validity == 2){
			$map['expiry_time'] = array('LT',time());
		}
		
        $list = $this->organ_model->where($map)->order('id DESC')->paginate(15, false, ['page' => $page,'query' => request()->param()]);
		foreach($list as $k=>$v){
			$list[$k]['level_name'] = getLevelName($v['level']);
			$list[$k]['create_time_text'] = date('Y-m-d H:i:s',$v['create_time']);
			$ntime = time();
			$list[$k]['validity'] = $v['expiry_time'] > $ntime ? 1 : 0;
			$list[$k]['expiry_time_text'] = getPeriodValidity($v['create_time'],$v['expiry_time']);
		}
		$level_list = getLevel();

        return $this->fetch('index', ['list'=>$list,'level_list'=>$level_list,'level'=>$level,'validity'=>$validity]);
    }

    /**
     * 添加机构
     * @return mixed
     */
    public function add()
    {
		if ($this->request->isPost()) {
            $data           = $this->request->post();
			$organ = $this->organ_model->where('org_id',$data['org_id'])->find();
			
			if($organ){
				echo json_encode(array('code'=>2,'msg'=>'保存失败，当前机构已存在'));
			}else{
				$data['logo'] 		 = $data['logo'] != '' ? ''.$data['logo'] : '/public/uploads/default-logo.png';
				$data['code'] = $data['org_id'];
				$data['expiry_time'] = strtotime($data['expiry_time']);

				if ($this->organ_model->allowField(true)->save($data) !== false) {
					$this->success('保存成功',url('admin/Organ/index'));
				} else {
					$this->error('保存失败');
				}
			}
        }else{
			$level_list = getLevel();
			return $this->fetch('add',['level_list'=>$level_list]);
		}
    }

    /**
     * 编辑机构
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
		if ($this->request->isPost()) {
            $data			= $this->request->post();
			$organ = $this->organ_model->where('org_id',$data['org_id'])->find();
			
			if($organ && $organ['id'] != $data['id']){
				echo json_encode(array('code'=>2,'msg'=>'保存失败，当前机构已存在'));
			}else{
				$data['logo'] 		 = $data['logo'] != '' ? $data['logo'] : '/public/uploads/default-logo.png';
				$data['expiry_time'] = strtotime($data['expiry_time']);
				$data['update_time'] = time();
				
				if ($this->organ_model->update($data) !== false) {
					$this->success('更新成功');
				} else {
					$this->error('更新失败');
				}
			}
        }else{
			$data = $this->organ_model->find($id);
			$data['expiry_time'] = date('Y-m-d',$data['expiry_time']);
			$level_list = getLevel();
			return $this->fetch('edit', ['data' => $data,'level_list'=>$level_list]);
		}
    }

    /**
     * 删除机构
     * @param $id
     */
    public function delete($id)
    {
		$organ_info = $this->organ_model->where('id',$id)->find();
        if ($this->organ_model->destroy($id)) {
			$this->organ_model->where('code',$organ_info['org_id'])->delete();
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}