<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
include_once(D_P.'data/cache/hcate_cache.php');

$hid = GetGP('hid');
$hname = GetGP('hname','P');
$categpslt = '';
foreach ($_HCATE as $key => $value) {
	$value['vieworder'] = (int)$value['vieworder'];
	if ($hname && $_POST['step'] == 2) {
		($set == 'add' || ($set == 'edit' && $hid && $hid != $value['id'])) && $hname == $value['name'] && adminmsg('cate_exist');
	}
	$selected = ($hid && $value['id'] == $hid) ? ' SELECTED' : '';
	$categpslt .= "<option value=\"$value[id]\"$selected>$value[name]</option>";
	$_HCATE[$key] = $value;
}
!$set && $set = 'list';
if ($job == 'class') {
	if ($_POST['step']!=2) {
		if ($set == 'delete') {
			$count = $db->get_value("SELECT COUNT(*) FROM pw_hobbyitem WHERE hid='$hid'");
			$count > 0 && adminmsg("cate_hobbyexist");
			$db->update("DELETE FROM pw_hobby WHERE id='$hid'");
			updatecache_hc();
			adminmsg('operate_success');
		}
	} else {
		if ($set == 'list') {
			$orders = GetGP('orders','P');
			!is_array($orders) && $orders = array();
			foreach ($orders as $key => $value) {
				$key = (int)$key;
				$db->update("UPDATE pw_hobby SET vieworder='$value' WHERE id='$key'");
			}
			updatecache_hc();
			adminmsg('operate_success');
		} elseif ($set == 'add') {
			!$hname && adminmsg('cate_nameempty');
			$db->update("INSERT INTO pw_hobby (name,vieworder) VALUES ('$hname','0')");
			$hid = $db->insert_id();
			updatecache_hc();
			adminmsg('operate_success',"$basename&set=edit&hid=$hid");
		} elseif ($set == 'edit') {
			!$hname && adminmsg('cate_nameempty');
			$vieworder = GetGP('vieworder','P',0);
			$vieworder = (int)$vieworder;
			$db->update("UPDATE pw_hobby SET name='$hname',vieworder='$vieworder' WHERE id='$hid'");
			updatecache_hc();
			adminmsg('operate_success',"$basename&set=edit&hid=$hid");
		}
	}
} elseif ($job == 'cp') {
	$id = GetGP('id');
	if ($_POST['step']!=2) {
		if ($set == 'list') {
			$ifcheck = GetGP('ifcheck','P');
			$page = GetGP('page','G');
			$hobbydb = array();
			$addpage = $where = $pages = '';
			if ((int)$hid > 0) {
				$where = "hid='$hid'";
				$addpage = "hid=$hid&";
			}
			if (strlen($ifcheck) > 0 && (int)$ifcheck > -1) {
				${'checkslt_'.(int)$ifcheck} = ' SELECTED';
				$where .= ($where ? ' AND' : '')." ifcheck='$ifcheck'";
				$addpage .= "ifcheck=$ifcheck&";
			}
			$where && $where = 'WHERE '.$where;
			(int)$page<1 && $page = 1;
			$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
			$query = $db->query("SELECT * FROM pw_hobbyitem $where ORDER BY vieworder $limit");
			while ($rt = $db->fetch_array($query)) {
				$rt['vieworder'] = (int)$rt['vieworder'];
				$hobbydb[] = $rt;
			}
			$db->free_result($query);
			$count = $db->get_value("SELECT COUNT(*) FROM pw_hobbyitem $where");
			if ($count > $db_perpage) {
				require_once(R_P.'mod/page_mod.php');
				$pages = page($count,$page,$db_perpage,"$basename&set=list&$addpage");
			}
		} elseif ($set == 'edit') {
			$hobbydb = $db->get_one("SELECT * FROM pw_hobbyitem WHERE id='$id'");
			$hobbydb['vieworder'] = (int)$hobbydb['vieworder'];
		}
	} else {
		$name = GetGP('name','P');
		if ($set == 'list') {
			InitGP(array('orders','selid','type','orthid'),'P',0);
			$ids = '';
			!is_array($orders) && $orders = array();
			!is_array($selid)  && $selid = array();
			$type   = Char_cv($type);
			$orders = CheckInt($orders);
			$selid  = CheckInt($selid);
			foreach ($selid as $value) {
				if ((int)$value > 0) {
					if ($type == 'delete') {
						unset($orders[$value]);
					}
					$ids .= ($ids ? ',' : '')."'$value'";
				}
			}
			foreach ($orders as $key => $value) {
				$key = (int)$key;
				$db->update("UPDATE pw_hobbyitem SET vieworder='$value' WHERE id='$key'");
			}
			if ($ids) {
				$sqlwhere = strpos($ids,',')===false ? "=$ids" : " IN ($ids)";
				if ($type == 'delete') {
					$db->update("DELETE FROM pw_hobbyitem WHERE id{$sqlwhere}");
				} elseif ($type == 'cgcheck') {
					$db->update("UPDATE pw_hobbyitem SET ifcheck='1' WHERE id{$sqlwhere}");
				} elseif ($type == 'cghid') {
					$db->update("UPDATE pw_hobbyitem SET hid='".(int)$orthid."' WHERE id{$sqlwhere}");
				}
			}
			adminmsg('operate_success');
		} elseif ($set == 'edit') {
			!$name && adminmsg('cate_nameempty');
			$vieworder = GetGP('vieworder','P',0);
			$vieworder = (int)$vieworder;
			$db->update("UPDATE pw_hobbyitem SET name='$name',hid='$hid',vieworder='$vieworder' WHERE id='$id'");
			adminmsg('operate_success',"$basename&set=edit&id=$id&hid=$hid");
		} elseif ($set == 'add') {
			!$name && adminmsg('hobby_nameempty');
			empty($hid) && adminmsg('hobby_cateempty');
			$db->update("INSERT INTO pw_hobbyitem (name,hid,vieworder,ifcheck) VALUES ('$name','$hid','0','1')");
			$id = $db->insert_id();
			$basename .= "&set=edit&id=$id&hid=$hid";
			adminmsg('operate_success');
		}
	}
}
include PrintEot('sethobby');footer();
?>