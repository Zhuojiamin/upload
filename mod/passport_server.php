<?php
!function_exists('readover') && exit('Forbidden');

(!$db_pptifopen && $db_ppttype != 'server') && exit("LxBlog: Passport closed");

$jumpurl = $userdb_encode = '';

!$forward && $forward = $db_blogurl;
$clienturl  = explode(',',$db_ppturls);
$jumpurl	= array_shift($clienturl);

!$jumpurl && Showmsg('undefined_action');

$userdb = array();
foreach ($clienturl as $value) {
	($value && $value != $jumpurl) && $userdb['url'] .= ($userdb['url'] ? ',' : '').$value;
}

$userdb = $db->get_one("SELECT uid,username,password,email,rvrc,money,credit FROM pw_user WHERE uid='$winduid'");
$userdb['time']		= $timestamp;
$userdb['cktime']	= $cktime;

foreach ($userdb as $key => $value) {
	$userdb_encode .= ($userdb_encode ? '&' : '')."$key=$value";
}

$db_hash = $db_pptkey;
$userdb_encode = str_replace('=','',StrCode($userdb_encode));
if ($action=='login') {
	$verify = md5("login$userdb_encode$forward$db_pptkey");
	ObHeader("$jumpurl/passport_client.php?action=login&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
} elseif ($action=='quit') {
	$verify = md5("quit$userdb_encode$forward$db_pptkey");
	ObHeader("$jumpurl/passport_client.php?action=quit&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
} else {
	Showmsg('undefined_action');
}
?>