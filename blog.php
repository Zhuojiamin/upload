<?php
require_once('global.php');
require_once(R_P.'mod/windcode.php');
$type && !in_array($type,array('blog','photo','photos','music','bookmark','team','gbook','combine','aboutme')) && Showmsg('illegal_tid');

$sql = $title = $style = $sqlstyle = '';
if ($do=='showone' && $type) {
	if($type == 'blog'){
		$itemid = (int)$itemid;
		$blogdb = $db->get_one("SELECT t.*,i.itemid,i.dirid,i.uid,i.transfer,i.icon,i.subject,i.postdate,i.hits,i.replies,i.digest,i.allowreply,i.ifhide,i.ifwordsfb,i.ifcheck,i.footprints,i.uploads,i.cmttext,u.uid,u.username,u.icon as uicon,u.groupid,u.memberid,u.blogtitle,u.regdate,u.friendview,u.friends,ui.style,ui.bbsuid,ui.domainname,ui.signature,ui.wshownum,ui.pshownum,ui.cshownum,ui.flashurl,ui.headerdb,ui.leftdb,ui.dirdb,ui.introduce,ui.gdcheck,ui.qcheck,ui.klink FROM pw_items i LEFT JOIN pw_$type t ON i.itemid=t.itemid LEFT JOIN pw_user u ON i.uid=u.uid LEFT JOIN pw_userinfo ui ON i.uid=ui.uid WHERE i.itemid='$itemid'");
		!$blogdb['ifcheck'] && Showmsg('illegal_tid');
		$uid = (int)$blogdb['uid'];
		$bbsuid = (int)$blogdb['bbsuid'];
		$title = $blogdb['subject'].' - ';
		$blogdb['ifhide'] = (int)$blogdb['ifhide'];
		($uid != $winduid && ($blogdb['ifhide']==1 || ($blogdb['ifhide']==2 && strpos(",$blogdb[friends],",",$winduid,")===false))) && Showmsg('sec_blog');
		//获取系统分类信息，用于日志转载处
		@include_once Pcv(D_P."data/cache/forum_cache_blog.php");
		$catedb = ${'_BLOG'};
	}elseif($type == 'photo'){
		$pid = (int)GetGP('pid','G');
		$blogdb = $db->get_one("SELECT p.*,a.aid,a.cid,a.subject,a.postdate,a.photos,a.replies,a.digest,a.allowreply,a.ifhide,a.ifwordsfb,a.ifcheck,a.footprints,u.uid,u.username,u.icon as uicon,u.groupid,u.memberid,u.blogtitle,u.regdate,u.friendview,u.friends,ui.style,ui.bbsuid,ui.domainname,ui.wshownum,ui.pshownum,ui.cshownum,ui.flashurl,ui.headerdb,ui.leftdb,ui.dirdb,ui.albumdb,ui.introduce,ui.gdcheck,ui.qcheck FROM pw_photo p LEFT JOIN pw_albums a ON p.aid=a.aid LEFT JOIN pw_user u ON p.uid=u.uid LEFT JOIN pw_userinfo ui ON p.uid=ui.uid WHERE p.pid='$pid'");
		(empty($blogdb) || !$blogdb['ifcheck']) && Showmsg('illegal_tid');
		$uid = (int)$blogdb['uid'];
		$blogdb['ifhide'] = (int)$blogdb['ifhide'];
		if($blogdb['ifhide'] == '3'){
			$ckpwd = '0';
			list($check_winduid,$check_aid,$check_pwd) = explode("\t",StrCode(GetCookie('alubm_pwd'),'DECODE'));
			$password = $db->get_value("SELECT password FROM pw_albums WHERE aid='$blogdb[aid]'");
			is_numeric($check_winduid) && $check_winduid == $winduid && strlen($check_pwd)>16 && $password == $check_pwd && $check_aid == $blogdb['aid'] && $ckpwd = '1';
		}
		($uid != $winduid && ($blogdb['ifhide']==1 || ($blogdb['ifhide']==2 && strpos(",$blogdb[friends],",",$winduid,")===false) || ($blogdb['ifhide'] == '3' && $ckpwd == '0'))) && Showmsg('sec_photo');
		$blogdb['uicon'] = showfacedesign($blogdb['uicon']);
		$catedb = ${'_'.strtoupper($type)};
		$typename = $ilang['c'.$type];
		$cidname = $catedb[$blogdb['cid']]['name'];
		$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
	}elseif($type == 'music'){
		$maid = GetGP('maid','G');
		(!$maid || !$type) && Showmsg('illegal_tid');
		$blogdb = $db->get_one("SELECT ma.*,u.uid,u.username,u.icon as uicon,u.groupid,u.memberid,u.blogtitle,u.regdate,u.friendview,u.friends,ui.style,ui.bbsuid,ui.domainname,ui.wshownum,ui.pshownum,ui.cshownum,ui.flashurl,ui.headerdb,ui.leftdb,ui.dirdb,ui.malbumdb,ui.introduce,ui.gdcheck,ui.qcheck FROM pw_malbums ma LEFT JOIN pw_user u ON ma.uid=u.uid LEFT JOIN pw_userinfo ui ON ma.uid=ui.uid WHERE ma.ifcheck='1' AND ma.maid='$maid'");
		(empty($blogdb) || !$blogdb['ifcheck']) && Showmsg('illegal_tid');
		$blogdb['uicon'] = showfacedesign($blogdb['uicon']);
		$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
		$typename = $ilang['c'.$type];
		$cidname = $catedb[$malbumdb['cid']]['name'];
		$query = $db->query("SELECT * FROM pw_music WHERE maid='$maid'");
		while($music = $db->fetch_array($query)){
			$musicdb[] = $music;
		}
		empty($musicdb) && $musicdb=array();
	}
} else {
	InitGP(array('bbsuid','username'),'G');
	$uid	= (int)$uid;
	$bbsuid = (int)$bbsuid;
	if ($uid && $uid > 0) {
		$sql = "u.uid='$uid'";
	} elseif ($username && is_string($username)) {
		$sql = "u.username='".Char_cv($username)."'";
	} elseif ($bbsuid && $bbsuid > 0) {
		$sql = "ui.bbsuid='$bbsuid'";
	}
	!$sql && Showmsg('undefined_action');
	$blogdb = ($uid>0) && $uid == $winduid ? $winddb : $db->get_one("SELECT u.*,ui.* FROM pw_user u LEFT JOIN pw_userinfo ui USING(uid) WHERE $sql");
}
empty($blogdb) && Showmsg('no_blog');
$blogdb['friendview'] = (int)$blogdb['friendview'];
($uid != $winduid && ($blogdb['friendview']==1 && strpos(",$blogdb[friends],",",$winduid,")===false)) && Showmsg('sec_blog');
($uid != $winduid && $blogdb['friendview']==2) && Showmsg('hide_blog');
$blogdb['groupid']=='-1' && $blogdb['groupid'] = $blogdb['memberid'];
(int)$blogdb['groupid']<1 && $blogdb['groupid'] = 2;
if (file_exists(D_P."data/groupdb/group_$blogdb[groupid].php")) {
	require_once(Pcv(D_P."data/groupdb/group_$blogdb[groupid].php"));
} else {
	require_once(D_P.'data/groupdb/group_1.php');
}

list($style,$sqlstyle) = explode('|',$blogdb['style']);
require_once(R_P.'mod/user_head.php');

$catetype = $type;
if (!$catetype) {
	$catetype = 'index';
	$type = 'blog';
}
require_once(R_P.'mod/post_mod.php');
require_once(R_P.'mod/side.php');

!$do && $do='list';
if ($do=='list') {
	require_once(R_P.'mod/list.php');
} elseif ($do=='showone') {
	$articlename = $type!='photo' ? $left_name[$type] : $left_name['newalbumn'];
	if($type == 'blog'){
		require_once(R_P.'mod/article.php');
		${$type} = $blogdb;
	}elseif($type == 'photo'){
		require_once(R_P.'mod/particle.php');
	}elseif($type == 'music'){
		require_once(R_P.'mod/marticle.php');
	}
	include_once(getPath('main'));
} elseif ($do=='combine') {
	require_once(R_P.'mod/combine.php');
} elseif ($do=='info') {
	require_once(R_P.'mod/info.php');
} elseif ($do=='aboutme') {
	require_once(R_P.'mod/aboutme.php');
} elseif ($do=='bbs' ) {
	require_once(R_P.'mod/bbs.php');
} else {
	Showmsg('undefined_action');
}
if ($winduid != $uid) {
	$lastvisitdb = $blogdb['lastvisitdb'] ? unserialize($blogdb['lastvisitdb']) : array();
	($side_order['lastvisit']['ifshow'] && !empty($winduid) && $winduid != $lastvisitdb[0]['uid']) && updateusercache($uid,'lastvisitdb');
	if (GetCookie('olip') != md5($onlineip)) {
		$db->update("UPDATE pw_user SET views=views+1 WHERE uid='$uid'");
		Cookie('olip',md5($onlineip),$timestamp+300);
	}
}
($side_order['comment']['ifshow'] && $winduid != $lastvisitdb[0]['uid']) && updateusercache($uid,'commentdb');
footer('getPath');
function updateusercache($uid,$type,$num=null){
	global $db,$windid,$winduid,$windicon;
	$cachedata = array();
	if ($type == 'dirdb') {
		$query = $db->query("SELECT typeid,name,type FROM pw_itemtype WHERE uid='$uid' ORDER BY vieworder");
		while ($rt=$db->fetch_array($query)) {
			$cachedata[$rt['type']][$rt['typeid']] = $rt;
		}
	} elseif ($type == 'commentdb') {
		empty($num) && $num=10;
		$query = $db->query("SELECT id,itemid,type,content FROM pw_comment WHERE uid='$uid' AND itemid!='' AND ifcheck='1' ORDER BY postdate DESC LIMIT 0,$num");
		while ($rt = $db->fetch_array($query)) {
			$rt['content']  = substrs($rt['content'],25);
			$cachedata[$rt['type']][$rt['id']] = array($rt['itemid'],$rt['content']);
		}
	} elseif ($type == 'lastvisitdb') {
		empty($num) && $num=6;
		$lastvisitdb = $db->get_value("SELECT lastvisitdb FROM pw_userinfo WHERE uid='$uid'");
		$lastvisitdb = $lastvisitdb ? unserialize($lastvisitdb) : array();
		$cachearray  = array('uid' => $winduid,'icon' => $windicon,'name' => $windid);
		$cachedata[] = $cachearray;
		if (!empty($lastvisitdb)) {
			foreach ($lastvisitdb as $key => $value) {
				if ($winduid==$value['uid']) {
					$lastvisitdb[$key] = null;
				}
			}
			while (count($lastvisitdb) >= $num) {
				array_pop($lastvisitdb);
			}
			foreach ($lastvisitdb as $value) {
				$cachedata[] = $value;
			}
		}
	}
	if (!empty($cachedata)) {
		Strip_S($cachedata);
		$cachedata = addslashes(serialize($cachedata));
	}
	$db->update("UPDATE pw_userinfo SET {$type}='$cachedata' WHERE uid='$uid'");
}
function getPath($template,$EXT="htm"){
	global $style;
	!file_exists(R_P."theme/$style/template/$template.$EXT") && $style = 'default';
	return R_P."theme/$style/template/$template.$EXT";
}
?>