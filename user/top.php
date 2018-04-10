<?php
!function_exists('getdirname') && exit('Forbidden');

empty($type) && $type = GetGP('type','G');
$headerdb = $showhead = array();
$indexdb = array('blog','photo','bookmark','goods','file','music');
$headerdb['index'] = $menuhead['index'];
foreach ($menulang as $key => $value) {
	!$tkey && array_key_exists($type.$action,$value) && $tkey = $key;
	if ((in_array($key,$indexdb) && strpos(",$_GROUP[module],",",$key,")===false) || ($key == 'teamcp' && $db_teamgroups && strpos(",$db_teamgroups,",",$groupid,")===false)) {
		continue;
	}
	$headerdb[$key] = $menuhead[$key];
}
!$tkey && $tkey = 'index';
$headerdb[$tkey][1] = '';
if ($tkey != 'index') {
	foreach ($menulang[$tkey] as $key => $value) {
		$key == $type.$action && $value[1] = '';
		$db_cbbbsopen == '0' && $key=='bbsatc' && $value[0]='';
		($db_keywordlink == '0' || ($db_keywordlink == '1' && $_GROUP[keywordlink] == '0')) && $key=='userklink' && $value[0]='';
		$type != 'read' && $key=='readmessage' && $value[0]='';
		$showhead[$key] = $value;
	}
}
require PrintEot('top');
?>