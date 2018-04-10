<?php
/**
 * Copyright (c) 2003-07  PHPWind.net. All rights reserved.
 * 
 * @filename: charset_mod.php
 * @author: Noizy (noizyfeng@gmail.com), QQ:7703883
 * @modify: Fri Mar 23 18:02:15 CST 2007
 */
!function_exists('readover') && exit('Forbidden');

function convert_charset($incharset,$outcharset,$string){
	$incharset  = strtoupper($incharset);
	$outcharset = strtoupper($outcharset);
	if ($incharset == $outcharset) {
		return $string;
	} else {
		global $db_charsetmod;
		!$db_charsetmod && $db_charsetmod = 'N_charset_string';
		if ($db_charsetmod != 'N_charset_string') {
			return $db_charsetmod($incharset,$outcharset,$string);
		} else {
			return N_charset_string($incharset,$outcharset,$string);
		}
    }
}
function N_iconv($incharset,$outcharset,$string){
	if (!function_exists('iconv')) {
		return N_charset_string($incharset,$outcharset,$string);
	}
	if ((@stristr(PHP_OS,'AIX')) && (@strcasecmp(ICONV_IMPL,'unknown')==0) && (@strcasecmp(ICONV_VERSION,'unknown')==0)) {
		$iconvarray = array('BIG5' => 'IBM-eucTW');
		$iconvarray[$incharset]  && $incharset  = $iconvarray[$incharset];
		$iconvarray[$outcharset] && $outcharset = $iconvarray[$outcharset];
	}
	$outcharset .= '//IGNORE';
	return iconv($incharset,$outcharset,$string);
}
function N_recode_string($incharset,$outcharset,$string){
	if (!function_exists('recode_string')) {
		return N_charset_string($incharset,$outcharset,$string);
	} else {
		return recode_string($incharset.'..'.$outcharset,$string);
	}
}
function N_libiconv($incharset,$outcharset,$string){
	if (!function_exists('libiconv')) {
		return N_charset_string($incharset,$outcharset,$string);
	} else {
		return libiconv($incharset,$outcharset,$string);
	}
}
function N_mb_convert_encoding($incharset,$outcharset,$string){
	if (!function_exists('mb_convert_encoding')) {
		return N_charset_string($incharset,$outcharset,$string);
	} else {
		return mb_convert_encoding($string,$outcharset,$incharset);
	}
}
function N_charset_string($incharset,$outcharset,$string){
	(preg_match('/^GB([K0-9]{1,5}$)/',$incharset))  && $incharset  = 'GB';
	(preg_match('/^GB([K0-9]{1,5}$)/',$outcharset)) && $outcharset = 'GB';
	$num1 = $num2 = 0;
	if ($incharset == 'UTF-8') {
		$num1 = 7; $num2 = 6;
		$incharset = 'UNICODE';
	} elseif ($incharset == 'UNICODE') {
		$num1 = 9; $num2 = 4;
	}
	if ($outcharset == 'UTF-8') {
		$num1 = 7; $num2 = 6;
		$outcharset = 'UNICODE';
	} elseif ($outcharset == 'UNICODE') {
		$num1 = 9; $num2 = 4;
	}
	list($farray,$func) = CharSetFunc($incharset,$outcharset);
	if (!in_array($incharset,array('GB','BIG5','UNICODE')) || !in_array($outcharset,array('GB','BIG5','UNICODE')) || !$farray) {
		return $string;
	}
	if (empty($farray)) return $string;
	if ($func == 'gbig5_utf8') {
		$outarray = array();
		foreach ($farray as $value) {
			if ($incharset == 'UNICODE') {
				$outarray[hexdec(substr($value,$num1,$num2))] = substr($value,0,6);
			} elseif ($outcharset == 'UNICODE') {
				$outarray[hexdec(substr($value,0,6))] = substr($value,$num1,$num2);
			}
		}
		if (empty($outarray)) return $string;
		return $func($string,$outarray,$incharset,$outcharset);
	} else {
		return $func($string,$farray);
	}
}
function CharSetFunc($incharset,$outcharset){
	if ($incharset == 'GB' && $outcharset == 'BIG5') {
		$file = 'gb-big5.table';
		$func = 'gb_big5';
	} elseif ($incharset == 'BIG5' && $outcharset == 'GB') {
		$file = 'big5-gb.table';
		$func = 'gb_big5';
	} elseif (($incharset == 'GB' && $outcharset == 'UNICODE') || ($incharset == 'UNICODE' && $outcharset == 'GB')) {
		$file = 'gb-unicode.table';
		$func = 'gbig5_utf8';
	} elseif (($incharset == 'BIG5' && $outcharset == 'UNICODE') || ($incharset == 'UNICODE' && $outcharset == 'BIG5')) {
		$file = 'big5-unicode.table';
		$func = 'gbig5_utf8';
	}
	$fp = '';
	if ($func == 'gbig5_utf8') {
		$fp = @file(R_P."mod/encode/$file");
	} elseif ($func == 'gb_big5') {
		$fp = @fopen(R_P."mod/encode/$file","rb");
	}
	return array($fp,$func);
}
function gb_big5($str,$fp){
	for ($i=0; $i<(strlen($str)-1); $i++) {
		$h = ord($str[$i]);
		if ($h>=160) {
			$l = ord($str[$i+1]);
			if ($h==161 && $l==64) {
				$gb = '  ';
			} else {
				fseek($fp,($h-160)*510+($l-1)*2);
				$gb = fread($fp,2);
			}
			$str[$i]   = $gb[0];
			$str[$i+1] = $gb[1];
			$i++;
		}
	}
	fclose($fp);
	return $str;
}
function gbig5_utf8($str,$fp,$incharset,$outcharset){
	if ($incharset=='GB' || $incharset == 'BIG5') {
		$return = '';
		while ($str != '') {
			if (ord(substr($str,0,1))>127) {
				if ($incharset == 'GB') {
					$utf8 = unicode_utf8(hexdec($fp[hexdec(bin2hex(substr($str,0,2)))-0x8080]));
				} elseif ($incharset == 'BIG5') {
					$utf8 = unicode_utf8(hexdec($fp[hexdec(bin2hex(substr($str,0,2)))]));
				}
				for ($i=0; $i<strlen($utf8); $i+=3) {
					$return .= chr(substr($utf8,$i,3));
				}
				$str = substr($str,2,strlen($str));
			} else {
				$return .= substr($str,0,1);
				$str	 = substr($str,1,strlen($str));
			}
		}
		unset($fp,$str);
		return $return;
	} elseif ($incharset == 'UNICODE') {
		$return = ''; $i = 0;
		while ($i < strlen($str)) {
			$c = ord(substr($str,$i++,1));
			if (($c >> 4) < 8 && ($c >> 4) >= 0) {
				$return .= substr($str,$i-1,1);
			} elseif (($c >> 4) < 14 && ($c >> 4) > 11) {
				$char2 = ord(substr($str,$i++,1));
				$char3 = $fp[(($c & 0x1F) << 6) | ($char2 & 0x3F)];
				if ($outcharset=='GB') {
					$return .= hex2bin(dechex($char3 + 0x8080));
				} elseif ($outcharset=='BIG5') {
					$return .= hex2bin($char3);
				}
			} elseif (($c >> 4) == '14') {
				$char2 = ord(substr($str,$i++,1));
				$char3 = ord(substr($str,$i++,1));
				$char4 = $fp[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];
				if ($outcharset=='GB') {
					$return .= hex2bin(dechex($char4 + 0x8080));
				} elseif ($outcharset=='BIG5') {
					$return .= hex2bin($char4);
				}
			}
		}
		return $return;
	} else {
		return false;
	}
}
function unicode_utf8($str){
	$return = '';
	if ($str < 0x80) {
		$return .= $str;
	} elseif ($str < 0x800) {
		$return .= (0xC0 | $str >> 6);
		$return .= (0x80 | $str & 0x3F);
	} elseif ($str < 0x10000) {
		$return .= (0xE0 | $str >> 12);
		$return .= (0x80 | $str >> 6 & 0x3F);
		$return .= (0x80 | $str & 0x3F);
	} elseif ($str < 0x200000) {
		$return .= (0xF0 | $str >> 18);
		$return .= (0x80 | $str >> 12 & 0x3F);
		$return .= (0x80 | $str >> 6 & 0x3F);
		$return .= (0x80 | $str & 0x3F);
	}
	return $return;
}
function hex2bin($hexdata){
	$bindata = '';
	for ($i=0; $i<strlen($hexdata); $i+=2){
		$bindata .= chr(hexdec(substr($hexdata,$i,2)));
	}
	return $bindata;
}
?>