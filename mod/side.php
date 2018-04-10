<?php
!function_exists('getPath') && exit('Forbidden');

foreach ($side_name as $key => $value) {
	!$leftdb[$key]['name'] && $leftdb[$key]['name'] = $value;
	!$leftdb[$key]['sign'] && $leftdb[$key]['sign'] = $key;
}

$side_order = $lefts = $customdb = $getpath = $_ITEMDB = $_TEMPDB = ${$type} = $attachdb = $attachmentdb = array();
$sidedb = array('icon','notice','calendar','search','info','player','link','comment','team','archive','lastvisit','userclass','friends');

if ($catetype == 'blog') {
	$lefts = array('calendar'=>'0','userclass'=>'1','archive'=>'2','search'=>'3');
} elseif ($catetype == 'photo' || $catetype=='photos' || $catetype == 'bookmark' || $catetype == 'goods' || $catetype == 'file' || $catetype == 'music') {
	$lefts = array('userclass'=>'0','search'=>'1');
} elseif ($catetype == 'team') {
	$lefts = array('icon'=>'0','search'=>'1');
} elseif ($catetype == 'gbook') {
	$lefts = array('icon'=>'0','info'=>'1');
} elseif ($catetype == 'aboutme') {
	$lefts = array('icon'=>'0','search'=>'1');
}
foreach ($leftdb as $key => $value) {
	if ($catetype != 'index') {
		if (array_key_exists($key,$lefts)) {
			$leftdb[$key]['ifshow'] = $value['ifshow'] = 1;
			$leftdb[$key]['order'] = $value['order'] = (int)$lefts[$key];
		} else {
			$leftdb[$key]['ifshow'] = $value['ifshow'] = 0;
		}
	}
	if ($value['ifshow']) {
		$leftdb[$key]['name'] = $value['note'] ? $value['note'] : $value['name'];
		$side_order[$key] = (int)$value['order'];
	}
}
@asort($side_order);
foreach ($side_order as $key => $value) {
	if (!in_array($key,$sidedb)) {
		$customdb[] = $key;
		$getpath[$key] = 'side_custom';
	} else {
		$getpath[$key] = "side_$key";
	}
	$side_order[$key] = $leftdb[$key];
}
unset($leftdb);
list($nowy,$nowm) = explode('-',get_date($timestamp,'Y-n'));
if ($side_order['icon']['ifshow']) {
	$blogdb['icon'] = showfacedesign($blogdb['icon']);
	$blogdb['age'] = '';
	$bdaydb = explode('-',$blogdb['bday']);
	foreach ($bdaydb as $key => $value) {
		$bdaydb[$key] = (int)$value;
	}
	if (strlen($bdaydb[0])>3) {
		$blogdb['age'] = ($nowy - $bdaydb[0]) - ($bdaydb[1] && ($nowm < $bdaydb[1]) ? 1 : 0);
	}
	$windgroup['cmduser'] && $blogdb['commend'] = $ilang['cmduser'.$blogdb['commend']];
	$hobbydb = unserialize($blogdb['hobbydb']);
	list(,$db_msgsound) = explode("\t",$db_msgcfg);
	$db_msgsound && $msgsound = "<bgsound src=\"$imgpath/msg.wav\" border=\"0\">";
	empty($hobbydb) && $hobbydb = array();
	list($iconw,$iconh) = explode('|',$blogdb['iconsize']);
}
if ($side_order['notice']['ifshow'] || !empty($customdb)) {
	$customsql = '';
	$side_order['notice']['ifshow'] && $customsql  = "'notice'";
	foreach ($customdb as $value) {
		$customsql .= ($customsql ? ',' : '')."'$value'";
	}
	if ($customsql) {
		$customsql = strpos($customsql,',')===false ? "=$customsql" : " IN ($customsql)";
		$query = $db->query("SELECT	sign,content FROM pw_lcustom WHERE authorid='$uid' AND sign$customsql");
		while ($rt = $db->fetch_array($query)) {
			$rt['content'] = str_replace(array("\r","\n"),array('','<br />'),$rt['content']);
			$customdb[$rt['sign']] = $rt['content'];
		}
		$db->free_result($query);
	}
	empty($customdb) && $side_order['notice'] = null;
}
if ($side_order['calendar']['ifshow']) {
	InitGP(array('y','m'),'G');
	$calendar = array();
	$ny = !$y ? $nowy : $y;
	$nm = !$m ? $nowm : $m;
	require_once(R_P.'mod/calendar_mod.php');
	$calendar['days']	  = calendar($nm,$ny);
	$calendar['nm'] 	  = ($nm+1)>12 ? 1 : $nm+1;
	$calendar['pm'] 	  = ($nm-1)<1 ? 12 : $nm-1;
	$calendar['ny'] 	  = $ny+1;
	$calendar['py'] 	  = $ny-1;
	$calendar['cur_date'] = get_date($timestamp,'Y n.j D');
}
if ($side_order['userclass']['ifshow']) {
	if($catetype == 'blog' || $catetype == 'index'){
		$dirid = GetGP('dirid','G');
		$dirdb = unserialize($blogdb['dirdb']);
		$dirdb = $dirdb[$type];
		$select = $L_join = '';
		if ($winduid != $uid) {
			$select .= ',u.friends';
			$L_join .= ' LEFT JOIN pw_user u ON i.uid=u.uid';
		}
		@include(D_P.'data/cache/wordfb.php');
		$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
	}elseif($catetype == 'photo' || $catetype == 'photos' ){
		$aid = GetGP('aid','G');
		@include(D_P.'data/cache/wordfb.php');
		$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
		$albumdb = unserialize($blogdb['albumdb']);
		
		/*
		if ($winduid != $uid) {
			$select .= ',u.friends';
			$L_join .= ' LEFT JOIN pw_user u ON i.uid=u.uid';
		}
		
		
		$query	= $db->query("SELECT a.aid,a.author,a.subject,a.descrip,a.ifhide,a.postdate,a.hits,a.ifwordsfb,a.uploads{$select},p.attachurl FROM pw_albums a {$L_join} LEFT JOIN pw_photo p ON a.hpagepid=p.pid  WHERE a.uid='$uid' AND a.ifcheck='1' ORDER BY a.postdate DESC");
		while ($rt = $db->fetch_array($query)) {
			if ($uid == $winduid || $rt['ifhide']==0 || ($rt['ifhide']==2 && strpos(",$rt[friends],",",$winduid,")!==false)) {
				if (!$rt['ifwordsfb']) {
					$ifwordsfb = 0;
					$cktitle = $rt['subject'];
					$ckdescrip = $rt['descrip'];
					foreach ($_FORBIDDB as $value) {
						$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
						$ckdescrip = N_strireplace($value['word'],$value['wordreplace'],$ckdescrip);
					}
					if ($cktitle != $rt['subject']) {
						$rt['subject'] = $cktitle;
						$ifwordsfb = 1;
					}
					if ($ckdescrip != $rt['descrip']) {
						$rt['descrip'] = $ckdescrip;
						$ifwordsfb = 1;
					}
					if ($ifwordsfb) {
						$db->update("UPDATE pw_albums SET ifwordsfb='1',subject='".addslashes($cktitle)."',descrip='".addslashes($ckdescrip)."' WHERE aid='$rt[aid]'");
					}
				}
				$rt['hpageurl'] =  $rt['attachurl'] ? $attachpath.'/'.$rt[attachurl] : $imgpath.'/nopic.jpg';
				$blogdb['wshownum'] && $rt['descrip'] = substrs($rt['descrip'],$blogdb['wshownum']);
				$db_copyctrl && $rt['descrip'] = preg_replace("/<br \/>/eis","copyctrl()",$rt['descrip']);
				$rt['uploads'] = $rt['uploads'] ? unserialize($rt['uploads']) : array();
				$_TEMPDB[$rt['aid']][] = $rt;
			}
		}*/
	}elseif ($catetype == 'music') {
		$maid = GetGP('maid','G');
		@include(D_P.'data/cache/wordfb.php');
		$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
		$malbumdb = unserialize($blogdb['malbumdb']);
		/*
		$dirdb = unserialize($blogdb['dirdb']);
		$dirdb = $dirdb[$type];
		$dirdb[0]['typeid'] = '0';
		$dirdb[0]['name'] = 'none';
		$select = $L_join = '';
		if ($winduid != $uid) {
			$select .= ',u.friends';
			$L_join .= ' LEFT JOIN pw_user u ON i.uid=u.uid';
		}
		@include(D_P.'data/cache/wordfb.php');
		$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
		$query	= $db->query("SELECT i.itemid,i.dirid,i.author,i.subject,i.ifhide,i.postdate,i.hits,i.ifwordsfb,i.uploads,t.*$select FROM pw_items i LEFT JOIN pw_$type t ON i.itemid=t.itemid$L_join WHERE i.uid='$uid' AND i.type='$type' AND i.ifcheck='1' ORDER BY i.postdate DESC");
		while ($rt = $db->fetch_array($query)) {
			if ($uid == $winduid || $rt['ifhide']==0 || ($rt['ifhide']==2 && strpos(",$rt[friends],",",$winduid,")!==false)) {
				if ($catetype == 'photo' && !$rt['uploads'] && !$rt['absoluteurl']) {
					continue;
				}
				if (!$rt['ifwordsfb']) {
					$ifwordsfb = 0;
					$cktitle = $rt['subject'];
					$ckcontent = $rt['content'];
					foreach ($_FORBIDDB as $value) {
						$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
						$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
					}
					if ($cktitle != $rt['subject']) {
						$rt['subject'] = $cktitle;
						$ifwordsfb = 1;
					}
					if ($ckcontent != $rt['content']) {
						$rt['content'] = $ckcontent;
						$ifwordsfb = 1;
					}
					if ($ifwordsfb) {
						$db->update("UPDATE pw_items SET ifwordsfb='1',subject='".addslashes($cktitle)."' WHERE itemid='$rt[itemid]'");
						$db->update("UPDATE pw_$type SET content='".addslashes($ckcontent)."' WHERE itemid='$rt[itemid]'");
					}
				}
				$rt['ifsign']<2 && $rt['content'] = nl2br($rt['content']);
				$blogdb['wshownum'] && $rt['content'] = substrs($rt['content'],$blogdb['wshownum']);
				$rt['ifconvert'] && $rt['content'] = convert($rt['content'],$db_post);
				$db_copyctrl && $rt['content'] = preg_replace("/<br \/>/eis","copyctrl()",$rt['content']);
				$rt['uploads'] = $rt['uploads'] ? unserialize($rt['uploads']) : array();
				$_TEMPDB[$rt['dirid']][] = $rt;
			}
		}
	*/
	} elseif ($catetype == 'bookmark') {
		$dirid = GetGP('dirid','G');
		$dirdb = unserialize($blogdb['dirdb']);
		$dirdb = $dirdb[$type];
		$L_join = '';
		if ($winduid != $uid) {
			$L_join .= ' LEFT JOIN pw_user u ON i.uid=u.uid';
		}
		@include(D_P.'data/cache/wordfb.php');
		$_FORBIDDB = (array)$_REPLACE + (array)$_FORBID;
		//$dirdb = array();
	}
}
if ($side_order['info']['ifshow']) {
	include_once(D_P.'data/cache/level_cache.php');
	$_alllevel	= $_gdefault+$_gsystem+$_gmember+$_gspecial;
	$blogdb['level'] = $_alllevel[$blogdb['groupid']]['title'];
	unset($blogdb['groupid'],$blogdb['memberid'],$_alllevel);
	$blogdb['rvrc'] = floor($blogdb['rvrc']/10);
	$blogdb['onlinetime'] = floor($blogdb['onlinetime']/3600);
	$blogdb['rdate'] = get_date($blogdb['regdate'],'Y-m-d');
}
if ($side_order['archive']['ifshow']) {
	list($beginy,$beginm) = explode('-',get_date($blogdb['regdate'],'Y-n'));
	$archive = array();
	for ($i=$beginy;$i<$nowy;$i++) {
		for ($j=$beginm;$j<=12;$j++) {
			$archive[] = array('y'=>$i,'m'=>$j);
		}
		$beginm=1;
	}
	for ($j=$beginm;$j<=$nowm;$j++) {
		$archive[] = array('y'=>$nowy,'m'=>$j);
	}
}
if ($side_order['player']['ifshow'] && $blogdb['bmusicurl']) {
	if (!preg_match('/^http/is',$blogdb['bmusicurl'])) {
		$blogdb['bmusicurl'] = $db_blogurl."/$imgpath/skin/".rawurlencode($blogdb['bmusicurl']);
	} else {
		$blogdb['bmusicurl'] = substr($blogdb['bmusicurl'],0,strrpos($blogdb['bmusicurl'],'/')+1).rawurlencode(substr($blogdb['bmusicurl'],strrpos($blogdb['bmusicurl'],'/')+1));
	}
}
if ($side_order['comment']['ifshow'] && $blogdb['commentdb']) {
	$commentdb = unserialize($blogdb['commentdb']);
	$commentdb = $commentdb[$type];
}
if ($side_order['lastvisit']['ifshow'] && $blogdb['lastvisitdb']) {
	$lastvisitdb = unserialize($blogdb['lastvisitdb']);
}
if ($side_order['link']['ifshow'] && $blogdb['link']) {
	$linkdb = unserialize($blogdb['link']);
}
if($side_order['team']['ifshow'] && $blogdb['teamdb']) {
	$teamdb = unserialize($blogdb['teamdb']);
}
if($side_order['friends']['ifshow'] ){
	$frienddb = unserialize($blogdb['frienddb']);
	empty($frienddb) && $frienddb = array();
	$ajax_total = count($frienddb);
	$sumpage = ceil($ajax_total/6);
	/*
	$basename = "blog.php?uid=$uid";
	$ajax_total = count($frienddb);
	$ajax_num=2;
	$ajax_pagenum = ceil($ajax_total/$ajax_num);
	$ajax_page=isset($_GET['ajax_page'])?intval($_GET['ajax_page']):1;
	$ajax_page=min($ajax_pagenum,$ajax_page);
	$ajax_prepg=$ajax_page-1;
	$ajax_nextpg=($ajax_prepg==$ajax_pagenum ? 0 : $ajax_page+1);
	$ajax_offset=($ajax_page-1)*$ajax_num;
	$pagenav='共('.$ajax_total.')个好友';
	if($ajax_pagenum<=1) return false;
	$pagenav.=" <a onclick=dopage('listPic1','1'); style=\"cursor:pointer\">首页</a> ";
	if($ajax_prepg) $pagenav.=" <a onclick=\"dopage('listPic1','".$ajax_prepg."');\" style=\"cursor:pointer\">前页</a> "; else $pagenav.=" 前页 ";
	if($ajax_nextpg) $pagenav.=" <a onclick=\"dopage('listPic1','".$ajax_nextpg."');\" style=\"cursor:pointer\">后页</a> "; else $pagenav.=" 后页 ";
	$pagenav.=" <a onclick=\"dopage('listPic1','$ajax_pagenum');\" style=\"cursor:pointer\">尾页</a> ";
	$pagenav.="第 $ajax_page　页，共 $ajax_pagenum 页";
	
	if($ajax_page>$ajax_pagenum){
       Echo "Error : Can Not Found The page ".$ajax_page;
       Exit;
	}
	*/
	$frienddb = array_slice($frienddb,0,6);

}
include_once(getPath('side'));
?>