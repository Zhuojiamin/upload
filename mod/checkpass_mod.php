<?php
!function_exists('readover') && exit('Forbidden');

function Loginout(){
	global $db,$timestamp,$db_onlinetime,$winduid,$db_ckpath,$db_ckdomain;
	$thisvisit = $timestamp-$db_onlinetime*1.5;
	$db->update("UPDATE pw_user SET thisvisit='$thisvisit' WHERE uid='$winduid'");
	list($db_ckpath,$db_ckdomain) = explode("\t",GetCookie('ckinfo'));
	Cookie('bloguser','',0);
	Cookie('lastvisit','',0);
	Cookie('ckinfo','',0);
}
function checkpass($typename,$typevalue,$password){
	global $db,$timestamp,$onlineip,$db_ckpath,$db_ckdomain,$ilang;
	if ($typename!='username' && $typename!='uid') {
		$from = $typename=='domainname' ? 'pw_userinfo' : 'pw_user';
		$count = $db->get_value("SELECT COUNT(*) FROM $from WHERE $typename='$typevalue'");
		$count > 1 && Showmsg('login_limit');
	}
	$SQL = $typename=='domainname' ? "pw_userinfo i LEFT JOIN pw_user u USING(uid) WHERE i.domainname='$typevalue'" : "pw_user WHERE $typename='$typevalue'";
	!$SQL && Showmsg('undefined_action');
	$rt = $db->get_one("SELECT uid,password,memberid,groupid,logincheck FROM $SQL");
	if ($rt) {
		$e_login = explode('|',$rt['logincheck']);
		if (($timestamp-$e_login[0])>600 || $e_login[1]>1) {
			$uid = $rt['uid'];
			$pwd = $rt['password'];
			$check_pwd = md5($password);
			if ($pwd==$check_pwd) {
				$groupid = $rt['groupid']=='-1' ? $rt['memberid'] : $rt['groupid'];
				Cookie("ckinfo",$db_ckpath."\t".$db_ckdomain);
				return array($uid,$groupid,PwdCode(md5($password)));
			} else {
				global $L_T;
				$L_T = ($timestamp-$e_login[0])>600 ? 5 : $e_login[1];
				$L_T ? $L_T-- : $L_T=5;
				$F_login = "$timestamp|$L_T";
				$db->update("UPDATE pw_user SET onlineip='$onlineip',logincheck='$F_login' WHERE uid='$uid'");
				Showmsg('login_pwd_error');
			}
		} else {
			global $L_T;
			$L_T = 600-($timestamp-$e_login[0]);
			Showmsg('login_forbid');
		}
	} else {
		global $errorname,$errorvalue;
		$errorname = $ilang['lg'.$typename];
		$errorvalue = $typevalue;
		Showmsg('user_not_exists');
	}
	return true;
}
function GdConfirm($code){
	$cknum = GetCookie('cknum');
	Cookie('cknum','',0);
	(!$code || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$code)) && Showmsg('gdcode_error');
}
function Loginipwrite($winduid){
	global $db,$timestamp,$onlineip;
	$logininfo = "$onlineip|$timestamp|6";
	$db->update("UPDATE pw_user SET lastvisit=thisvisit,thisvisit='$timestamp',onlineip='$logininfo' WHERE uid='$winduid'");
}
?>