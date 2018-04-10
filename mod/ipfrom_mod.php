<?php
!function_exists('readover') && exit('Forbidden');
function cvipfrom($ip){
	global $ipa0;
	$d_ip=explode(".",$ip);
	$f_n=file_exists(R_P."ipdata/$d_ip[0].txt") ? R_P."ipdata/$d_ip[0].txt" : R_P.'ipdata/0.txt';
	$ip=implode('.',d_ip($d_ip));
	$db=fopen($f_n,"rb");
	flock($db,LOCK_SH);
	$d=fread($db,filesize($f_n));
	$s_ip="\n$d_ip[0].$d_ip[1].$d_ip[2]";
	if($s=strpos($d,$s_ip)){
		!($f=s_ip($db,$s,$ip)) && list($l_d,$ff)=nset($db);
	}else{	
		$s_ip="\n$d_ip[0].$d_ip[1]";
		if($s=strpos($d,$s_ip)){
			!($f=s_ip($db,$s,$ip)) && list($l_d,$ff)=nset($db);
		}elseif($s=strpos($d,"\n$d_ip[0]") && $f_n==R_P.'ipdata/0.txt'){ 
			$s_ip="\n$d_ip[0]";
			!($f=s_ip($db,$s,$ip)) && list($l_d,$ff)=nset($db);
		}else{
			$f='Unknown';
		}
	}
	if(empty($f) && $s!==false){
		while(ereg("^$s_ip","\n".$l_d)!==false){
			if($ipa0==1 || $f=s_ip($db,$s,$ip,$l_d)) break;
			list($l_d,$cff)=nset($db);
			$cff && $ff=$cff;
		}
	}
	fclose($db);
	return $f ? $f : $ff;
}
function s_ip($db,$s,$ip,$l_d=''){
	global $ipa0;
	if(!$l_d){
		fseek($db,$s+1,SEEK_SET);
		$l_d=fgets($db,100);
	}
	$ip_a=explode("\t",$l_d);
	$ip_a[0]=implode('.',d_ip(explode('.',$ip_a[0])));
	$ip_a[1]=implode('.',d_ip(explode('.',$ip_a[1])));
	if($ip<$ip_a[0]) $ipa0=1;
	if ($ip>=$ip_a[0] && $ip<=$ip_a[1]) return $ip_a[2].$ip_a[3];
}
function nset($db){
	$l_d=fgets($db,100);
	$ip_a=explode("\t",$l_d);
	return array($l_d,$ip_a[2].$ip_a[3]);
}
function d_ip($d_ip){
	for($i=0; $i<=3; $i++){
		$d_ip[$i]     = sprintf("%03d", $d_ip[$i]);
	}
	return $d_ip;
}
?>