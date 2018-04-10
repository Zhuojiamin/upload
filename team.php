<?php
require_once('global.php');
require_once(R_P.'mod/header_inc.php');
require_once(R_P.'template/default/wind/lang_user.php');
//$teamid = GetGP('teamid','G');
InitGP(array('cid','teamid','job'),'G');
(!$cid && !$teamid) && Showmsg('undefined_action');
//$teamid && $cid = 0;
$count = 0;
$teamdb = array();
$pages = $cids = $ifteammember='';
include_once(D_P.'data/cache/forum_cache_team.php');
$typename = $ilang['team'];
!$db_perpage && $db_perpage = 30;
(int)$page<1 && $page = 1;
$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
$winddb_teamdb_tmp = unserialize($winddb[teamdb]);
empty($winddb_teamdb_tmp) && $winddb_teamdb_tmp=array();
foreach($winddb_teamdb_tmp as $key => $value){
	$winddb_teamdb[] = $key;
}
if ((int)$teamid > 0) {
	$ifshow = $db->get_one("SELECT uid AS tadminid,ifshow FROM pw_team WHERE teamid='$teamid'");
	$query = $db->query("SELECT uid FROM pw_tuser WHERE teamid='$teamid' AND ifcheck='1'");
	while($rt = $db->fetch_array($query)){
		$tuserdb[] = $rt[uid];
	}
	$db->free_result($query);
	empty($tuserdb) && $tuserdb=array();
	if($ifshow[ifshow] == '1' && !in_array($winduid,$tuserdb)){
		Showmsg('not_join');
	}elseif($ifshow[ifshow] == '2' && empty($winduid)){
		Showmsg('not_login');
	}elseif($ifshow[ifshow] == '0' && (empty($winduid) || $winduid != $ifshow[tadminid])){
		Showmsg('qhidden');
	}
	$winduid && in_array($winduid,$tuserdb) && $ifteammember = '1';
	$tblogdb = $talbumdb = $tmalbumdb = $tuserdb = $_WRITER = $_NEWUSER = array();
	$teamdb = $db->get_one("SELECT teamid,cid,uid,username,name,descrip,icon,items,blogs,albums,malbums,bloggers FROM pw_team WHERE teamid='$teamid'");
	!$teamdb['icon'] && $teamdb['icon'] = 'nopic.jpg';
	$cidname = $_TEAM[$teamdb['cid']]['name'];
	unset($_TEAM);
	$tbloghash = substr(md5("{$db_hash}tags{$cidname}{$teamdb[name]}"),0,10);
	if($job == 'blog'){
		@include_once Pcv(D_P."data/cache/team_$tbloghash.php");
		$query = $db->query("SELECT tb.*,u.username,i.cid AS blogcid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_items i ON tb.itemid=i.itemid WHERE tb.teamid='$teamid' AND tb.type='blog' ORDER BY tb.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['subject'] = substrs($rt['subject'],50);
			$tblogdb[] = $rt;
		}
		if ($teamdb['blogs'] > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($teamdb['blogs'],$page,$db_perpage,"team.php?job=blog&cid=$cid&teamid=$teamid&");
		}
		$indexname = $index_name['blog'];
		$_TOP = $_TOPBLOG;
		$_CMD = $_CMDBLOG;
	}elseif($job == 'album'){
		@include_once Pcv(D_P."data/cache/team_$tbloghash.php");
		$query = $db->query("SELECT tb.*,u.username,p.attachurl,p.ifthumb,p.cid AS albumcid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_photo p ON tb.itemid=p.aid WHERE tb.teamid='$teamid' AND tb.type='photo' ORDER BY tb.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
			!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
			if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
				$rt['attachurl'] = "$attachpath/$rt[attachurl]";
			} elseif (file_exists($attach_url/$rt[attachurl])) {
				$rt['attachurl'] = "$attach_url/$rt[attachurl]";
			} else {
				$rt['attachurl'] = "$attachpath/none.gif";
			}
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['subject'] = substrs($rt['subject'],20);
			$talbumdb[] = $rt;
		}
		if ($teamdb['blogs'] > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($teamdb['albums'],$page,$db_perpage,"team.php?job=album&cid=$cid&teamid=$teamid&");
		}
		$indexname = $index_name['photo'];
		$_TOP = $_TOPALBUM;
		$_CMD = $_CMDALBUM;
	}elseif($job == 'malbum'){
		@include_once Pcv(D_P."data/cache/team_$tbloghash.php");
		$query = $db->query("SELECT tb.*,u.username,ma.hpageurl,ma.cid AS malbumcid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_malbums ma ON tb.itemid=ma.maid WHERE tb.teamid='$teamid' AND type='music' ORDER BY tb.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['hpageurl'] = showhpageurl($rt['hpageurl']);
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$rt['subject'] = substrs($rt['subject'],20);
			$tmalbumdb[] = $rt;
		}
		if ($teamdb['malbums'] > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($teamdb['malbums'],$page,$db_perpage,"team.php?job=malbum&cid=$cid&teamid=$teamid&");
		}
		$indexname = $index_name['music'];
		$_TOP = $_TOPMALBUM;
		$_CMD = $_CMDMALBUM;
	}elseif($job == 'user'){
		@include_once Pcv(D_P."data/cache/team_$tbloghash.php");
		$query = $db->query("SELECT tu.uid,u.username,u.icon FROM pw_tuser tu LEFT JOIN pw_user u ON tu.uid=u.uid WHERE tu.teamid='$teamid' AND tu.ifcheck='1' ORDER BY tu.joindate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['icon'] = showfacedesign($rt['icon']);
			$tuserdb[] = $rt;
		}
		if ($teamdb['bloggers'] > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($teamdb['bloggers'],$page,$db_perpage,"team.php?job=user&cid=$cid&teamid=$teamid&");
		}
	}elseif($job == 'showteam'){
		if ($timestamp - @filemtime(D_P."data/cache/team_$tbloghash.php") > 3600) {
			$writecache = '';
			//最新日志缓存
			$query = $db->query("SELECT tb.*,u.username,i.cid AS blogcid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_items i ON tb.itemid=i.itemid WHERE tb.teamid='$teamid' AND tb.type='blog' ORDER BY tb.postdate DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$rt['subject'] = substrs($rt['subject'],50);
				$_NEWBLOG[] = $rt;
			}
			empty($_NEWBLOG) && $_NEWBLOG = array();
			$db->free_result($query);

			//最新相册缓存
			$query = $db->query("SELECT tb.*,u.username,p.attachurl,p.ifthumb,p.cid AS photocid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_photo p ON tb.itemid=p.aid WHERE tb.teamid='$teamid' AND tb.type='photo' ORDER BY tb.postdate DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
				!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
				if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
					$rt['attachurl'] = "$attachpath/$rt[attachurl]";
				} elseif (file_exists($attach_url/$rt[attachurl])) {
					$rt['attachurl'] = "$attach_url/$rt[attachurl]";
				} else {
					$rt['attachurl'] = "$attachpath/none.gif";
				}
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$rt['subject'] = substrs($rt['subject'],20);
				$_NEWALBUM[] = $rt;
			}
			empty($_NEWALBUM) && $_NEWALBUM = array();
			$db->free_result($query);
			//最新音乐缓存
			$query = $db->query("SELECT tb.*,u.username,ma.hpageurl,ma.cid AS musiccid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_malbums ma ON tb.itemid=ma.maid WHERE tb.teamid='$teamid' AND type='music' ORDER BY tb.postdate DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['hpageurl'] = showhpageurl($rt['hpageurl']);
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$rt['subject'] = substrs($rt['subject'],20);
				$_NEWMALBUM[] = $rt;
			}
			empty($_NEWMALBUM) && $_NEWMALBUM = array();
			$db->free_result($query);
			//写手排行缓存
			$query = $db->query("SELECT tu.uid,u.username FROM pw_tuser tu LEFT JOIN pw_user u ON tu.uid=u.uid WHERE tu.teamid='$teamid' AND tu.ifcheck='1' ORDER BY tu.blogs DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$_WRITER[] = $rt;
			}
			empty($_WRITER) && $_WRITER = array();
			$db->free_result($query);;
			//最新成员缓存
			$query = $db->query("SELECT tu.uid,u.username,u.icon FROM pw_tuser tu LEFT JOIN pw_user u ON tu.uid=u.uid WHERE tu.teamid='$teamid' AND tu.ifcheck='1' ORDER BY tu.joindate DESC LIMIT 0,4");
			while ($rt = $db->fetch_array($query)) {
				$rt['icon'] = showfacedesign($rt['icon']);
				$_NEWUSER[] = $rt;
			}
			empty($_NEWUSER) && $_NEWUSER = array();
			$db->free_result($query);
			
			//日志排行缓存
			$query = $db->query("SELECT tb.*,u.username,i.cid AS blogcid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_items i ON tb.itemid=i.itemid WHERE tb.teamid='$teamid' AND tb.type='blog' AND i.ifcheck='1' ORDER BY i.hits DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$_TOPBLOG[] = $rt;
			}
			empty($_TOPBLOG) && $_TOPBLOG = array();
			$db->free_result($query);
			//相册排行缓存
			$query = $db->query("SELECT tb.*,u.username,a.cid AS photocid,a.hits FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_albums a ON tb.itemid=a.aid WHERE tb.teamid='$teamid' AND tb.type='photo' ORDER BY a.hits DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
				!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
				if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
					$rt['attachurl'] = "$attachpath/$rt[attachurl]";
				} elseif (file_exists($attach_url/$rt[attachurl])) {
					$rt['attachurl'] = "$attach_url/$rt[attachurl]";
				} else {
					$rt['attachurl'] = "$attachpath/none.gif";
				}
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$_TOPALBUM[] = $rt;
			}
			empty($_TOPALBUM) && $_TOPALBUM = array();
			$db->free_result($query);
			//音乐排行缓存
			$query = $db->query("SELECT tb.*,u.username,ma.hpageurl,ma.cid AS musiccid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_malbums ma ON tb.itemid=ma.maid WHERE tb.teamid='$teamid' AND type='music' ORDER BY ma.hits DESC LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['hpageurl'] = showhpageurl($rt['hpageurl']);
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$_TOPMALBUM[] = $rt;
			}
			empty($_TOPMALBUM) && $_TOPMALBUM = array();
			$db->free_result($query);
			
			//圈主推荐日志缓存
			$query = $db->query("SELECT tb.*,u.username,i.cid AS blogcid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_items i ON tb.itemid=i.itemid WHERE tb.teamid='$teamid' AND tb.type='blog' AND i.ifcheck='1' AND tb.commend='1' LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$_CMDBLOG[] = $rt;
			}
			empty($_CMDBLOG) && $_CMDBLOG = array();
			$db->free_result($query);
			
			//圈主推荐相册缓存
			$query = $db->query("SELECT tb.*,u.username,a.cid AS photocid,a.hits FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_albums a ON tb.itemid=a.aid WHERE tb.teamid='$teamid' AND tb.type='photo' AND tb.commend='1' LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
				!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
				if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
					$rt['attachurl'] = "$attachpath/$rt[attachurl]";
				} elseif (file_exists($attach_url/$rt[attachurl])) {
					$rt['attachurl'] = "$attach_url/$rt[attachurl]";
				} else {
					$rt['attachurl'] = "$attachpath/none.gif";
				}
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$_CMDALBUM[] = $rt;
			}
			empty($_CMDALBUM) && $_CMDALBUM = array();
			$db->free_result($query);
			
			//圈主推荐音乐缓存
			$query = $db->query("SELECT tb.*,u.username,ma.hpageurl,ma.cid AS musiccid FROM pw_tblog tb LEFT JOIN pw_user u ON tb.uid=u.uid LEFT JOIN pw_malbums ma ON tb.itemid=ma.maid WHERE tb.teamid='$teamid' AND tb.type='music' AND tb.commend='1' LIMIT 0,10");
			while ($rt = $db->fetch_array($query)) {
				$rt['hpageurl'] = showhpageurl($rt['hpageurl']);
				$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
				$_CMDMALBUM[] = $rt;
			}
			empty($_CMDMALBUM) && $_CMDMALBUM = array();
			$db->free_result($query);
			
			$writecache .= '$_NEWBLOG = '.N_var_export($_NEWBLOG).";\r\n";
			$writecache .= '$_NEWALBUM = '.N_var_export($_NEWALBUM).";\r\n";
			$writecache .= '$_NEWMALBUM = '.N_var_export($_NEWMALBUM).";\r\n";
			$writecache .= '$_WRITER = '.N_var_export($_WRITER).";\r\n";
			$writecache .= '$_NEWUSER = '.N_var_export($_NEWUSER).";\r\n";
			$writecache .= '$_TOPBLOG = '.N_var_export($_TOPBLOG).";\r\n";
			$writecache .= '$_TOPALBUM = '.N_var_export($_TOPALBUM).";\r\n";
			$writecache .= '$_TOPMALBUM = '.N_var_export($_TOPMALBUM).";\r\n";
			$writecache .= '$_CMDBLOG = '.N_var_export($_CMDBLOG).";\r\n";
			$writecache .= '$_CMDALBUM = '.N_var_export($_CMDALBUM).";\r\n";
			$writecache .= '$_CMDMALBUM = '.N_var_export($_CMDMALBUM).";\r\n";
			
			writeover(D_P."data/cache/team_$tbloghash.php","<?php\r\n$writecache?>");
		}
		$db->free_result($query);
		@include_once Pcv(D_P."data/cache/team_$tbloghash.php");
	}
	
}elseif ((int)$cid > 0) {
	foreach ($_TEAM as $value) {
		if ($value['cid']==$cid || $value['cup']==$cid) {
			$cids .= ($cids ? ',' : '')."'$value[cid]'";
			$count += $_TEAM[$value['cid']]['counts']+0;
		}
	}
	!$cids && Showmsg('undefined_action');
	$cidname = $_TEAM[$cid]['name'];
	unset($_TEAM);
	$teamhash = substr(md5("{$db_hash}tags{$cidname}"),0,10);
	$cids = strpos($cids,',')!==false ? " IN ($cids)" : "=$cids";
	$query = $db->query("SELECT teamid,uid,username,name,bloggers FROM pw_team WHERE ifshow<>'0' AND cid$cids ORDER BY bloggers DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$teamdb[] = $rt;
	}
	empty($teamdb) && Showmsg('teamid_false');
	$db->free_result($query);
	if ($count > $db_perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"team.php?cid=$cid&");
	}
	$_COMMEND = $_NEWTEAM = $_TOP = array();
	if (!empty($teamdb) && $timestamp - @filemtime(D_P."data/cache/team_$teamhash.php") > 3600) {
		$writecache = '';
		$query = $db->query("SELECT teamid,cid,uid,username,name,descrip,icon FROM pw_team WHERE ifshow!='0' AND cid$cids ORDER BY commend DESC,blogs DESC LIMIT 0,5");
		while ($rt = $db->fetch_array($query)) {
			!$rt['icon'] && $rt['icon'] = 'nopic.jpg';
			$_COMMEND[] = $rt;
		}
		$query = $db->query("SELECT teamid,cid,name FROM pw_team WHERE ifshow!='0' AND cid$cids ORDER BY teamid DESC LIMIT 0,10");
		while ($rt = $db->fetch_array($query)) {
			$rt['name'] = substrs($rt['name'],15);
			$_NEWTEAM[] = $rt;
		}
		$db->free_result($query);
		$writecache .= '$_COMMEND = '.N_var_export($_COMMEND).";\r\n";
		$writecache .= '$_NEWTEAM = '.N_var_export($_NEWTEAM).";\r\n";
		writeover(D_P."data/cache/team_$teamhash.php","<?php\r\n$writecache?>");
	}
	@include_once Pcv(D_P."data/cache/team_$teamhash.php");
	@include_once Pcv(D_P."data/cache/mod_cache.php");
	if (!empty($_COMMEND)) {
		$_TOP = $_COMMEND[0];
		$_COMMEND = array_slice($_COMMEND,1);
	}
}

require_once PrintEot('team');footer();
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