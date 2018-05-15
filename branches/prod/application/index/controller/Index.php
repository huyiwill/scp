<?php
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Db;
use think\Cache;
use think\Cookie;
use think\Config;

use app\common\model\Organ as OrganModel;
use app\common\model\Examination as ExaminationModel;
use app\common\model\ExaminationResult as ExaminationResultModel;
use app\common\model\DefaultTemplate as DefaultTemplateModel;

class Index extends IndexBase
{
	protected $organ_model,$examination_model;
	protected function _initialize()
    {
        parent::_initialize();
		$this->organ_model = new OrganModel();
		$this->examination_model = new ExaminationModel();
		$this->examination_result_model = new ExaminationResultModel();
		$this->default_template_model = new DefaultTemplateModel();
    }
	
	// 机构学科、年级
    public function index($org_id,$sid = '',$sname = '')
    {
		error_reporting(0);
		if($sid != '' && $sname != ''){
			Cookie::forever('sid',$sid);
			Cookie::forever('sname',$sname);
		}
		$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
		$result = array('pri'=>array('name'=>'小学','grade'=>array()),
						'mid'=>array('name'=>'初中','grade'=>array()),
						'high'=>array('name'=>'高中','grade'=>array()));
		
		$data = array();
		if($org_info)
		{
			$examinations = $this->examination_model->where(array('org_id'=>$org_info['org_id'],'status'=>2))->order('grade asc')->select();
			$Subjects = getSubjectList();
			
			foreach($examinations as $key=>$val)
			{
				$type = '';
				if($val['grade']>=1 &&$val['grade']<=6)
				{
					$type='pri';
				}
				elseif($val['grade']>=7 && $val['grade']<=9)
				{
					$type='mid';
				}
				elseif($val['grade']>=10 && $val['grade']<=12)
				{
					$type='high';
				}
				if(!isset($result[$type]['grade'][getGrade($val['grade'])][getSubject($val['subject'])]['num']))
				{
					$result[$type]['grade'][getGrade($val['grade'])][getSubject($val['subject'])]['name'] = getSubject($val['subject']);
					$result[$type]['grade'][getGrade($val['grade'])][getSubject($val['subject'])]['sid'] = $val['subject'];
					$result[$type]['grade'][getGrade($val['grade'])][getSubject($val['subject'])]['gid'] = $val['grade'];
					$result[$type]['grade'][getGrade($val['grade'])][getSubject($val['subject'])]['num'] = 	1;
					$result[$type]['grade'][getGrade($val['grade'])][getSubject($val['subject'])]['icon'] = $Subjects[$val['subject']]['icon'];
				}
				else
				{
					$result[$type]['grade'][getGrade($val['grade'])][getSubject($val['subject'])]['num']++;
				}
			}
			$data['list'] = $result;
			if($org_info['expiry_time']<time())
			{
				$data['status'] = 2;
				$data['msg'] = '<p>当前机构已过期</p> <p>续费合作请联系软云商务</p> <p>400-182-6266</p>';
			}
			else
			{
				$data['status'] = 1;
				$data['msg'] = '成功';
			}
		}
		else
		{
			$data['status'] = 0;
			$data['msg'] = '不存在该组织机构';
			$data['list'] = $result;
		}
		// echo json_encode($data);
        return $this->fetch('index',['data'=>$data,'org_info'=>$org_info]);
    }
	
	// 机构学科、年级试卷列表
	public function examinations($org_id = 0,$subject_id = 0,$grade_id = 0,$page = 1)
	{
		error_reporting(0);
		$data = array();
		$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
		
		if($org_info['expiry_time'] < time())
		{
			$data['status'] = 2;
			$data['msg'] = '<p>当前机构已过期</p> <p>续费合作请联系软云商务</p> <p>400-182-6266</p>';
		}else{
			if($subject_id && $grade_id)
			{
				$where = array();
				$where['org_id'] = $org_id;
				$where['subject'] = $subject_id;
				$where['grade'] = $grade_id;
				$where['status'] = 2;
				$list = $this->examination_model->field('*')->where($where)->order('id desc')->select();//->paginate(15, false, ['page' => $page]);
				$data['status'] = 1;
				$data['msg'] = '成功';
				$data['list'] = $list;
				$data['grade'] = getGrade($list[0]['grade']);
				$data['subject'] = getStage($list[0]['grade']) . getSubject($list[0]['subject']);
			}
			else
			{
				$data['status'] = 0;
				$data['msg'] = '参数不合法';
			}
		}
		//echo $this->examination_model->getLastSql();
		//echo json_encode($data);
		return $this->fetch('examinations',['data'=>$data,'org_info'=>$org_info]);
	}
	
	// 试卷题目列表
	public function questions($org_id,$examinations_id = 0,$sid = '',$sname = '')
	{
		error_reporting(0);
		if($sid != '' && $sname != ''){
			Cookie::forever('sid',$sid);
			Cookie::forever('sname',$sname);
		}
		$data = array();
		$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
		if($org_info['expiry_time'] < time())
		{
			$data['status'] = 2;
			$data['msg'] = '<p>当前机构已过期</p> <p>续费合作请联系软云商务</p> <p>400-182-6266</p>';
		}else{
			if($examinations_id)
			{
				$examinations_info = $this->examination_model
										  ->field('id,name,exam_num,question_ids,status')
										  ->where(array('id'=>$examinations_id))->find();
				if($examinations_info)
				{
					// 获取魔题库试题信息
					$list = controller('Api/Motiku')->getList($examinations_info['question_ids']);
					$data['list'] = $list['Value']['Questions'];
					$data['exam_num'] = $examinations_info['exam_num'];
					$data['exam_name'] = $examinations_info['name'];
					$data['exam_stauts'] = $examinations_info['status'];
					$data['examinations_id'] = $examinations_id;
					$data['status'] = 1;
					$data['msg'] = '成功';
				}
				else
				{
					$data['status'] = 0;
					$data['msg'] = '不存在试题';
				}
			}
			else
			{
				$data['status'] = 0;
				$data['msg'] = '参数不合法';
			}
		}
		
		return $this->fetch('questions',['data'=>$data,'org_info'=>$org_info]);
		//return $this->fetch();
	}
	
	// 试卷结果信息
	public function result_info()
	{
		$student_id = Cookie::get('sid');
		$student_name = Cookie::get('sname');
		$examinations_id = input('examinations_id');
		$result_str = input('examinations_result');
		$result_str = str_replace('&quot;','"',$result_str);
		$result_list = json_decode($result_str,true);
		$data = array();
		if($examinations_id)
		{
			$examinations_info = $this->examination_model->field('id,grade,subject,org_id')->where(array('id'=>$examinations_id))->find();
			if($examinations_info)
			{
				$org_info = $this->organ_model->field('code')->where(array('org_id'=>$examinations_info['org_id']))->find();
				$post = array();
				$post['SCPOrganizationCode'] = $org_info['code'];
				$post['SCPStudentId'] = $student_id;
				$post['GradeId'] = $examinations_info['grade'];
				$post['StudentQuestions'] = $result_list;
				$list = controller('Api/Motiku')->upanswer($post);
				
				if($list['ApiResultType']==1)
				{
					$question_reult = array();
					foreach($result_list as $k => $v){
						$question_reult[$v['QuestionId']] = $v['StudentAnswer'];
					}
					$rdata['examination_id'] = $examinations_id;
					$rdata['org_id'] = $examinations_info['org_id'];
					$rdata['grade'] = $examinations_info['grade'];
					$rdata['subject'] = $examinations_info['subject'];
					$rdata['s_id'] = $student_id;
					$rdata['s_name'] = $student_name;
					$rdata['question_reult'] = json_encode($question_reult);
					$rdata['result_info'] = json_encode($list);
					$rdata['create_time'] = time();
					$res = $this->examination_result_model->save($rdata);
					$data['status'] = 1;
					$data['msg'] = '成功';
					$data['eid'] = $this->examination_result_model->getLastInsID();
				}
				else
				{
					$data['status'] = 2;
					$data['msg'] = '获取测试结果失败';
				}
				
			}
			else
			{
				$data['status'] = 0;
				$data['msg'] = '不存在试题';
			}
		}
		else
		{
			$data['status'] = 0;
			$data['msg'] = '参数不合法';
		}
		
		echo json_encode($data);
	}
	
	public function exam_result($eid){
		error_reporting(0);
		$examInfo = $this->examination_result_model->field('*')->where('id',$eid)->find();
		$org_info = $this->organ_model->where('org_id',$examInfo['org_id'])->find();
		
		$from = input('from');
		if($from != ''){
			$this->redirect('index/index/questions',['org_id'=>$org_info['org_id'],'examinations_id'=>$examInfo['examination_id']]);
		}
		
		$exam = $this->examination_model->field('*')->where('id',$examInfo['examination_id'])->find();
		$data = array();
		$result_info = json_decode($examInfo['result_info'],true);
		$result_info = $result_info['Value'];
		$data['examinations_id'] = $exam['id'];
		$data['total_sub_num'] = $exam['exam_num'];
		$data['correct_sub_num'] = 0;
		$data['CapabilityScores'] = $result_info['CapabilityScores'];
		$data['KnowledgePointScores'] = $result_info['KnowledgePointScores'];
		$data['exam_name'] = $exam['name'];
		$data['temp'] = (time() - $org_info['create_time']) < 3*30*86400 ? $exam['template'] : 1;
		$question_ids = '';
		
		foreach($result_info['QuestionResult'] as $k => $v){
			if($v['IsCorrect']){
				$data['correct_sub_num'] = $data['correct_sub_num'] + 1;
			}
			if($k == 0){
				$question_ids = $v['QuestionId'];
			}else{
				$question_ids .= ','.$v['QuestionId'];
			}
		}
		usort($result_info['KnowledgePointScores'],"best_sort");
		$capLen = count($result_info['KnowledgePointScores']);
		$data['best'] = array('name'=>$result_info['KnowledgePointScores'][$capLen-1]['KnowledgePointName'],
							  'ratio'=>floor($result_info['KnowledgePointScores'][$capLen-1]['Score']));
		$data['lift'] = array('name'=>$result_info['KnowledgePointScores'][0]['KnowledgePointName'],
							  'ratio'=>floor($result_info['KnowledgePointScores'][0]['Score']));
		$data['lift2'] = array('name'=>$result_info['KnowledgePointScores'][1]['KnowledgePointName'],
							  'ratio'=>floor($result_info['KnowledgePointScores'][2]['Score']));
		
		if($org_info['level'] == 4){
			if($exam['is_part']){
				$pub_number =  floor($result_info['CourseScore']/25)+1;
				$pub_name = 'pub_text'.$pub_number;
				$pub_text = $exam[$pub_name];
			}else{
				$pub_text = $exam['pub_text'];
			}
		}else{
			$pub_text = $exam['pub_text'];
		}
		
		$data['organ'] = array('name'=>$org_info['name'],'logo'=>$org_info['logo'],'pub_text'=>$pub_text,'mobile'=>$org_info['mobile']);
		$examList = controller('Api/Motiku')->getList($question_ids);
		$data['examList'] = array();
		$my_answer =  explode(',',$examInfo);
		$myAnswerArr = json_decode($examInfo['question_reult'],true);
		
		foreach($examList['Value']['Questions'] as $k => $v){
			$exam_arr = array();
			$exam_arr['QuestionId'] = $v['QuestionId'];
			$exam_arr['Answer'] = $v['Answer'];
			$exam_arr['QuestionContent'] = $v['QuestionContent'];
			$exam_arr['Options'] = $v['Options'];
			foreach($myAnswerArr as $k2 => $v2){
				if($k2 == $v['QuestionId']){
					$exam_arr['myAnswer'] = $v2 ? $v2 : '未填';
					$exam_arr['IsCorrect'] = $v2 == $v['Answer'] ? true : false;
				}
			}
			array_push($data['examList'],$exam_arr);
		}
		
		return $this->fetch('result_info',['data'=>$data,'org_info'=>$org_info]);
	}
	
}