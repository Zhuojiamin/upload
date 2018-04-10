<?php
!function_exists('usermsg') && exit('Forbidden');
require_once(R_P.'mod/page_mod.php');
(int)$page<1 && $page = 1;
$carticle=array();
if(empty($step)){
	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_carticle c LEFT JOIN pw_items i ON c.itemid=i.itemid LEFT JOIN pw_user u ON c.uid=u.uid WHERE c.touid='$admin_uid'");
	$sum=$rt['count'];
	$pages=page($sum,$page,$db_perpage,"$basename");
	$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$query=$db->query("SELECT c.*,i.subject,i.type,u.username FROM pw_carticle c LEFT JOIN pw_items i ON c.itemid=i.itemid LEFT JOIN pw_user u ON c.uid=u.uid WHERE c.touid='$admin_uid' ORDER BY c.cdate DESC $limit");
	while($rt=$db->fetch_array($query)){
		$rt['cdate']=get_date($rt['cdate']);
		$carticle[]=$rt;
	}
	require_once PrintEot('carticle');footer();
}elseif($step=='del'){
	if(!$selid = checkselid($selid)){
		usermsg('operate_error');
	}
	$db->update("DELETE FROM pw_carticle WHERE touid='$admin_uid' AND id IN($selid)");
	usermsg('operate_success',"$basename");
}
?>