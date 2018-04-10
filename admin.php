<?php
error_reporting(E_ERROR | E_PARSE);

@ini_set('zend.ze1_compatibility_mode',false);
@ini_set('magic_quotes_runtime',false);

$t_array	= explode(' ',microtime());
$P_S_T  	= $t_array[0]+$t_array[1];
$timestamp  = time();
define('R_P',getdirname(__FILE__));
define('D_P',R_P);

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$admin_file = $_SERVER['PHP_SELF'];

require_once(R_P.'admin/admincp.php');
$basename = '';
if (empty($action)) {
	require_once(R_P.'admin/index.php');
} elseif ($action == 'admin') {
	$pw_size = $o_size = 0;
	$query = $db->query('SHOW TABLE STATUS');
	while ($rt = $db->fetch_array($query)) {
		if (preg_match("/^$PW/",$rt['Name'])) {
			$pw_size += $rt['Data_length'] + $rt['Index_length'] + 0;
		} else {
			$o_size  += $rt['Data_length'] + $rt['Index_length'] + 0;
		}
	}
	$db->free_result($query);
	$o_size		 = number_format($o_size/(1024*1024),2);
	$pw_size	 = number_format($pw_size/(1024*1024),2);
	$systemtime  = gmdate("Y-m-d H:i",time()+$db_timedf*3600);
	$altertime   = gmdate("Y-m-d H:i",$timestamp+$db_timedf*3600);
	$sysversion  = PHP_VERSION;
	$sysos		 = $_SERVER['SERVER_SOFTWARE'];
	$max_upload  = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled';
	$max_ex_time = ini_get('max_execution_time').' seconds';
	if (ini_get('sendmail_path')) {
		$sys_mail = 'Unix Sendmail ( Path: '.ini_get('sendmail_path').')';
	} elseif (ini_get('SMTP')) {
		$sys_mail = 'SMTP ( Server: '.ini_get('SMTP').')';
	} else {
		$sys_mail = 'Disabled';
	}
	$ifcookie  = isset($_COOKIE) ? 'SUCCESS' : 'FAIL';
	$dbversion = $db->server_info();
	include PrintEot('admin');footer();
} elseif ((in_array($action.'_'.$job,array('manager_rightset','manager_manager','manager_diy')) && If_manager) || ckrightset($rightset,$action,$job)) {
	$basename = "$admin_file?action=$action";
	require_once(Pcv(R_P."admin/$action.php"));
} else {
	adminmsg('undefined_action');
}
function ckrightset($rightset,$action,$job = null){
	$rightset = array_keys($rightset);
	$check = !empty($job) ? $action.'_'.$job : '';
	foreach ($rightset as $value) {
		if (strpos($value,'_')!==false) {
			if ($value == $check) return true;
		} else {
			if ($value == $action) return true;
		}
	}
	return false;
}
function getdirname($path){
	if (strpos($path,'\\')!==false) {
		return substr($path,0,strrpos($path,'\\')).'/';
	} elseif (strpos($path,'/')!==false) {
		return substr($path,0,strrpos($path,'/')).'/';
	} else {
		return './';
	}
}
?>