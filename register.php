<?php
require_once('global.php');
$lxheader = 'reg';
include_once(D_P.'data/cache/dbreg.php');
list(,$reggd) = explode("\t",$db_gdcheck);
list($regq) = explode("\t",$db_qcheck);

InitGP(array('forward'));

!$rg_allowregister && Showmsg('reg_close');
if (!$db_pptifopen) {
	$groupid!='2' && Showmsg('reg_repeat');
} else {
	$db_ppttype=='client' && Showmsg('passport_register');
}
if ($rg_allowsameip && file_exists(D_P.'data/cache/ip_cache.php')) {
	$ipdata = readover(D_P.'data/cache/ip_cache.php');
	strpos(readover(D_P.'data/cache/ip_cache.php'),'<'.$onlineip.'>')!==false && Showmsg('reg_limit');
}
!$rg_showpermit && $_POST['step']=1;
$step = GetGP('step','P');
if ($step != 2) {
	!$forward && $forward = $db_blogurl;
	require_once(R_P.'mod/header_inc.php');
	if ($step == 1) {
		$gostep = 2;
		if ($reggd) {
			$rawwindid = (!$windid) ? 'guest' : rawurlencode($windid);
			$ckurl = str_replace('?','',$ckurl);
		} else {
			$rawwindid = $ckurl = '';
		}
		$allregdb = array();
		$needdb = $rg_needdb ? explode("\t",$rg_needdb) : array();
		if (!$rg_showdetail) {
			$unneeddb = array();
		} else {
			$unneeddb = $rg_unneeddb ? explode("\t",$rg_unneeddb) : array();
		}
		$allreg = array_merge($needdb,$unneeddb);
		foreach ($allreg as $value) {
			$onblur = in_array($value,$needdb) ? ' onblur="CheckReg(this);"' : '';
			if ($value == 'style') {
				!$db_defaultustyle && $db_defaultustyle = 'default';
				$styleslt = "<select name=\"$value\"$onblur>";
				$fp = opendir(R_P.'theme');
				while ($theme = readdir($fp)) {
					if (strpos($theme,'.')===false && $theme!='..') {
						list($stylename) = explode("\n",str_replace("\r",'',readover(R_P."theme/$theme/info.txt")));
						$stylename = str_replace('name:','',$stylename);
						!$stylename && $stylename = $theme;
						$skinslt = $theme==$db_defaultustyle ? 'SELECTED' : '';
						$styleslt .= "<option value=\"$theme\" $skinslt>$stylename</option>";
					}
				}
				closedir($fp);
				$styleslt .= '</select>';
			} elseif ($value == 'gender') {
				$styleslt = '';
				$checked = ' CHECKED';
				if (strpos("\t$rg_needdb\t","\tgender\t")===false) {
					$checked = '';
					$styleslt .= "<input type=\"radio\" name=\"$value\" value=\"0\" CHECKED> 保密";
				}
				$styleslt .= "<input type=\"radio\" name=\"$value\" value=\"1\"$checked> 男 <input type=\"radio\" name=\"$value\" id=\"$value\" value=\"2\"> 女";
			} elseif ($value == 'signature' || $value == 'introduce') {
				$styleslt = "<textarea class=\"ip\" name=\"$value\" rows=\"4\" cols=\"40\"$onblur></textarea>";
			} elseif ($value == 'city') {
				$onblur && $onblur = " onblur=\"CheckReg('city');\"";
				$styleslt = "<script src=\"js/initcity.js\"></script><select onChange=\"initcity();\" id=\"province\" name=\"province\"$onblur><script>creatprovince();</script></select><select id=\"city\" name=\"city\"$onblur><script>initcity();</script></select>";
			} elseif ($value == 'bday') {
				$onblur && $onblur = " onblur=\"CheckReg('bday');\"";
				$year = get_date($timestamp,'Y')-13;
				$styleslt = "<select name=\"year\"$onblur><option value=\"0000\"></option>";
				for ($i=1970;$i<=$year;$i++) {
					$styleslt .= "<option value=\"$i\">$i</option>";
				}
				$styleslt .= "</select> $ilang[year]";
				$styleslt .= "<select name=\"month\"$onblur><option value=\"00\"></option>";
				for ($i=1;$i<=12;$i++) {
					strlen($i)<2 && $i = '0'.$i;
					$styleslt .= "<option value=\"$i\">$i</option>";
				}
				$styleslt .= "</select> $ilang[month]";
				$styleslt .= "<select name=\"day\"$onblur><option value=\"00\"></option>";
				for ($i=1;$i<=31;$i++) {
					strlen($i)<2 && $i = '0'.$i;
					$styleslt .= "<option value=\"$i\">$i</option>";
				}
				$styleslt .= "</select> $ilang[day]";
			} elseif ($value == 'cid') {
				include_once(D_P.'data/cache/forum_cache_user.php');
				$styleslt = "<select name=\"$value\" id=\"$value\"$onblur>";
				foreach ($_USER as $key => $v) {
					$add = '';
					for ($i=0;$i<$v['type'];$i++) {
						$add .= '>';
					}
					$styleslt .= "<option value=\"$v[cid]\">$add $v[name]</option>";
				}
				$styleslt .= '</select>';
			} elseif ($value == 'domainname' && $db_userdomain) {
				!$db_domain && $db_domain = substr($_SERVER['HTTP_HOST'],strpos($_SERVER['HTTP_HOST'],'.')+1);
				$styleslt = "<input name=\"$value\" value=\"\" size=\"30\" class=\"ip\"$onblur style=\"vertical-align:bottom\" />.$db_domain $ilang[regdomainname]";
			} elseif ($value == 'blogtitle') {
				$regblogtitle = strpos("\t$rg_needdb\t","\t$value\t")===false ? $ilang['regblogtitle'] : '';
				$styleslt = "<input name=\"$value\" value=\"\" size=\"30\" class=\"ip\"$onblur style=\"vertical-align:bottom\" /> $regblogtitle";
			} else {
				$styleslt = "<input name=\"$value\" value=\"\" size=\"30\" class=\"ip\"$onblur style=\"vertical-align:bottom\" />";
			}
			$allregdb[$value] = $styleslt;
		}
		
	} else {
		$gostep = 1;
		$rg_permit = str_replace(array("\r","\n"),array('','<br />'),$rg_permit);
	}
	require_once PrintEot('register');footer();
} else {
	$rg_needdb = str_replace(array('city','bday'),array("province\tcity","year\tmonth\tday"),$rg_needdb);
	$rg_unneeddb = str_replace(array('city','bday'),array("province\tcity","year\tmonth\tday"),$rg_unneeddb);
	$needdb = $rg_needdb ? explode("\t",$rg_needdb) : array();
	if (!$rg_showdetail) {
		$unneeddb = array();
	} else {
		$unneeddb = $rg_unneeddb ? explode("\t",$rg_unneeddb) : array();
	}
	$allgp = array_merge(array('username','password','ckpassword','email','publicemail','gdcode','qanswer','qkey'),$needdb,$unneeddb);
	InitGP($allgp,'P');
	foreach ($allgp as $value) {
		$ckvalue = ${$value};
		strpos("\t$rg_needdb\t","\t$value\t")!==false && !$ckvalue && Showmsg('must_empty');
		$_BANDB = $value != 'signature' && $value != 'introduce' && $value != 'site' && $value != 'blogtitle' ? array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#') : array();
		if ($ckvalue) {
			!isset($_FORBIDDB) && @include(D_P.'data/cache/wordfb.php');
			$_FORBIDDB = array_merge($_REPLACE,$_FORBID,$_BANDB);
			foreach ($_FORBIDDB as $banword) {
				is_array($banword) && $banword = $banword['word'];
				N_stripos($ckvalue,$banword)!==false && Showmsg('post_wordsfb');
			}
		}
	}
	unset($_FORBIDDB);
	//username
	list($rg_minlen,$rg_maxlen) = explode("\t",$rg_reglen);
	(!$username || strlen($username) > $rg_maxlen || strlen($username) < $rg_minlen) && Showmsg('username_limit');
	$username == 'guest' && Showmsg('illegal_value');
	if (!$rg_lower) {
		for ($asc=65;$asc<=90;$asc++) {
			strpos($username,chr($asc))!==false && Showmsg('username_lower');
		}
	}
	$rg_banname = explode(',',$rg_banname);
	foreach ($rg_banname as $banword) {
		strpos($username,$banword)!==false && Showmsg('post_wordsfb');
	}
	$db->get_value("SELECT uid FROM pw_user WHERE username='$username'") && Showmsg('username_same');

	if($username!==Sql_cv($username)){
		Showmsg('illegal_username');
	}
	//password
	(!$password || strlen($password) < 6) && Showmsg('passport_limit');
	$ckpassword != $password && Showmsg('password_confirm');
	$password = md5($password);
	//email
	!$email && Showmsg('email_empty');
	(strpos("\t$lg_logindb\t","\temail\t")!==false && $db->get_value("SELECT email FROM pw_user WHERE email='$email'")) && Showmsg('email_same');
	$emy = array('email','msn','yahoo');
	foreach ($emy as $value) {
		${$value} && !preg_match('/^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',${$value}) && Showmsg('email_error');
	}

	if(!empty($email) && $email!==Sql_cv($email)){
		Showmsg('illegal_username');
	}

	if(!empty($domainname) && $domainname!==Sql_cv($domainname)){
		Showmsg('illegal_username');
	}
	if ($reggd) 
	{
		$cknum = GetCookie('cknum');
		Cookie('cknum','',0);
		if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
			Showmsg('gdcode_error');
		}
	}
	if ($regq == '1' && !empty($db_question)){
		$answer = unserialize($db_answer);
		$qanswer != $answer[$qkey] && Showmsg('qanswer_error');
	}
	$qq = (int)$qq;
	$cid = (int)$cid;
	$gender!=1 && $gender!=2 && $gender = 0;
	$site && !preg_match('/^http([s]?):\/\//i',$site) && $site = 'http://'.$site;
	$bday = (int)$year.'-'.(int)$month.'-'.(int)$day;
	if ($domainname) {
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		!preg_match("/^[-a-zA-Z0-9]{{$rg_domainmin},{$rg_domainmax}}$/",$domainname) && Showmsg('domain_limit');
		$domainhold = array_merge(explode(' ',$db_domainhold),array('www','blog','bbs'));
		(in_array($domainname,$domainhold) || $db->get_value("SELECT domainname FROM pw_userinfo WHERE domainname='$domainname'")) && Showmsg('domain_same');
	}
	(strlen($signature)>200 || strlen($introduce)>200) && Showmsg('signature_limit');
	$groupid = ($rg_ifcheck == '1') ? '7' : '-1';
	include_once(D_P.'data/cache/level_cache.php');
	foreach ($_gmember as $value) {
		$volume[$key] = $value['creditneed'];
	}
	array_multisort($volume,SORT_ASC,$_gmember);
	foreach ($_gmember as $key => $value){
		$memberid = $key;
		break;
	}
	$verify = $rg_emailcheck==1 ? $timestamp : 1;
	list($rg_rvrc,$rg_money) = explode("\t",$rg_regcredit);
	!$db_defaultustyle && $db_defaultustyle = 'default';
	include_once GetLang('reg');
	$db->update("INSERT INTO pw_user(username,password,blogtitle,email,publicmail,groupid,memberid,iconsize,gender,regdate,qq,msn,yahoo,site,province,city,rvrc,money,lastvisit,thisvisit,bday,verify,onlineip) VALUES ('$username','$password','$blogtitle','$email','$publicmail','$groupid','$memberid','115|115','$gender','$timestamp','$qq','$msn','$yahoo','$site','$province','$city','$rg_rvrc','$rg_money','$timestamp','$timestamp','$bday','$verify','$onlineip')");
	$winduid = $db->insert_id();
	$db->update("INSERT INTO pw_userinfo(uid,cid,style,domainname,wshownum,headerdb,leftdb,signature,introduce) VALUES ('$winduid','$cid','$db_defaultustyle','$domainname','200','$headerdb','$leftdb','$signature','$introduce')");
	update_bloginfo_cache('users',$username,$winduid);
	$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$cid'");
	$windid	 = $username;
	$windpwd = $password;
	Cookie('bloguser',Strcode($winduid."\t".PwdCode($windpwd)));
	Cookie('ckinfo',$db_ckpath."\t".$db_ckdomain);
	Cookie('lastvisit','',0);
	if (GetCookie('userads') && $db_ads=='2') {
		list($u,$a) = explode("\t",GetCookie('userads'));
		(is_numeric($u) || ($a && strlen($a)<16)) && require_once(R_P.'mod/userads_inc.php');
	}
	//writeip
	if ($rg_allowsameip) {
		if (file_exists(D_P.'data/cache/ip_cache.php')) {
			writeover(D_P.'data/cache/ip_cache.php',"<$onlineip>","ab");
		} else {
			writeover(D_P.'data/cache/ip_cache.php',"<?die;?><$onlineip>");
		}
	}
	//writeip
	//passport
	if ($db_pptifopen && $db_ppttype == 'server') {
		$action = 'login';
		$cktime = 'F';
		require_once(R_P.'mod/passport_server.php');
	}
	//passport
	refreshto("blog.php?uid=$winduid",'reg_success');
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