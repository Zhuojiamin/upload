<?php
!function_exists('N_strireplace') && exit('Forbidden');
!empty($blogdb) && $photodb = $blogdb;
$articleurl = $do != 'showone' ? 'article.php?' : 'blog.php?do=showone&';
$aid = (int)$photodb['aid'];
$pid = (int)$photodb['pid'];
$cid = (int)$photodb['cid'];
$photodb['icon'] = explode(',',$photodb['icon']);
$photodb['postdate'] = get_date($photodb['postdate'],'Y-m-d');
$photodb['uploadtime'] = get_date($photodb['uploadtime'],'Y-m-d');

InitGP(array('goto'),'G');
if(!empty($goto)){
	$pid = $db->get_value("SELECT pid FROM pw_photo WHERE pid=$pid");
	if($do == 'showone'){
		$url = "{$articleurl}uid=$uid&type=$type&aid=$aid";
		$sqladd = "AND uid='$uid' AND aid=$aid ORDER BY pid";
	}else{
		$url = "{$articleurl}type=$type&cid=$cid&aid=$aid";
		$sqladd = "AND cid=$cid AND aid=$aid ORDER BY pid";
	}
	if($goto == 'previous'){
		$previous = $db->get_one("SELECT pid FROM pw_photo WHERE pid>$pid $sqladd ASC LIMIT 1");
		if($previous){
			ObHeader("$url&pid=$previous[pid]");
		} else{
			ObHeader("$url&pid=$pid");
		}
	}elseif($goto == 'next'){
		$next = $db->get_one("SELECT pid FROM pw_photo WHERE pid<$pid $sqladd DESC LIMIT 1");
		if($next){
			ObHeader("$url&pid=$next[pid]");
		} else{
			ObHeader("$url&pid=$pid");
		}
	}else{
		Showmsg('undefined_action');
	}
}

$db->update("UPDATE pw_albums SET hits=hits+1 WHERE aid='$aid'");
$db->update("UPDATE pw_photo SET phits=phits+1 WHERE pid='$pid'");
$attachurl = GetPhotoUrl($photodb['attachurl'],$photodb['ifthumb']);
$photodb[photo_url] = cvpic($attachurl,$db_post['picwidth'],$db_post['picheight'],1);

if (!$photodb['ifwordsfb']) {
	$ifwordsfb = 0;
	$ckname= $photodb['name'];
	$ckdescrip = $blogdb['descrip'];
	foreach ($_FORBIDDB as $value) {
		$ckname = N_strireplace($value['word'],$value['wordreplace'],$ckname);
		$ckdescrip = N_strireplace($value['word'],$value['wordreplace'],$ckdescrip);
	}
	if ($ckname != $photodb['name']) {
		$photodb['name'] = $ckname;
		$ifwordsfb = 1;
	}
	if ($ckdescrip != $photodb['descrip']) {
		$photodb['descrip'] = $ckdescrip;
		$ifwordsfb = 1;
	}
	if ($ifwordsfb) {
		$db->update("UPDATE pw_photo SET ifwordsfb='1',name='".addslashes($ckname)."',descrip='".addslashes($ckdescrip)."' WHERE pid='$pid'");
	}
}
$photodb['tagsdb'] = array();
if ($photodb['tags']) {
	$taginfo = array_unique(explode(',',$photodb['tags']));
	foreach ($taginfo as $key => $value) {
		if ($value) {
			$tagname = rawurlencode($value);
			$photodb['tagsdb'][$key] = array('name' => $value,'tagname' => $tagname);
		} else {
			unset($photodb['tagsdb'][$key]);
		}
	}
}
unset($photodb['tags']);
if ($photodb['preplies']) {
	(int)$page<1 && $page = 1;
	if ((int)$photodb['cshownum'] < 1) {
		$photodb['cshownum'] = $db_perpage ? $db_perpage : 30;
	}
	$start = ($page-1)*$photodb['cshownum'];
	$photodb['cmttext'] = $photodb['cmttext'] ? (array)unserialize($photodb['cmttext']) : array();
	$cmttextnum = count($photodb['cmttext']);
	if ($cmttextnum > $start) {
		$photodb['cmttext'] = array_slice($photodb['cmttext'],$start,$photodb['cshownum']);
	} else {
		$photodb['cmttext'] = array();
		$query = $db->query("SELECT c.id,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,u.icon as picon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.itemid='$pid' ORDER BY c.postdate DESC LIMIT $start,$photodb[cshownum]");
		while ($rt = $db->fetch_array($query)) {
			$photodb['cmttext'][] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content']);
		}
		$db->free_result($query);
	}
	foreach ($photodb['cmttext'] as $key => $value) {
		$photodb['cmttext'][$key]['picon'] = showfacedesign($value['picon']);
		$photodb['cmttext'][$key]['postdate'] = get_date($value['postdate']);
		if (!$value['ifwordsfb']) {
			$ifwordsfb = 0;
			$ckcontent = $value['content'];
			foreach ($_FORBIDDB as $v) {
				$ckcontent = N_strireplace($v['word'],$v['wordreplace'],$ckcontent);
			}
			if ($ckcontent != $value['content']) {
				$value['content'] = $ckcontent;
				$ifwordsfb = 1;
			}
			$ifwordsfb && $db->update("UPDATE pw_comment SET ifwordsfb='1',content='".addslashes($ckcontent)."' WHERE id='$value[id]'");
		}
		$value['content'] = nl2br($value['content']);
		$photodb['cmttext'][$key]['content'] = $value['ifconvert'] ? convert($value['content'],$db_post) : $value['content'];
		strpos($photodb['cmttext'][$key]['content'],'[s:')!==false && $photodb['cmttext'][$key]['content'] = showsmile($photodb['cmttext'][$key]['content']);
	}
	if ($photodb['replies'] > $photodb['cshownum']) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($photodb['replies'],$page,$photodb['cshownum'],"{$articleurl}type=$type&cid=$cid&aid=$aid&pid=$pid&");
	}
}
$windgroup['closecmt'] && $photodb['allowreply'] = 0;
$rawwindid = $ckurl = '';
if ($photodb['allowreply']) {
	list(,,,,$cmtgd) = explode("\t",$db_gdcheck);
	if (!$cmtgd) {
		list($cmtgd) = explode(',',$photodb['gdcheck']);
	}
	if ($cmtgd) {
		$rawwindid = (!$windid) ? 'guest' : rawurlencode($windid);
		$ckurl = str_replace('?','',$ckurl);
	}
	list(,,,,$cmtq) = explode("\t",$db_qcheck);
	list($photodb['gbqcheck'],$photodb['cmtqcheck']) = explode(',',$photodb['qcheck']);
	if(!$cmtq){
		$ifcmtq = $photodb['cmtqcheck'] ? '1' : '0';
	}else{
		$ifcmtq = '1';
	}
}
$photodb['albumdbname'] = $albumdb[$photodb['albumdb']]['name'];

function GetPhotoUrl($attachurl,$ifthumb){
	global $db_blogurl,$attach_url,$attachpath;
	$a_url = $attach_url ? $attach_url : "$db_blogurl/$attachpath";
	$photourl = "$a_url/$attachurl";
	N_stripos($photourl,'login') && $photourl = N_strireplace('login','log in',$photourl);
	$attachurl = $photourl;
	/*单图显示的时候不使用缩略
	if ($ifthumb) {
		$attach_ext = strrchr($attachurl,'.');
		$attachurl = str_replace($attach_ext,"_thumb{$attach_ext}",$attachurl);
	}
	*/
	return $attachurl;
}
?>