<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
if ($job == 'stats') {
	$statsdb = array();
	$query = $db->query("SELECT COUNT(*) AS count,groupid FROM pw_user GROUP BY groupid");
	while ($rt = $db->fetch_array($query)) {
		$statsdb[$rt['groupid']] = array($rt['count'],$_alllevel[$rt['groupid']]['title']);
	}
	$db->free_result($query);
	include PrintEot('setgroup');footer();
} elseif ($job == 'level') {
	if (!$set) {
		if (!$_POST['step']) {
			include PrintEot('setgroup');footer();
		} else {
			$ort = GetGP('ort','P');
			if ($_POST['step'] == 'default') {
				$pre = 'd';
			} elseif ($_POST['step'] == 'member') {
				$pre = 'm';
			} elseif ($_POST['step'] == 'system') {
				$pre = 's';
			} elseif ($_POST['step'] == 'special') {
				$pre = 'sp';
			}
			InitGP(array($pre.'title',$pre.'img',$pre.'credit'),'P');
			$title  = ${$pre.'title'};
			$img	= CheckInt(${$pre.'img'});
			$credit = CheckInt(${$pre.'credit'});
			if ($ort!='add') {
				if (!is_array($title) || !is_array($img) || ($_POST['step'] == 'member' && (!is_array($credit) || empty($credit))) || empty($title) || empty($img)) {
					adminmsg('operate_fail');
				}
				foreach ($title as $key => $value) {
					$addcredit = '';
					if ($_POST['step'] == 'member') {
						!is_numeric($credit[$key]) && $credit[$key] = 20*pow(2,(int)$credit[$key]);
						$addcredit .= ",creditneed='$credit[$key]'";
					}
					$db->update("UPDATE pw_group SET title='$title[$key]',img='$img[$key]'{$addcredit} WHERE gid='$key'");
				}
			} else {
				if ($_POST['step'] != 'default') {
					(!$title || !$img) && adminmsg('operate_fail');
					$credit = (int)$credit;
					$ifdefault = $_POST['step'] == 'member' ? 1 : 0;
					$db->update("INSERT INTO pw_group(type,title,img,creditneed,ifdefault) VALUES ('$_POST[step]', '$title', '$img','$credit','$ifdefault')");
					$gid = $db->insert_id();
				}
			}
			$jumpurl = $basename.($ort!='add' ? '' : '&set=edit&gid='.$gid);
			updatecache_level();
			adminmsg('operate_success',$jumpurl);
		}
	} elseif ($set == 'edit') {
		$gid = GetGP('gid');
		if ($_POST['step'] != '2') {
			((int)$gid<1) && $gid = '2';
			$basename .= '&set=edit';
			if ($_GET['step'] != '3') {
				if (file_exists(D_P."data/groupdb/group_$gid.php")) {
					require_once(Pcv(D_P."data/groupdb/group_$gid.php"));
				} else {
					require_once(D_P.'data/groupdb/group_1.php');
				}
				$_GROUP['ifdefault'] && $gid!=1 && include_once(D_P.'data/groupdb/group_1.php');
				$gpslt = '';
				foreach ($_alllevel as $key => $value) {
					$lvslt = $gid==$key ? 'SELECTED' : '';
					$gpslt .= "<option value=\"$key\" $lvslt>$value[title]</option>";
				}
				$module = explode(',',$_GROUP['module']);
				foreach ($module as $value) {
					${$value.'_CK'} = 'CHECKED';
				}
				unset($module);
				!is_numeric($_GROUP['allowsearch']) && $_GROUP['allowsearch'] = 0;
				!is_numeric($_GROUP['ifdomain'])	&& $_GROUP['ifdomain']    = 0;
				${'allowsch_'.$_GROUP['allowsearch']} = ${'ifdomain_'.$_GROUP['ifdomain']} = 'CHECKED';
				list($_GROUP['upfacew'],$_GROUP['upfaceh']) = explode(',',$_GROUP['upfacewh']);
				list($_GROUP['patcnum'],$_GROUP['pcmtnum'],$_GROUP['pgbooknum']) = explode(',',$_GROUP['postnum']);
				list($_GROUP['latcnum'],$_GROUP['lcmtnum'],$_GROUP['lgbooknum']) = explode(',',$_GROUP['limitnum']);
				$ifcheckdb = array(
					'allowread' => $_GROUP['allowread'],
					'portait' => $_GROUP['allowportait'],
					'upface' => $_GROUP['allowupface'],
					'ifexport' => $_GROUP['ifexport'],
					'allowpost' => $_GROUP['allowpost'],
					'closecmt' => $_GROUP['closecmt'],
					'closegbook' => $_GROUP['closegbook'],
					'htmlcode' => $_GROUP['htmlcode'],
					'wysiwyg' => $_GROUP['wysiwyg'],
					'allowupload' => $_GROUP['allowupload'],
					'allowdown' => $_GROUP['allowdown'],
					'admincp' => $_GROUP['admincp'],
					'allowlimit' => $_GROUP['allowlimit'],
					'deluser' => $_GROUP['deluser'],
					'delatc' => $_GROUP['delatc'],
					'delcmt' => $_GROUP['delcmt'],
					'delattach' => $_GROUP['delattach'],
					'cmduser' => $_GROUP['cmduser'],
					'cmdact' => $_GROUP['cmdact'],
					'allowmsg' => $_GROUP['allowmsg'],
					'keywordlink' => $_GROUP['keywordlink']
				);
				$_GROUP['attachsize'] /= 1024;
				ifcheck($ifcheckdb);
				include PrintEot('setgroup');footer();
			} else {
				empty($_gmember[$gid]) && adminmsg('level_error');
				$db->update("UPDATE pw_group SET ifdefault='1' WHERE gid='$gid'");
				P_unlink(D_P."data/groupdb/group_$gid.php");
				adminmsg('operate_success',"$basename&gid=$gid");
			}
		} else {
			InitGP(array('othergroup','module','group','upfacewh','postnum','limitnum','agroup','othergid'),'P');
			$group['module']	= implode(',',(array)$module);
			$group['upfacewh']	= implode(',',CheckInt($upfacewh));
			$group['postnum']	= implode(',',CheckInt($postnum));
			$group['limitnum']	= implode(',',CheckInt($limitnum));
			$group['attachsize'] *= 1024;
			$group['attachext'] = ArrayTxt($group['attachext']);//charcv
			
			if (is_array($agroup) && !empty($agroup)) {
				foreach ($agroup as $key => $value) {
					$group[$key] = $value;
				}
			}
			!is_numeric($group['admincp']) && $group['admincp'] = 0;
			$mright = array();
			foreach ($group as $key => $value) {
				if ($key != 'admincp') {
					!in_array($key,array('module','upfacewh','postnum','limitnum','attachext')) && $group[$key] = CheckInt($value);
					$mright[$key] = $group[$key];
				}
			}
			Strip_S($mright);
			$mright = addslashes(serialize($mright));
			$db->update("UPDATE pw_group SET ifdefault='0',mright='$mright',admincp='$group[admincp]' WHERE gid='$gid'");
			updatecache_group($gid);
			$othergids = '';
			if (is_array($othergid) && !empty($othergid)) {
				foreach ($othergid as $value) {
					$othergids .= ($othergids ? "','" : '').$value;
				}
			}
			$othergids && $othergids = "'".$othergids."'";
			if (is_array($othergroup) && !empty($othergroup) && $othergids) {
				$tgroup = array();
				$sqlwhere = strpos($othergids,',')===false ? "=$othergids" : " IN ($othergids)";
				$query = $db->query("SELECT gid,type,mright,admincp FROM pw_group WHERE gid{$sqlwhere}");
				while ($rt = $db->fetch_array($query)) {
					$rt['mright'] && $tgroup = unserialize($rt['mright']);
					foreach ($othergroup as $value) {
						$tgroup[$value] = $group[$value];
					}
					$admincp = $rt['admincp'];
					if (!in_array($rt['type'],array('system','special')) && !empty($agroup)) {
						foreach ($agroup as $key => $value) {
							unset($tgroup[$key]);
						}
					}
					Strip_S($tgroup);
					$tgroup = addslashes(serialize($tgroup));
					$db->update("UPDATE pw_group SET ifdefault='0',mright='$tgroup',admincp='$admincp' WHERE gid='$rt[gid]'");
				}
				updatecache_group();
			}
			$basename .= "&set=edit&gid=$gid";
			adminmsg('operate_success');
		}
	} elseif ($set == 'delete') {
		$gid = GetGP('gid','G');
		$gid<7 && adminmsg('level_delete');
		$db->update("DELETE FROM pw_group WHERE gid='$gid'");
		updatecache_level();
		adminmsg('operate_success');
	}
}
?>