<?php
!function_exists('adminmsg') && exit('Forbidden');
include_once(D_P.'data/cache/dbreg.php');
include_once(D_P.'data/cache/forum_cache_user.php');
!$job && $job = 'add';
$basename .= "&job=$job";
if ($job != 'edit') {
	if ($_POST['step']!='2') {
		$sysgpslt  = $categpslt = '';
		$_gpsltall = $_gsystem+$_gspecial;
		foreach ($_gpsltall as $key => $value) {
			$sysgpslt .= "<option value=\"$key\">$value[title]</option>";
		}
		foreach ($_USER as $key => $value) {
			$add = '';
			for ($i=0;$i<$value['type'];$i++) {
				$add .= '>';
			}
			$categpslt .= "<option value=\"$value[cid]\">$add $value[name]</option>";
		}
		include PrintEot('userort');footer();
	} else {
		InitGP(array('username','userpwd','email','domainname','blogtitle','usergid','usercid'),'P');
		//$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
		//foreach ($S_key as $value) {
			//strpos($username,$value)!==false && adminmsg('illegal_username');
			//strpos($userpwd,$value)!==false && adminmsg('illegal_password');
			//strpos($domainname,$value)!==false && adminmsg('llegal_domain');
			//strpos($blogtitle,$value)!==false && adminmsg('illegal_blogtitle');
		//}
		//name
		list($rg_minlen,$rg_maxlen) = explode("\t",$rg_reglen);
		(strlen($username) < $rg_minlen || strlen($username) > $rg_maxlen) && adminmsg('illegal_userlenght');
		$rg_banname = explode(',',$rg_banname);
		foreach ($rg_banname as $value) {
			strpos($username,$value)!==false && adminmsg('illegal_userwords');
		}
		$db->get_value("SELECT uid FROM pw_user WHERE username='$username'") && adminmsg('username_same');
		//pwd
		strlen($userpwd) < 6 && adminmsg('illegal_pwdlenght');
		$userpwd = md5($userpwd);
		//email
		(!preg_match('/^[-a-zA-Z0-9_\.]{3,}+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$email)) && adminmsg('illegal_email');
		//domain
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		(!CheckRegNeed('domainname') || ($domainname && !preg_match("/^[a-zA-Z0-9]{{$rg_domainmin},{$rg_domainmax}}$/",$domainname))) && adminmsg('illegal_domainlenght');
		$domainhold = $db_domainhold ? explode(' ',$db_domainhold) : array();
		$domainhold = array_merge((array)$domainhold,array('www','blog','bbs'));
		(in_array($domainname,$domainhold) || $db->get_value("SELECT domainname FROM pw_userinfo WHERE domainname='$domainname'")) && adminmsg('domain_same');
		//blogtitle
		!CheckRegNeed('blogtitle') && adminmsg('blogtitle_empty');
		!$blogtitle && $blogtitle = $username;
		//groupid
		!If_manager && $usergid == '3' && adminmsg('manager_right');
		//cid
		$usercid = (int)$usercid;
		!CheckRegNeed('cid','usercid') && adminmsg('cate_empty');
		//memberid
		$usermid = key($_gmember);
		//update sql
		require_once(GetLang('cpreg'));
		list($rg_rvrc,$rg_money) = explode("\t",$rg_regcredit);
		$db->update("INSERT INTO pw_user(username,password,blogtitle,email,publicmail,groupid,memberid,gender,regdate,rvrc,money,lastvisit,thisvisit,verify) VALUES ('$username','$userpwd','$blogtitle','$email','1','$usergid','$usermid','0','$timestamp','$rg_rvrc','$rg_money','$timestamp','$timestamp','1')");
		$uid = $db->insert_id();
		$db->update("INSERT INTO pw_userinfo(uid,cid,style,domainname,wshownum,headerdb,leftdb) VALUES ('$uid','$usercid','$db_defaultustyle','$domainname','200','$headerdb','$leftdb')");
		//$db->update("UPDATE pw_bloginfo SET newmember='$username',totalmember=totalmember+1 WHERE id='1'");
		update_bloginfo_cache('users',$username,$uid);
		$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$usercid'");
		adminmsg('operate_success');
	}
} else {
	$uid = GetGP('uid');
	$userdb = $db->get_one("SELECT u.username,u.password,u.blogtitle,u.email,u.publicmail,u.groupid,u.gender,u.regdate,u.qq,u.msn,u.yahoo,u.site,u.province,u.city,u.blogs,u.comments,u.msgs,u.views,u.rvrc,u.money,u.credit,u.commend,u.bday,u.verify as emailjh,u.timedf,u.onlineip as userip,u.friendview,ui.cid,ui.style,ui.bbsid,ui.domainname,ui.signature,ui.introduce FROM pw_user u LEFT JOIN pw_userinfo ui USING(uid) WHERE u.uid='$uid'");
	if ($_POST['step']!='2') {
		$publicmail = $sysgpslt = $categpslt = $styleslt = '';
		$cmddb = $viewdb = $sexdb = $yeardb = $monthdb = $daydb = array();
		$userdb['publicmail'] == 1 && $publicmail = 'CHECKED';
		$userdb['blogtitle'] == $userdb['username'] && $userdb['blogtitle'] = '';
		$_gpsltall = $_gsystem+$_gspecial;
		foreach ($_gpsltall as $key => $value) {
			$gidslt = $key==$userdb['groupid'] ? 'SELECTED' : '';
			$sysgpslt .= "<option value=\"$key\" $gidslt>$value[title]</option>";
		}
		foreach ($_USER as $key => $value) {
			$add = '';
			for ($i=0;$i<$value['type'];$i++) {
				$add .= '>';
			}
			$cidslt = $value['cid']==$userdb['cid'] ? 'SELECTED' : '';
			$categpslt .= "<option value=\"$value[cid]\" $cidslt>$add $value[name]</option>";
		}
		$userdb['regdate'] = get_date($userdb['regdate'],'Y-m-d');
		list($userdb['style'],$userdb['ustyle']) = explode('|',$userdb['style']);
		!$userdb['style'] && $userdb['style'] = $db_defaultustyle;
		$fp = opendir(R_P.'theme');
		while ($theme = readdir($fp)) {
			if (strpos($theme,'.')===false && $theme!='..') {
				list($stylename) = explode("\n",str_replace("\r",'',readover(R_P."theme/$theme/info.txt")));
				$stylename = str_replace('name:','',$stylename);
				!$stylename && $stylename = $theme;
				$skinslt = $theme==$userdb['style'] ? 'SELECTED' : '';
				$styleslt .= "<option value=\"$theme\" $skinslt>$stylename</option>";
			}
		}
		closedir($fp);
		$cmddb[$userdb['commend']] = $viewdb[$userdb['friendview']] = $sexdb[$userdb['gender']] = 'CHECKED';
		$userdb['rvrc'] = floor($userdb['rvrc']/10);
		$userdb['qq']=='0' && $userdb['qq'] = '';
		$birthdb = explode('-',$userdb['bday']);
		$yeardb[(int)$birthdb[0]] = $monthdb[(int)$birthdb[1]] = $daydb[(int)$birthdb[2]] = 'SELECTED';
		$userdb['timedf'] < 0 ? ${'zone_0'.str_replace('.','_',abs($userdb['timedf']))} = 'SELECTED' : ${'zone_'.str_replace('.','_',$userdb['timedf'])} = 'SELECTED';
		include PrintEot('userort');footer();
	} else {
		InitGP(array('ustyle','username','bbsid','password','ckpassword','email','publicmail','emailjh','blogtitle','domainname','groupid','cid','commend','gender','regdate','style','friendview','province','city','userip','money','rvrc','credit','blogs','comments','msgs','views','qq','msn','yahoo','site','year','month','day','timedf','introduce','signature'),'P');
		$basename .= "&uid=$uid";
		//$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
		//foreach ($S_key as $value) {
			//strpos($username,$value)!==false && adminmsg('illegal_username');
			//strpos($password,$value)!==false && adminmsg('illegal_password');
			//strpos($domainname,$value)!==false && adminmsg('llegal_domain');
			//strpos($blogtitle,$value)!==false && adminmsg('illegal_blogtitle');
		//}
		//name
		list($rg_minlen,$rg_maxlen) = explode("\t",$rg_reglen);
		(strlen($username) < $rg_minlen || strlen($username) > $rg_maxlen) && adminmsg('illegal_userlenght');
		if ($userdb['username'] != $username) {
			$rg_banname = explode(',',$rg_banname);
			foreach ($rg_banname as $value) {
				strpos($username,$value)!==false && adminmsg('illegal_userwords');
			}
		}
		$count = $db->get_value("SELECT COUNT(*) FROM pw_user WHERE username='$username'");
		$count>1 && adminmsg('username_same');
		//bbs
		if ($bbsid) {
			$bbsid!=$username && adminmsg('illegal_bbsid');
			$bbsid = ",bbsid='$bbsid'";
		}
		//pwd
		if ($password) {
			$bbsid && adminmsg('illegal_bbspwd');
			strlen($password) < 6 && adminmsg('illegal_pwdlenght');
			$password!=$ckpassword && adminmsg('illegal_ckpassword');
			$password = ",password='".md5($password)."'";
		}
		//email
		!preg_match('/^[-a-zA-Z0-9_\.]{3,}+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$email) && adminmsg('illegal_email');
		//blogtitle
		!$blogtitle && $blogtitle = $username;
		//domainname
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		$domainname && !preg_match("/^[-a-zA-Z0-9]{{$rg_domainmin},{$rg_domainmax}}$/",$domainname) && adminmsg('illegal_domainlenght');
		$domainhold = $db_domainhold ? explode(' ',$db_domainhold) : array();
		$domainhold = array_merge((array)$domainhold,array('www','blog','bbs'));
		$count = $domainname ? $db->get_value("SELECT COUNT(*) FROM pw_userinfo WHERE domainname='$domainname'") : 0;
		$false = $count>1 ? 1 : 0;
		(in_array($domainname,$domainhold) || $false) && adminmsg('domain_same');
		//groupid
		!If_manager && $groupid == '3' && adminmsg('manager_right');
		//cid
		$cid = (int)$cid;
		//commend
		$commend = (int)$commend;
		//gender
		$gender!=1 && $gender!=2 && $gender = 0;
		//regdate
		$regdate = PwStrtoTime($regdate);
		//style
		!$style && $style = $db_defaultustyle;
		$ustyle && $style = $style.'|'.$ustyle;
		//friendview
		$friendview!=1 && $friendview!=2 && $friendview = 0;
		//rvrc
		$rvrc *= 10;
		//qq
		$qq = (int)$qq;
		$msn && !preg_match('/^[-a-zA-Z0-9_\.]{3,}+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$msn) && $msn = '';
		$yahoo && (!preg_match('/^[-a-zA-Z0-9_\.]{3,}+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$yahoo)) && $yahoo = '';
		$site && !preg_match('/^http([s]?):\/\//i',$site) && $site = 'http://'.$site;
		$bday = (!$year || !$month || !$day) ? '0000-00-00' : $year.'-'.$month.'-'.$day;
		$introduce = substrs($introduce,234,'');
		$signature = substrs($signature,234,'');
		$memberid = getmemberid($blogs);
		$db->update("UPDATE pw_user SET username='$username'{$password},blogtitle='$blogtitle',email='$email',publicmail='$publicmail',groupid='$groupid',memberid='$memberid',gender='$gender',regdate='$regdate',qq='$qq',msn='$msn',yahoo='$yahoo',site='$site',province='$province',city='$city',blogs='$blogs',comments='$comments',msgs='$msgs',views='$views',rvrc='$rvrc',money='$money',credit='$credit',commend='$commend',bday='$bday',verify='$emailjh',timedf='$timedf',onlineip='$userip',friendview='$friendview' WHERE uid='$uid'");
		$db->update("UPDATE pw_userinfo SET cid='$cid',style='$style'{$bbsid},domainname='$domainname',signature='$signature',introduce='$introduce' WHERE uid='$uid'");
		if ($cid != $userdb['cid']) {
			$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$cid'");
			$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$userdb[cid]'");
		}
		adminmsg('operate_success');
	}
}
function CheckRegNeed($ckvalue,$glbvalue = null){
	global $rg_needdb;
	$regneeddb = !empty($rg_needdb) ? explode("\t",$rg_needdb) : array();
	if (!in_array($ckvalue,$regneeddb)) {
		return true;
	} else {
		empty($glbvalue) && $glbvalue = $ckvalue;
		if (!empty($GLOBALS[$glbvalue])) {
			return true;
		} else {
			return false;
		}
	}
}
function PwStrtoTime($date){
	global $db_timedf;
	return function_exists('date_default_timezone_set') ? strtotime($date) - $db_timedf*3600 : strtotime($date);
}
function getmemberid($nums){
	global $_gmember;
	$gid = 0;
	foreach ($_gmember as $key => $value) {
		(int)$nums>=$value['creditneed'] && $gid = $key;
	}
	return $gid;
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