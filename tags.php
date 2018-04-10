<?php
require_once('global.php');

$tagname = GetGP('tagname','G');
!$tagname && Showmsg('undefined_action');

$tagnameurl = rawurlencode($tagname);
$lxheader = 'tags';
$hlang['tags']['url'] = "tags.php?tagname=$tagnameurl";

require_once(R_P.'mod/header_inc.php');
$tagdb = array();
$sqlwhere = $sblog = $sphoto = $smusic = $sbookmark = $pages = '';
if ($type) {
	${'s'.$type} = ' SELECTED';
	$sqlwhere = " AND tagtype='$type'";
}else{
	$sblog = ' SELECTED';
	$sqlwhere = " AND tagtype='blog'";
}
$taghash = substr(md5("{$db_hash}tags{$tagname}"),0,10);
!$db_perpage && $db_perpage = 30;
(int)$page<1 && $page = 1;
$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
empty($type) && $type = 'blog';
if($type == 'blog' || $type == 'bookmark'){
	$query = $db->query("SELECT itemid,uid,author,tagtype,subject,addtime FROM pw_taginfo WHERE tagname='$tagname'$sqlwhere ORDER BY addtime DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['addtime'] = get_date($rt['addtime'],'Y-m-d');
		$tagdb[] = $rt;
	}
}elseif($type == 'photo'){
	$query = $db->query("SELECT p.pid,p.cid,p.aid,p.name,t.uid,t.author,t.addtime,t.tagtype FROM pw_photo p LEFT JOIN pw_taginfo t ON p.pid=t.itemid WHERE t.tagname='$tagname'$sqlwhere ORDER BY addtime DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['addtime'] = get_date($rt['addtime'],'Y-m-d');
		$tagdb[] = $rt;
	}
}elseif($type == 'music'){
	$query = $db->query("SELECT m.mid,m.maid,m.name,t.uid,t.author,t.addtime,t.tagtype FROM pw_music m LEFT JOIN pw_taginfo t ON m.mid=t.itemid WHERE t.tagname='$tagname'$sqlwhere ORDER BY addtime DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['addtime'] = get_date($rt['addtime'],'Y-m-d');
		$tagdb[] = $rt;
	}
}

$db->free_result($query);
$count = $db->get_value("SELECT COUNT(*) FROM pw_taginfo WHERE tagname='$tagname'$sqlwhere");
if ($count > $db_perpage) {
	require_once(R_P.'mod/page_mod.php');
	$pages = page($count,$page,$db_perpage,"cate.php?type=$type&cid=$cid&");
}
if (!empty($tagdb) && $timestamp - @filemtime(D_P."data/cache/tags_$taghash.php") > 3600) {
	$writecache = '';
	$query = $db->query("SELECT tagname FROM pw_btags WHERE tagname!='' ORDER BY allnum DESC LIMIT 0,10");
	while ($rt = $db->fetch_array($query)) {
		$rt['tagnameurl'] = rawurlencode($rt['tagname']);
		$_HOTTAG[] = $rt;
	}
	$db->free_result($query);
	$writecache .= '$_HOTTAG = '.N_var_export($_HOTTAG).";\r\n";
	writeover(D_P."data/cache/tags_$taghash.php","<?php\r\n$writecache?>");
}
@include_once Pcv(D_P."data/cache/tags_$taghash.php");
require_once PrintEot('tags');footer();
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