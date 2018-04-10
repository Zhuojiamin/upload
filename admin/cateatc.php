<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";

if ($set == 'edit' && $_POST['step']!=1) {
	$itemid = GetGP('itemid');
	(int)$itemid < 1 && adminmsg('illegal_request');
	((int)$admin_gid < 1) && $admin_gid = '2';
	if (file_exists(D_P."data/groupdb/group_$admin_gid.php")) {
		require_once(Pcv(D_P."data/groupdb/group_$admin_gid.php"));
	} else {
		require_once(D_P.'data/groupdb/group_1.php');
	}
	require_once(R_P.'mod/post_mod.php');
	$editor = 'wysiwyg';
	list($db_titlemax,$db_postmin,$db_postmax) = explode(',',$db_lenlimit);
	if($job == 'blog' || $job == 'bookmark'){
		$dirgpslt = '';
		$dirarray = $attdb = $tagdb = $absurldb = $songurldb = array();
		$atc = $db->get_one("SELECT im.*,i.cid,i.dirid,i.uid,i.author,i.icon,i.subject,i.allowreply,i.ifcheck,i.ifhide,i.uploads FROM pw_$job im LEFT JOIN pw_items i ON im.itemid = i.itemid WHERE im.itemid='$itemid'");
		$cid = $atc['cid'];
		$dirdb = getitemtype($job,$atc['uid']);
		foreach ($dirdb as $value) {
	 		$dirselected = ($atc['dirid']==$value['typeid']) ? ' SELECTED' : '';
			$dirgpslt .= "<option id=\"dirop$value[typeid]\" value=\"$value[typeid]\"$dirselected>$value[name]</option>";
			$dirarray[$value['typeid']] = array('name' => $value['name'],'vieworder' => $value['vieworder']);
		}
		unset($dirdb);
		$icon = explode(',',$atc['icon']);
		${'icon1_'.(int)$icon[0]} = ${'icon2_'.(int)$icon[1]} = ' CHECKED';
		$tdisplay = ' style="display:none;"';
		if ($atc['tags']) {
			$tagdb = explode(',',$atc['tags']);
			$tagdb[0] && $tdisplay = '';
		}
		${'allowreply_'.(int)$atc['allowreply']} = ${'ifhide_'.(int)$atc['ifhide']} = ' SELECTED';
		$html_CK = (int)$atc['ifsign'] < 2 ? '' : 'CHECKED';
		$ifsign_CK = ($atc['ifsign']==1 || $atc['ifsign']==3) ? 'CHECKED' : '';
		$atc_content = (strpos($atc['content'],$db_blogurl)!==false) ? str_replace(array('p_w_picpath','p_w_upload','<','>'),array($picpath,$attachpath,'&lt;','&gt;'),$atc['content']) : str_replace(array('<','>'),array('&lt;','&gt;'),$atc['content']);
		$atc['uploads'] && $attachdb = unserialize($atc['uploads']);
		!is_array($attachdb) && $attachdb = array();
		$_GROUP['attachext'] && $db_uploadfiletype = $_GROUP['attachext'];
		$_GROUP['uploadnum'] && $db_attachnum = $_GROUP['uploadnum'];
		$_GROUP['attachsize'] && $db_uploadmaxsize = $_GROUP['attachsize'];
		$atc['uploads'] && $uploaddb = unserialize($atc['uploads']);
	}elseif($job == 'photo'){
		$albumdb = $db->get_one("SELECT uid,cid,subject,allowreply,ifhide,descrip FROM pw_albums WHERE aid='$itemid'");
		!$albumdb && usermsg('modify_error');
		$atc_title = $albumdb['subject'];
		$atc_content  = $albumdb['descrip'];
		$cid = $albumdb['cid'];
		$html_CK = (int)$albumdb['ifsign'] < 2 ? '' : 'CHECKED';
		${'allowreply_'.(int)$albumdb['allowreply']} = ${'ifhide_'.(int)$albumdb['ifhide']} = ' SELECTED';
	}elseif($job == 'music'){
		$malbumdb = $db->get_one("SELECT uid,cid,subject,allowreply,hpageurl,descrip,pushlog FROM pw_malbums WHERE maid='$itemid'");
		!$malbumdb && usermsg('modify_error');
		$atc_title = $malbumdb['subject'];
		$atc_content  = $malbumdb['descrip'];
		$cid = $malbumdb['cid'];
		$hpageurl = $post_hpageurl = $malbumdb['hpageurl'];
		$hpagesrc = showhpageurl($malbumdb['hpageurl']);
		if (!preg_match('/^http(s)?:\/\//i',$hpageurl)) {
			$uploadface = 'upload';
			$hpageurl = '';
		}else{
			$uploadface = 'http';
		}
		${'allowreply_'.(int)$malbumdb['allowreply']} = ' CHECKED';	
	}
}
$categpslt = $selected = '';
if ($job != 'gbook' && $job != 'bookmark') {
	include_once(D_P."data/cache/forum_cache_$job.php");
	$catedb = ${strtoupper('_'.$job)};
	foreach ($catedb as $key => $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$selected = ($set == 'edit' && $cid && $value['cid'] == $cid) ? ' SELECTED' : '';
		$categpslt .= "<option value=\"$value[cid]\"$selected>$add $value[name]</option>";
	}
}
if ($set == 'list') {
	InitGP(array('page','atccid','author','ckauthor','keyword','ktype','ckkeyword','postdate1','postdate2','digest','topped','ifcheck','orderby','sc','perpage','username','ckusername'));
	$sql = $addpage = $pages = '';
	$atcdb = array();
	if ($job != 'gbook') {
		if (strlen($atccid) > 0 && (int)$atccid > -1) {
			$sql .= ($sql ? ' AND' : '')." i.cid='$atccid'";
			$addpage .= "atccid=$atccid&";
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
		if (strlen($digest) > 0 && (int)$digest > -1) {
			$sql .= ($sql ? ' AND' : '')." i.digest='$digest'";
			$addpage .= "digest=$digest&";
		}
		if (strlen($ifcheck) > 0 && (int)$ifcheck > -1) {
			$sql .= ($sql ? ' AND' : '')." i.ifcheck='$ifcheck'";
			$addpage .= "ifcheck=$ifcheck&";
		}
	} else {
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
	}
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
		$sql .= $sql ? ' AND' : '';
		$pre = 'im';
		if ($job == 'gbook') {
			$pre = 'i';
			$ktype = 'content';
		}
		if ($ktype != 'content') {
			$ktype = 'subject';
			$sql .= ' i.subject';
		} else {
			$sql .= " $pre.content";
		}
		if ($ckkeyword) {
			$sql .= "='$keyword'";
			$addpage .= 'ckkeyword=1&';
		} else {
			$sql .= " LIKE '%".str_replace('*','%',$keyword)."%'";
		}
		$addpage .= ($job == 'gbook' ? '' : "ktype=$ktype&")."keyword=$keyword&";
	}
	$where = $sql ? "WHERE $sql" : '';
	$orderby != 'uid' && $orderby != 'cid' && $orderby = 'postdate';
	$sc != 'asc' && $sc = 'desc';
	if ((int)$perpage < 1) {
		$perpage = $db_perpage ? $db_perpage : 30;
	}
	$addpage .= ($orderby == 'postdate' ? '' : "orderby=$orderby&")."sc=$sc&perpage=$perpage&";
	$orderby = " ORDER BY i.$orderby $sc";
	(int)$page<1 && $page = 1;
	$limit = 'LIMIT '.($page-1)*$perpage.",$perpage";
	if($job == 'blog' || $job == 'bookmark'){
		$feildslt = 'i.itemid,i.cid,i.subject,i.topped,i.digest,i.ifcheck,';
		$fromjoin = "pw_$job im LEFT JOIN pw_items i USING(itemid)";
	}elseif($job == 'photo'){
		$feildslt = 'i.aid AS itemid,i.cid,i.subject,i.topped,i.digest,i.ifcheck,';
		$fromjoin = "pw_albums i";
	}elseif($job == 'music'){
		$feildslt = 'i.maid AS itemid,i.cid,i.subject,i.topped,i.digest,i.ifcheck,';
		$fromjoin = "pw_malbums i";
	}elseif ($job == 'gbook') {
		$feildslt = 'i.id,i.content,im.username,';
		$fromjoin = 'pw_gbook i LEFT JOIN pw_user im USING(uid)';
	}
	$query = $db->query("SELECT {$feildslt}i.uid,i.author,i.postdate FROM $fromjoin $where $orderby $limit");
	while ($rt = $db->fetch_array($query)) {
		strlen($rt['cid']) > 0 && $rt['cate'] = $catedb[$rt['cid']]['name'];
		strlen($rt['digest']) > 0 && $rt['digest'] = $otherlang['digest_'.(int)$rt['digest']];
		strlen($rt['author']) < 1 && $rt['author'] = 'guest';
		strlen($rt['author']) > 0 && $rt['author'] = substrs($rt['author'],16);
		strlen($rt['content']) > 0 && $rt['content'] = substrs($rt['content'],50);
		$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
		$atcdb[] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM $fromjoin $where");
	if ($count > $perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$perpage,"$basename&set=list&$addpage");
	}
} elseif ($set == 'edit' && is_numeric($_POST['step'])) {
	if ($_POST['step']==1) {
		InitGP(array('selid','type','digest','atccid'),'P');
		$tids = '';
		!is_array($selid) && $selid = array();
		if (!empty($selid)) {
			foreach ($selid as $value) {
				if ((int)$value > 0) {
					$tids .= ($tids ? ',' : '')."'$value'";
				}
			}
		}
		!$tids && adminmsg('operate_error');
		$tids = strpos($tids,',')===false ? "=$tids" : " IN ($tids)";
		if ($type == 'delete') {
			$userdb = array();
			if ($job == 'blog') {
				$itemids = '';
				$catedb = $typedb = $typesdb = array();
				$query = $db->query("SELECT itemid,cid,type,uid,ifcheck FROM pw_items WHERE itemid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['ifcheck']) {
						$catedb[$rt['cid']]['num']++;
						$userdb[$rt['uid']]['items']++;
						$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
						$uid = $rt['uid'];
					} else {
						$uid = 0;
					}
					$typedb[$rt['type']][] = array($uid,$rt['itemid']);
				}
				if ($itemids) {
					$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
					$query = $db->query("SELECT uid FROM pw_comment WHERE ifcheck='1' AND itemid{$itemids}");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['comments']++;
					}
					$query = $db->query("SELECT tagid,tagtype,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid{$itemids} GROUP BY tagid");
					while ($rt = $db->fetch_array($query)) {
						if ($rt['tagid'] && $rt['tagtype']) {
							$tagnum = "{$rt[tagtype]}num";
							$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],$tagnum=$tagnum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
						}
					}
				}
				$query = $db->query("SELECT attachurl,ifthumb FROM pw_upload WHERE itemid{$tids}");
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
				foreach ($typedb as $key => $value) {
					foreach ($value as $k => $v) {
						$db->update("DELETE FROM pw_$key WHERE itemid = $v[1]");
						$v[0] > 0 && $userdb[$v[0]][$key.'s']++;
					}
					!in_array($key.'s',$typesdb) && $typesdb[] = $key.'s';
				}
				foreach ($userdb as $key => $value) {
					$updatesql = '';
					if ($value['items']) {
						$updatesql .= ($updatesql ? ',' : '')."items=items-'$value[items]'";
					}
					if ($value['comments']) {
						$updatesql .= ($updatesql ? ',' : '')."comments=comments-'$value[comments]'";
					}
					if (!empty($typesdb)) {
						foreach ($typesdb as $v) {
							if ($value[$v]) {
								$updatesql .= ($updatesql ? ',' : '')."$v=$v-'$value[$v]'";
							}
						}
					}
					$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
				}
				$db->update("DELETE FROM pw_carticle WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_collections WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_comment WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_footprint WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_items WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_taginfo WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_tblog WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_upload WHERE itemid{$tids}");
				updatecache_cate($job);
				update_bloginfo_cache('blogs');
			} elseif($job == 'photo'){
				$query = $db->query("SELECT aid,uid,cid,ifcheck FROM pw_albums WHERE aid{$tids}");
				$userdb = $typedb = $typesdb = array();
				while ($rt = $db->fetch_array($query)) {
					if ($rt['ifcheck']) {
						$catedb[$rt['cid']]['num']++;
						$userdb[$rt['uid']]['albums']++;
						$aids .= ($aids ? ',' : '')."'$rt[aid]'";
					}
				}
				if ($aids) {
					$aids = strpos($aids,',')===false ? "=$aids" : " IN ($aids)";
					$query = $db->query("SELECT c.uid FROM pw_comment c LEFT JOIN pw_photo p ON c.itemid=p.pid WHERE p.aid{$aids}");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['comments']++;
					}
					$query = $db->query("SELECT t.tagid,t.tagtype,COUNT(*) AS tagnum FROM pw_taginfo t LEFT JOIN pw_photo p ON t.itemid=p.pid WHERE p.aid{$aids} GROUP BY t.tagid");
					while ($rt = $db->fetch_array($query)) {
						if ($rt['tagid'] && $rt['tagtype']) {
							$tagnum = "{$rt[tagtype]}num";
							$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],$tagnum=$tagnum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
						}
					}
					$query = $db->query("SELECT uid,attachurl,ifthumb FROM pw_photo WHERE aid{$aids}");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['photos']++;
						P_unlink("$attachdir/$rt[attachurl]");
						if ($rt['ifthumb'] == 1) {
							$ext  = substr(strrchr($rt['attachurl'],'.'),1);
							$name = substr($rt['attachurl'],0,strrpos($rt['attachurl'],'.'));
							P_unlink(R_P."$attachdir/{$name}_thumb.{$ext}");
						}
					}
					$db->free_result($query);
					foreach ($catedb as $key => $value) {
						$db->update("UPDATE pw_categories SET counts=counts-'$value[num]' WHERE cid='$key'");
					}
					foreach ($userdb as $key => $value) {
						$updatesql = '';
						if ((int)$value['albums'] > 0) {
							$updatesql .= ($updatesql ? ',' : '')."albums=albums-'$value[albums]',items=items-'$value[albums]'";
						}
						if ((int)$value['comments'] > 0) {
							$updatesql .= ($updatesql ? ',' : '')."comments=comments-'$value[comments]'";
						}
						if((int)$value['photos']>0) {
							$updatesql .= ($updatesql ? ',' : '')."photos=photos-'$value[photos]'";
						}
						if (!empty($typesdb)) {
							foreach ($typesdb as $v) {
								if ($value[$v]) {
									$updatesql .= ($updatesql ? ',' : '')."$v=$v-'$value[$v]'";
								}
							}
						}
						$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
					}
					$query = $db->query("SELECT pid FROM pw_photo WHERE aid{$aids}");
					while($rt = $db->fetch_array($query)){
						if ($rt['pid']) {
						$pids .= ($pids ? ',' : '')."'$rt[$pids]'";
						}
					}
					$pids = strpos($pids,',')===false ? "=$pids" : " IN ($pids)";
				}
				$db->update("DELETE FROM pw_carticle WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_collections WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_comment WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_footprint WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_albums WHERE aid{$tids}");
				$db->update("DELETE FROM pw_photo WHERE aid{$tids}");
				$db->update("DELETE FROM pw_taginfo WHERE itemid{$tids}");
				foreach($userdb as $key => $value){
					update_albumdb($key);
				}
				updatecache_cate($type);
				update_bloginfo_cache('albums');
			} elseif($job == 'music'){
				$query = $db->query("SELECT maid,uid,cid,ifcheck FROM pw_malbums WHERE maid{$tids}");
				$userdb = $typedb = $typesdb = array();
				while ($rt = $db->fetch_array($query)) {
					if ($rt['ifcheck']) {
						$catedb[$rt['cid']]['num']++;
						$userdb[$rt['uid']]['malbums']++;
						$maids .= ($maids ? ',' : '')."'$rt[maid]'";
					}
				}
				if ($aids) {
					$maids = strpos($maids,',')===false ? "=$maids" : " IN ($maids)";
					$query = $db->query("SELECT c.uid FROM pw_comment c LEFT JOIN pw_malbums ma ON c.itemid=ma.maid WHERE ma.maid{$maids}");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['comments']++;
					}
					$query = $db->query("SELECT t.tagid,t.tagtype,COUNT(*) AS tagnum FROM pw_taginfo t LEFT JOIN pw_music m ON t.itemid=m.mid WHERE m.maid{$maids} GROUP BY t.tagid");
					while ($rt = $db->fetch_array($query)) {
						if ($rt['tagid'] && $rt['tagtype']) {
							$tagnum = "{$rt[tagtype]}num";
							$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],$tagnum=$tagnum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
						}
					}
					$query = $db->query("SELECT uid FROM pw_music WHERE maid{$maids}");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['musics']++;
					}
					$db->free_result($query);
					foreach ($catedb as $key => $value) {
						$db->update("UPDATE pw_categories SET counts=counts-'$value[num]' WHERE cid='$key'");
					}
					foreach ($userdb as $key => $value) {
						$updatesql = '';
						if ((int)$value['malbums'] > 0) {
							$updatesql .= ($updatesql ? ',' : '')."malbums=malbums-'$value[malbums]',items=items-'$value[malbums]'";
						}
						if ((int)$value['comments'] > 0) {
							$updatesql .= ($updatesql ? ',' : '')."comments=comments-'$value[comments]'";
						}
						if((int)$value['musics']>0) {
							$updatesql .= ($updatesql ? ',' : '')."musics=musics-'$value[musics]'";
						}
						if (!empty($typesdb)) {
							foreach ($typesdb as $v) {
								if ($value[$v]) {
									$updatesql .= ($updatesql ? ',' : '')."$v=$v-'$value[$v]'";
								}
							}
						}
						$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
					}
					$query = $db->query("SELECT mid FROM pw_music WHERE maid{$maids}");
					while($rt = $db->fetch_array($query)){
						if ($rt['mid']) {
						$mids .= ($mids ? ',' : '')."'$rt[$mids]'";
						}
					}
					$mids = strpos($mids,',')===false ? "=$mids" : " IN ($mids)";
				}
				$db->update("DELETE FROM pw_carticle WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_collections WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_comment WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_footprint WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_malbums WHERE maid{$tids}");
				$db->update("DELETE FROM pw_music WHERE maid{$tids}");
				$db->update("DELETE FROM pw_taginfo WHERE itemid{$tids}");
				foreach($userdb as $key => $value){
					update_malbumdb($key);
				}
				updatecache_cate($type);
				update_bloginfo_cache('malbums');
			} elseif($job == 'gbook') {
				$query = $db->query("SELECT uid FROM pw_gbook WHERE id{$tids}");
				while ($rt = $db->fetch_array($query)) {
					$userdb[$rt['uid']]['msgs']++;
				}
				$db->free_result($query);
				foreach ($userdb as $key => $value) {
					$db->update("UPDATE pw_user SET msgs=msgs-'$value[msgs]' WHERE uid='$key'");
				}
				$db->update("DELETE FROM pw_gbook WHERE id{$tids}");
			} elseif ($job == 'bookmark'){
				$itemids = '';
				$catedb = $typedb = $typesdb = array();
				$query = $db->query("SELECT itemid,cid,type,uid,ifcheck FROM pw_items WHERE itemid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['ifcheck']) {
						$userdb[$rt['uid']]['items']++;
						$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
					}
				}
				if ($itemids) {
					$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
				}
				foreach ($userdb as $key => $value) {
					$updatesql = '';
					if ((int)$value['items'] > 0) {
						$updatesql .= ($updatesql ? ',' : '')."bookmarks=bookmarks-'$value[items]',items=items-'$value[items]'";
					}
					$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
				}
				$db->update("DELETE FROM pw_items WHERE itemid{$tids}");
				$db->update("DELETE FROM pw_bookmark WHERE itemid{$tids}");
			}
		} elseif ($type == 'allowcheck') {
			if($job == 'blog'){
				$itemids = '';
				$catedb = $userdb = $typedb = $typesdb = array();
				$query = $db->query("SELECT itemid,cid,uid,type,ifcheck FROM pw_items WHERE itemid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if (!$rt['ifcheck']) {
						$catedb[$rt['cid']]['num']++;
						$userdb[$rt['uid']]['items']++;
						$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
						$typedb[$rt['type']][] = array($rt['uid'],$rt['itemid']);
					}
				}
				if ($itemids) {
					$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
					$query = $db->query("SELECT uid FROM pw_comment WHERE ifcheck='1' AND itemid{$itemids} AND type='$job'");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['comments']++;
					}
					$query = $db->query("SELECT tagid,tagtype,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid{$itemids}  AND tagtype='$job' GROUP BY tagid");
					while ($rt = $db->fetch_array($query)) {
						if ($rt['tagid'] && $rt['tagtype']) {
							$tagnum = "{$rt[tagtype]}num";
							$db->update("UPDATE pw_btags SET allnum=allnum+$rt[tagnum],$tagnum=$tagnum+$rt[tagnum] WHERE tagid='$rt[tagid]'");
						}
					}
				}
				$db->free_result($query);
				foreach ($catedb as $key => $value) {
					$db->update("UPDATE pw_categories SET counts=counts+'$value[num]' WHERE cid='$key'");
				}
				foreach ($typedb as $key => $value) {
					foreach ($value as $k => $v) {
						$userdb[$v[0]][$key.'s']++;
					}
					!in_array($key.'s',$typesdb) && $typesdb[] = $key.'s';
				}
				foreach ($userdb as $key => $value) {
					$updatesql = '';
					if ($value['items']) {
						$updatesql .= ($updatesql ? ',' : '')."items=items+'$value[items]'";
					}
					if ($value['comments']) {
						$updatesql .= ($updatesql ? ',' : '')."comments=comments+'$value[comments]'";
					}
					if (!empty($typesdb)) {
						foreach ($typesdb as $v) {
							if ($value[$v]) {
								$updatesql .= ($updatesql ? ',' : '')."$v=$v+'$value[$v]'";
							}
						}
					}
					$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
				}
				$db->update("UPDATE pw_items SET ifcheck='1' WHERE itemid{$itemids}");
				updatecache_cate($job);
			}elseif($job == 'photo'){
				$pids = '';$aids = '';
				$catedb = $userdb = $typedb = $typesdb = array();
				$query = $db->query("SELECT aid,cid,uid,ifcheck,photos FROM pw_albums WHERE aid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if (!$rt['ifcheck']) {
						$catedb[$rt['cid']]['num']++;
						$userdb[$rt['uid']]['aids']++;
						$userdb[$rt['uid']]['pids'] += $rt[photos];
						$aids .= ($aids ? ',' : '')."'$rt[aid]'";
						$query2 = $db->query("SELECT pid FROM pw_photo WHERE aid='$rt[aid]'");
						while($rt2 = $db->fetch_array($query2)){
							$pids .= ($pids ? ',' : '')."'$rt2[pid]'";
						}
						$typedb['photo'][] = array($rt['uid'],$rt['aid']);
					}
				}
				if ($pids) {
					$pids = strpos($pids,',')===false ? "=$pids" : " IN ($pids)";
					$query = $db->query("SELECT uid FROM pw_comment WHERE ifcheck='1' AND itemid{$pids} AND type='photo'");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['comments']++;
					}
					$query = $db->query("SELECT tagid,tagtype,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid{$pids} AND tagtype='photo' GROUP BY tagid");
					while ($rt = $db->fetch_array($query)) {
						if ($rt['tagid'] && $rt['tagtype']) {
							$db->update("UPDATE pw_btags SET allnum=allnum+$rt[tagnum],$tagnum=photonum+$rt[tagnum] WHERE tagid='$rt[tagid]'");
						}
					}
				}
				$db->free_result($query);
				foreach ($catedb as $key => $value) {
					$db->update("UPDATE pw_categories SET counts=counts+'$value[num]' WHERE cid='$key'");
				}
				foreach ($userdb as $key => $value) {
					$updatesql = '';
					if ($value['aids']) {
						$updatesql .= ($updatesql ? ',' : '')."albums=albums+'$value[aids]'";
					}
					if ($value['comments']) {
						$updatesql .= ($updatesql ? ',' : '')."comments=comments+'$value[comments]'";
					}
					if ($value['pids']) {
						$updatesql .= ($updatesql ? ',' : '')."photos=photos+'$value[pids]'";
					}
					$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
				}
				$aids = strpos($aids,',')===false ? "=$aids" : " IN ($aids)";
				$db->update("UPDATE pw_albums SET ifcheck='1' WHERE aid{$aids}");
				foreach($userdb as $key => $value){
					update_albumdb($key);
				}
				updatecache_cate($job);
			}elseif($job == 'music'){
				$mids = '';$maids = '';
				$catedb = $userdb = $typedb = $typesdb = array();
				$query = $db->query("SELECT maid,cid,uid,ifcheck,musics FROM pw_malbums WHERE maid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if (!$rt['ifcheck']) {
						$catedb[$rt['cid']]['num']++;
						$userdb[$rt['uid']]['maids']++;
						$userdb[$rt['uid']]['mids'] += $rt[musics];
						$maids .= ($maids ? ',' : '')."'$rt[maid]'";
						$query2 = $db->query("SELECT mid FROM pw_music WHERE maid='$rt[maid]'");
						while($rt2 = $db->fetch_array($query2)){
							$mids .= ($mids ? ',' : '')."'$rt2[mid]'";
						}
					}
				}
				if ($maids) {
					$maids = strpos($maids,',')===false ? "=$maids" : " IN ($maids)";
					$query = $db->query("SELECT uid FROM pw_comment WHERE ifcheck='1' AND itemid{$maids} AND type='music'");
					while ($rt = $db->fetch_array($query)) {
						$userdb[$rt['uid']]['comments']++;
					}
				}
				if ($mids) {
					$query = $db->query("SELECT tagid,tagtype,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid{$mids} AND tagtype='music' GROUP BY tagid");
					while ($rt = $db->fetch_array($query)) {
						if ($rt['tagid'] && $rt['tagtype']) {
							$db->update("UPDATE pw_btags SET allnum=allnum+$rt[tagnum],$tagnum=musicnum+$rt[tagnum] WHERE tagid='$rt[tagid]'");
						}
					}
				}
				$db->free_result($query);
				foreach ($catedb as $key => $value) {
					$db->update("UPDATE pw_categories SET counts=counts+'$value[num]' WHERE cid='$key'");
				}
				foreach ($userdb as $key => $value) {
					$updatesql = '';
					if ($value['maids']) {
						$updatesql .= ($updatesql ? ',' : '')."malbums=malbums+'$value[maids]'";
					}
					if ($value['comments']) {
						$updatesql .= ($updatesql ? ',' : '')."comments=comments+'$value[comments]'";
					}
					if ($value['mids']) {
						$updatesql .= ($updatesql ? ',' : '')."musics=musics+'$value[mids]'";
					}
					$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
				}
				$db->update("UPDATE pw_malbums SET ifcheck='1' WHERE maid{$maids}");
				foreach($userdb as $key => $value){
					update_malbumdb($key);
				}
				updatecache_cate($job);
			}elseif($job == 'bookmark'){
				$itemids = '';
				$catedb = $userdb = $typedb = $typesdb = array();
				$query = $db->query("SELECT itemid,uid,type,ifcheck FROM pw_items WHERE itemid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if (!$rt['ifcheck']) {
						$userdb[$rt['uid']]['items']++;
						$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
						$typedb[$rt['type']][] = array($rt['uid'],$rt['itemid']);
					}
				}
				if ($itemids) {
					$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
				}
				$db->free_result($query);
				foreach ($typedb as $key => $value) {
					foreach ($value as $k => $v) {
						$userdb[$v[0]][$key.'s']++;
					}
					!in_array($key.'s',$typesdb) && $typesdb[] = $key.'s';
				}
				foreach ($userdb as $key => $value) {
					$updatesql = '';
					if ($value['items']) {
						$updatesql .= ($updatesql ? ',' : '')."items=items+'$value[items]'";
					}
					if (!empty($typesdb)) {
						foreach ($typesdb as $v) {
							if ($value[$v]) {
								$updatesql .= ($updatesql ? ',' : '')."$v=$v+'$value[$v]'";
							}
						}
					}
					$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
				}
				$db->update("UPDATE pw_items SET ifcheck='1' WHERE itemid{$itemids}");
			}
		} elseif ($type == 'setdigest') {
			if($job == 'blog' || $job == 'bookmark'){
				$db->update("UPDATE pw_items SET digest='".(int)$digest."' WHERE itemid{$tids}");
			}elseif($job == 'photo'){
				$db->update("UPDATE pw_albums SET digest='".(int)$digest."' WHERE aid{$tids}");
			}elseif($job == 'music'){
				$db->update("UPDATE pw_malbums SET digest='".(int)$digest."' WHERE maid{$tids}");
			}
		} elseif ($type == 'setcid') {
			$catedb = array();
			if($job == 'blog'){
				$itemids = '';
				$query = $db->query("SELECT itemid,cid,ifcheck FROM pw_items WHERE itemid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['ifcheck'] && $rt['cid'] != $atccid) {
						$catedb[$rt['cid']]++;
						$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
					}
				}
				$db->free_result($query);
				if ($itemids) {
					$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
					foreach ($catedb as $key => $value) {
						$db->update("UPDATE pw_categories SET counts=counts-'$value' WHERE cid='$key'");
					}
					$db->update("UPDATE pw_items SET cid='".(int)$atccid."' WHERE itemid{$itemids}");
					updatecache_cate($job);
				}
			}elseif($job == 'photo'){
				$aids = '';
				$query = $db->query("SELECT aid,cid,ifcheck FROM pw_albums WHERE aid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['ifcheck'] && $rt['cid'] != $atccid) {
						$catedb[$rt['cid']]++;
						$aids .= ($aids ? ',' : '')."'$rt[aid]'";
					}
				}
				$db->free_result($query);
				if ($aids) {
					$aids = strpos($aids,',')===false ? "=$aids" : " IN ($aids)";
					foreach ($catedb as $key => $value) {
						$db->update("UPDATE pw_categories SET counts=counts-'$value' WHERE cid='$key'");
					}
					$db->update("UPDATE pw_albums SET cid='".(int)$atccid."' WHERE aid{$aids}");
					updatecache_cate($job);
				}
			}elseif($job == 'music'){
				$maids = '';
				$query = $db->query("SELECT maid,cid,ifcheck FROM pw_malbums WHERE maid{$tids}");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['ifcheck'] && $rt['cid'] != $atccid) {
						$catedb[$rt['cid']]++;
						$maids .= ($maids ? ',' : '')."'$rt[maid]'";
					}
				}
				$db->free_result($query);
				if ($maids) {
					$maids = strpos($maids,',')===false ? "=$maids" : " IN ($maids)";
					foreach ($catedb as $key => $value) {
						$db->update("UPDATE pw_categories SET counts=counts-'$value' WHERE cid='$key'");
					}
					$db->update("UPDATE pw_malbums SET cid='".(int)$atccid."' WHERE maid{$maids}");
					updatecache_cate($job);
				}
			}
		}
		adminmsg('operate_success');
	} elseif ($_POST['step']==2) {
		require_once(R_P.'mod/windcode.php');
		require_once(R_P.'mod/upload_mod.php');
		InitGP(array('atc_cid','atc_oldcid','atc_dirid','atc_iconid1','atc_iconid2','atc_allowreply','atc_ifhide','atc_tagdb'),'P');
		$atc_iconid = $atc_iconid1.','.$atc_iconid2;
		$attachdb = $_POST['attachdb'];
		list($atc_title,$atc_content) = ConCheck($_POST['atc_title'],$_POST['atc_content']);
		$atc_usesign = $_POST['atc_ifsign'] ? 1 : 0;
		($_GROUP['htmlcode'] && $_POST['atc_htmlcode']) && $atc_usesign += 2;
		$atc_content = Atc_cv($atc_content,$atc_usesign);
		$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
		$updatefeile = '';
		$atc_cid = (int)$atc_cid;
	 	$atc_oldcid = (int)$atc_oldcid;
	 	$atc_allowreply = (int)$atc_allowreply;
	 	$atc_ifhide = (int)$atc_ifhide;
	 	if ($atc_oldcid != $atc_cid && $atc['ifcheck']) {
	 		$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$atc_oldcid'");
	 		$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$atc_cid'");
	 		updatecache_cate($job);
	 	}
		if($job == 'blog' || $job == 'bookmark'){
			!is_array($atc_tagdb) && $atc_tagdb = array();
			$tagdb = array();
			foreach ($atc_tagdb as $key => $value) {
	 			(int)$key > -1 && $value && !is_array($value) && $tagdb[$key] = $value;
	 		}
	 		unset($atc_tagdb);
	 		!empty($tagdb) && $atc_tagdb = implode(',',array_unique($tagdb));
	 		$db->update("UPDATE pw_items SET cid='$atc_cid',dirid='$atc_dirid',icon='$atc_iconid',subject='$atc_title',allowreply='$atc_allowreply',ifhide='$atc_ifhide' WHERE itemid='$itemid'");
	 		$db->update("UPDATE pw_$job SET tags='$atc_tagdb',ifsign='$atc_usesign'{$updatefeile},ifconvert='$ifconvert',content='$atc_content' WHERE itemid='$itemid'");
	 		UploadSQL($atc['uid'],$itemid,$atc_cid,$job);
		}elseif ($job == 'photo') {
			$db->update("UPDATE pw_albums SET cid='$atc_cid',subject='$atc_title',lastpost='$timestamp',allowreply='$atc_allowreply',descrip='$atc_content',ifconvert='$ifconvert',ifhide='$atc_ifhide' WHERE aid='$itemid'");
	 	} elseif ($job == 'music') {
			InitGP(array('post_hpageurl','attachment_1','ckulface'));
			$hpageurl = $post_hpageurl;
			if ($ckulface != 'upload') {
				$attachment_1 && !preg_match('/^http(s)?:\/\//i',$attachment_1) && usermsg('face_fail');
				$hpageurl = $attachment_1;
			}elseif (!empty($_FILES['attachment_1']['tmp_name'])) {
				$hpageurl && !preg_match('/^http(s)?:\/\//i',$hpageurl) && P_unlink("$attachdir/$hpageurl");
				$_GROUP['attachsize'] && $db_uploadmaxsize = $_GROUP['attachsize'];
				$_GROUP['uploadnum'] && $db_attachnum = $_GROUP['uploadnum'];
				$db_uploadfiletype = 'gif jpg jepg png';
				$uploaddb = UploadFile($admin_uid,1);
				$hpageurl = $uploaddb[0]['attachurl'];
				$hpageurldb = GetImgSize("$attachdir/$hpageurl");
				if ($hpageurldb['width'] > 100 || $hpageurldb['height'] > 100) {
					P_unlink("$attachdir/$hpageurl");
					if ($uploaddb[0]['ifthumb'] == 1) {
						$ext  = substr(strrchr($hpageurl,'.'),1);
						$name = substr($hpageurl,0,strrpos($hpageurl,'.'));
						P_unlink("$attachdir/{$name}_thumb.{$ext}");
					}
					usermsg('pro_size_limit');
				}
			}
			$db->update("UPDATE pw_malbums SET cid='$atc_cid',subject='$atc_title',lastpost='$timestamp',allowreply='$atc_allowreply',hpageurl='$hpageurl',ifconvert='$ifconvert',descrip='$atc_content' WHERE maid='$itemid'");
		 } elseif ($job == 'bookmark') {
	 		
	 	}
	}
	adminmsg('operate_success');
} else {
	$postdate1 = '2004-01-01';
	$postdate2 = get_date($timestamp+24*3600,'Y-m-d');
}
include PrintEot('cateatc');footer();
function PwStrtoTime($date){
	global $db_timedf;
	return function_exists('date_default_timezone_set') ? strtotime($date) - $db_timedf*3600 : strtotime($date);
}
function getitemtype($type,$uid = null){
	global $db,$winduid;
	empty($uid) && $uid = $winduid;
	$items = array();
	$query = $db->query("SELECT typeid,name,vieworder FROM pw_itemtype WHERE uid='$uid' AND type='$type' ORDER BY vieworder ASC");
	while ($rt = $db->fetch_array($query)) {
		$rt['name'] = preg_replace('/\<(.+?)\>/is','',$rt['name']);
		$rt['vieworder'] = (int)$rt['vieworder'];
		$items[] = $rt;
	}
	return $items;
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

function update_albumdb($uid){
	global $db;
	$cachedb = array();
	(int)$albums = 0;
	$query = $db->query("SELECT a.aid,a.subject,a.descrip,a.ifhide,p.ifthumb,p.attachurl AS hpageurl FROM pw_albums a LEFT JOIN pw_photo p ON a.hpagepid=p.pid WHERE a.uid='$uid' AND ifcheck='1' ORDER BY postdate DESC");
	while ($rt = $db->fetch_array($query)) {
		empty($rt[hpageurl]) && $rt[hpageurl] = 'nopic.jpg';
		$cachedb[$rt['aid']] = $rt;
		$albums++;
	}
	if (!empty($cachedb)) {
		Strip_S($cachedb);
		$cachedb = addslashes(serialize($cachedb));
	}
	$itemdb = $db->get_one("SELECT blogs,malbums,bookmarks FROM pw_user WHERE uid='$uid'");
	$items = (int)$itemdb['blogs'] + (int)$itemdb['malbums'] + (int)$itemdb['bookmarks'] + $albums;
	$db->update("UPDATE pw_userinfo SET albumdb='$cachedb' WHERE uid='$uid'");
	$db->update("UPDATE pw_user SET albums='$albums',items='$items' WHERE uid='$uid'");
}

function update_malbumdb($uid){
	global $db;
	$cachedb = array();
	(int)$malbums = 0;
	$query = $db->query("SELECT maid,subject,descrip,ifcheck,allowreply FROM pw_malbums WHERE uid='$uid' AND ifcheck='1' ORDER BY postdate DESC");
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



function showhpageurl($hpageurl){
	global $attachpath,$imgpath;
	if (!$hpageurl) {
		return $imgpath.'/nopic.jpg';
	} elseif (preg_match('/^http/i',$hpageurl)) {
		return $hpageurl;
	} else {
		return $attachpath.'/'.$hpageurl;
	}
}
?>