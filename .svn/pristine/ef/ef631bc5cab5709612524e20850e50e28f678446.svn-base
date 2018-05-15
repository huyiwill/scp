<?php
namespace app\api\controller;

use think\Controller;
use think\Session;
use think\Request;
/**
 * 通用上传接口
 * Class Upload
 * @package app\api\controller
 */
class Upload extends Controller
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
     * 通用图片上传接口
     * @return \think\response\Json
     */
    public function upload()
    {
        $config = [
            'size' => 2097152,
            'ext'  => 'jpg,gif,png,bmp'
        ];

        $file = $this->request->file('file');

        $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
        $save_path   = '/uploads/';
        $info        = $file->validate($config)->move($upload_path);
		
        if ($info) {
			$event = controller('Motiku', 'controller');
			$url = str_replace('\\', '/', $save_path . $info->getSaveName());
			$res = $event->upload($url);
			
			if($res['ApiResultType'] == 1){
				$url = $res['Value'];
				$result = [
					'error' => 0,
					'url'   => $url
				];
			}else{
				$result = [
					'error'   => 1,
					'message' => '上传失败'
				];
			}
			/*
            $result = [
                'error' => 0,
                'url'   => str_replace('\\', '/', $save_path . $info->getSaveName())
            ];
			*/
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }
		
		echo json_encode($result);
        //return json($result);
    }
	
	/**
     * 通用word上传接口
     * @return \think\response\Json
     */
    public function uploadWord()
    {
        $config = [
            'size' => 1024000000,
            'ext'  => 'docx,doc'
        ];

        $file = $this->request->file('file');
		$save_path   = '/uploads/word/';
		$url = array();
		$fileName = array();
		$error = 0;
		$message = '';
		
		foreach($file as $k=>$v){
			$upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads/word');
			$info        = $v->validate($config)->move($upload_path);
			$fileInfo = $info->getInfo();
			
			if ($info) {
				$fileInfo = $info->getInfo();
				$fname =  explode('.',$fileInfo['name']);
				array_push($fileName,$fname[0]);
				array_push($url, str_replace('\\', '/', $save_path . $info->getSaveName()));
			}else{
				$error = 1;
				$message = $v->getError();
			}
		}
		
		if($error == 0){
			$event = controller('Motiku', 'controller');
			$data = array();
			$i=0;
			foreach($url as $k=>$v){
				$res = $event->yanzheng($v);
				$data[$i]['url'] = $v;
				$data[$i]['ApiResultType'] = $res['ApiResultType'];
				
				if($res['ApiResultType'] == 1){
					$data[$i]['subjectid'] = $res['Value']['CourseId'];
					$data[$i]['subject'] = $res['Value']['CourseName'];
					$data[$i]['exam_num'] = sizeof($res['Value']['QuestionIds']);
					$data[$i]['question_ids'] = implode(',',$res['Value']['QuestionIds']);
					$data[$i]['fileName'] = $fileName[$k];
					$res2 = $event->upload($v);
					if($res2['ApiResultType'] == 1){
						$data[$i]['ryun_url'] = $res2['Value'];
					}else{
						$data[$i]['ryun_url'] ='';
					}
				}else{
					$data[$i]['fileName'] = $fileName[$k];
					$data[$i]['ResultMessage'] = $res['ResultMessage'];
					$data[$i]['subject'] = $res['Value']['CourseName'];
				}
				$i++;
			}
			 $result = [
                'error' => 0,
                'url'   => $url,
				'data'  => $data
            ];
		}else{
			$result = [
                'error'   => 1,
                'message' => $message
            ];
		}
		
		echo json_encode($result);
		
		die;
        //return json($result);
    }
	
	
  public function picupload(){//图片上传
	$exts       = 'jpg,gif,png,jpeg';
	$maxSize    = 1024*1024*1024*10;
	$from       = input('post.from','');
	$uploadname = ($act == '') ? 'Filedata' : 'imgFile';
	$file       = request()->file($uploadname);
	$validate   = array('size'=>$maxSize,'ext'=>$exts);
	$movepath   = '';
	$dpath      = date('Ymd');
	$movepath   = ROOT_PATH . config('upload_path').'images';
	$info       = $file->rule('uniqid')->validate($validate)->move($movepath.'/'.$dpath);
	if ( $info ) {
	  $iswater  = input('post.iswater',0,'intval'); //是否加水印
	  $width    = input('post.width',0,'intval');
	  $height   = input('post.height',0,'intval');
	  $imgPath  = $dpath.'/'.$info->getSaveName();
	  $json     = array('file'=>$imgPath,'error'=>'');  
	  json($json);
	} else {
	  $json     = array('file'=>'','error'=>$file->getError());
	  json($json);
	}
  }
  
  //裁剪
  public function croppic($conf = array()){
    $path    = isset($conf['path'])    ? $conf['path']    : '';
	$w       = isset($conf['w'])       ? $conf['w']       : 0;
	$h       = isset($conf['h'])       ? $conf['h']       : 0;
	$x       = isset($conf['x'])       ? $conf['x']       : 0;
	$y       = isset($conf['y'])       ? $conf['y']       : 0;
	$r       = isset($conf['r'])       ? $conf['r']       : 0;
	$iswater = isset($conf['iswater']) ? $conf['iswater'] : 0;
	$delpic  = isset($conf['delpic'])  ? $conf['delpic']  : 0;
	if ( $path == '' ) return array('state'=>0,'msg'=>'图片不存在');
	if ( $w == 0 || $h == 0 ) return array('state'=>0,'msg'=>'裁剪图片宽高不得为0像素');
	$abspath  = config('upload_path').'images/';
	if ( !file_exists($abspath.$path) ) return array('state'=>0,'msg'=>'图片不存在');
    $img = \think\Image::open($abspath.$path);
	$info = pathinfo($path);  
	$savefile = '';
    $savefile .= (isset($info['dirname']) && $info['dirname']!='') ? $info['dirname'].'/' : '';
	$md5file   = md5($info['filename'].'_'.$w.'_'.$h);
	$savefile .= substr($md5file,0,13).'.'.$info['extension'];
    $img->crop($w,$h,$x,$y)->save($abspath.$savefile);
	if(!$delpic) @unlink($abspath.$path);
	return array('state'=>1,'msg'=>'','file'=>$savefile);
  }
}