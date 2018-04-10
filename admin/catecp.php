<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
include_once(D_P."data/cache/forum_cache_$job.php");
$catedb = ${strtoupper('_'.$job)};

InitGP(array('cid','cup'));
if (in_array($set,array('delete','unite'))) {
	foreach ($catedb as $value) {
		if ($value['cup'] == $cid) {
			adminmsg('unite_havesub');
			break;
		}
	}
}
if ($_POST['step']!=2) {
	!$set && $set = 'list';
	$categpslt = '';
	foreach ($catedb as $key => $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$value['hname'] = preg_replace('/\<(.+?)\>/is','',$value['name']);
		$value['bname'] = !$add ? "<b>$value[name]</b>" : $add.' '.$value['name'];
		$value['vieworder'] = (int)$value['vieworder'];
		$selected = ($set == 'edit' && $cup && $value['cid'] == $cup) ? ' SELECTED' : '';
		$categpslt .= "<option value=\"$value[cid]\"$selected>$add $value[name]</option>";
		$catedb[$key] = $value;
	}
	if ($set == 'list') {
		$cpatcurl = "$admin_file?action=cateatc&job=$job&set=list&atccid=";
		if ($job == 'user') {
			$cpatcurl = "$admin_file?action=usercp&job=list&usercid=";
		} elseif ($job == 'team') {
			$cpatcurl = "$admin_file?action=teamatc&job=list&teamcid=";
		}
	} elseif ($set == 'edit') {
		$catedb = Char_cv($catedb);
		ifcheck(array('ifshow' => $catedb[$cid]['_ifshow']));
	} elseif ($set == 'delete') {
		if ($job == 'user' || $job == 'team') {
			$sqlfrom = $job == 'user' ? 'pw_userinfo' : "pw_$job";
			$error = $job;
			$count = $db->get_value("SELECT COUNT(*) FROM $sqlfrom WHERE cid='$cid'");
		} else {
			$error = 'atc';
			$count = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE cid='$cid'");
		}
		$count > 0 && adminmsg("cate_{$error}exist");
		$db->update("DELETE FROM pw_categories WHERE cid='$cid'");
		updatecache_cate($job);
		adminmsg('operate_success');
	}
	include PrintEot('catecp');footer();
} else {
	$name = Char_cv(GetGP('name','P',0),'N');
	if ($set == 'add') {
		empty($name) && adminmsg('cate_nameempty');
		$type = $cup != 0 ? $catedb[$cup]['type']+1 : 0;
		$db->update("INSERT INTO pw_categories (cup,type,name,catetype) VALUES ('$cup','$type','$name','$job')");
		$cid = $db->insert_id();
		updatecache_cate($job);
		$basename .= "&set=edit&cid=$cid&cup=$cup";
		adminmsg('operate_success');
	} elseif ($set == 'list') {
		$order = GetGP('order','P');
		foreach ($order as $key => $value) {
			$value = (int)$value;
			$value != $catedb[$key]['vieworder'] && $db->update("UPDATE pw_categories SET vieworder='$value' WHERE cid='$key'");
		}
		updatecache_cate($job);
		adminmsg('operate_success');
	} elseif ($set == 'edit' && (int)$cid > 0) {
		InitGP(array('descrip','ifshow'),'P',0);
		$descrip = Char_cv($descrip,'N');
		$ifshow = (int)$ifshow;
		$cup == $cid && adminmsg('catecup_error1');
		$catecup = $db->get_value("SELECT cup FROM pw_categories WHERE cid='$cid'");
		if ($cup != $catecup) {
			$cupinfo = $db->get_value("SELECT cupinfo FROM pw_categories WHERE cid='$cup'");
			strpos(",$cupinfo,",",$cid,")!==false && adminmsg('catecup_error2');
		}
		$db->update("UPDATE pw_categories SET name='$name',descrip='$descrip',cup='$cup',_ifshow='$ifshow' WHERE cid='$cid'");
		updatecache_cate($job);
		adminmsg('operate_success',"$basename&set=edit&cid=$cid&cup=$cup");
	} elseif ($set == 'unite') {
		$tocid = GetGP('tocid','P',0);
		$tocid = (int)$tocid;
		if ($cid!=$tocid) {
			$count = 0;
			if ($job == 'user' || $job == 'team') {
				$sqlfrom = $job == 'user' ? 'pw_userinfo' : "pw_$job";
				$db->update("UPDATE $sqlfrom SET cid='$tocid' WHERE cid='$cid'");
				$count = $db->get_value("SELECT COUNT(*) FROM $sqlfrom WHERE cid='$cid'");
			} else {
				$db->update("UPDATE pw_items SET cid='$tocid' WHERE cid='$cid'");
				$db->update("UPDATE pw_comment SET cid='$tocid' WHERE cid='$cid'");
				$count = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE cid='$cid'");
			}
			$db->update("DELETE FROM pw_categories WHERE cid='$cid'");
			$db->update("UPDATE pw_categories SET counts='$count' WHERE cid='$tocid'");
			updatecache_cate($job);
		}
		$basename .= '&set=unite';
		adminmsg('operate_success');
	} else {
		adminmsg('undefined_action');
	}
}
?>