<?php
!function_exists('N_strireplace') && exit('Forbidden');

require_once(R_P.'mod/passport.php');
(int)$page<1 && $page = 1;
!$db_perpage && $db_perpage = 30;
$db->select_db($db_cbbbsdbname);
$PW = $db_cbbbspre;
if(empty($blogdb[bbsuid])){
	Showmsg('bbsnamefail');
}
$bbssql = " t.authorid=".$blogdb[bbsuid];
$limit = " LIMIT ".($page-1)*$db_perpage.",$db_perpage";
$query = $db->query("SELECT t.tid,t.fid,t.subject,t.postdate,tm.content FROM pw_threads t LEFT JOIN pw_tmsgs tm ON t.tid=tm.tid WHERE$bbssql ORDER BY t.fid,t.postdate DESC$limit");
while($rt = $db->fetch_array($query)){
	$rt['postdate'] = get_date($rt['postdate']);
	$rt['content'] = convert($rt['content']);
	$blogdb['wshownum'] && $rt['content'] = substrs(strip_tags($rt['content']),$blogdb['wshownum']);
	$bbsdb[] = $rt;
}
$count = $db->get_value("SELECT COUNT(*) FROM pw_threads WHERE authorid='$ckbbsuid'");
if ($count > $db_perpage) {
	require_once(R_P.'mod/page_mod.php');
	$pages = page($count,$page,$db_perpage,"blog.php?do=bbs&uid=$uid&");
}
$db->select_db($db_sqlname);
$PW = $db_sqlpre;
include_once(getPath("bbs"));
?>