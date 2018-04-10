<?php
!function_exists('adminmsg') && exit('Forbidden');
if ($job != 'update') {
	include_once(D_P.'data/cache/forum_cache_user.php');
	$sysgpslt  = $categpslt = $memgpslt = '';
	$_gpsltall = $_gsystem+$_gspecial;
	foreach ($_gmember as $key => $value) {
		$memgpslt .= "<option value=\"$key\">$value[title]</option>";
	}
	foreach ($_gpsltall as $key => $value) {
		$sysgpslt .= "<option value=\"$key\">$value[title]</option>";
	}
	foreach ($_USER as $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$categpslt .= "<option value=\"$value[cid]\">$add $value[name]</option>";
	}
	if ($job != 'list') {
		$regdate1 = '2004-01-01';
		$regdate2 = get_date($timestamp+24*3600,'Y-m-d');
	} else {
		InitGP(array('page','usermid','usergid','usercid','commend','username','ckusername','domainname','ckdomain','email','ckemail','emlailjh','userip','ckip','regdate1','regdate2','userlv','lvtype','orderby','sc','perpage'));
		$sql = $addpage = $pages = '';
		$userdb = array();
		if (strlen($username) > 0) {
			$sql = 'u.username';
			if ($ckusername) {
				$sql .= "='$username'";
				$addpage = 'ckusername=1&';
			} else {
				$sql .= " LIKE '%".str_replace('*','%',$username)."%'";
			}
			$addpage .= "username=$username&";
		}
		if (strlen($email) > 0) {
			$sql .= ($sql ? ' AND' : '').' u.email';
			if ($ckemail) {
				$sql .= "='$email'";
				$addpage .= 'ckemail=1&';
			} else {
				$sql .= " LIKE '%".str_replace('*','%',$email)."%'";
			}
			$addpage .= "email=$email&";
		}
		if (strlen($domainname) > 0) {
			$sql .= ($sql ? ' AND' : '').' ui.domainname';
			if ($ckdomain) {
				$sql .= "='$domainname'";
				$addpage = 'ckdomain=1&';
			} else {
				$sql .= " LIKE '%".str_replace('*','%',$domainname)."%'";
			}
			$addpage .= "domainname=$domainname&";
		}
		if (strlen($usercid) > 0 && (int)$usercid > -1) {
			$sql .= ($sql ? ' AND' : '')." ui.cid='$usercid'";
			$addpage .= "usercid=$usercid&";
		}
		if ((int)$usermid > 7) {
			$sql .= ($sql ? ' AND' : '')." u.memberid='$usermid'";
			$addpage .= "usermid=$usermid&";
		}
		if ($usergid == '-1' || (int)$usergid > 2) {
			$sql .= ($sql ? ' AND' : '')." u.groupid='$usergid'";
			$addpage .= "usergid=$usergid&";
		}
		if (strlen($commend) > 0 && (int)$commend > -1 && (int)$commend < 3) {
			$sql .= ($sql ? ' AND' : '')." u.commend='$commend'";
			$addpage .= "commend=$commend&";
		}
		if (strlen($emlailjh) > 0 && (int)$emlailjh > -1) {
			$sql .= ($sql ? ' AND' : '').' u.verify'.($emlailjh==1 ? '=' : '!=')."'1'";
			$addpage .= "emlailjh=$emlailjh&";
		}
		if (strlen($userip) > 0) {
			$sql .= ($sql ? ' AND' : '').' u.onlineip';
			if ($ckip) {
				$sql .= "='$userip'";
				$addpage .= 'ckip=1&';
			} else {
				$sql .= " LIKE '%".str_replace('*','%',$userip)."%'";
			}
			$addpage .= "onlineip=$userip&";
		}
		if (strlen($regdate1) > 0 || strlen($regdate2) > 0) {
			if ($regdate1) {
				!is_numeric($regdate1) && $regdate1 = PwStrtoTime($regdate1);
				$sql .= ($sql ? ' AND' : '')." u.regdate>'$regdate1'";
				$addpage .= "regdate1=$regdate1&";
			}
			if ($regdate2) {
				!is_numeric($regdate2) && $regdate2 = PwStrtoTime($regdate2);
				$sql .= ($sql ? ' AND' : '')." u.regdate<'$regdate2'";
				$addpage .= "regdate2=$regdate2&";
			}
		}
		if ((int)$userlv > 0) {
			$addpage .= "userlv=$userlv&";
			if ($lvtype == 'day') {
				$userlv *= 24*3600;
			} elseif ($lvtype == 'month') {
				$userlv *= 30*24*3600;
			} elseif ($lvtype == 'year') {
				$userlv *= 365*24*3600;
			} else {
				$userlv = 0;
			}
			if ($userlv>0) {
				$addpage .= "lvtype=$lvtype&";
				$schtime = $timestamp-$userlv;
				$sql .= ($sql ? ' AND' : '')." u.thisvisit<'$schtime'";
			}
		}
		$where = $sql ? "WHERE $sql" : '';
		$orderby != 'lastvisit' && $orderby != 'regdate' && $orderby = 'username';
		$sc != 'desc' && $sc = 'asc';
		if ((int)$perpage<1) {
			$perpage = $db_perpage ? $db_perpage : 30;
		}
		$addpage .= "orderby=$orderby&sc=$sc&perpage=$perpage&";
		$orderby = " ORDER BY u.$orderby $sc";
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$perpage.",$perpage";
		$query = $db->query("SELECT u.uid,u.username,u.email,u.groupid,u.memberid,u.regdate,u.onlineip,u.commend,ui.cid,ui.domainname FROM pw_user u LEFT JOIN pw_userinfo ui USING(uid) $where $orderby $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['regdate'] = get_date($rt['regdate'],'Y-m-d');
			$rt['groupid']=='-1' && $rt['groupid'] = $rt['memberid'];
			$rt['group'] = $_alllevel[$rt['groupid']]['title'];
			$rt['cate'] = $_USER[$rt['cid']]['name'];
			$userdb[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_user u LEFT JOIN pw_userinfo ui USING(uid) $where");
		if ($count > $perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$perpage,"$basename&job=list&$addpage");
		}
	}
	include PrintEot('usercp');footer();
} else {
	InitGP(array('selid','type','usergid','usercid'),'P');
	$uids = '';
	!is_array($selid) && $selid = array();
	if (!empty($selid)) {
		foreach ($selid as $value) {
			if (is_numeric($value)) {
				$uids .= ($uids ? ',' : '')."'$value'";
			}
		}
	}
	!$uids && adminmsg('operate_error');
	$sqlwhere = strpos($uids,',')===false ? "=$uids" : " IN ($uids)";
	if (($type == 'delete' || $type == 'cggroup') && !If_manager) {
		$ckgroupid = $db->get_value("SELECT groupid FROM pw_user WHERE uid{$sqlwhere}");
		$ckgroupid == '3' && adminmsg('manager_right');
	}
	if ($type == 'delete') {
		$itemids = $updatesql = '';
		$catedb = $typedb = $cmtdb = $itemdb = array();
		$query = $db->query("SELECT itemid,cid,type FROM pw_items WHERE uid{$sqlwhere}");
		while ($rt = $db->fetch_array($query)) {
			$catedb[$rt['cid']]['num']++;
			$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
			$typedb[$rt['type']][] = $rt['itemid'];
		}
		$query = $db->query("SELECT uid,itemid,ifcheck FROM pw_comment WHERE authorid{$sqlwhere}");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['ifcheck']) {
				strpos($uids,"'$rt[uid]'")===false && $cmtdb[$rt['uid']]['num']++;
				strpos($itemids,"'$rt[itemid]'")===false && $itemdb[$rt['itemid']]['rls']++;
			}
		}
		$query = $db->query("SELECT itemid FROM pw_footprint WHERE uid{$sqlwhere}");
		while ($rt = $db->fetch_array($query)) {
			strpos($itemids,"'$rt[itemid]'")===false && $itemdb[$rt['itemid']]['fps']++;
		}
		$query = $db->query("SELECT attachurl,ifthumb FROM pw_upload WHERE uid{$sqlwhere}");
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
				$tmsgsql = '';
				foreach ($value as $v) {
					$tmsgsql .= ($tmsgsql ? ',' : '')."'$v'";
				}
				$tmsgsql = strpos($tmsgsql,',')===false ? "=$tmsgsql" : " IN ($tmsgsql)";
				$db->update("DELETE FROM pw_$key WHERE itemid{$tmsgsql}");
			}
		}
		$db->update("DELETE FROM pw_blogfriend WHERE uid{$sqlwhere} OR fuid{$sqlwhere}");
		$db->update("DELETE FROM pw_carticle WHERE uid{$sqlwhere} OR touid{$sqlwhere}");
		$db->update("DELETE FROM pw_collections WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_comment WHERE uid{$sqlwhere} OR authorid{$sqlwhere}");
		$db->update("DELETE FROM pw_footprint WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_gbook WHERE uid{$sqlwhere} OR authorid{$sqlwhere}");
		$db->update("DELETE FROM pw_items WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_itemtype WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_lcustom WHERE authorid{$sqlwhere}");
		$db->update("DELETE FROM pw_taginfo WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_btags WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_tblog WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_team WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_tgbook WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_tuser WHERE uid{$sqlwhere} OR admin{$sqlwhere}");
		$db->update("DELETE FROM pw_upload WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_user WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_userhobby WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_userinfo WHERE uid{$sqlwhere}");
		$db->update("DELETE FROM pw_userskin WHERE uid{$sqlwhere}");
		$db->update("UPDATE pw_bloginfo SET totalmember=totalmember-1 WHERE id='1'");
		updatecache_cate('user');
	} elseif ($type == 'emlailjh') {
		$db->update("UPDATE pw_user SET verify='1' WHERE uid{$sqlwhere}");
	} elseif (in_array($type,array('cmd','uncmd'))) {
		$usercmd = $type == 'cmd' ? '1' : '0';
		$db->update("UPDATE pw_user SET commend='$usercmd' WHERE uid{$sqlwhere}");
	} elseif ($type == 'cggroup') {
		!If_manager && $usergid == '3' && adminmsg('manager_right');
		$db->update("UPDATE pw_user SET groupid='".(int)$usergid."' WHERE uid{$sqlwhere}");
	} elseif ($type == 'cgcid') {
		$uidsql = '';
		$query = $db->query("SELECT uid,cid FROM pw_userinfo WHERE uid{$sqlwhere}");
		while ($rt = $db->fetch_array($query)) {
			if ($rt['cid'] != $usercid) {
				$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$usercid'");
				$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$rt[cid]'");
				$uidsql = ($uidsql ? ',' : '')."'$rt[uid]'";
			}
		}
		if ($uidsql) {
			$uidsql = strpos($uids,',')===false ? "=$uidsql" : " IN ($uidsql)";
			$db->update("UPDATE pw_userinfo SET cid='".(int)$usercid."' WHERE uid{$uidsql}");
		}
	}
	adminmsg('operate_success');
}
function PwStrtoTime($date){
	global $db_timedf;
	return function_exists('date_default_timezone_set') ? strtotime($date) - $db_timedf*3600 : strtotime($date);
}
?>