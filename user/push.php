<?php
!function_exists('usermsg') && exit('Forbidden');

$itemid=(int)$itemid;
if($itemid){
	$teamids=$extra=$teamsel='';
	$query=$db->query("SELECT t.teamid,t.cid,t.username,t.name FROM pw_tuser tu LEFT JOIN pw_team t ON t.teamid=tu.teamid WHERE tu.uid='$admin_uid' AND ifcheck='1'");
	while($rt=$db->fetch_array($query)){
		if($rt['teamid']){
			$teamids.=$extra.$rt['teamid'];
			$extra=',';
			$teamsel.="<option value='$rt[teamid]'>$rt[name]</option>";
		}
	}
	
	if(empty($job)){

		require_once(PrintEot('push'));footer();
	} elseif($job=='push'){
		
		if(strpos(",$teamids,",",$teamid,")===false){
			usermsg('operate_error');
		}
		(!$itemid || !$teamid) && usermsg('operate_error');

		$tm=$db->get_one("SELECT teamid,name FROM pw_team WHERE teamid='$teamid'");
		$rt=$db->get_one("SELECT subject,type FROM pw_items WHERE itemid='$itemid' AND ifcheck=1");
		if(!$rt){
			usermsg('operate_error');
		}
		$rt2=$db->get_one("SELECT pushlog FROM pw_{$rt[type]} WHERE itemid='$itemid'");
		if(strpos("\t".$rt2['pushlog'],"\t".$tm['teamid'].','.$tm['name']."\t")!==false){
			usermsg('have_pushed');
		}
		$pushlog=$rt2['pushlog'].$tm['teamid'].','.$tm['name']."\t";

		$db->update("UPDATE pw_{$rt[type]} SET pushlog='$pushlog' WHERE itemid='$itemid'");
		$db->update("UPDATE pw_team SET blogs=blogs+1,lastid='$itemid' WHERE teamid='$teamid'");
		$db->update("INSERT INTO pw_tblog(itemid,uid,teamid,postdate,subject) VALUES('$itemid','$admin_uid','$teamid','$timestamp','".addslashes($rt['subject'])."')");
		$basename=$rt['type']=='blog' ? "article.php?itemid=$itemid&type=blog" : "blog.php?do=showone&itemid=$itemid&type=$rt[type]";
		usermsg('operate_success',$basename);
	}
}
?>