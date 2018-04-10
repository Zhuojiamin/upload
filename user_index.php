<?php
error_reporting(E_ERROR | E_PARSE);

@ini_set('zend.ze1_compatibility_mode',false);
@ini_set('magic_quotes_runtime',false);

$t_array	= explode(' ',microtime());
$P_S_T  	= $t_array[0]+$t_array[1];
$timestamp  = time();
define('R_P',getdirname(__FILE__));
define('D_P',R_P);
!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$user_file = $_SERVER['PHP_SELF'];
require_once(R_P.'user/global.php');
require_once(R_P.'user/top.php');

if (!$action) {
	@include_once(D_P.'data/cache/novosh_cache.php');
	include_once(D_P."data/cache/forum_cache_blog.php");
	$categpslt = '';
	$fcid = 0;
	foreach ($_BLOG as $key => $value) {
		!$fcid && $fcid = $value['cid'];
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$categpslt .= "<option value=\"$value[cid]\">$add $value[name]</option>";
	}
	list(,,,$postgd) = explode("\t",$db_gdcheck);
	list(,,$postq) = explode("\t",$db_qcheck);
	$items = (int)$admindb['items'] + (int)$admindb['albums'] + (int)$admindb['musics'];
	($postq && ($items < $postq)) ? $postq='1' : $postq='0';
	if ($admindb['items'] < $postgd) {
		$rawwindid = (!$admin_name) ? 'guest' : rawurlencode($admin_name);
		$ckurl = str_replace('?','',$ckurl);
	} else {
		$rawwindid = $ckurl = '';
	}
	
	$usersizes = $db->get_value("SELECT SUM(size) FROM pw_upload WHERE uid='$admin_uid'");
	$usersizes = (int)$usersizes;
	$commentdb = $itemdb = array();
	$query = $db->query("SELECT id,content FROM pw_gbook WHERE uid='$admin_uid' ORDER BY postdate DESC LIMIT 0,5");
	while ($rt = $db->fetch_array($query)) {
		$rt['content'] = substrs($rt['content'],50);
		$commentdb[] = $rt;
	}
	$query = $db->query("SELECT itemid,subject,type FROM pw_items WHERE uid='$admin_uid' AND ifcheck='1' AND type='blog' ORDER BY postdate DESC LIMIT 0,5");
	while ($rt = $db->fetch_array($query)) {
		$itemdb[] = $rt;
	}
	$db->free_result($query);
	$items = (int)$admindb['items'];
	$comments = (int)$admindb['comments'];
	$dirs = 0;
	$dirdb = $admindb['dirdb'] ? unserialize($admindb['dirdb']) : array();
	foreach ($dirdb as $key => $value) {
		$dirs += count($value);
	}
	foreach ($_NOTICE as $key => $value) {
		$_NOTICE[$key]['startdate'] = get_date($value['startdate']);
	}
	list($db_titlemax,$db_postmin,$db_postmax) = explode(',',$db_lenlimit);
	require_once PrintEot('index');footer();
} elseif ($action && file_exists(R_P."user/$action.php")) {
	$basename = "$user_file?action=$action";
	require_once(Pcv(R_P."user/$action.php"));
}
function getdirname($path){
	if (strpos($path,'\\')!==false) {
		return substr($path,0,strrpos($path,'\\')).'/';
	} elseif (strpos($path,'/')!==false) {
		return substr($path,0,strrpos($path,'/')).'/';
	} else {
		return './';
	}
}
?>