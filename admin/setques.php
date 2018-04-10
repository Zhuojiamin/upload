<?php
!function_exists('adminmsg') && exit('Forbidden');

if($_POST['step'] != 2){
	list($qcheck['regq'],$qcheck[loginq],$postq,$qcheck[gbookq],$qcheck[cmtq],$qcheck[msgq]) = explode("\t",$db_qcheck);
	$db_question = unserialize($db_question);
	$db_answer = unserialize($db_answer);
	ifcheck($qcheck);
} else {
	InitGP(array('qcheck','question','answer'),'P',0);
	(!is_numeric($qcheck['post']) || $qcheck['post'] < 0) && $qcheck['post'] = 0;
	$qcheck	= implode("\t",$qcheck);
	$q_array = $a_array = array();
	if($question){
		foreach($question as $key=>$value){
			if($value){
				$q_array[] = stripslashes($value);
				$a_array[] = stripslashes($answer[$key]);
			}
		}
	}
	$question = $q_array ? addslashes(serialize($q_array)) : '';
	$answer	= $a_array ? addslashes(serialize($a_array)) : '';
	$db->update("UPDATE pw_setting SET db_value='$qcheck' WHERE db_name='db_qcheck'");
	$db->update("UPDATE pw_setting SET db_value='$question' WHERE db_name='db_question'");
	$db->update("UPDATE pw_setting SET db_value='$answer' WHERE db_name='db_answer'");
	updatecache_db();
	adminmsg('operate_success',"$basename&");
}
include PrintEot('setques');footer();
?>