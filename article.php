<?php
require_once('global.php');
$phpfile = 'cate';
if (!$db_articleurl) {
	$urladd = $type == 'photo' ? "pid=$pid" : ($type == 'music' ? "maid=$maid" : "itemid=$itemid");
	ObHeader("blog.php?do=showone&type=$type&$urladd");exit;
}


require_once(R_P.'mod/windcode.php');
require_once(R_P.'template/default/wind/lang_user.php');
@include_once Pcv(D_P."data/cache/forum_cache_$type.php");
@include(D_P.'data/cache/wordfb.php');

if($type == 'blog' || $type == 'bookmark'){
	$itemid = GetGP('itemid','G');
	$cid = GetGP('cid','G');
	(!$itemid || !$type) && Showmsg('illegal_tid');
	$blogdb = $db->get_one("SELECT t.*,i.itemid,i.cid,i.dirid,i.icon,i.subject,i.postdate,i.hits,i.replies,i.digest,i.allowreply,i.ifhide,i.ifwordsfb,i.ifcheck,i.footprints,i.uploads,i.cmttext,u.uid,u.username,u.icon as uicon,u.friends,ui.style,ui.bbsuid,ui.domainname,ui.wshownum,ui.pshownum,ui.cshownum,ui.dirdb,ui.gdcheck,ui.qcheck FROM pw_items i LEFT JOIN pw_$type t ON i.itemid=t.itemid LEFT JOIN pw_user u ON i.uid=u.uid LEFT JOIN pw_userinfo ui ON i.uid=ui.uid WHERE i.itemid='$itemid'");
	(empty($blogdb) || !$blogdb['ifcheck']) && Showmsg('illegal_tid');
	$uid = (int)$blogdb['uid'];
	$bbsuid = (int)$blogdb['bbsuid'];
	$title = $blogdb['subject'];
	
	if ($type == 'music') {
			$blogdb['musicurl'] = (unserialize($blogdb['musicurl']) ? unserialize($blogdb['musicurl']) : array());
			foreach ($blogdb['musicurl'] as $key => $value) {
				if (eregi("\.(rm|rmvb|ra|ram)$",$value['url'])) {
					$blogdb['musicurl'][$key]['type'] = 'rm';
				} elseif (eregi("\.(qt|mov|4mv)$",$value['url'])) {
					$blogdb['musicurl'][$key]['type'] = 'qt';
				} else {
					$blogdb['musicurl'][$key]['type'] = 'wmv';
				}
			}
	}
	
	$blogdb['ifhide'] = (int)$blogdb['ifhide'];
	($uid != $winduid && ($blogdb['ifhide']==1 || ($blogdb['ifhide']==2 && strpos(",$blogdb[friends],",",$winduid,")===false))) && Showmsg('sec_blog');
	$upload = $attachdb = $attachmentdb = array();
	$blogdb['uicon'] = showfacedesign($blogdb['uicon']);
	$catedb = ${'_'.strtoupper($type)};
	$typename = $ilang['c'.$type];
	$cidname = $catedb[$blogdb['cid']]['name'];
	$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;

if($db_setassociate == '1'){
	$blogdb['tagsdb'] = array();
	if ($blogdb['tags']) {
		$taginfo = array_unique(explode(',',$blogdb['tags']));
		foreach ($taginfo as $key => $value) {
			if ($value) {
				$tagname = Char_cv($value);
				$blogdb['tagsdb'][$key] = array('name' => $value,'tagname' => $tagname);
			} else {
				unset($blogdb['tagsdb'][$key]);
			}
		}
	}
	if(!empty($blogdb[tagsdb])){
		$tags = '';
		foreach($blogdb['tagsdb'] as $key => $value){
			$tags .= ($tags ? ',' : '')."'$value[tagname]'";
		}
		$tags = strpos($tags,',')!==false ? " IN ($tags)" : "=$tags";
		$query = $db->query("SELECT itemid,author,subject,addtime FROM pw_taginfo WHERE tagname{$tags} AND itemid!='{$blogdb['itemid']}' AND tagtype='blog' ORDER BY addtime ASC LIMIT 10");
		while($rt = $db->fetch_array($query)){
			$rt['subject'] = substrs($rt['subject'],30);
			$rt['addtime'] = date('Y-m-d', $rt['addtime']);
			$tagsubject[] = $rt;
		}
		!is_array($tagsubject) && $tagsubject = array();
	}
}
	@include_once Pcv(D_P."data/cache/cate_cid_$blogdb[cid].php");
	require_once(R_P.'mod/article.php');
}elseif($type == 'photo'){
	$pid = GetGP('pid','G');
	(!$pid || !$type) && Showmsg('illegal_tid');
	$photodb = $db->get_one("SELECT p.*,a.aid,a.cid,a.subject,a.postdate,a.photos,a.replies,a.digest,a.allowreply,a.ifhide,a.ifwordsfb,a.ifcheck,a.ifconvert,a.footprints,u.uid,u.username,u.icon as uicon,u.friends,ui.style,ui.bbsuid,ui.domainname,ui.wshownum,ui.pshownum,ui.cshownum,ui.albumdb,ui.gdcheck,ui.qcheck FROM pw_photo p LEFT JOIN pw_albums a ON p.aid=a.aid LEFT JOIN pw_user u ON p.uid=u.uid LEFT JOIN pw_userinfo ui ON p.uid=ui.uid WHERE p.pid='$pid'");
	(empty($photodb) || !$photodb['ifcheck']) && Showmsg('illegal_tid');
	$uid = (int)$photodb['uid'];
	$photodb['ifhide'] = (int)$photodb['ifhide'];
	($uid != $winduid && ($photodb['ifhide']==1 || ($photodb['ifhide']==2 && strpos(",$photodb[friends],",",$winduid,")===false) || $photodb['ifhide'] == '3')) && Showmsg('sec_blog');
	$photodb['uicon'] = showfacedesign($photodb['uicon']);
	$catedb = ${'_'.strtoupper($type)};
	$typename = $ilang['c'.$type];
	$cidname = $catedb[$photodb['cid']]['name'];
	$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
	@include_once Pcv(D_P."data/cache/cate_cid_$photodb[cid].php");
	require_once(R_P.'mod/particle.php');
}elseif($type == 'music'){
	$maid = GetGP('maid','G');
	(!$maid || !$type) && Showmsg('illegal_tid');
	$malbumdb = $db->get_one("SELECT ma.*,u.uid,u.username,u.icon as uicon,ui.domainname,ui.commentdb,ui.gdcheck,ui.qcheck FROM pw_malbums ma LEFT JOIN pw_user u ON ma.uid=u.uid LEFT JOIN pw_userinfo ui ON ma.uid=ui.uid WHERE ma.ifcheck='1' AND ma.maid='$maid'");
	(empty($malbumdb) || !$malbumdb['ifcheck']) && Showmsg('illegal_tid');
	$malbumdb['uicon'] = showfacedesign($malbumdb['uicon']);
	$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
	$typename = $ilang['c'.$type];
	$cidname = $catedb[$malbumdb['cid']]['name'];
	$query = $db->query("SELECT * FROM pw_music WHERE maid='$maid'");
	while($music = $db->fetch_array($query)){
		if (eregi("\.(rm|rmvb|ra|ram)$",$music['murl'])) {
			$music['type'] = 'rm';
		} elseif (eregi("\.(qt|mov|4mv)$",$music['murl'])) {
			$music['type'] = 'qt';
		} else {
			$music['type'] = 'wmv';
		}
		$musicdb[] = $music;
	}
	empty($musicdb) && $musicdb=array();
	@include_once Pcv(D_P."data/cache/cate_cid_$malbumdb[cid].php");
	require_once(R_P.'mod/marticle.php');
}
require_once(R_P.'mod/header_inc.php');

if (!empty($blogdb) && !file_exists(D_P."data/cache/cate_cid_$blogdb[cid].php")) {
	$writecache = $cids = '';
	foreach ($catedb as $value) {
		if ($value['cid']==$blogdb['cid'] || $value['cup']==$blogdb['cid']) {
			$cids .= ($cids ? ',' : '')."'$value[cid]'";
		}
	}
	$cids = strpos($cids,',')!==false ? " IN ($cids)" : "=$cids";
	$query = $db->query("SELECT itemid,author,subject FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND cid$cids ORDER BY hits DESC LIMIT 0,10");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],15);
		$_HOTCATE[] = $rt;
	}
	$query = $db->query("SELECT itemid,author,subject FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND cid$cids ORDER BY digest DESC LIMIT 0,10");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],15);
		$_DIGESTCATE[] = $rt;
	}
	$db->free_result($query);
	$writecache .= '$_HOTCATE = '.N_var_export($_HOTCATE).";\r\n";
	$writecache .= '$_DIGESTCATE = '.N_var_export($_DIGESTCATE).";\r\n";
	writeover(D_P."data/cache/cate_cid_$cid.php","<?php\r\n$writecache?>");
}

require_once PrintEot('article');footer();
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