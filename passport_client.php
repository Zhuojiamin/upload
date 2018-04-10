<?php
require_once('global.php');

InitGP(array('userdb','forward','verify'),'G');
!$db_pptifopen && exit("LxBlog: Passport closed");
($db_ppttype != 'client' || md5($action.$userdb.$forward.$db_pptkey) != $verify) && exit("LxBlog: Illegal request");

$sqldb = array();
$bak_hash = $db_hash;
$db_hash = $db_pptkey;
parse_str(StrCode($userdb,'DECODE'),$userdb);

$jumpurl = $userdb_encode = $falsegroup = '';
if ($action=='login') {
	(!$userdb['time'] || !$userdb['username'] || !$userdb['password'] || !$userdb['email']) && exit("LxBlog: Lack of parameters");
	foreach ($userdb as $key => $value) {
		$userdb[$key] = addslashes($value);
	}
	
	($timestamp-$userdb['time']>3600) && exit('LxBlog: Passport request expired');
	//combine
	if ($db_cbbbsopen) {
		require_once(R_P.'mod/passport.php');
		if (!CheckGroup($userdb['uid'])) {
			$falsegroup = '1';
			substr($forward,-1) != '/' && strpos($forward,'.php')===false && $forward = $forward.'/';
			$forward .= '?nogroups';
		}
	}
	//combine
	
	if (!$falsegroup) {
		$user_field = array('username','password','email');
		$credit_field = array('rvrc','money','credit');
		$sqldb = array_merge($user_field,$credit_field);
		empty($sqldb) && exit("LxBlog: Illegal request");
		$sqldb = ','.implode(',',$sqldb);
		$rt = $db->get_one("SELECT uid $sqldb FROM pw_user WHERE username='".Char_cv(trim($userdb[username]))."'");
		$sql1 = '';
		if ($rt['uid']) {
			foreach ($userdb as $key => $value) {
				($rt[$key] != $value && (in_array($key,$user_field) || in_array($key,$credit_field) && strpos(",$db_pptcredit,",",$key,")!==false)) && $sql1 .= ($sql1 ? ',' : '')."$key='$value'";
			}
			if ($sql1) {
				$db->update("UPDATE pw_user SET $sql1 WHERE uid='$rt[uid]'");
				$db->update("UPDATE pw_userinfo SET bbsid='$userdb[username]',bbsuid='$userdb[uid]' WHERE uid='$rt[uid]'");
			}
			$winduid = $rt['uid'];
		} else {
			$sql2 = '';
			foreach ($userdb as $key => $value) {
				if (in_array($key,$user_field) || (in_array($key,$credit_field) && strpos(",$db_pptcredit,",",$key,")!==false)) {
					$sql1 .= ($sql1 ? ',' : '').$key;
					$sql2 .= ($sql2 ? ',' : '')."'$value'";
				}
			}
			include(R_P.'template/default/wind/lang_reg.php');
			!$db_defaultustyle && $db_defaultustyle = 'default';
			$db->update("REPLACE INTO pw_user($sql1,groupid,memberid,gender,regdate,signchange) VALUES($sql2,'-1','8','0','$timestamp','1')");
			$winduid = $db->insert_id();
			
			$db->update("REPLACE INTO pw_userinfo(uid,style,bbsid,bbsuid,wshownum,headerdb,leftdb) VALUES ('$winduid','default','$userdb[username]','$userdb[uid]','200','$headerdb','$leftdb')");
			//$db->update("UPDATE pw_bloginfo SET newmember='$userdb[username]',totalmember=totalmember+1 WHERE id='1'");
			update_bloginfo_cache('users',$userdb[username],$winduid);
		}
		$db_hash = $bak_hash;
		$windpwd = confuse($userdb['password']);
		Cookie("bloguser",StrCode($winduid."\t".$windpwd),$userdb['cktime']);
		Cookie('lastvisit','',0);
		Cookie("ckinfo",$db_ckpath."\t".$db_ckdomain);
		Loginipwrite($winduid);
	}
	if ($userdb['url']) {
		$clienturl	= explode(',',$userdb['url']);
		$jumpurl	= array_shift($clienturl);
		$userdb['url'] = implode(',',$clienturl);
	}
	if ($jumpurl) {
		foreach ($userdb as $key => $value) {
			$userdb_encode .= ($userdb_encode ? '&' : '')."$key=$value";
		}
		unset($userdb);
		$db_hash = $db_pptkey;
		$userdb_encode = str_replace('=','',StrCode($userdb_encode));
		$verify = md5("login$userdb_encode$forward$db_pptkey");
		ObHeader("$jumpurl/passport_client.php?action=login&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
	} else {
		ObHeader($forward ? $forward : $db_pptserverurl);
	}
} elseif ($action=='quit') {
	$db_hash = $bak_hash;
	Loginout();
	if ($userdb['url']) {
		$clienturl  = explode(',',$userdb['url']);
		$jumpurl	= array_shift($clienturl);
		$userdb['url'] = implode(',',$clienturl);
	}
	if ($jumpurl) {
		foreach ($userdb as $key => $value) {
			$userdb_encode .= ($userdb_encode ? '&' : '')."$key=$value";
		}
		unset($userdb);
		$db_hash = $db_pptkey;
		$userdb_encode = str_replace('=','',StrCode($userdb_encode));
		$verify = md5("quit$userdb_encode$forward$db_pptkey");
		ObHeader("$jumpurl/passport_client.php?action=quit&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
	} else {
		strpos($forward,'user_index.php') && $forward = $db_blogurl;
		ObHeader($forward ? $forward : $db_pptserverurl);
	}
} else {
	Showmsg('undefined_action');
}

function confuse($pwd){
	global $db_hash;
	$pwd=md5($_SERVER["HTTP_USER_AGENT"].$pwd.$db_hash);
	return $pwd;
}
function Loginipwrite($winduid){
	global $db,$timestamp,$onlineip;
	$logininfo = "$onlineip|$timestamp|6";
	$db->update("UPDATE pw_user SET lastvisit=thisvisit,thisvisit='$timestamp',onlineip='$logininfo' WHERE uid='$winduid'");
}
function Loginout(){
	global $db,$timestamp,$db_onlinetime,$groupid,$winduid,$db_ckpath,$db_ckdomain;
	$thisvisit = $timestamp-$db_onlinetime*1.5;
	$db->update("UPDATE pw_user SET thisvisit='$thisvisit' WHERE uid='$winduid'");
	list($db_ckpath,$db_ckdomain) = explode("\t",GetCookie('ckinfo'));
	Cookie('bloguser','',0);
	Cookie('lastvisit','',0);
	Cookie('ckinfo','',0);
}

function update_bloginfo_cache($type,$username=null,$uid=null){
	global $db,$tdtime;
	if($type == 'blogs'){
		$tdtcontrol = $db->get_value("SELECT tdtcontrol FROM pw_bloginfo WHERE id='1'");
		if($tdtcontrol != $tdtime){
			$tdtcontrol = $tdtime;
			$tdblogs = 0;
			$db->update("UPDATE pw_bloginfo SET tdblogs='0',tdtcontrol='$tdtime'");
		}
		$totalblogs = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE ifcheck='1'");
		$tdblogs = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE postdate>'$tdtime' AND ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalblogs='$totalblogs',tdblogs='$tdblogs' WHERE id='1'");
	}elseif($type == 'albums'){
		$totalalbums = $db->get_value("SELECT COUNT(*) FROM pw_albums WHERE ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalalbums='$totalalbums' WHERE id='1'");
	}elseif($type == 'malbums'){
		$totalmalbums = $db->get_value("SELECT COUNT(*) FROM pw_malbums WHERE ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalmalbums='$totalmalbums' WHERE id='1'");
	}elseif($type == 'users'){
		$totalmember = $db->get_value("SELECT COUNT(*) FROM pw_user");
		$newmember = $uid.','.$username;
		$db->update("UPDATE pw_bloginfo SET newmember='$newmember',totalmember=$totalmember WHERE id='1'");
	}
	$bloginfodb = "<?php\r\n";
	$bloginfo = $db->get_one("SELECT newmember,totalmember,totalblogs,totalalbums,totalmalbums,tdblogs FROM pw_bloginfo WHERE id='1'");
	foreach($bloginfo as $key => $value){
		$bloginfodb .= "\$$key='$value';\r\n";
	}
	$bloginfodb .= "?>";
	writeover(D_P.'data/cache/bloginfo.php',$bloginfodb);
}
?>