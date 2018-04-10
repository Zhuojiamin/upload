<?php
!function_exists('adminmsg') && exit('Forbidden');

$moddb = array();
if ($job != 'update') {
	$query = $db->query("SELECT id,name,funcname,everycache,lastcache,limitnum,shownum FROM pw_module WHERE uid='0' ORDER BY lastcache");
	while ($rt = $db->fetch_array($query)) {
		(int)$rt['limitnum']<1 && $rt['limitnum']=10;
		if ($rt['shownum']) {
			list($rt['shownum']) = explode(',',$rt['shownum']);
			$rt['shownum'] = $otherlang['mod_title'].$rt['shownum'];
//			$rt['shownum'] = str_replace(',',','.$otherlang['mod_content'],$rt['shownum']);
		}
//		strlen($rt['shownum']) < 1 && $rt['shownum'] = $otherlang['mod_title'].'0,'.$otherlang['mod_content'].'0';
		strlen($rt['shownum']) < 1 && $rt['shownum'] = $otherlang['mod_title'].'0';
		!$rt['everycache'] && $rt['everycache'] = '---';
		$rt['lastcache'] = $rt['lastcache'] ? get_date($rt['lastcache']) : '---';
		$moddb[] = $rt;
	}
	$db->free_result($query);
} else {
	$id = GetGP('id');
	if ($_POST['step'] != 2) {
		$ctimearray = array();
		$moddb = $db->get_one("SELECT name,everycache,limitnum,shownum FROM pw_module WHERE id='$id'");
		$moddb['everycache'] = (int)$moddb['everycache'];
		(int)$moddb['limitnum']<1 && $moddb['limitnum']=10;
		list($moddb['showtnum'],) = CheckInt(explode(',',$moddb['shownum']));
		unset($moddb['shownum']);
	} else {
		$cachetime = $shownum = '';
		InitGP(array('name','funcname','everycache','limitnum','shownum'),'P');
		!is_array($shownum) && $shownum = array();
		$shownum = CheckInt($shownum);
		$shownum[] = 0;
		$shownum = implode(',',$shownum);
		$db->update("UPDATE pw_module SET name='$name',everycache='$everycache',limitnum='$limitnum',shownum='$shownum' WHERE id='$id'");
		adminmsg('operate_success');
	}
}
include PrintEot('setmodule');footer();
?>