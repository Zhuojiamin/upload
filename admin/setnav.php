<?php
!function_exists('adminmsg') && exit('Forbidden');

if ($job == 'cp') {
	if ($_POST['step']!=2) {
		$listdb = array();
		$query = $db->query("SELECT * FROM pw_itemnav ORDER BY vieworder");
		while ($rt = $db->fetch_array($query)) {
			$listdb[] = $rt;
		}
		$db->free_result($query);
		include PrintEot('setnav');footer();
	} else {
		$basename .= '&job=cp';
		$order = GetGP('order','P','0');
		if (is_array($order)) {
			foreach ($order as $key => $value) {
				(int)$key > 0 && $db->update("UPDATE pw_itemnav SET vieworder='".(int)$value."' WHERE id='$key'");
			}
			updatecache_itemnav();
		}
		adminmsg('operate_success');
	}
} elseif ($job == 'ort') {
	!$set && $set = 'add';
	if ($_POST['step']!=2) {
		$edit = array();
		if ($set == 'edit') {
			$id = GetGP('id','G');
			$basename .= "&id=$id";
			$edit = $db->get_one("SELECT name,url,vieworder,_ifblank,_ifshow,type FROM pw_itemnav WHERE id='$id'");
			$typedb = explode(',',$edit['type']);
			foreach ($typedb as $value) {
				${'ck_'.$value} = 'CHECKED';
			}
		} elseif ($set == 'del') {
			$id = GetGP('id','G');
			$basename .= '&job=cp';
			if ((int)$id > 0) {
				$db->update("DELETE FROM pw_itemnav WHERE id = '$id'");
				updatecache_itemnav();
			}
			adminmsg('operate_success');
		} else {
			$ck_head = $ck_foot = '';
		}
		$basename .= "&set=$set";
		ifcheck(array('ifblank' => $edit['_ifblank'],'ifshow' => $edit['_ifshow']));
		include PrintEot('setnav');footer();
	} else {
		$basename .= "&set=$set&job=ort";
		InitGP(array('id','itemdb','selid'),'P');
		($set == 'edit' && (int)$id > 0) && $basename .= "&id=$id";
		if (is_array($selid)) {
			$itemdb['type'] = '';
			foreach ($selid as $value) {
				if ($value && !is_array($value)) {
					$itemdb['type'] .= ($itemdb['type'] ? ',' : '').$value;
				}
			}
		}
		if (is_array($itemdb)) {
			$updatevalue = '';
			(!$itemdb['name'] || !$itemdb['url']) && adminmsg('operate_fail');
			$itemdb['url'] = str_replace("$db_blogurl/",'',$itemdb['url']);
			
			foreach ($itemdb as $key => $value) {
				if (isset($value)) {
					$updatevalue .= ($updatevalue ? ',' : '')."$key='$value'";
				}
			}
			if ($updatevalue) {
				$updatesql = '';
				if ($set == 'add') {
					$updatesql = "INSERT INTO pw_itemnav SET $updatevalue";
				} elseif ($set == 'edit' && (int)$id > 0) {
					$updatesql = "UPDATE pw_itemnav SET $updatevalue WHERE id='$id'";
				}
				if ($updatesql) {
					$db->update($updatesql);
					updatecache_itemnav();
				}
			}
			adminmsg('operate_success');
		}
		adminmsg('operate_fail');
	}
}
?>