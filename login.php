<?php
require_once('global.php');
$lxheader = 'login';
((!$db_pptifopen || $db_ppttype == 'client') && $groupid!='2' && $action!='quit') && Showmsg('login_have');
$action!='quit' && $db_pptifopen && $db_ppttype == 'client' && Showmsg('passport_login');
list(,$loginq) = explode("\t",$db_qcheck);

$pre_url = $_SERVER['HTTP_REFERER'];
(!$pre_url || strpos($pre_url,'login.php')!==false || strpos($pre_url,'register.php')!==false) && $pre_url = $db_blogurl;

InitGP(array('forward'));
!$action && $action = 'login';
if ($action != 'quit') {
	list(,,$logingd) = explode("\t",$db_gdcheck);
	include_once(D_P.'data/cache/dbreg.php');
	if ($_POST['step']!=2) {
		!$forward && $forward = $db_blogurl;
		if ($winduid) {
			require_once(R_P.'mod/checkpass_mod.php');
			Loginout();
		}
		!$skin && $skin = $db_defaultstyle;
		list($db_metatitle,$db_metakeyword,$db_metadescrip) = explode('@:wind:@',$db_metadata);
		if (file_exists(D_P."data/style/$skin.php") && strpos($skin,'..')===false) {
			@include Pcv(D_P."data/style/$skin.php");
		} else {
			@include D_P.'data/style/wind.php';
		}
		if ($logingd) {
			$rawwindid = (!$windid) ? 'guest' : rawurlencode($windid);
			$ckurl = str_replace('?','',$ckurl);
		} else {
			$rawwindid = $ckurl = '';
		}
		
		$lgslt = '';
		$logindb = $lg_logindb ? explode("\t",$lg_logindb) : array();
		foreach ($logindb as $value) {
			if ($value == 'domainname' && !$db_userdomain) {
				continue;
			}
			$name = $ilang['lg'.$value];
			$lgslt .= "<option value=\"$value\">$name</option>";
		}
		unset($logindb);
		require_once PrintEot('login');footer('');
	} else {
		InitGP(array('jumpurl','pwtypen','pwtypev','pwpwd','cktime','gdcode','qanswer','qkey'),'P');
		if ($pwtypen && $pwtypev && $pwpwd) {
			require_once(R_P.'mod/checkpass_mod.php');
			list($winduid,$groupid,$windpwd) = checkpass($pwtypen,$pwtypev,$pwpwd);
		} else {
			Showmsg('must_empty');
		}
		$logingd && GdConfirm($gdcode);
		if ($loginq == '1' && !empty($db_question)){
			$answer = unserialize($db_answer);
			$qanswer != $answer[$qkey] && Showmsg('qanswer_error');
		}
		(int)$groupid < 1 && $groupid = '2';
		if (file_exists(D_P."data/groupdb/group_$groupid.php")) {
			require_once(Pcv(D_P."data/groupdb/group_$groupid.php"));
		} else {
			require_once(D_P.'data/groupdb/group_1.php');
		}
		$cktime != 0 && $cktime += $timestamp+0;
		Cookie('bloguser',StrCode($winduid."\t".$windpwd),$cktime);
		Cookie('lastvisit','',0);
		$logininfo = "$timestamp|6";
		$db->update("UPDATE pw_user SET lastvisit='$timestamp',thisvisit='$timestamp',onlineip='$onlineip',logincheck='$logininfo' WHERE uid='$winduid'");
		($db_pptifopen && $db_ppttype == 'server') && require_once(R_P.'mod/passport_server.php');//passport
		!$jumpurl && $jumpurl = $pre_url;
		strpos($jumpurl,'.php')===false && strpos($jumpurl,'index.php')!==false && $jumpurl = "blog.php?uid=$winduid";
		refreshto($jumpurl,'login_in');
	}
} else {
	require_once(R_P.'mod/checkpass_mod.php');
	Loginout();
	($db_pptifopen && $db_ppttype == 'server') && require_once(R_P.'mod/passport_server.php');//passport
	refreshto($db_blogurl,'login_out');
}
?>