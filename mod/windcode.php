<?php
!function_exists('readover') && exit('Forbidden');

function convert($message,$allow = array(),$prtimes = '-1'){
	global $code_num,$code_htm,$phpcode_htm,$attachpath,$picpath,$userstyle,$userimgpath,$imgpath,$stylepath,$codelang,$showimgpath,$showstylepath;
	$allow['times'] && $prtimes = $allow['times'];
	$message = preg_replace('/\[code\](.+?)\[\/code\]/eis',"phpcode('\\1')",$message,$prtimes);
	$message = preg_replace('/\[list=([aA1]?)\](.+?)\[\/list\]/is',"<ol type=\"\\1\" style=\"margin:0 0 0 25px\">\\2</ol>", $message);
	$message = str_replace(
		array('[u]','[/u]','[b]','[/b]','[i]','[/i]','[list]','[li]','[/li]','[/list]','[sub]','[/sub]','[sup]','[/sup]','[strike]','[/strike]','[blockquote]','[/blockquote]','[hr]','[p]','[/p]','p_w_upload','p_w_picpath'),
		array('<u>','</u>','<b>','</b>','<i>','</i>','<ul style="margin:0 0 0 15px">','<li>','</li>', '</ul>','<sub>','</sub>','<sup>','</sup>','<strike>','</strike>','<blockquote>','</blockquote>','<hr />','<p>','</p>',$attachpath,$picpath),
		$message
	);
	$message = preg_replace(
		array(
			'/\[font=([^\[]+?)\](.+?)\[\/font\]/is',
			'/\[color=([#0-9a-z]{1,10})\](.+?)\[\/color\]/is',
			'/\[backcolor=([#0-9a-z]{1,10})\](.+?)\[\/backcolor\]/is',
			'/\[email=([^\[]*)\]([^\[]*)\[\/email\]/is',
			'/\[email\]([^\[]*)\[\/email\]/is',
			'/\[size=(\d+)\](.+?)\[\/size\]/eis',
			'/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is',
			'/\[glow=(\d+)\,([0-9a-zA-Z]+?)\,(\d+)\](.+?)\[\/glow\]/is'
		),
		array(
			"<font face=\"\\1\">\\2</font>",
			"<font color=\"\\1\">\\2</font>",
			"<font style=\"background-color:\\1\">\\2</font>",
			"<a href=\"mailto:\\1\">\\2</a>",
			"<a href=\"mailto:\\1\">\\1</a>",
			"size('\\1','\\2','$allow[size]')",
			"<div align=\"\\1\">\\2</div>",
			"<div style=\"width:\\1px;filter:glow(color=\\2,strength=\\3);\">\\4</div>"
		),
		$message
	);
	$code_num = 0;
	$code_htm = array();
	@include R_P.'template/default/wind/lang_windcode.php';
	if ($userstyle) {
		$showimgpath = 'theme';
		$showstylepath = "$userstyle/$userimgpath";
	} else {
		$showimgpath = $imgpath;
		$showstylepath = $stylepath;
	}
	$message  = $allow['ifpic'] ? preg_replace('/\[img\](.+?)\[\/img\]/eis',"cvpic('\\1','$allow[picwidth]','$allow[picheight]')",$message,$prtimes) : preg_replace('/\[img\](.+?)\[\/img\]/eis',"nopic('\\1')",$message,$prtimes);
	$message  = preg_replace(
		array(
			'/\[url=(https?|ftp|gopher|news|telnet|mms|rtsp)([^\[\s]+?)\](.+?)\[\/url\]/eis',
			'/\[url\]www\.([^\[]+?)\[\/url\]/eis',
			'/\[url\](https?|ftp|gopher|news|telnet|mms|rtsp)([^\[]+?)\[\/url\]/eis'
		),
		array(
			"cvurl('\\1','\\2','\\3')",
			"cvurl('\\1')",
			"cvurl('\\1','\\2')"
		),
		$message
	);
	$message = preg_replace(
		array(
			'/\[fly\]([^\[]*)\[\/fly\]/is',
			'/\[move\]([^\[]*)\[\/move\]/is'
		),
		array(
			"<marquee width=\"90%\" behavior=\"alternate\" scrollamount=\"3\">\\1</marquee>",
			"<marquee scrollamount=\"3\">\\1</marquee>"
		),
		$message
	);
	if (strpos($message,'[table') !== false && strpos($message,'[/table]') !== false) {
		for ($t = 0;$t < 5;$t++) {
			$message = preg_replace('/\[table(=(\d{1,3}(%|px)?))?\](.*?)\[\/table\]/eis', "table('\\2','\\3','\\4')",$message);
		}
	}
	$message = preg_replace(
		array(
			'/\[post\](.+?)\[\/post\]/is',
			'/\[hide=(.+?)\](.+?)\[\/hide\]/is',
			'/\[sell=(.+?)\](.+?)\[\/sell\]/is'
		),
		"\\1",
		$message
	);
	$message = preg_replace('/\[quote\](.+?)\[\/quote\]/eis',"qoute('\\1')",$message);
	krsort($code_htm);
	foreach ($code_htm as $codehtm) {
		foreach ($codehtm as $key => $value) {
			$message = str_replace("<\twind_code_$key\t>",$value,$message);
		}
	}
	$message = $allow['ifflash'] ? preg_replace('/\[flash=(\d+?)\,(\d+?)\](.+?)\[\/flash\]/eis',"flaplayer('\\3','\\1','\\2')",$message,$prtimes) : preg_replace('/\[flash=(\d+?)\,(\d+?)\](.+?)\[\/flash\]/eis',"flaplayer('\\3','','','1')",$message,$prtimes);
	if ($allow['ifmpeg']) {
		$message = preg_replace(
			array(
				'/\[wmv=(0|1)\](.+?)\[\/wmv\]/eis',
				'/\[wmv=([0-9]{1,3})\,([0-9]{1,3})\,(0|1)\](.+?)\[\/wmv\]/eis',
				'/\[rm\](.+?)\[\/rm\]/eis',
				'/\[rm=([0-9]{1,3})\,([0-9]{1,3})\,(0|1)\](.+?)\[\/rm\]/eis'
			),
			array(
				"wmvplayer('\\2','314','53','\\1')",
				"wmvplayer('\\4','\\1','\\2','\\3')",
				"rmplayer('\\1')",
				"rmplayer('\\4','\\1','\\2','\\3')"
			),
			$message,
			$prtimes
		);
	} else {
		$message = preg_replace(
			array(
				'/\[wmv=[01]{1}\](.+?)\[\/wmv\]/is',
				'/\[wmv=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1}\](.+?)\[\/wmv\]/is',
				'/\[rm\](.+?)\[\/rm\]/is',
				'/\[rm=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1}\](.+?)\[\/rm\]/is'
			),
			"<img src=\"$showimgpath/$showstylepath/music.gif\" align=\"absbottom\"> <a href=\"\\1\" target=\"_blank\">\\1</a>",
			$message,
			$prtimes
		);
	}
	$message = $allow['ififrame'] ? preg_replace('/\[iframe\](.+?)\[\/iframe\]/eis',"<iframe src=\"\\1\" frameborder=\"0\" allowtransparency=\"true\" scrolling=\"yes\" width=\"97%\" height=\"340\"></iframe>",$message,$prtimes) : preg_replace('/\[iframe\](.+?)\[\/iframe\]/is',"Iframe Close: <a href=\"\\1\" target=\"_blank\">\\1</a>",$message,$prtimes);
	if (is_array($phpcode_htm)) {
		foreach ($phpcode_htm as $key => $value) {
			$message = str_replace("<\twind_phpcode_$key\t>",$value,$message);
		}
	}
	return $message;
}
function qoute($code){
	global $code_num,$code_htm;
	$code_num++;
	$code_htm[2][$code_num] = '<h6 class="quote">Quote:</h6><blockquote>'.stripslashes($code).'</blockquote>';
	return "<\twind_code_$code_num\t>";
}
function table($width,$unit,$text){
	global $tdcolor;
	!$tdcolor && $tdcolor = '#D4EFF7';
	if ($width) {
		$unit!='%' && $unit = 'px';
		if ($unit != '%') {
			$unit = 'px';
			$width > 600 && $width = 600;
		} else {
			$width > 98 && $width = 98;
		}
		$width .= $unit;
	} else {
		$width = '98%';
	}
	$table = "<table style=\"border: 1px solid $tdcolor;width: $width\">";
	$text = preg_replace('/\[td=(\d{1,2}),(\d{1,2})(,(\d{1,3}%?))?\]/is','<td colspan="\\1" rowspan="\\2" width="\\4">',$text);
	$text = preg_replace('/\[tr\]/is','<tr class="tr3">',$text);
	$text = preg_replace('/\[td\]/is','<td>',$text);
	$text = preg_replace('/\[\/(tr|td)\]/is','</\\1>',$text);
	$table .= $text;
	$table .= '</table>';
	return stripslashes($table);
}
function rmplayer($rmurl,$width='316',$height='241',$auto='1'){
	global $codelang;
	@include R_P.'template/default/wind/lang_windcode.php';
	return "<object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"$width\" height=\"$height\" id=\"PlayerR\"><param name=\"src\" value=\"$rmurl\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"$auto\" /><embed src=\"$rmurl\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"$auto\" console=\"clip1\" controls=\"Imagewindow\" width=\"$width\" height=\"$height\"></embed></object><br /><object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"$width\" height=\"44\"><param name=\"src\" value=\"$rmurl\" /><param name=\"controls\" value=\"ControlPanel\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"$rmurl\" /><embed src=\"$rmurl\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"$auto\" console=\"clip1\" controls=\"ControlPanel\" width=\"$width\" height=\"44\"></embed></object><script language=\"javascript\">function FullScreenR(){document.PlayerR.SetFullScreen();}</script><input type=\"button\" onclick=\"javascript:FullScreenR()\" value=\"$codelang[full_screen]\"> ";
}
function wmvplayer($wmvurl,$width='314',$height='256',$auto='1'){
	global $codelang;
	@include R_P.'template/default/wind/lang_windcode.php';
	return "<object width=\"$width\" height=\"$height\" classid=\"CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\" id=\"PlayerW\"><param name=\"url\" value=\"$wmvurl\" /><embed width=\"$width\" height=\"$height\" type=\"application/x-mplayer2\" src=\"$wmvurl\"></embed></object><script language=\"javascript\">function FullScreenW(){document.PlayerW.DisplaySize = 3;}</script><input type=\"button\" onclick=\"javascript:FullScreenW()\" value=\"$codelang[full_screen]\"> ";
}
function flaplayer($flaurl,$width='420',$height='320',$nofla = null){
	global $showimgpath,$showstylepath,$codelang;
	@include R_P.'template/default/wind/lang_windcode.php';
	if (!empty($nofla)) {
		return "<img src=\"$showimgpath/$showstylepath/music.gif\" align=\"absbottom\"> <a href=\"$flaurl\" target=\"_blank\">flash: $flaurl</a>";
	} else {
		return "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"$width\" height=\"$height\"><param name=\"movie\" value=\"$flaurl\" /><param name=\"quality\" value=\"high\" /><embed src=\"$flaurl\" quality=\"high\" type=\"application/x-shockwave-flash\" width=\"$width\" height=\"$height\"></embed></object>[<a href=\"$flaurl\" target=\"_blank\">$codelang[full_screen]</a>] ";
	}
}
function cvurl($http,$url = null,$name = null){
	global $code_num,$code_htm;
	$code_num++;
	if (empty($url)) {
		$url = $name = "www.$http";
		$http = 'http://';
	} elseif (empty($name)) {
		$name = $http.$url;
	}
	$name = stripslashes($name);
	$url = "<a href=\"$http$url\" target=\"_blank\">$name</a>";
	$code_htm[1][$code_num] = $url;
	return "<\twind_code_$code_num\t>";
}
function nopic($url){
	global $showimgpath,$showstylepath;
	$code_num++;
	$code_htm[0][$code_num] = "<img src=\"$showimgpath/$showstylepath/img.gif\" align=\"absbottom\" border=\"0\" /> <a href=\"$url\" target=\"_blank\">img: $url</a>";
	return "<\twind_code_$code_num\t>";
}
function cvpic($url,$picwidth = null,$picheight = null,$type = null,$descrip = null){
	global $code_num,$db_blogurl,$code_htm;
	$code_num++;
	(substr(strtolower($url),0,4)!='http') && $url = "$db_blogurl/$url";
	(strpos(strtolower($url),'login')!==false && (strpos(strtolower($url),'action=quit')!==false || strpos(strtolower($url),'action-quit')!==false)) && $url = str_replace('login','log in',$url);
	$descrip && $descrip = " alt=\"$descrip\"";
	if ($picwidth || $picheight) {
		$onload = ' onload="';
		$picwidth  && $onload .= "if(this.width>'$picwidth')this.width='$picwidth';";
		$picheight && $onload .= "if(this.height>'$picheight')this.height='$picheight';";
		$onload .= '"';
	} else {
		$onload = '';
	}
	$onclick = " onclick=\"window.open('".$url."');\"";
	$code = "<img src=\"$url\"{$onclick} {$onload}{$descrip} border=\"0\" />";
	$code_htm[0][$code_num] = $code;
	if ($type) {
		return $code;
	} else {
		return "<\twind_code_$code_num\t>";
	}
}
function size($size,$code,$allowsize){
	$allowsize && $size > $allowsize && $size = $allowsize;
	return "<font size=\"$size\">".str_replace('\\"','"',$code)."</font>";
}
function phpcode($code){
	global $phpcode_htm,$codeid;
	$code = str_replace(array("[attachment=",'\\"'),array("&#91;attachment=",'"'),$code);
	$codeid ++;
	$phpcode_htm[$codeid]="<h6 class=\"quote\"><a href=\"javascript:\"  onclick=\"CopyCode(document.getElementById('code$codeid'));\">Copy code</a></h6><blockquote id=\"code$codeid\">".preg_replace("/^(\<br \/\>)?(.*)/is","\\2",$code)."</blockquote>";
	return "<\twind_phpcode_$codeid\t>";
}
function attachment($message,$attachdb,$prtimes='-1'){
	$message = preg_replace('/\[attachment=([0-9]+)\]/eis',"upload('\\1')",$message,$prtimes);
	return $message;
}
function upload($aid){
	global $aids,$attachmentdb;
	if ($attachmentdb[$aid]) {
		$aids[] = $aid;
		return $attachmentdb[$aid];
	} else {
		return "[upload=$aid]";
	}
}
function copyctrl(){
	version_compare(PHP_VERSION, '4.2.0', '<') && mt_srand((double)microtime() * 1000000);
	$randval = '';
	for ($i=0;$i<10;$i++) {
		$randval .= chr(mt_rand(0,126));
	}
	$randval = str_replace('<','&lt;',$randval);
	return "<span style=\"display:none\"> $randval </span>&nbsp;<br />";
}

function postcache($key){
		global $smile,$imgpath;
		!$smile[$key] && $smile[$key]=current($smile);
		return "<img src=\"$imgpath/smile/{$smile[$key]}\" />";
}

function showsmile($message){
	global $smile,$smiles,$db_post;
	include_once(D_P.'data/cache/smile_cache.php');
	$message = preg_replace("/\[s:(.+?)\]/eis","postcache('\\1')",$message,$db_post['times']);
	return $message;
}

?>