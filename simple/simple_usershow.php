<?php
!function_exists('readover') && exit('Forbidden');

$readdb = array();
$page = $pages = '';
$username = $db->get_value("SELECT username FROM pw_user WHERE uid='$uid'");
if (!$username) {
	$errorname = $ilang['lgusername'];
	$errorvalue = '';
	Showmsg('user_not_exists');
}
$count = $db->get_value("SELECT COUNT(*) AS sum FROM pw_items WHERE uid='$uid' AND ifcheck='1'");
$pages = smpage($count,$page,$pagesize,"simple/index.php?uid=$uid");
$start = $pagesize*($page-1);
if($count > 0){
	$query = $db->query("SELECT itemid,type,replies,subject FROM pw_items WHERE uid='$uid' AND ifcheck='1' ORDER BY postdate DESC LIMIT $start,$pagesize");
	while ($rt = $db->fetch_array($query)) {
		$readdb[] = $rt;
	}
	$db->free_result($query);
}
require_once PrintEot('simple_userread');
?>