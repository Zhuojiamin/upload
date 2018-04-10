<?php
!function_exists('adminmsg') && exit('Forbidden');

$categpslt = '';
include_once(D_P."data/cache/forum_cache_team.php");
foreach ($_TEAM as $key => $value) {
	$add = '';
	for ($i=0;$i<$value['type'];$i++) {
		$add .= '>';
	}
	$categpslt .= "<option value=\"$value[cid]\">$add $value[name]</option>";
}
if ($job == 'list') {
	InitGP(array('page','atccid','name','ckname','username','ckusername','keyword','ckkeyword','postdate1','postdate2','sc','perpage','teamid'));
	$sql = $addpage = $pages = '';
	$atcdb = array();
	if (strlen($teamid) > 0 && (int)$teamid > -1) {
		$sql .= ($sql ? ' AND' : '')." tg.teamid='$teamid'";
		$addpage .= "teamid=$teamid&";
	}
	if (strlen($atccid) > 0 && (int)$atccid > -1) {
		$sql .= ($sql ? ' AND' : '')." t.cid='$atccid'";
		$addpage .= "atccid=$atccid&";
	}
	if (strlen($name) > 0) {
		$sql .= ($sql ? ' AND' : '').' t.name';
		if ($ckname) {
			$sql .= "='$name'";
			$addpage = 'ckname=1&';
		} else {
			$sql .= " LIKE '%".str_replace('*','%',$name)."%'";
		}
		$addpage .= "name=$name&";
	}
	if (strlen($username) > 0) {
		$sql .= ($sql ? ' AND' : '').' u.username';
		if ($ckusername) {
			$sql .= "='$username'";
			$addpage = 'ckusername=1&';
		} else {
			$sql .= " LIKE '%".str_replace('*','%',$username)."%'";
		}
		$addpage .= "username=$username&";
	}
	if (strlen($keyword) > 0) {
		$sql .= ($sql ? ' AND' : '').' tg.subject';
		if ($ckkeyword) {
			$sql .= "='$keyword'";
			$addpage = 'ckkeyword=1&';
		} else {
			$sql .= " LIKE '%".str_replace('*','%',$keyword)."%'";
		}
		$addpage .= "keyword=$keyword&";
	}
	if (strlen($postdate1) > 0 || strlen($postdate2) > 0) {
		if ($postdate1) {
			!is_numeric($postdate1) && $postdate1 = PwStrtoTime($postdate1);
			$sql .= ($sql ? ' AND' : '')." tg.postdate>'$postdate1'";
			$addpage .= "postdate1=$postdate1&";
		}
		if ($postdate2) {
			!is_numeric($postdate2) && $postdate2 = PwStrtoTime($postdate2);
			$sql .= ($sql ? ' AND' : '')." tg.postdate<'$postdate2'";
			$addpage .= "postdate2=$postdate2&";
		}
	}
	
	$where = $sql ? "WHERE $sql" : '';
	$sc != 'desc' && $sc = 'asc';
	if ((int)$perpage < 1) {
		$perpage = $db_perpage ? $db_perpage : 30;
	}
	$addpage .= "sc=$sc&perpage=$perpage&";
	$orderby = " ORDER BY tg.postdate $sc";
	(int)$page<1 && $page = 1;
	$limit = 'LIMIT '.($page-1)*$perpage.",$perpage";
	$query = $db->query("SELECT tg.itemid,tg.uid,tg.subject,tg.postdate,t.cid,t.name,t.cid,u.username FROM pw_tblog tg LEFT JOIN pw_team t USING(teamid) LEFT JOIN pw_user u ON tg.uid=u.uid $where $orderby $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['tid'] = $rt['itemid'].'.'.$rt['uid'];
		$rt['cate'] = $_TEAM[$rt['cid']]['name'];
		$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
		$atcdb[] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_tblog tg LEFT JOIN pw_team t USING(teamid) LEFT JOIN pw_user u ON tg.uid=u.uid $where");
	if ($count > $perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$perpage,"$basename&job=list&$addpage");
	}
} elseif ($job == 'update') {
	$selid = GetGP('selid','P');
	$uids = '';
	$tempdb = $teamdb = array();
	!is_array($selid) && $selid = array();
	if (!empty($selid)) {
		foreach ($selid as $value) {
			list($itemid,$uid) = explode('.',$value);
			if ($itemid && $uid) {
				$uids .= ($uids ? ',' : '')."'$uid'";
				$tempdb[$uid][] = $itemid;
			}
		}
	}
	empty($tempdb) && adminmsg('operate_error');
	$sqlwhere = strpos($uids,',')===false ? "=$uids" : " IN ($uids)";
	$query = $db->query("SELECT itemid,uid,teamid FROM pw_tblog WHERE uid{$sqlwhere}");
	while ($rt = $db->fetch_array($query)) {
		if (N_InArray($rt['itemid'],$tempdb[$rt['uid']])) {
			$db->update("DELETE FROM pw_tblog WHERE itemid='$rt[itemid]' AND uid='$rt[uid]'");
			$teamdb[$rt['teamid']][$rt['uid']]++;
		}
	}
	$db->free_result($query);
	foreach ($teamdb as $key => $array) {
		foreach ($array as $k => $v) {
			$db->update("UPDATE pw_tuser SET blogs=blogs-1 WHERE uid='$k' AND teamid='$key'");
			$db->update("UPDATE pw_team SET blogs=blogs-1 WHERE teamid='$key'");
		}
	}
	adminmsg('operate_success');
} else {
	$postdate1 = '2004-01-01';
	$postdate2 = get_date($timestamp+24*3600,'Y-m-d');
}
include PrintEot('teamatc');footer();
function PwStrtoTime($date){
	global $db_timedf;
	return function_exists('date_default_timezone_set') ? strtotime($date) - $db_timedf*3600 : strtotime($date);
}
?>