<?php
!function_exists('GetGP') && exit('Forbidden');

header("HTTP/1.0 200 OK");
header("HTTP/1.1 200 OK");
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Expires: 0");
header("Pragma: no-cache");

if (!empty($_POST)) {
	$_POST = Char_cv($_POST);
	if ($db_charset != 'utf-8') {
		require_once(R_P.'mod/charset_mod.php');
		foreach ($_POST as $key => $value) {
			${'utf8_'.$key} = $value;
			${$key} = convert_charset('utf-8',$db_charset,$value);
		}
	} else {
		foreach ($_POST as $key => $value) {
			${'utf8_'.$key} = ${$key} = $value;
		}
	}
}
function Tag_cv($tag){
	$chars = "`~!@#$%^&*()_-+=|\\{}[]:\";',./<>?";
	$len = strlen($chars);
	for ($i=0; $i<$len; $i++) {
		$tag = str_replace($chars[$i],'',$tag);
	}
	return $tag;
}
function update_dirdb($uid){
	global $db;
	$cachedb = array();
	$query = $db->query("SELECT typeid,name,type,vieworder FROM pw_itemtype WHERE uid='$uid' ORDER BY vieworder");
	while ($rt = $db->fetch_array($query)) {
		$cachedb[$rt['type']][$rt['typeid']] = $rt;
	}
	if (!empty($cachedb)) {
		Strip_S($cachedb);
		$cachedb = addslashes(serialize($cachedb));
	}
	$db->update("UPDATE pw_userinfo SET dirdb='$cachedb' WHERE uid='$uid'");
}
function ShowResponseText($responseText){
	header("Content-Type: text/html; charset=utf-8");
	echo $responseText;exit;
}
?>