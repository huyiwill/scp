<?php
namespace app\api\controller;

use think\Controller;
use think\Session;

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

        return json($result);
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

        return json($result);
    }
}