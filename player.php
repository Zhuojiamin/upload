<?php
require_once('global.php');
InitGP('mid',G);
$songdb = $db->get_one("SELECT name,murl,singer,descrip FROM pw_music WHERE mid='$mid'");
$name = substrs($songdb[name],30);
$singer = $songdb['singer'];
$murl = substrs($songdb[murl],30);
$descrip = substrs($songdb[descrip],200);
if (eregi("\.(rm|rmvb|ra|ram)$",$songdb['murl'])) {
	$type = 'rm';
} elseif (eregi("\.(qt|mov|4mv)$",$songdb['murl'])) {
	$type = 'qt';
} else {
	$type = 'wmv';
}
require_once(PrintEot('player'));


?>