<?php
!function_exists('usermsg') && exit('Forbidden');

require_once(R_P.'mod/page_mod.php');
(int)$page<1 && $page = 1;
$userdb=$db->get_one("SELECT msgs FROM pw_user WHERE uid='$admin_uid'");
if(!$job){
	(!is_numeric($page) || $page < 1) && $page = 1;
	$pages = page($userdb['msgs'],$page,$db_perpage,"user_index.php?action=gbook&");
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";

	$gbookdb=array();
	$query = $db->query("SELECT * FROM pw_gbook WHERE uid='$admin_uid' ORDER BY postdate DESC $limit");
	while ($rt = $db->fetch_array($query)){
		$rt['postdate'] = get_date($rt['postdate']);
		$rt['replydate']= get_date($rt['replydate']);
		$rt['content']	= substrs($rt['content'],40);
		$gbookdb[]		= $rt;
	}
	require_once PrintEot('gbook');footer();
} elseif($job == 'reply'){
	$rt=$db->get_one("SELECT reply FROM pw_gbook WHERE id='$id' AND uid='$admin_uid'");
	!$rt && usermsg('undefined_action');
	if(!$step){
		require_once PrintEot('gbook');footer();
	}elseif($step==2){
		$content = Char_cv($content);
		$db->update("UPDATE pw_gbook SET replydate='$timestamp',reply='$content' WHERE id='$id'");
		if($gbook){
			ObHeader("blog.php?do=gbook&uid=$admin_uid");
		}else{
			usermsg('reply_success',$basename);
		}
	}
} elseif($job == 'del'){
	if(!$selid && is_numeric($id)){
		$selid = array($id);
	}
	$count = count($selid);
	if(!$selid = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		usermsg('operate_error');
	}
	$db->update("DELETE FROM pw_gbook WHERE id IN($selid) AND uid='$admin_uid'");
	$db->update("UPDATE pw_user SET msgs=msgs-'$count' WHERE uid='$admin_uid'");
	if($gbook){
		ObHeader("blog.php?do=gbook&uid=$admin_uid");
	}else{
		usermsg('del_success',$basename);
	}
	
}
?>