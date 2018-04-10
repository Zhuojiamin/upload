<?php
require_once('global.php');
require_once(R_P.'mod/header_inc.php');
require_once(R_P.'data/cache/hobby_cache.php');
require_once(R_P.'mod/page_mod.php');
$hobbyid = GetGP('hobbyid',G);
$hobbyid=intval($hobbyid);
empty($hobbyid) && Showmsg('no_condition');
@extract($db->get_one("SELECT name as hobbyname,hid FROM pw_hobbyitem WHERE id='$hobbyid'"));
@extract($db->get_one("SELECT COUNT(*) AS count FROM pw_userhobby WHERE hobbyid='$hobbyid'"));
$pages = page($count,$page,$db_perpage,"hobbysc.php?hobbyid=$hobbyid");
(int)$page<1 && $page = 1;
$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
$userdb=array();
$query=$db->query("SELECT h.uid,u.username,u.commend,u.icon,u.qq,u.gender,u.province,u.city,u.msn,u.site,u.regdate,u.thisvisit FROM pw_userhobby h LEFT JOIN pw_user u ON h.uid=u.uid WHERE h.hobbyid='$hobbyid' $limit");
while($rt=$db->fetch_array($query)){
	$rt['thisvisit']=get_date($rt['thisvisit'],'Y-m-d');
	$rt['regdate']=get_date($rt['regdate'],'Y-m-d');
	$rt['icon']=showfacedesign($rt['icon']);
	$userdb[]=$rt;
}
require_once(PrintEot('hobbysc'));footer();
?>