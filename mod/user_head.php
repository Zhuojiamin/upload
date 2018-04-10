<?php
!function_exists('readover') && exit('Forbidden');

$uid = $blogdb['uid'];
$tkey = '';
list($db_metatitle,$db_metakeyword,$db_metadescrip) = explode('@:wind:@',$db_metadata);

!$blogdb['blogtitle'] && $blogdb['blogtitle'] = $blogdb['username'];
//$allcss = $sqlstyle ? $db->get_one("SELECT css,diycss FROM pw_userskin WHERE sign='$style' AND uid='$uid'") : readover(R_P."theme/$style/template/style.css");
if($sqlstyle){
	$br ="\n";
	$allcss = $db->get_one("SELECT css,diycss FROM pw_userskin WHERE sign='$style' AND uid='$uid'");
	$usercss = $allcss['css'];
	if(empty($usercss)) $usercss = readover(R_P."theme/$style/template/style.css");
	$arr_diycss = unserialize($allcss['diycss']);
	//${'bgchecked_'.$arr_diycss[bgtype]} = 'checked';
	$bgchecked = $arr_diycss[bgtype] ? 'checked' : '';
	$arr_diycss[bgtype] = $arr_diycss[bgtype] == 1 ? 'repeat' : 'no-repeat';
	if(!empty($arr_diycss[bgcolor])) $diycss .= "body { background-color: $arr_diycss[bgcolor]; }".$br;
	if(!empty($arr_diycss[bgimg])) $diycss .= "body { background-image: url($arr_diycss[bgimg]); }".$br;
	if(!empty($arr_diycss[bgtype])) $diycss .= "body { background-repeat: $arr_diycss[bgtype]; }".$br;
	if(!empty($arr_diycss[bannerimg])) $diycss .= "#header{background-image:url($arr_diycss[bannerimg]);background-repeat:no-repeat;}".$br;
}else{
	$usercss = readover(R_P."theme/$style/template/style.css");
}
!$db_domain && !$islocalhost && $db_domain = substr($_SERVER['HTTP_HOST'],strpos($_SERVER['HTTP_HOST'],'.')+1);

$H = (!$db_userdomain || !$db_domain || !$blogdb['domainname']) ? "$db_blogurl/?$blogdb[username]" : "http://$blogdb[domainname].$db_domain";

$blogdb['flashurl'] && !preg_match('/^http/is',$blogdb['flashurl']) && $blogdb['flashurl'] = "$imgpath/skin/$blogdb[flashurl]";
$blogdb['introduce'] = str_replace(array("\r","\n"),array('','<br />'),$blogdb['introduce']);

$headerdb = $blogdb['headerdb'] ? unserialize($blogdb['headerdb']) : array();
$leftdb   = $blogdb['leftdb'] ? unserialize($blogdb['leftdb']) : array();
unset($blogdb['headerdb'],$blogdb['leftdb']);

require_once(R_P.'template/default/wind/lang_user.php');
foreach ($index_name as $key => $value) {
	$headerdb[$key]['name'] = $value;
	$headerdb[$key]['sign'] = $key;
	if($key == 'aboutme'){
		$headerdb[$key]['url']  = "blog.php?do=aboutme&uid=$uid&type=aboutme";
	}elseif($key == 'bbs'){
		$headerdb[$key]['url']  = "blog.php?do=bbs&uid=$uid";
	}else{
		$headerdb[$key]['url']  = "blog.php?do=list&uid=$uid&type=$key";
	}
}
$header_order = $index_order = array();
!$db_teamifopen && $headerdb['team']['ifshow'] = 0;
!$db_cbbbsopen && $headerdb['combine']['ifshow'] = 0;
foreach ($headerdb as $key => $value) {
	$header_order[$key] = (int)$value['order'];
}
array_multisort($header_order,SORT_ASC,$headerdb);

$indexdb = array('blog','photo','bookmark','goods','file','music');
$index_order['index'] = array();
foreach ($headerdb as $key => $value) {
	if ($value['ifshow']) {
		if (in_array($key,$indexdb) && strpos(",$_GROUP[module],",",$key,")===false) {
			continue;
		}
		if($key == 'bbs' && $db_cbbbsopen == '0'){
			continue;
		}
		$value['note'] && $value['name'] = $value['note'];
		!$value['url'] && $value['url'] = 'javascript:;';
		$index_order[$key] = $value;
	}
}
unset($headerdb,$header_order);
if($uid == $winduid){
	$colorinfo = '<div id="colorbox1" class="menu" style="display:none;z-index:1000;"><div id="colorbox">';
	foreach($colors as $key => $value){
		$colorinfo .= '<div unselectable="on" style="background:#'.$value.'" onClick="SetC(\''.$value.'\')"></div>';
	}
	$colorinfo .= '</div></div>';
}
include_once(getPath('header'));
?>