<?php
!function_exists('adminmsg') && exit('Forbidden');

$headdb = array();
$guides = 'var guides={';
$titles = 'var titles={';
$dfcate = $gd_diy = '';
$db_diy = $db_diy ? explode(',',$db_diy) : array('setting_set','setting_core','setgroup_level','setmodule','setword');

if (If_manager) {
	$headdb['manager'] = $lefthead['manager'];
	$output1 = '';
	foreach ($leftmanager['option'] as $farray) {
		foreach ($farray as $key => $value) {
			$output1 .= "'$key' : ['$value[0]','$value[1]'],";
		}
	}
	$output1 = substr($output1,0,-1);
	$guides .= "\r\n\t'manager' : {'manager' : {{$output1}}},";
	$titles .= "'manager' : '$leftmanager[name]',";
}
foreach ($leftlang as $cate => $left) {
	$output1 = '';
	foreach ($left as $title => $farray) {
		$output2 = '';
		foreach ($farray['option'] as $key => $value) {
			if (isset($value[0])) {
				if ($rightset[$key]) {
					if (in_array($key,$db_diy)) {
						$dfcate = 'common';
						$gd_diy .= "'$key' : ['$value[0]','$value[1]'],";
					}
					$output2 .= "'$key' : ['$value[0]','$value[1]'],";
				}
			} else {
				foreach ($value as $k => $v) {
					$c_key = $key.'_'.$k;
					if ($rightset[$c_key]) {
						if (in_array($c_key,$db_diy)) {
							$dfcate = 'common';
							$gd_diy .= "'$c_key' : ['$v[0]','$v[1]'],";
						}
						$output2 .= "'$c_key' : ['$v[0]','$v[1]'],";
					}
				}
			}
		}
		if (!$output2) continue;
		$output2 = substr($output2,0,-1);
		$output1.= "'$title' : {{$output2}},";
		$titles .= "'$title' : '$farray[name]',";
	}
	if (!$output1) continue;
	$output1 = substr($output1,0,-1);
	$guides .= "\r\n\t'$cate' : {{$output1}},";
	$lefthead[$cate] && $headdb[$cate] = $lefthead[$cate];
	!$dfcate && $dfcate = $cate;
}
if ($dfcate == 'common') {
	$gd_diy = substr($gd_diy,0,-1);
	$guides .= "\r\n\t'common' : {'diy' : {{$gd_diy}}},";
	$titles .= "'diy' : '$cplang[diy]',";
}
$guides = substr($guides,0,-1)."\n}";
$titles = substr($titles,0,-1)."}";
include PrintEot('index');footer('N');
?>