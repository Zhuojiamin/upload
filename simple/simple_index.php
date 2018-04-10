<?php
!function_exists('readover') && exit('Forbidden');

$classdb = array('blog','photo','music','bookmark','goods','file');
$catedb = array();
$catename = $end_li = '';
if (!$type) {
	foreach ($classdb as $value) {
		strpos("\t$db_showcate\t","\t$value\t")!==false && $catedb[] = array('value' => $value,'name' => $ilang['c'.$value]);
	}
} else {
	!in_array($type,$classdb) && Showmsg('undefined_action');
	$lastgrad = 0;
	$catename = $ilang['c'.$type];
	@include_once Pcv(D_P."data/cache/forum_cache_$type.php");
	$catedb = ${'_'.strtoupper($type)};
	foreach ($catedb as $key => $value) {
		if($value['_ifshow'] == '1'){
			$value['li_add'] = '';
			$gradnum = $value['type'] - $lastgrad;
			if ($gradnum>0) {
				for ($i=0;$i<$gradnum;$i++) {
					$value['li_add'] .= '<ul>';
				}
			} elseif ($gradnum<0) {
				for ($i=0;$i < -$gradnum;$i++) {
					$value['li_add'] .= '</ul>';
				}
			}
			$lastgrad = $value['type'];
			$showcatedb[$key] = $value;
		}
	}
	for ($i=$gradnum;$i>0;$i--) {
		$end_li .= '</ul>';
	}
}
require_once PrintEot('simple_index');
?>