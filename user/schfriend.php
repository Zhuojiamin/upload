<?php
!function_exists('usermsg') && exit('Forbidden');
$friends=array();
if(empty($job)){
	if(empty($step)){
		$sqladd = $gid ? "AND b.gid='$gid'" : '';
		$query=$db->query("SELECT b.*,u.username FROM pw_blogfriend b LEFT JOIN pw_user u ON b.fuid=u.uid WHERE b.uid='$admin_uid' AND b.ifcheck=1 $sqladd ORDER BY b.fdate DESC");
		while($rt=$db->fetch_array($query)){
			$rt['fdate']=get_date($rt['fdate']);
			$friends[]=$rt;
		}
		$query = $db->query("SELECT * FROM pw_blogfriendg WHERE uid='$admin_uid'");
		while($rt2 = $db->fetch_array($query)){
			$friendgroup[] = $rt2;
		}
		require_once PrintEot('schfriend');footer();
	}elseif($step=='del'){
		if(!$selid = checkselid($selid)){
			usermsg('operate_error');
		}
		if ($ntype == 'delete'){
			$friends = $db->get_value("SELECT friends FROM pw_user WHERE uid='$admin_uid'");
			$afriends = explode(',',$friends);
			$query = $db->query("SELECT fuid FROM pw_blogfriend WHERE uid='$admin_uid' AND ifcheck=1 AND id IN($selid)");
			while($rt = $db->fetch_array($query)){
				$delfriends[] = $rt['fuid'];
			}
			$friends = implode(',',array_diff($afriends,$delfriends));
			$db->update("DELETE FROM pw_blogfriend WHERE uid='$admin_uid' AND ifcheck=1 AND id IN($selid)");
			$db->update("UPDATE pw_user SET friends='$friends' WHERE uid='$admin_uid'");
		} elseif ($ntype == 'setgid'){
			$setfriends = array();
			$query = $db->query("SELECT id,gid FROM pw_blogfriend WHERE uid='$admin_uid' AND ifcheck=1 AND id IN($selid)");
			while($rt = $db->fetch_array($query)){
				if($rt['gid'] != '0'){
					$setfriends[$rt['gid']]['gids']++;
				}
				$sumgids++;
			}
			foreach($setfriends as $k => $v){
				$db->update("UPDATE pw_blogfriendg SET gnum=gnum-'$v[gids]' WHERE gid='$k'");
			}
			$db->update("UPDATE pw_blogfriendg SET gnum=gnum+'$sumgids' WHERE gid='$atcgid'");
			$db->update("UPDATE pw_blogfriend SET gid='$atcgid' WHERE uid='$admin_uid' AND ifcheck=1 AND id IN($selid)");
		}
		usermsg('operate_success',"$basename");
	}
}elseif($job=='check'){
	if(empty($step)){
		$query=$db->query("SELECT b.*,u.username FROM pw_blogfriend b LEFT JOIN pw_user u ON b.uid=u.uid WHERE b.fuid='$admin_uid' AND b.ifcheck=0 ORDER BY b.fdate DESC");
		while($rt=$db->fetch_array($query)){
			$rt['fdate']=get_date($rt['fdate']);
			$friends[]=$rt;
		}
		
		$query = $db->query("SELECT * FROM pw_blogfriendg WHERE uid='$admin_uid'");
		while($rt2 = $db->fetch_array($query)){
			$friendgroup[] = $rt2;
		}
		require_once PrintEot('schfriend');footer();
	}elseif($step=='check'){
		if(!$selid = checkselid($selid)){
			usermsg('operate_error');
		}
		$db->update("UPDATE pw_blogfriend SET ifcheck=1 WHERE fuid='$admin_uid' AND id IN($selid)");
		$query = $db->query("SELECT uid FROM pw_blogfriend WHERE fuid='$admin_uid' AND id IN($selid)");
		while($rt = $db->fetch_array($query)){
			$friends = $db->get_value("SELECT friends FROM pw_user WHERE uid='{$rt['uid']}'");
			$friends = (empty($friends) ? $admin_uid : ($friends.','.$admin_uid));
			$db->update("UPDATE pw_user SET friends='$friends' WHERE uid='{$rt['uid']}'");
			$frienddb_query = $db->query("SELECT bf.fuid,u.username,u.icon FROM pw_blogfriend bf LEFT JOIN pw_user u ON bf.fuid=u.uid WHERE bf.uid=$rt[uid]");
			while ($rt2 = $db->fetch_array($frienddb_query)) {
				$rt2['icon'] = showfacedesign($rt2['icon']);
				$frienddb[] = $rt2;
			}
			$frienddb = serialize($frienddb);
			$db->update("UPDATE pw_userinfo SET frienddb='$frienddb' WHERE uid='$rt[uid]'");
			$frienddb = array();
		}
		usermsg('operate_success',"$basename&job=check");
	}elseif($step=='del'){
		if(!$selid = checkselid($selid)){
			usermsg('operate_error');
		}
		$db->update("DELETE FROM pw_blogfriend WHERE fuid='$admin_uid' AND ifcheck=0 AND id IN($selid)");
		usermsg('operate_success',"$basename&job=check");
	}
}
?>