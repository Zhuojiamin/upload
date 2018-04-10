<?php
require_once('global.php');

if ($db_userdomain) {
	$domainhold = array_merge(explode(' ',$db_domainhold),array('www','blog','bbs'));
	$pre_host = strtolower(substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],'.')));
	!$db_domain && $db_domain = substr($_SERVER['HTTP_HOST'],strpos($_SERVER['HTTP_HOST'],'.')+1);
	$uid = (strpos($B_url,"$pre_host.")===false && !in_array($pre_host,$domainhold)) ? $db->get_value("SELECT uid FROM pw_userinfo WHERE domainname='$pre_host'") : 0;
	if ((int)$uid>0) {
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		if ($db_ckdomain == ".$db_domain" && strlen($pre_host) > $rg_domainmin && strlen($pre_host) < $rg_domainmax) {
			ObHeader("http://$pre_host.$db_domain/blog.php?uid=$uid");
		} else {
			ObHeader("$B_url/blog.php?uid=$uid");
		}
		exit;
	} else {
		$uid = null;
	}
}

if(!empty($_SERVER['QUERY_STRING'])){
	$querystring = addslashes($_SERVER['QUERY_STRING']);
	$uid = $db->get_value("SELECT uid FROM pw_user WHERE username='$querystring'");
	if(empty($uid)){
		Showmsg('username_empty');
	}else{
		ObHeader("$B_url/blog.php?uid=$uid");
	}
}

@include_once(D_P.'data/cache/mod_cache.php');
@include_once(D_P.'data/cache/novosh_cache.php');
@include_once(D_P.'data/cache/bloginfo.php');
require_once(R_P.'mod/header_inc.php');
require_once(R_P.'mod/template_mod.php');
list($newmember_uid,$newmember_name) = explode(',',$newmember);
$imgnums = count($_IMGPLAYER);
$imgpics = $imglinks = $imgtexts = '';
foreach ((array)$_IMGPLAYER as $value) {
	$imgpics  .= ($imgpics  ? '|' : '').$value['attachurl'];
	$imglinks .= ($imglinks ? '|' : '')."{$articleurl}type=$value[type]&itemid=$value[itemid]";
	$imgtexts .= ($imgtexts ? '|' : '').substrs($value['subject'],19);
}

if(!$winduid && (!$db_pptifopen || $db_ppttype == 'server')){
	list(,,$logingd) = explode("\t",$db_gdcheck);
	include_once(D_P.'data/cache/dbreg.php');
	if ($logingd) {
			$rawwindid = (!$windid) ? 'guest' : rawurlencode($windid);
			$ckurl = str_replace('?','',$ckurl);
		} else {
			$rawwindid = $ckurl = '';
		}
		$lgslt = '';
		$logindb = $lg_logindb ? explode("\t",$lg_logindb) : array();
		foreach ($logindb as $value) {
			if ($value == 'domainname') {
				continue;
			}
			$name = $ilang['lg'.$value];
			$lgslt .= "<option value=\"$value\">$name</option>";
		}
		unset($logindb);	
}

require_once PrintEot('index');
mod_index();
footer();
?>