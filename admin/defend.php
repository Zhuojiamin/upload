<?php
!function_exists('readover') && exit('Forbidden');

InitGP(array('action','job','set'),'G');
(strpos($action,'..') !== false || strpos($job,'..') !== false || strpos($set,'..') !== false) && exit('Forbidden');

include_once(D_P.'data/cache/config.php');
($db_forcecharset && !defined('DIR')) && @header("Content-Type: text/html; charset=$db_charset");

if ($db_htmifopen && $db_dir && $db_ext){
	$self_array = explode('-',$db_ext ? substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],$db_ext)) : $_SERVER['QUERY_STRING']);
	for ($i=0;$i<count($self_array);$i++) {
		$_key	= $self_array[$i];
		$_value	= $self_array[++$i];
		!preg_match('/^\_/',$_key) && strlen($GLOBALS[$_key]) < 1 && $GLOBALS[$_key] = addslashes(rawurldecode($_value));
	}
}

if ($db_htmifopen && $db_dir && $db_ext){
	$self_array = explode('-',$db_ext ? substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],$db_ext)) : $_SERVER['QUERY_STRING']);
	for ($i=0;$i<count($self_array);$i++) {
		$_key	= $self_array[$i];
		$_value	= $self_array[++$i];
		!preg_match('/^\_/',$_key) && strlen($GLOBALS[$_key]) < 1 && $GLOBALS[$_key] = addslashes(rawurldecode($_value));
	}
}

function InitGP($keys,$method='GP',$htmcv=1){
	!is_array($keys) && $keys = array($keys);
	foreach ($keys as $key) {
		$value = GetGP($key,$method,$htmcv);
		strlen($GLOBALS[$key]) < 1 && $GLOBALS[$key] = $value;
	}
	unset($keys);
	return true;
}
function GetGP($key,$method='GP',$htmcv=1){
	$value = '';
	if ($method!='P' && isset($_GET[$key])) {
		$value = $_GET[$key];
	} elseif ($method!='G' && isset($_POST[$key])) {
		$value = $_POST[$key];
	}
	//CheckVar($value);
	$htmcv && $value && $value = Char_cv($value);
	return $value;
}
function Char_cv($msg,$unhtml = 'Y'){
	if (is_array($msg)) {
		foreach ($msg as $key => $value) {
			$value = Char_cv($value,$unhtml);
			$msg[$key] = $value;
		}
	} else {
		if ($unhtml != 'N') {
			$msg = str_replace(
				array('&amp;','&nbsp;','"',"'",'<','>'),
				array('&',' ','&quot;','&#39;','&lt;','&gt;'),
				$msg
			);
		}
		$msg = str_replace(
			array("\t","\r",'  '),
			array('&nbsp; &nbsp; ','','&nbsp; '),
			$msg
		);
	}
	return $msg;
}
function CheckVar($var){
	if (!empty($var)) {
		if (is_array($var)) {
			foreach ($var as $key=>$value) {
				CheckVar($value);
			}
		} else {
			global $action,$basename;
			if ($action!='setadv' && @preg_match('/\<(iframe|meta|script)/is',$var)) {
				$basename = 'javascript:history.go(-1);';
				useradminmsg('word_error');
			}
		}
	}
}
function useradminmsg($msg){
	if (function_exists('usermsg') && !defined('AJAXUSER')) {
		usermsg($msg);
	} elseif (function_exists('adminmsg') && !defined('AJAXADMIN')) {
		adminmsg($msg);
	} else {
		exit($msg);
	}
}
?>