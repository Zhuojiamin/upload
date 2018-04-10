<?php
!function_exists('getPath') && exit('Forbidden');

InitGP(array('dirid','y','m','date','ifhide','job'),'G');
$sqlwhere = $pages = $addpage = '';

if ((int)$blogdb['pshownum'] < 1) {
	$blogdb['pshownum'] = $db_perpage ? $db_perpage : 30;
}
(int)$page<1 && $page = 1;
$start = ($page-1)*$blogdb['pshownum'];
$attachdb['down'] = array();
//$urladd = ($dirid ? "&dirid=$dirid" : '');
if ($type == 'photo') {
	$limit = "LIMIT $start,$blogdb[pshownum]";
	$query = $db->query("SELECT aid,password,cid,subject,postdate,photos,hits,replies,ifwordsfb,ifhide,hpagepid,descrip FROM pw_albums WHERE uid=$uid AND ifcheck='1' ORDER BY postdate DESC $limit");
	while($rt = $db->fetch_array($query)){
		if ($uid == $winduid || $rt['ifhide']==0 || $rt['ifhide'] == 3 || ($rt['ifhide']==2 && !empty($winduid) && strpos(",$blogdb[friends],",",$winduid,")!==false)){
			if (!$rt['ifwordsfb']) {
				$ifwordsfb = 0;
				$cktitle = $rt['subject'];
				$ckdescrip = $rt['descrip'];
				empty($_FORBIDDB) && $_FORBIDDB=array();
				foreach ($_FORBIDDB as $value) {
					$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
					$ckdescrip = N_strireplace($value['word'],$value['wordreplace'],$ckdescrip);
				}
				if ($cktitle != $rt['subject']) {
					$rt['subject'] = $cktitle;
					$ifwordsfb = 1;
				}
				if ($ckdescrip != $rt['descrip']) {
					$rt['descrip'] = $ckdescrip;
					$ifwordsfb = 1;
				}
				if ($ifwordsfb) {
					$db->update("UPDATE pw_albums SET ifwordsfb='1',subject='".addslashes($cktitle)."',descrip='".addslashes($ckdescrip)."' WHERE aid='$rt[aid]'");
				}
			}
			$rt['subject'] = substrs($rt['subject'],15);
			$rt['descrip'] = substrs($rt['descrip'],20);
			$db_copyctrl && $rt['descrip'] = preg_replace("/<br \/>/eis","copyctrl()",$rt['descrip']);
			$_TEMPDB[$rt['aid']] = $rt;
		}
	}
	foreach($_TEMPDB as $key => $value){
		$albumdb[$key]['ifthumb'] && $albumdb[$key]['hpageurl'] = str_replace('.','_thumb.',$albumdb[$key]['hpageurl']);
		!$albumdb[$key]['hpageurl'] && $albumdb[$key]['hpageurl'] = 'none.gif';
		if (file_exists(R_P."$attachpath/{$albumdb[$key][hpageurl]}")) {
			$albumdb[$key]['hpageurl'] = "$attachpath/{$albumdb[$key][hpageurl]}";
		} elseif (file_exists("$attach_url/{$albumdb[$key][hpageurl]}")) {
			$albumdb[$key]['hpageurl'] = "$attach_url/{$albumdb[$key][hpageurl]}";
		} else {
			$albumdb[$key]['hpageurl'] = "$attachpath/none.gif";
		}
		$_TEMPDB[$key]['hpageurl'] = $albumdb[$key]['hpageurl'];	
	}
	
	$sqladd = $winduid == $uid ? '' : (strpos(",$blogdb[friends],",",$winduid,") ? 'WHERE ifhide!=1' : 'WHERE ifhide=0 AND ifhide=3');
	$sqladd .= $sqladd ? ' AND ifcheck=1' : 'WHERE ifcheck=1';
	$count = $db->get_value("SELECT COUNT(*) FROM pw_albums $sqladd");
	if ($count > $blogdb[pshownum]) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$blogdb[pshownum],"blog.php?do=list&uid=$uid&type=$type&");
	}
	/*
	foreach ($_TEMPDB as $value) {
		foreach ((array)$value as $v) {
			!$_ITEMDB[$v['aid']] && $_ITEMDB[$v['aid']] = $v;
		}
	}
	unset($_TEMPDB);
	$aid = '';
	foreach ($_ITEMDB as $key => $value) {
		!$aid && $aid = $value['aid'];
		$_ITEMDB[$key]['postdate'] = get_date($value['postdate'],"Y{$left_name[year]}m{$left_name[month]}d{$left_name[day]} H:i:s");
		
		foreach ($value['uploads'] as $upload) {
			$upload['ifthumb'] && $upload['attachurl'] = str_replace('.','_thumb.',$photo['attachurl']);
			!$upload['attachurl'] && $upload['attachurl'] = 'none.gif';
			if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
				$upload['attachurl'] = "$attachpath/$upload[attachurl]";
			} elseif (file_exists($attach_url/$rt[attachurl])) {
				$upload['attachurl'] = "$attach_url/$upload[attachurl]";
			} else {
				$upload['attachurl'] = "$attachpath/none.gif";
			}
			$_ITEMDB[$key]['attachurl']['ul_'.$upload['pid']] = $upload['attachurl'];
		}
	}
	*/
}elseif($type == 'photos'){
		$allow_read = '0';
		$ckpwd = '0';
		if($albumdb[$aid]['ifhide'] == '3'){
			list($check_winduid,$check_aid,$check_pwd) = explode("\t",StrCode(GetCookie('alubm_pwd'),'DECODE'));
			$password = $db->get_value("SELECT password FROM pw_albums WHERE aid='$aid'");
			is_numeric($check_winduid) && $check_winduid == $winduid && strlen($check_pwd)>16 && $password == $check_pwd && $check_aid == $aid && $ckpwd = '1';
		}
		if($winduid == $uid || $albumdb[$aid]['ifhide'] == 0 ||  ($albumdb[$aid]['ifhide'] == 3 && $ckpwd == '1') || ($albumdb[$aid]['ifhide']==2 && !empty($winduid) && strpos(",$blogdb[friends],",",$winduid,")!==false)){
			$allow_read = '1';
		}
	if($job == 'slide'){
		$imgs = '';
		$allow_read == '0' && showmsg('group_right');
		$query = $db->query("SELECT attachurl FROM pw_photo WHERE aid=$aid ORDER BY uploadtime DESC");
		while($photo = $db->fetch_array($query)){
			$imgs .= "$attachpath/$photo[attachurl]".'|';
		}
		$imgs .= "$imgpath".'/end.jpg';
	}else{
		$limit = "LIMIT $start,$blogdb[pshownum]";
		$query = $db->query("SELECT p.*,a.subject,a.ifhide FROM pw_photo p LEFT JOIN pw_albums a ON p.aid=a.aid WHERE p.aid='$aid' ORDER BY p.pid DESC $limit");
		while($photo = $db->fetch_array($query)){
			$photo['name'] = substrs($photo['name'],15);
			$photo['descrip'] = substrs($photo['descrip'],10);
			$photo['uploadtime'] = date('Y-m-d',$photo['uploadtime']);
			$photo['ifthumb'] && $photo['attachurl'] = str_replace('.','_thumb.',$photo['attachurl']);
			!$photo['attachurl'] && $photo['attachurl'] = 'none.gif';
			if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
				$photo['attachurl'] = "$attachpath/$photo[attachurl]";
			} elseif (file_exists($attach_url/$rt[attachurl])) {
				$photo['attachurl'] = "$attach_url/$photo[attachurl]";
			} else {
				$photo['attachurl'] = "$attachpath/none.gif";
			}
			$album_name = $photo['subject'];
			$photos[] = $photo;
		}
		$count = $db->get_value("SELECT COUNT(*) FROM pw_photo WHERE  aid='$aid'");
		if ($count > $blogdb[pshownum]) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$blogdb[pshownum],"blog.php?do=list&uid=$uid&type=photos&job=view&aid=$aid");
		}
	}
}elseif($type == 'music'){
	$limit = "LIMIT $start,$blogdb[pshownum]";
	$query = $db->query("SELECT maid,uid,author,subject,postdate,hpageurl FROM pw_malbums WHERE ifcheck='1' AND uid=$uid ORDER BY postdate DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt[subject] = substrs($rt['subject'],15);
		$rt['postdate'] = date('Y-m-d',$rt['postdate']);
		$rt['hpageurl'] = showhpageurl($rt['hpageurl']);
		$_TEMPDB[$rt[maid]] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_malbums WHERE uid=$uid AND ifcheck='1'");
	if ($count > $db_perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"blog.php?do=list&uid=$uid&type=music&");
	}
}elseif ($type == 'bookmark') {
	if ((int)$dirid > 0) {
		$sqlwhere = " AND dirid='$dirid'";
		$addpage = "dirid=$dirid&";
	}
	$limit = "LIMIT $start,$blogdb[pshownum]";
	$query = $db->query("SELECT i.itemid,i.subject,i.postdate,i.ifwordsfb,i.ifhide,b.tags,b.bookmarkurl,b.ifconvert,b.content FROM pw_items i LEFT JOIN pw_bookmark b ON i.itemid=b.itemid$L_join WHERE i.uid='$uid' AND i.type='bookmark' AND i.ifcheck='1' $sqlwhere ORDER BY i.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			if ($uid == $winduid || $rt['ifhide']==0 || ($rt['ifhide']==2 && !empty($winduid) && strpos(",$blogdb[friends],",",$winduid,")!==false)) {
				$rt['tagsdb'] = array();
				if ($rt['tags']) {
					$taginfo = array_unique(explode(',',$rt['tags']));
					foreach ($taginfo as $key => $value) {
						if ($value) {
							$rt['tagsdb'][$k] = array('name' => $value,'tagname' => rawurlencode($value));
						}
					}
				}
				$rt['ifsign']<2 && $rt['content'] = nl2br($rt['content']);
				$blogdb['wshownum'] && $rt['content'] = substrs($rt['content'],$blogdb['wshownum']);
				if (!$rt['ifwordsfb']) {
					$ifwordsfb = 0;
					$cktitle = $rt['subject'];
					$ckcontent = $rt['content'];
					empty($_FORBIDDB) && $_FORBIDDB=array();
					foreach ($_FORBIDDB as $value) {
						$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
						$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
					}
					if ($cktitle != $rt['subject']) {
						$rt['subject'] = $cktitle;
						$ifwordsfb = 1;
					}
					if ($ckcontent != $rt['content']) {
						$rt['content'] = $ckcontent;
						$ifwordsfb = 1;
					}
					if ($ifwordsfb) {
						$db->update("UPDATE pw_items SET ifwordsfb='1',subject='".addslashes($cktitle)."' WHERE itemid='$rt[itemid]'");
						$db->update("UPDATE pw_bookmark SET content='".addslashes($ckcontent)."' WHERE itemid='$rt[itemid]'");
					}
				}
				$rt['ifconvert'] && $rt['content'] = convert($rt['content'],$db_post);
				$db_copyctrl && $rt['content'] = preg_replace("/<br \/>/eis","copyctrl()",$rt['content']);
				$_TEMPDB[$rt['itemid']] = $rt;
			}
		}
		$query && $db->free_result($query);
		foreach ($_TEMPDB as $key => $value) {
			if ($tagid && strpos(",$value[tags],",",$tagid,")===false) {
				continue;
			}
			$value['postdate'] = get_date($value['postdate'],"Y{$left_name[year]}m{$left_name[month]}d{$left_name[day]} H:i:s");
			unset($value['tags']);
			$value['content'] = attachment($value['content'],$db_post['times']);
			$_ITEMDB[$key] = $value;
		}
		unset($_TEMPDB);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE uid='$uid' AND type='bookmark' $sqlwhere");
		if ($count > $blogdb['pshownum']) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$blogdb['pshownum'],"blog.php?do=list&uid=$uid&type=$type&$addpage");
		}
} elseif ($type == 'blog') {
	if ((int)$dirid > 0) {
	$sqlwhere = " AND dirid='$dirid'";
	$addpage = "dirid=$dirid&";
	}
	if ($y && $m) {
		if (!isset($date)) {
			$t_start = mktime (0,0,0,$m,1,$y);
			$t_end = mktime (0,0,0,$m+1,0,$y);
		} else {
			$t_start = strtotime(str_replace('_','-',$date));
			$t_end = $t_start+24*3600;
		}
		$sqlwhere = " AND postdate>'$t_start' AND postdate<'$t_end'";
		$addpage = "y=$y&m=$m&";
	}
	$sqlwhere .= ($ifhide == '1' ? " AND ifhide='1'" : (($ifhide == '2') ? " AND ifhide='2'" : ''));
	$i = 0;
	$limit = "LIMIT $start,$blogdb[pshownum]";
	$query = $db->query("SELECT i.itemid,i.dirid,i.author,i.transfer,i.icon,i.subject,i.ifhide,i.postdate,i.hits,i.replies,i.ifwordsfb,t.*$select FROM pw_items i LEFT JOIN pw_$type t ON i.itemid=t.itemid{$L_join} WHERE i.uid='$uid' AND i.type='$type' AND i.ifcheck='1'$sqlwhere ORDER BY i.postdate DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		if ($uid == $winduid || $rt['ifhide']==0 || ($rt['ifhide']==2 && !empty($winduid) && strpos(",$blogdb[friends],",",$winduid,")!==false)) {
			$rt['postdate'] = get_date($rt['postdate'],"Y{$left_name[year]}m{$left_name[month]}d{$left_name[day]} H:i:s");
			if (!$rt['ifwordsfb']) {
				$ifwordsfb = 0;
				$cktitle = $rt['subject'];
				$ckcontent = $rt['content'];
				empty($_FORBIDDB) && $_FORBIDDB=array();
				foreach ($_FORBIDDB as $value) {
					$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
					$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
				}
				if ($cktitle != $rt['subject']) {
					$rt['subject'] = $cktitle;
					$ifwordsfb = 1;
				}
				if ($ckcontent != $rt['content']) {
					$rt['content'] = $ckcontent;
					$ifwordsfb = 1;
				}
				if ($ifwordsfb) {
					$db->update("UPDATE pw_items SET ifwordsfb='1',subject='".addslashes($cktitle)."' WHERE itemid='$rt[itemid]'");
					$db->update("UPDATE pw_$type SET content='".addslashes($ckcontent)."' WHERE itemid='$rt[itemid]'");
				}
			}
			$rt['ifsign']<2 && $rt['content'] = nl2br($rt['content']);
			if(strpos($rt['content'],'[more]') !== false){
				$rt['content'] = showmore($rt['content']);
				$rt['ifmore'] = 1;
			}
			$rt['ifconvert'] && $rt['content'] = convert($rt['content'],$db_post);
			strpos($rt['content'],'[s:')!==false && $rt['content'] = showsmile($rt['content']);
			$rt['content'] = preg_replace("/\[attachment=([0-9]+)\]/is",'',$rt['content']);
			$klink = $blogdb['klink'] ? unserialize($blogdb['klink']) : array();
			$rt['content'] = keywordlink($rt['content'],$klink);
			//$rt['content'] = preg_replace('/\[(.+?)\]/eis','',$rt['content']);
			//$rt['content'] = nl2br($rt['content']);
			//$blogdb['wshownum'] && $rt['content'] = substrs($rt['content'],$blogdb['wshownum']);
			//$rt['ifconvert'] && $rt['content'] = convert($rt['content'],$db_post);
			//$db_copyctrl && $rt['content'] = preg_replace("/<br \/>/eis","copyctrl()",$rt['content']);
			//$rt['content'] = strip_tags(str_replace('>\'','&lt;',$rt['content']));
			$rt['tagsdb'] = array();
			if ($rt['tags']) {
				$taginfo = array_unique(explode(',',$rt['tags']));
				foreach ($taginfo as $key => $value) {
					if ($value) {
						$tagname = rawurlencode($value);
						$rt['tagsdb'][$key] = array('name' => $value,'tagname' => $tagname);
					} else {
						unset($rt['tagsdb'][$key]);
					}
				}
			}
			unset($rt['tags']);
			$dirdb = unserialize($blogdb['dirdb']);
		    $dirdb = $dirdb[$type];
			$rt['dirname'] = $dirdb[$rt['dirid']]['name'];
			$rt['icon'] = explode(',',$rt['icon']);
			$rt['transfer'] = explode(',',$rt['transfer']);
			$_ITEMDB[$rt['itemid']] = $rt;
		} else {
			$i++;
		}
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE uid='$uid' AND type='$type' AND ifcheck='1'$sqlwhere");
	$count -= $i;
	if ($count > $blogdb['pshownum']) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$blogdb['pshownum'],"blog.php?do=list&uid=$uid&type=$type&$addpage");
	}
} elseif ($type == 'team') {
	$join = GetGP('join','G');
	$teamwhere = "tu.uid='$uid' AND tu.ifcheck='1' AND ".($join == '1' ? "tu.admin!='$uid'" : "tu.admin='$uid'");
	$_ITEMDB = array();
	$query   = $db->query("SELECT tu.joindate,t.teamid,t.cid,t.username,t.name,t.descrip,t.icon FROM pw_tuser tu LEFT JOIN pw_team t ON tu.teamid=t.teamid WHERE $teamwhere");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['icon']) {
			$a_url = $attach_url ? $attach_url : "$db_blogurl/$attachpath";
			$attach_ext  = strrchr($rt['icon'],'.');
			$attach_name = substr($rt['icon'],0,strrpos($rt['icon'],'.'));
			$rt['picurl'] = showfacedesign($rt[icon]);
			N_stripos($rt['picurl'],'login') && $rt['picurl'] = N_strireplace('login','log in',$rt['picurl']);
			$rt['attachurl'] = file_exists("$a_url/{$attach_name}_thumb{$attach_ext}") ? "$a_url/{$attach_name}_thumb{$attach_ext}" : $rt['picurl'];
			N_stripos($rt['attachurl'],'login') && $rt['attachurl'] = N_strireplace('login','log in',$rt['attachurl']);
		} else {
			$rt['picurl'] = "theme/$style/images/nopic.gif";
		}
		$rt['joindate'] = get_date($rt['joindate'],"Y{$left_name[year]}m{$left_name[month]}d{$left_name[day]}");
		$_ITEMDB[] = $rt;
	}
	$db->free_result($query);
} elseif ($type == 'gbook') {
	$limit = "LIMIT $start,$blogdb[pshownum]";
	$query = $db->query("SELECT id,uid,author,authorid,authoricon,postdate,content,replydate,reply,ifwordsfb FROM pw_gbook WHERE uid='$uid' ORDER BY postdate DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		!$rt['author'] && $rt['author'] = 'guest';
		$rt['authoricon'] = showfacedesign($rt['authoricon']);
		$rt['postdate'] = get_date($rt['postdate'],"Y{$left_name[year]}m{$left_name[month]}d{$left_name[day]}");
		$rt['replydate']= get_date($rt['replydate'],"Y{$left_name[year]}m{$left_name[month]}d{$left_name[day]}");
		$ckcontent = $rt['content'];
		$ckreply = $rt['reply'];
		$sql = '';
		empty($_FORBIDDB) && $_FORBIDDB=array();
		foreach ($_FORBIDDB as $value) {
			$ckreply = N_strireplace($value['word'],$value['wordreplace'],$ckreply);
			$rt['ifwordsfb'] && $ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
		}
		if ($ckreply != $rt['reply']) {
			$rt['reply'] = $ckreply;
			$sql = "reply='".addslashes($ckreply)."'";
		}
		if ($ckcontent && $ckcontent != $rt['content']) {
			$rt['content'] = $ckcontent;
			$sql .= ($sql ? ',' : '')."ifwordsfb='1',content='".addslashes($ckcontent)."'";
		}
		$sql && $db->update("UPDATE pw_gbook SET $sql WHERE uid='$uid'");
		$rt['content'] = nl2br($rt['content']);
		$rt['reply'] = nl2br($rt['reply']);
		if ($db_copyctrl) {
			$rt['content'] = preg_replace("/<br \/>/eis","copyctrl()",$rt['content']);
			$rt['reply'] = preg_replace("/<br \/>/eis","copyctrl()",$rt['reply']);
		}
		$_ITEMDB[] = $rt;
	}
	$db->free_result($query);
	$gdisplay = 'none';
	$windgroup['closecmt'] && $blogdb['ifgbook'] = 0;
	$rawwindid = $ckurl = '';
	if ($blogdb['ifgbook']) {
		list(,,,,,$gbookgd) = explode("\t",$db_gdcheck);
		if (!$gbookgd) {
			list(,$gbookgd) = explode(',',$blogdb['gdcheck']);
		}
		if ($gbookgd) {
			$rawwindid = (!$windid) ? 'guest' : rawurlencode($windid);
			$ckurl = str_replace('?','',$ckurl);
		}
		list(,,,$gbq) = explode("\t",$db_qcheck);
		list($blogdb['gbqcheck'],$blogdb['cmtqcheck']) = explode(',',$blogdb['qcheck']);
		if(!$gbq){
			$ifgbq = $blogdb['gbqcheck'] ? '1' : '0';
		}else{
			$ifgbq = '1';
		}
	}
	if (!empty($_ITEMDB)) {
		$gdisplay = '';
		if ($blogdb['msgs'] > $blogdb['pshownum']) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($blogdb['msgs'],$page,$blogdb['pshownum'],"blog.php?do=list&uid=$uid&type=gbook&");
		}
	}
}
include_once(getPath("list_$type"));
function GetAttachUrl($array){
	global $db_blogurl,$attach_url,$attachpath;
	$a_url = $attach_url ? $attach_url : "$db_blogurl/$attachpath";
	$array['picurl'] = "$a_url/$array[attachurl]";
	N_stripos($array['picurl'],'login') && $array['picurl'] = N_strireplace('login','log in',$array['picurl']);
	$array['attachurl'] = $array['picurl'];
	if ($array['ifthumb']) {
		$attach_ext = strrchr($array['attachurl'],'.');
		$array['attachurl'] = str_replace($attach_ext,"_thumb{$attach_ext}",$array['attachurl']);
		}
	return $array;
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

function showmore($content){
	$content = substr($content,0,strpos($content,'[more]'));
	return $content;
}

function keywordlink($content,$klink){
	foreach($klink as $key => $value){
		$content = N_strireplace($value[0],'<a href="'.$value[1].'" title="'.$value[2].'" target="_blank">'.$value[0].'</a>',$content);
	}
	return $content;
}
?>