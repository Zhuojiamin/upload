<?php
!function_exists('adminmsg') && exit('Forbidden');

!$job && $job = 'cp';
if ($job == 'cp') {
	if ($_POST['step'] != 2) {
		$page = GetGP('page','G');
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT id,subject,type,votedate,_ifshow as ifshow FROM pw_blogvote ORDER BY votedate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['votedate'] = get_date($rt['votedate'],'Y-m-d');
			$votedb[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_blogvote WHERE 1");
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&");
		}
	} else {
		$basename .= '&job=cp';
		InitGP(array('selid','type','ifshow'),'P',0);
		$ids = '';
		!is_array($selid)  && $selid = array();
		$selid  = CheckInt($selid);
		$type   = Char_cv($type);
		$ifshow = (int)$ifshow;
		foreach ($selid as $value) {
			if ((int)$value > 0) {
				$ids .= ($ids ? ',' : '')."'$value'";
			}
		}
		!$ids && adminmsg('operate_error');
		$sqlwhere = strpos($ids,',') ? "=$ids" : " IN ($ids)";
		if ($type == 'delete') {
			$db->update("DELETE FROM pw_blogvote WHERE id{$sqlwhere}");
		} elseif ($type == 'cgshow') {
			$db->update("UPDATE pw_blogvote SET _ifshow='$ifshow' WHERE id{$sqlwhere}");
		}
		updatecache_novosh('vo');
		adminmsg('operate_success');
	}
} elseif ($job == 'ort') {
	if ($set != 'edit') {
		if ($_POST['step'] != 2) {
			$votedb = array();
			$type_N = $ifshow_Y = $ifview_N = 'CHECKED';
		} else {
			$voteitem = array();
			InitGP(array('subject','type','maxnum','ifshow','ifview','newvitemdb','content'),'P');
			!is_array($newvitemdb) && $newvitemdb = array();
			if ((int)$type != 1 || $maxnum == 1) {
				$type = 0;
				$maxnum = 1;
			}
			$maxnum = (int)$maxnum;
			$ifshow = (int)$ifshow;
			$ifview = (int)$ifview;
			foreach ($newvitemdb as $key => $value) {
				(int)$key > -1 && $value && !is_array($value) && $voteitem[] = $value;
			}
			unset($newvitemdb);
			if (!$subject) {
				$basename .= "&job=$job&set=add";
				adminmsg('operate_error');
			}
			$basename .= "&job=$job&set=edit";
			$db->update("INSERT INTO pw_blogvote (subject,voteitem,content,type,votedate,_ifshow,maxnum,_ifview) VALUES ('$subject','','$content','$type','$timestamp','$ifshow','$maxnum','$ifview')");
			$id = $db->insert_id();
			foreach ($voteitem as $value) {
				$value && $db->update("INSERT INTO pw_voteitem (vid,item) VALUES ('$id','$value')");
				$v_itemid = $db->insert_id();
				$upvoteitem[$v_itemid] = $value;
			}
			if (!empty($upvoteitem)) {
				Strip_S($upvoteitem);
				$upvoteitem = addslashes(serialize($upvoteitem));
			} else {
				$upvoteitem = '';
			}
			$db->update("UPDATE pw_blogvote SET voteitem='$upvoteitem' WHERE id='$id'");
			$basename .= "&id=$id";
			adminmsg('operate_success');
		}
	} else {
		$basename .= '&set=edit';
		$id = GetGP('id');
		$votedb = $db->get_one("SELECT subject,content,type,_ifshow,maxnum,_ifview FROM pw_blogvote WHERE id='$id'");
		$query  = $db->query("SELECT id,item FROM pw_voteitem WHERE vid='$id'");
		while ($rt = $db->fetch_array($query)) {
			$votedb['items'][$rt['id']] = $rt['item'];
		}
		$db->free_result($query);
		if ($_POST['step'] != 2) {
			$ifcheckdb = array('type' => $votedb['type'],'ifshow' => $votedb['_ifshow'],'ifview' => $votedb['_ifview']);
			ifcheck($ifcheckdb);
		} else {
			$basename .= "&job=$job";
			$voteitem = array();
			InitGP(array('subject','type','maxnum','ifshow','ifview','oldvitemdb','newvitemdb','content'),'P');
			!is_array($newvitemdb) && $newvitemdb = array();
			!is_array($oldvitemdb) && $oldvitemdb = array();
			if ((int)$type != 1 || $maxnum == 1) {
				$type = 0;
				$maxnum = 1;
			}
			$maxnum = (int)$maxnum;
			$ifshow = (int)$ifshow;
			$ifview = (int)$ifview;
			foreach ($oldvitemdb as $key => $value) {
				if ((int)$key > -1 && !is_array($value)) {
					if (isset($votedb['items'][$key])) {
						if (trim($value)) {
							$db->update("UPDATE pw_voteitem SET item='$value' WHERE id='$key'");
							$voteitem[$key] = $value;
						} else {
							$db->update("DELETE FROM pw_voteitem WHERE id='$key'");
						}
					} else {
						if ($value) {
							$db->update("INSERT INTO pw_voteitem (vid,item) VALUES ('$id','$value')");
							$vitemid = $db->insert_id();
							$voteitem[$vitemid] = $value;
						}
					}
					
				}
			}
			foreach ($newvitemdb as $key => $value) {
				if ((int)$key > -1 && $value && !is_array($value)) {
					$db->update("INSERT INTO pw_voteitem (vid,item) VALUES ('$id','$value')");
					$vitemid = $db->insert_id();
					$voteitem[$vitemid] = $value;
				}
			}
			if (!empty($voteitem)) {
				$upvoteitem = $voteitem;
				Strip_S($upvoteitem);
				$upvoteitem = addslashes(serialize($upvoteitem));
			} else {
				$upvoteitem = '';
			}
			$db->update("UPDATE pw_blogvote SET subject='$subject',voteitem='$upvoteitem',content='$content',type='$type',_ifshow='$ifshow',maxnum='$maxnum',_ifview='$ifview' WHERE id='$id'");
			$basename .= "&id=$id";
			updatecache_novosh('vo');
			adminmsg('operate_success');
		}
	}
}
include PrintEot('setvote');footer();
?>