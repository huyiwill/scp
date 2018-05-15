<?php
namespace app\api\controller;

use org\Auth;
use think\Loader;
use think\Cache;
use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;
use think\Config;
/**
 * 通用上传接口
 * Class Upload
 * @package app\api\controller
 */
class Motiku extends Controller
{
	
    protected function _initialize()
    {
        parent::_initialize();
		/*
        if (!Session::has('admin_id')) {
            $result = [
                'error'   => 1,
                'message' => '未登录'
            ];

            return json($result);
        }
		*/
    }

    /**
     * 2.1.	校验试卷中题目是否能用于闪测评做题
     * 
     */
    public function yanzheng($link)
    {
		//$file = input('file.filename');
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$str = ROOT_PATH.'/public/'.$link;
		$outstr = iconv('utf-8','gb2312',$str);
		$file = file_get_contents($outstr);
		$url = Config('MotikuApiHttp')."Shanceping/CheckQuestion";
		//var_dump(basename($file));
		$headers_new = array();
		$multi = false;
		$headers_new[] = "Content-type:application/json";
		$headers_new[] = "AppKey:".$site_config['app_key'];
		$headers_new[] = "AppSecret:".$site_config['app_secret'];
		
		$httpstr = $this->kickhttp($url, $file, 'POST',$headers_new,true,true);
		//var_dump($httpstr);
		$httpstr = json_decode($httpstr,true);
		return $httpstr;
    }
	
	/**
     *2.2.	上传文件到OSS
     * 
     */
    public function upload($link)
    {
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$public = strpos($link,'public') === false ? '/public' : '';
        $str =  ROOT_PATH.$public.$link;
		$outstr = iconv('utf-8','gb2312',$str);
		$file = file_get_contents($outstr);
		$base = basename($outstr);
		$url = Config('MotikuApiHttp')."Shanceping/UploadFile";
		$headers_new = array();
		$multi = false;
		$headers_new[] = "Content-type:text/json";
		$headers_new[] = "AppKey:".$site_config['app_key'];
		$headers_new[] = "AppSecret:".$site_config['app_secret'];
		$headers_new[] = "FileName:".$base;
		$httpstr = $this->kickhttp($url, $file, 'POST',$headers_new,true,true);
		$httpstr = json_decode($httpstr,true);
		return $httpstr;
    }
	
	/*
	  获取题目详情
	*/
	public function getTimuById($id)
	{
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$url = Config('MotikuApiHttp')."Shanceping/QuestionDetails";
		if ( !$id ) return false;
		$ti = Db::name('examination')->field('question_ids')->where('Id',$id)->find();
		$listid = ($ti!='') ? explode(",",$ti['question_ids']) : array();
		$headers_new = array();
		$headers_new[] = "Content-Type: text/json";
		$data = array();
		$data['AppKey'] = $site_config['app_key'];
		$data['AppSecret'] = $site_config['app_secret'];
		$data['QuestionIds'] = array();
		$data['QuestionIds'] = $listid;
		$data = json_encode($data);
		$httpstr = $this->kickhttp($url, $data, 'POST',$headers_new,true,true);
		$httpstr = json_decode($httpstr,true);
		return $httpstr;
	}
	
	//
	public function getExamStats($id)
	{
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$url = Config('MotikuApiHttp')."Shanceping/ExamStats";
		if ( !$id ) return false;
		$headers_new = array();
		$headers_new[] = "Content-Type: text/json";
		$data = array();
		$data['AppKey'] = $site_config['app_key'];
		$data['AppSecret'] = $site_config['app_secret'];
		$data['ExaminationId'] = $id;
		$data = json_encode($data);
		$httpstr = $this->kickhttp($url, $data, 'POST',$headers_new,true,true);
		$httpstr = json_decode($httpstr,true);
		return $httpstr;
	}	
	
	/**
     *2.3.	获取题目详情列表
     * 
     */
    public function getList($listid)
    {
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$listid = explode(',',$listid);
		$url = Config('MotikuApiHttp')."Shanceping/QuestionDetails";
		$headers_new = array();
		$headers_new[] = "Content-Type: text/json";
		$data = array();
		$data['AppKey'] = $site_config['app_key'];
		$data['AppSecret'] = $site_config['app_secret'];
		$data['QuestionIds'] = array();
		$data['QuestionIds'] = $listid;
		$data = json_encode($data);
		$httpstr = $this->kickhttp($url, $data, 'POST',$headers_new,true,true);
		$httpstr = json_decode($httpstr,true);
		return $httpstr;
    }
	
	/**
     *2.4.	提交做题结果
     * 
     */
    public function upanswer($post)
    {
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$url = Config('MotikuApiHttp')."Shanceping/SubmitExamResult";
		$headers_new = array();
		$headers_new[] = "Content-Type: text/json";
		$data = array();
		$data['AppKey'] = $site_config['app_key'];
		$data['AppSecret'] = $site_config['app_secret'];
		$data['SCPOrganizationCode'] = $post['SCPOrganizationCode'];
		if(!empty($post['SCPStudentId'])){
			$data['SCPStudentId'] = $post['SCPStudentId'];
		}
		$data['GradeId'] = $post['GradeId'];
		$data['StudentQuestions'] = array();
		$data['StudentQuestions'] = $post['StudentQuestions'];
		$data['ExaminationId']    = $post['ExaminationId'];
		//cache('postdata',$data);
		$data = json_encode($data);
		$httpstr = $this->kickhttp($url, $data, 'POST',$headers_new,true,true);
		$httpstr = json_decode($httpstr,true);
		return $httpstr;
    }
	
	//获取分享签名
	public function getsign($linkurl='')
	{
	  if ( $linkurl!='' ) {
		$site_config = Db::name('system')->field('value')->where('name', 'site_config')->find();
        $site_config = unserialize($site_config['value']);
		$url = Config('MotikuApiHttp')."Shanceping/WeChatJsSdkInitParameter";
		$headers_new = array();
		$headers_new[] = "Content-Type: text/json";
		$data = array();
		$data['AppKey']    = $site_config['app_key'];
		$data['AppSecret'] = $site_config['app_secret'];
		$data['Url']       = $linkurl;
		$data = json_encode($data);
		$httpstr = $this->kickhttp($url, $data, 'POST',$headers_new,true,true);
		$httpstr = json_decode($httpstr,true);
		return $httpstr;
	  }
	}
	
	/*phpcurl方法 qhyj_edit*/
	function kickhttp($url, $params, $method = 'GET', $header = array(), $multi = false,$gzip = false){
		$opts = array(
				CURLOPT_TIMEOUT        => 1200,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HTTPHEADER     => $header
		);
		/* 根据请求类型设置特定参数 */
		switch(strtoupper($method)){
			case 'GET':
				$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
				break;
			case 'POST':
				//判断是否传输文件
				$params = $multi ? $params : http_build_query($params);
				$opts[CURLOPT_URL] = $url;
				$opts[CURLOPT_POST] = 1;
				$opts[CURLOPT_POSTFIELDS] = $params;
				break;
			default:
				$this->error('不支持的请求方式！');
		}
		// var_dump($opts);
		/* 初始化并执行curl请求 */
		$ch = curl_init();
		if($gzip == true){
			curl_setopt($ch,CURLOPT_ENCODING,'gzip');
		}
		curl_setopt_array($ch, $opts);
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if($error) $this->error('请求发生错误：' . $error);
		return  $data;
	}

}