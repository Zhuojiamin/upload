<?php
$lxheader = 'notice';
require_once('global.php');
require_once(R_P.'mod/header_inc.php');
require_once(R_P.'mod/windcode.php');

!$db_perpage && $db_perpage = 30;
(int)$page<1 && $page = 1;
$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";

$notice = array();
$query = $db->query("SELECT aid,vieworder,author,startdate,url,subject,content FROM pw_notice ORDER BY vieworder,startdate DESC $limit");
while ($rt = $db->fetch_array($query)) {
	if (!$rt['url']) {
		$rt['startdate'] = get_date($rt['startdate'],'Y-m-d');
		$rt['content'] = nl2br($rt['content']);
		$notice[] = convert($rt);
	}
}
$db->free_result($query);
require_once(PrintEot('notice'));footer();
?>