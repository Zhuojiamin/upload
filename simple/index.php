<?php
define('W_T',__FILE__ ? substr(__FILE__,0,-16) : '../');
require_once(W_T.'global.php');

$db_blogurl = substr($db_blogurl,0,-7);

$tablewidth = '95%';
$i_table = 'bgcolor="#E5E3E3"';
$pagesize = 100;
$title = '';
if ($db_htmifopen && $db_dir && $db_ext) {
	$self_array = explode('-',$db_ext ? substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],$db_ext)) : $_SERVER['QUERY_STRING']);
	for ($i=0;$i<count($self_array);$i++) {
		$_key	= $self_array[$i];
		$_value	= $self_array[++$i];
		!preg_match('/^\_/',$_key) && strlen($GLOBALS[$_key]) < 1 && $GLOBALS[$_key] = addslashes(rawurldecode($_value));
	}
}

$cid = (int)$_GET['cid'];
require_once PrintEot('simple_head');
if ($cid > 0) {
	include_once(R_P.'simple/simple_thread.php');
} elseif ((int)$itemid > 0) {
	include_once(R_P.'simple/simple_read.php');
} elseif ((int)$uid > 0) {
	include_once(R_P.'simple/simple_usershow.php');
} else {
	include_once(R_P.'simple/simple_index.php');
}
$ft_gzip = $db_obstart==1 ? 'Gzip enabled' : 'Gzip disabled';
$wind_spend = '';
if ($db_footertime==1) {
	$db && $qn = $db->query_num;
	$t_array = explode(' ',microtime());
	$totaltime = number_format(($t_array[0]+$t_array[1]-$P_S_T),6);
	$wind_spend = "Time $totaltime second(s),query:$qn";
}
include PrintEot('simple_footer');
$output = str_replace(array('<!--<!---->','<!---->'),'',ob_get_contents());
if ($db_htmifopen) {
	$output = preg_replace(
	"/\<a(\s*[^\>]+\s*)href\=([\"|\']?)([^\"\'>\s]+\.php\?[^\"\'>\s]+)([\"|\']?)/ies",
	"Htm_cv('\\3','<a\\1href=\"')",
	$output
	);
}
ob_end_clean();
echo ObContents($output);
ObFlush();
exit;
function smpage($count,&$page,$per,$url){
	global $tablecolor;
	$ret = '';
	$pre = 3;
	$next = 4;
	$sum = ceil($count/$per);
	$page > $sum && $page = $sum;
	(int)$page<1 && $page = 1;
	if ($sum <= 1){
		return;
	} else {
		$ret = "<a href='$url&page=1'><< </a>";
		$flag = 0;
		for ($i=$page-$pre;$i <= min($sum,$page+$next);$i++) {
			if ($i<1) continue;
			$ret .= $i==$page ? "&nbsp;&nbsp;<b>$page</b>&nbsp;" : " <a href=\"$url&page=$i\">&nbsp;$i&nbsp;</a>";
		}
		$ret .= " <input type=\"text\" size=\"2\" style=\"height: 16px; border:1px solid #E5E5E5;\" onkeydown=\"javascript: if(window.event.keyCode==13) window.location='{$url}'+this.value;\"> <a href=\"$url&page=$sum\"> >></a>&nbsp;&nbsp;Pages: ( $page/$sum total )";
		return $ret;
	}
}
?>