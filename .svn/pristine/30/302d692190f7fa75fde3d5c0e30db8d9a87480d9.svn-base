<?php
namespace app\orgadmin\model;

use think\Model;
use think\Db;
use think\Request;
use think\Image;

class File{
   
  protected $table = '';
  
  public function picupload($act=''){//图片上传
    $data       = $this->uploadSet();
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
	  return array('file'=>$imgPath,'error'=>'');
	} else {
	  return array('file'=>'','error'=>$file->getError());
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
	//if(!$delpic) @unlink($abspath.$path);
	return array('state'=>1,'msg'=>'','file'=>$savefile);
  }
  
  //添加水印
  public function addwater($imgPath,$iswater=0,$act=''){
	  return $imgPath;
	  $data  = $this->uploadSet();
	  if($iswater){
	   return ($imgPath);
	  }
	  if(($data['iswater']==1 && $data['waterpic']!='') || ($data['iswater']==1 && $data['fonttext']!='')){
		$wpath    = config('upload_path').'images/';
		$image    = \think\Image::open($abspath.$imgPath);
		if($data['waterpic']!=''){ //加图片水印
	      $image->water($wpath.$data['waterpic'],$data['waterpos'],$data['wateralpha'])->save($abspath.$imgPath);
		  return ($imgPath);
		}
		if($data['fonttext']!=''){ //添加文字水印
		  $facetype = ($data['facetype']==0) ? 'c:\\WINDOWS\\Fonts\\simsun.ttc' : dirname(THINK_PATH).'/public/water/'.$data['facetype'].'.ttf';
		  $image->text($data['fonttext'],$facetype,$data['fontsize'],$data['fontcolor'],$data['fontpos'])->save($abspath.$imgPath);
		  return ($imgPath);
		}
	  }
	  return $imgPath;
  }

  //私有裁剪
  public function thumb($h,$w,$file,$act='',$type=1,$savefile=''){
	 $abspath  = config('upload_path').'images/';
     if(!file_exists($abspath.$file)){
	   return false;
	 }else{
	   $img =  \think\Image::open($abspath.$file);
	   if($savefile==''){
		 $info = pathinfo($file);  
	     $savefile = '';
		 $savefile .= (isset($info['dirname']) && $info['dirname']!='') ? $info['dirname'].'/' : '';
		 $savefile .= $info['filename'].'_'.$w.'_'.$h.'.'.$info['extension'];
	   }
	   $img->thumb($w,$h,$type)->save($abspath.$savefile);
	   //删除掉原图
	   @unlink(config('upload_path').'images/'.$file);
	   return $savefile;
	 }
  }
  
  //获取配置
  private function uploadSet(){
    if (!$uploadField = cache('uploadField')) {
	  $data  = array('iswater'=>0,'fontpos'=>5,'waterpos'=>5,'waterpic'=>'','fonttext'=>'闪测评','fontsize'=>30,'fontcolor'=>'#ff0000','facetype'=>0,'rotation'=>0,'wateralpha'=>25,'picsuffix'=>'','filesuffix'=>'','picsize'=>0,'filesize'=>0,'picmaxwidth'=>0,'picmaxsize'=>0,'cropwidth'=>0);
	  cache('uploadField',$data,3600*24);
	  return $data;
	} else {
	  return $uploadField;
	}
  }

  
  //文件上传
  public function fileupload($act = '',$uploadType=''){
    $data    = $this->uploadSet();
	$exts    = ($data['filesuffix']!='') ? explode(',',$data['filesuffix']) : '';
	$maxSize = 1024*1024*1024*10;
	$fname   = config('upload_path').'files/';
	$dpath      = date('Ymd');
	$movepath   = ROOT_PATH . $fname;
	$uploadname = ($act == '') ? 'Filedata' : 'imgFile';
	$validate   = array('size'=>$maxSize,'ext'=>$exts);
	$file       = request()->file($uploadname);
	$info       = $file->rule('uniqid')->validate($validate)->move($movepath.'/'.$dpath);
	if ($info) {
	  return array('file'=>$dpath.'/'.$info->getSaveName(),'error'=>'');
	} else {
	  return array('file'=>'','error'=>$file->getError());
	} 
  }
  
}
