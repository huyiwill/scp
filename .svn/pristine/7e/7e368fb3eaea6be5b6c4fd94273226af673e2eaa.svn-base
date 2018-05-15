<?php

// Api接口返回err类型
function Error_Message($data){
	$error = array(
		1001   => '缺少必要的参数信息',
		1002   => '不存在该组织结构'
	);
	return $error[$data];
}

// 返回数据方法
function outPut($data,$page = NULL)
{
	if (!is_array($data)) {
		$status = array(
			'status' => array(
				'succeed' => 0,
				'error_code' => $data,
				'error_desc' => Error_Message($data)
			)
		);
		die(json_encode($status));
	}
	if (isset($data['data'])) {
		$data = $data['data'];
	}
	$data = array_merge(array('data'=>$data), array('status' => array('succeed' => 1)));
	if (!empty($pager)) {
		$data = array_merge($data, array('page'=>$pager));
	}
	die(json_encode($data));
}
