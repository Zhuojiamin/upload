<?php
/*
**************************************************************************
*
* PHPWind 首页调用
*
* $color   : 标题后增加显示信息颜色，例如作者，时间，点击数
* $prefix  : 标题前字符，可以用图片 : <img src="http://bbs.phpwind.net/pre.gif" border="0">
*
**************************************************************************
*/
require_once('global.php');
ob_end_clean();
$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();

$color   = '#666666';
$prefix  = array('<li>','◇','·','○','●','- ','□-');
$per = $db_jsper;

$REFERER = parse_url($_SERVER['HTTP_REFERER']);

include(GetLang('msg'));
if(!$db_jsifopen){
	exit("document.write(\"$lang[js_close]\");");
}
if($db_bindurl && $_SERVER['HTTP_REFERER'] && strpos(",$db_bindurl,",",$REFERER[host],") === false){
	exit("document.write(\"$lang[js_bindurl]\");");
}
InitGP(array('action'));
if($action == 'bloginfo'){
	include_once(D_P.'data/cache/bloginfo.php');
	InitGP(array('pre','n_totalblogs','n_totalalbums','n_totalmalbums','n_totalmember','n_tdblogs','n_newmember'));
	$pre       = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
	$bloginfo = '';
	$bloginfo_array= array('totalblogs' => $n_totalblogs,'totalalbums'=>$n_totalalbums,'totalmalbums'=>$n_totalmalbums,'totalmember'=>$n_totalmember,'tdblogs'=>$n_tdblogs,'newmember'=>$n_newmember);
	foreach($bloginfo_array as $key => $value){
		if($value == 1){
			foreach($lang as $k => $v){
				$k == $key && $name = $v;
			}
			if($key == 'newmember'){
				list($n_uid,$n_username) = explode(',',${$key});
				${$key} = "<a href='$db_blogurl/blog.php?uid=$n_uid' target='_blank'>$n_username</a>";
			}
			$bloginfo .= $pre.$name.':'.${$key}.'<br />';
		}
	}
	echo "document.write(\"$bloginfo\");";
}
?>