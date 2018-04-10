<?php
/**
 * Copyright (c) 2003-07  PHPWind.net. All rights reserved.
 * 
 * @filename: global.php
 * @author: Noizy (noizyfeng@gmail.com), QQ:7703883
 * @modify: Mon Mar 12 09:31:17 CST 2007
 */
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
$onlineip = preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$onlineip) ? $onlineip : 'Unknown';
$REQUEST_URI  = $user_file.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '');
$blog_version = '6.0';

require_once(R_P.'admin/defend.php');
if (!in_array($action,array('blogdata','comment','itemcp','post','userinfo'))) {
	//'blogdata','comment','itemcp','post','userinfo','global','top'
	foreach ($_POST as $_key => $_value) {
		!ereg('^\_',$_key) && strlen(${$_key})<1 && ${$_key} = $_POST[$_key];
	}
	foreach ($_GET as $_key => $_value) {
		!ereg('^\_',$_key) && strlen(${$_key})<1 && ${$_key} = $_GET[$_key];
	}
}
$db_debug && error_reporting(E_ALL ^ E_NOTICE);
$ob_check = !$action ? 1 : 0;
ObStart();

require_once GetLang('left');
include_once(D_P.'data/cache/level_cache.php');
include_once(D_P.'data/sql_config.php');
include_once(D_P.'data/cache/dbreg.php');
$db_cvtime!=0 && $timestamp += $db_cvtime*60;
!$db_perpage  && $db_perpage = 30;
$db_sqlpre  = $PW;
$db_sqlname = $dbname;
$B_url  	= $db_blogurl;
$imgdir		= R_P.$picpath;
$attachdir	= R_P.$attachpath;
$imgpath	= $db_http != 'N' ? $db_http : $picpath;
$attpath	= $attachpath;
$_alllevel	= $_gdefault+$_gsystem+$_gmember+$_gspecial;
$temp  = (strpos($_SERVER['PHP_SELF'],$db_dir)!==false) ? substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],$db_dir)) : $_SERVER['PHP_SELF'];
$db_blogurl = 'http://'.$_SERVER['HTTP_HOST'].substr($temp,0,strrpos($temp,'/'));

if (file_exists(D_P."data/style/$db_defaultstyle.php") && strpos($db_defaultstyle,'..')===false) {
	@include Pcv(D_P."data/style/$db_defaultstyle.php");
} else {
	@include D_P.'data/style/wind.php';
}
if ($db_blogifopen==0 && !GetCookie('AdminUser')) {
	$groupid = '2';
	usermsg($db_whyblogclose);
}
if (GetCookie('lastvisit')) {
	list($c_oltime,$lastvisit,$lastpath) = explode("\t",GetCookie('lastvisit'));
	$onblogtime = $timestamp-$lastvisit;
	$onblogtime<$db_onlinetime && $c_oltime += $onblogtime+0;
} else {
	$c_oltime = $onblogtime = 0;
}
if ($db_refreshtime!=0 && $REQUEST_URI==$lastpath && $onblogtime<$db_refreshtime) {
	!GetCookie('bloguser') && $groupid = '2';
	$manager = true;
	usermsg("refresh_limit");
}
Ipban();

$t		= array('hours'=>gmdate('G',$timestamp+$db_timedf*3600));
$tdtime	= (floor($timestamp/3600)-$t['hours'])*3600;

require_once(Pcv(R_P."mod/db_$database.php"));
$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$pconnect);
unset($dbhost,$dbuser,$dbpw,$dbname,$pconnect,$manager_pwd);
list($admin_uid,$admin_pwd) = explode("\t",StrCode(GetCookie('bloguser'),'DECODE'));
if (is_numeric($admin_uid) && strlen($admin_pwd)>16) {
	$admindb = User_info($admin_uid,$admin_pwd);
	$admin_uid = $admindb['uid'];
	$admin_name = $admindb['username'];
	$admin_bbsuid = $admindb['bbsuid'];
	$admin_bbsname = $admindb['bbsid'];
	$admin_icon = showfacedesign($admindb['icon']);
	$groupid = $admindb['groupid']=='-1' ? $admindb['memberid'] : $admindb['groupid'];
	$_datefm = $admindb['datefm'];
	$_timedf = $admindb['timedf'];
	unset($admin_pwd);
} else {
	$groupid = '2';
	$admin_icon = $imgpath.'/upload/none.gif';
	$admin_uid = $admin_pwd = $admin_name = $_datefm = $_timedf = '';
	unset($admindb,$admin_uid,$admin_pwd,$admin_name,$_datefm,$_timedf);
}
list(,,$logingd) = explode("\t",$db_gdcheck);
if ($logingd) {
	$rawwindid = (!$admin_name) ? 'guest' : rawurlencode($admin_name);
} else {
	$rawwindid = '';
}
(int)$groupid<1 && $groupid = '2';
if (file_exists(D_P."data/groupdb/group_$groupid.php")) {
	require_once(Pcv(D_P."data/groupdb/group_$groupid.php"));
} else {
	require_once(D_P.'data/groupdb/group_1.php');
}
$editor = ($_GROUP['wysiwyg'] && $admindb['editor']) ? 'wysiwyg' : 'windcode';
#passport
if ($db_pptifopen && $db_ppttype == 'client') {
	$pptforward  = rawurlencode($db_blogurl);
	$loginurl	 = "$db_pptserverurl/$db_pptloginurl?forward=$pptforward";
	$loginouturl = "$db_pptserverurl/$db_pptloginouturl&forward=$pptforward";
	$regurl 	 = "$db_pptserverurl/$db_pptregurl?forward=$pptforward";
	$ckurl		 = "ck.php";
} else {
	$loginurl	 = 'login.php';
	$loginouturl = 'login.php?action=quit';
	$regurl 	 = 'register.php';
	$ckurl		 = 'ck.php';
}
list($db_metatitle,$db_metakeyword,$db_metadescrip) = explode('@:wind:@',$db_metadata);
$logindb = $lg_logindb ? array_flip(explode("\t",$lg_logindb)) : array();
foreach ($logindb as $key => $value) {
	$value = $ulang['lg'.$key];
	$logindb[$key] = $value;
}
if (!$admin_uid) {
	if($db_pptifopen){
		ObHeader($B_url);
	}else{
		include PrintEot('login');footer('N');
	}
}
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$referer_a = @parse_url($_SERVER['HTTP_REFERER']);
	$s_host = $_SERVER['HTTP_HOST'];
	strpos($s_host,':') && $s_host = substr($s_host,0,strpos($s_host,':'));
	($referer_a['host'] && $referer_a['host']!=$s_host) && usermsg('undefined_action');
	if (!defined('AJAXUSER')) {
		$verify = GetGP('verify','G');
		PostCheck($verify);
	}
}
Cookie('lastvisit',$c_oltime."\t".$timestamp."\t".$REQUEST_URI);

function P_unlink($filename){
	strpos($filename,'..')!==false && exit('Forbidden');
	@unlink($filename);
}
function substrs($content,$length = null,$add=' ..'){
	global $db_charset;
	if (empty($length)) return $content;
	if (strlen($content) > $length) {
		if ($db_charset!='utf8' && $db_charset!='utf-8') {
			$retstr = '';
			for ($i = 0; $i < $length - 2; $i++) {
				$retstr .= ord($content[$i]) > 127 ? $content[$i].$content[++$i] : $content[$i];
			}
			return $retstr.$add;
		} else {
			return utf8_trim(substr($content,0,$length)).$add;
		}
	} else {
		return $content;
	}
}
function utf8_trim($str){
	$hex = '';
	for ($i=strlen($str)-1;$i>=0;$i-=1) {
		$hex .= ' '.ord($str[$i]);
		$ch   = ord($str[$i]);
		if (($ch & 128)==0 || ($ch & 192)==192) {
			return substr($str,0,$i);
		}
	}
	return($str.$hex);
}
function get_date($timestamp,$timeformat=''){
	global $db_datefm,$db_timedf,$_datefm,$_timedf;
	$date_show=$timeformat ? $timeformat : ($_datefm ? $_datefm : $db_datefm);
	if($_timedf){
		$offset = $_timedf=='111' ? 0 : $_timedf;
	}else{
		$offset = $db_timedf=='111' ? 0 : $db_timedf;
	}
	return gmdate($date_show,$timestamp+$offset*3600);
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
function N_InArray($needle,$haystack){
	if (is_array($haystack) && in_array($needle,$haystack)) {
		return true;
	}
	return false;
}
function showfacedesign($usericon){
	global $imgpath;
	if (!$usericon) {
		return $imgpath.'/upload/none.gif';
	} elseif (preg_match('/^http/i',$usericon)) {
		return $usericon;
	} else {
		return $imgpath.'/upload/'.$usericon;
	}
}
function User_info($winduid,$windpwd){
	global $db,$timestamp,$db_onlinetime,$db_ifonlinetime,$c_oltime,$onlineip,$db_ipcheck,$action;
	$rt = $db->get_one("SELECT u.*,ui.* FROM pw_user u LEFT JOIN pw_userinfo ui USING(uid) WHERE u.uid='$winduid'");
	$loginout = 0;
	if ($db_ipcheck==1 && strpos($rt['onlineip'],$onlineip)===false) {
		$iparray  = explode('.',$onlineip);
		strpos($rt['onlineip'],$iparray[0].'.'.$iparray[1])===false && $loginout = 1;
		unset($iparray);
	}
	if (!$rt || PwdCode($rt['password']) != $windpwd || $loginout==1) {
		$rt = array(); $GLOBALS['groupid']='2';
		if (!function_exists('Loginout')) {
			require_once(R_P.'mod/checkpass_mod.php');
		}
		Loginout();
	} else {
		$rt['uid'] = $winduid;
		$action != 'userinfo' && $rt['password'] = null;
		if ($timestamp-$rt['lastvisit']>$db_onlinetime || $timestamp-$rt['lastvisit']>3600) {
			$ct = "lastvisit='$timestamp',thisvisit='$timestamp'";
			if ($db_ifonlinetime==1 && $c_oltime > 0) {
				($c_oltime>$db_onlinetime*1.2) && $c_oltime = $db_onlinetime;
				$ct .= ",onlinetime=onlinetime+'$c_oltime'";
				$c_oltime=0;
			}
			$db->update("UPDATE pw_user SET $ct WHERE uid='$winduid'");
		}
	}
	return $rt;
}
function PwdCode($pwd){
	return md5($_SERVER["HTTP_USER_AGENT"].$pwd.$GLOBALS['db_hash']);
}
function SafeCheck($CK,$PwdCode){
	global $timestamp;
	$t = $timestamp - $CK[0];
	if ($t > 1800 || $CK[2] != md5($PwdCode.$CK[0])) {
		Cookie('cknum','',0);
		return false;
	} else {
		$CK[0] = $timestamp;
		$CK[2] = md5($PwdCode.$timestamp);
		$value = implode("\t",$CK);
		$cknum  = StrCode($value);
		Cookie('cknum',StrCode($value));
		return true;
	}
}
function footer($inchtml='Y'){
	global $blog_version,$db_obstart;
	$inchtml=='Y' && include PrintEot('userbottom');
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
function FormCheck($pre,$url,$add){
	return '<form'.stripslashes($pre).' action="'.EncodeUrl($url).'&"'.stripslashes($add).'>';
}
function EncodeUrl($url){
	global $db_hash,$admin_name,$admin_gid;
	$url_a = substr($url,strrpos($url,'?')+1);
	$commer = '';
	if (strpos($url,'&')!==false) {
		substr($url,-1)!='&' && $commer = '&';
	} else {
		substr($url,-1)!='?' && $commer = '?';
	}
	parse_str($url_a,$url_a);
	$source = '';
	foreach ($url_a as $key => $value) {
		$source .= $key.$value;
	}
	$url .= $commer.'verify='.substr(md5($source.$admin_name.$admin_gid.$db_hash),0,8);
	return $url;
}
function PostCheck($verify){
	global $db_hash,$admin_name,$admin_gid;
	$source = '';
	foreach ($_GET as $key => $value) {
		$key!='verify' && $source .= $key.$value;
	}
	$verify!=substr(md5($source.$admin_name.$admin_gid.$db_hash),0,8) && usermsg('illegal_request');
	return true;
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
function Pcv($filename,$ifcheck = true){
	(preg_match('/http(s)?:\/\//i',$filename) || ($ifcheck && strpos($filename,'..')!==false)) && exit('Forbidden');
	return $filename;
}
function Ipban(){
	global $db_ipban,$onlineip;
	if ($db_ipban) {
		$baniparray = explode(',',$db_ipban);
		foreach ($baniparray as $banip) {
			if (!$banip) continue;
			$banip = trim($banip);
			(strpos(','.$onlineip.'.',','.$banip.'.')!==false) && usermsg('ip_ban');
		}
	}
	return true;
}
function usermsg($msg,$jumpurl = null,$t = 2){
	@extract($GLOBALS, EXTR_SKIP);
	require_once GetLang('msg');
	if ($msg == 'undefined_action') {
		$jumpurl = $admin_file;
	}
	$lang[$msg] && $msg = $lang[$msg];
	include PrintEot('usermsg');footer();
}
function GetCookie($Var){
	return $_COOKIE[CookiePre().'_'.$Var];
}
function Cookie($ck_Var,$ck_Value,$ck_Time='F',$httponly = true){
	global $timestamp,$db_ckpath,$db_ckdomain,$islocalhost;
	if ($islocalhost) {
		$db_ckpath = '/'; $db_ckdomain = '';
	} else {
		/*
		if (!$db_ckdomain) {
			$pre_host = strtolower(substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],'.'))+1);
			$db_ckdomain = substr($_SERVER['HTTP_HOST'],strpos($_SERVER['HTTP_HOST'],'.')+1);
			$db_ckdomain = '.'.((strpos($db_ckdomain,'.')===false) ? $_SERVER['HTTP_HOST'] : $db_ckdomain);
			if (strpos($B_url,$pre_host)!==false) {
				$db_ckdomain = $pre_host.$db_ckdomain;
			}
		}
		*/
		if (!$db_ckdomain) {
			$db_ckdomain = substr($_SERVER['HTTP_HOST'],strpos($_SERVER['HTTP_HOST'],'.')+1);
			if (strlen($db_ckdomain)==5 && substr($db_ckdomain,-2)=='cn') {
				$db_ckdomain = '';
			} else {
				$db_ckdomain = '.'.((strpos($db_ckdomain,'.')===false) ? $_SERVER['HTTP_HOST'] : $db_ckdomain);
				$pre_host = strtolower(substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],'.'))+1);
				if (strpos($B_url,$pre_host)!==false) {
					$db_ckdomain = $pre_host.$db_ckdomain;
				}
			}
		}
	}
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
function GetLang($lang,$EXT='php'){
	global $tplpath;
	!file_exists(R_P."template/$tplpath/user/cp_lang_$lang.$EXT") && $tplpath = 'default';
	return R_P."template/$tplpath/user/cp_lang_$lang.$EXT";
}
function PrintEot($template,$EXT="htm"){
	global $tplpath;
	!file_exists(R_P."template/$tplpath/user/$template.$EXT") && $tplpath = 'default';
	return R_P."template/$tplpath/user/$template.$EXT";
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
function Strip_S(&$array){
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$array[$key] = stripslashes($value);
			} else {
				Strip_S($array[$key]);
			}
		}
	}
}
function Add_S(&$array){
	if (is_array($array)) {
		array_map('Add_S',&$array);
	} else {
		$array = addslashes($array);
	}
}





function forumoption($catetype,$cid=''){
	@include(D_P.'data/cache/forum_cache_'.$catetype.'.php');
	$catetype = ${'_'.strtoupper($catetype)};
	$forumcache = '';
	foreach ($catetype as $value) {
		$add = '';
		$value['name'] = preg_replace("/\<(.+?)\>/eis",'',$value['name']);
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$selected	 = ($cid && $value['cid'] == $cid) ? ' SELECTED' : '';
		$forumcache .= '<option value="'.$value['cid'].'"'.$selected.'>'.$add.' '.$value['name'].'</option>';
	}
	return $forumcache;
}function itemtypeoption($uid,$type,$typeid=''){
	global $db;

	$itemtypeoption='';
	$query = $db->query("SELECT typeid,name FROM pw_itemtype WHERE uid='$uid' AND type='$type' ORDER BY vieworder ASC");
	while ($rt = $db->fetch_array($query)){
		if($typeid && $rt['typeid'] == $typeid){
			$itemtypeoption .= "<option value='$rt[typeid]' selected>$rt[name]</option>";
		}else{
			$itemtypeoption .= "<option value='$rt[typeid]'>$rt[name]</option>";
		}
	}
	return $itemtypeoption;
}function ifcheck($var,$out){
	global ${$out.'_Y'},${$out.'_N'};
	empty($var) ? ${$out.'_N'}='CHECKED' : ${$out.'_Y'} = 'CHECKED';
}

function checkselid($selid){
	if(is_array($selid)){
		$extra=$ret='';
		foreach($selid as $key => $value){
			if(!is_numeric($value)) return;
			$ret.=$extra.$value;  $extra=',';
		}
		return $ret;
	} else{
		return;
	}
}
function ObHeader($URL){
	global $db_obstart,$db_blogurl,$db_htmifopen;
	($db_htmifopen && strtolower(substr($URL,0,4))!='http') && $URL = $db_blogurl.'/'.$URL;
	ob_end_clean();
	if ($db_obstart) {
		header('Location: '.$URL);exit;
	} else {
		ObStart();
		echo '<meta http-equiv="refresh" content="0;url='.$URL.'">';exit;
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
function N_strireplace($search,$replace,$subject){
	return function_exists('str_ireplace') ? str_ireplace($search,$replace,$subject) : preg_replace("/$search/i",$replace,$subject);
}
function N_stripos($haystack,$needle){
	if (function_exists('stripos')) {
		if (stripos($haystack,$needle)!==false) {
			return true;
		}
		return false;
	}
	if (@preg_match("/$needle/i",$haystack)) {
		return true;
	}
	return false;
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