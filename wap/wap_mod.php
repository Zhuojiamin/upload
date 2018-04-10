<?php
function wap_header($id,$title,$u="",$t=""){
	header("Content-type: text/vnd.wap.wml;");
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	echo "<!DOCTYPE wml PUBLIC \"-//WAPFORUM//DTD WML 1.1//EN\" \"http://www.wapforum.org/ DTD/wml_1.1.xml\">\n\n";
	echo "<wml>\n";
	if($u){
		echo "<card id=\"$id\" ontimer=\"$u\">\n";
		echo "<timer value=\"$t\" />\n";
	} else{
		echo "<card id=\"$id\">\n";
	}
	echo "<p align=\"center\"><b>$title</b>\n<br/></p>\n";
}

function wap_footer(){
	global $blog_version,$windid;
	echo "<p><a href=\"index.php\">首页</a></p>\n";
	if(empty($windid)){
		echo "<p><a href=\"index.php?prog=login\">登录</a></p>\n";
	}else{
		echo "<p>".$windid."<anchor title=\"submit\">退出<go href=\"index.php?prog=loginout\" method=\"post\"></go></anchor></p>";
	}
	echo "<p align=\"center\"><br/><b>LxBlog V$blog_version</b></p>\n"; 
	echo "</card></wml>\n";
	exit;
}

function wap_output($output){
	echo $output; 
}

function wap_msg($msg,$u="",$t="30"){
	global $db_blogname;
	//wap_header('msg',$db_blogname,$u,$t);
	wap_output("<p align=\"center\">$msg<br/></p>\n");
	wap_footer();
}

function wap_navig(){
	echo "<p><br/>导航</p>\n";
	echo "<p>&gt;&gt;<a href=\"index.php\">首页</a></p>\n";
}

function wap_login($username,$password){
	global $db,$timestamp,$onlineip,$db_ckpath,$db_ckdomain,$ilang;
	$rt = $db->get_one("SELECT uid,password,memberid,groupid,logincheck FROM pw_user WHERE username='$username'");
	if ($rt) {
		$e_login = explode('|',$rt['logincheck']);
		if (($timestamp-$e_login[0])>0 || $e_login[1]>1) {
			$uid = $rt['uid'];
			$pwd = $rt['password'];
			$check_pwd = md5($password);
			if ($pwd==$check_pwd) {
				$groupid = $rt['groupid']=='-1' ? $rt['memberid'] : $rt['groupid'];
				Cookie("ckinfo",$db_ckpath."\t".$db_ckdomain);
				$winduid = $uid;
				$groupid = $groupid;
				$windpwd = PwdCode(md5($password));
			} else {
				global $L_T;
				$L_T = ($timestamp-$e_login[0])>600 ? 5 : $e_login[1];
				$L_T ? $L_T-- : $L_T=5;
				$F_login = "$timestamp|$L_T";
				$db->update("UPDATE pw_user SET onlineip='$onlineip',logincheck='$F_login' WHERE uid='$uid'");
				wap_msg('login_pwd_error');
			}
		} else {
			global $L_T;
			$L_T = 600-($timestamp-$e_login[0]);
			wap_msg('login_forbid');
		}
	} else {
		global $errorname,$errorvalue;
		$errorname = $ilang['lg'.$typename];
		$errorvalue = $typevalue;
		wap_msg('user_not_exists');
	}
	$cktime != 0 && $cktime += $timestamp+0;
	Cookie('bloguser',StrCode($winduid."\t".$windpwd),$cktime);
	Cookie('lastvisit','',0);
	$logininfo = "$timestamp|6";
	$db->update("UPDATE pw_user SET lastvisit='$timestamp',thisvisit='$timestamp',onlineip='$onlineip',logincheck='$logininfo' WHERE uid='$winduid'");
	wap_msg('成功登录');
}

function Loginout(){
	global $db,$timestamp,$db_onlinetime,$winduid,$db_ckpath,$db_ckdomain;
	$thisvisit = $timestamp-$db_onlinetime*1.5;
	$db->update("UPDATE pw_user SET thisvisit='$thisvisit' WHERE uid='$winduid'");
	list($db_ckpath,$db_ckdomain) = explode("\t",GetCookie('ckinfo'));
	Cookie('bloguser','',0);
	Cookie('lastvisit','',0);
	Cookie('ckinfo','',0);
}

?>