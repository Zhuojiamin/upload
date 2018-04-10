<?php
!function_exists('readover') && exit('Forbidden');

(!$type || !in_array($type,array('blog','photo','music','bookmark','goods','file'))) && Showmsg('undefined_action');
$articleurl = $db_articleurl ? 'article.php?' : 'blog.php?do=showone&';

$blogdb = $db->get_one("SELECT i.cid,i.uid,i.author,i.postdate,i.subject,i.replies,i.ifcheck,i.ifhide,i.ifwordsfb,i.cmttext,b.ifsign,b.ifconvert,b.content,u.friends FROM pw_items i LEFT JOIN pw_{$type} b ON i.itemid=b.itemid LEFT JOIN pw_user u ON u.uid=i.uid WHERE i.itemid='$itemid'");
(empty($blogdb) || !$blogdb['ifcheck']) && Showmsg('illegal_tid');

$uid = (int)$blogdb['uid'];
$title = $blogdb['subject'];
$catename = $ilang['c'.$type];
@include_once Pcv(D_P."data/cache/forum_cache_{$type}.php");
$catedb = ${'_'.strtoupper($type)};
$cidname = $catedb[$blogdb['cid']]['name'];

$blogdb['ifhide'] = (int)$blogdb['ifhide'];
($uid != $winduid && ($blogdb['ifhide']==1 || ($blogdb['ifhide']==2 && strpos(",$blogdb[friends],",",$winduid,")===false))) && Showmsg('sec_blog');

require_once(R_P.'mod/windcode.php');
@include(D_P.'data/cache/wordfb.php');
$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
$blogdb['postdate'] = get_date($blogdb['postdate'],'Y-m-d');
$blogdb['ifsign']<2 && $blogdb['content'] = nl2br($blogdb['content']);

if (!$blogdb['ifwordsfb']) {
	$ifwordsfb = 0;
	$cktitle = $blogdb['subject'];
	$ckcontent = $blogdb['content'];
	foreach ($_FORBIDDB as $value) {
		$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
		$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
	}
	if ($cktitle != $blogdb['subject']) {
		$blogdb['subject'] = $cktitle;
		$ifwordsfb = 1;
	}
	if ($ckcontent != $blogdb['content']) {
		$blogdb['content'] = $ckcontent;
		$ifwordsfb = 1;
	}
	if ($ifwordsfb) {
		$db->update("UPDATE pw_items SET ifwordsfb='1',subject='".addslashes($cktitle)."' WHERE itemid='$itemid'");
		$db->update("UPDATE pw_$type SET content='".addslashes($ckcontent)."' WHERE itemid='$itemid'");
	}
}
$blogdb['ifconvert'] && $blogdb['content'] = convert($blogdb['content'],$db_post);
$db_copyctrl && $blogdb['content'] = preg_replace('/<br \/>/eis',"copyctrl()",$blogdb['content']);
$pages = ''; (int)$page<1 && $page = 1;
$readdb = array();
$page == 1 && $readdb[] = $blogdb;
$replies = 0;
if ($blogdb['replies']) {
	$blogdb['cmttext'] = $blogdb['cmttext'] ? (array)unserialize($blogdb['cmttext']) : array();
	$replies = $blogdb['replies']+1;
	$pages = smpage($replies,$page,$pagesize,"simple/index.php?type=$type&itemid=$itemid");
	$start = $pagesize*($page-1);
	$page==1 ? $pagesize -= 1 : $start -= 1;
	$textnum = count($blogdb['cmttext']);
	if ($textnum > $start) {
		$blogdb['cmttext'] = array_slice($blogdb['cmttext'],$start,$pagesize);
	} else {
		$blogdb['cmttext'] = array();
		$query = $db->query("SELECT c.id,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,u.icon as picon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND itemid='$itemid' ORDER BY postdate DESC LIMIT $start,$pagesize");
		while ($rt = $db->fetch_array($query)) {
			$blogdb['cmttext'][] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content']);
		}
		$db->free_result($query);
	}
	foreach ($blogdb['cmttext'] as $key => $value) {
		$value['uid'] = $value['authorid'];
		$value['postdate'] = get_date($value['postdate']);
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
		$value['content'] = $value['ifconvert'] ? convert($value['content'],$db_post) : $value['content'];
		$readdb[] = $value;
	}
}
require_once PrintEot('simple_read');
?>