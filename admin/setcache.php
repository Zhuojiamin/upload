<?php
!function_exists('adminmsg') && exit('Forbidden');

$limitnum = GetGP('limitnum');
if ($job == 'all') {
	updatecache();
	adminmsg('operate_success');
} elseif ($job == 'bloginfo') {
	//$newmember = addslashes($db->get_value("SELECT uid,username FROM pw_user ORDER BY regdate DESC LIMIT 1"));
	list($newmember_uid,$newmember_name) = $db->get_one("SELECT uid,username FROM pw_user ORDER BY regdate DESC LIMIT 1");
	$newmember_uid = (int)$newmember_uid;
	$newmember_name = addslashes($newmember_name);
	$newmember = $newmember_uid.','.$newmember_name;
	$totalmember = $db->get_value("SELECT COUNT(*) FROM pw_user WHERE 1");
	$totalblogs = $db->get_value("SELECT COUNT(*) FROM pw_blog WHERE 1");
	$totalalbums = $db->get_value("SELECT COUNT(*) FROM pw_albums WHERE 1");
	$totalmalbums = $db->get_value("SELECT COUNT(*) FROM pw_malbums WHERE 1");
	$db->update("UPDATE pw_bloginfo SET newmember='$newmember',totalmember='$totalmember',totalblogs='$totalblogs',totalalbums='$totalalbums',totalmalbums='$totalmalbums' WHERE id='1'");
	adminmsg('operate_success');
} elseif ($job == 'group') {
	$start = GetGP('start','G');
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	$temparray = array();
	$query = $db->query("SELECT uid,blogs FROM pw_user ORDER BY uid DESC LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$temparray[$rt['uid']] = $rt['blogs'];
		$times++;
	}
	$db->free_result();
	foreach ($temparray as $key => $value) {
		$value = getmemberid($value);
		$db->update("UPDATE pw_user SET memberid='$value' WHERE uid='$key'");
	}
	if ($times < $limitnum) {
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=group&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
} elseif ($job == 'itemtype') {
	$start = GetGP('start','G');
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	$userdb = array();
	$query = $db->query("SELECT typeid,uid,type,name FROM pw_itemtype ORDER BY typeid DESC LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$userdb[$rt['uid']][$rt['type']][$rt['typeid']] = array('typeid' => $rt['typeid'],'type' => $rt['type'],'name' => $rt['name']);
		$times++;
	}
	$db->free_result();
	foreach ($userdb as $key => $value) {
		Strip_S($value);
		$dirdb = addslashes(serialize($value));
		$db->update("UPDATE pw_userinfo SET dirdb='$dirdb' WHERE uid='$key'");
	}
	if ($times < $limitnum) {
		updatecache_cate();
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=itemtype&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
} elseif ($job == 'categories') {
	$start = GetGP('start','G');
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	$temparray = array();
	$query = $db->query("SELECT cid,catetype FROM pw_categories ORDER BY cid DESC LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		P_unlink(D_P."data/cache/cate_cid_$rt[cid].php");
		if ($rt['catetype'] =='blog' || $rt['catetype'] =='bookmark') {
			$rt['catetype'] = 'items';
		}elseif($rt['catetype'] =='user') {
			$rt['catetype'] = 'userinfo';
		}elseif($rt['catetype'] =='photo') {
			$rt['catetype'] = 'albums';
		}elseif($rt['catetype'] =='music') {
			$rt['catetype'] = 'malbums';
		}elseif($rt['catetype'] =='team') {
			$rt['catetype'] = 'team';
		}
		$temparray[] = $rt;
		$times++;
	}
	$db->free_result();
	foreach ($temparray as $value) {
		if(!in_array($value['catetype'],array('items','userinfo','albums','malbums','team'))){
			continue;
		}
		if($value['catetype'] == 'items' || $rt['catetype'] == 'albums' || $rt['catetype'] =='malbums'){
			$sqlwhere =  " AND ifcheck='1' AND ifhide='0'";
		}else{
			$sqlwhere =  "";
		}
		$count = $db->get_value("SELECT COUNT(*) FROM pw_{$value[catetype]} WHERE cid='$value[cid]'$sqlwhere");
		$db->update("UPDATE pw_categories SET counts='$count' WHERE cid='$value[cid]'");
	}
	if ($times < $limitnum) {
		updatecache_cate();
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=categories&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
} elseif ($job == 'usernum') {
	$start = GetGP('start','G');
	if (!$start) {
		$db->update("UPDATE pw_user SET blogs='0',albums='0',photos='0',malbums='0',musics='0',comments='0',views='0',bookmarks='0',items='0' WHERE 1");
		$start = 0;
	}
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	/*
	$temparray = array();
	$query = $db->query("SELECT uid,type,COUNT(*) as count,SUM(replies) as comments,SUM(hits) as views FROM pw_items WHERE ifcheck='1' AND ifhide='0' GROUP BY uid,type LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$temparray[$rt['uid']][$rt['type'].'s'] = $rt['count'];
		$temparray[$rt['uid']]['items'] += $rt['count'];
		$temparray[$rt['uid']]['comments'] += $rt['comments'];
		$temparray[$rt['uid']]['views'] += $rt['views'];
		$times++;
	}
	$db->free_result($query);
	foreach ($temparray as $key => $value) {
		$updatesql = '';
		foreach ($value as $k => $v) {
			$updatesql .= ($updatesql ? ',' : '')."$k='$v'";
		}
		$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
	}
	unset($temparray);
	*/
	$query = $db->query("SELECT uid FROM pw_user LIMIT $start,$limitnum");
	while($userdb = $db->fetch_array($query)){
		$uid = $userdb[uid];
		$blogdb = $db->get_one("SELECT COUNT(*) as blogs,SUM(replies) as comments,SUM(hits) as views FROM pw_items WHERE uid='$uid' AND type='blog'");
		$albumdb = $db->get_one("SELECT COUNT(*) as albums,SUM(photos) as photos,SUM(replies) as comments,SUM(hits) as views FROM pw_albums WHERE uid='$uid'");
		$malbumdb = $db->get_one("SELECT COUNT(*) as malbums,SUM(musics) as musics,SUM(replies) as comments,SUM(hits) as views FROM pw_malbums WHERE uid='$uid'");
		$bookmarkdb = $db->get_one("SELECT COUNT(*) as bookmarks,SUM(hits) as views FROM pw_items WHERE uid='$uid' AND type='bookmark'");
		$blogs = $blogdb['blogs'];
		$albums = $albumdb['albums'];
		$photos = $albumdb['photos'];
		$malbums = $malbumdb['malbums'];
		$musics = $malbumdb['musics'];
		$bookmarks = $bookmarkdb['bookmarks'];
		$comments = $blogdb['comments'] + $albumdb['comments'] + $malbumdb['comments'];
		$views = $blogdb['views'] + $albumdb['views'] + $malbumdb['views'] + $bookmarkdb['views'];
		$items = $blogs + $albums + $malbums + $bookmarks;
		$db->update("UPDATE pw_user SET blogs='$blogs',albums='$albums',photos='$photos',malbums='$malbums',musics='$musics',comments='$comments',views='$views',bookmarks='$bookmarks',items='$items' WHERE uid='$uid'");
		$time++;
	}
	$db->free_result($query);
	if ($times < $limitnum) {
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=usernum&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
} elseif ($job == 'blogcmtnum') {
	$start = $_GET['start'];
	if (!$start) {
		$db->update("UPDATE pw_items SET replies='0',lastreplies='0',cmttext='' WHERE 1");
		$start = 0;
	}
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	!$db_perpage && $db_perpage = 30;
	$itemdb = array();
	$query = $db->query("SELECT c.id,c.itemid,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,c.replydate,c.reply,i.lastreplies,i.cmttext,u.icon as picon FROM pw_comment c LEFT JOIN pw_items i ON c.itemid=i.itemid LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.type='blog' ORDER BY postdate DESC LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$itemdb[$rt['itemid']]['replies']++;
		$itemdb[$rt['itemid']]['lastreplies'] = $rt['postdate'] > $rt['lastreplies'] ? $rt['postdate'] : $rt['lastreplies'];
		if (count($rt['cmttext']) < $db_perpage) {
			$itemdb[$rt['itemid']]['cmttext'][] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content'],'replydate' => $rt['replydate'],'reply' => $rt['reply']);
		}
		$times++;
	}
	$db->free_result($query);
	foreach ($itemdb as $key => $value) {
		$value['cmttext'] = (array)$value['cmttext'];
		if (!empty($value['cmttext'])) {
			Strip_S($value['cmttext']);
			$value['cmttext'] = serialize($value['cmttext']);
		} else {
			$value['cmttext'] = '';
		}
		Add_S($value);
		$db->update("UPDATE pw_items SET replies=replies+'$value[replies]',lastreplies='$value[lastreplies]',cmttext='$value[cmttext]' WHERE itemid='$key'");
	}
	if ($times < $limitnum) {
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=blogcmtnum&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
}elseif ($job == 'photocmtnum'){
	$start = $_GET['start'];
	if (!$start) {
		$db->update("UPDATE pw_photo SET preplies='0',plastreplies='0',cmttext='' WHERE 1");
		$start = 0;
	}
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	!$db_perpage && $db_perpage = 30;
	$photodb = array();
	$query = $db->query("SELECT c.id,c.itemid,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,p.plastreplies,p.cmttext,u.icon as picon FROM pw_comment c LEFT JOIN pw_photo p ON c.itemid=p.pid LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.type='photo' ORDER BY postdate DESC LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$photodb[$rt['itemid']]['replies']++;
		$photodb[$rt['itemid']]['lastreplies'] = $rt['postdate'] > $rt['plastreplies'] ? $rt['postdate'] : $rt['plastreplies'];
		if (count($rt['cmttext']) < $db_perpage) {
			$photodb[$rt['itemid']]['cmttext'][] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content']);
		}
		$times++;
	}
	$db->free_result($query);
	foreach ($photodb as $key => $value) {
		$value['cmttext'] = (array)$value['cmttext'];
		if (!empty($value['cmttext'])) {
			Strip_S($value['cmttext']);
			$value['cmttext'] = serialize($value['cmttext']);
		} else {
			$value['cmttext'] = '';
		}
		Add_S($value);
		$db->update("UPDATE pw_photo SET preplies=preplies+'$value[replies]',plastreplies='$value[lastreplies]',cmttext='$value[cmttext]' WHERE pid='$key'");
	}
	if ($times < $limitnum) {
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=photocmtnum&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
}elseif ($job == 'musiccmtnum'){
	$start = $_GET['start'];
	if (!$start) {
		$db->update("UPDATE pw_malbums SET replies='0',lastreplies='0',cmttext='' WHERE 1");
		$start = 0;
	}
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	!$db_perpage && $db_perpage = 30;
	$malbumdb = array();
	$query = $db->query("SELECT c.id,c.itemid,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,ma.lastreplies,ma.cmttext,u.icon as picon FROM pw_comment c LEFT JOIN pw_malbums ma ON c.itemid=ma.maid LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.type='music' ORDER BY postdate DESC LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$malbumdb[$rt['itemid']]['replies']++;
		$malbumdb[$rt['itemid']]['lastreplies'] = $rt['postdate'] > $rt['lastreplies'] ? $rt['postdate'] : $rt['lastreplies'];
		if (count($rt['cmttext']) < $db_perpage) {
			$malbumdb[$rt['itemid']]['cmttext'][] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content']);
		}
		$times++;
	}
	$db->free_result($query);
	foreach ($malbumdb as $key => $value) {
		$value['cmttext'] = (array)$value['cmttext'];
		if (!empty($value['cmttext'])) {
			Strip_S($value['cmttext']);
			$value['cmttext'] = serialize($value['cmttext']);
		} else {
			$value['cmttext'] = '';
		}
		Add_S($value);
		$db->update("UPDATE pw_malbums SET replies=replies+'$value[replies]',lastreplies='$value[lastreplies]',cmttext='$value[cmttext]' WHERE maid='$key'");
	}
	if ($times < $limitnum) {
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=musiccmtnum&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
}elseif ($job == 'tagnum') {
	$start = GetGP('start','G');
	if (!$start) {
		$db->update("UPDATE pw_btags SET blognum='0',photonum='0',bookmarknum='0',musicnum='0',allnum='0' WHERE 1");
		$db->update("UPDATE pw_blog SET tags='' WHERE 1");
		$db->update("UPDATE pw_bookmark SET tags='' WHERE 1");
		$db->update("UPDATE pw_music SET tags='' WHERE 1");
		$db->update("UPDATE pw_photo SET tags='' WHERE 1");
		$start = 0;
	}
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	$temparray = $tempsarray = array();
	$query = $db->query("SELECT tagid,tagname,tagtype,COUNT(*) as count FROM pw_taginfo GROUP BY tagid,tagtype LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$taghash = substr(md5("{$db_hash}tags{$rt[tagname]}"),0,10);
		P_unlink(D_P."data/cache/tags_$taghash.php");
		$temparray[$rt['tagid']][$rt['tagtype'].'num'] = $rt['count'];
		$temparray[$rt['tagid']]['allnum'] += $rt['count'];
		$times++;
	}
	$query = $db->query("SELECT ti.itemid,ti.tagtype,t.tagname FROM pw_taginfo ti LEFT JOIN pw_btags t ON ti.tagid=t.tagid GROUP BY ti.itemid,t.tagname LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$tempsarray[$rt['itemid'].'_'.$rt['tagtype']][] = $rt['tagname'];
		$times++;
	}
	$db->free_result($query);
	foreach ($temparray as $key => $value) {
		$updatesql = '';
		foreach ($value as $k => $v) {
			$updatesql .= ($updatesql ? ',' : '')."$k='$v'";
		}
		$updatesql && $db->update("UPDATE pw_btags SET $updatesql WHERE tagid='$key'");
	}
	foreach ($tempsarray as $key => $value) {
		$vs = '';
		list($itemid,$tagtype) = explode('_',$key);
		foreach ($value as $k => $v) {
			$vs .= ($vs ? ',' : '')."$v";
		}
		$itemname = ($tagtype == 'photo' ? 'pid' :($tagtype == 'music' ? 'mid' : 'itemid'));
		$db->update("UPDATE pw_$tagtype SET tags='$vs' WHERE {$itemname}='$itemid'");
	}
	if ($times < $limitnum) {
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=tagnum&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
} elseif ($job == 'teamnum') {
	$start = GetGP('start','G');
	if (!$start) {
		$db->update("UPDATE pw_team SET blogs='0',bloggers='0' WHERE 1");
		$start = 0;
	}
	!$start && $start = 0;
	$times = 0;
	!$limitnum && $limitnum = 30;
	$query = $db->query("SELECT teamid,COUNT(*) as count FROM pw_tblog GROUP BY teamid LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$db->update("UPDATE pw_team SET blogs='$rt[count]' WHERE teamid='$rt[teamid]'");
		$times++;
	}
	$query = $db->query("SELECT teamid,COUNT(*) as count FROM pw_tuser GROUP BY teamid LIMIT $start,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$db->update("UPDATE pw_team SET bloggers='$rt[count]' WHERE teamid='$rt[teamid]'");
		$times++;
	}
	$db->free_result($query);
	if ($times < $limitnum) {
		adminmsg('operate_success');
	} else {
		$end = $start + $limitnum;
		$basename = "$basename&job=tagnum&start=$end&limitnum=$limitnum";
		adminmsg('updatecache_job');
	}
} else {
	include PrintEot('setcache');footer();
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