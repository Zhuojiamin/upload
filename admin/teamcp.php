<?php
!function_exists('adminmsg') && exit('Forbidden');

if ($job == 'set') {
	$basename .= "&job=$job";
	if ($_POST['step'] != 2) {
		ifcheck(array('teamifopen' => $db_teamifopen));
		$tmpgroup = $_gsystem+$_gmember+$_gspecial;
		$gpslt = '<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center"><tr>';
		$num = 0;
		foreach ($tmpgroup as $key => $value) {
			$num++;
			$htm_tr = $num % 4 == 0 ? '</tr><tr>' : '';
			$g_ck = strpos(",$db_teamgroups,",",$key,")!==false ? ' CHECKED' : '';
			$gpslt .= "<td><input type=\"checkbox\" name=\"teamgroups[]\" value=\"$key\"$g_ck>$value[title]</td>$htm_tr";
		}
		$gpslt .= '</tr></table>';
		unset($tmpgroup);
	} else {
		InitGP(array('config','teamgroups'),'P');
		$config['teamlimit'] = (int)$config['teamlimit'];
		$config['teamgroups'] = !empty($teamgroups) ? implode(',',CheckInt($teamgroups)) : '';
		!is_array($config) && $config = array();
		updatesetting($config);
		adminmsg('operate_success');
	}
} elseif ($job == 'list') {
	include_once(D_P.'data/cache/forum_cache_team.php');
	$basename .= "&job=$job";
	if ($set != 'edit') {
		if ($_POST['step'] != 2) {
			InitGP(array('teamcid','page'),'G');
			$teamdb = array();
			$where = $addpage = $pages = '';
			if ((int)$teamcid > 0) {
				$where = " WHERE cid='$teamcid'";
				$addpage = "teamcid=$teamcid&";
			}
			(int)$page<1 && $page = 1;
			$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
			$query = $db->query("SELECT teamid,cid,uid,username,name,blogs,bloggers,type,commend FROM pw_team$where $limit");
			while ($rt = $db->fetch_array($query)) {
				$rt['cate'] = $_TEAM[$rt['cid']]['name'];
				$teamdb[] = $rt;
			}
			$db->free_result($query);
			$count = $db->get_value("SELECT COUNT(*) FROM pw_team$where");
			if ($count > $db_perpage) {
				require_once(R_P.'mod/page_mod.php');
				$pages = page($count,$page,$db_perpage,"$basename&$addpage");
			}
		} else {
			InitGP(array('selid','type','tcid','ttype','commend'),'P');
			$teamids = '';
			!is_array($selid) && $selid = array();
			if (!empty($selid)) {
				foreach ($selid as $value) {
					if (is_numeric($value)) {
						$teamids .= ($teamids ? ',' : '')."'$value'";
					}
				}
			}
			!$teamids && adminmsg('operate_error');
			$sqlwhere = strpos($teamids,',')===false ? "=$teamids" : " IN ($teamids)";
			if ($type == 'delete') {
				$catedb = array();
				$query = $db->query("SELECT cid FROM pw_team WHERE teamid{$sqlwhere}");
				while ($rt = $db->fetch_array($query)) {
					$catedb[$rt['cid']]['num']++;
				}
				$db->free_result($query);
				foreach ($catedb as $key => $value) {
					$db->update("UPDATE pw_categories SET counts=counts-'$value[num]' WHERE cid='$key'");
				}
				$db->update("DELETE FROM pw_tblog WHERE teamid{$sqlwhere}");
				$db->update("DELETE FROM pw_tuser WHERE teamid{$sqlwhere}");
				$db->update("DELETE FROM pw_team WHERE teamid{$sqlwhere}");
				$db->update("DELETE FROM pw_tgbook WHERE teamid{$sqlwhere}");
				updatecache_cate();
			} elseif ($type == 'cgcid') {
				$db->update("UPDATE pw_team SET cid='".(int)$tcid."' WHERE teamid{$sqlwhere}");
			} elseif ($type == 'cgtype') {
				$db->update("UPDATE pw_team SET type='".(int)$ttype."' WHERE teamid{$sqlwhere}");
			} elseif ($type == 'cgcmd') {
				$db->update("UPDATE pw_team SET commend='".(int)$commend."' WHERE teamid{$sqlwhere}");
			}
			adminmsg('operate_success');
		}
	} else {
		if ($_POST['step'] != 2) {
			$teamid = GetGP('teamid','G');
			$basename .= '&set=edit';
			$teamdb = $db->get_one("SELECT cid,uid,name,descrip,icon,notice,type,ifshow,gbooktype FROM pw_team WHERE teamid='$teamid'");
			$cid = $teamdb['cid'];
			$uid = $teamdb['uid'];
			${'ifshow_'.(int)$teamdb['ifshow']} = ${'gbooktype_'.(int)$teamdb['gbooktype']} = 'CHECKED';
			ifcheck(array('type' => $teamdb['type']));
			$input = '';
			if (!$teamdb['icon']) {
				$teamdb['icon'] = 'nopic.jpg';
			} else {
				$input = '<input type="hidden" name="icon" value="'.$teamdb['icon'].'">';
			}
		} else {
			require_once(R_P.'mod/upload_mod.php');
			InitGP(array('teamid','uid','icon','teamdb'),'P','0');
			$teamid = (int)$teamid;
			$uid = (int)$uid;
			$teamdb['icon'] = null;
			$teamdb['name'] = Char_cv($teamdb['name']);
			$teamdb['type'] = (int)$teamdb['type'];
			$teamdb['ifshow'] = (int)$teamdb['ifshow'];
			$teamdb['gbooktype'] = (int)$teamdb['gbooktype'];
			$teamdb['cid'] = (int)$teamdb['cid'];
			$teamdb['notice'] = Char_cv($teamdb['notice'],'N');
			$teamdb['descrip'] = Char_cv($teamdb['descrip'],'N');
			$attachdir = "$imgdir/upload";
			$icon = Char_cv($icon);
			$uploaddb = UploadFile('t'.$uid,1);
			if ($uploaddb[0]['attachurl']) {
				$icon && P_unlink("$attachdir/$icon");
				$teamdb['icon'] = $uploaddb[0]['attachurl'];
			}
			$setfeild = '';
			foreach ($teamdb as $key => $value) {
				if (!is_null($value)) {
					$setfeild .= ($setfeild ? ',' : '')."$key='$value'";
				}
			}
			$setfeild && $db->update("UPDATE pw_team SET $setfeild WHERE teamid='$teamid'");
			adminmsg('operate_success');
		}
	}
	$categpslt = $selected = '';
	foreach ($_TEAM as $key => $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$selected = ($set == 'edit' && $cid && $value['cid'] == $cid) ? ' SELECTED' : '';
		$categpslt .= "<option value=\"$value[cid]\"$selected>$add $value[name]</option>";
	}
} else {
	adminmsg('undefined_action');
}
include PrintEot('teamcp');footer();
?>