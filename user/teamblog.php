<?php
!function_exists('usermsg') && exit('Forbidden');
require_once(R_P.'mod/page_mod.php');
!$db_teamifopen && usermsg('team_close');
if($db_teamgroups && strpos($db_teamgroups,"$groupid")===false){
	usermsg('team_groupright');
}
$teamdb=array();
$teamids=$extra=$teamsel='';
$query=$db->query("SELECT teamid,name FROM pw_team WHERE uid='$admin_uid'");
while($rt=$db->fetch_array($query)){
	$teamids.=$extra.$rt['teamid'];
	$extra=',';
	$teamsel.="<option value='$rt[teamid]'>$rt[name]</option>";
	$teamdb[$rt['teamid']]=$rt;
}
!$teamids && usermsg('no_teams');
if(empty($job)){
	$postdate1='2004-01-01';
	$postdate2=get_date($timestamp+24*3600,'Y-m-d');
	require_once PrintEot('teamblog');footer();
} elseif($job=='search'){
	require_once(R_P.'user/tusercheck.php');
	if(is_numeric($teamid)){
		if(strpos(",$teamids,",",$teamid,")===false){
			usermsg('operate_error');
		}
		$sql="t.teamid='$teamid'";
	}else{
		$sql="t.teamid IN($teamids)";
	}
	$sql=is_numeric($teamid) ? "t.teamid='$teamid'" : "t.teamid IN($teamids)";
	if($username){
		list($shckuid,$shckusername) = UserCheck($username);
		if($shckuid){
			$sql.=" AND t.uid='$rt[uid]'";
		}
	}

	if($keyword){
		$keyword_a=explode(",",trim($keyword));
		$keywhere=$extra='';
		foreach($keyword_a as $key => $value){
			//$value=str_replace('*','%',$value);
			$keywhere.="$extra t.subject LIKE '%$value%'";
			$extra=' OR';
		}
		$keywhere && $sql.=" AND ($keywhere) ";
	}

	if($postdate1 || $postdate2){
		!is_numeric($postdate1) && $postdate1=strtotime($postdate1);
		!is_numeric($postdate2) && $postdate2=strtotime($postdate2);
		is_numeric($postdate1)  && $sql.=" AND t.postdate>'$postdate1'";
		is_numeric($postdate2)  && $sql.=" AND t.postdate<'$postdate2'";
	}
	$sql.=" ORDER BY t.postdate DESC";

	$page < 1 && $page=1;
	$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";

	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_tblog t WHERE $sql");
	$sum=$rt['count'];	$pages=page($sum,$page,$db_perpage,"$basename&job=search&teamid=$teamid&username=".rawurlencode($username)."&keyword=".rawurlencode($keyword)."&postdate1=$postdate1&postdate2=$postdate2&");

	$tblogdb=array();
	$query=$db->query("SELECT t.*,u.username FROM pw_tblog t LEFT JOIN pw_user u ON u.uid=t.uid WHERE $sql $limit");
	while($rt=$db->fetch_array($query)){
		$rt['subject']=substrs($rt['subject'],30);
		$rt['postdate']=get_date($rt['postdate']);
		$rt['name']=$teamdb[$rt['teamid']]['name'];
		$rt['type']=$index_name[$rt['type']];
		$tblogdb[]=$rt;
	}
	require_once PrintEot('teamblog');footer();
} elseif($job == 'del'){
	if(!$selid = checkselid($selid)){
		usermsg('operate_error');	
	}
	$query=$db->query("SELECT itemid,type,teamid FROM pw_tblog WHERE teamid IN($teamids) AND itemid IN($selid)");
	while($rt=$db->fetch_array($query)){
		$itemnum[$rt[teamid]][$rt[type]]++;
		$db->update("DELETE FROM pw_tblog WHERE itemid='$rt[itemid]' AND itemid IN($selid)");
	}
	is_array($itemnum) && update_team($itemnum);
	usermsg('operate_success',$basename);
} elseif($job == 'commend'){
	InitGP('commend');
	if(!$selid = checkselid($selid)){
		usermsg('operate_error');	
	}
	$query=$db->query("SELECT itemid FROM pw_tblog WHERE teamid IN($teamids) AND itemid IN($selid)");
	while($rt=$db->fetch_array($query)){
		$db->update("UPDATE pw_tblog SET commend='$commend' WHERE itemid='$rt[itemid]'");
	}
	usermsg('operate_success',$basename);
}

function update_team($itemnum){
	global $db;
	foreach($itemnum as $team_key => $team_value){
		foreach($team_value as $key => $value){
			if($key == 'blog'){
				$db->update("UPDATE pw_team SET blogs=blogs-{$value},items=items-{$value} WHERE teamid='$team_key'");
			}elseif($key == 'photo'){
				$db->update("UPDATE pw_team SET albums=albums-{$value},items=items-{$value} WHERE teamid='$team_key'");
			}elseif($key == 'music'){
				$db->update("UPDATE pw_team SET malbums=malbums-{$value},items=items-{$value} WHERE teamid='$team_key'");
			}
		}
	}
}
?>