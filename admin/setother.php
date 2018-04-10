<?php
!function_exists('adminmsg') && exit('Forbidden');

!$job && $job = 'ads';
$basename .= "&job=$job";
if ($job != 'link') {
	if ($_POST['step'] != 2) {
		${'ads_'.(int)$db_ads} = 'CHECKED';
	} else {
		$ads = GetGP('ads','P');
		if ($db_ads != $ads) {
			$db->get_value("SELECT db_name FROM pw_setting WHERE db_name='db_ads'") ? $db->update("UPDATE pw_setting SET db_value='$ads' WHERE db_name='db_ads'") : $db->update("INSERT INTO pw_setting(db_name,db_value) VALUES ('db_ads','$ads')");
			updatecache_db();
		}
		adminmsg('operate_success');
	}
} else {
	!$set && $set = 'list';
	$basename .= "&set=$set";
	if ($set == 'list') {
		if ($_POST['step'] != 2) {
			InitGP(array('page','ifcheck'),'G');
			$where = strlen($ifcheck)>0 ? " WHERE ifcheck='".(int)$ifcheck."'" : '';
			$page = GetGP('page','G');
			$sharedb = array();
			(int)$page<1 && $page = 1;
			$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
			$query = $db->query("SELECT * FROM pw_share{$where} ORDER BY threadorder $limit");
			while ($rt = $db->fetch_array($query)) {
				$sharedb[] = $rt;
			}
			$count = $db->get_value("SELECT COUNT(*) FROM pw_share");
			if ($count > $db_perpage) {
				require_once(R_P.'mod/page_mod.php');
				$pages = page($count,$page,$db_perpage,"$basename&set=cp&");
			}
		} else {
			InitGP(array('orders','selid','type'),'P',0);
			$sids = '';
			!is_array($orders) && $orders = array();
			!is_array($selid)  && $selid = array();
			$orders = CheckInt($orders);
			$selid  = CheckInt($selid);
			$type	= Char_cv($type);
			foreach ($selid as $value) {
				if ((int)$value > 0) {
					if ($type == 'delete') {
						unset($orders[$value]);
					}
					$sids .= ($sids ? ',' : '')."'$value'";
				}
			}
			foreach ($orders as $key => $value) {
				$key = (int)$key;
				$db->update("UPDATE pw_share SET threadorder='$value' WHERE sid='$key'");
			}
			if ($sids) {
				$sqlwhere = strpos($sids,',')===false ? "=$sids" : " IN ($sids)";
				if ($type == 'delete') {
					$db->update("DELETE FROM pw_share WHERE sid{$sqlwhere}");
				} elseif ($type == 'ifcheck') {
					$db->update("UPDATE pw_share SET ifcheck='1',linktime='0' WHERE sid{$sqlwhere}");
				}
			}
			updatecache_novosh('sh');
			adminmsg('operate_success');
		}
	} elseif ($set == 'add' || $set == 'edit') {
		if ($_POST['step'] != 2) {
			$sharedb = array();
			if ($set == 'add') {
				$sharedb['threadorder'] = 0;
			} else {
				$sid = GetGP('sid');
				if ((int)$sid > 0) {
					$sharedb = $db->get_one("SELECT * FROM pw_share WHERE sid='$sid'");
					$sharedb['threadorder'] = (int)$sharedb['threadorder'];
				}
			}
		} else {
			InitGP(array('name','threadorder','url','descrip','logo'),'P');
			(!$name || !$url || !$descrip) && adminmsg('operate_fail');
			$threadorder = (int)$threadorder;
			if ($set == 'add') {
				$db->update("INSERT INTO pw_share (threadorder,name,url,descrip,logo,ifcheck) VALUES ('$threadorder','$name','$url','$descrip','$logo','1')");
				$sid = $db->insert_id();
			} else {
				$sid = GetGP('sid','P');
				$db->update("UPDATE pw_share SET threadorder='$threadorder',name='$name',url='$url',descrip='$descrip',logo='$logo' WHERE sid='$sid'");
			}
			updatecache_novosh('sh');
			$basename .= "&sid=$sid";
			adminmsg('operate_success');
		}
	}
}
include PrintEot('setother');footer();
?>