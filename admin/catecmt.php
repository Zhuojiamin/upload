<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";

if ($set == 'list') {
	InitGP(array('page','author','ckauthor','keyword','postdate1','postdate2','ifcheck','orderby','sc','perpage','username','ckusername'));
	$sql = $addpage = $pages = '';
	$cmtdb = array();
	if (strlen($author) > 0) {
		$sql .= ($sql ? ' AND' : '').' i.author';
		if ($ckauthor) {
			$sql .= "='$author'";
			$addpage = 'ckauthor=1&';
		} else {
			$sql .= " LIKE '%".str_replace('*','%',$author)."%'";
		}
		$addpage .= "author=$author&";
	}
	if (strlen($keyword) > 0) {
		$sql .= ($sql ? ' AND' : '')." i.content";
		/*if ($ckkeyword) {
			$sql .= "='$keyword'";
			$addpage .= 'ckkeyword=1&';
		} else {*/
			$sql .= " LIKE '%".str_replace('*','%',$keyword)."%'";
//		}
		$addpage .= "keyword=$keyword&";
	}
	if (strlen($postdate1) > 0 || strlen($postdate2) > 0) {
		if ($postdate1) {
			!is_numeric($postdate1) && $postdate1 = PwStrtoTime($postdate1);
			$sql .= ($sql ? ' AND' : '')." i.postdate>'$postdate1'";
			$addpage .= "postdate1=$postdate1&";
		}
		if ($postdate2) {
			!is_numeric($postdate2) && $postdate2 = PwStrtoTime($postdate2);
			$sql .= ($sql ? ' AND' : '')." i.postdate<'$postdate2'";
			$addpage .= "postdate2=$postdate2&";
		}
	}
	if (strlen($ifcheck) > 0 && (int)$ifcheck > -1) {
		$sql .= ($sql ? ' AND' : '')." i.ifcheck='$ifcheck'";
		$addpage .= "ifcheck=$ifcheck&";
	}
	if (strlen($username) > 0) {
		$sql .= ($sql ? ' AND' : '').' im.username';
		if ($ckusername) {
			$sql .= "='$username'";
			$addpage = 'ckusername=1&';
		} else {
			$sql .= " LIKE '%".str_replace('*','%',$username)."%'";
		}
		$addpage .= "username=$username&";
	}
	if (strlen($job) > 0) {
		$sql .= ($sql ? ' AND' : '')." i.type='$job'";
		$addpage .= "job=$job&";
	}
	$where = $sql ? "WHERE $sql" : '';
	$sc != 'desc' && $sc = 'asc';
	if ((int)$perpage < 1) {
		$perpage = $db_perpage ? $db_perpage : 50;
	}
	$addpage .= "sc=$sc&perpage=$perpage&";
	$orderby = " ORDER BY i.postdate $sc";
	(int)$page<1 && $page = 1;
	$limit = 'LIMIT '.($page-1)*$perpage.",$perpage";
	$query = $db->query("SELECT i.id,i.uid,i.author,i.postdate,i.ifcheck,i.content,im.username FROM pw_comment i LEFT JOIN pw_user im USING(uid) $where $orderby $limit");
	while ($rt = $db->fetch_array($query)) {
		strlen($rt['author']) < 1 && $rt['author'] = 'guest';
		strlen($rt['author']) > 0 && $rt['author'] = substrs($rt['author'],16);
		strlen($rt['content']) > 0 && $rt['content'] = substrs($rt['content'],50);
		$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
		$atcdb[] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_comment i LEFT JOIN pw_user im USING(uid) $where");
	if ($count > $perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$perpage,"$basename&set=list&$addpage");
	}
} elseif ($set == 'update') {
	InitGP(array('selid','type'),'P');
	$cids = '';
	!is_array($selid) && $selid = array();
	if (!empty($selid)) {
		foreach ($selid as $value) {
			if ((int)$value > 0) {
				$cids .= ($cids ? ',' : '')."'$value'";
			}
		}
	}
	!$cids && adminmsg('operate_error');
	$userdb = $itemdb = array();
	$sqlwhere = strpos($cids,',')===false ? "=$cids" : " IN ($cids)";
	$query = $db->query("SELECT uid,itemid,ifcheck FROM pw_comment WHERE id{$sqlwhere}");
	if ($type == 'delete') {
		while ($rt = $db->fetch_array($query)) {
			if ($rt['ifcheck']) {
				$userdb[$rt['uid']]['comments']++;
				$itemdb[$rt['itemid']]['replies']++;
			}
		}
		$db->free_result($query);
		foreach ($userdb as $key => $value) {
			$db->update("UPDATE pw_user SET comments=comments-'".(int)$value[comments]."' WHERE uid='$key'");
		}
		$db->update("DELETE FROM pw_comment WHERE id{$sqlwhere}");
	
		if($job == 'blog' || $job == 'bookmark'){
			foreach ($itemdb as $key => $value) {
				$cmttext = array();
				$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$key' AND c.type='$job' ORDER BY c.postdate DESC");
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
				empty($cmttext) && $cmttext = array();
				$cmttext = array_slice($cmttext,0,$db_perpage);
				Strip_S($cmttext);
				$cmttext = addslashes(serialize($cmttext));
				$db->update("UPDATE pw_items SET replies=replies-'".(int)$value[replies]."',cmttext='$cmttext' WHERE itemid='$key'");
			}
		}elseif($job == 'photo'){
			foreach ($itemdb as $key => $value) {
				$cmttext = array();
				$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$key' AND c.type='photo' ORDER BY c.postdate DESC");
				while($commentdb = $db->fetch_array($query)){
					$commentdbs[id] = $commentdb[id];
					$commentdbs[author] = $commentdb[author];
					$commentdbs[authorid] = $commentdb[authorid];
					$commentdbs[postdate] = $commentdb[postdate];
					$commentdbs[ifwordsfb] = $commentdb[ifwordsfb];
					$commentdbs[ifconvert] = $commentdb[ifconvert];
					$commentdbs[content] = $commentdb[content];
					$commentdbs[picon] = $commentdb[icon];
					$cmttext[] = $commentdbs;
				}
				!$db_perpage && $db_perpage = 30;
				empty($cmttext) && $cmttext = array();
				$cmttext = array_slice($cmttext,0,$db_perpage);
				Strip_S($cmttext);
				$cmttext = addslashes(serialize($cmttext));
				$db->update("UPDATE pw_photo SET preplies=preplies-'".(int)$value[replies]."',cmttext='$cmttext' WHERE pid='$key'");
				$aid = $db->get_value("SELECT aid FROM pw_photo WHERE pid='$key'");
				$db->update("UPDATE pw_albums SET replies=replies-'".(int)$value[replies]."' WHERE aid='$aid'");
			}
		}elseif($job == 'music'){
			foreach ($itemdb as $key => $value) {
				$cmttext = array();
				$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$key' AND c.type='music' ORDER BY c.postdate DESC");
				while($commentdb = $db->fetch_array($query)){
					$commentdbs[id] = $commentdb[id];
					$commentdbs[author] = $commentdb[author];
					$commentdbs[authorid] = $commentdb[authorid];
					$commentdbs[postdate] = $commentdb[postdate];
					$commentdbs[ifwordsfb] = $commentdb[ifwordsfb];
					$commentdbs[ifconvert] = $commentdb[ifconvert];
					$commentdbs[content] = $commentdb[content];
					$commentdbs[picon] = $commentdb[icon];
					$cmttext[] = $commentdbs;
				}
				!$db_perpage && $db_perpage = 30;
				empty($cmttext) && $cmttext = array();
				$cmttext = array_slice($cmttext,0,$db_perpage);
				Strip_S($cmttext);
				$cmttext = addslashes(serialize($cmttext));
				$db->update("UPDATE pw_malbums SET replies=replies-'".(int)$value[replies]."',cmttext='$cmttext' WHERE maid='$key'");
			}
		}
	} elseif ($type == 'allowcheck') {
		while ($rt = $db->fetch_array($query)) {
			if (!$rt['ifcheck']) {
				$userdb[$rt['uid']]['comments']++;
				$itemdb[$rt['itemid']]['replies']++;
			}
		}
		$db->free_result($query);
		foreach ($userdb as $key => $value) {
			$db->update("UPDATE pw_user SET comments=comments+'".(int)$value[comments]."' WHERE uid='$key'");
		}
		foreach ($itemdb as $key => $value) {
			$db->update("UPDATE pw_items SET replies=replies+'".(int)$value[replies]."' WHERE itemid='$key'");
		}
		$db->update("UPDATE pw_comment SET ifcheck='1' WHERE id{$sqlwhere}");
	}
	adminmsg('operate_success');
} else {
	$postdate1 = '2004-01-01';
	$postdate2 = get_date($timestamp+24*3600,'Y-m-d');
}
include PrintEot('catecmt');footer();
function PwStrtoTime($date){
	global $db_timedf;
	return function_exists('date_default_timezone_set') ? strtotime($date) - $db_timedf*3600 : strtotime($date);
}
?>