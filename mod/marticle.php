<?php
!function_exists('N_strireplace') && exit('Forbidden');
!empty($blogdb) && $malbumdb = $blogdb;
$articleurl = $do != 'showone' ? 'article.php?' : 'blog.php?do=showone&';

$maid = (int)$malbumdb['maid'];
$uid = (int)$malbumdb['uid'];
$cid = (int)$malbumdb['cid'];
$malbumdb['postdate'] = get_date($malbumdb['postdate'],'Y-m-d');
$malbumdb['lastpost'] = get_date($malbumdb['lastpost'],'Y-m-d');

if (!$malbumdb['ifwordsfb']) {
	$ifwordsfb = 0;
	$cksubject = $malbumdb['subject'];
	$ckdescrip = $malbumdb['descrip'];
	foreach ($_FORBIDDB as $value) {
		$cksubject = N_strireplace($value['word'],$value['wordreplace'],$cksubject);
		$ckdescrip = N_strireplace($value['word'],$value['wordreplace'],$ckdescrip);
	}
	if ($cksubject != $malbumdb['subject']) {
		$malbumdb['subject'] = $cksubject;
		$ifwordsfb = 1;
	}
	if ($ckdescrip != $malbumdb['descrip']) {
		$malbumdb['descrip'] = $ckdescrip;
		$ifwordsfb = 1;
	}
	if ($ifwordsfb) {
		$db->update("UPDATE pw_malbums SET ifwordsfb='1',subject='".addslashes($ckdescrip)."',descrip='".addslashes($ckdescrip)."' WHERE maid='$maid'");
	}
}

$malbumdb['ifconvert'] && $malbumdb['descrip'] = convert($malbumdb['descrip'],$db_post);

foreach ($musicdb as $key => $value) {
	if (eregi("\.(rm|rmvb|ra|ram)$",$value['murl'])) {
		$musicdb[$key]['type'] = 'rm';
	} elseif (eregi("\.(qt|mov|4mv)$",$value['murl'])) {
		$musicdb[$key]['type'] = 'qt';
	} else {
		$musicdb[$key]['type'] = 'wmv';
	}
}

$query = $db->query("SELECT tags FROM pw_music WHERE maid='$maid'");
while($tags = $db->fetch_array($query)){
	$tagsdb[] = $tags[tags];
}
if ($tagsdb) {
	$taginfo = array_unique($tagsdb);
	foreach ($taginfo as $key => $value) {
		if ($value) {
			$tagname = rawurlencode($value);
			$malbumdb['tagsdb'][$key] = array('name' => $value,'tagname' => $tagname);
		} else {
			unset($tagsdb);
		}
	}
}

if ($malbumdb['replies']) {
	(int)$page<1 && $page = 1;
	if ((int)$malbumdb['cshownum'] < 1) {
		$malbumdb['cshownum'] = $db_perpage ? $db_perpage : 30;
	}
	$start = ($page-1)*$malbumdb['cshownum'];
	$malbumdb['cmttext'] = $malbumdb['cmttext'] ? (array)unserialize($malbumdb['cmttext']) : array();
	$cmttextnum = count($malbumdb['cmttext']);
	if ($cmttextnum > $start) {
		$malbumdb['cmttext'] = array_slice($malbumdb['cmttext'],$start,$malbumdb['cshownum']);
	} else {
		$malbumdb['cmttext'] = array();
		$query = $db->query("SELECT c.id,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,u.icon as picon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.itemid='$maid' ORDER BY c.postdate DESC LIMIT $start,$malbumdb[cshownum]");
		while ($rt = $db->fetch_array($query)) {
			$malbumdb['cmttext'][] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content']);
		}
		$db->free_result($query);
	}
	foreach ($malbumdb['cmttext'] as $key => $value) {
		$malbumdb['cmttext'][$key]['picon'] = showfacedesign($value['picon']);
		$malbumdb['cmttext'][$key]['postdate'] = get_date($value['postdate']);
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
		$malbumdb['cmttext'][$key]['content'] = $value['ifconvert'] ? convert($value['content'],$db_post) : $value['content'];
		strpos($malbumdb['cmttext'][$key]['content'],'[s:')!==false && $malbumdb['cmttext'][$key]['content'] = showsmile($malbumdb['cmttext'][$key]['content']);
	}
	if ($malbumdb['replies'] > $malbumdb['cshownum']) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($malbumdb['replies'],$page,$malbumdb['cshownum'],"{$articleurl}type=$type&cid=$cid&maid=$maid&");
	}
}


$windgroup['closecmt'] && $malbumdb['allowreply'] = 0;

$rawwindid = $ckurl = '';
if ($malbumdb['allowreply']) {
	list(,,,,$cmtgd) = explode("\t",$db_gdcheck);
	if (!$cmtgd) {
		list($cmtgd) = explode(',',$malbumdb['gdcheck']);
	}
	if ($cmtgd) {
		$rawwindid = (!$windid) ? 'guest' : rawurlencode($windid);
		$ckurl = str_replace('?','',$ckurl);
	}
	list(,,,,$cmtq) = explode("\t",$db_qcheck);
	list($malbumdb['gbqcheck'],$malbumdb['cmtqcheck']) = explode(',',$malbumdb['qcheck']);
	if(!$cmtq){
		$ifcmtq = $malbumdb['cmtqcheck'] ? '1' : '0';
	}else{
		$ifcmtq = '1';
	}
}

?>