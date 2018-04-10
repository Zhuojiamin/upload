<?php
!defined('R_P') && exit('Forbidden');
unset($_ENV,$HTTP_ENV_VARS,$_REQUEST,$HTTP_POST_VARS,$HTTP_GET_VARS,$HTTP_POST_FILES,$HTTP_COOKIE_VARS);

if (!get_magic_quotes_gpc()) {
	!empty($_POST)	 && Add_S($_POST);
	!empty($_GET)	 && Add_S($_GET);
	!empty($_COOKIE) && Add_S($_COOKIE);
}
!empty($_FILES) && Add_S($_FILES);

function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');
if ($_SERVER['REMOTE_ADDR']) {
	$onlineip = $_SERVER['REMOTE_ADDR'];
} elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
	$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$onlineip = $_SERVER['HTTP_CLIENT_IP'];
}
$islocalhost = ($_SERVER['HTTP_HOST']=='localhost' || preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}(\:[0-9]{2,})?$/',$_SERVER['HTTP_HOST'])) ? 1 : 0;
$onlineip = preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$onlineip) ? $onlineip : 'Unknown';
$REQUEST_URI  = $admin_file.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');

$blog_version = '6.0';
$blog_repair  = '';


require_once(R_P.'admin/defend.php');
if ($action=='quit') {
	Cookie('AdminUser','',0);
	ObHeader($admin_file);
}
$db_debug && error_reporting(E_ALL ^ E_NOTICE);
$ob_check = (!$action || $action=='setting') ? 1 : 0;
ObStart();

!$db_defaultstyle && $db_defaultstyle = 'wind';
!$db_defaultustyle && $db_defaultustyle = 'default';
if (file_exists(D_P."data/style/$skin.php")) {
	@include(Pcv(D_P."data/style/$skin.php"));
} else {
	@include(Pcv(D_P."data/style/$db_defaultstyle.php"));
}
file_exists('install.php') && adminmsg('installfile_exists');
require_once GetLang('cpleft');
include_once(D_P.'data/cache/level_cache.php');
include_once(D_P.'data/sql_config.php');
!$manager && adminmsg('sql_config');
$db_cvtime!=0 && $timestamp += $db_cvtime*60;
!$db_perpage  && $db_perpage = 30;
$B_url  	= $db_blogurl;
$imgdir		= R_P.$picpath;
$attachdir	= R_P.$attachpath;
$imgpath	= $db_http != 'N' ? $db_http : $picpath;
$attpath	= $attachpath;
$_alllevel	= $_gdefault+$_gsystem+$_gmember+$_gspecial;
if (GetCookie('refreshlimit')) {
	list($lastvisit,$lastpath) = explode("\t",GetCookie('refreshlimit'));
	$onblogtime = $timestamp-$lastvisit;
} else {
	$lastvisit = $onblogtime = 0;
}

$recordfile = D_P.'data/cache/admin_log.php';
$F_count	= F_L_count($recordfile);
$L_T		= 1200 - ($timestamp - @filemtime($recordfile));
$L_left 	= 15 - $F_count;

if ($F_count>15 && $L_T>0) {
	require_once GetLang('cpmsg');
	$msg = $lang['login_fail'];
	include PrintEot('adminlogin');footer('N');
}

require_once(Pcv(R_P."mod/db_$database.php"));
$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$pconnect);
unset($dbhost,$dbuser,$dbpw,$dbname,$pconnect);

require_once(R_P.'admin/cache.php');

$AdminUser = $CK = $admin_name = $logindata = '';
if ($_POST['admin_name'] && $_POST['admin_pwd']) {
	$AdminUser = StrCode($timestamp."\t".$_POST['admin_name']."\t".md5(PwdCode(md5($_POST['admin_pwd'])).$timestamp));
	Cookie('AdminUser',$AdminUser);
} elseif (GetCookie('AdminUser')) {
	$AdminUser = GetCookie('AdminUser');
}

if ($AdminUser) {
	$CK 		= explode("\t",StrCode($AdminUser,'DECODE'));
	$admin_name = stripcslashes($CK[1]);
}
list($admingd) = explode("\t",$db_gdcheck);
if ($admingd) {
	$rawwindid = (!$admin_name) ? 'guest' : rawurlencode($admin_name);
} else {
	$rawwindid = '';
}
$lg_num = GetGP('lg_num','P');
$rightset = checkpass($CK);
if (empty($rightset)) {
	if ($_POST['admin_name'] && $_POST['admin_pwd']) {
		$logindata = Char_cv($_POST['admin_name'])."\t".Char_cv($_POST['admin_pwd'])."\t".'Logging Failed'."\t".$onlineip."\t".$timestamp."\n";
		writeover($recordfile,$logindata,"ab");
		adminmsg('login_error');
	}
	include PrintEot('adminlogin');footer('N');
}
$admin_gid = $rightset['gid'];
$admin_level = If_manager ? 'manager' : $_alllevel[$admin_gid]['title'];
$_POST['admin_name'] && ObHeader($REQUEST_URI);

if ($action != 'left' && $action != 'index') {
	$_postdata	 = $_POST ? PostLog($_POST) : '';
	$record_name = Char_cv($admin_name);
	$record_URI	 = Char_cv($REQUEST_URI);
	$new_record  = "<?die;?>|$record_name||$record_URI|$onlineip|$timestamp|$_postdata|\n";
	writeover($recordfile,$new_record,"ab");
}
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$referer_a = @parse_url($_SERVER['HTTP_REFERER']);
	$s_host = $_SERVER['HTTP_HOST'];
	strpos($s_host,':') && $s_host = substr($s_host,0,strpos($s_host,':'));
	($referer_a['host'] && $referer_a['host']!=$s_host) && adminmsg('undefined_action');
	if (!defined('AJAXADMIN')) {
		$verify = GetGP('verify','G');
		PostCheck($verify);
	}
}

Cookie('refreshlimit',$timestamp."\t".$REQUEST_URI);

function readover($filename,$method='rb',$readsize='D'){
	strpos($filename,'..')!==false && exit('Forbidden');
	$filesize = @filesize($filename);
	$readsize!='D' && $filesize = min($filesize,$readsize);
	$filedata = '';
	if ($handle = @fopen($filename,$method)) {
		flock($handle,LOCK_SH);
		$filedata = @fread($handle,$filesize);
		fclose($handle);
	}
	return $filedata;
}
function FormCheck($pre,$url,$add){
	return '<form'.stripslashes($pre).' action="'.EncodeUrl($url).'&"'.stripslashes($add).'>';
}
function EncodeUrl($url){
	global $db_hash,$admin_name,$admin_gid;
	$url_a = substr($url,strrpos($url,'?')+1);
	substr($url,-1)=='&' && $url = substr($url,0,-1);
	parse_str($url_a,$url_a);
	$source = '';
	foreach ($url_a as $key => $value) {
		$source .= $key.$value;
	}
	$url .= '&verify='.substr(md5($source.$admin_name.$admin_gid.$db_hash),0,8);
	return $url;
}
function PostCheck($verify){
	global $db_hash,$admin_name,$admin_gid;
	$source = '';
	foreach ($_GET as $key => $value) {
		$key!='verify' && $source .= $key.$value;
	}
	$verify!=substr(md5($source.$admin_name.$admin_gid.$db_hash),0,8) && adminmsg('illegal_request');
	return true;
}
function PostLog($log){
	foreach ($log as $key => $value) {
		if (is_array($value)) {
			$data .= "$key=array(".PostLog($value).")";
		} else {
			$value = str_replace(array("\n","\r","|"),array('','','&#124;'),$value);
			$data .= "$key=".(($key=='password' || $key=='check_pwd') ? '***, ' : "$value, ");
		}
	}
	return $data;
}
function writeover($filename,$data,$method="rb+",$iflock=1,$check=1,$chmod=1){
	$check && strpos($filename,'..')!==false && exit('Forbidden');
	touch($filename);
	$handle = fopen($filename,$method);
	$iflock && flock($handle,LOCK_EX);
	fwrite($handle,$data);
	$method=="rb+" && ftruncate($handle,strlen($data));
	fclose($handle);
	$chmod && @chmod($filename,0777);
}
function checkpass($CK){
	global $db,$manager,$manager_pwd,$lg_num,$admingd,$leftlang;
	if (!$CK) {
		return false;
	}
	Add_S($CK);
	($admingd && $_POST['Login_f']==1) && GdConfirm($lg_num);
	$rightset = array();
	if (strtolower($CK[1])==strtolower($manager)) {
		if (!SafeCheck($CK,PwdCode($manager_pwd))) {
			$pwd = $db->get_value("SELECT password FROM pw_user WHERE username='$CK[1]'");
			if (!SafeCheck($CK,PwdCode($pwd))) {
				return false;
			}
		}
		unset($pwd);
		define('If_manager',1);
		foreach ($leftlang as $left) {
			foreach ($left as $array) {
				foreach ($array['option'] as $key => $value) {
					if (isset($value[0])) {
						$rightset[$key] = 1;
					} else {
						foreach ($value as $k => $v) {
							$c_key = $key.'_'.$k;
							$rightset[$c_key] = 1;
						}
					}
				}
			}
		}
		$rightset['gid'] = 3;
	} else {
		define('If_manager',0);
		$admindb = $db->get_one("SELECT g.type,g.admincp,u.password,u.groupid FROM pw_group g LEFT JOIN pw_user u ON g.gid=u.groupid WHERE u.username='$CK[1]'");
		if (!$admindb || !$admindb['admincp'] || ($admindb['type']!='system' && $admindb['type']!='special') || !SafeCheck($CK,PwdCode($admindb['password']))) {
			return false;
		}
		$rightset = $db->get_value("SELECT value FROM pw_rightset WHERE gid='$admindb[groupid]'");
		$rightset = $rightset ? unserialize($rightset) : array();
		$rightset['gid'] = $admindb['groupid'];
	}
	return $rightset;
	
}
function GdConfirm($code){
	Cookie('cknum','',0);
	if(!$code || !SafeCheck(explode("\t",StrCode(GetCookie('cknum'),'DECODE')),$code,'cknum',300)){
		global $basename,$admin_file;
		Cookie('AdminUser','',0);
		$basename = $admin_file;
		adminmsg('gdcode_error');
	}
}
function SafeCheck($CK,$PwdCode,$var='AdminUser',$expire=1800){
	global $timestamp;
	$t = $timestamp - $CK[0];
	if ($t > $expire || $CK[2] != md5($PwdCode.$CK[0])) {
		Cookie($var,'',0);
		return false;
	} else {
		$CK[0] = $timestamp;
		$CK[2] = md5($PwdCode.$timestamp);
		$value = implode("\t",$CK);
		${$var}  = StrCode($value);
		Cookie($var,StrCode($value));
		return true;
	}
}
function PwdCode($pwd){
	return md5($_SERVER["HTTP_USER_AGENT"].$pwd.$GLOBALS['db_hash']);
}
function StrCode($string,$action='ENCODE'){
	$key	= substr(md5($_SERVER["HTTP_USER_AGENT"].$GLOBALS['db_hash']),8,18);
	$action == 'DECODE' && $string = base64_decode($string);
	$len	= strlen($key); $code = '';
	for ($i=0; $i<strlen($string); $i++) {
		$k		= $i % $len;
		$code  .= $string[$i] ^ $key[$k];
	}
	$action == 'ENCODE' && $code = base64_encode($code);
	return $code;
}
function F_L_count($filename,$offset = '2000'){
	$count = $count_F = 0;
	if ($fp = @fopen($filename,"rb")) {
		global $onlineip;
		flock($fp,LOCK_SH);
		fseek($fp,-$offset,SEEK_END);
		$readb = fread($fp,$offset);
		fclose($fp);
		$readb   = explode("\n",trim($readb));
		$count   = count($readb);
		for ($i=$count-1;$i>0;$i--) {
			if (strpos($readb[$i],"\tLogging Failed\t$onlineip|")===false) {
				break;
			}
			$count_F++;
		}
	}
	return $count_F;
}
function adminmsg($msg,$jumpurl = null,$t = 2){
	@extract($GLOBALS, EXTR_SKIP);
	empty($jumpurl) && $basename && $jumpurl = $basename;
	require_once GetLang('cpmsg');
	if ($msg == 'undefined_action') {
		Cookie('AdminUser','',0);
		$jumpurl = $admin_file;
	}
	$lang[$msg] && $msg = $lang[$msg];
	include PrintEot('adminmsg');footer();
}
function GetLang($lang,$EXT='php'){
	global $tplpath;
	!file_exists(R_P."template/$tplpath/admin/cp_lang_$lang.$EXT") && $tplpath = 'default';
	return R_P."template/$tplpath/admin/cp_lang_$lang.$EXT";
}
function PrintEot($template,$EXT="htm"){
	global $tplpath;
	!file_exists(R_P."template/$tplpath/admin/$template.$EXT") && $tplpath = 'default';
	return R_P."template/$tplpath/admin/$template.$EXT";
}
function footer($inchtml='Y'){
	global $blog_version,$db_obstart;
	$inchtml=='Y' && include PrintEot('adminbottom');
	$output = str_replace(array('<!--<!--<!---->','<!--<!---->','<!---->'),'',ob_get_contents());
	$inchtml=='Y' && $output = preg_replace(
		'/\<form([^\<\>]*)\saction=[\'|"]?([^\s"\'\<\>]+)[\'|"]?([^\<\>]*)\>/ies',
		"FormCheck('\\1','\\2','\\3')",
		$output
	);
	ob_end_clean();
	echo ObContents($output);
	ObFlush();
	exit;
}
function Pcv($filename,$ifcheck = true){
	(preg_match('/http(s)?:\/\//i',$filename) || ($ifcheck && strpos($filename,'..')!==false)) && exit('Forbidden');
	return $filename;
}
function ObFlush(){
    if (N_output_zip() == 'ob_gzhandler') {
		return;
	}
	if (php_sapi_name() != 'apache2handler' && php_sapi_name() != 'apache2filter') {
		flush();
	}
	if (function_exists('ob_get_status') && ob_get_status() && function_exists('ob_flush') && !ObGetMode($GLOBALS['db_obstart'])) {
		@ob_flush();
	} 
}
function ObStart(){
	ObGetMode() == 1 ? ob_start('ob_gzhandler') : ob_start();
}
function ObContents($output){
	ob_end_clean();
	if (!headers_sent() && $GLOBALS['db_obstart'] && $_SERVER['HTTP_ACCEPT_ENCODING'] && N_output_zip()!='ob_gzhandler') {
		$encoding = '';
		if (strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false) {
			$encoding = 'gzip';
		} elseif (strpos(' '.$_SERVER['HTTP_ACCEPT_ENCODING'],'x-gzip') !== false) {
			$encoding = 'x-gzip';
		}
		if ($encoding && function_exists('crc32') && function_exists('gzcompress')) {
			header('Content-Encoding: '.$encoding);
			$outputlen  = strlen($output);
			$outputzip  = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			$outputzip .= substr(gzcompress($output,$GLOBALS['db_obstart']),0,-4);
			$outputzip .= @pack('V',crc32($output));
			$output = $outputzip.@pack('V',$outputlen);
		} else {
			ObStart();
		}
	} else {
		ObStart();
	}
	return $output;
}
function ObGetMode(){
	static $mode = null;
	if ($mode!==null) {
		return $mode;
	}
	$mode = 0;
	if ($GLOBALS['db_obstart'] && function_exists('ob_gzhandler') && N_output_zip()!='ob_gzhandler' && (!function_exists('ob_get_level') || ob_get_level()<1)) {
		$mode = 1;
	}
	return $mode;
}
function ObHeader($URL){
	echo "<meta http-equiv=\"expires\" content=\"0\">\n";
	echo "<meta http-equiv=\"Pragma\" content=\"no-cache\">\n";
	echo "<meta http-equiv=\"Cache-Control\" content=\"no-cache\">\n";
	echo "<meta http-equiv=\"Refresh\" content=\"0;url=$URL\">";
	exit;
}
function GetCookie($Var){
	return $_COOKIE[CookiePre().'_'.$Var];
}
function Cookie($ck_Var,$ck_Value,$ck_Time='F',$httponly = true){
	global $timestamp,$islocalhost;
	$db_ckpath = '/'; $db_ckdomain = '';
	if ($ck_Time=='F') {
		$ck_Time = $timestamp+31536000;
	} else {
		($ck_Value=='' && $ck_Time==0) && $ck_Time = $timestamp-31536000;
	}
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		return setcookie(CookiePre().'_'.$ck_Var, $ck_Value, $ck_Time, $db_ckpath, $db_ckdomain, GetSecure(), $httponly);
	} else {
		return setcookie(CookiePre().'_'.$ck_Var, $ck_Value, $ck_Time, $db_ckpath.($httponly ? '; HttpOnly' : ''), $db_ckdomain, GetSecure());
	}
}
function CookiePre(){
	return substr(md5($GLOBALS['db_hash']),0,5);
}
function GetSecure(){
	$https = array();
	$_SERVER['REQUEST_URI'] && $https = @parse_url($_SERVER['REQUEST_URI']);
	if (empty($https['scheme'])) {
		if ($_SERVER['HTTP_SCHEME']) {
			$https['scheme'] = $_SERVER['HTTP_SCHEME'];
		} else {
			$https['scheme'] = ($_SERVER['HTTPS'] && strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
		}
	}
	if ($https['scheme'] == 'https'){
		return true;
	}
	return false;
}
function Add_S(&$array){
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$array[$key] = addslashes($value);
			} else {
				Add_S($array[$key]);
			}
		}
	}
}

function checkselid($selid){
	if(is_array($selid)){
		$ret='';
		foreach($selid as $key => $value){
			if(!is_numeric($value)){
				return false;
			}
			$ret .= $ret ? ','.$value : $value;
		}
		return $ret;
	} else{
		return '';
	}
}
function Showtruemsg($msg){
	if (function_exists('usermsg')) {
		usermsg($msg);
	} elseif (function_exists('adminmsg')) {
		adminmsg($msg);
	} elseif (function_exists('Showmsg')) {
		Showmsg($msg);
	} else {
		exit($msg);
	}
}

function N_output_zip(){
	static $output_handler = null;
	if ($output_handler === null) {
		if (@ini_get('zlib.output_compression')) {
			$output_handler = 'ob_gzhandler';
		} else {
			$output_handler = @ini_get('output_handler');
		}
	}
	return $output_handler;
}
?>