<?php
namespace app\orgAdmin\controller;

use app\common\controller\OrganBase;
use think\Cache;
use think\Cookie;
use think\Config;
use think\Db;
use app\common\model\Organ as OrganModel;
use app\common\model\Examination as ExaminationModel;
use app\common\model\DefaultTemplate as DefaultTemplateModel;

class Examination extends OrganBase
{
	protected $organ_model,$examination_model,$default_template_model;

    protected function _initialize()
    {
        parent::_initialize();
		$this->organ_model = new OrganModel();
		$this->examination_model = new ExaminationModel();
		$this->default_template_model = new DefaultTemplateModel();
    }
	
    public function index($grade = '', $subject = '', $status = '', $page = 1)
    {
		$map = [];
		$organ_id = $this->organ_id;
		$map['org_id'] = $organ_id;
		$map['status'] = array('neq',4);
		
        if ($grade) {
            $map['grade'] = $grade;
        }
		if ($subject) {
            $map['subject'] = $subject;
        }
		if ($status) {
            $map['status'] = $status;
        }
		
        $list = $this->examination_model->where($map)->order('id DESC')->paginate(15, false, ['page' => $page,'query' => request()->param()]);

		$grade_list = getGradeList();
		$subject_list = getSubjectList();
		$status_list = getStatusList();
		$organInfo = $this->organ_model->where('org_id',$organ_id)->find(); 
		$organ_level_name = getLevelName($organInfo['level']);
		
		foreach($list as $k=>$v){
			$list[$k]['grade_name'] = getGrade($v['grade']);
			$list[$k]['subject_name'] = $subject_list[$v['subject']]['stageName'].$subject_list[$v['subject']]['name'];
			$list[$k]['status_name'] = getStatus($v['status']);
			$list[$k]['create_time_text'] = date('Y-m-d H:i',$v['create_time']);
		}
		
        return $this->fetch('index', ['list'=>$list,'organ_level'=>$organInfo['level'],'organ_level_name'=>$organ_level_name,'grade'=>$grade,'grade_list'=>$grade_list,'subject'=>$subject,'subject_list'=>$subject_list,'grade_list_json'=>json_encode($grade_list),'status'=>$status,'status_list'=>$status_list]);
	}
	
	/**
     * 获取题集
     * @return mixed
     */
    public function getExam($id)
	{
		$info = Db::name('examination')->where('id',$id)->find();
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		if($info){
			$organ_id = $this->organ_id;
			$organInfo = $this->organ_model->where('org_id',$organ_id)->find();
			$def_tmp = $organInfo['pub_text'] ? $organInfo['pub_text'] : '';
			$auth = getAuth($organInfo['level']);
			$templateList = getTemplateList();
			$levelList = getLevel();
			if($info['qrhost'] != $site_config['site_url']){
				$url = $site_config['site_url'].'/index.php/index/index/questions?org_id='.$organ_id.'&examinations_id='.$info['id'];
				$id = $info['id'];
				$qrurl = $this->getQrcode($url,$id);
				$motiku = controller('Api/Motiku', 'controller');
				//var_dump($qrurl);
				$res = $motiku->upload($qrurl);
				if($res['ApiResultType'] == 1){
					$info['qrcode'] = $res['Value'];
					$info['qrhost'] = $site_config['site_url'];
					$this->examination_model->update($info);
				}
			}
			$info['exam_url'] = $site_config['site_url'].'/index.php/index/index/questions?org_id='.$organ_id.'&examinations_id='.$info['id'];
			echo json_encode(array('code'=>1,'info'=>$info,'organInfo'=>$organInfo,'def_tmp'=>$def_tmp,'auth'=>$auth,'levelList'=>$levelList,'templateList'=>$templateList));
		}else{
			echo json_encode(array('code'=>2,'msg'=>'题集不存在'));
		}
	}
	
		/**
     * 获取机构
     * @return mixed
     */
    public function getUserExam($id)
	{
		
		$info = Db::name('organ')->where('org_id',$id)->find();
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		if($info){
			if($info['qrhost'] != $site_config['site_url']){
				$url = $site_config['site_url'].'/index.php/index/index/index?org_id='.$info['org_id'];
				$id = $info['org_id'];
				$qrurl = $this->getQrcode($url,$id);
				$motiku = controller('Api/Motiku', 'controller');
				//var_dump($qrurl);
				$res = $motiku->upload($qrurl);
				if($res['ApiResultType'] == 1){
					$info['qrcode'] = $res['Value'];
					$info['qrhost'] = $site_config['site_url'];
					$this->organ_model->update($info);
				}
			}
			$info['exam_url'] = $site_config['site_url'].'/index.php/index/index/index?org_id='.$info['org_id'];
			echo json_encode(array('code'=>1,'info'=>$info));
		}else{
			echo json_encode(array('code'=>2,'msg'=>'机构不存在'));
		}
	}
	
	/**
     * 添加题集
     * @return mixed
     */
    public function add()
    {
		$organ_id = $this->organ_id;
		$organInfo = $this->organ_model->where('org_id',$organ_id)->find();
		$auth = getAuth($organInfo['level']);
		if ($this->request->isPost()) {
			$data= $this->request->post();
			$newdata = array();
			$i = 0;
			foreach($data['data'] as $k=>$v){
				$newdata[$i]['org_id'] = $organ_id;
				$newdata[$i]['create_time'] = time();
				$newdata[$i]['update_time'] = time();
				$newdata[$i]['name'] = $v['name'];
				$newdata[$i]['subject'] = $v['subject'];
				$newdata[$i]['exam_url'] = $v['exam_url'];
				$newdata[$i]['ryun_url'] = $v['ryun_url'];
				$newdata[$i]['grade'] = $v['grade'];
				$newdata[$i]['exam_num'] = $v['exam_num'];
				$newdata[$i]['question_ids'] = $v['question_ids'];
				$newdata[$i]['pub_text'] = $organInfo['pub_text'] ? $organInfo['pub_text'] : '';
				$i++;
			}
			if ($this->examination_model->saveAll($newdata) !== false) {
				//echo $this->examination_model->getLastSql();
				$this->success('添加成功');
			} else {
				$this->error('添加失败');
			}
		}
	}
	
	/**
     * 编辑题集
     * @param $id
     * @return mixed
     */
    public function edit()
    {
		if ($this->request->isPost()) {
            $data			= $this->request->post();
			$organ_id = $this->organ_id;
			$organInfo = $this->organ_model->where('org_id',$organ_id)->find();
			$auth = getAuth($organInfo['level']);
			$def_tmp = $organInfo['pub_text'] ? $organInfo['pub_text'] : '';
			$info = array();
			$info['id'] = $data['id'];
			$info['template'] = $data['template'];
			$info['is_part'] = $data['is_part'];
			$info['update_time'] = time();
			
			if($organInfo['level'] == 4){
				if($data['is_part'] == 1){
					$info['pub_text'] = '';
					$info['pub_text1'] = $data['pub_text1'];
					$info['pub_text2'] = $data['pub_text2'];
					$info['pub_text3'] = $data['pub_text3'];
					$info['pub_text4'] = $data['pub_text4'];
				}else{
					$info['pub_text'] = $data['pub_text1'];
					$info['pub_text1'] = '';
					$info['pub_text2'] = '';
					$info['pub_text3'] = '';
					$info['pub_text4'] = '';
				}
			}else{
				if($organInfo['level'] == 3){
					$info['pub_text'] = $data['pub_text1'];
				}else{
					$info['pub_text'] = $def_tmp;
				}
				$info['pub_text1'] = '';
				$info['pub_text2'] = '';
				$info['pub_text3'] = '';
				$info['pub_text4'] = '';
			}
			
			if ($this->examination_model->update($info) !== false) {
				$this->success('设置成功');
			} else {
				$this->error('设置失败');
			}
        }
    }
	
	/*生成二维码*/
	public function getQrcode($url,$id){
		$time = time();
		$code = md5($id.$time);
		$value = $url;
		$errorCorrectionLevel = 'L';//容错级别 
		$matrixPointSize = 6;//生成图片大小 
		vendor('phpqrcode.phpqrcode');
		//生成二维码图片 
		 \QRcode::png($value, 'public/uploads/QRcodeImage/'.$code.'.png', $errorCorrectionLevel, $matrixPointSize, 2); 
		//$logo = 'http://'.$_SERVER['HTTP_HOST'].'/Uploads/2016-12-28/thumb_58638fbb4affe.jpg';//准备好的logo图片 
		return '/uploads/QRcodeImage/'.$code.'.png';//已经生成的原始二维码图 
	}
	/**
     * 上架
     * @param $id
     */
    public function using($id)
    {
		$info = array();
		$info['id'] = $id;
		$info['status'] = 2;
		
        if ($this->examination_model->update($info) !== false) {
            $this->success('上架成功');
        } else {
            $this->error('上架失败');
        }
    }
	
	/**
     * 下架
     * @param $id
     */
    public function nonuse($id)
    {
		$info = array();
		$info['id'] = $id;
		$info['status'] = 3;
		
        if ($this->examination_model->update($info) !== false) {
            $this->success('下架成功');
        } else {
            $this->error('下架失败');
        }
    }
	
	/**
     * 删除题集
     * @param $id
     */
    public function delete($id)
    {
		$info = array();
		$info['id'] = $id;
		$info['status'] = 4;
		
        if ($this->examination_model->update($info) !== false) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
