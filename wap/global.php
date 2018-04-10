<?php
error_reporting(0);
eval('$__file__=__FILE__;');
define('DIR',getcwd() ? substr(dirname($__file__),0,-3) : './..');
chdir(DIR);

require_once(DIR.'global.php');
require_once(R_P.'wap/wap_mod.php');
include_once(D_P.'data/cache/forum_cache_blog.php');
InitGP(array('cid','tid'));
$cid=(int)$cid;
$tid=(int)$tid;
function Wap_cv($msg){
	$msg = str_replace('&amp;','&',$msg);
	$msg = str_replace('&nbsp;',' ',$msg);
	$msg = str_replace('"','&quot;',$msg);
	$msg = str_replace("'",'&#39;',$msg);
	$msg = str_replace("<","&lt;",$msg);
	$msg = str_replace(">","&gt;",$msg);
	$msg = str_replace("\t","   &nbsp;  &nbsp;",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);
	return $msg;
}
?>