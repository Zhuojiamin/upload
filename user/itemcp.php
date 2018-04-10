<?php
!function_exists('usermsg') && exit('Forbidden');

$basename .= '&type='.$type;
!N_InArray($type,array('blog','bookmark','file','goods','music','photo')) && usermsg('undefined_action');
$articleurl = $db_articleurl ? 'article.php?' : 'blog.php?do=showone&';
if ($job == 'update') {
	InitGP(array('selid','ntype','atccid','atcdirid','maid'),'P');
	$tids = '';
	!is_array($selid) && $selid = array();
	foreach ($selid as $value) {
		if ((int)$value > 0) {
			$tids .= ($tids ? ',' : '')."'$value'";
		}
	}
	!$tids && usermsg('operate_error');
	$tids = strpos($tids,',')===false ? "=$tids" : " IN ($tids)";
	if($type == 'blog'){
		$query = $db->query("SELECT itemid,cid,dirid,uid,ifcheck FROM pw_items WHERE itemid{$tids}");
		$catedb = array();
		if ($ntype == 'delete') {
			$itemids = '';
			$userdb = $typedb = $typesdb = array();
			while ($rt = $db->fetch_array($query)) {
				if ($rt['ifcheck']) {
					$catedb[$rt['cid']]['num']++;
					$userdb[$rt['uid']]['items']++;
					$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
				}
			}
			if ($itemids) {
				$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
				$query = $db->query("SELECT uid FROM pw_comment WHERE itemid{$itemids}");
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
				if ((int)$value['items'] > 0) {
					$updatesql .= ($updatesql ? ',' : '')."{$type}s={$type}s-'$value[items]',items=items-'$value[items]'";
				}
				if ((int)$value['comments'] > 0) {
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
			$db->update("DELETE FROM pw_$type WHERE itemid{$tids}");
			$db->update("DELETE FROM pw_taginfo WHERE itemid{$tids}");
			$db->update("DELETE FROM pw_tblog WHERE itemid{$tids}");
			$db->update("DELETE FROM pw_upload WHERE itemid{$tids}");
			updatecache_cate($type);
			update_bloginfo_cache('blogs');
		} elseif ($ntype == 'setcid') {
			while ($rt = $db->fetch_array($query)) {
				if ($rt['ifcheck'] && $rt['cid'] != $atccid) {
					$catedb[$rt['cid']]++;
					$itemcounts++;
					$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
				}
			}
			$db->free_result($query);
			if ($itemids) {
				$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
				foreach ($catedb as $key => $value) {
					$db->update("UPDATE pw_categories SET counts=counts-'$value' WHERE cid='$key'");
				}
				$db->update("UPDATE pw_categories SET counts=counts+'$itemcounts' WHERE cid='".(int)$atccid."'");
				$db->update("UPDATE pw_items SET cid='".(int)$atccid."' WHERE itemid{$itemids}");
				updatecache_cate($type);
			}
		} elseif ($ntype == 'setdirid'){
			while ($rt = $db->fetch_array($query)) {
				if ($rt['ifcheck'] && $rt['dirid'] != $atcdirid) {
					$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
				}
			}
			$db->free_result($query);
			if ($itemids) {
				$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
				$db->update("UPDATE pw_items SET dirid='".(int)$atcdirid."' WHERE itemid{$itemids}");
			}
		}
		usermsg('operate_success',"$basename");
	}elseif($type == 'photo'){
		$query = $db->query("SELECT aid,uid,cid,ifcheck FROM pw_albums WHERE aid{$tids}");
		if ($ntype == 'delete') {
			$dirids = '';
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
			update_albumdb($admin_uid);
			updatecache_cate($type);
			update_bloginfo_cache('albums');
		}elseif($ntype == 'setcid'){
			while ($rt = $db->fetch_array($query)) {
				if ($rt['ifcheck'] && $rt['cid'] != $atccid) {
					$catedb[$rt['cid']]++;
					$itemcounts++;
					$aids .= ($aids ? ',' : '')."'$rt[aid]'";
				}
			}
			$db->free_result($query);
			if ($aids) {
				$aids = strpos($aids,',')===false ? "=$aids" : " IN ($aids)";
				foreach ($catedb as $key => $value) {
					$db->update("UPDATE pw_categories SET counts=counts-'$value' WHERE cid='$key'");
				}
				$db->update("UPDATE pw_categories SET counts=counts+'$itemcounts' WHERE cid='".(int)$atccid."'");
				$db->update("UPDATE pw_albums SET cid='".(int)$atccid."' WHERE aid{$aids}");
				updatecache_cate($type);
			}
		}
		usermsg('operate_success',"$basename");
	}elseif($type == 'music'){
		if($ntype == 'delete'){
			if($tids){
				$musicnum = $db->get_value("SELECT COUNT(*) AS num FROM pw_music WHERE mid{$tids}");
				$db->update("UPDATE pw_user SET musics=musics-$musicnum");
				$query = $db->query("SELECT tagid,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid{$tids} AND tagtype='music' GROUP BY tagid");
				while ($rt = $db->fetch_array($query)) {
					if ($rt['tagid']) {
						$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],musicnum=musicnum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
					}
				}
				$db->update("DELETE FROM pw_music WHERE mid{$tids}");
			}
		}
		usermsg('operate_success',"$basename&job=list&maid=$maid&");
	}elseif($type == 'bookmark'){
		$query = $db->query("SELECT itemid,dirid,uid,ifcheck FROM pw_items WHERE itemid{$tids}");
		if ($ntype == 'delete') {
			$itemids = '';
			$userdb = $typedb = $typesdb = array();
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
					$updatesql .= ($updatesql ? ',' : '')."{$type}s={$type}s-'$value[items]',items=items-'$value[items]'";
				}
				$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
			}
			$db->update("DELETE FROM pw_items WHERE itemid{$tids}");
			$db->update("DELETE FROM pw_$type WHERE itemid{$tids}");
		} elseif ($ntype == 'setdirid'){
			while ($rt = $db->fetch_array($query)) {
				if ($rt['ifcheck'] && $rt['dirid'] != $atcdirid) {
					$itemids .= ($itemids ? ',' : '')."'$rt[itemid]'";
				}
			}
			$db->free_result($query);
			if ($itemids) {
				$itemids = strpos($itemids,',')===false ? "=$itemids" : " IN ($itemids)";
				$db->update("UPDATE pw_items SET dirid='".(int)$atcdirid."' WHERE itemid{$itemids}");
			}
		}
		usermsg('operate_success',"$basename");
	}
}elseif($job == 'list'){
	if($type == 'photo'){
		InitGP(array('aid','page'));
		$album_name_array = unserialize($admindb[albumdb]);
		$album_name = $album_name_array[$aid][subject];
		$album_descrip = $album_name_array[$aid][descrip];
		$db_perpage = 16;
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT * FROM pw_photo WHERE aid='$aid' AND uid='$admin_uid' ORDER BY uploadtime DESC $limit");
		while($photo = $db->fetch_array($query)){
			$photo['uploadtime'] = date('Y-m-d',$photo['uploadtime']);
			$photo['name'] = substrs($photo['name'],15);
			$photo['descrip'] = substrs($photo['descrip'],15);
			$photo['ifthumb'] && $photo['attachurl'] = str_replace('.','_thumb.',$photo['attachurl']);
				if(!$photo['attachurl']){
					$photo['attachurl'] = 'none.gif';
				}
				if (file_exists(R_P."$attachpath/$photo[attachurl]")) {
					$photo['attachurl'] = "$attachpath/$photo[attachurl]";
				} elseif (file_exists($attach_url/$photo[attachurl])) {
					$photo['attachurl'] = "$attach_url/$photo[attachurl]";
				} else {
					$photo['attachurl'] = "$attachpath/none.gif";
				}
			$photos[] = $photo;
		}
			//if($photo['ifhpage'] == 1){
				
				//$hpageurl = $photo['attachurl'];
			//}
		$hpageurl = $db->get_one("SELECT attachurl,ifthumb FROM pw_photo WHERE ifhpage='1' AND aid='$aid'");
		$hpageurl['ifthumb'] && $hpageurl['attachurl'] = str_replace('.','_thumb.',$hpageurl['attachurl']);
		if(!$hpageurl['attachurl']){
				$hpageurl['attachurl'] = 'none.gif';
		}
		if (file_exists(R_P."$attachpath/$hpageurl[attachurl]")) {
			$hpageurl = "$attachpath/$hpageurl[attachurl]";
		} elseif (file_exists($attach_url/$photo[attachurl])) {
			$hpageurl = "$attach_url/$hpageurl[attachurl]";
		} else {
			$hpageurl = "$attachpath/none.gif";
		}
		$count = $db->get_value("SELECT COUNT(*) FROM pw_photo WHERE  aid='$aid' AND uid='$admin_uid'");
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&type=photo&job=list&aid=$aid&$addpage");
		}
	}elseif($type == 'music'){
		InitGP(array('maid','page'));
		$malbum_name_array = unserialize($admindb[malbumdb]);
		$malbum_name = $malbum_name_array[$maid][subject];
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT * FROM pw_music WHERE maid='$maid' AND uid='$admin_uid' ORDER BY posttime DESC $limit");
		while($music = $db->fetch_array($query)){
			$music['posttime'] = date('Y-m-d',$music['posttime']);
			$music['name'] = str_replace(array("\r","\n"),'',$music['name']);
			$music['name'] = substrs($music['name'],40);
			$music['descrip'] = str_replace(array("\r","\n"),'',$music['descrip']);
			$music['descrip'] = substrs($music['descrip'],40);
			$musics[] = $music;
		}
		$count = $db->get_value("SELECT COUNT(*) FROM pw_music WHERE  maid='$maid' AND uid='$admin_uid'");
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&job=list&maid='.$maid.'&'.$addpage");
		}
	}
	include PrintEot('itemcp');footer();
}elseif($job == 'modify'){
	InitGP('type');
	if($type == 'photo'){
		
	}elseif($type == 'music'){
		InitGP('maid');
		$malbumdb = $db->get_one("SELECT uid,cid,subject,allowreply,hpageurl,descrip FROM pw_malbums WHERE maid='$maid'");
		(!$malbumdb || $malbumdb['uid']!=$admin_uid) && usermsg('modify_error');
	}
	include PrintEot('itemcp');footer();
} else {
	InitGP(array('titlekey','page','cid','dirid','aid','maid','ifcheck','orderby','sc'));
	$sltorder = $sltsc = $sltcheck = $itemdb = array();
	$cateop = $cateslt = $dirop = $dirslt = $sql = $addpage = $pages = '';
	include_once(D_P."data/cache/forum_cache_$type.php");
	$catedb = ${strtoupper('_'.$type)};
	foreach ($catedb as $key => $value){
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$cateslt = ($value['cid'] == $cid) ? ' SELECTED' : '';
		$cateop .= "<option value=\"$value[cid]\"$cateslt>$add $value[name]</option>";
	}
	if($type == 'blog' || $type == 'bookmark'){
		$dirdb = $admindb['dirdb'] ? unserialize($admindb['dirdb']) : array();
		$dirdb = (array)$dirdb[$type];
		foreach ($dirdb as $key => $value) {
			$dirslt = ($value['typeid'] == $dirid) ? ' SELECTED' : '';
			$name = substrs($value[name],15);
		 	$dirop .= "<option value=\"$value[typeid]\"$dirslt>$name</option>";
		}
		$sltorder[$orderby] = $sltsc[$sc] = $sltcheck[$ifcheck] = ' SELECTED';
		
		if (strlen($titlekey) > 0) {
			$sql .= ($sql ? ' AND' : '')." subject LIKE '%".str_replace('*','%',$titlekey)."%'";
			$addpage .= "titlekey=$titlekey&";
		}
			
		if (strlen($cid) > 0 && (int)$cid > -1) {
			$sql .= ($sql ? ' AND' : '')." cid='$cid'";
			$addpage .= "cid=$cid&";
		}
		if (strlen($dirid) > 0 && (int)$dirid > -1) {
			$sql .= ($sql ? ' AND' : '')." dirid='$dirid'";
			$addpage .= "cid=$dirid&";
		}
		if (strlen($ifcheck) > 0 && (int)$ifcheck > -1) {
			$sql .= ($sql ? ' AND' : '')." ifcheck='$ifcheck'";
			$addpage .= "ifcheck=$ifcheck&";
		}
		$sql && $sql = ' AND '.$sql;
		$orderby != 'lastpost' && $orderby != 'replies' && $orderby != 'hits' && $orderby = 'postdate';
		$sc != 'asc' && $sc = 'desc';
		!$db_perpage && $db_perpage = 30;
		$addpage .= ($orderby == 'postdate' ? '' : "orderby=$orderby&")."sc=$sc&";
		$orderby = " ORDER BY $orderby $sc";
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT itemid,cid,dirid,subject,ifcheck,postdate,replies,hits FROM pw_items WHERE uid='$admin_uid' AND type='$type'$sql $orderby $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['cate'] = $rt['cid'] ? $catedb[$rt['cid']]['name'] : $ulang['none'];
			$rt['dir'] = $rt['dirid'] ? $dirdb[$rt['dirid']]['name'] : $ulang['none'];
			$rt['ifcheck'] = $ulang['ifcheck_'.$rt['ifcheck']];
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['subject'] = str_replace(array("\r","\n"),'',$rt['subject']);
			$rt['subject'] = substrs($rt['subject'],40);
			$itemdb[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE uid='$admin_uid' AND type='$type'$sql");
	}elseif($type == 'photo'){
		$albumdb = $admindb['albumdb'] ? unserialize($admindb['albumdb']) : array();
		empty($albumdb) && $albumdb = array();
		foreach ($albumdb as $key => $value) {
			$aidslt = ($value['aid'] == $aid) ? ' SELECTED' : '';
			$subject = substrs($value[subject],15);
		 	$albumop .= "<option value=\"$value[aid]\"$aidslt>$subject</option>";
		}
		$sltorder[$orderby] = $sltsc[$sc] = $sltcheck[$ifcheck] = ' SELECTED';
		if (strlen($titlekey) > 0) {
			$sql .= ($sql ? ' AND' : '')." subject LIKE '%".str_replace('*','%',$titlekey)."%'";
			$addpage .= "titlekey=$titlekey&";
		}
		if (strlen($cid) > 0 && (int)$cid > -1) {
			$sql .= ($sql ? ' AND' : '')." cid='$cid'";
			$addpage .= "cid=$cid&";
		}
		if (strlen($aid) > 0 && (int)$aid > -1) {
			$sql .= ($sql ? ' AND' : '')." aid='$aid'";
			$addpage .= "aid=$aid&";
		}
		if (strlen($ifcheck) > 0 && (int)$ifcheck > -1) {
			$sql .= ($sql ? ' AND' : '')." ifcheck='$ifcheck'";
			$addpage .= "ifcheck=$ifcheck&";
		}
		$sql && $sql = ' AND '.$sql;
		$orderby != 'lastpost' && $orderby != 'replies' && $orderby != 'hits' && $orderby = 'postdate';
		$sc != 'asc' && $sc = 'desc';
		!$db_perpage && $db_perpage = 30;
		$addpage .= ($orderby == 'postdate' ? '' : "orderby=$orderby&")."sc=$sc&";
		$orderby = " ORDER BY $orderby $sc";
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT aid,cid,subject,ifcheck,postdate,replies,hits,photos FROM pw_albums WHERE uid='$admin_uid'$sql $orderby $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['cate'] = $rt['cid'] ? $catedb[$rt['cid']]['name'] : $ulang['none'];
			$rt['ifcheck'] = $ulang['ifcheck_'.$rt['ifcheck']];
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['subject'] = str_replace(array("\r","\n"),'',$rt['subject']);
			$rt['subject'] = substrs($rt['subject'],40);
			$itemdb[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_albums WHERE uid='$admin_uid'$sql");
	}elseif($type == 'music'){
		$malbumdb = $admindb['malbumdb'] ? unserialize($admindb['malbumdb']) : array();
		empty($malbumdb) && $malbumdb = array();
		foreach ($malbumdb as $key => $value) {
			$maidslt = ($value['maid'] == $maid) ? ' SELECTED' : '';
			$subject = substrs($value[subject],15);
		 	$malbumop .= "<option value=\"$value[maid]\"$maidslt>$subject</option>";
		}
		$sltorder[$orderby] = $sltsc[$sc] = $sltcheck[$ifcheck] = ' SELECTED';
		if (strlen($titlekey) > 0) {
			$sql .= ($sql ? ' AND' : '')." subject LIKE '%".str_replace('*','%',$titlekey)."%'";
			$addpage .= "titlekey=$titlekey&";
		}
		if (strlen($cid) > 0 && (int)$cid > -1) {
			$sql .= ($sql ? ' AND' : '')." cid='$cid'";
			$addpage .= "cid=$cid&";
		}
		if (strlen($maid) > 0 && (int)$maid > -1) {
			$sql .= ($sql ? ' AND' : '')." maid='$maid'";
			$addpage .= "maid=$maid&";
		}
		if (strlen($ifcheck) > 0 && (int)$ifcheck > -1) {
			$sql .= ($sql ? ' AND' : '')." ifcheck='$ifcheck'";
			$addpage .= "ifcheck=$ifcheck&";
		}
		$sql && $sql = ' AND '.$sql;
		$orderby != 'lastpost' && $orderby != 'replies' && $orderby != 'hits' && $orderby = 'postdate';
		$sc != 'asc' && $sc = 'desc';
		!$db_perpage && $db_perpage = 30;
		$addpage .= ($orderby == 'postdate' ? '' : "orderby=$orderby&")."sc=$sc&";
		$orderby = " ORDER BY $orderby $sc";
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT maid,cid,subject,ifcheck,postdate,replies,hits,musics FROM pw_malbums WHERE uid='$admin_uid'$sql $orderby $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['cate'] = $rt['cid'] ? $catedb[$rt['cid']]['name'] : $ulang['none'];
			$rt['ifcheck'] = $ulang['ifcheck_'.$rt['ifcheck']];
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['subject'] = str_replace(array("\r","\n"),'',$rt['subject']);
			$rt['subject'] = substrs($rt['subject'],40);
			$itemdb[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_malbums WHERE uid='$admin_uid'$sql");
	}
	if ($count > $db_perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"$basename&$addpage");
	}
	include PrintEot('itemcp');footer();
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

function update_albumdb($uid){
	global $db;
	$cachedb = array();
	(int)$albums = 0;
	$query = $db->query("SELECT a.aid,a.subject,a.descrip,a.ifhide,p.ifthumb,p.attachurl AS hpageurl FROM pw_albums a LEFT JOIN pw_photo p ON a.hpagepid=p.pid WHERE a.uid='$uid' ORDER BY postdate DESC");
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