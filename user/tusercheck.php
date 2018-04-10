<?php
!function_exists('usermsg') && exit('Forbidden');

!$db_teamifopen && usermsg('team_close');
if($db_teamgroups && strpos($db_teamgroups,"$groupid")===false){
	usermsg('team_groupright');
}
$teamdb=array();
$teamids=$extra='';
$query=$db->query("SELECT teamid,name FROM pw_team WHERE uid='$admin_uid'");
while($rt=$db->fetch_array($query)){
	$teamids.=$extra.$rt['teamid'];
	$extra=',';
	$teamdb[$rt['teamid']]=$rt;
}
!$teamids && usermsg('no_teams');

if(empty($job)){

	$tuserdb=array();
	if($teamids){
		$query=$db->query("SELECT tu.uid,tu.teamid,tu.joindate,tu.ifcheck,u.username FROM pw_tuser tu LEFT JOIN pw_user u ON u.uid=tu.uid WHERE teamid IN($teamids)");
		while($rt=$db->fetch_array($query)){
			$rt['joindate']=get_date($rt['joindate']);
			$rt['name']=$teamdb[$rt['teamid']]['name'];
			$tuserdb[]=$rt;
		}
	}
	require_once PrintEot('tusercheck');footer();
} elseif($job == 'pass' || $job == 'del'){

	(!$uid || !$teamid || !$teamdb[$teamid]) && usermsg('undefine_action');
	$rt=$db->get_one("SELECT ifcheck FROM pw_tuser WHERE teamid='$teamid' AND uid='$uid'");
	
	if($job == 'pass'){
		if($rt['ifcheck']){
			usermsg('have_pass');
		}
		$db->update("UPDATE pw_tuser SET ifcheck='1' WHERE teamid='$teamid' AND uid='$uid'");
		$db->update("UPDATE pw_team SET bloggers=bloggers+1 WHERE teamid='$teamid' AND uid='$admin_uid'");
	} elseif($job == 'del'){
		$owner=$db->get_one("SELECT uid FROM pw_team WHERE teamid='$teamid'");
		$owner['uid']==$uid && usermsg('undefined_action');
		$db->update("DELETE FROM pw_tuser WHERE teamid='$teamid' AND uid='$uid'");
		$db->update("UPDATE pw_team SET bloggers=bloggers-1 WHERE teamid='$teamid' AND uid='$admin_uid'");
	}
	usermsg('operate_success',$basename);
}elseif($job == 'invite'){
	if($_POST['step'] != '2'){
		$query = $db->query("SELECT t.teamid,t.name FROM pw_tuser tu LEFT JOIN pw_team t ON t.teamid=tu.teamid WHERE tu.admin='$admin_uid' AND tu.ifcheck='1'");
		while ($rt = $db->fetch_array($query)) {
			$rt['teamid'] && $teamsel .= "<option value=\"$rt[teamid]\">$rt[name]</option>";
		}
		require_once PrintEot('tusercheck');footer();
	}else{
		InitGP(array('teamid','username'),G);
		list($teamuid,$teamusername) = UserCheck($username);
		$teamid = (int)$teamid;
		$teamdb=$db->get_one("SELECT teamid,type FROM pw_team WHERE teamid='$teamid'");
		if($teamdb){
			$rt=$db->get_one("SELECT teamid FROM pw_tuser WHERE teamid='$teamid' AND uid='$teamuid'");
			if($rt){
				usermsg('have_join',$basename);
			}
			$db->update("UPDATE pw_team SET bloggers=bloggers+1 WHERE teamid='$teamid'");
			$db->update("INSERT INTO pw_tuser(uid,teamid,joindate,ifcheck) VALUES('$teamuid','$teamid','$timestamp','1')");
			usermsg('join_success',$basename);
		} else{
			usermsg('undefined_action',$basename);
		}
	}
}

function UserCheck($pwuser){
	global $db;
	$pwuser = trim($pwuser);
	$uid = $db->get_value("SELECT uid FROM pw_user WHERE username='$pwuser'");
	if(!$uid){
		Showtruemsg('user_not_exists');
		return false;
	}
	return array($uid,$pwuser);
}
?>