<?php
require_once('global.php');
require_once(R_P.'mod/header_inc.php');
require_once(R_P.'template/default/wind/lang_user.php');
(!$cid || !$type) && Showmsg('undefined_action');

$count = 0;
$itemdb = array();
$pages = $cids = '';
include_once Pcv(D_P."data/cache/forum_cache_$type.php");
$catedb = ${'_'.strtoupper($type)};
$typename = $ilang['c'.$type];
$cidname = $catedb[$cid]['name'];
foreach ($catedb as $value) {
	if ($value['cid']==$cid || $value['cup']==$cid) {
		$cids .= ($cids ? ',' : '')."'$value[cid]'";
		$count += $catedb[$value['cid']]['counts']+0;
	}
}
unset($catedb);
!$cids && Showmsg('undefined_action');
$cids = strpos($cids,',')!==false ? " IN ($cids)" : "=$cids";
!$db_perpage && $db_perpage = 30;
(int)$page<1 && $page = 1;
$limit = 'LIMIT '.($page-1)*$db_perpage.','.$db_perpage;
if($type == 'blog' || $type == 'bookmark'){
	$query = $db->query("SELECT itemid,uid,author,subject,postdate FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND cid$cids ORDER BY postdate DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
		$itemdb[] = $rt;
	}
	$db->free_result($query);
	if ($count > $db_perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"cate.php?type=$type&cid=$cid&");
	}
	$_HOTCATE = $_DIGESTCATE = array();
	if (!empty($itemdb) && !file_exists(D_P."data/cache/cate_cid_$cid.php")) {
		$writecache = '';
		$query = $db->query("SELECT itemid,author,subject FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND cid$cids ORDER BY hits DESC LIMIT 0,10");
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'],15);
			$_HOTCATE[] = $rt;
		}
		$query = $db->query("SELECT itemid,author,subject FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND digest<>'0' AND cid$cids ORDER BY digest DESC LIMIT 0,10");
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'],15);
			$_DIGESTCATE[] = $rt;
		}
		$db->free_result($query);
		$writecache .= '$_HOTCATE = '.N_var_export($_HOTCATE).";\r\n";
		$writecache .= '$_DIGESTCATE = '.N_var_export($_DIGESTCATE).";\r\n";
		writeover(D_P."data/cache/cate_cid_$cid.php","<?php\r\n$writecache?>");
	}	
}elseif($type == 'photo'){
	$job = GetGP('job','G');
	if($job == 'plist'){
		InitGP(array('aid','page'));
		$db_perpage = 16;
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT p.*,a.subject FROM pw_photo p LEFT JOIN pw_albums a ON p.aid=a.aid WHERE p.aid='$aid' ORDER BY p.uploadtime DESC $limit");
		while($photo = $db->fetch_array($query)){
			$photo['name'] = substrs($photo['name'],15);
			$photo['descrip'] = substrs($photo['descrip'],15);
			$photo['uploadtime'] = date('Y-m-d',$photo['uploadtime']);
			$photo['ifthumb'] && $photo['attachurl'] = str_replace('.','_thumb.',$photo['attachurl']);
				!$photo['attachurl'] && $photo['attachurl'] = 'none.gif';
				if (file_exists(R_P."$attachpath/$photo[attachurl]")) {
					$photo['attachurl'] = "$attachpath/$photo[attachurl]";
				} elseif (file_exists($attach_url/$photo[attachurl])) {
					$photo['attachurl'] = "$attach_url/$photo[attachurl]";
				} else {
					$photo['attachurl'] = "$attachpath/none.gif";
				}
			$photo['ifhpagestyle'] = $photo['ifhpage'] == 1 ? '' : 'style="display:none"';
			$album_name = $photo['subject'];
			$photos[] = $photo;
		}
		$count = $db->get_value("SELECT COUNT(*) FROM pw_photo WHERE  aid='$aid'");
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"cate.php?type=$type&job=plist&cid=$cid&aid=$aid&$addpage");
		}
	}else{
		$query = $db->query("SELECT a.aid,a.uid,a.author,a.subject,a.postdate,a.hpagepid,a.descrip,p.attachurl FROM pw_albums a LEFT JOIN pw_photo p ON a.hpagepid=p.pid WHERE a.ifcheck='1' AND a.ifhide='0' AND a.cid$cids ORDER BY a.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['attachurl'] =  $rt['attachurl'] ? $attachpath.'/'.$rt[attachurl] : $imgpath.'/nopic.jpg';
			$rt['subject'] = substrs($rt['subject'],15);
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$itemdb[] = $rt;
		}
		$db->free_result($query);
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"cate.php?type=$type&cid=$cid&");
		}
		$_HOTCATE = $_DIGESTCATE = array();
		if (!empty($itemdb) && !file_exists(D_P."data/cache/cate_cid_$cid.php")) {
			$writecache = '';
			$query = $db->query("SELECT aid,author,subject FROM pw_albums WHERE ifcheck='1' AND ifhide='0' AND cid$cids ORDER BY hits DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['subject'] = substrs($rt['subject'],15);
				$_HOTCATE[] = $rt;
			}
			$query = $db->query("SELECT aid,author,subject FROM pw_albums WHERE ifcheck='1' AND ifhide='0' AND cid$cids ORDER BY digest DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['subject'] = substrs($rt['subject'],15);
				$_DIGESTCATE[] = $rt;
			}
			$db->free_result($query);
			$writecache .= '$_HOTCATE = '.N_var_export($_HOTCATE).";\r\n";
			$writecache .= '$_DIGESTCATE = '.N_var_export($_DIGESTCATE).";\r\n";
			writeover(D_P."data/cache/cate_cid_$cid.php","<?php\r\n$writecache?>");
		}
	}
}elseif($type == 'music'){
		$query = $db->query("SELECT maid,uid,author,subject,postdate,hpageurl FROM pw_malbums WHERE ifcheck='1' AND cid$cids ORDER BY postdate DESC $limit");
		while($rt = $db->fetch_array($query)){
			$rt[subject] = substrs($rt['subject'],15);
			$rt['postdate'] = date('Y-m-d',$rt['postdate']);
			$rt['hpageurl'] = showhpageurl($rt['hpageurl']);
			$malbumdb[] = $rt;
		}
		$db->free_result($query);
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"cate.php?type=$type&cid=$cid&");
		}
		$_HOTCATE = $_DIGESTCATE = array();
		if (!empty($malbumdb) && !file_exists(D_P."data/cache/cate_cid_$cid.php")) {
			$writecache = '';
			$query = $db->query("SELECT maid,author,subject FROM pw_malbums WHERE ifcheck='1' AND cid$cids ORDER BY hits DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['subject'] = substrs($rt['subject'],15);
				$_HOTCATE[] = $rt;
			}
			$query = $db->query("SELECT maid,author,subject FROM pw_malbums WHERE ifcheck='1' AND cid$cids ORDER BY digest DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['subject'] = substrs($rt['subject'],15);
				$_DIGESTCATE[] = $rt;
			}
			$db->free_result($query);
			$writecache .= '$_HOTCATE = '.N_var_export($_HOTCATE).";\r\n";
			$writecache .= '$_DIGESTCATE = '.N_var_export($_DIGESTCATE).";\r\n";
			writeover(D_P."data/cache/cate_cid_$cid.php","<?php\r\n$writecache?>");
		}
}
@include_once Pcv(D_P."data/cache/cate_cid_$cid.php");
require_once PrintEot('cate');footer();
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

function showhpageurl($hpageurl){
	global $attachpath,$imgpath;
	if (!$hpageurl) {
		return $imgpath.'/nopic.jpg';
	} elseif (preg_match('/^http/i',$hpageurl)) {
		return $hpageurl;
	} else {
		return $attachpath.'/'.$hpageurl;
	}
}
?>