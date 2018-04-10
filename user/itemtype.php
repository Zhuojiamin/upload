<?php
!function_exists('usermsg') && exit('Forbidden');

!in_array($type,array('blog','goods','photo','music','file','bookmark')) && exit;

require_once(R_P.'mod/windcode.php');
if ($db_charset != 'utf-8') {
	require_once(R_P.'mod/charset_mod.php');
	foreach ($_POST as $key => $value) {
		${$key} = convert_charset('utf-8',$db_charset,$value);
	}
}
if ($job == 'add') {
	!$typename && exit;
	!is_numeric($order) && $order = 0;
	$db->update("INSERT INTO pw_itemtype(uid,type,name,vieworder) VALUES('$admin_uid','$type','".Char_cv($typename)."','$order')");
	$typeid = $db->insert_id();
	$num='';
	updateusercache($admin_uid,'dirdb',$num);
	echo $typeid;exit;
}elseif($job=="edit"){
	(!is_numeric($typeid) || !$newname) && exit;
	!is_numeric($neworder) && $neworder=0;
	$newname = Char_cv($newname);
	$db->update("UPDATE pw_itemtype SET name='$newname',vieworder='$neworder' WHERE typeid='$typeid' AND uid='$admin_uid'");
	$num='';
	updateusercache($admin_uid,'dirdb',$num);
	$responseText = $typeid;
	echo $typeid;exit;
}elseif($job=="del"){
	!is_numeric($typeid) && exit;
	$db->update("DELETE FROM pw_itemtype WHERE typeid='$typeid' AND uid='$admin_uid'");
	$num='';
	updateusercache($admin_uid,'dirdb',$num);
	echo $typeid;exit;
}
?>