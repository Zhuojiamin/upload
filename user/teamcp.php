<?php
!function_exists('usermsg') && exit('Forbidden');
include_once D_P.'data/cache/forum_cache_team.php';
include_once R_P.'mod/ipfrom_mod.php';

!$db_teamifopen && usermsg('team_close');
if ($db_teamgroups && strpos($db_teamgroups,"$groupid")===false) {
	usermsg('team_groupright');
}

$teamid = (int)$teamid;
if (!$job) {
	$teamdb = array();
	$query = $db->query("SELECT teamid,name,cid FROM pw_team WHERE uid='$admin_uid'");
	while ($rt = $db->fetch_array($query)) {
		$rt['cname'] = $_TEAM[$rt['cid']]['name'];
		$teamdb[] = $rt;
	}
	require_once PrintEot('teamcp');footer();
} elseif ($job=='add') {
	if ($db_teamlimit) {
		$count = $db->get_value("SELECT COUNT(*) FROM pw_team WHERE uid='$admin_uid'");
		$count >= $db_teamlimit && usermsg('team_limit');
	}
	if ($step!=2) {
		$rt['icon'] = 'nopic.jpg';
		$input = '';
		$gbooktype_0 = 'CHECKED';
		$ifshow_3 = 'CHECKED';
		$forumcache = forumoption('team');
		require_once PrintEot('teamcp');footer();
	} else {
		!$name && usermsg('teamcp_name');
		!$cid && usermsg('teamcp_cate');
		$ckteamid = $db->get_value("SELECT teamid FROM pw_team WHERE name='$name'");
		$ckteamid && usermsg('team_nameerror');
		$name	 = Char_cv($name);
		$descrip = Char_cv($descrip);
		$notice  = Char_cv($notice);
		$gbooktype=(int)$gbooktype;
		$type=$type==1 ? 1 : 0;
		include_once(R_P.'mod/upload_mod.php');
		$attachdir = "$imgdir/upload";
		$icon = Char_cv($icon);
		$uploaddb = UploadFile('t'.$admin_uid,1);
		if ($uploaddb[0]['attachurl']) {
			$icon && P_unlink("$attachdir/$icon");
			$icon = $uploaddb[0]['attachurl'];
		}
		$db->update("INSERT INTO pw_team(uid,username,name,cid,descrip,icon,notice,type,ifshow,gbooktype) VALUES('$admin_uid','$admin_name','$name','$cid','$descrip','$icon','$notice','$type','$ifshow','$gbooktype')");
		$teamid=$db->insert_id();
		$db->update("INSERT INTO pw_tuser(admin,uid,teamid,joindate,blogs,ifcheck) VALUES('$admin_uid','$admin_uid','$teamid','$timestamp','0','1')");
		$db->update("UPDATE pw_team SET bloggers=bloggers+1 WHERE teamid='$teamid' AND uid='$admin_uid'");
		$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$cid'");
		updatecache_cate($type);
		usermsg('operate_success',$basename);
	}
} elseif($job=='edit'){
	$rt=$db->get_one("SELECT name,cid,descrip,notice,icon,type,ifshow,gbooktype FROM pw_team WHERE teamid='$teamid' AND uid='$admin_uid'");
	if(!$step){
		!$rt && usermsg('undefine_action');
		$forumcache = forumoption('team',$rt['cid']);
		$name		= $rt['name'];
		$descrip	= $rt['descrip'];
		$notice		= $rt['notice'];
		$input = '';
		if (!$rt['icon']) {
			$rt['icon'] = 'nopic.jpg';
		} else {
			$input = '<input type="hidden" name="icon" value="'.$rt['icon'].'">';
		}
		$rt['type']==1 && $typechecked='checked';
		${'gbooktype_'.$rt['gbooktype']}='checked';
		if($rt['ifshow'] == 0){
			$ifshow_0='checked';
		}elseif($rt['ifshow'] == 1){
			$ifshow_1='checked';
		}elseif($rt['ifshow'] == 2){
			$ifshow_2='checked';
		}elseif($rt['ifshow'] == 3){
			$ifshow_3='checked';
		}else{
			$ifshow_0='checked';
		}

		require_once PrintEot('teamcp');footer();
	} elseif($_POST['step']==2){
		!$rt && usermsg('undefine_action');
		!$name && usermsg('teamcp_name');
		!$cid && usermsg('teamcp_cate');
		$name	 = Char_cv($name);
		$descrip = Char_cv($descrip);
		$notice  = Char_cv($notice);
		$gbooktype=(int)$gbooktype;
		$type=$type==1 ? 1 : 0;

		include_once(R_P.'mod/upload_mod.php');
		$attachdir = "$imgdir/upload";
		$icon = Char_cv($icon);
		$uploaddb = UploadFile('t'.$admin_uid,1);
		if ($uploaddb[0]['attachurl']) {
			$icon && P_unlink("$attachdir/$icon");
			$icon = $uploaddb[0]['attachurl'];
		}
		$db->update("UPDATE pw_team SET name='$name',cid='$cid',descrip='$descrip',icon='$icon',notice='$notice',type='$type',ifshow='$ifshow',gbooktype='$gbooktype' WHERE teamid='$teamid' AND uid='$admin_uid'");
		usermsg('operate_success',$basename);
	}
} elseif($job=='del'){
	if($_POST['step']==2){
		if(!$selid = checkselid($selid)){
			usermsg('operate_error');	
		}
		$delid='';
		$query=$db->query("SELECT teamid FROM pw_team WHERE teamid IN($selid) AND uid='$admin_uid'");
		while($rt=$db->fetch_array($query)){
			$delid.=$delid ? ",$rt[teamid]" : "$rt[teamid]";
		}
		empty($delid) && usermsg('undefine_action');
		$db->update("DELETE FROM pw_tblog WHERE teamid IN($delid)");
		$db->update("DELETE FROM pw_tuser WHERE teamid IN($delid)");
		$db->update("DELETE FROM pw_team WHERE teamid IN($delid)");
		$db->update("DELETE FROM pw_tgbook WHERE teamid IN($delid)");
	}
	usermsg('operate_success',$basename);
} elseif($job=='gbook'){
	require_once(R_P.'mod/page_mod.php');
	(int)$page < 1 && $page = 1;
	$rs=$db->get_one("SELECT uid FROM pw_team WHERE teamid='$teamid'");
	$rs['uid']!=$admin_uid && usermsg('undefine_action');
	if(empty($step)){
		$rs=$db->get_one("SELECT COUNT(*) AS sum FROM pw_tgbook WHERE teamid='$teamid'");
		$sum=$rs['sum'];
		$pages=page($sum,$page,$db_perpage,"{$basename}&teamid=$teamid&job=gbook&");
		
		$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$gbookdb=array();
		$query=$db->query("SELECT * FROM pw_tgbook WHERE teamid='$teamid' ORDER BY postdate DESC $limit");
		while($rt=$db->fetch_array($query)){
			empty($rt['name']) && $rt['name']='guest';
			$rt['content']=substrs($rt['content'],75);
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$gbookdb[]=$rt;
		}
		require_once PrintEot('teamcp');footer();
	}elseif($_POST['step']==2){
		if(!$selid = checkselid($selid)){
			usermsg('operate_error');	
		}
		$db->update("DELETE FROM pw_tgbook WHERE teamid='$teamid' AND id IN($selid)");
		usermsg('operate_success',$basename);
	}
}

function updatecache_cate($catetype = null){
	global $db;
	$where = !empty($catetype) ? "WHERE catetype='$catetype'" : '';
	$db->update("UPDATE pw_categories SET type='0',cupinfo='' $where".($where ? " AND" : "WHERE")." cup='0'");
	$catedb = $subdb = array();
	$typedb = array('blog','bookmark','file','goods','music','photo','team','user');
	$query = $db->query("SELECT cid,cup,type,name,descrip,cupinfo,counts,vieworder,_ifshow,catetype,fid FROM pw_categories $where ORDER BY vieworder,cid");
	while ($rt = $db->fetch_array($query)) {
		if (in_array($rt['catetype'],$typedb)) {
			P_unlink(D_P."data/cache/cate_cid_$rt[cid].php");
			!is_array(${'_'.$rt['catetype']}) && ${'_'.$rt['catetype']} = array();
			$rt['name']	   = Char_cv($rt['name'],'N');
			$rt['descrip'] = Char_cv($rt['descrip'],'N');
			if ($rt['cup'] == 0) {
				$catedb[] = $rt;
			} else {
				$subdb[$rt['cup']][] = $rt;
			}
		}
	}
	foreach ($catedb as $cate) {
		if (empty($cate)) continue;
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($subdb[$cate['cid']])) continue;
		${'_'.$cate['catetype']} += get_subcate($subdb,$cate['cid']);
	}
	foreach ($typedb as $value) {
		if (!empty($catetype) && $value != $catetype) {
			continue;
		}
		$writecache = '$_'.strtoupper($value).' = '.N_var_export(${'_'.$value}).";\r\n";
		writeover(D_P."data/cache/forum_cache_{$value}.php","<?php\r\n$writecache?>");
	}
}

function get_subcate($array,$cid){
	global $db;
	static $type = 0;
	static $cupinfo = null;
	$type++;
	$cupinfo .= (empty($cupinfo) ? '' : ',').$cid;
	foreach ($array[$cid] as $cate) {
		if (empty($cate)) continue;
		$sql = '';
		if ($cate['type'] != $type) {
			$cate['type'] = $type;
			$sql .= "type='$type'";
		}
		if ($cate['cupinfo'] != $cupinfo) {
			$cate['cupinfo'] = $cupinfo;
			$sql && $sql .= ',';
			$sql .= "cupinfo='$cupinfo'";
		}
		$sql && $db->update("UPDATE pw_categories SET $sql WHERE cid='$cate[cid]'");
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($array[$cate['cid']])) {
			$type = 0;
			$cupinfo = null;
			continue;
		}
		${'_'.$cate['catetype']} += get_subcate($array,$cate['cid']);
	}
	return ${'_'.$cate['catetype']};
}

function N_var_export($input,$f = 1,$t = null) {
	$output = '';
	if (is_array($input)) {
		$output .= "array(\r\n";
		foreach ($input as $key => $value) {
			$output .= $t."\t".N_var_export($key,$f,$t."\t").' => '.N_var_export($value,$f,$t."\t");
			$output .= ",\r\n";
		}
		$output .= $t.')';
	} elseif (is_string($input)) {
		$output .= $f ? "'".str_replace(array("\\","'"),array("\\\\","\'"),$input)."'" : "'$input'";
	} elseif (is_int($input) || is_double($input)) {
		$output .= "'".(string)$input."'";
	} elseif (is_bool($input)) {
		$output .= $input ? 'true' : 'false';
	} else {
		$output .= 'NULL';
	}
	return $output;
}
?>