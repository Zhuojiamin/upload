<?php
!function_exists('usermsg') && exit('Forbidden');
$userdb =& $admindb;
$sex = $view = $ckarray = $ckyear = $ckmonth = $ckday = $cmtcheck = $gbcheck = $gbook = array();

!$db_defaultustyle && $db_defaultustyle = 'default';
$db_cbbbsopen && require_once(R_P.'mod/passport.php');
if ($_POST['step']!=2) {
	include_once(D_P.'data/cache/forum_cache_user.php');
	$upuserinfo = $facedisabled = $styleslt = '';
	if ($db_cbbbsopen) {
		if ($userdb['bbsid'] != $ckarray['username']) {
			$upuserinfo = "bbsid='',bbsuid=''";
			$userdb['bbsid'] = '';
		}
		$pwdsyn  = $userdb['password'] != $ckarray['password'] ? 0 : 1;
		$facesyn = $userdb['icon'] != $ckarray['icon'] ? 0 : 1;
	}
	if ($userdb['domainname']) {
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		$domainhold = array_merge(explode(' ',$db_domainhold),array('www','blog','bbs'));
		if (in_array($userdb['domainname'],$domainhold) || !preg_match("/^[-a-zA-Z0-9]{{$rg_domainmin},{$rg_domainmax}}$/",$userdb['domainname'])) {
			$upuserinfo .= !empty($upuserinfo) ? ",domainname=''" : "domainname=''" ;
			$userdb['domainname'] = '';
		}
	}
	$upuserinfo && $db->update("UPDATE pw_userinfo SET $upuserinfo WHERE uid='$admin_uid'");
	$categpslt = '';
	foreach ($_USER as $key => $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$cidslt = $value['cid']==$userdb['cid'] ? 'SELECTED' : '';
		$categpslt .= "<option value=\"$value[cid]\" $cidslt>$add $value[name]</option>";
	}
	$userdb['icon'] = $admin_icon;
	if (!preg_match('/^http(s)?:\/\//i',$userdb['icon'])) {
		$uploadface = 'upload';
		$httpface = '';
	} else {
		$uploadface = 'http';
		$httpface = $userdb['icon'];
	}
	if (!$db_allowupload || !$_GROUP['allowupface']) {
		$uploadface = 'http';
		$facedisabled = 'disabled';
	}
	if ($uploadface == 'http') {
		$httpstyle = '';
		$uploadstyle = 'none';
	} else {
		$httpstyle = 'none';
		$uploadstyle = '';
	}
	list(,,,,$cmtgd,$gbgd) = explode("\t",$db_gdcheck);
	list($userdb['cmtcheck'],$userdb['gbcheck']) = explode(',',$userdb['gdcheck']);
	list(,,,$gbq,$cmtq) = explode("\t",$db_qcheck);
	list($userdb['gbqcheck'],$userdb['cmtqcheck']) = explode(',',$userdb['qcheck']);
	$postnum = explode(',',$userdb['postnum']);
	$plimitnum = explode(',',$userdb['plimitnum']);
	$gbook[$userdb['ifgbook']] = $view[$userdb['friendview']] = $sex[$userdb['gender']] = $cmtcheck[(int)$userdb['cmtcheck']] = $gbcheck[(int)$userdb['gbcheck']] = $gbqcheck[(int)$userdb['gbqcheck']] = $cmtqcheck[(int)$userdb['cmtqcheck']] = 'CHECKED';
	$birth = explode('-',$userdb['bday']);
	$ckyear[(int)$birth[0]] = $ckmonth[(int)$birth[1]] = $ckday[(int)$birth[2]] = "SELECTED";
	/*
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
	*/
	$userdb['timedf'] < 0 ? ${'zone_0'.str_replace('.','_',abs($userdb['timedf']))} = 'SELECTED' : ${'zone_'.str_replace('.','_',$userdb['timedf'])} = 'SELECTED';
	$userdb['qq']=='0' && $userdb['qq'] = '';
	list($iconw,$iconh) = explode('|',$userdb['iconsize']);
	list($_GROUP['upfacew'],$_GROUP['upfaceh']) = explode(',',$_GROUP['upfacewh']);
	list(,$db_facesizew,$db_facesizeh) = explode("\t",$db_facesize);
	$_GROUP['upfacew']  = empty($_GROUP['upfacew']) ? $db_facesizew : $_GROUP['upfacew'];
	$_GROUP['upfaceh']  = empty($_GROUP['upfaceh']) ? $db_facesizeh : $_GROUP['upfaceh'];
	require_once PrintEot('userinfo');unset($userdb);footer();
} else {
	include_once(D_P.'data/cache/dbreg.php');
	$rg_needdb = str_replace(array('city','bday'),array("province\tcity","year\tmonth\tday"),$rg_needdb);
	$rg_unneeddb = str_replace(array('city','bday'),array("province\tcity","year\tmonth\tday"),$rg_unneeddb);
	$needdb = $rg_needdb ? explode("\t",$rg_needdb) : array();
	//当系统未开启二级域名功能或者是用户组静止使用二级域名的时候，去除注册必选项domainname
	if($db_userdomain == '0' || $_GROUP['ifdomain'] == '0'){
		$needdb = array_diff($needdb,array('domainname'));
	}
	if (!$rg_showdetail) {
		$unneeddb = array();
	} else {
		$unneeddb = $rg_unneeddb ? explode("\t",$rg_unneeddb) : array();
	}
	$allgp = array('bbsid','domainname','blogtitle','friendview','ifgbook','cmtgd','gbgd','cmtq','gbq','postnum','plimitnum','oldpwd','pwdsyn','password','ckpassword','email','province','city','cid','gender','userface','iconw','iconh','delicon','facesyn','ckulface','attachment_1','qq','yahoo','msn','site','year','month','day','ustyle','timedf','introduce','signature');
	InitGP($allgp,'P');
	foreach ($allgp as $value) {
		$ckvalue = $value;
		$c_value = ${$value};
		//strpos("\t$rg_needdb\t","\t$value\t")!==false && $value!='password' && !$c_value && usermsg('must_empty');
		in_array($value,$needdb) && $value!='password' && !$c_value && usermsg('must_empty');
		$_BANDB = $value != 'signature' && $value != 'introduce' && $value != 'site' && $value != 'attachment_1' ? array("\\",'&',"'",'"','/','*',',','<','>',"\r","\t","\n",'#') : array();
		if ($c_value) {
			!isset($_FORBIDDB) && @include(D_P.'data/cache/wordfb.php');
			$_FORBIDDB = array_merge($_REPLACE,$_FORBID,$_BANDB);
			foreach ($_FORBIDDB as $banword) {
				is_array($banword) && $banword = $banword['word'];
				if (is_array($c_value)) {
					$vs = '';
					foreach ($c_value as $v) {
						$vs .= $v;
					}
					$c_value = $vs;
				}
				N_stripos($c_value,$banword)!==false && usermsg('post_wordsfb');
			}
		}
	}
	unset($allgp);
	//pwd icon
	if ($oldpwd) {
		!$pwdsyn && (!$password || md5($oldpwd)!=$userdb['password'] || strlen($password) < 6 || $password!=$ckpassword) && usermsg('pwd_fail');
		$admin_name==$manager && usermsg('pro_manager');
		$password = md5($password);
	}
	//icon
	if (!$facesyn) {
		if ($ckulface != 'upload') {
			$attachment_1 && !preg_match('/^http(s)?:\/\//i',$attachment_1) && usermsg('face_fail');
			$icon = $attachment_1;
		} else {
			if (!$delicon) {
				$icon = $userdb['icon'];
				if (!empty($_FILES['attachment_2']['tmp_name'])) {
					require_once(R_P.'mod/upload_mod.php');
					$attachdir = "$imgdir/upload";
					$icon && !preg_match('/^http(s)?:\/\//i',$icon) && P_unlink("$attachdir/$icon");
					list($_GROUP['upfacew'],$_GROUP['upfaceh']) = explode(',',$_GROUP['upfacewh']);
					list(,$db_facesizew,$db_facesizeh) = explode("\t",$db_facesize);
					$_GROUP['upfacew']  = empty($_GROUP['upfacew']) ? $db_facesizew : $_GROUP['upfacew'];
					$_GROUP['upfaceh']  = empty($_GROUP['upfaceh']) ? $db_facesizeh : $_GROUP['upfaceh'];
					$_GROUP['attachsize'] && $db_uploadmaxsize = $_GROUP['attachsize'];
					$_GROUP['uploadnum'] && $db_attachnum = $_GROUP['uploadnum'];
					$db_uploadfiletype = 'gif jpg jepg png';
					$uploaddb = UploadFile($admin_uid,2);
					$icon = $uploaddb[0]['attachurl'];
					$icondb = GetImgSize("$attachdir/$icon");
					if ($icondb['width'] > $_GROUP['upfacew'] || $icondb['height'] > $_GROUP['upfaceh']) {
						P_unlink("$attachdir/$icon");
						if ($uploaddb[0]['ifthumb'] == 1) {
							$ext  = substr(strrchr($icon,'.'),1);
							$name = substr($icon,0,strrpos($icon,'.'));
							P_unlink("$attachdir/{$name}_thumb.{$ext}");
						}
						usermsg('pro_size_limit');
					}
				}
			} else {
				P_unlink("$imgdir/upload/$userdb[icon]");
				$icon = '';
			}
		}
	}
	$iconw = (int)$iconw;
	$iconh = (int)$iconh;
	list($_GROUP['upfacew'],$_GROUP['upfaceh']) = explode(',',$_GROUP['upfacewh']);
	if($iconw > $_GROUP['upfacew'] || $iconh > $_GROUP['upfaceh']){
		usermsg('icon_size_limit');
	}
	$iconsize = $iconw.'|'.$iconh;
	
	//passport
	if ($pwdsyn == 1) {
		if ($db_cbbbsopen=='1' && $bbsid==$admin_name) {
			$password = $ckarray['password'];
		} else {
			usermsg('passportfail');
		}
	}
	!$password && $password = $userdb['password'];
	if ($facesyn == 1) {
		if ($db_cbbbsopen=='1' && $bbsid==$admin_name) {
			$icon = $ckarray['icon'];
		} else {
			usermsg('passportfail');
		}
	}
	$bbsuid = $db_pptifopen=='1' && $bbsid==$admin_name ? $ckarray['uid'] : '';
	//pwd icon
	($userdb['email'] != $email) && empty($email) && usermsg('email_empty');
	$ckemail = $db->get_value("SELECT uid FROM pw_user WHERE email='$email'");
	(strpos("\t$lg_logindb\t","\temail\t")!==false && $ckemail && $ckemail!=$admin_uid) && usermsg('email_same');
	$emy = array('email','msn','yahoo');
	foreach ($emy as $value) {
		${$value} && !preg_match('/^[-a-zA-Z0-9_\.]{3,}+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',${$value}) && usermsg('email_error');
	}
	if ($domainname) {
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		!preg_match("/^[-a-zA-Z0-9]{{$rg_domainmin},{$rg_domainmax}}$/",$domainname) && usermsg('domain_limit');
		$domainhold = array_merge(explode(' ',$db_domainhold),array('www','blog','bbs'));
		$domain = $db->get_value("SELECT uid FROM pw_userinfo WHERE domainname='$domainname'");
		(in_array($domainname,$domainhold) || $domain && $domain!=$admin_uid) && usermsg('domain_same');
	}
	$qq = (int)$qq;
	$cid = (int)$cid;
	$gender!=1 && $gender!=2 && $gender = 0;
	$friendview!=1 && $friendview!=2 && $friendview = '0';
	$ifgbook!=1 && $ifgbook = '0';
	$gdcheck = (int)$cmtgd.','.(int)$gbgd;
	$qcheck  = (int)$gbq.','.(int)$cmtq;
	$postnum = implode(',',(array)$postnum);
	$plimitnum = implode(',',(array)$plimitnum);
	$site && !preg_match('/^http([s]?):\/\//i',$site) && $site = 'http://'.$site;
	$bday = (int)$year.'-'.(int)$month.'-'.(int)$day;
	//!$style && $style = $db_defaultustyle;
	//$ustyle && $style = $style.'|'.$ustyle;
	$db->update("UPDATE pw_user SET password='$password',blogtitle='$blogtitle',email='$email',icon='$icon',iconsize='$iconsize',gender='$gender',qq='$qq',msn='$msn',yahoo='$yahoo',site='$site',province='$province',city='$city',bday='$bday',timedf='$timedf',friendview='$friendview' WHERE uid='$admin_uid'");
	$db->update("UPDATE pw_userinfo SET cid='$cid',bbsid='$bbsid',bbsuid='$bbsuid',domainname='$domainname',signature='$signature',introduce='$introduce',gdcheck='$gdcheck',qcheck='$qcheck',postnum='$postnum',plimitnum='$plimitnum',ifgbook='$ifgbook' WHERE uid='$admin_uid'");
	$db->update("UPDATE pw_gbook SET authoricon='$icon' WHERE authorid='$admin_uid'");
	if ($cid != $userdb['cid']) {
		$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$cid'");
		$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$userdb[cid]'");
	}
	usermsg('operate_success',$basename);
}
?>