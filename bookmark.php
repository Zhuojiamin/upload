<?php
require_once('global.php');
require_once(R_P.'mod/header_inc.php');

!$db_perpage && $db_perpage = 30;
(int)$page<1 && $page = 1;
$limit = 'LIMIT '.($page-1)*$db_perpage.','.$db_perpage;
$count = $db->get_value("SELECT count(*) FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND type='bookmark'");
$query = $db->query("SELECT i.itemid,i.uid,i.author,i.subject,i.postdate,i.hits,b.bookmarkurl,b.content FROM pw_items i LEFT JOIN pw_bookmark b ON i.itemid=b.itemid WHERE i.ifcheck='1' AND i.ifhide='0' AND i.type='bookmark' ORDER BY postdate DESC $limit");
while ($rt = $db->fetch_array($query)) {
	$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
	$bookmarkdb[] = $rt;
}
$db->free_result($query);
if ($count > $db_perpage) {
	require_once(R_P.'mod/page_mod.php');
	$pages = page($count,$page,$db_perpage,"bookmark.php?");
}
$_HOTBOOKMARK = $_DIGESTBOOKMARK = array();
if (!empty($bookmarkdb) && !file_exists(D_P."data/cache/bookmarkcache.php")) {
	$writecache = '';
	$query = $db->query("SELECT i.itemid,i.hits,i.subject,bm.bookmarkurl FROM pw_items i LEFT JOIN pw_bookmark bm ON i.itemid=bm.itemid WHERE i.ifcheck='1' AND i.ifhide='0' AND i.type='bookmark' ORDER BY i.hits DESC LIMIT 0,10");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],15);
		$_HOTBOOKMARK[] = $rt;
	}
	$query = $db->query("SELECT itemid,hits,subject FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND digest<>'0' AND type='bookmark' ORDER BY digest DESC LIMIT 0,10");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],15);
		$_DIGESTBOOKMARK[] = $rt;
	}
	$db->free_result($query);
	$writecache .= '$_HOTBOOKMARK = '.N_var_export($_HOTBOOKMARK).";\r\n";
	$writecache .= '$_HOTBOOKMARK = '.N_var_export($_HOTBOOKMARK).";\r\n";
	writeover(D_P."data/cache/bookmarkcache.php","<?php\r\n$writecache?>");
}
@include_once Pcv(D_P."data/cache/bookmarkcache.php");
require_once PrintEot('bookmark');footer();

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
?>