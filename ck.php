<?php
error_reporting(E_ERROR | E_PARSE);

$timestamp = time();
define('R_P',getdirname(__FILE__));
define('D_P',R_P);

require_once(D_P.'data/cache/config.php');
$db_cvtime != 0 && $timestamp += $db_cvtime*60;
if ($_GET['admin']) {
	$db_ckpath	 = '/';
	$db_ckdomain = '';
}
$B_url = $db_blogurl;
$islocalhost = ($_SERVER['HTTP_HOST']=='localhost' || preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}(\:[0-9]{2,})?$/',$_SERVER['HTTP_HOST'])) ? 1 : 0;
$nmsg = num_rand(4);
Cookie('cknum',StrCode($timestamp."\t\t".md5($nmsg.$timestamp)));

header('Pragma:no-cache');
header('Cache-control:no-cache');
if (function_exists('imagecreate') && function_exists('imagepng')) {
	header('Content-type: image/png');
	$x_size=60;
	$y_size=20;
	$imagecreate = function_exists('imagecreatetruecolor') ? 'imagecreatetruecolor' : 'imagecreate';
	$aimg = $imagecreate($x_size,$y_size);
	$back = imagecolorallocate($aimg,255,255,255);
	$border = imagecolorallocate($aimg,0,0,0);
	imagefilledrectangle($aimg,0,0,$x_size - 1,$y_size - 1,$back);
	imagerectangle($aimg,0,0,$x_size - 1,$y_size - 1,$border);
    for ($i=1; $i<=20;$i++) {
		$dot = imagecolorallocate($aimg,mt_rand(50,255),mt_rand(50,255),mt_rand(50,255));
		imagesetpixel($aimg,mt_rand(1,$x_size-1), mt_rand(1,$y_size-1),$dot);
    }
    $xing = '~!@#$%^&*.,/+-=<>';
	for ($i=1; $i<=10;$i++) {
		$num = mt_rand(0,strlen($xing)-1);
		imagestring($aimg,1,$i*$x_size/10+mt_rand(1,3),mt_rand(1,15),$xing[$num],imagecolorallocate($aimg,mt_rand(150,255),mt_rand(150,255),mt_rand(150,255)));
	}
    for ($i=0;$i<strlen($nmsg);$i++) {
		imagestring($aimg,mt_rand(3,5),$i*$x_size/4+mt_rand(1,5),mt_rand(1,6),$nmsg[$i],imagecolorallocate($aimg,mt_rand(50,255),mt_rand(0,120),mt_rand(50,255)));
    }
    imagepng($aimg);
    imagedestroy($aimg);
} else {
	header('Content-type: image/bmp');
	$Color[0] = chr(0).chr(0).chr(0);
	$Color[1] = chr(255).chr(255).chr(255);
	$_Num[0]  = "1110000111110111101111011110111101001011110100101111010010111101001011110111101111011110111110000111";
	$_Num[1]  = "1111011111110001111111110111111111011111111101111111110111111111011111111101111111110111111100000111";
	$_Num[2]  = "1110000111110111101111011110111111111011111111011111111011111111011111111011111111011110111100000011";
	$_Num[3]  = "1110000111110111101111011110111111110111111100111111111101111111111011110111101111011110111110000111";
	$_Num[4]  = "1111101111111110111111110011111110101111110110111111011011111100000011111110111111111011111111000011";
	$_Num[5]  = "1100000011110111111111011111111101000111110011101111111110111111111011110111101111011110111110000111";
	$_Num[6]  = "1111000111111011101111011111111101111111110100011111001110111101111011110111101111011110111110000111";
	$_Num[7]  = "1100000011110111011111011101111111101111111110111111110111111111011111111101111111110111111111011111";
	$_Num[8]  = "1110000111110111101111011110111101111011111000011111101101111101111011110111101111011110111110000111";
	$_Num[9]  = "1110001111110111011111011110111101111011110111001111100010111111111011111111101111011101111110001111";
	echo chr(66).chr(77).chr(230).chr(4).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(54).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(40).chr(0).chr(0).chr(0).chr(10).chr(0).chr(0).chr(0).chr(1).chr(0);
	echo chr(24).chr(0).chr(0).chr(0).chr(0).chr(0).chr(176).chr(4).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(18).chr(11).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0);
	for ($i=9;$i>=0;$i--){
		for ($j=0;$j<=3;$j++){
			for ($k=1;$k<=10;$k++) {
				if (mt_rand(0,7)<1) {
					echo $Color[mt_rand(0,1)];
				} else {
					echo $Color[substr($_Num[$nmsg[$j]], $i * 10 + $k, 1)];
				}
			}
		}
	}
}
exit;
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
function num_rand($lenth){
	mt_srand((double)microtime() * 1000000);
	$randval = '';
	for ($i=0;$i<$lenth;$i++) {
		$randval .= mt_rand(1,9);
	}
	return $randval;
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