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
	protected $organ_model,$examination_model,$isweixin,$wdata;
	protected function _initialize()
    {
        parent::_initialize();
		$this->organ_model = new OrganModel();
		$this->examination_model = new ExaminationModel();
		$this->examination_result_model = new ExaminationResultModel();
		$this->default_template_model = new DefaultTemplateModel();
		$this->isweixin = isweixin();
		$this->assign('isweixin',$this->isweixin);
    }
	
	private function wxauth($org_id){
	  if ( !$org_id ) return false;
	  $data = Db::name('organ')->field('pub_text,name,logo')->where('org_id',$org_id)->find();
	  $des  = strip_tags(htmlspecialchars_decode($data['pub_text']));
	  $url  = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	  $sign = false;
	  $iswx = $this->isweixin;
	  if ( !$iswx ) return array('des'=>'','url'=>'','title'=>'','wxpic'=>'','sign'=>'');
	  $sign = controller('api/Motiku')->getsign($url);
	  if ( $sign['ApiResultType'] == 1 ) {
		$sign = $sign['Value'];
	  } else {
	    $sign = false;
	  }
	  $title  = $data['name'];
	  $wxpic  = ( $data['logo'] != '' ) ? $data['logo'] : 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
	  if(strpos($wxpic,'http')===false){
		 $wxpic= 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
	  }
	  $wdata  = array('des'=>$des,'url'=>$url,'title'=>$title,'wxpic'=>$wxpic,'sign'=>$sign);
	  return $wdata;
	}

	// 机构学科、年级
    public function index($org_id = 0,$sid = '',$sname = '')
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
			
			if ( $org_info['parent_id']  ) {
			  $pinfo = Db::name('organ')->field('expiry_time')->where("id",$org_info['parent_id'])->find();
			  if ( $pinfo ) $org_info['expiry_time'] = $pinfo['expiry_time'];
			}
			
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
		$des   =  strip_tags(htmlspecialchars_decode($org_info['pub_text']));
		$url   = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$title = $org_info['name'];
		$sign  = false;
		if ( $data['status'] == 1 ) {
			if ( $this->isweixin ) {
			  $sign = controller('api/Motiku')->getsign($url);
			  if ( $sign['ApiResultType'] == 1 ) {
				$sign = $sign['Value'];
			  } else {
				$sign = false;
			  }
			}
		}
		$wxpic = ( $org_info['logo'] != '' ) ? $org_info['logo'] : 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
		if(strpos($wxpic,'http')===false){
		  $wxpic = 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
		}
        return $this->fetch('index',['data'=>$data,'sign'=>$sign,'url'=>$url,'title'=>$title,'des'=>$des,'wxpic'=>$wxpic,'org_info'=>$org_info]);
    }
	
	// 机构学科、年级试卷列表
	public function examinations($org_id = 0,$subject_id = 0,$grade_id = 0,$page = 1)
	{
		error_reporting(0);
		$data = array();
		$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
		if ( $org_info['parent_id']  ) {
		  $pinfo = Db::name('organ')->field('expiry_time')->where("id",$org_info['parent_id'])->find();
		  if ( $pinfo ) $org_info['expiry_time'] = $pinfo['expiry_time'];
		}
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
		$wxauth = $this->wxauth($org_id);
		$data['exam_name'] = '';
		return $this->fetch('examinations',['data'=>$data,'org_info'=>$org_info,'sign'=>$wxauth['sign'],'wxpic'=>$wxauth['wxpic'],'title'=>$wxauth['title'],'des'=>$wxauth['des'],'url'=>$wxauth['url']]);
	}
	
	// 试卷题目列表
	public function questions($org_id = 0,$examinations_id = 0,$sid = '',$sname = '')
	{
		error_reporting(0);
		$answer_name = Cookie::get('answer_name');
		$answer_mobile = Cookie::get('answer_mobile');
		$eid = Cookie::get('answer_id_'.$org_id.'_'.$examinations_id);
		$isrepeat = input('isrepeat',0,'intval');
		
		
		
		if($sid != '' && $sname != ''){
			Cookie::forever('sid',$sid);
			Cookie::forever('sname',$sname);
		}
		
		if ( $eid && !$isrepeat ) {
	      $this->redirect('index/index/exam_result',['eid'=>$eid,'org_id'=>$org_id]);
		}
		
		$data = array();
		$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
		$des      =  strip_tags(htmlspecialchars_decode($org_info['pub_text']));
		
		if ( $org_info['parent_id']  ) {
		  $pinfo = Db::name('organ')->field('expiry_time')->where("id",$org_info['parent_id'])->find();
		  if ( $pinfo ) $org_info['expiry_time'] = $pinfo['expiry_time'];
		}
		if($org_info['expiry_time'] < time())
		{
			$data['status'] = 2;
			$data['msg'] = '<p>当前机构已过期</p> <p>续费合作请联系软云商务</p> <p>400-182-6266</p>';
		}else{
			if($examinations_id)
			{
				$examinations_info = $this->examination_model
										  ->field('id,name,exam_num,question_ids,status,is_verify,template,sharepic')
										  ->where(array('id'=>$examinations_id,'org_id'=>$org_id))->find();
				//echo $this->examination_model->getLastSql();
				if($examinations_info)
				{
					// 获取魔题库试题信息
					$list = controller('api/Motiku')->getList($examinations_info['question_ids']);  
					$data['list'] = $list['Value']['Questions'];
					$data['exam_num'] = $examinations_info['exam_num'];
					$data['exam_name'] = $examinations_info['name'];
					$data['exam_stauts'] = $examinations_info['status'];
					$data['examinations_id'] = $examinations_id;
					$data['sharepic']  = $examinations_info['sharepic'];
					$data['is_verify'] = $examinations_info['is_verify'];
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
		
		//echo $data['is_verify'];
		
		$tpl   = 'questions';
		$url   = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$title = $data['exam_name'];
		$des   = ($des!='') ? $des : $data['exam_name'];
		$sign  = false;
		if ( $this->isweixin ) {
		  $sign = controller('api/Motiku')->getsign($url);
		  if ( $sign['ApiResultType'] == 1 ) {
		    $sign = $sign['Value'];
		  } else {
		    $sign = false;
		  }
		}
		
		if ( $examinations_info['template'] == 5 )  $tpl = 'webquest';
		if ( $data['sharepic'] == '' ) $data['sharepic'] = 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
		if(strpos($data['sharepic'],'http')===false){
		  $data['sharepic'] = 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
		}
		return $this->fetch($tpl,['data'=>$data,'sign'=>$sign,'url'=>$url,'title'=>$title,'des'=>$des,'wxpic'=>$data['sharepic'],'org_info'=>$org_info,'answer_name'=>$answer_name,'answer_mobile'=>$answer_mobile]);
	}
	
	public function sync(){
	  $acount   = Db::name('examination_result')->count();
	  $successcount = cache('successcount');
	  $this->assign('acount',$acount);
	  $this->assign('successcount',$successcount);
	  return $this->fetch('');
	}
	
	//没导入的，重新导入
	public function errordata()
	{
      $errid  = cache('syncerrorid'); //同步失败id
	  if ( $errid ) {
	    foreach( $errid as $key=>$val ) {
		  $data      = Db::name('examination_result')->field('id,question_reult,examination_id,org_id,grade,subject')->where('id',$val)->order('id ASC')->select(); 
		  if ( $data ) {
			foreach( $data as $dk=>$dv ) {
			  $org_info = $this->organ_model->field('code')->where(array('org_id'=>$dv['org_id']))->find();
			  
			  $result = json_decode($dv['question_reult']);
			  $answer = array();
			  if ( $result ) {
				foreach( $result as $rk=>$ev ) {
				  $answer[] = array('QuestionId'=>$rk,'StudentAnswer'=>$ev);
				}
			  }
			  $post = array();
			  $post['SCPOrganizationCode'] = $org_info['code'];
			  $post['SCPStudentId']        = '';
			  $post['GradeId']             = $dv['grade'];
			  $post['StudentQuestions']    = $answer;
			  $post['ExaminationId']       = $dv['examination_id'];
			  $list                        = controller('api/Motiku')->upanswer($post);
			  if( $list['ApiResultType']==1 ){
				$score = 0;
				$QuestionResult = $list['Value']['QuestionResult'];
				if ( $QuestionResult ) {
				  foreach( $QuestionResult as $qk=>$qv ) {
					if ( $qv['IsCorrect'] ) $score++;
				  }
				}
				$up  = array('result_info'=>json_encode($list),'score'=>$score);
				$res = Db::name('examination_result')->where('id',$dv['id'])->update($up);
				cache('syncid',$dv['id']);
				if ( $res ) {
				  $success += ($res) ? 1 : 0;
				} else {
				  $syncerrorid = (cache('syncerrorid')) ? $syncerrorid : array();
				  $syncerrorid[] = $dv['id'];
				  cache('syncerrorid',$syncerrorid);
				}
			  } else {
				$syncerrorid = (cache('syncerrorid')) ? $syncerrorid : array();
				$syncerrorid[] = $dv['id'];
				cache('syncerrorid',$syncerrorid);
			  }
			}//each
			$successcount += $success;
			cache('successcount',$successcount);
			return json(array('state'=>1,'msg'=>'','success'=>$success,'count'=>count($data)));
		  } else {
			return json(array('state'=>0,'msg'=>'syncdata nodata'));
		  }
		}
	  } else {
	    return json(array('state'=>0,'msg'=>'syncdata nodata'));
	  }
	}
	
	
	//重新提交答案
	public function syncdata()
	{ 
	  $page     = input('page',1,'intval');
	  $pagesize = 10;
	  $start    = ($page-1)*$pagesize;
	  $successcount = cache('successcount');
	  $successcount = (!$successcount || $successcount=='') ? 0 : $successcount;
	  $where     = "1=1";
	  $syncid    = cache('syncid');
	  $errid     = cache('syncerrorid'); //同步失败id
	  if ( $syncid!=0 && $syncid > 0 ) $where .= " AND id>".$syncid;
	  
	  $data      = Db::name('examination_result')->field('id,question_reult,examination_id,org_id,grade,subject')->where($where)->limit($pagesize)->order('id ASC')->select();  
	  
	  $success   = 0;
	  if ( $data ) {
		foreach( $data as $dk=>$dv ) {
		  $org_info = $this->organ_model->field('code')->where(array('org_id'=>$dv['org_id']))->find();
		  
		  $result = json_decode($dv['question_reult']);
		  $answer = array();
		  if ( $result ) {
		    foreach( $result as $rk=>$ev ) {
		      $answer[] = array('QuestionId'=>$rk,'StudentAnswer'=>$ev);
			}
		  }
		  $post = array();
		  $post['SCPOrganizationCode'] = $org_info['code'];
		  $post['SCPStudentId']        = '';
		  $post['GradeId']             = $dv['grade'];
		  $post['StudentQuestions']    = $answer;
		  $post['ExaminationId']       = $dv['examination_id'];
		  $list                        = controller('api/Motiku')->upanswer($post);
		  if( $list['ApiResultType']==1 ){
			$score = 0;
			$QuestionResult = $list['Value']['QuestionResult'];
			if ( $QuestionResult ) {
			  foreach( $QuestionResult as $qk=>$qv ) {
				if ( $qv['IsCorrect'] ) $score++;
			  }
			}
		    $up  = array('result_info'=>json_encode($list),'score'=>$score); //,'create_time'=>time()
			$res = Db::name('examination_result')->where('id',$dv['id'])->update($up);
			cache('syncid',$dv['id']);
			if ( $res ) {
			  $success += ($res) ? 1 : 0;
			} else {
			  $syncerrorid = (cache('syncerrorid')) ? $syncerrorid : array();
			  $syncerrorid[] = $dv['id'];
			  cache('syncerrorid',$syncerrorid);
			}
		  } else {
			$syncerrorid = (cache('syncerrorid')) ? $syncerrorid : array();
			$syncerrorid[] = $dv['id'];
		    cache('syncerrorid',$syncerrorid);
		  }
		}//each
		$successcount += $success;
		cache('successcount',$successcount);
		return json(array('state'=>1,'msg'=>'','success'=>$success,'count'=>count($data)));
	  } else {
	    return json(array('state'=>0,'msg'=>'syncdata nodata'));
	  }
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
				$post['ExaminationId'] = $examinations_id;
				$list = controller('api/Motiku')->upanswer($post);
				
				if($list['ApiResultType']==1)
				{
					$question_reult = array();
					foreach($result_list as $k => $v){
						$question_reult[$v['QuestionId']] = $v['StudentAnswer'];
					}
					$answer_name = Cookie::get('answer_name');
					$answer_mobile = Cookie::get('answer_mobile');
					
					//$QuestionResult = $list['Value']['QuestionResult'];
					
					$score = 0;
					$QuestionResult = $list['Value']['QuestionResult'];
					if ( $QuestionResult ) {
					  foreach( $QuestionResult as $qk=>$qv ) {
						if ( $qv['IsCorrect'] ) $score++;
					  }
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
					$rdata['answer_name'] = $answer_name;
					$rdata['answer_mobile'] = $answer_mobile;
					$rdata['score'] = $score;
					$res = $this->examination_result_model->save($rdata);
					$data['status'] = 1;
					$data['msg'] = '成功';
					$eid         = $this->examination_result_model->getLastInsID();
					$data['eid'] = $eid;
					//Cookie::get('answer_id_'.$org_id.'_'.$examinations_id);
					
					Cookie::forever('answer_id_'.$examinations_info['org_id'].'_'.$examinations_id,$eid);
					
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
		$answer_name = Cookie::get('answer_name');
		$answer_mobile = Cookie::get('answer_mobile');
		$examInfo = $this->examination_result_model->field('*')->where('id',$eid)->find();
		$org_info = $this->organ_model->where('org_id',$examInfo['org_id'])->find();
		if ( !$examInfo ) $this->redirect('/index/index/error');
		$from = input('from');
		if($from != ''){
			$this->redirect('index/index/questions',['org_id'=>$org_info['org_id'],'examinations_id'=>$examInfo['examination_id']]);
		}
		//百夫长
		$exam = $this->examination_model->field('*')->where('id',$examInfo['examination_id'])->find();
		$examination_id = $examInfo['examination_id'];
		$template = ($exam) ? $exam['template'] : 0;
		$tpl = 'result_info';
		if ( $template == 5 ) $this->redirect('index/index/exam_webresult',['id'=>$eid,'org_id'=>$exam['org_id']]);
		//结束
		
		$data = array();
		$result_info = json_decode($examInfo['result_info'],true);
		$result_info = $result_info['Value'];
		$data['examinations_id'] = $exam['id'];
		$data['eid'] = $eid;
		$data['is_verify'] = $exam['is_verify'];
		$data['total_sub_num'] = $exam['exam_num'];
		$data['correct_sub_num'] = 0;
		$data['CapabilityScores'] = $result_info['CapabilityScores'];
		$data['KnowledgePointScores'] = $result_info['KnowledgePointScores'];
		$data['exam_name'] = $exam['name'];
		
		//$data['temp'] = (time() - $org_info['create_time']) < 3*30*86400 ? $exam['template'] : 1;
		
		$data['temp'] = $exam['template'];
		
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
				$ratio = floor(($data['correct_sub_num']/$data['total_sub_num'])*100);
				if($ratio == 0){
					$pub_number = 1;
				}else if($ratio == 100){
					$pub_number = 4;
				}else{
					$pub_number = floor($ratio/25)+1;
				}
				$pub_name = 'pub_text'.$pub_number;
				$pub_text = $exam[$pub_name];
			}else{
				$pub_text = $exam['pub_text'];
			}
		}else{
			$pub_text = $exam['pub_text'];
		}
		
		$data['organ'] = array('name'=>$org_info['name'],'logo'=>$org_info['logo'],'pub_text'=>$pub_text,'mobile'=>$org_info['mobile']);
		$examList = controller('api/Motiku')->getList($question_ids);
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
					$exam_arr['Analysis'] = $v['Analysis'];
				}
			}
			array_push($data['examList'],$exam_arr);
		}
		$tpl = 'result_info';
		
		$wxauth = $this->wxauth($examInfo['org_id']);
		$wxauth['title'] = $exam['name'];
		$wxauth['wxpic'] = $exam['sharepic'];
		if ( $exam['sharepic'] == '' ) $wxauth['wxpic'] = 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
		if(strpos($wxauth['wxpic'],'http')===false){
		  $wxauth['wxpic'] = 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
		}
		//echo $wxauth['wxpic']; die;
        //$shareurl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/index/index/questions/examinations_id/'.$examInfo['examination_id'].'/org_id/'.$examInfo['org_id'].'.html';
		return $this->fetch($tpl,['data'=>$data,'orgid'=>$examInfo['org_id'],'examinations_id'=>$examInfo['examination_id'],'org_info'=>$org_info,'answer_name'=>$answer_name,'answer_mobile'=>$answer_mobile,'sign'=>$wxauth['sign'],'wxpic'=>$wxauth['wxpic'],'title'=>$wxauth['title'],'des'=>$wxauth['des'],'url'=>$wxauth['url']]);
	}
	
	public function exam_webresult($id,$org_id)
	{
	  header("Content-type: text/html; charset=utf-8"); 
      $info = Db::name('examination_result')->where('id',$id)->find();
	  if ( $info ) {
		$this->assign('id',$info['examination_id']);
		$this->assign('orgid',$org_id);
		$exam = Db::name('examination')->where('id',$info['examination_id'])->find();
		
		$mtk  = controller('api/Motiku', 'controller');
		$timu = $mtk->getTimuById($info['examination_id']);
		$tobj = false;
		$error = '';
		if ( $timu['ApiResultType'] == 1 ) {
		  $tobj = $timu['Value']['Questions'];
		  
		} else {
		  $error = $timu['ResultMessage'];
		}
		
		//print_r($tobj);
		//die;
		
		
		$question_reult = $info['question_reult'];
		$question_reult = json_decode($question_reult);
		

		
		$result_info = $info['result_info'];
		$result_info = json_decode($result_info);	
		
		$result_info = $result_info->Value;
		
		$QuestionResult   = ($result_info) ? $result_info->QuestionResult : false; //学员答案
		$KnowledgePointScores = ($result_info) ? $result_info->KnowledgePointScores : false; //学生当前学科知识点测评得分集合
		$KnowledgePointMasterAnalysis = ($result_info) ? $result_info->KnowledgePointMasterAnalysis : false; //知识点掌握分析
		$CourseScore = ($result_info) ? $result_info->CourseScore : 0; //知识点得分
		
		
		
		$pdata   = $mtk->getExamStats($info['examination_id']);
		$pdata   = $pdata['Value'];
		
		$myscore = array('Score',$info['score'],'Count'=>0);
		$ScoreDistributionStats = ($pdata) ? $pdata['ScoreDistributionStats'] : false; //做题结果中整体得分分布。  如：做对5道题的人次有20个，做对6道题的人次有15个。
		$exam_num = $exam['exam_num']; //题目数量
		$mhtml    = ($info['score']==0) ? '超过0位' : '';
		$paimingjson = array();
		if ( $ScoreDistributionStats && is_array($ScoreDistributionStats)  && count($ScoreDistributionStats) >0 ) {
		  $ScoreDistributionStats = Array_sort($ScoreDistributionStats,'Score','desc');
		  $ScoreDistributionStats = array_merge($ScoreDistributionStats);
		  $chaoguo   = 0;
		  $position  = 0;//位置
		  $paiming   = 0;//
		  $daticount = 0;
		  foreach( $ScoreDistributionStats as $sckey=>$scval ) {
		    if( $scval['Score'] == $info['score'] ) {
			  $position = $sckey+1;
			} 
			$daticount += ($scval['Count']);
		  }
		  //dump($ScoreDistributionStats); die;
		  if ( $position ) {
		    $position = $position-1;
			foreach( $ScoreDistributionStats as $skk=>$svv ) {
			  if ( $skk > $position )  $chaoguo += $svv['Count'];
			}
		    $paiming += $position+1;
		  }
		  $exam_num = $daticount;
		  $row      = ($exam_num>5) ? intval($exam_num/5) : $exam_num;
		  $floor    = array();
		  $yu       = ( $exam_num - $row*5 ); //余数
		  if ( $exam_num > 5 ) {
		    for( $r=1;$r<=5;$r++ ) {
			  if ( $r == 5 ) {
				$maxs    = $yu ? ($yu+($r)*$row) : ($r)*$row;
				$topic   = (($r-1)*$row).'-'.$maxs.'名次';
			    $floor[] = array('name'=>$topic,'minscore'=>(($r-1)*$row),'maxscore'=>$maxs);
			  } else {
			    $floor[] = array('name'=>(($r-1)*$row).'-'.(($r)*$row).'名次','minscore'=>(($r-1)*$row),'maxscore'=>(($r)*$row));
		      }
			}
		  } else {
		    for( $r=1;$r<=$exam_num;$r++ ) {
			  $floor[] = array('name'=>($r-1).'-'.($r).'名次','minscore'=>$r-1,'maxscore'=>$r);
			}
		  }
		  if ( $info['score'] == 0 ) {
		    $paiming = $exam_num;
		  }
		  
		  foreach( $floor as $fk=>$fv ) {
		    if ( $paiming > $fv['minscore'] &&  $paiming <= $fv['maxscore'] ) {
			  $paimingjson[] = array('name'=>'他的位置： <font>'.$paiming.'</font>,超过 <font>'.$chaoguo.'</font> 人','active'=>1);
			} else {
			  $paimingjson[] = array('name'=>$fv['name'],'active'=>0);
			}
		  }

		}

		//错题
		$QuestionWrongStats = ($pdata) ? $pdata['QuestionWrongStats'] : false;
		$count              = ($pdata) ? $pdata['NumberOfDoQuestion'] : 0;//做题次数
		$level              = ($pdata) ? $pdata['ExamQuestionLevel']  : 1;//end
		$wrongcount = 0; //错题数量
		if ( $QuestionWrongStats && count($QuestionWrongStats) > 0 ) {
		  $lists  = array();
		  foreach( $QuestionWrongStats as $qkey=>$qval ) {
			$wrongcount += $qval['NumberOfWrong'];
		  }
		}
		
		$avg2 = ($wrongcount && $count) ? $wrongcount/$count : 0;
		$avg2 = ($avg2) ? $exam['exam_num'] - $avg2 : 0;
		$avg2 = ($avg2) ? round($avg2,2) : 0;
 		
		//知识点
		$TopKnowledgePointDistribution = ($pdata) ? $pdata['TopKnowledgePointDistribution'] : false;
		
		$tknowcount = ($TopKnowledgePointDistribution) ? count($TopKnowledgePointDistribution) : 0;
	   	$KnowledgePointScoresx = []; //知识点
		$knowarr = [];
	    if ( $TopKnowledgePointDistribution ) {
		  foreach( $TopKnowledgePointDistribution as $tk=>$tv ) {
		    $KnowledgePointScoresx[] = array($tv['KnowledgePointName'],$tv['NumberOfQuestion']);
			$knowarr[] = array('score'=>$tv['NumberOfQuestion'],'name'=>$tv['KnowledgePointName']);
		  }
		  $knowarr = Array_sort($knowarr,'score','desc');
		  $knowarr = array_merge($knowarr);
		}
		$name = isset($knowarr[0]) ? $knowarr[0]['name'] : '';
		$KnowledgePointScoresx =  json_encode($KnowledgePointScoresx);
		
		
		
		$data = array();
		$result_info = json_decode($info['result_info'],true);
		$result_info = $result_info['Value'];
		$data['examinations_id'] = $info['examination_id'];
		//$data['eid'] = $eid;
		$data['is_verify'] = $exam['is_verify'];
		$data['total_sub_num'] = $exam['exam_num'];
		$data['correct_sub_num'] = 0;
		$data['CapabilityScores'] = $result_info['CapabilityScores'];
		$data['KnowledgePointScores'] = $result_info['KnowledgePointScores'];
		$data['exam_name'] = $exam['name'];
		//$data['temp'] = (time() - $org_info['create_time']) < 3*30*86400 ? $exam['template'] : 1;
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
		
		$org_info = $this->organ_model->where('org_id',$org_id)->find();
		if($org_info['level'] == 4){
			if($exam['is_part']){
				$ratio = floor(($data['correct_sub_num']/$data['total_sub_num'])*100);
				if($ratio == 0){
					$pub_number = 1;
				}else if($ratio == 100){
					$pub_number = 4;
				}else{
					$pub_number = floor($ratio/25)+1;
				}
				$pub_name = 'pub_text'.$pub_number;
				$pub_text = $exam[$pub_name];
			}else{
				$pub_text = $exam['pub_text'];
			}
		}else{
			$pub_text = $exam['pub_text'];
		}
		$organ    = array('name'=>$org_info['name'],'logo'=>$org_info['logo'],'pub_text'=>$pub_text,'mobile'=>$org_info['mobile']);
		
		
		$answer_name = Cookie::get('answer_name');
		$answer_mobile = Cookie::get('answer_mobile');
		
		$wxauth = $this->wxauth($org_id);
		$wxauth['title'] = $exam['name'];
		$wxauth['wxpic'] = $exam['sharepic'];
		
		if ( $exam['sharepic'] == '' ) {$wxauth['wxpic'] = 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';}
		if(strpos($wxauth['wxpic'],'http')===false){
		  $wxauth['wxpic'] = 'http://motkfile.oss-cn-hangzhou.aliyuncs.com/shanceping/50f26a07d8ee6.png';
		}
		//$shareurl = 'http://'.$_SERVER['HTTP_HOST'].'/index.php/index/index/questions/examinations_id/'.$info['examination_id'].'/org_id/'.$org_id.'.html';
		return $this->fetch('result_webinfo',['data'=>array('exam_name'=>$exam['name']),'organ'=>$organ,'info'=>$info,'exam'=>$exam,'id'=>$id,'subject'=>getSubjectList(),'tobj'=>$tobj,'error'=>$error,'QuestionResult'=>$QuestionResult,'KnowledgePointScoresx'=>$KnowledgePointScoresx,'KnowledgePointMasterAnalysis'=>$KnowledgePointMasterAnalysis,'name'=>$name,'CourseScore'=>$CourseScore,'question_reult'=>$question_reult,'avg2'=>$avg2,'level'=>$level,'tknowcount'=>$tknowcount,'paimingjson'=>$paimingjson,'answer_name'=>$answer_name,'answer_mobile'=>$answer_mobile,'sign'=>$wxauth['sign'],'wxpic'=>$wxauth['wxpic'],'title'=>$wxauth['title'],'des'=>$wxauth['des'],'url'=>$wxauth['url']]);
	  } else {
	    $this->error('未查询到有效统计信息');
	  }
	}
	
	
	public function save_user()
	{
		$answer_name = input('answer_name');
		$answer_mobile = input('answer_mobile');
		
		Cookie::forever('answer_name',$answer_name);
		Cookie::forever('answer_mobile',$answer_mobile);
		
		echo json_encode(array('status'=>1));
	}
	
	public function save_user2()
	{
		$answer_name = input('answer_name');
		$answer_mobile = input('answer_mobile');
		$eid = input('eid');
		
		Cookie::forever('answer_name',$answer_name);
		Cookie::forever('answer_mobile',$answer_mobile);
		
		$res = $this->examination_result_model->where('id',$eid)->update(['answer_name'=>$answer_name,'answer_mobile'=>$answer_mobile]);
		if($res){
			echo json_encode(array('status'=>1));
		}else{
			echo json_encode(array('status'=>0,'msg'=>'提交失败'));
		}
	}
}