<?php

use think\Db;

/**
 * 获取分类所有子分类
 * @param int $cid 分类ID
 * @return array|bool
 */
function get_category_children($cid)
{
    if (empty($cid)) {
        return false;
    }

    $children = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->select();

    return array2tree($children);
}

/**
 * 根据分类ID获取文章列表（包括子分类）
 * @param int   $cid   分类ID
 * @param int   $limit 显示条数
 * @param array $where 查询条件
 * @param array $order 排序
 * @param array $filed 查询字段
 * @return bool|false|PDOStatement|string|\think\Collection
 */
function get_articles_by_cid($cid, $limit = 10, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->limit($limit)->select();

    return $article_list;
}

/**
 * 根据分类ID获取文章列表，带分页（包括子分类）
 * @param int   $cid       分类ID
 * @param int   $page_size 每页显示条数
 * @param array $where     查询条件
 * @param array $order     排序
 * @param array $filed     查询字段
 * @return bool|\think\paginator\Collection
 */
function get_articles_by_cid_paged($cid, $page_size = 15, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->paginate($page_size);

    return $article_list;
}

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int   $pid
 * @param int   $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    static $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level;
            $list[]     = $v;
            array2level($array, $v['id'], $level + 1);
        }
    }

    return $list;
}

/**
 * 构建层级（树状）数组
 * @param array  $array          要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name       父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }

    return $tree;
}

/**
 * 子元素计数器
 * @param array $array
 * @param int   $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }

    return $counter;
}

/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name]))
                $item[$child_key_name] = [];
            $item[$child_key_name][] = $child;
        }
    }

    return $parent;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name)
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function is_mobile()
{
    static $is_mobile;

    if (isset($is_mobile)) {
        return $is_mobile;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
    ) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

/**
 * 手机号格式检查
 * @param string $mobile
 * @return bool
 */
function check_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';

    return preg_match($reg, $mobile) ? true : false;
}

function getLevel(){
	$level_list = array(1=>'通用',2=>'白金',3=>'钻石',4=>'百夫长');
	return $level_list;
}

function getLevelName($level){
	$level_list = getLevel();
	return $level_list[$level];
}

function getAuth($level){
	$auth = array(1=>array('edit_logo'=>false,'hide_ry_logo'=>false,'hide_ry_support'=>false,'exam_rank'=>false,'question_num'=>5,'exam_pub'=>false,'pub_link'=>false),
				  2=>array('edit_logo'=>true,'hide_ry_logo'=>false,'hide_ry_support'=>false,'exam_rank'=>false,'question_num'=>10,'exam_pub'=>false,'pub_link'=>false),
				  3=>array('edit_logo'=>true,'hide_ry_logo'=>true,'hide_ry_support'=>false,'exam_rank'=>true,'question_num'=>0,'exam_pub'=>1,'pub_link'=>true),
				  4=>array('edit_logo'=>true,'hide_ry_logo'=>true,'hide_ry_support'=>true,'exam_rank'=>true,'question_num'=>0,'exam_pub'=>2,'pub_link'=>true));
	return $auth[$level];
}

function getGradeList(){
	$grade_list = array(1=>'一年级',2=>'二年级',3=>'三年级',4=>'四年级',5=>'五年级',6=>'六年级',7=>'初一',8=>'初二',9=>'初三',10=>'高一',11=>'高二',12=>'高三');
	return $grade_list;
}

function getGrade($grade){
	$grade_list = getGradeList();
	return $grade_list[$grade];
}

function getStageList(){
	$stage_list = array(1=>'小学',2=>'小学',3=>'小学',4=>'小学',5=>'小学',6=>'小学',7=>'初中',8=>'初中',9=>'初中',10=>'高中',11=>'高中',12=>'高中');
	return $stage_list;
}

function getStage($grade){
	$stage_list = getStageList();
	return $stage_list[$grade];
}

function getSubjectList(){
	$subject_list = array(1=>array('id'=>1,'name'=>'语文','stage'=>1,'stageName'=>'初中','icon'=>'yw'),
						  2=>array('id'=>2,'name'=>'数学','stage'=>1,'stageName'=>'初中','icon'=>'sx'),
						  3=>array('id'=>3,'name'=>'英语','stage'=>1,'stageName'=>'初中','icon'=>'yy'),
						  4=>array('id'=>4,'name'=>'物理','stage'=>1,'stageName'=>'初中','icon'=>'wl'),
						  5=>array('id'=>5,'name'=>'化学','stage'=>1,'stageName'=>'初中','icon'=>'hx'),
						  6=>array('id'=>6,'name'=>'生物','stage'=>1,'stageName'=>'初中','icon'=>'sw'),
						  7=>array('id'=>7,'name'=>'历史','stage'=>1,'stageName'=>'初中','icon'=>'ls'),
						  8=>array('id'=>8,'name'=>'地理','stage'=>1,'stageName'=>'初中','icon'=>'dl'),
						  9=>array('id'=>9,'name'=>'语文','stage'=>2,'stageName'=>'高中','icon'=>'yw'),
						  10=>array('id'=>10,'name'=>'数学','stage'=>2,'stageName'=>'高中','icon'=>'sx'),
						  11=>array('id'=>11,'name'=>'英语','stage'=>2,'stageName'=>'高中','icon'=>'yy'),
						  12=>array('id'=>12,'name'=>'物理','stage'=>2,'stageName'=>'高中','icon'=>'wl'),
						  13=>array('id'=>13,'name'=>'化学','stage'=>2,'stageName'=>'高中','icon'=>'hx'),
						  14=>array('id'=>14,'name'=>'生物','stage'=>2,'stageName'=>'高中','icon'=>'sw'),
						  15=>array('id'=>15,'name'=>'历史','stage'=>2,'stageName'=>'高中','icon'=>'ls'),
						  16=>array('id'=>16,'name'=>'地理','stage'=>2,'stageName'=>'高中','icon'=>'dl'),
						  17=>array('id'=>17,'name'=>'政治','stage'=>2,'stageName'=>'高中','icon'=>'zz'),
						  18=>array('id'=>18,'name'=>'通用技术','stage'=>2,'stageName'=>'高中','icon'=>'ty'),
						  19=>array('id'=>19,'name'=>'思想品德','stage'=>1,'stageName'=>'初中','icon'=>'pd'),
						  20=>array('id'=>20,'name'=>'奥数','stage'=>3,'stageName'=>'小学','icon'=>'as'),
						  21=>array('id'=>21,'name'=>'语文','stage'=>3,'stageName'=>'小学','icon'=>'yw'),
						  22=>array('id'=>22,'name'=>'数学','stage'=>3,'stageName'=>'小学','icon'=>'sx'),
						  23=>array('id'=>23,'name'=>'英语','stage'=>3,'stageName'=>'小学','icon'=>'yy'));
	return $subject_list;
}

function getSubject($subject){
	$subject_list = getSubjectList();
	return $subject_list[$subject]['name'];
}

function getStatusList(){
	$status_list = array(1=>'待上架',2=>'已上架',3=>'已下架',4=>'被删除');
	return $status_list;
}

function getStatus($status){
	$status_list = getStatusList();
	return $status_list[$status];
}

function getTemplateList(){
	$template_list = array(1=>'基础模版',2=>'基础模版+做题反馈',3=>'能力雷达模版',4=>'能力雷达模版+做题反馈');
	return $template_list;
}

function getTemplate($template){
	$template_list = getTemplateList();
	return $template_list[$template];
}

function getPeriodValidity($stime,$etime){
	$ntime = time();
	$stime = strtotime(date('Y-m-d',$stime));
	$stime_text = date('Y-m-d',$stime);
	$etime_text = date('Y-m-d',$etime);
	
	if($etime < $ntime){
		return $stime_text.' 至 '.$etime_text.' <span style="color: red;">已过期</span>';
	}else{
		list($date_1['y'],$date_1['m']) = explode("-",date('Y-m',$stime));
		list($date_2['y'],$date_2['m']) = explode("-",date('Y-m',$etime));
		$m = abs(($date_2['y']-$date_1['y'])*12 + $date_2['m']-$date_1['m']);
		$y_num = intval($m/12);
		$m_num = $m-$y_num*12;
		if($y_num > 0 && $m_num > 0){
			return $stime_text.' 至 '.$etime_text.' 有效('.$y_num.'年'.$m_num.'月)';
		}else if(!($y_num > 0) && $m_num > 0){
			return $stime_text.' 至 '.$etime_text.' 有效('.$m_num.'月)';
		}else if($y_num > 0 && !($m_num > 0)){
			return $stime_text.' 至 '.$etime_text.' 有效('.$y_num.'年)';
		}else {
			$d_num = intval(($etime - $stime) / 86400);
			return $stime_text.' 至 '.$etime_text.' 有效('.$d_num.'天)';
		}
	}
}

function best_sort($a,$b)
{
	if($a['Score']==$b['Score'])
	{
		return 0;
	}
	return $a['Score']>$b['Score']?1:-1;
}

/*字符串剪切*/
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(mb_strlen($str,$charset)>$length){
        if(function_exists("mb_substr")){
            if($suffix)
                return mb_substr($str, $start, $length, $charset)."...";
            else
                return mb_substr($str, $start, $length, $charset);
        }elseif(function_exists('iconv_substr')) {
            if($suffix)
                return iconv_substr($str,$start,$length,$charset)."...";
            else
                return iconv_substr($str,$start,$length,$charset);
        }
        $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if($suffix) return $slice."…";
        return $slice;
    }else{
        return $str;
    }
}

function deletep($str){
	$str = str_replace('<p','<span',$str);
	$str = str_replace('</p>','</span>',$str);
	$str = str_replace('<div','<span',$str);
	$str = str_replace('</div>','</span>',$str);
	return $str;
}