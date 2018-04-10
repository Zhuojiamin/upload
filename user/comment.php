<?php
!function_exists('usermsg') && exit('Forbidden');

$basename .= '&type='.$type;
!N_InArray($type,array('blog','bookmark','file','goods','music','photo')) && usermsg('undefined_action');

if ($job != 'update') {
	InitGP(array('page','author','keyword','ifcheck','sc'));
	$cmtdb = $catedb = $sltsc = $sltcheck = array();
	$sql = $addpage = $pages = $L_join = $sqladd = '';
	$sltsc[$sc] = $sltcheck[$ifcheck] = ' SELECTED';
	include_once(D_P."data/cache/forum_cache_$type.php");
	$catedb = ${strtoupper('_'.$type)};
	if (strlen($author) > 0) {
		$sql .= ($sql ? ' AND' : '')." c.author LIKE '%".str_replace('*','%',$author)."%'";
		$addpage .= "author=$author&";
	}
	if (strlen($keyword) > 0) {
		$sql .= ($sql ? ' AND' : '')." c.content LIKE '%".str_replace('*','%',$keyword)."%'";
		$addpage .= "keyword=$keyword&";
	}
	if (strlen($ifcheck) > 0 && (int)$ifcheck > -1) {
		$sql .= ($sql ? ' AND' : '')." c.ifcheck='$ifcheck'";
		$addpage .= "ifcheck=$ifcheck&";
	}
	if($type == 'photo'){
		$sqladd = ',p.name';
		$L_join .= " LEFT JOIN pw_photo p ON c.itemid=p.pid";
	}elseif($type == 'music'){
		$sqladd = ',ma.subject';
		$L_join .= " LEFT JOIN pw_malbums ma ON c.itemid=ma.maid";
	}elseif($type == 'blog'){
		$sqladd = ',i.subject';
		$L_join .= " LEFT JOIN pw_items i ON c.itemid=i.itemid";
	}else{
		$sqladd = '';
		$L_join .= '';
	}
	$sql && $sql = ' AND '.$sql;
	$sc != 'asc' && $sc = 'desc';
	!$db_perpage && $db_perpage = 30;
	$addpage .= "sc=$sc&";
	$orderby = " ORDER BY c.postdate $sc";
	(int)$page<1 && $page = 1;
	$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
	$query = $db->query("SELECT c.id,c.cid,c.author,c.authorid,c.postdate,c.ifcheck,c.content{$sqladd} FROM pw_comment c{$L_join} WHERE c.uid='$admin_uid'$sql AND c.type='".Char_cv($type)."' $orderby $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['author'] = substrs($rt['author'],16);
		$rt['cate'] = $rt['cid'] ? $catedb[$rt['cid']]['name'] : $ulang['none'];
		$rt['ifcheck'] = $ulang['ifcheck_'.$rt['ifcheck']];
		$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
		$rt['name'] && $rt['subject'] = $rt['name'];
		$rt['descrip'] && $rt['content'] = $rt['descrip'];
		$rt['content'] = str_replace(array("\r","\n"),'',$rt['content']);
		$rt['content'] = substrs($rt['content'],35);
		$cmtdb[] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_comment c WHERE uid='$admin_uid' AND type='$type'$sql");
	if ($count > $db_perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"$basename&$addpage");
	}
	
	include PrintEot('comment');footer();
} else {
	InitGP(array('selid','ntype','type'));
	$cids = '';
	!is_array($selid) && $selid = array();
	foreach ($selid as $value) {
		if ((int)$value > 0) {
			$cids .= ($cids ? ',' : '')."'$value'";
		}
	}
	!$cids && usermsg('operate_error');
	$userdb = $itemdb = array();
	$sqlwhere = strpos($cids,',')===false ? "=$cids" : " IN ($cids)";
	$query = $db->query("SELECT uid,itemid,ifcheck FROM pw_comment WHERE id{$sqlwhere}");
	if ($ntype == 'delete') {
		$cmttext = array();
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
		if($type == 'blog'){
			foreach ($itemdb as $key => $value) {
				$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$key' AND c.type='$type' ORDER BY c.postdate DESC");
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
		}elseif($type == 'photo'){
			foreach ($itemdb as $key => $value) {
				$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$key' AND c.type='$type' ORDER BY c.postdate DESC");
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
				$db->update("UPDATE pw_photo SET cmttext='$cmttext',preplies=preplies-'".(int)$value[replies]."' WHERE pid='$key'");
				$aid = $db->get_value("SELECT aid FROM pw_photo WHERE pid='$key'");
				$db->update("UPDATE pw_albums SET replies=replies-'".(int)$value[replies]."' WHERE aid='$aid'");
			}
		}elseif($type == 'music'){
			foreach ($itemdb as $key => $value) {
				$query = $db->query("SELECT c.*,u.icon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.itemid='$key' AND c.type='$type' ORDER BY c.postdate DESC");
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
	} elseif ($ntype == 'allowcheck') {
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
	usermsg('operate_success',"$basename");
}
?>