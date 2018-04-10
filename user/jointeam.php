<?php
!function_exists('usermsg') && exit('Forbidden');
include_once D_P.'data/cache/forum_cache_team.php';

!$db_teamifopen && usermsg('team_close');
if($db_teamgroups && strpos($db_teamgroups,"$groupid")===false){
	usermsg('team_groupright');
}
if(empty($job)){

	$teamdb=array();
	$query=$db->query("SELECT t.teamid,t.cid,t.username,t.name FROM pw_tuser tu LEFT JOIN pw_team t ON t.teamid=tu.teamid WHERE tu.uid='$admin_uid' AND t.uid!='$admin_uid' AND ifcheck='1'");
	while($rt=$db->fetch_array($query)){
		$rt['cname']=$_TEAM[$rt['cid']]['name'];
		$teamdb[]=$rt;
	}
	require_once PrintEot('jointeam');footer();
}elseif($job == 'leave'){
	!$teamid && usermsg('undefine_action');
	$db->update("DELETE FROM pw_tuser WHERE uid='$admin_uid' AND teamid='$teamid'");
	$db->update("UPDATE pw_team SET bloggers=bloggers-1 WHERE teamid='$teamid'");
	usermsg('operate_success',$basename);
}
?>