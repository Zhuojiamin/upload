<?php
!function_exists('Showmsg') && exit('Forbidden');

include_once(D_P.'data/cache/config.php');
($db_forcecharset && !defined('DIR')) && @header("Content-Type: text/html; charset=$db_charset");

if ($db_htmifopen && $db_dir && $db_ext) {
	$self_array = explode('-',$db_ext ? substr($_SERVER['QUERY_STRING'],0,strpos($_SERVER['QUERY_STRING'],$db_ext)) : $_SERVER['QUERY_STRING']);
	for ($i=0;$i<count($self_array);$i++) {
		$_key	= $self_array[$i];
		$_value	= $self_array[++$i];
		!preg_match('/^\_/',$_key) && strlen($GLOBALS[$_key]) < 1 && $GLOBALS[$_key] = addslashes(rawurldecode($_value));
	}
}

InitGP(array('action','type','uid','do','itemid','page','maid','pid'),'G');
(strpos($action,'..') !== false) && exit('Forbidden');

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
	if (strlen($GLOBALS[$key]) < 1) {
		if ($method!='P' && isset($_GET[$key])) {
			$value = $_GET[$key];
		} elseif ($method!='G' && isset($_POST[$key])) {
			$value = $_POST[$key];
		}
	} else {
		$value = $GLOBALS[$key];
	}
	if ($value && (int)$htmcv > 0) {
		if ($htmcv == 1) {
			$value = Char_cv($value);
		} elseif ($htmcv == 2) {
			$value = Char_int($value);
		}
	}
	return $value;
}
function Char_int($array){
	if (is_array($array)) {
		$array = array_map('Char_int',$array);
	} else {
		$array = (int)$array;
	}
	return $array;
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
?>