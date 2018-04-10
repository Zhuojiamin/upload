<?php
include_once('global.php');
require_once(R_P.'mod/charset_mod.php');
include_once R_P.'mod/ipfrom_mod.php';

if(!$_POST['subject'] || !$_POST['content']){
	wap_msg("主题或内容为空!");
}elseif(!$winduid){
	wap_msg("您还没有登录，不能发表日志");
} else{
	$subject = Wap_cv($_POST['subject']);
	$content = Wap_cv($_POST['content']);
	$cid = Wap_cv($_POST['cid']);
	$rt=$db->get_one("SELECT uid FROM pw_user WHERE uid='$winduid'");
	if($rt){
		$subject = Wap_cv($subject);
		$content = Wap_cv($content);
		$subject = convert_charset('utf-8',$db_charset,$subject);
		$content = convert_charset('utf-8',$db_charset,$content);
		$ipfrom  = cvipfrom($onlineip);
		$ipfrom  = str_replace("\n","",$ipfrom);
		$ifcheck=$db_postcheck ? 0 : 1;
		
		@include(D_P.'data/cache/wordfb.php');
		$_FORBIDDB = (array)$_REPLACE+(array)$_FORBID;
		$ifwordsfb = 0;
		$cktitle = $subject;
		$ckcontent = $content;
		foreach ($_FORBIDDB as $value) {
			$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
			$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
		}
		if ($cktitle != $subject) {
			$subject = $cktitle;
			$ifwordsfb = 1;
		}
		if ($ckcontent != $content) {
			$content = $ckcontent;
			$ifwordsfb = 1;
		}
		$db->update("INSERT INTO pw_items(cid,uid,author,type,subject,postdate,allowreply,ifcheck,ifwordsfb,ifhide) VALUES ('$cid','$rt[uid]','$pwuser','blog','$subject','$timestamp','1','$ifcheck','$ifwordsfb','0')");
		$itemid = $db->insert_id();
		$db->update("INSERT INTO pw_blog(itemid,userip,ifsign,ipfrom,ifconvert,content) VALUES('$itemid','$onlineip','0','$ipfrom','0','$content')");
		$userdb = $ifcheck ? array('uid' => $admin_uid,'type' => 'blog','items' => $admindb['items'],'todaypost' => $admindb['todaypost'],'lastpost' => $admindb['lastpost']) : array();
		update_post($userdb);
		wap_msg("日志发表成功","blog.php?itemid=$itemid");
	} else{
		wap_msg("用户名或密码错误!");
	}
}
function update_post($userdb){
	global $db,$db_credit,$timestamp,$tdtime;
	if (!empty($userdb)) {
		$memberid = getmemberid($userdb['items']);
		$typenum = $userdb['type'].'s';
		if ($userdb['lastpost'] < $tdtime) {
			$userdb['todaypost'] = 1;
		} else {
			$userdb['todaypost']++;
		}
		list($rvrc,$money) = explode(',',$db_credit);
		$rvrc = floor($rvrc/10);
		$db->update("UPDATE pw_user SET memberid='$memberid', $typenum=$typenum+1,items=items+1,todaypost='$userdb[todaypost]',lastpost='$timestamp',rvrc=rvrc+'$rvrc',money=money+'$money' WHERE uid='$userdb[uid]'");
	}
}
function getmemberid($nums){
	global $_gmember;
	$gid = 0;
	$_gmember =  $_gmember ? $_gmember : array();
	foreach ($_gmember as $key => $value) {
		(int)$nums>=$value['creditneed'] && $gid = $key;
	}
	return $gid;
}
?>