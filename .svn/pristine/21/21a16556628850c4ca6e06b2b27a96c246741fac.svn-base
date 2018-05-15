<?php
namespace app\orgadmin\controller;

use app\common\controller\OrganBase;
use think\Cache;
use think\Cookie;
use think\Config;
use think\Db;
use app\common\model\Organ as OrganModel;
use app\common\model\Examination as ExaminationModel;
use app\common\model\DefaultTemplate as DefaultTemplateModel;
use app\common\model\ExaminationResult as ExaminationResultModel;

class Examination extends OrganBase
{
	protected $organ_model,$examination_model,$default_template_model;
    protected function _initialize()
    {
        parent::_initialize();
		$this->organ_model = new OrganModel();
		$this->examination_model = new ExaminationModel();
		$this->default_template_model = new DefaultTemplateModel();
		$this->examination_result_model = new ExaminationResultModel();
		config('dispatch_success_tmpl',dirname(THINK_PATH).'/application/orgadmin/view/state.html');
		config('dispatch_error_tmpl',dirname(THINK_PATH).'/application/orgadmin/view/state.html');
    }
	
    public function index($grade = '', $subject = '', $status = '',$is_mobile=false, $page = 1, $page1 = 1, $page2 = 1, $is_page1 = false, $is_page2 = false)
    {
		$map = [];
		$map1 = [];
		$organ_id = $this->organ_id;
		$map['org_id'] = $organ_id;
		$map1['a.org_id'] = $organ_id;
		$map['status'] = array('neq',4);
		if($is_page1 != "true"){
			$page1 = 1;
		}
		if($is_page2 != "true"){
			$page2 = 1;
		}
        if ($grade) {
            $map['grade'] = $grade;
			//$map1['a.grade'] = $grade;
        }
		if ($subject) {
            $map['subject'] = $subject;
			//$map1['a.subject'] = $subject;
        }
		if ($status) {
            $map['status'] = $status;
        }
		if($is_mobile == "true"){
			$map1['a.answer_mobile'] = array(['EXP','IS NOT NULL'],['neq',''],'and');
		}
		
		$count = $this->examination_model->where($map)->count();
		$psize = 25;
		
		if ( $count &&  $page1 > ceil($count/$psize) ) $page1 = ceil($count/$psize);
		
		
        $list = $this->examination_model->where($map)->order('id DESC')->paginate($psize, false, ['page' => $page1,'query' => request()->param()]);
		
		$grade_list = getGradeList();
		$subject_list = getSubjectList();
		$status_list = getStatusList();
		$organInfo = $this->organ_model->where('org_id',$organ_id)->find(); 
		$organ_level_name = getLevelName($this->level);
		
		foreach($list as $k=>$v){
			$list[$k]['grade_name'] = getGrade($v['grade']);
			$list[$k]['subject_name'] = $subject_list[$v['subject']]['stageName'].$subject_list[$v['subject']]['name'];
			$list[$k]['status_name'] = getStatus($v['status']);
			$list[$k]['create_time_text'] = date('Y-m-d H:i',$v['create_time']);
			$list[$k]['counts'] = Db::name('examination_result')->where('examination_id',$v['id'])->count();
		}
/*		$relist = $this->examination_result_model->alias('a')->join('examination b','a.examination_id=b.id')->field('a.*,b.name as examination_name,b.exam_num,b.template')->where($map1)->order('a.create_time DESC')->paginate(25, false, ['page' => $page2,'query' => request()->param()]);
		//echo db('examination_result')->getLastSql();
		foreach($relist as $k=>$v){
			$relist[$k]['grade_name'] = getGrade($v['grade']);
			$relist[$k]['subject_name'] = $subject_list[$v['subject']]['stageName'].$subject_list[$v['subject']]['name'];
			$relist[$k]['create_time_text'] = date('Y-m-d H:i',$v['create_time']);
			if(mb_strlen($v['answer_name'],'utf8')>5){
				$relist[$k]['answer_name'] = mb_substr($v['answer_name'],0,5,'utf-8').'...';
			}
			if($v['result_info']){
				$relist[$k]['result_info'] = json_decode($v['result_info'],true);
				if($relist[$k]['result_info']['Value']['QuestionResult']){
					$i=0;
					foreach($relist[$k]['result_info']['Value']['QuestionResult'] as $k1=>$v1){
						if($v1['IsCorrect'] == true){
							$i++;
						}
					}
					$relist[$k]['result_num'] = $i;
				}else{
					$relist[$k]['result_num'] = '未提交';
				}
			}
		}*/
        return $this->fetch('index', ['tpllist'=>getTemplateList(),'list'=>$list,'organ_level'=>$this->level,'organ_level_name'=>$organ_level_name,'grade'=>$grade,'grade_list'=>$grade_list,'subject'=>$subject,'subject_list'=>$subject_list,'grade_list_json'=>json_encode($grade_list),'status'=>$status,'status_list'=>$status_list,'page1'=>$page1,'page2'=>$page2,'is_mobile'=>$is_mobile]);
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
			$organ_id  = $this->organ_id;
			$organInfo = $this->organ_model->where('org_id',$organ_id)->find();
			$def_tmp   = $organInfo['pub_text'] ? $organInfo['pub_text'] : '';
			$auth      = getAuth($this->level);
			$templateList = getTemplateList();
			$levelList    = getLevel();
			if($info['qrhost'] != $site_config['site_url']){
				$url    = $site_config['site_url'].'/index.php/index/index/questions?org_id='.$organ_id.'&examinations_id='.$info['id'];
				$id     = $info['id'];
				$qrurl  = $this->getQrcode($url,$id);
				$motiku = controller('api/Motiku', 'controller');
				$res    = $motiku->upload($qrurl);
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
	
    public function editExam()
	{
		$id      = input('id',0,'intval');
		$success = input('success',0,'intval');
		$info    = Db::name('examination')->where('id',$id)->find();
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		if($info){
			$savedata = array();
			if ( $this->level != 4  ) {
			  if ( $info['template'] == 5 ) $savedata['template'] = 1;
			  if ( $info['is_part'] == 1 )  $savedata['is_part'] = 0;
			  if ( count($savedata) > 0 ) {
				Db::name('examination')->where('id',$id)->update($savedata);
			    $info = Db::name('examination')->where('id',$id)->find();
			  }
			}
			
			$organ_id = $this->organ_id;
			$organInfo = $this->organ_model->where('org_id',$organ_id)->find();
			
			
			$def_tmp = $organInfo['pub_text'] ? $organInfo['pub_text'] : '';
			$auth = getAuth($this->level);
			$templateList = getTemplateList();
			$levelList = getLevel();
			if($info['qrhost'] != $site_config['site_url']){
				$url = $site_config['site_url'].'/index.php/index/index/questions?org_id='.$organ_id.'&examinations_id='.$info['id'];
				$id = $info['id'];
				$qrurl = $this->getQrcode($url,$id);
				$motiku = controller('api/Motiku', 'controller');
				//var_dump($qrurl);
				$res = $motiku->upload($qrurl);
				if($res['ApiResultType'] == 1){
					$info['qrcode'] = $res['Value'];
					$info['qrhost'] = $site_config['site_url'];
					$this->examination_model->update($info);
				}
			}
			$info['exam_url'] = $site_config['site_url'].'/index.php/index/index/questions?org_id='.$organ_id.'&examinations_id='.$info['id'];
			$data = (array('code'=>1,'info'=>$info,'organInfo'=>$organInfo,'def_tmp'=>$def_tmp,'auth'=>$auth,'levelList'=>$levelList,'templateList'=>$templateList));
			$motiku  = controller('api/Motiku', 'controller');
			return $this->fetch('',['data'=>$data,'id'=>$id,'success'=>$success,'pub_text'=>$organInfo['pub_text'],'levname'=>getLevel()]);
		}else{
			$this->error('未查询到有效信息');
		}
	}
	
	public function saveexam()
	{
	  $id   = input('id');
	  if ( !$id ) return json(array('state'=>0,'msg'=>'未查询到有效信息'));
	  $info = Db::name('examination')->where('id',$id)->find();
	  if ( !$info ) return json(array('state'=>0,'msg'=>'未查询到有效信息'));
	  $data = input('post.');
	  $savedata['sharepic']  = $data['sharepic'];
	  $savedata['is_verify'] = $data['is_verify'];
	  $savedata['template']  = $data['template'];
	  $savedata['name']      = $data['name'];
	  $savedata['is_part']   = $data['is_part'];
	  $savedata['pub_text']  = $data['pub_text'];
	  $savedata['pub_text1'] = $data['pub_text1'];
	  $savedata['pub_text2'] = $data['pub_text2'];
	  $savedata['pub_text3'] = $data['pub_text3'];
	  $savedata['pub_text4'] = $data['pub_text4'];
	  $savedata['update_time'] = time();
	  $res = Db::name('examination')->where('id',$id)->update($savedata);
	  if ( $this->level < 3 ) { //无法设置视频和链接
	    if ( stristr($data['pub_text'],'href=') ) {return json(array('state'=>2,'msg'=>'包含外部链接'));}
	    if ( stristr($data['pub_text'],'video') || stristr($data['pub_text'],'object')) {return json(array('state'=>3,'msg'=>'包含视频链接'));}
	  }
	  //return json(array('state'=>0,'msg'=>$data['sharepic'],'xx'=>$savedata));
	  if ( $res ) {
	    $this->organ_model->where('org_id',$info['organ_id'])->update(array('def_opt1'=>$savedata['is_verify'],'def_opt2'=>$savedata['template']));
		return json(array('state'=>1,'msg'=>''));
	  } else {
	    //$this->error('题集设置失败',url('orgadmin/examination/editexam','id='.$id));
		return json(array('state'=>0,'msg'=>'题集保存失败'));
	  }
	}
	
	
	//上传
	public function uploadpic(){
	  return json(model('File')->picupload());
	}
	//裁剪
	public function croppic(){
	   if ( request()->isAjax() ) {
		 $w    = input('post.w',0,'intval');
		 $h    = input('post.h',0,'intval');
		 $x    = input('post.x',0,'intval');
		 $y    = input('post.y',0,'intval');
		 $r    = input('post.r',0,'intval');
		 $path = input('post.path','');
		 $iswater = input('post.iswater',0,'intval');
		 $delpic  = input('post.delpic',0,'intval');
		 if ( $path == '' ) return json(array('state'=>0,'msg'=>'请上传裁剪的图片'));
		 if ( $w == 0 || $h == 0 ) return json(array('state'=>0,'msg'=>'裁剪的宽度和高度不能为0px'));
		 $rdata = model("File")->croppic(array('x'=>$x,'y'=>$y,'w'=>$w,'h'=>$h,'r'=>$r,'iswater'=>$iswater,'path'=>$path,'delpic'=>$delpic));
		 if ( $rdata['state'] ) {
		  	$event = controller('api/Motiku', 'controller');
			$url   = '/public/uploads/images/'.$rdata['file'];
			$res   = $event->upload($url);
			if($res['ApiResultType'] == 1){
			  $rdata['file'] = $res['Value'];
			}else{
			  $rdata['state'] = 0;
			  $rdata['msg'] = '裁剪失败';
			  $rdata['file'] = '';
			}
		 }
		 return json($rdata);
	   }
	}
	
	/*
	 * 统计
	 * add function -- 
	*/
	public function statistics($id,$page=1)
	{
	  header("Content-type: text/html; charset=utf-8"); 
      $info = Db::name('examination')->where('id',$id)->find();
	  if ( $info ) {
		$psize   = 10;
		$where   = "1=1 AND examination_id={$id}";
		$count   = Db::name('examination_result')->where($where)->count();
		$pobj    = new \page\AdminPage($count,$psize);
		$stulist = Db::name('examination_result')->where($where)->limit($pobj->limit)->order('create_time DESC')->select();
		$pshow   = ($count > $psize) ? $pobj->showpage() : '';
		$motiku  = controller('api/Motiku', 'controller');
		$pdata   = $motiku->getExamStats($id);
		$pdata   = $pdata['Value'];
		//错题
		$QuestionWrongStats = ($pdata) ? $pdata['QuestionWrongStats'] : false;
		$counts             = ($pdata) ? $pdata['NumberOfDoQuestion'] : 0;//做题次数
		$wrongcount = 0; //错题数量
		$QuestionWrongStats = ($pdata) ? Array_sort($QuestionWrongStats,'NumberOfWrong','desc') : array();
		$QuestionWrongStats = array_merge($QuestionWrongStats);
		if ( $QuestionWrongStats && count($QuestionWrongStats) > 0 ) {
		  $lists  = array();
		  foreach( $QuestionWrongStats as $qkey=>$qval ) {
			$ql = $motiku->getList($qval['QuestionId']);
			$ql = ($ql)     ? $ql['Value']['Questions']  : '';
			$ql = ($ql)     ? $ql[0]['QuestionContent']  : '';
			$QuestionWrongStats[$qkey]['name'] = $ql;
			$wrongcount += $qval['NumberOfWrong'];
		  }
		}
		
		$avg2 = ($wrongcount && $counts) ? $wrongcount/$counts : 0;
		$avg2 = ($avg2) ? $info['exam_num'] - $avg2 : 0;
		$avg2 = ($avg2) ? round($avg2,2) : 0;
		
		//结束
		$KnowledgePointEvaluationAvgScores = ($pdata) ? $pdata['KnowledgePointEvaluationAvgScores'] : false;
		$know = array();
		if ( $KnowledgePointEvaluationAvgScores ) {
		  foreach( $KnowledgePointEvaluationAvgScores as $key=>$val ) {
		    if ( $val['HasEvaluation'] ) $know[] = array('score'=>$val['Score'],'topic'=>$val['KnowledgePointName']);
			if ( count($val['Children']) > 0 ) {
			  foreach( $val['Children'] as $ckk=>$cvv ) {
			    if ( $cvv['HasEvaluation'] ) $know[] = array('score'=>$cvv['Score'],'topic'=>$cvv['KnowledgePointName']);
				
				if ( count($cvv['Children']) > 0  ) {
				  foreach( $cvv['Children'] as $ckkk=>$cvvv ) {
				    $know[] = array('score'=>$cvvv['Score'],'topic'=>$cvvv['KnowledgePointName']);
				  }
				}
				
			  }
			}
		  }
		}
		$knowa = (count($know)>0) ? Array_sort($know,'score','asc') : false;
		$knowa = ($knowa) ? array_merge($knowa) : array();

		return $this->fetch('statistics',['info'=>$info,'id'=>$id,'dtcount'=>$counts,'stulist'=>$stulist,'avg'=>$avg2,'pshow'=>$pshow,'qwrong'=>$QuestionWrongStats,'know'=>$knowa]);
	  } else {
	    $this->error('未查询到有效统计信息');
	  }
	}
	
	public function examrecord($page=1,$isphone=0)
	{
	  $psize   = 20;
	  $where   = "1=1 AND org_id=".$this->organ_id;
	  if ( $isphone == 1 ) $where   .= " AND answer_mobile <>''";
	  $count   = Db::name('examination_result')->where($where)->count();
	  $pobj    = new \page\AdminPage($count,$psize);
	  $stulist = Db::name('examination_result')->where($where)->limit($pobj->limit)->order('create_time DESC')->select();
      $pshow   = ($count > $psize) ? $pobj->showpage() : '';
	  
	  return $this->fetch('examrecord',['activeid'=>'activeid-org','isphone'=>$isphone,'page'=>$page,'stulist'=>$stulist,'pshow'=>$pshow,'subject'=>getSubjectList(),'agelist'=>getStageList()]);

	}
	
	public function tree($id,$page=1)
	{
	  header("Content-type: text/html; charset=utf-8"); 
      $info = Db::name('examination')->where('id',$id)->find();
	  if ( $info ) {
		$motiku = controller('api/Motiku', 'controller');
		$res  = $motiku->getExamStats($id);
		$res  = $res['Value'];
		$tree = ($res) ? $res['KnowledgePointEvaluationAvgScores'] : false;
		return $this->fetch('tree',['info'=>$info,'tree'=>$tree,'id'=>$id]);
	  } else {
	    $this->error('未查询到有效信息');
	  }
	}
	
	/*
	 * 学生成绩
	 * add function -- 
	*/
	public function studentresult($id)
	{
	  $isrecord = input('param.isrecord',0,'intval');
	  header("Content-type: text/html; charset=utf-8"); 
      $info = Db::name('examination_result')->where('id',$id)->find();
	  if ( $info ) {
		
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
		$question_reult   = $info['question_reult'];
		$question_reult   = json_decode($question_reult);
		$result_info      = $info['result_info'];
		$result_info      = json_decode($result_info);	
		$result_info      = $result_info->Value;
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
		
		$activeid = 'examination-org';
		if ( $isrecord == 1 ) $activeid = 'activeid-org';
		
		return $this->fetch('studentresult',['info'=>$info,'activeid'=>$activeid,'exam'=>$exam,'id'=>$id,'subject'=>getSubjectList(),'tobj'=>$tobj,'error'=>$error,'QuestionResult'=>$QuestionResult,'KnowledgePointScoresx'=>$KnowledgePointScoresx,'KnowledgePointMasterAnalysis'=>$KnowledgePointMasterAnalysis,'name'=>$name,'isrecord'=>$isrecord,'CourseScore'=>$CourseScore,'question_reult'=>$question_reult,'avg2'=>$avg2,'levels'=>$level,'tknowcount'=>$tknowcount,'paimingjson'=>$paimingjson]);
	  } else {
	    $this->error('未查询到有效统计信息');
	  }
	}
	/*
	 * 讲解
	 * add function -- 
	*/
	public function explain($id,$page=1)
	{
	  //dump(input('param.'));
      $info = Db::name('examination')->where('id',$id)->find();
	  if ( $info ) {
		$motiku  = controller('api/Motiku', 'controller');
		$pdata   = $motiku->getExamStats($id);
		$pdata   = $pdata['Value'];
		$count   = ($pdata) ? $pdata['NumberOfDoQuestion'] : 0;//做题次数
		$wrongcount = 0; //错题数量
		//错题
		$QuestionWrongStats = ($pdata) ? $pdata['QuestionWrongStats'] : false;
		$QuestionWrongStats = ($pdata) ? Array_sort($QuestionWrongStats,'NumberOfWrong','desc') : array();
		$QuestionWrongStats = array_merge($QuestionWrongStats);
		if ( $QuestionWrongStats && count($QuestionWrongStats) > 0 ) {
		  $lists  = array();
		  foreach( $QuestionWrongStats as $qkey=>$qval ) {
			$qlobj = $motiku->getList($qval['QuestionId']);
			$qlobj = ($qlobj['ApiResultType']) ? $qlobj['Value']['Questions'] : false; 
			$ql    = ($qlobj)     ? $qlobj[0]['QuestionContent']  : '';
			$QuestionWrongStats[$qkey]['name']     = $ql;
			$QuestionWrongStats[$qkey]['Options']  = ($qlobj) ? $qlobj[0]['Options']  : '';
			$QuestionWrongStats[$qkey]['Analysis'] = ($qlobj) ? $qlobj[0]['Analysis'] : '';
			$QuestionWrongStats[$qkey]['Answer']   = ($qlobj) ? $qlobj[0]['Answer']   : '';
			$QuestionWrongStats[$qkey]['KnowledgePointNames'] = ($qlobj) ? $qlobj[0]['KnowledgePointNames']   : '';//知识点
			$wrongcount += $qval['NumberOfWrong'];
		  }
		}
		$avg = ($wrongcount && $count) ? $wrongcount/$count : 0;
		$avg = ($avg) ? $info['exam_num'] - $avg : 0;
		$avg = ($avg) ? round($avg,2) : 0;
		return $this->fetch('explain',['info'=>$info,'id'=>$id,'dtcount'=>$count,'avg'=>$avg,'qwrong'=>$QuestionWrongStats]);
	  } else {
	    $this->error('未查询到有效统计信息');
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
				$motiku = controller('api/Motiku', 'controller');
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
		$auth = getAuth($this->level);
		if ($this->request->isPost()) {
			$data= $this->request->post();
			$newdata = array();
			$i = 0;
			//上一次的url
			$before = Db::name('examination')->field('sharepic')->where("1=1 AND sharepic<>'' AND org_id=".$organ_id)->order('update_time DESC')->find();
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
				$newdata[$i]['is_verify'] = $organInfo['def_opt1'];
				$newdata[$i]['question_ids'] = $v['question_ids'];
				$newdata[$i]['pub_text'] = $organInfo['pub_text'] ? $organInfo['pub_text'] : '';
				
				if ( $this->level == 4 ) {
				  $newdata[$i]['template'] = 5;
				} else {
				  $newdata[$i]['template'] = $organInfo['def_opt2'];
				}
				
				if ( $before && $before['sharepic'] !='' ) $newdata[$i]['sharepic'] = $before['sharepic'];
				
				$i++;
			}
			//return json(array('xx'=>$this->request->isPost()));
			//return json($newdata); die;
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
			$auth = getAuth($this->level);
			$def_tmp = $organInfo['pub_text'] ? $organInfo['pub_text'] : '';
			$info = array();
			$info['id'] = $data['id'];
			$info['template'] = $data['template'];
			$info['is_part'] = $data['is_part'];
			$info['is_verify'] = $data['is_verify'];
			$info['name'] = $data['name'];
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
				$this->organ_model->where('org_id',$organ_id)->update(array('def_opt1'=>$info['is_verify'],'def_opt2'=>$info['template']));
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
		 \QRcode::png($value, 'public/uploads/qrcodemage/'.$code.'.png', $errorCorrectionLevel, $matrixPointSize, 2); 
		//$logo = 'http://'.$_SERVER['HTTP_HOST'].'/Uploads/2016-12-28/thumb_58638fbb4affe.jpg';//准备好的logo图片 
		return '/uploads/qrcodemage/'.$code.'.png';//已经生成的原始二维码图 
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
