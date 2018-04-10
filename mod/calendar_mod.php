<?php
/**
 * Copyright (c) 2003-07  PHPWind.net. All rights reserved.
 * 
 * @filename: calendar_mod.php
 * @author: Noizy (noizyfeng@gmail.com), QQ:7703883
 * @modify: Mon Mar 12 11:25:44 CST 2007
 */
!function_exists('readover') && exit('Forbidden');

function calendar($m,$y){
	global $db,$H,$uid,$timestamp,$type,$B_url;
	$today		= get_date($timestamp,'j');
	$weekday	= get_date(mktime(0,0,0,$m,1,$y),'w');
	$totalday	= Days4month($y,$m);
	$start		= strtotime($y.'-'.$m.'-1');
	$end		= strtotime($y.'-'.$m.'-'.$totalday);
	$postdates	= '';
	if (in_array($type,array('blog','goods','photo','music','file','bookmark','tag','team','user'))) {
		$query = $db->query("SELECT postdate FROM pw_items WHERE uid='$uid' AND type='$type' AND postdate>'$start' AND postdate<'$end'");
		while ($rt=$db->fetch_array($query)) {
			$postdates .= ($postdates ? ',' : '').get_date($rt['postdate'],'Y-n-j');
		}
	}
	$br = 0;
	$days = '<tr>';
	for ($i=1; $i<=$weekday; $i++) {
		$days .= '<td>&nbsp;</td>';
		$br++;
	}
	for ($i=1; $i<=$totalday; $i++) {
		$br++;
		$td = (strpos(",$postdates,",','.$y.'-'.$m.'-'.$i.",") !== false) ? '<a href="'.$B_url.'/'.'blog.php?do=list&uid='.$uid.'&type='.$type.'&y='.$y.'&m='.$m.'&date='.$y.'_'.$m.'_'.$i.'"><b>'.$i.'</b></a>' : $i;
		$days .= '<td>'.$td.'</td>';
		if ($br>=7) {
			$days .= '</tr><tr>';
			$br = 0;
		}
	}
	if ($br!=0) {
		for ($i=$br; $i<7;$i++) {
			$days .= '<td>&nbsp;</td>';
		}
	}
	return $days;
}
function Days4month($year,$month){
	if (!function_exists('cal_days_in_month')) {
		return date('t',mktime(0,0,0,$month+1,0,$year));
	} else {
		return cal_days_in_month(CAL_GREGORIAN,$month,$year);
	}
}
?>