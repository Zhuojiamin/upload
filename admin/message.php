<?php
!function_exists('adminmsg') && exit('Forbidden');
if($job == 'send'){
	if($_POST[step] != '2'){
		$ltitle = $_gsystem+$_gspecial;
	}else{
		InitGP(array('step','sendto','subject','atc_content'));
		!$sendto && adminmsg('operate_error');
		if(empty($subject) || empty($atc_content)){
			adminmsg('sendmsg_empty');
		}
		$subject     = Char_cv($subject);
		$sendmessage = Char_cv($atc_content);
		$sendto = implode(",",$sendto);
		$sqlwhere = "groupid IN('".str_replace(",","','",$sendto)."')";
		$db->update("INSERT INTO pw_msgs (togroups,fromuid,username,type,ifnew,title,mdate,content) VALUES (',$sendto,','0','SYSTEM','public','0','$subject','$timestamp','$sendmessage')");
		adminmsg('operate_success',"$basename&job=$job&");
	}
}elseif($job == 'set'){
	if($_POST[step] != '2'){
		list($msgseting['open'],$msgseting['sound'],$msgseting['regmsg']) = explode("\t",$db_msgcfg);
		ifcheck($msgseting);
	}else{
		InitGP(array('config','maxmsg','maxsendmsg'),'P');
		(!is_numeric($maxmsg) || $maxmsg<0) && $maxmsg = 0;
		(!is_numeric($maxsendmsg) || $maxsendmsg<0) && $maxsendmsg = 0;
		$db_msgcfg = implode("\t",$config);
		$db->update("UPDATE pw_setting SET db_value='$db_msgcfg' WHERE db_name='db_msgcfg'");
		$db->update("UPDATE pw_setting SET db_value='$maxmsg' WHERE db_name='db_msgmax'");
		$db->update("UPDATE pw_setting SET db_value='$maxsendmsg' WHERE db_name='db_msgmaxsend'");
		updatecache_db();
		adminmsg('operate_success',"$basename&job=$job&");
	}
}
include PrintEot('message');footer();
?>