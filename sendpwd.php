<?php
require_once('global.php');
InitGP(array('action'));
!$action && $action='sendpwd';
list(,$loginq) = explode("\t",$db_qcheck);
if($action=='sendpwd'){
	list(,,$logingd) = explode("\t",$db_gdcheck);
	if(!$_POST['step']){
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
		require_once PrintEot('sendpwd');footer('');
	} elseif($_POST['step']==2){
		InitGP(array('username','email','qkey','qanswer','gdcode'));
		$userarray = $db->get_one("SELECT password,email,regdate FROM pw_user WHERE username='$username'");
		if($userarray['email'] != $email){
			Showmsg('email_error',1);
		}
		$logingd && GdConfirm($gdcode);
		if ($loginq == '1' && !empty($db_question)){
			$answer = unserialize($db_answer);
			$qanswer != $answer[$qkey] && Showmsg('qanswer_error');
		}
		if($userarray){
			if($timestamp-GetCookie('lastwrite')<=60){
				$gp_postpertime = 60;
				Showmsg('sendpwd_limit',1);
			}
			Cookie('lastwrite',$timestamp);
			$send_email = $userarray['email'];
			$submit     = $userarray['regdate'];
			$submit    .= md5(substr($userarray['password'],10));
			$pwuser   = rawurlencode($username);
			require_once(R_P.'mod/sendemail.php');
			if(sendemail($send_email,'email_sendpwd_subject','email_sendpwd_content','email_additional')){
				Showmsg('mail_success',1);
			} else{
				Showmsg('mail_failed',1);
			}
		} else{
			$errorname = Char_cv($pwuser);
			Showmsg('user_not_exists',1);
		}
	}
} elseif($action=='getback'){
	InitGP(array('pwuser','submit'));
	!$skin && $skin = $db_defaultstyle;
	list($db_metatitle,$db_metakeyword,$db_metadescrip) = explode('@:wind:@',$db_metadata);
		if (file_exists(D_P."data/style/$skin.php") && strpos($skin,'..')===false) {
			@include Pcv(D_P."data/style/$skin.php");
		} else {
			@include D_P.'data/style/wind.php';
		}
	if($pwuser==$manager){
		Showmsg('undefined_action',1);
	}
	$detail = $db->get_one("SELECT password,regdate FROM pw_user WHERE username='$pwuser'");
	if($detail){
		$is_right  = $detail['regdate'];
		$is_right .= md5(substr($detail['password'],10));
		if($submit==$is_right){
			if(!$_POST['jop']){
				require_once PrintEot('getpwd');footer('');
			} elseif($_POST['jop']==2){
				InitGP(array('new_pwd','pwdreapt'));
				if($new_pwd!=$pwdreapt || !$new_pwd){
					Showmsg('password_confirm',1);
				} else{
					$new_pwd = stripslashes($new_pwd);
					$new_pwd = str_replace("\t","",$new_pwd);
					$new_pwd = str_replace("\r","",$new_pwd);
					$new_pwd = str_replace("\n","",$new_pwd);
					$new_pwd = md5($new_pwd);
					$db->update("UPDATE pw_user SET password='$new_pwd' WHERE username='$pwuser'");
					Showmsg('password_change_success',1);
				}
			}
		} else{
			Showmsg('password_confirm_fail',1);
		}
	} else{
		$errorname = Char_cv($pwuser);
		Showmsg('user_not_exists',1);
	}
}

function GdConfirm($code){
	$cknum = GetCookie('cknum');
	Cookie('cknum','',0);
	(!$code || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$code)) && Showmsg('gdcode_error');
}
?>