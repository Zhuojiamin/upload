<?php
/**
*
*  Copyright (c) 2003-07  PHPWind.net. All rights reserved.
*  Support : http://www.phpwind.net
*  This software is the proprietary information of PHPWind.net.
*
*/
error_reporting(E_ERROR | E_PARSE);
@ini_set('zend.ze1_compatibility_mode',false);
@ini_set('magic_quotes_runtime',false);

$t_array = explode(' ',microtime());
$P_S_T	 = $t_array[0]+$t_array[1];
$timestamp  = time();
define('R_P',getdirname(__FILE__));
define('D_P',R_P);

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

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$REQUEST_URI  = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
$blog_version = '6.0';

require_once(R_P.'mod/defend.php');
$db_debug && error_reporting(E_ALL ^ E_NOTICE);

$B_url = $db_blogurl;
$temp  = (strpos($_SERVER['PHP_SELF'],$db_dir)!==false) ? substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],$db_dir)) : $_SERVER['PHP_SELF'];
$db_blogurl = 'http://'.$_SERVER['HTTP_HOST'].substr($temp,0,strrpos($temp,'/'));

ObStart();
require_once GetLang('index');
require_once(D_P.'data/sql_config.php');

$db_cvtime!=0 && $timestamp += $db_cvtime*60;
$imgdir		= R_P.$picpath;
$attachdir	= R_P.$attachpath;
defined('W_T') && $db_http = "$B_url/$picpath";
$imgpath	= $db_http != 'N' ? $db_http : $picpath;
$attpath	= $attachpath;
$db_sqlpre  = $PW;
$db_sqlname = $dbname;
$skinco 	= GetCookie('skinco') ? GetCookie('skinco') : '';
if ($db_blogifopen==0 && !GetCookie('AdminUser')) {
	$skin = $skinco ? $skinco : $db_defaultstyle;
	$groupid = '2';
	Showmsg($db_whyblogclose);
}
if (GetCookie('lastvisit')) {
	list($c_oltime,$lastvisit,$lastpath) = explode("\t",GetCookie('lastvisit'));
	$onblogtime = $timestamp-$lastvisit;
	$onblogtime<$db_onlinetime && $c_oltime += $onblogtime+0;
} else {
	$c_oltime = $onblogtime = 0;
}
if ($db_refreshtime!=0 && $REQUEST_URI==$lastpath && $onblogtime<$db_refreshtime) {
	!GetCookie('bloguser') && $groupid='2';
	$manager = true;
	$skin = $skinco ? $skinco : $db_defaultstyle;
	Showmsg("refresh_limit");
}
Ipban();

$t		= array('hours'=>gmdate('G',$timestamp+$db_timedf*3600));
$tdtime	= (floor($timestamp/3600)-$t['hours'])*3600;

require_once(Pcv(R_P."mod/db_$database.php"));
$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$pconnect);
unset($dbhost,$dbuser,$dbpw,$dbname,$pconnect,$manager_pwd);

list($winduid,$windpwd) = explode("\t",StrCode(GetCookie('bloguser'),'DECODE'));
if (is_numeric($winduid) && strlen($windpwd)>16) {
	$winddb 	= User_info();
	$winduid	= $winddb['uid'];
	$windid 	= $winddb['username'];
	$windicon	= showfacedesign($winddb['icon']);
	$groupid	= $winddb['groupid']=='-1' ? $winddb['memberid'] : $winddb['groupid'];
	$_datefm	= $winddb['datefm'];
	$_timedf	= $winddb['timedf'];
} else {
	$groupid	= '2';
	$windicon	= $imgpath.'/upload/none.gif';
	$winduid	= $windpwd = $windid = $_datefm = $_timedf = '';
	unset($winddb,$winduid,$windpwd,$windid,$_datefm,$_timedf);
}
(int)$groupid<1 && $groupid = '2';
if (file_exists(D_P."data/groupdb/group_$groupid.php")) {
	require_once(Pcv(D_P."data/groupdb/group_$groupid.php"));
} else {
	require_once(D_P.'data/groupdb/group_1.php');
}
$windgroup = $_GROUP;
$islocalhost = ($_SERVER['HTTP_HOST']=='localhost' || preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}(\:[0-9]{2,})?$/',$_SERVER['HTTP_HOST'])) ? 1 : 0;
//(($_GROUP['ifdomain']==1 && !$winddb['domainname']) || !$_GROUP['ifdomain'] || !$db_domain || $islocalhost) && $db_userdomain = 0;
(!$db_domain || $islocalhost) && $db_userdomain = 0;
#passport
if ($db_pptifopen && $db_ppttype == 'client') {
	$pptforward  = rawurlencode($db_blogurl);
	$loginurl	 = "$db_pptserverurl/$db_pptloginurl?forward=$pptforward&";
	$loginouturl = "$db_pptserverurl/$db_pptloginouturl&forward=$pptforward";
	$regurl 	 = "$db_pptserverurl/$db_pptregurl?forward=$pptforward";
	$ckurl		 = "$db_pptserverurl/ck.php";
	$sendpwdurl  = "$db_pptserverurl/sendpwd.php";
} else {
	$loginurl	 = 'login.php';
	$loginouturl = 'login.php?action=quit';
	$regurl 	 = 'register.php';
	$ckurl		 = 'ck.php';
	$sendpwdurl  = 'sendpwd.php';
}
#passport
$_GET['skinco']  && $skinco = $_GET['skinco'];
$_POST['skinco'] && $skinco = $_POST['skinco'];
if ($skinco && file_exists(D_P."data/style/$skinco.php") && strpos($skinco,'..')===false) {
	$skin = $skinco;
	Cookie('skinco',$skinco);
}
Cookie('lastvisit',$c_oltime."\t".$timestamp."\t".$REQUEST_URI);
$lxheader = '';

if ($db_ads && (is_numeric($u) || ($a && strlen($a)<16)) && strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])===false) {
	if ($db_ads=='2' && !$windid) {
		Cookie('userads',"$u\t$a");
	} elseif ($db_ads=='1') {
		require_once(R_P.'mod/userads_inc.php');
	} else {
		unset($db_ads);
	}
}
//广告缓存
if(file_exists(R_P.'data/cache/adv_cache.php')){
	@include_once(R_P.'data/cache/adv_cache.php');
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
function N_InArray($needle,$haystack){
	if (is_array($haystack) && in_array($needle,$haystack)) {
		return true;
	}
	return false;
}
function refreshto($URL,$content,$statime=1){
	global $db_ifjump;
	$URL = str_replace('&#61;','=',$URL);
	if ($db_ifjump && $statime>0) {
		ob_end_clean();
		global $db_charset,$db_metadata,$db_blogname,$db_blogurl,$skin,$db_defaultstyle;
		ObStart();
		list($db_metatitle,$db_metakeyword,$db_metadescrip) = explode('@:wind:@',$db_metadata);
		if (file_exists(D_P."data/style/$skin.php") && strpos($skin,'..')===false) {
			@include Pcv(D_P."data/style/$skin.php");
		} elseif (file_exists(D_P."data/style/$db_defaultstyle.php") && strpos($db_defaultstyle,'..')===false) {
			@include Pcv(D_P."data/style/$db_defaultstyle.php");
		} else {
			@include D_P.'data/style/wind.php';
		}
		@extract($GLOBALS,EXTR_SKIP);
		require_once GetLang('refreshto');
		$lang[$content] && $content=$lang[$content];
		@require PrintEot('refreshto');
		exit;
	} else {
		ObHeader($URL);
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
function footer($inchtml = 'PrintEot'){
	global $db,$db_obstart,$db_footertime,$P_S_T,$db_ceoconnect,$blog_version,$imgpath,$db_htmifopen,$db_icpurl,$db_icp,$_FOOTNAV,$db_ceoemail;
	if ($inchtml) {
		$ft_gzip = 'Gzip '.($db_obstart==1 ? 'enabled' : 'disabled');
		$wind_spend = '';
		if ($db_footertime==1) {
			$db && $qn = $db->query_num;
			$t_array	= explode(' ',microtime());
			$totaltime  = number_format(($t_array[0]+$t_array[1]-$P_S_T),6);
			$wind_spend = "Time $totaltime second(s),query:$qn";
		}
		if ($db_icp) {
			$db_icp = ' <a href="'.($db_icpurl ? "$db_icpurl\">$db_icp" : "http://www.miibeian.gov.cn\">$db_icp").'</a>';
		}
		include $inchtml('footer');
	}
	$output = str_replace(array('<!--<!---->','<!---->'),'',ob_get_contents());
	if ($db_htmifopen) {
		$output = preg_replace(
			"/\<a(\s*[^\>]+\s*)href\=([\"|\']?)([^\"\'>\s]+\.php\?[^\"\'>\s]+)([\"|\']?)/ies",
			"Htm_cv('\\3','<a\\1href=\"')",
			$output
		);
	}
	ob_end_clean();
	echo ObContents($output);
	ObFlush();
	exit;
}
function Htm_cv($url,$tag){
	global $db_dir,$db_ext;
	if (!preg_match('/^http|ftp|telnet|mms|rtsp|admin.php|rss.php/ie',$url)) {
		$add = strpos($url,'#')!==false ? substr($url,strpos($url,'#')) : '';
		$url = str_replace(
			array('.php?','=','&',$add),
			array($db_dir,'-','-',''),
			$url
		).$db_ext.$add;
	}
	return stripslashes($tag.$url.'"');
}
function showfacedesign($usericon){
	global $imgpath;
	if (!$usericon) {
		return $imgpath.'/upload/none.gif';
	} elseif (preg_match('/^http/i',$usericon)) {
		return $usericon;
	} else {
		$attach_ext = strrchr($usericon,'.');
		$attach_name = substr($usericon,0,strrpos($usericon,'.'));
		if (file_exists(R_P."$imgpath/upload/{$attach_name}_thumb$attach_ext")) {
			return "$imgpath/upload/{$attach_name}_thumb$attach_ext";
		} else {
			return $imgpath.'/upload/'.$usericon;
		}
	}
}
function User_info(){
	global $db,$timestamp,$db_onlinetime,$winduid,$windpwd,$db_ifonlinetime,$c_oltime,$onlineip,$db_ipcheck;
	$rt = $db->get_one("SELECT u.*,ui.* FROM pw_user u LEFT JOIN pw_userinfo ui USING(uid) WHERE u.uid='$winduid'");
	$loginout = 0;
	if ($db_ipcheck==1 && strpos($rt['onlineip'],$onlineip)===false) {
		$iparray  = explode('.',$onlineip);
		strpos($rt['onlineip'],$iparray[0].'.'.$iparray[1])===false && $loginout = 1;
		unset($iparray);
	}
	if (!$rt || PwdCode($rt['password']) != $windpwd || $loginout==1) {
		unset($rt); $GLOBALS['groupid']='2';
		if (!function_exists('Loginout')) {
			require_once(R_P.'mod/checkpass_mod.php');
		}
		Loginout();
		Showmsg('ip_change');
	} else {
		$rt['uid'] = $winduid;
		$rt['password'] = null;
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
function SafeCheck($CK,$PwdCode,$var='cknum',$expire=1800){
	global $timestamp;
	$t = $timestamp - $CK[0];
	if ($t > $expire || $CK[2] != md5($PwdCode.$CK[0])) {
		Cookie($var,'',0);
		return false;
	} else {
		$CK[0] = $timestamp;
		$CK[2] = md5($PwdCode.$timestamp);
		$value = implode("\t",$CK);
		${$var} = StrCode($value);
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
			(strpos(','.$onlineip.'.',','.$banip.'.')!==false) && Showmsg('ip_ban');
		}
	}
	return true;
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
function Showmsg($msg_info,$url='',$time=1){
	@extract($GLOBALS, EXTR_SKIP);
	ob_end_clean();
	ObStart();
	require(R_P.'mod/header_inc.php');
	@include_once(D_P.'data/cache/novosh_cache.php');
	require GetLang('msg');
	$lang[$msg_info] && $msg_info = $lang[$msg_info];
	require_once PrintEot('showmsg');footer();
}

function GetLang($lang,$EXT='php'){
	global $tplpath;
	!file_exists(R_P."template/$tplpath/wind/lang_$lang.$EXT") && $tplpath = 'default';
	return R_P."template/$tplpath/wind/lang_$lang.$EXT";
}
function PrintEot($template,$EXT="htm"){
	global $tplpath;
	!file_exists(R_P."template/$tplpath/wind/$template.$EXT") && $tplpath = 'default';
	return R_P."template/$tplpath/wind/$template.$EXT";
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
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$array[$key] = addslashes($value);
			} else {
				Add_S($array[$key]);
			}
		}
	}
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

function Sql_cv($var){
	global $db;
	$db->update("INSERT INTO pw_bgsqlcv(var) VALUES ('$var')");
	$id = $db->insert_id();
	$rt = $db->get_one("SELECT var FROM pw_bgsqlcv WHERE id='$id'");
	$db->update("DELETE FROM pw_bgsqlcv WHERE id='$id'");
	return $rt['var'];
}

function openfile($filename){
	$filedb = explode('<:wind:>',str_replace("\n","\n<:wind:>",readover($filename)));
	$count = count($filedb)-1;
	if ($count > -1 && (!$filedb[$count] || $filedb[$count]=="\r")) {
		unset($filedb[$count]);
	}
	empty($filedb) && $filedb[0] = '';
	return $filedb;
}
?>