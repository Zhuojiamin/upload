<?php
!function_exists('usermsg') && exit('Forbidden');

if(empty($job)){
	$rt = $db->get_one("SELECT commend FROM pw_user WHERE uid='$admin_uid'");
	require_once PrintEot('commend');footer();
} elseif($job=='commend'){
	$db->update("UPDATE pw_user SET commend=2 WHERE uid='$admin_uid'");
	usermsg('operate_success',$basename);
}