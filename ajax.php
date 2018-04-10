<?php
require_once('global.php');
require_once(R_P.'mod/ajax_mod.php');
define('AJAX',true);

if ($action=='vote') {
	!$winduid && exit('not_login');
	(int)$votenum < 1 && exit('erro_voteid');
	$voteitem = array();
	$query = $db->query("SELECT id,voteduid FROM pw_voteitem WHERE vid='$vid'");
	while ($rt = $db->fetch_array($query)) {
		strpos(",$rt[voteduid],",",$winduid,")!==false && exit('have_voted');
		$voteitem[$rt['id']] = $rt['voteduid'];
	}
	$db->free_result($query);
	$voteid = (!empty($voteids) && $voteids != 'undefined') ? explode('|',$voteids) : array();
	foreach ($voteid as $value) {
		$value = (int)$value;
		$voteuid = ($voteitem[$value] ? "$voteitem[$value]," : '').$winduid;
		$db->update("UPDATE pw_voteitem SET num=num+1,voteduid='$voteuid' WHERE id='$value'");
	}
	echo 'lxblog';
} elseif ($action=='getcid') {
	(!$type || !in_array($type,array('blog','photo','music','bookmark','goods','file'))) && exit('undefined_action');
	include_once(D_P."data/cache/forum_cache_$type.php");
	$categpslt = '';
	$catedb = (array)${strtoupper('_'.$type)};
	foreach ($catedb as $key => $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '&nbsp;&nbsp;';
		}
		$categpslt .= "<option value=\"$value[cid]\">$add $value[name]</option>";
	}
	$ilangtype = str_replace(' ','',$ilang['c'.$type]);
	$categpslt && ShowRresponse("$ilangtype\t$categpslt",true);
	exit;
} elseif ($action=='reg') {
	include_once(D_P.'data/cache/dbreg.php');
	@include(D_P.'data/cache/wordfb.php');
	$_BANDB = $ajaxname != 'signature' && $ajaxname != 'introduce' && $ajaxname != 'site' ? array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#') : array();
	$_FORBIDDB = array_merge($_REPLACE,$_FORBID,$_BANDB);
	foreach ($_FORBIDDB as $banword) {
		is_array($banword) && $banword = $banword['word'];
		N_stripos($ajaxvalue,$banword)!==false && ShowRresponse('post_wordsfb');
	}
	unset($_FORBIDDB);
	if ($ajaxname == 'username') {
		list($rg_minlen,$rg_maxlen) = explode("\t",$rg_reglen);
		(!$ajaxvalue || strlen($ajaxvalue) > $rg_maxlen || strlen($ajaxvalue) < $rg_minlen) && ShowRresponse('username_limit');
		$ajaxvalue == 'guest' && ShowRresponse('illegal_value');
		if (!$rg_lower) {
			for ($asc=65;$asc<=90;$asc++) {
				strpos($ajaxvalue,chr($asc))!==false && ShowRresponse('username_lower');
			}
		}
		$rg_banname = explode(',',$rg_banname);
		foreach ($rg_banname as $banword) {
			strpos($ajaxvalue,$banword)!==false && ShowRresponse('post_wordsfb');
		}
		$db->get_value("SELECT uid FROM pw_user WHERE username='$ajaxvalue'") && ShowRresponse('username_same');
	} elseif ($ajaxname == 'password') {
		(!$ajaxvalue || strlen($ajaxvalue) < 6) && ShowRresponse('passport_limit');
	} elseif ($ajaxname == 'ckpassword') {
		$ajaxckvalue != $ajaxvalue && ShowRresponse('password_confirm');
	} elseif ($ajaxname == 'email' || $ajaxname == 'msn' || $ajaxname == 'yahoo') {
		!$ajaxvalue && ShowRresponse('email_empty');
		writeover(R_P.'test.txt',$ajaxvalue);
		!preg_match('/^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$ajaxvalue) && ShowRresponse('email_error');
		if ($ajaxname == 'email') {
			(strpos("\t$lg_logindb\t","\temail\t")!==false && $db->get_value("SELECT email FROM pw_user WHERE email='$ajaxvalue'")) && ShowRresponse('email_same');
		}
	} elseif ($ajaxname == 'gdcode') {
		list(,$reggd) = explode("\t",$db_gdcheck);
		if ($reggd) {
			$cknum = GetCookie('cknum');
			Cookie('cknum','',0);
			if (!$ajaxvalue || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$ajaxvalue)) {
				ShowRresponse('gdcode_error');
			}
		}
	} elseif($ajaxname == 'qanswer'){
		$answer = unserialize($db_answer);
		($ajaxvalue != $answer[$qkey]) && ShowRresponse('qanswer_error');
	} elseif ($ajaxname == 'blogtitle') {
		!$ajaxvalue && ShowRresponse('blogtitle_empty');
	} elseif ($ajaxname == 'qq') {
		$ajaxvalue = (int)$ajaxvalue;
		!$ajaxvalue && ShowRresponse('qq_empty');
	} elseif ($ajaxname == 'site') {
		!$ajaxvalue && ShowRresponse('site_empty');
	} elseif ($ajaxname == 'city') {
		list($province,$city) = explode('|',$ajaxvalue);
		(!$province || !$city) && ShowRresponse('city_empty');
	} elseif ($ajaxname == 'bday') {
		list($year,$month,$day) = explode('|',$ajaxvalue);
		$year = (int)$year;
		$month = (int)$month;
		$day = (int)$day;
		(!$year || !$month || !$day) && ShowRresponse('bday_empty');
	} elseif ($ajaxname == 'domainname') {
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		(!$ajaxvalue || !preg_match("/^[-a-zA-Z0-9]{{$rg_domainmin},{$rg_domainmax}}$/",$ajaxvalue)) && ShowRresponse('domain_limit');
		$domainhold = array_merge(explode(' ',$db_domainhold),array('www','blog','bbs'));
		(in_array($ajaxvalue,$domainhold) || $db->get_value("SELECT domainname FROM pw_userinfo WHERE domainname='$ajaxvalue'")) && ShowRresponse('domain_same');
	} elseif ($ajaxname == 'signature' || $ajaxname == 'introduce') {
		(strlen($ajaxvalue) < 1 || strlen($ajaxvalue)>200) && ShowRresponse('signature_limit');
	}
} elseif ($action=='link') {
	!$winduid && exit('not_login');
	(!$name || !$url || !$descrip) && exit('operate_fail');
	$rt = $db->get_one("SELECT linkuid,ifcheck FROM pw_share WHERE linkuid='$winduid'");
	if ($rt['linkuid']) {
		!$rt['ifcheck'] && exit('have_link');
		exit('have_limit');
	}
	$db->update("INSERT INTO pw_share (name,url,descrip,logo,ifcheck,linkuid,linktime) VALUES ('$name','$url','$descrip','$logo','0','$winduid','$timestamp')");
} elseif ($action=='friend') {
	!$winduid && exit('not_login');
	$winduid==$fuid && exit('not_myself');
	$showerror = 0;
	$friends = '';
	$query = $db->query("SELECT fuid FROM pw_blogfriend WHERE uid='$winduid'");
	while ($rt = $db->fetch_array($query)) {
		$rt['fuid'] == $fuid && $showerror = 1;
		$friends .= ($friends ? ',' : '').$rt['fuid'];
	}
	$db->free_result($query);
	$db->update("UPDATE pw_user SET friends='$friends' WHERE uid='$winduid'");
	$showerror && exit('have_friend');
	$db->update("INSERT INTO pw_blogfriend (uid,fuid,fdate) VALUES ('$winduid','$fuid','$timestamp')");
} elseif ($action=='footprint') {
	!$winduid && exit('not_login');
	if($type == 'blog'){	
		$count = $db->get_value("SELECT itemid FROM pw_items WHERE itemid = '$id'");
		!$count && exit('illegal_tid');
		$count = $db->get_value("SELECT COUNT(*) FROM pw_footprint WHERE itemid = '$id' AND type='blog' AND uid='$winduid'");
		$count>0 && exit('have_print');
		$db->update("INSERT INTO pw_footprint(itemid,type,uid,fdate) VALUES('$id','blog','$winduid','$timestamp')");
		$db->update("UPDATE pw_items SET footprints=footprints+1 WHERE itemid='$id'");
	}elseif($type == 'photo'){
		$count = $db->get_value("SELECT pid FROM pw_photo WHERE pid = '$id'");
		!$count && exit('illegal_tid');
		$count = $db->get_value("SELECT COUNT(*) FROM pw_footprint WHERE itemid = '$id' AND type='photo' AND uid='$winduid'");
		$count>0 && exit('have_print');
		$db->update("INSERT INTO pw_footprint(itemid,type,uid,fdate) VALUES('$id','photo','$winduid','$timestamp')");
		$aid = $db->get_value("SELECT aid FROM pw_photo WHERE pid='$id'");
		$db->update("UPDATE pw_albums SET footprints=footprints+1 WHERE aid='$aid'");
		$db->update("UPDATE pw_photo SET pfootprints=pfootprints+1 WHERE pid='$id'");
	}
	exit('foot_success');
} elseif ($action=='collecitems') {
	!$winduid && exit('not_login');
	if($type == 'blog'){
		$rt = $db->get_one("SELECT itemid FROM pw_items WHERE itemid = '$id' AND type='blog'");
		$itemid = $rt['itemid'];
	}elseif($type == 'photo'){
		$rt = $db->get_one("SELECT aid FROM pw_albums WHERE aid = '$id'");
		$itemid = $rt['aid'];
	}elseif($type == 'music'){
		$rt = $db->get_one("SELECT maid FROM pw_malbums WHERE maid = '$id'");
		$itemid = $rt['maid'];
	}elseif($type == 'user'){
		$rt = $db->get_one("SELECT uid FROM pw_user WHERE uid = '$id'");
		$itemid = $rt['uid'];
	}
	!$itemid && exit('illegal_tid');	
	$count = $db->get_value("SELECT COUNT(*) FROM pw_collections WHERE uid='$winduid' AND type='$type' AND itemid='$id'");
	$count>0 && exit('have_clt');
	$db->update("INSERT INTO pw_collections(itemid,uid,type,adddate) VALUES('$id','$winduid','$type','$timestamp')");
	exit('clt_success');
} elseif ($action=='collecusers') {
	!$winduid && exit('not_login');
	$uid = $db->get_value("SELECT uid FROM pw_user WHERE uid = '$id'");
	!$uid && exit('illegal_uid');
	$count = $db->get_value("SELECT COUNT(*) FROM pw_collections WHERE uid='$winduid' AND type='user' AND itemid='$id'");
	$count>0 && exit('have_clt');
	$db->update("INSERT INTO pw_collections(itemid,uid,type,adddate) VALUES('$id','$winduid','user','$timestamp')");
	exit('clt_success');
} elseif ($action=='cmditems') {
	!$winduid && exit('not_login');
	!$_GROUP['cmdact'] && exit('group_right');
	$rt = $db->get_one("SELECT itemid,digest FROM pw_items WHERE itemid = '$id'");
	!$rt['itemid'] && exit('illegal_tid');
	$rt['digest'] != $digest && $db->update("UPDATE pw_items SET digest='$digest' WHERE itemid = '$id'");
	exit("success\tcmditems");
} elseif ($action=='cmdusers') {
	!$winduid && exit('not_login');
	!$_GROUP['cmduser'] && exit('group_right');
	$rt = $db->get_one("SELECT uid,commend FROM pw_user WHERE uid = '$id'");
	!$rt['uid'] && exit('illegal_uid');
	$commend = $rt['commend'] > 0 ? 0 : 1;
	$db->update("UPDATE pw_user SET commend='$commend' WHERE uid = '$id'");
	exit("success\tcmdusers\t$commend");
} elseif ($action=='delatc'){
	!$winduid && exit('not_login');
	!$_GROUP['delatc'] && exit('group_right');
	if($type == 'blog'){
		$items = $db->get_one("SELECT itemid,cid,uid,type FROM pw_items WHERE itemid = '$id'");
		!$items['itemid'] && exit('illegal_tid');
		$cmtcount = (int)$db->get_value("SELECT COUNT(*) FROM pw_comment WHERE ifcheck='1' AND uid = '$items[uid]' AND itemid='$items[itemid]' AND type='blog'");
		$query = $db->query("SELECT tagid,tagtype,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid = '$id' AND tagtype='blog' GROUP BY tagid");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['tagid'] && $rt['tagtype']) {
				$tagnum = "{$rt[tagtype]}num";
				$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],$tagnum=$tagnum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
			}
		}
		$query = $db->query("SELECT attachurl,ifthumb FROM pw_upload WHERE itemid = '$id'");
		while ($rt = $db->fetch_array($query)) {
			P_unlink("$attachdir/$rt[attachurl]");
			if ($rt['ifthumb'] == 1) {
				$ext = strrchr($rt['attachurl'],'.');
				$thumb = str_replace($ext,"_thumb$ext",$rt['attachurl']);
				P_unlink("$attachdir/$thumb");
			}
		}
		$db->free_result($query);
		$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$items[cid]'");
		$db->update("UPDATE pw_user SET items=items-1,{$items[type]}s={$items[type]}s-1,comments=comments-$cmtcount WHERE uid='$items[uid]'");
		$db->update("DELETE FROM pw_carticle WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_collections WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_comment WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_footprint WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_items WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_{$items[type]} WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_taginfo WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_tblog WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_upload WHERE itemid = '$id'");
		updatecache_cate($items['type']);
		update_bloginfo_cache('blogs');
	}elseif($type == 'photo'){
		$photo = $db->get_one("SELECT pid,cid,uid,aid,attachurl,ifthumb FROM pw_photo WHERE pid = '$id'");
		!$photo['pid'] && exit('illegal_tid');
		$cmtcount = (int)$db->get_value("SELECT COUNT(*) FROM pw_comment WHERE ifcheck='1' AND uid='$photo[uid]' AND itemid='$id' AND type='$type'");
		$fptcount = (int)$db->get_value("SELECT pfootprints FROM pw_photo WHERE pid='$photo[pid]'");
		$query = $db->query("SELECT tagid,tagtype,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid='$id' AND tagtype='photo' GROUP BY tagid");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['tagid']) {
				$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],photonum=photonum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
			}
		}
		P_unlink("$attachdir/$photo[attachurl]");
		if ($photo['ifthumb'] == 1) {
			$ext  = substr(strrchr($photo['attachurl'],'.'),1);
			$name = substr($photo['attachurl'],0,strrpos($photo['attachurl'],'.'));
			P_unlink("$attachdir/{$name}_thumb.{$ext}");
		}
		$db->free_result($query);
		$db->update("UPDATE pw_user SET photos=photos-1,comments=comments-$cmtcount WHERE uid='$photo[uid]'");
		$db->update("UPDATE pw_albums SET photos=photos-1,footprints=footprints-$fptcount,replies=replies-$cmtcount WHERE aid='$photo[aid]'");
		$db->update("DELETE FROM pw_comment WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_footprint WHERE itemid = '$id'");
		$db->update("DELETE FROM pw_photo WHERE pid = '$id'");
		$db->update("DELETE FROM pw_taginfo WHERE itemid = '$id'");
	}elseif($type == 'music'){
		(int)$id<1 && exit;
		$maid = $id;
		(int)$musics = 0;
		$cid = $db->get_value("SELECT cid FROM pw_malbums WHERE maid='$maid'");
		$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$cid' AND type='music'");
		$query = $db->query("SELECT mid FROM pw_music WHERE maid='$maid'");
		while($rt = $db->fetch_array($query)){
			$mids .= ($mids ? ',' : '')."'$rt[mid]'";
			$musics++;
		}
		if($mids){
			$mids = strpos($mids,',')===false ? "=$mids" : " IN ($mids)";
			$query = $db->query("SELECT tagid,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid{$mids} AND tagtype='music' GROUP BY tagid");
			while ($rt = $db->fetch_array($query)) {
				if ($rt['tagid']) {
					$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],musicnum=musicnum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
				}
			}
		}
		$query = $db->query("SELECT uid FROM pw_comment WHERE itemid='$maid'");
		while ($rt = $db->fetch_array($query)) {
			$userdb[$rt['uid']]['comments']++;
		}
		empty($userdb) && $userdb = array();
		foreach($userdb as $key => $value){
			if ((int)$value['comments'] > 0) {
				$updatesql .= ($updatesql ? ',' : '')."comments=comments-'$value[comments]'";
			}
			$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
		}
		$db->update("UPDATE pw_user SET malbums=malbums-1,musics=musics-$musics WHERE uid='$admin_uid'");
		$db->update("DELETE FROM pw_comment WHERE itemid='$maid' AND type='music'");
		$db->update("DELETE FROM pw_malbums WHERE maid='$maid'");
		$db->update("DELETE FROM pw_music WHERE maid='$maid'");
		$db->update("DELETE FROM pw_taginfo WHERE itemid{$mids}");
		update_malbumdb($admin_uid);
		updatecache_cate('music');
	}
	exit('del_success');
} elseif ($action=='deluser') {
	!$winduid && exit('not_login');
	!$_GROUP['deluser'] && exit('group_right');
	$winduid == $id && exit('sameuser_right');
	$rt = $db->get_one("SELECT uid,groupid FROM pw_user WHERE uid = '$id'");
	!$rt['uid'] && exit('illegal_uid');
	$rt['groupid'] == '3' && exit('manager_right');
	$catedb = $typedb = $cmtdb = $itemdb = array();
	$itemids = '';
	$query = $db->query("SELECT itemid,cid,type FROM pw_items WHERE uid = '$id'");
	while ($rt = $db->fetch_array($query)) {
		$catedb[$rt['cid']]['num']++;
		$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
		$typedb[$rt['type']][] = $rt['itemid'];
	}
	$query = $db->query("SELECT uid,itemid,ifcheck FROM pw_comment WHERE authorid = '$id'");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ifcheck']) {
			$rt['uid'] != $id && $cmtdb[$rt['uid']]['num']++;
			strpos($itemids,"'$rt[itemid]'")===false && $itemdb[$rt['itemid']]['rls']++;
		}
	}
	$query = $db->query("SELECT itemid FROM pw_footprint WHERE uid = '$id'");
	while ($rt = $db->fetch_array($query)) {
		strpos($itemids,"'$rt[itemid]'")===false && $itemdb[$rt['itemid']]['fps']++;
	}
	$query = $db->query("SELECT attachurl,ifthumb FROM pw_upload WHERE uid = '$id'");
	while ($rt = $db->fetch_array($query)) {
		P_unlink("$attachdir/$rt[attachurl]");
		if ($rt['ifthumb'] == 1) {
			$ext = strrchr($rt['attachurl'],'.');
			$thumb = str_replace($ext,"_thumb$ext",$rt['attachurl']);
			P_unlink("$attachdir/$thumb");
		}
	}
	$db->free_result($query);
	foreach ($catedb as $key => $value) {
		$db->update("UPDATE pw_categories SET counts=counts-'$value[num]' WHERE cid='$key'");
	}
	foreach ($cmtdb as $key => $value) {
		$db->update("UPDATE pw_user SET comments=comments-'$value[num]' WHERE uid='$key'");
	}
	foreach ($itemdb as $key => $value) {
		$updatesql = $value['rls'] ? "replies=replies-'$value[rls]'" : '';
		if ($value['fps']) {
			$updatesql .= ($updatesql ? ',' : '')."footprints=footprints-'$value[fps]'";
		}
		$updatesql && $db->update("UPDATE pw_items SET $updatesql WHERE itemid='$key'");
	}
	if ($itemids) {
		foreach ($typedb as $key => $value) {
			foreach($value as $k => $v){
				$db->update("DELETE FROM pw_$key WHERE itemid = $v");
			}
		}
	}
	$db->update("DELETE FROM pw_blogfriend WHERE uid = '$id' OR fuid = '$id'");
	$db->update("DELETE FROM pw_carticle WHERE uid = '$id' OR touid = '$id'");
	$db->update("DELETE FROM pw_collections WHERE uid = '$id'");
	$db->update("DELETE FROM pw_comment WHERE uid = '$id' OR authorid = '$id'");
	$db->update("DELETE FROM pw_footprint WHERE uid = '$id'");
	$db->update("DELETE FROM pw_gbook WHERE uid = '$id' OR authorid = '$id'");
	$db->update("DELETE FROM pw_items WHERE uid = '$id'");
	$db->update("DELETE FROM pw_itemtype WHERE uid = '$id'");
	$db->update("DELETE FROM pw_lcustom WHERE authorid = '$id'");
	$db->update("DELETE FROM pw_taginfo WHERE uid = '$id'");
	$db->update("DELETE FROM pw_btags WHERE uid = '$id'");
	$db->update("DELETE FROM pw_tblog WHERE uid = '$id'");
	$db->update("DELETE FROM pw_team WHERE uid = '$id'");
	$db->update("DELETE FROM pw_tgbook WHERE uid = '$id'");
	$db->update("DELETE FROM pw_tuser WHERE uid = '$id' OR admin = '$id'");
	$db->update("DELETE FROM pw_upload WHERE uid = '$id'");
	$db->update("DELETE FROM pw_user WHERE uid = '$id'");
	$db->update("DELETE FROM pw_userhobby WHERE uid = '$id'");
	$db->update("DELETE FROM pw_userinfo WHERE uid = '$id'");
	$db->update("DELETE FROM pw_userskin WHERE uid = '$id'");
	$db->update("UPDATE pw_bloginfo SET totalmember=totalmember-1 WHERE id='1'");
	updatecache_cate('user');
	exit('del_success');
} elseif ($action=='addcomment') {
	require_once(R_P.'mod/post_mod.php');
	require_once(R_P.'mod/windcode.php');
	require_once(R_P.'mod/ipfrom_mod.php');
	@include(D_P.'data/cache/wordfb.php');
	(!$cmtuser || !$cmtcontent) && exit('cmt_empty');
	if($type == 'blog'){
		$rt = $db->get_one("SELECT i.itemid,i.cid,i.bbsfid,i.uid,i.type,i.allowreply,i.cmttext,ui.gdcheck,ui.qcheck,ui.postnum,ui.plimitnum FROM pw_items i LEFT JOIN pw_userinfo ui ON i.uid=ui.uid WHERE i.itemid='$id'");
		!$rt['itemid'] && exit('illegal_tid');
		$_GROUP['closecmt'] && $rt['allowreply'] = 0;
		!$rt['allowreply'] && exit('group_right');
		list(,,,,$cmtgd) = explode("\t",$db_gdcheck);
		if (!$cmtgd) {
			list($cmtgd) = explode(',',$rt['gdcheck']);
		}
		if ($cmtgd) {
			$cknum = GetCookie('cknum');
			Cookie('cknum','',0);
			if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
				exit('gdcode_error');
			}
		}
		
		list(,,,,$cmtq) = explode("\t",$db_qcheck);
		list(,$cmtqcheck) = explode(',',$rt['qcheck']);
		if(($cmtq=='1' || $cmtqcheck=='1') && !empty($db_question)){
			$answer = unserialize($db_answer);
			($qanswer != $answer[$qkey]) && exit('qanswer_error');
		}
		
		$cid = $rt['cid'];
		$uid = $rt['uid'];
		$type = $rt['type'];
		$cmttext = $rt['cmttext'] ? (array)unserialize($rt['cmttext']) : array();
		list(,$spostnum) = explode(',',$_GROUP['postnum']);
		list(,$slimitnum) = explode(',',$_GROUP['limitnum']);
		list($upostnum) = explode(',',$rt['postnum']);
		list($ulimitnum) = explode(',',$rt['plimitnum']);
		$spostnum = (int)$spostnum;
		$upostnum = (int)$upostnum;
		$postnum = min($spostnum,$upostnum);
		if (!$spostnum || !$upostnum) {
			$postnum = $spostnum ? $spostnum : $upostnum;
		}
		$limitnum = (int)max($slimitnum,$ulimitnum);
		if (!$slimitnum || !$ulimitnum) {
			$limitnum = $slimitnum ? $slimitnum : $ulimitnum;
		}
		if ($postnum || $limitnum) {
			$count = $postdate = 0;
			$query = $db->query("SELECT postdate FROM pw_comment WHERE userip='$onlineip' AND postdate > $tdtime ORDER BY postdate DESC");
			while ($rt2 = $db->fetch_array($query)) {
				!$postdate && $postdate = $rt2['postdate'];
				($limitnum && ($timestamp - $postdate < $limitnum)) && exit("time_limit\t$limitnum");
				($postnum && $count > $postnum) && exit("post_limit\t$postnum");
				$count++;
			}
			$db->free_result($query);
		}
		$winduid = (int)$winduid;
		$cmtcontent = AutoUrl($cmtcontent);
		$cmtcontent = Atc_cv($cmtcontent,0);
		$ifconvert = ($cmtcontent==convert($cmtcontent,$db_post)) ? 0 : 1;
		!is_array($_REPLACE) && $_REPLACE = array();
		!is_array($_FORBID) && $_FORBID = array();
		foreach ($_FORBID as $value) {
			N_stripos($cmtcontent,$value['word']) && exit('word_ban');
		}
		$ifwordsfb = 0;
		$ckcontent = $cmtcontent;
		$_FORBIDDB = $_REPLACE+$_FORBID;
		foreach ($_FORBIDDB as $value) {
			$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
		}
		if ($ckcontent != $cmtcontent) {
			$cmtcontent = $ckcontent;
			$ifwordsfb = 1;
		}
		$cmtcontent = preg_replace('/\[attachment=([0-9]+)\]/is','',$cmtcontent);
		$ipfrom  = cvipfrom($onlineip);
		$ifcheck = $db_commentcheck ? 0 : 1;
		$db->update("INSERT INTO pw_comment(cid,itemid,type,uid,author,authorid,postdate,userip,ipfrom,ifcheck,ifwordsfb,ifconvert,content) VALUES ('$cid','$id','$type','$uid','$cmtuser','$winduid','$timestamp','$onlineip','$ipfrom','$ifcheck','$ifwordsfb','$ifconvert','$cmtcontent')");
		$cmtid = $db->insert_id();
		if ($ifcheck) {
			if($rt[bbsfid]){
				$itemid = $rt['itemid'];
				$cmttext = array();
				$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$itemid' AND c.type='blog' ORDER BY c.postdate DESC");
				while($commentdb = $db->fetch_array($query)){
					$commentdbs[id] = $commentdb[id];
					$commentdbs[author] = $commentdb[author];
					$commentdbs[authorid] = $commentdb[authorid];
					$commentdbs[postdate] = $commentdb[postdate];
					$commentdbs[ifwordsfb] = $commentdb[ifwordsfb];
					$commentdbs[ifconvert] = $commentdb[ifconvert];
					$commentdbs[content] = $commentdb[content];
					$commentdbs[picon] = $commentdb[icon];
					$commentdbs[replydate] = $commentdb[replydate];
					$commentdbs[reply] = $commentdb[reply];
					$cmttext[] = $commentdbs;
				}
			}else{
				$cmttext = array_merge(array(array('id' => $cmtid,'author' => $cmtuser,'authorid' => $winduid,'picon' => $winddb['icon'],'postdate' => $timestamp,'ifwordsfb' => $ifwordsfb,'ifconvert' => $ifconvert,'content' => $cmtcontent)),$cmttext);
			}
			!$db_perpage && $db_perpage = 30;
			$cmttext = array_slice($cmttext,0,$db_perpage);
			Strip_S($cmttext);
			$cmttext = addslashes(serialize($cmttext));
			$db->update("UPDATE pw_items SET replies=replies+1,lastreplies='$timestamp',cmttext='$cmttext' WHERE itemid='$id'");
			$winduid && $db->update("UPDATE pw_user SET comments=comments+1 WHERE uid='$winduid'");
			$cmtcontent = nl2br($cmtcontent);
			$ifconvert && $cmtcontent = convert($cmtcontent,$db_post);
			$delcmt = $_GROUP['delcmt'] ? 1 : 0;
			strpos($cmtcontent,'[s:')!==false && $cmtcontent = showsmile($cmtcontent);
			$success = "success\t$cmtid\t$windicon\t$winduid\t$cmtuser\t".get_date($timestamp)."\t$cmtcontent\t$delcmt\t$type\t$id";
		} else {
			$success = 'success';
		}
	}elseif($type == 'photo'){
		$rt = $db->get_one("SELECT p.pid,p.aid,p.cid,p.uid,p.cmttext,p.pallowreply,ui.gdcheck,ui.qcheck,ui.postnum,ui.plimitnum FROM pw_photo p LEFT JOIN pw_userinfo ui ON p.uid=ui.uid WHERE p.pid='$id'");
		!$rt['pid'] && exit('illegal_tid');
		$_GROUP['closecmt'] && $rt['allowreply'] = 0;
		!$rt['pallowreply'] && exit('group_right');
		list(,,,,$cmtgd) = explode("\t",$db_gdcheck);
		if (!$cmtgd) {
			list($cmtgd) = explode(',',$rt['gdcheck']);
		}
		if ($cmtgd) {
			$cknum = GetCookie('cknum');
			Cookie('cknum','',0);
			if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
				exit('gdcode_error');
			}
		}
		
		list(,,,,$cmtq) = explode("\t",$db_qcheck);
		list(,$cmtqcheck) = explode(',',$rt['qcheck']);
		if(($cmtq=='1' || $cmtqcheck=='1') && !empty($db_question)){
			$answer = unserialize($db_answer);
			($qanswer != $answer[$qkey]) && exit('qanswer_error');
		}
		
		$cid = $rt['cid'];
		$uid = $rt['uid'];
		$cmttext = $rt['cmttext'] ? (array)unserialize($rt['cmttext']) : array();
		list(,$spostnum) = explode(',',$_GROUP['postnum']);
		list(,$slimitnum) = explode(',',$_GROUP['limitnum']);
		list($upostnum) = explode(',',$rt['postnum']);
		list($ulimitnum) = explode(',',$rt['plimitnum']);
		$spostnum = (int)$spostnum;
		$upostnum = (int)$upostnum;
		$postnum = min($spostnum,$upostnum);
		if (!$spostnum || !$upostnum) {
			$postnum = $spostnum ? $spostnum : $upostnum;
		}
		$limitnum = (int)max($slimitnum,$ulimitnum);
		if (!$slimitnum || !$ulimitnum) {
			$limitnum = $slimitnum ? $slimitnum : $ulimitnum;
		}
		if ($postnum || $limitnum) {
			$count = $postdate = 0;
			$query = $db->query("SELECT postdate FROM pw_comment WHERE userip='$onlineip' AND postdate > $tdtime ORDER BY postdate DESC");
			while ($rt2 = $db->fetch_array($query)) {
				!$postdate && $postdate = $rt2['postdate'];
				($limitnum && ($timestamp - $postdate < $limitnum)) && exit("time_limit\t$limitnum");
				($postnum && $count > $postnum) && exit("post_limit\t$postnum");
				$count++;
			}
			$db->free_result($query);
		}
		$winduid = (int)$winduid;
		$cmtcontent = AutoUrl($cmtcontent);
		$cmtcontent = Atc_cv($cmtcontent,0);
		$ifconvert = ($cmtcontent==convert($cmtcontent,$db_post)) ? 0 : 1;
		!is_array($_REPLACE) && $_REPLACE = array();
		!is_array($_FORBID) && $_FORBID = array();
		foreach ($_FORBID as $value) {
			N_stripos($cmtcontent,$value['word']) && exit('word_ban');
		}
		$ifwordsfb = 0;
		$ckcontent = $cmtcontent;
		$_FORBIDDB = $_REPLACE+$_FORBID;
		foreach ($_FORBIDDB as $value) {
			$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
		}
		if ($ckcontent != $cmtcontent) {
			$cmtcontent = $ckcontent;
			$ifwordsfb = 1;
		}
		$cmtcontent = preg_replace('/\[attachment=([0-9]+)\]/is','',$cmtcontent);
		$ipfrom  = cvipfrom($onlineip);
		$ifcheck = $db_commentcheck ? 0 : 1;
		$db->update("INSERT INTO pw_comment(cid,itemid,type,uid,author,authorid,postdate,userip,ipfrom,ifcheck,ifwordsfb,ifconvert,content) VALUES ('$cid','$id','$type','$uid','$cmtuser','$winduid','$timestamp','$onlineip','$ipfrom','$ifcheck','$ifwordsfb','$ifconvert','$cmtcontent')");
		$cmtid = $db->insert_id();
		if ($ifcheck) {
			$cmttext = array_merge(array(array('id' => $cmtid,'author' => $cmtuser,'authorid' => $winduid,'picon' => $winddb['icon'],'postdate' => $timestamp,'ifwordsfb' => $ifwordsfb,'ifconvert' => $ifconvert,'content' => $cmtcontent)),$cmttext);
			!$db_perpage && $db_perpage = 30;
			$cmttext = array_slice($cmttext,0,$db_perpage);
			Strip_S($cmttext);
			$cmttext = addslashes(serialize($cmttext));
			$db->update("UPDATE pw_albums SET replies=replies+1,lastreplies='$timestamp' WHERE aid='$rt[aid]'");
			$db->update("UPDATE pw_photo SET preplies=preplies+1,cmttext='$cmttext' WHERE pid='$id'");
			$winduid && $db->update("UPDATE pw_user SET comments=comments+1 WHERE uid='$winduid'");
			$cmtcontent = nl2br($cmtcontent);
			$ifconvert && $cmtcontent = convert($cmtcontent,$db_post);
			$delcmt = $_GROUP['delcmt'] ? 1 : 0;
			strpos($cmtcontent,'[s:')!==false && $cmtcontent = showsmile($cmtcontent);
			$success = "success\t$cmtid\t$windicon\t$winduid\t$cmtuser\t".get_date($timestamp)."\t$cmtcontent\t$delcmt\t$type";
		} else {
			$success = 'success';
		}
	}elseif($type == 'music'){
		$rt = $db->get_one("SELECT ma.maid,ma.cid,ma.uid,ma.allowreply,ma.cmttext,ui.gdcheck,ui.qcheck,ui.postnum,ui.plimitnum FROM pw_malbums ma LEFT JOIN pw_userinfo ui ON ma.uid=ui.uid WHERE ma.maid='$id'");
		!$rt['maid'] && exit('illegal_tid');
		$_GROUP['closecmt'] && $rt['allowreply'] = 0;
		!$rt['allowreply'] && exit('group_right');
		list(,,,,$cmtgd) = explode("\t",$db_gdcheck);
		if (!$cmtgd) {
			list($cmtgd) = explode(',',$rt['gdcheck']);
		}
		if ($cmtgd) {
			$cknum = GetCookie('cknum');
			Cookie('cknum','',0);
			if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
				exit('gdcode_error');
			}
		}
		
		list(,,,,$cmtq) = explode("\t",$db_qcheck);
		list(,$cmtqcheck) = explode(',',$rt['qcheck']);
		if(($cmtq=='1' || $cmtqcheck=='1') && !empty($db_question)){
			$answer = unserialize($db_answer);
			($qanswer != $answer[$qkey]) && exit('qanswer_error');
		}
		$cid = $rt['cid'];
		$uid = $rt['uid'];
		$cmttext = $rt['cmttext'] ? (array)unserialize($rt['cmttext']) : array();
		list(,$spostnum) = explode(',',$_GROUP['postnum']);
		list(,$slimitnum) = explode(',',$_GROUP['limitnum']);
		list($upostnum) = explode(',',$rt['postnum']);
		list($ulimitnum) = explode(',',$rt['plimitnum']);
		$spostnum = (int)$spostnum;
		$upostnum = (int)$upostnum;
		$postnum = min($spostnum,$upostnum);
		if (!$spostnum || !$upostnum) {
			$postnum = $spostnum ? $spostnum : $upostnum;
		}
		$limitnum = (int)max($slimitnum,$ulimitnum);
		if (!$slimitnum || !$ulimitnum) {
			$limitnum = $slimitnum ? $slimitnum : $ulimitnum;
		}
		if ($postnum || $limitnum) {
			$count = $postdate = 0;
			$query = $db->query("SELECT postdate FROM pw_comment WHERE userip='$onlineip' AND postdate > $tdtime ORDER BY postdate DESC");
			while ($rt = $db->fetch_array($query)) {
				!$postdate && $postdate = $rt['postdate'];
				($limitnum && ($timestamp - $postdate < $limitnum)) && exit("time_limit\t$limitnum");
				($postnum && $count > $postnum) && exit("post_limit\t$postnum");
				$count++;
			}
			$db->free_result($query);
		}
		$winduid = (int)$winduid;
		$cmtcontent = AutoUrl($cmtcontent);
		$cmtcontent = Atc_cv($cmtcontent,0);
		$ifconvert = ($cmtcontent==convert($cmtcontent,$db_post)) ? 0 : 1;
		!is_array($_REPLACE) && $_REPLACE = array();
		!is_array($_FORBID) && $_FORBID = array();
		foreach ($_FORBID as $value) {
			N_stripos($cmtcontent,$value['word']) && exit('word_ban');
		}
		$ifwordsfb = 0;
		$ckcontent = $cmtcontent;
		$_FORBIDDB = $_REPLACE+$_FORBID;
		foreach ($_FORBIDDB as $value) {
			$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
		}
		if ($ckcontent != $cmtcontent) {
			$cmtcontent = $ckcontent;
			$ifwordsfb = 1;
		}
		$cmtcontent = preg_replace('/\[attachment=([0-9]+)\]/is','',$cmtcontent);
		$ipfrom  = cvipfrom($onlineip);
		$ifcheck = $db_commentcheck ? 0 : 1;
		$db->update("INSERT INTO pw_comment(cid,itemid,type,uid,author,authorid,postdate,userip,ipfrom,ifcheck,ifwordsfb,ifconvert,content) VALUES ('$cid','$id','$type','$uid','$cmtuser','$winduid','$timestamp','$onlineip','$ipfrom','$ifcheck','$ifwordsfb','$ifconvert','$cmtcontent')");
		$cmtid = $db->insert_id();
		if ($ifcheck) {
			$cmttext = array_merge(array(array('id' => $cmtid,'author' => $cmtuser,'authorid' => $winduid,'picon' => $winddb['icon'],'postdate' => $timestamp,'ifwordsfb' => $ifwordsfb,'ifconvert' => $ifconvert,'content' => $cmtcontent)),$cmttext);
			!$db_perpage && $db_perpage = 30;
			$cmttext = array_slice($cmttext,0,$db_perpage);
			Strip_S($cmttext);
			$cmttext = addslashes(serialize($cmttext));
			$db->update("UPDATE pw_malbums SET replies=replies+1,lastreplies='$timestamp',cmttext='$cmttext' WHERE maid='$id'");
			$winduid && $db->update("UPDATE pw_user SET comments=comments+1 WHERE uid='$winduid'");
			$cmtcontent = nl2br($cmtcontent);
			$ifconvert && $cmtcontent = convert($cmtcontent,$db_post);
			$delcmt = $_GROUP['delcmt'] ? 1 : 0;
			strpos($cmtcontent,'[s:')!==false && $cmtcontent = showsmile($cmtcontent);
			$success = "success\t$cmtid\t$windicon\t$winduid\t$cmtuser\t".get_date($timestamp)."\t$cmtcontent\t$delcmt\t$type";
		} else {
			$success = 'success';
		}
	}
	ShowRresponse($success,true);
} elseif ($action=='delcomment') {
	$cmt = $db->get_one("SELECT id,itemid,uid FROM pw_comment WHERE id='$id'");
	!$cmt['id'] && exit('illegal_cmtid');
	!$_GROUP['delcmt'] && exit('group_right');
	$db->update("DELETE FROM pw_comment WHERE id = '$id'");
	$cmttext = array(); $lastreplies = 0;
	!$db_perpage && $db_perpage = 30;
	if($type == 'blog'){
		$query = $db->query("SELECT c.id,c.itemid,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,c.replydate,c.reply,i.lastreplies,i.cmttext,u.icon as picon FROM pw_comment c LEFT JOIN pw_items i ON c.itemid=i.itemid LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.itemid='$cmt[itemid]' ORDER BY postdate DESC LIMIT 0,$db_perpage");
		while ($rt = $db->fetch_array($query)) {
			!$lastreplies && $lastreplies = $rt['postdate'];
			$cmttext[] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content'],'replydate' => $rt['replydate'],'reply' => $rt['reply']);
		}
		$db->free_result($query);
		if (!empty($cmttext)) {
			Strip_S($cmttext);
			$cmttext = addslashes(serialize($cmttext));
		} else {
			$cmttext = '';
		}
		$db->update("UPDATE pw_items SET replies=replies-1,lastreplies='$lastreplies',cmttext='$cmttext' WHERE itemid='$cmt[itemid]'");
		$cmt['uid'] && $db->update("UPDATE pw_user SET comments=comments-1 WHERE uid='$cmt[uid]'");
	}elseif($type == 'photo'){
		$query = $db->query("SELECT c.id,c.itemid,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,p.plastreplies,p.cmttext,u.icon as picon FROM pw_comment c LEFT JOIN pw_photo p ON c.itemid=p.pid LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.itemid='$cmt[itemid]' ORDER BY postdate DESC LIMIT 0,$db_perpage");
		while ($rt = $db->fetch_array($query)) {
			!$lastreplies && $lastreplies = $rt['postdate'];
			$cmttext[] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content']);
		}
		$db->free_result($query);
		if (!empty($cmttext)) {
			Strip_S($cmttext);
			$cmttext = addslashes(serialize($cmttext));
		} else {
			$cmttext = '';
		}
		$aid = $db->get_value("SELECT aid FROM pw_photo WHERE pid='$cmt[itemid]'");
		
		$db->update("UPDATE pw_albums SET replies=replies-1,lastreplies='$lastreplies' WHERE aid='$aid'");
		$db->update("UPDATE pw_photo SET preplies=preplies-1,plastreplies='$lastreplies',cmttext='$cmttext' WHERE pid='$cmt[itemid]'");
		$cmt['uid'] && $db->update("UPDATE pw_user SET comments=comments-1 WHERE uid='$cmt[uid]'");
	}elseif($type == 'music'){
		$query = $db->query("SELECT c.id,c.itemid,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,ma.lastreplies,ma.cmttext,u.icon as picon FROM pw_comment c LEFT JOIN pw_malbums ma ON c.itemid=ma.maid LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.itemid='$cmt[itemid]' ORDER BY postdate DESC LIMIT 0,$db_perpage");
		while ($rt = $db->fetch_array($query)) {
			!$lastreplies && $lastreplies = $rt['postdate'];
			$cmttext[] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content']);
		}
		$db->free_result($query);
		if (!empty($cmttext)) {
			Strip_S($cmttext);
			$cmttext = addslashes(serialize($cmttext));
		} else {
			$cmttext = '';
		}
		$db->update("UPDATE pw_malbums SET replies=replies-1,lastreplies='$lastreplies',cmttext='$cmttext' WHERE maid='$cmt[itemid]'");
		$cmt['uid'] && $db->update("UPDATE pw_user SET comments=comments-1 WHERE uid='$cmt[uid]'");
	}
	exit("success\t$id");
} elseif ($action=='addgbook') {
	require_once(R_P.'mod/post_mod.php');
	require_once(R_P.'mod/windcode.php');
	@include(D_P.'data/cache/wordfb.php');
	(!$guser || !$gcontent) && exit('g_empty');
	$rt = $db->get_one("SELECT uid,gdcheck,qcheck,postnum,plimitnum,ifgbook FROM pw_userinfo WHERE uid='$id'");
	!$rt['uid'] && exit('illegal_uid');
	$_GROUP['closecmt'] && $rt['ifgbook'] = 0;
	!$rt['ifgbook'] && exit('group_right');
	list(,,,,,$gbookgd) = explode("\t",$db_gdcheck);
	if (!$gbookgd) {
		list(,$gbookgd) = explode(',',$rt['gdcheck']);
	}
	if ($gbookgd) {
		$cknum = GetCookie('cknum');
		Cookie('cknum','',0);
		if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
			exit('gdcode_error');
		}
	}
	list(,,,$gbq) = explode("\t",$db_qcheck);
	list(,$gbqcheck) = explode(',',$rt['qcheck']);
	if(($gbq=='1' || $gbqcheck=='1') && !empty($db_question)){
		$answer = unserialize($db_answer);
		($qanswer != $answer[$qkey]) && exit('qanswer_error');
	}
	list(,,$spostnum) = explode(',',$_GROUP['postnum']);
	list(,,$slimitnum) = explode(',',$_GROUP['limitnum']);
	list(,$upostnum) = explode(',',$rt['postnum']);
	list(,$ulimitnum) = explode(',',$rt['plimitnum']);
	$spostnum = (int)$spostnum;
	$upostnum = (int)$upostnum;
	$postnum = min($spostnum,$upostnum);
	if (!$spostnum || !$upostnum) {
		$postnum = $spostnum ? $spostnum : $upostnum;
	}
	$limitnum = (int)max($slimitnum,$ulimitnum);
	if (!$slimitnum || !$ulimitnum) {
		$limitnum = $slimitnum ? $slimitnum : $ulimitnum;
	}
	if ($postnum || $limitnum) {
		$count = $postdate = 0;
		$query = $db->query("SELECT postdate FROM pw_gbook WHERE userip='$onlineip' AND postdate > $tdtime ORDER BY postdate DESC");
		while ($rt = $db->fetch_array($query)) {
			!$postdate && $postdate = $rt['postdate'];
			($limitnum && ($timestamp - $postdate < $limitnum)) && exit("time_limit\t$limitnum");
			($postnum && $count > $postnum) && exit("post_limit\t$postnum");
			$count++;
		}
		$db->free_result($query);
	}
	$winduid = (int)$winduid;
	$gcontent = nl2br($gcontent);
	!is_array($_REPLACE) && $_REPLACE = array();
	!is_array($_FORBID) && $_FORBID = array();
	foreach ($_FORBID as $value) {
		N_stripos($gcontent,$value['word']) && exit('word_ban');
	}
	$ifwordsfb = 0;
	$ckcontent = $gcontent;
	$_FORBIDDB = $_REPLACE+$_FORBID;
	foreach ($_FORBIDDB as $value) {
		$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
	}
	if ($ckcontent != $gcontent) {
		$gcontent = $ckcontent;
		$ifwordsfb = 1;
	}
	strpos($gcontent,'[s:')!==false && $gcontent = showsmile($gcontent);
	$db->update("INSERT INTO pw_gbook(uid,author,authorid,authoricon,type,postdate,userip,content,ifwordsfb) VALUES ('$id','$guser','$winduid','$winddb[icon]','0','$timestamp','$onlineip','$gcontent','$ifwordsfb')");
	$gid = $db->insert_id();
	$winduid && $db->update("UPDATE pw_user SET msgs=msgs+1 WHERE uid='$id'");
	$delg = $winduid == $id ? 1 : 0;
	ShowRresponse("success\t$gid\t$windicon\t$winduid\t$guser\t".get_date($timestamp)."\t$gcontent\t$id\t$delg",true);
} elseif ($action=='replygbook') {
	require_once(R_P.'mod/post_mod.php');
	require_once(R_P.'mod/windcode.php');
	@include(D_P.'data/cache/wordfb.php');
	(!$replytext) && exit('g_empty');
	$id = $db->get_value("SELECT id FROM pw_gbook WHERE id='$id'");
	!$id && exit('illegal_gid');
	$uid != $winduid && exit('illegal_uid');
	$replytext = nl2br($replytext);
	!is_array($_REPLACE) && $_REPLACE = array();
	!is_array($_FORBID) && $_FORBID = array();
	foreach ($_FORBID as $value) {
		N_stripos($replytext,$value['word']) && exit('word_ban');
	}
	$ckcontent = $replytext;
	$_FORBIDDB = $_REPLACE+$_FORBID;
	foreach ($_FORBIDDB as $value) {
		$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
	}
	$ckcontent != $replytext && $replytext = $ckcontent;
	$db->update("UPDATE pw_gbook SET replydate='$timestamp',reply='$replytext' WHERE id='$id'");
	ShowRresponse("success\t$id\t$windid\t".get_date($timestamp)."\t$replytext",true);
} elseif ($action=='delgbook') {
	$id = $db->get_value("SELECT id FROM pw_gbook WHERE id='$id'");
	!$id && exit('illegal_gid');
	$uid != $winduid && exit('illegal_uid');
	$db->update("DELETE FROM pw_gbook WHERE id = '$id'");
	$db->update("UPDATE pw_user SET msgs=msgs-1 WHERE uid = '$uid'");
	ShowRresponse("success\t$id",true);
} elseif ($action=='album_password'){
	!$winduid && exit('not_login');
	!$pw && exit('empty_pw');
	!$aid && exit('illegal_aid');
	$pwd = $db->get_value("SELECT password FROM pw_albums WHERE aid='$aid'");
	$check_pwd = md5($pw);
	if($check_pwd == $pwd){
		Cookie('alubm_pwd',StrCode($winduid."\t".$aid."\t".$check_pwd));
		exit("success\t$uid\t$aid");
	}else{
		exit('illegal_pwd');
	}
} elseif ($action=='mhits') {
	$count = $db->get_value("SELECT mid FROM pw_music WHERE mid='$mid'");
	!$count && exit('illegal_tid');
	$db->update("UPDATE pw_music SET mhits=mhits+1 WHERE mid='$mid'");
	$db->update("UPDATE pw_malbums SET hits=hits+1 WHERE maid='$maid'");
	exit("success\t$mid");
} elseif ($action=='bmhits') {
	$count = $db->get_value("SELECT itemid FROM pw_items WHERE itemid='$itemid'");
	!$count && exit('illegal_tid');
	$db->update("UPDATE pw_items SET hits=hits+1 WHERE itemid='$itemid'");
	exit("success\t$itemid\t$type");
} elseif ($action=='friendinfo') {
	$query = $db->query("SELECT itemid,subject FROM pw_items WHERE uid='$fuid' AND type='blog' ORDER BY postdate LIMIT 0,5");
	$friendinfos = $fuid;
	while($rt = $db->fetch_array($query)){
		$friendinfo = implode("|",$rt);
		$friendinfos .= ($friendinfos ? "\t" : '')."$friendinfo";
	}
	if($db_charset!='utf-8'){
		$friendinfos = ajax_convert($friendinfos,'utf-8',$db_charset);
	}
	exit("$friendinfos");
} elseif ($action=='dopage'){
	$frienddb = $db->get_value("SELECT frienddb FROM pw_userinfo WHERE uid='$uid'");
	$friends = unserialize($frienddb);
	$limit = (int)($pageid-1) * 2;
	$friends = array_slice($friends,$limit,2);
	foreach($friends as $key => $onefriend){
		$friendlist = implode("|",$onefriend);
		$friendlists .= ($friendlists ? "\t" : '')."$friendlist";
	}
	if($db_charset!='utf-8'){
		$friendlists = ajax_convert($friendlists,'utf-8',$db_charset);
	}
	exit("$friendlists");
} elseif ($action == 'dorandom'){
	$query = $db->query("SELECT itemid,uid,author,type,subject,postdate FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND type='blog' ORDER BY Rand() Limit $maxid");
	$shownum = $db->get_value("SELECT shownum FROM pw_module WHERE funcname='RANDOMLIST'");
	list($showtnum) = explode(',',$shownum);
	while($rt = $db->fetch_array($query)){
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$randomlist = implode('|',$rt);
		$randomlists .= ($randomlists ? "\t" : '')."$randomlist";
	}
	if($db_charset!='utf-8'){
		$randomlists = ajax_convert($randomlists,'utf-8',$db_charset);
	}
	exit("$randomlists");
} elseif ($action == 'savemode'){
	//$str = implode(',',$v);
	$leftdb = $db->get_value("SELECT leftdb FROM pw_userinfo WHERE uid='$winduid'");
	$mode = explode(',',$boxes);
	$leftdb = unserialize($leftdb);
	foreach ($mode as $key => $value){
		foreach($leftdb as $k => $v){
			if($value == $v['sign']){
				$leftdb[$k]['order'] = $key;
			}
		}
	}
	$leftdb = serialize($leftdb);
	$db->update("UPDATE pw_userinfo SET leftdb='$leftdb' WHERE uid='$winduid'");
	exit("success");
} elseif ($action == 'setbgmusic'){
	$murl = $db->get_value("SELECT murl FROM pw_music WHERE mid='$mid'");
	if($murl && $winduid){
		$db->update("UPDATE pw_userinfo SET bmusicurl='$murl' WHERE uid='$winduid'");
		exit("success");
	}
} elseif ($action == 'savestyle'){
	$bgcolor = Char_cv($bgcolor);
	$bannerimg = Char_cv($bannerimg);
	$bgimg = Char_cv($bgimg);
	if(!empty($bgcolor) && !preg_match("/^#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/",$bgcolor)){
		exit("bgcolor_error");
	}
	if(!empty($bannerimg) && !preg_match("/^http/i",$bannerimg)){
		exit("bannerimg_error");
	}
	if(!empty($bgimg) && !preg_match("/^http/i",$bgimg)){
		exit("bgimg_error");
	}
	/*
	if($bgcolor || $bgimg){
		$diycss .= 'body{background-color:'.$bgcolor.' url('.$bgimg.') repeat;}';
	}
	if($bannerimg){
		$diycss .= '#header{background:url('.$bannerimg.') no-repeat right;}';
	}
	*/
	$arr_diycss = array('bgcolor' => $bgcolor,'bannerimg' => $bannerimg,'bgimg' => $bgimg,'bgtype' => $bgtype);
	$diycss = serialize($arr_diycss);
	$userstyle = $db->get_value("SELECT style FROM pw_userinfo WHERE uid='$winduid'");
	list($style) = explode('|',$userstyle);
	$diystyle = $style.'|'.'1';
	$db->update("UPDATE pw_userinfo SET style='$diystyle' WHERE uid='$winduid'");
	$db->update("UPDATE pw_userskin SET diycss='$diycss' WHERE uid='$winduid' AND sign='$style'");
	exit("success");
} elseif ($action == 'revertstyle'){
	$userstyle = $db->get_value("SELECT style FROM pw_userinfo WHERE uid='$winduid'");
	list($style,$ifdiy) = explode('|',$userstyle);
	@extract($db->get_one("SELECT css,diycss FROM pw_userskin WHERE uid='$winduid' AND sign='$style'"));
	if(empty($css)){
		$db->update("UPDATE pw_userinfo SET style='$style' WHERE uid='$winduid'");
	}
	$db->update("UPDATE pw_userskin SET diycss='' WHERE uid='$winduid' AND sign='$style'");
	exit("success");
} elseif ($action == 'getstyleinfo'){
	$userstyle = $db->get_value("SELECT style FROM pw_userinfo WHERE uid='$winduid'");
	list($style,$ifdiy) = explode('|',$userstyle);
	$userdiycss  = $db->get_value("SELECT diycss FROM pw_userskin WHERE uid='$winduid' AND sign='$style'");
	$userdiycss = unserialize($userdiycss);
	empty($userdiycss) && $userdiycss = array();
	$userdiycss_array[0] = $userdiycss[bgcolor];
	$userdiycss_array[1] = $userdiycss[bannerimg];
	$userdiycss_array[2] = $userdiycss[bgimg];
	$userdiycss_array[3] = $userdiycss[bgtype];
	$userdiycsss = implode("\t",$userdiycss_array);
	exit("$userdiycsss");
} elseif ($action == 'commendtomenu'){
	$frienddb = $db->get_one("SELECT * FROM pw_blogfriend WHERE uid='$winduid' AND ifcheck='1'");
	if(empty($frienddb)){
		exit('nofriend');
	}else{
		$responseText='<select id="fuid">';
		$query = $db->query("SELECT b.fuid,u.username FROM pw_blogfriend b LEFT JOIN pw_user u ON b.fuid=u.uid WHERE b.uid='$winduid' AND b.ifcheck=1 ORDER BY b.fdate DESC");
		while($rt = $db->fetch_array($query)){
			$responseText.="<option value=\"$rt[fuid]\">$rt[username]</option>";
		}
		$responseText.='</select>';
	}
	if($db_charset!='utf-8'){
		$responseText = ajax_convert($responseText,'utf-8',$db_charset);
	}
	exit("$responseText");
} elseif ($action == 'commendto'){
	$fuid = char_cv($fuid);
	$itemid = char_cv($itemid);
	$ckcarticle = $db->get_one("SELECT * FROM pw_carticle WHERE uid='$winduid' AND touid='$fuid' AND itemid='$itemid'");writeover(R_P.'test.txt',"9999");
	if(!empty($ckcarticle)){
		exit('havecommend');
	}else{
		$db->update("INSERT INTO pw_carticle (itemid,uid,touid,cdate) VALUES ('$itemid','$winduid','$fuid','$timestamp')");
		exit('success');
	}
} elseif ($action == 'replycmt'){
	require_once(R_P.'mod/post_mod.php');
	require_once(R_P.'mod/windcode.php');
	@include(D_P.'data/cache/wordfb.php');
	(!$replytext) && exit('g_empty');
	$id = $db->get_value("SELECT id FROM pw_comment WHERE id='$id'");
	!$id && exit('illegal_gid');
	$uid != $winduid && exit('illegal_uid');
	$replytext = nl2br($replytext);
	!is_array($_REPLACE) && $_REPLACE = array();
	!is_array($_FORBID) && $_FORBID = array();
	foreach ($_FORBID as $value) {
		N_stripos($replytext,$value['word']) && exit('word_ban');
	}
	$ckcontent = $replytext;
	$_FORBIDDB = $_REPLACE+$_FORBID;
	foreach ($_FORBIDDB as $value) {
		$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
	}
	$ckcontent != $replytext && $replytext = $ckcontent;
	$db->update("UPDATE pw_comment SET replydate='$timestamp',reply='$replytext' WHERE id='$id'");
	
	$cmttext = array();
	$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$itemid' AND c.type='$type' ORDER BY c.postdate DESC");
	while($commentdb = $db->fetch_array($query)){
		$commentdbs[id] = $commentdb[id];
		$commentdbs[author] = $commentdb[author];
		$commentdbs[authorid] = $commentdb[authorid];
		$commentdbs[postdate] = $commentdb[postdate];
		$commentdbs[ifwordsfb] = $commentdb[ifwordsfb];
		$commentdbs[ifconvert] = $commentdb[ifconvert];
		$commentdbs[content] = $commentdb[content];
		$commentdbs[picon] = $commentdb[icon];
		$commentdbs[replydate] = $commentdb[replydate];
		$commentdbs[reply] = $commentdb[reply];
		$cmttext[] = $commentdbs;
	}
	!$db_perpage && $db_perpage = 30;
	$cmttext = array_slice($cmttext,0,$db_perpage);
	Strip_S($cmttext);
	$cmttext = addslashes(serialize($cmttext));
	//$cmttext = array_merge(array(array('id' => $id,'author' => $windid,'authorid' => $winduid,'picon' => $winddb['icon'],'postdate' => $timestamp,'ifwordsfb' => '0','ifconvert' => '0','content' => $ckcontent)),$cmttext);
	$db->update("UPDATE pw_items SET cmttext='$cmttext' WHERE itemid='$itemid'");
	ShowRresponse("success\t$id\t$windid\t".get_date($timestamp)."\t$replytext",true);
} elseif ($action == 'transferarticle'){
	require_once(R_P.'mod/ipfrom_mod.php');
	include_once(D_P.'data/cache/level_cache.php');
	$ipfrom = cvipfrom($onlineip);
	$itemdb = $db->get_one("SELECT i.*,b.* FROM pw_items i LEFT JOIN pw_blog b ON i.itemid=b.itemid WHERE i.itemid='$itemid' AND i.type='blog'");
	if($winduid == $itemdb['uid']){
		exit('own');
	}
	$transfer = $itemdb['uid'].','.$itemdb['author'];
	$ifcheck = $db_postcheck ? 0 : 1;
	$db->update("INSERT INTO pw_items(cid,dirid,uid,transfer,author,type,icon,subject,postdate,allowreply,ifcheck,ifwordsfb,uploads) VALUES ('$cid','$dirid','$winduid','$transfer','$windid','blog','{$itemdb['icon']}','{$itemdb['subject']}','$timestamp','1','$ifcheck','{$itemdb['ifwordsfb']}','{$itemdb['uploads']}')");
	$itemid = $db->insert_id();
	$db->update("INSERT INTO pw_blog (itemid,userip,ipfrom,ifconvert,content) VALUES('$itemid','$onlineip','$ipfrom','{$itemdb['ifconvert']}','{$itemdb['content']}')");
	$ifcheck && $db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$cid'");
	updatecache_cate('blog');
	$userdb = $ifcheck ? array('uid' => $winduid,'type' => 'blog','items' => $winddb['items'],'todaypost' => $winddb['todaypost'],'lastpost' => $winddb['lastpost']) : array();
	update_post($userdb);
	update_bloginfo_cache('blogs');
	exit('success');
}
function ShowRresponse($responseText,$cklang = null){
	global $db_charset;
	if (empty($cklang)) {
		global $rg_minlen,$rg_maxlen,$rg_domainmin,$rg_domainmax,$banword;
		require_once GetLang('msg');
		$responseText = $lang[$responseText];
	}
	if ($db_charset != 'utf-8') {
		!function_exists('convert_charset') && require_once(R_P.'mod/charset_mod.php');
		$responseText = convert_charset($db_charset,'utf-8',$responseText);
	}
	header("Content-Type: text/html; charset=utf-8");
	echo $responseText;
	exit;
}
function updatecache_cate($catetype = null){
	global $db;
	$where = !empty($catetype) ? "WHERE catetype='$catetype'" : '';
	$db->update("UPDATE pw_categories SET type='0',cupinfo='' $where".($where ? " AND" : "WHERE")." cup='0'");
	$catedb = $subdb = array();
	$typedb = array('blog','bookmark','file','goods','music','photo','team','user');
	$query = $db->query("SELECT cid,cup,type,name,descrip,cupinfo,counts,vieworder,_ifshow,catetype,fid FROM pw_categories $where ORDER BY vieworder,cid");
	while ($rt = $db->fetch_array($query)) {
		if (in_array($rt['catetype'],$typedb)) {
			P_unlink(D_P."data/cache/cate_cid_$rt[cid].php");
			!is_array(${'_'.$rt['catetype']}) && ${'_'.$rt['catetype']} = array();
			$rt['name']	   = Char_cv($rt['name'],'N');
			$rt['descrip'] = Char_cv($rt['descrip'],'N');
			if ($rt['cup'] == 0) {
				$catedb[] = $rt;
			} else {
				$subdb[$rt['cup']][] = $rt;
			}
		}
	}
	foreach ($catedb as $cate) {
		if (empty($cate)) continue;
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($subdb[$cate['cid']])) continue;
		${'_'.$cate['catetype']} += get_subcate($subdb,$cate['cid']);
	}
	foreach ($typedb as $value) {
		if (!empty($catetype) && $value != $catetype) {
			continue;
		}
		$writecache = '$_'.strtoupper($value).' = '.N_var_export(${'_'.$value}).";\r\n";
		writeover(D_P."data/cache/forum_cache_{$value}.php","<?php\r\n$writecache?>");
	}
}
function get_subcate($array,$cid){
	global $db;
	static $type = 0;
	static $cupinfo = null;
	$type++;
	$cupinfo .= (empty($cupinfo) ? '' : ',').$cid;
	foreach ($array[$cid] as $cate) {
		if (empty($cate)) continue;
		$sql = '';
		if ($cate['type'] != $type) {
			$cate['type'] = $type;
			$sql .= "type='$type'";
		}
		if ($cate['cupinfo'] != $cupinfo) {
			$cate['cupinfo'] = $cupinfo;
			$sql && $sql .= ',';
			$sql .= "cupinfo='$cupinfo'";
		}
		$sql && $db->update("UPDATE pw_categories SET $sql WHERE cid='$cate[cid]'");
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($array[$cate['cid']])) {
			$type = 0;
			$cupinfo = null;
			continue;
		}
		${'_'.$cate['catetype']} += get_subcate($array,$cate['cid']);
	}
	return ${'_'.$cate['catetype']};
}
function P_unlink($filename){
	strpos($filename,'..')!==false && exit('Forbidden');
	@unlink($filename);
}
function N_var_export($input,$f = 1,$t = null) {
	$output = '';
	if (is_array($input)) {
		$output .= "array(\r\n";
		foreach ($input as $key => $value) {
			$output .= $t."\t".N_var_export($key,$f,$t."\t").' => '.N_var_export($value,$f,$t."\t");
			$output .= ",\r\n";
		}
		$output .= $t.')';
	} elseif (is_string($input)) {
		$output .= $f ? "'".str_replace(array("\\","'"),array("\\\\","\'"),$input)."'" : "'$input'";
	} elseif (is_int($input) || is_double($input)) {
		$output .= "'".(string)$input."'";
	} elseif (is_bool($input)) {
		$output .= $input ? 'true' : 'false';
	} else {
		$output .= 'NULL';
	}
	return $output;
}

function update_malbumdb($uid){
	global $db;
	$cachedb = array();
	(int)$malbums = 0;
	$query = $db->query("SELECT maid,subject,descrip,ifcheck,allowreply FROM pw_malbums WHERE uid='$uid' ORDER BY postdate DESC");
	while ($rt = $db->fetch_array($query)) {
		empty($rt[hpageurl]) && $rt[hpageurl] = 'nopic.jpg';
		$cachedb[$rt['maid']] = $rt;
		$malbums++;
	}
	if (!empty($cachedb)) {
		Strip_S($cachedb);
		$cachedb = addslashes(serialize($cachedb));
	}
	$itemdb = $db->get_one("SELECT blogs,albums,bookmarks FROM pw_user WHERE uid='$uid'");
	$items = (int)$itemdb['blogs'] + (int)$itemdb['albums'] + (int)$itemdb['bookmarks'] + $malbums;
	$db->update("UPDATE pw_userinfo SET malbumdb='$cachedb' WHERE uid='$uid'");
	$db->update("UPDATE pw_user SET malbums='$malbums',items='$items' WHERE uid='$uid'");
}

function ajax_convert($str,$to_encoding,$from_encoding='utf-8'){
	if(function_exists('mb_convert_encoding')){
		return mb_convert_encoding($str,$to_encoding,$from_encoding);
	} else{
		require_once(R_P.'wap/chinese.php');
		$chs = new Chinese($from_encoding,$to_encoding);
		return $chs->Convert($str);
	}
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
	foreach ($_gmember as $key => $value) {
	(int)$nums>=$value['creditneed'] && $gid = $key;
	}
	return $gid;
}
?>