<?php
!function_exists('usermsg') && exit('Forbidden');
$basename .= '&type='.$type;
!$_GROUP['allowpost'] && usermsg('post_right');
!N_InArray($type,array('blog','bookmark','music','photo')) && usermsg('undefined_action');

define('USERPOST',true);
require_once(R_P.'mod/post_mod.php');
include_once(D_P."data/cache/forum_cache_$type.php");
$catedb = (array)${strtoupper('_'.$type)};
list($db_titlemax,$db_postmin,$db_postmax) = explode(',',$db_lenlimit);
$_GROUP['allowupload'] && $db_allowupload = $_GROUP['allowupload'];
$_GROUP['attachsize'] && $db_uploadmaxsize = $_GROUP['attachsize'];
$_GROUP['attachext'] && $db_uploadfiletype = $_GROUP['attachext'];
$_GROUP['uploadnum'] && $db_attachnum = $_GROUP['uploadnum'];
list(,,,$postgd) = explode("\t",$db_gdcheck);
list(,,$postq) = explode("\t",$db_qcheck);
$items = (int)$admindb['items'] + (int)$admindb['albums'] + (int)$admindb['musics'];
($postq && ($items < $postq)) ? $postq='1' : $postq='0';
if ($_POST['step']!=2) {
	$forumcache = $itemcache = '';
	$uploaddb = $itemarray = $tagname = $tagdb = $tagcltdb = array();
	$tagdisplay = 'none;';
	for ($i=0; $i<8; $i++) {
		${'icon_'.$i} = '';
	}
	$dirdb = $dirdb = $admindb['dirdb'] ? unserialize($admindb['dirdb']) : array();
	$dirdb = (array)$dirdb[$type];
	$albumdb = $admindb['albumdb'] ? unserialize($admindb['albumdb']) : array();
	$malbumdb = $admindb['malbumdb'] ? unserialize($admindb['malbumdb']) : array();
	$tagdb	  = gethottag($type.'num',5);
	$tagcltdb = getclttag($admin_uid,8);
	$teamsel  = getteamop($admin_uid);
	if ($items < $postgd) {
		$rawwindid = (!$admin_name) ? 'guest' : rawurlencode($admin_name);
		$ckurl = str_replace('?','',$ckurl);
	} else {
		$rawwindid = $ckurl = '';
	}
} else {
	require_once(R_P.'mod/windcode.php');
	require_once(R_P.'mod/ipfrom_mod.php');
	require_once(R_P.'mod/upload_mod.php');
	@include(D_P.'data/cache/wordfb.php');
	!is_array($_REPLACE) && $_REPLACE = array();
	!is_array($_FORBID) && $_FORBID = array();
	foreach ($_FORBID as $value) {
		(N_stripos($_POST['atc_title'],$value['word']) || N_stripos($_POST['atc_content'],$value['word'])) && usermsg('word_ban');
	}
	$_FORBIDDB = $_REPLACE+$_FORBID;
}
!$job && $job = 'add';

if ($job=='add') {
	require_once(R_P.'mod/postnew.php');
} elseif ($job=='modify') {
	$itemid = (int)GetGP('itemid');
	require_once(R_P.'mod/postmodify.php');
} else {
	usermsg('undefined_action');
}

function updatecache_cate($catetype = null){
	global $db;
	$where = !empty($catetype) ? "WHERE catetype='$catetype'" : '';
	$db->update("UPDATE pw_categories SET type='0',cupinfo='' $where".($where ? " AND" : "WHERE")." cup='0'");
	$catedb = $subdb = array();
	$typedb = array('blog','bookmark','file','goods','music','photo','team','user');
	$query = $db->query("SELECT cid,cup,type,name,descrip,cupinfo,counts,vieworder,_ifshow,catetype,fid FROM pw_categories $where ORDER BY vieworder,cid");
	while ($rt = $db->fetch_array($query)) {
		if (in_array($rt['catetype'],$typedb)) {
			P_unlink(D_P."data/cache/cate_cid_$rt[cid].php");
			!is_array(${'_'.$rt['catetype']}) && ${'_'.$rt['catetype']} = array();
			$rt['name']	   = Char_cv($rt['name'],'N');
			$rt['descrip'] = Char_cv($rt['descrip'],'N');
			if ($rt['cup'] == 0) {
				$catedb[] = $rt;
			} else {
				$subdb[$rt['cup']][] = $rt;
			}
		}
	}
	foreach ($catedb as $cate) {
		if (empty($cate)) continue;
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($subdb[$cate['cid']])) continue;
		${'_'.$cate['catetype']} += get_subcate($subdb,$cate['cid']);
	}
	foreach ($typedb as $value) {
		if (!empty($catetype) && $value != $catetype) {
			continue;
		}
		$writecache = '$_'.strtoupper($value).' = '.N_var_export(${'_'.$value}).";\r\n";
		writeover(D_P."data/cache/forum_cache_{$value}.php","<?php\r\n$writecache?>");
	}
}
function get_subcate($array,$cid){
	global $db;
	static $type = 0;
	static $cupinfo = null;
	$type++;
	$cupinfo .= (empty($cupinfo) ? '' : ',').$cid;
	foreach ($array[$cid] as $cate) {
		if (empty($cate)) continue;
		$sql = '';
		if ($cate['type'] != $type) {
			$cate['type'] = $type;
			$sql .= "type='$type'";
		}
		if ($cate['cupinfo'] != $cupinfo) {
			$cate['cupinfo'] = $cupinfo;
			$sql && $sql .= ',';
			$sql .= "cupinfo='$cupinfo'";
		}
		$sql && $db->update("UPDATE pw_categories SET $sql WHERE cid='$cate[cid]'");
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($array[$cate['cid']])) {
			$type = 0;
			$cupinfo = null;
			continue;
		}
		${'_'.$cate['catetype']} += get_subcate($array,$cate['cid']);
	}
	return ${'_'.$cate['catetype']};
}
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