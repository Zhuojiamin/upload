<?php
/**
 * Copyright (c) 2003-07  PHPWind.net. All rights reserved.
 * 
 * @filename: makethumb.php
 * @author: Noizy (noizyfeng@gmail.com)
 * @modify: Fri Feb 02 20:33:44 CST 2007
 */
!function_exists('readover') && exit('Forbidden');

function MakeThumb($srcFile,$dstFile,$dstW,$dstH,$color){
	$minitemp = GetImgInfo($srcFile,$dstW,$dstH);
	list($imagecreate,$imagecopyre) = GetImagecreate();
	if ($dstFile === $srcFile || empty($minitemp) || !$dstW || !$dstH || !$imagecreate || !$imagecopyre) return false;
	$imgwidth  = $minitemp['srcW'];
	$imgheight = $minitemp['srcH'];
	$srcX = $srcY = 0;
	if ($imgwidth < $imgheight) {
		$srcY = round(($imgheight - $imgwidth)/2);
		$imgheight = $imgwidth;
	} else {
		$srcX = round(($imgwidth - $imgheight)/2);
		$imgwidth = $imgheight;
	}
	$dstX = $dstY = 0;
	if (!$color) {
		$thumb = $imagecreate($minitemp['dstW'],$minitemp['dstH']);
	} else {
		$thumb = $imagecreate($dstW,$dstH);
		$color = trim($color,"#");
		sscanf($color,"%2x%2x%2x",$red,$green,$blue);
		$color = imagecolorallocate($thumb,$red,$green,$blue);
		imagefilledrectangle($thumb,0,0,$dstW,$dstH,$color);
		imagealphablending($thumb,true);
		list($dstX,$dstY) = GetPos($minitemp['dstW'],$minitemp['dstH'],$dstW,$dstH);
	}
	$imagecopyre($thumb,$minitemp['source'],$dstX,$dstY,$srcX,$srcY,$minitemp['dstW'],$minitemp['dstH'],$imgwidth,$imgheight);
	$minitemp['makeimage']($thumb,$dstFile);
	imagedestroy($thumb);
	return true;
}
function GetImgInfo($srcFile,$dstW,$dstH){
	$iext = strtolower(substr(strrchr($srcFile,'.'),1));
	$data = $imgdata = array();
	$imgdata = GetImgSize($srcFile);
	$type = $imagecreatefromtype = $imagetype = '';
	if (!empty($imgdata)) {
		$type = CheckImagetype($imgdata['type']);
		$imagecreatefromtype = function_exists('imagecreatefrom'.$type) ? 'imagecreatefrom'.$type : '';
		$imagetype = function_exists('image'.$type) ? 'image'.$type : '';
	}
	if (empty($imgdata) || !$type || !$imagecreatefromtype || !$imagetype) return false;
	$imgdata['source']	  = $imagecreatefromtype($srcFile);
	$imgdata['makeimage'] = $imagetype;
	if (!$imgdata['srcW'] || !$imgdata['srcH']) {
		$imgdata['srcW'] = imagesx($imgdata['source']);
		$imgdata['srcH'] = imagesy($imgdata['source']);
	}
	if ($imgdata['srcW']<=$dstW && $imgdata['srcH']<=$dstH) return false;
	if (($imgdata['srcW']/$dstW) < ($imgdata['srcH']/$dstH)) {
		$imgdata['dstW'] = $imgdata['dstH'] = $dstH;
	} else {
		$imgdata['dstW'] = $imgdata['dstH'] = $dstW;
	}
	return $imgdata;
}
function GetPos($newdstW,$newdstH,$dstW,$dstH){
	$scx = $scy = 0;
	if ($newdstW!=$dstW && $newdstH!=$dstH){
		$scx = $dstW-$newdstW;
		$scy = $dstH-$newdstH;
	} else {
		$newdstW == $dstW ? $scy = $dstH-$newdstH : $scx = $dstW-$newdstW;
	}
	$scx = round($scx/2);
	$scy = round($scy/2);
	return array($scx,$scy);
}
function CheckImagetype($imagetype){
	if ($imagetype==1) {
		$type = 'gif';
	} elseif ($imagetype==2) {
		$type = 'jpeg';
	} elseif ($imagetype==3) {
		$type = 'png';
	} else {
		return false;
	}
	return $type;
}
function GetImagecreate(){
	if (function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled')) {
		return array('imagecreatetruecolor','imagecopyresampled');
	} elseif (function_exists('imagecreate') && function_exists('imagecopyresized')) {
		return array('imagecreate','imagecopyresized');
	} else {
		return array();
	}
}
?>