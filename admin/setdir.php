<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
if ($_POST['step']!=2) {
	if ($job=='dir') {
		$imgdisabled = (!file_exists($imgdir) || !is_writeable($imgdir)) ? 'disabled' : '';
		$attdisabled = (!file_exists($attachdir) || !is_writeable($attachdir)) ? 'disabled' : '';
		$attachdir_ck[(int)$db_attachdir] = 'checked';
		$hour_sel[(int)$db_hour]='selected';
	}
	if ($job=='html') {
		!$db_dir && $db_dir = '.php?';
		!$db_ext && $db_ext = '.html';
		ifcheck(array('htmifopen' => $db_htmifopen));
	}
	include PrintEot('setdir');footer();
} else {
	$config = GetGP('config','P');
	if ($job=='dir') {
		$set = GetGP('set','P');
		$setting = array();
		if ($picpath!=$set['picpath']) {
			if (!is_dir($set['picpath'])) {
				@rename($picpath,$set['picpath']) ? $setting['pic'] = $set['picpath'] : adminmsg('setting_777');
			} else {
				$setting['pic'] = $set['picpath'];
			}
		}
		if ($attachpath!=$set['attachpath']) {
			if (!is_dir($set['attachpath'])) {
				@rename($attachpath,$set['attachpath']) ? $setting['att'] = $set['attachpath'] : adminmsg('setting_777');
			} else {
				$setting['att'] = $set['attachpath'];
			}
		}
		!empty($setting) && write_config($setting);
		$config['attachdir']=='1' && $config['attachdir'] = '0';
	}
	!is_array($config) && $config = array();
	updatesetting($config);
	adminmsg('operate_success');
}
?>