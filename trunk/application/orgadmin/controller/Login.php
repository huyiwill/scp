<?php
namespace app\orgadmin\controller;
use think\Controller;
use think\Cache;
use think\Cookie;
use think\Config;
use think\Db;
use app\common\model\Organ as OrganModel;

class Login extends Controller
{
	protected $organ_model;
    protected function _initialize()
    {
        parent::_initialize();
		$this->organ_model = new OrganModel();
    }
	
	public function test()
	{
		$key = 'VWM3ME0P';
		$vi = '3KM7WSU2';
		$time = date('Y-m-d H:i:s',time() - 1800);
		$dstr = $this->encrypt('1001|1001|'.base64_encode('测试机构').'|'.$time,base64_encode($key),base64_encode($vi));
		$dstr = urlencode($dstr);
		$this->redirect('orgadmin/index/index',['token'=>$dstr]);
		//echo '<a href="http://192.168.0.159:81/orgadmin/index/index/token/'.$dstr.'.html">login</a>';
	}
	
	/**
    *加密
    * @param <type> $value
    * @return <type>
    */
    public function encrypt ($value,$key,$vi)
    {
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $iv = base64_decode($vi);
        $value = $this->PaddingPKCS7($value);
        $key = base64_decode($key);
        mcrypt_generic_init($td, $key, $iv);
        $ret = base64_encode(mcrypt_generic($td, $value));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }

    /**
    *解密
    * @param <type> $value
    * @return <type>
    */
    public function decrypt ($value,$key,$vi)
    {
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $iv = base64_decode($vi);
        $key = base64_decode($key);
        mcrypt_generic_init($td, $key, $iv);
        $ret = trim(mdecrypt_generic($td, base64_decode($value)));
        $ret = $this->UnPaddingPKCS7($ret);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $ret;
    }

    private function PaddingPKCS7 ($data)
    {
        $block_size = mcrypt_get_block_size('tripledes', 'cbc');
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

    private function UnPaddingPKCS7($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, - 1 * $pad);
    }
	 
	//token解码
    public function index($token)
    {
		$where = array();
		$key = 'VWM3ME0P';
		$vi =  '3KM7WSU2';
		//$token = urldecode($token);
		$info = $this->decrypt($token,base64_encode($key),base64_encode($vi));
		$result = array();
		
		if($info)
		{
			$info_arr = explode('|',$info);
			$organ_id = '';
			$organ_name = '';
			$expiry_time = strtotime($info_arr[count($info_arr) - 1]) + 8*3600;
			
			//判断是否过期
			if(time() - $expiry_time < 15*60){
				if(count($info_arr)>=4){
					//子机构登录
					$parent_org_id = $info_arr[0];
					$parent_org_info = $this->organ_model->where(array('org_id'=>$parent_org_id))->find();
					if($parent_org_info){
						//存在总机构
						if($parent_org_info['expiry_time'] > time()){
							//总机构未过期
							$org_id = $info_arr[1];
							$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
							if($org_info){
								//子机构存在，子机构登录
								$organ_name = $org_info['name'];
								$organ_id = $org_info['org_id'];
							}else{
								//子机构不存在，创建子机构
								$org_arr = array();
								$org_arr['org_id'] = $org_id;
								$org_arr['code'] = $parent_org_id;
								$org_arr['parent_id'] = $parent_org_info['id'];
								$org_arr['name'] = base64_decode($info_arr[2]);
								$org_arr['mobile'] = $parent_org_info['mobile'];
								$org_arr['logo'] = $parent_org_info['logo'];
								$org_arr['level'] = $parent_org_info['level'];
								$org_arr['expiry_time'] = $parent_org_info['expiry_time'];
								$org_arr['create_time'] = time();
								$organ_id = $this->organ_model->insert($org_arr);
								
								$organ_name = $org_arr['name'];
								$organ_id = $org_arr['org_id'];
							}
						}else{
							//总机构已过期
							Cookie::set('u',null);
							$this->redirect('orgadmin/Login/noLogin');
						}
					}else{
						//总机构不存在
						Cookie::set('u',null);
						$this->redirect('orgadmin/Login/noLogin');
					}
				}else{
					//总机构登录
					$org_id = $info_arr[0];
					$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
					if($org_info){
						//总机构存在
						if($parent_org_info['expiry_time'] > time()){
							//总机构未过期,总机构登录
							$organ_name = $org_info['name'];
							$organ_id = $org_info['org_id'];
						}else{
							//总机构已过期
							Cookie::set('u',null);
							$this->redirect('orgadmin/Login/noLogin');
						}
					}else{
						//总机构不存在
						Cookie::set('u',null);
						$this->redirect('orgadmin/Login/noLogin');
					}
				}
			}else{
				Cookie::set('u',null);
				$this->redirect('orgadmin/Login/noLogin');
			}
			
			/*
			if(count($info_arr)>=4)
			{
				// 存在子机构
				$parent_org_id = $info_arr[0];
				$org_id = $info_arr[1];
				$org_name = base64_decode($info_arr[2]);
				$expiry_time = strtotime($info_arr[3])+8*3600;    // 把utc时间设置为本机时间+8个小时
				$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
				
				if(time() - $expiry_time < 15*60){
					if(!$org_info)
					{
						$parent_org_info = $this->organ_model->where(array('org_id'=>$parent_org_id))->find();
						if($parent_org_info)
						{
							// 添加子机构
							$org_arr = array();
							$org_arr['org_id'] = $org_id;
							$org_arr['code'] = $org_id;
							$org_arr['parent_id'] = $parent_org_info['id'];
							$org_arr['name'] = $org_name;
							$org_arr['mobile'] = $parent_org_info['mobile'];
							$org_arr['logo'] = $parent_org_info['logo'];
							$org_arr['level'] = $parent_org_info['level'];
							$org_arr['expiry_time'] = $parent_org_info['expiry_time'];
							$org_arr['create_time'] = time();
							$organ_id = $this->organ_model->insert($org_arr);
							// 子机构
							$organ_name = $organ_id;
							$organ_id = $org_arr['name'];
						}
						else
						{
							$result['status'] = false;
							$result['msg'] = '总机构不存在，机构无法登录系统！';
							//$this->error('总机构不存在，机构无法登录系统！',url('orgadmin/Login/noLogin'));
							Cookie::set('u',null);
							$this->redirect('orgadmin/Login/noLogin');
						}
					}
					else
					{
						if($org_info['expiry_time'] > time()){
							// 子机构
							$organ_name = $org_info['name'];
							$organ_id = $org_info['org_id'];
						}else{
							$result['status'] = false;
							$result['msg'] = '机构已过期！';
							//$this->error('机构已过期！',url('orgadmin/Login/noLogin'));
							Cookie::set('u',null);
							$this->redirect('orgadmin/Login/noLogin');
						}
					}
				}else {
					Cookie::set('u',null);
					//$this->error('账号过期！',url('orgadmin/Login/noLogin'));
					Cookie::set('u',null);
					$this->redirect('orgadmin/Login/noLogin');
				}
			}
			else
			{
				// 总机构登录
				$org_id = $info_arr[0];
				$expiry_time = strtotime($info_arr[1])+8*3600;
				$org_info = $this->organ_model->where(array('org_id'=>$org_id))->find();
				if(time() - $expiry_time < 15*60){
					if($org_info)
					{
						if($org_info['expiry_time'] > time()){
							$organ_name = $org_info['name'];
							$organ_id = $org_info['org_id'];
						}else{
							$result['status'] = false;
							$result['msg'] = '机构已过期！';
							//$this->error('机构已过期！',url('orgadmin/Login/noLogin'));
							Cookie::set('u',null);
							$this->redirect('orgadmin/Login/noLogin');
						}
					}
					else
					{
						$result['status'] = false;
						$result['msg'] = '总机构不存在，机构无法登录系统！';
						//$this->error('总机构不存在，机构无法登录系统！',url('orgadmin/Login/noLogin'));
						Cookie::set('u',null);
						$this->redirect('orgadmin/Login/noLogin');
					}
				}else {
					Cookie::set('u',null);
					//$this->error('账号过期！',url('orgadmin/Login/noLogin'));
					$this->redirect('orgadmin/Login/noLogin');
				}
			}
			*/
			
			$u = $organ_id.'|'.$organ_name.'|'.date('Y-m-d H:i:s',(time() + 3600));
			$dstr = $this->encrypt($u,base64_encode($key),base64_encode($vi));
			Cookie::set('u',$dstr);
			$this->redirect('orgadmin/Examination/index');
		}
		else
		{
			$result['status'] = false;
			$result['msg'] = 'token验证失败，机构无法登录系统！';
			//$this->error('token验证失败，机构无法登录系统！',url('orgadmin/Login/noLogin'));
			Cookie::set('u',null);
			$this->redirect('orgadmin/Login/noLogin');
		}
	}
	
	public function noLogin(){
		return $this->fetch('no_login');
	}
}