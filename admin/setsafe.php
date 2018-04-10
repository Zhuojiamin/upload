<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename .= "&job=$job";

if ($job == 'ipban') {
	if ($_POST['step'] != 2) {
		$db_ipban = str_replace(',',"\r\n",$db_ipban);
	} else {
		$ipban = GetGP('ipban','P');
		$ipban = ArrayTxt(str_replace("\n",',',$ipban),',');
		if ($ipban != $db_ipban) {
			$db->get_value("SELECT db_name FROM pw_setting WHERE db_name='db_ipban'") ? $db->update("UPDATE pw_setting SET db_value='$ipban' WHERE db_name='db_ipban'") : $db->update("INSERT INTO pw_setting(db_name,db_value) VALUES ('db_ipban','$ipban')");
			updatecache_db();
		}
		adminmsg('operate_success');
	}
} elseif ($job == 'other') {
	if ($_POST['step'] != 2) {
		ifcheck(array('ipcheck' => $db_ipcheck));
		!isset($db_hash) && $db_hash = '?3@d#s$7^';
	} else {
		$config = GetGP('config','P');
		!is_array($config) && $config = array();
		!$config['hash'] && $config['hash'] = '?3@d#s$7^';
		updatesetting($config);
		adminmsg('operate_success');
	}
}
include PrintEot('setsafe');footer();
?>