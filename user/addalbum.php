<?php
!function_exists('usermsg') && exit('Forbidden');
InitGP(array('job','aid'));
$basename .= '&type='.$type;
include_once(D_P."data/cache/forum_cache_photo.php");
require_once(R_P.'mod/post_mod.php');
list($db_titlemax,$db_postmin,$db_postmax) = explode(',',$db_lenlimit);
$catedb = (array)${strtoupper('_'.$type)};

@include(D_P.'data/cache/wordfb.php');
!is_array($_REPLACE) && $_REPLACE = array();
!is_array($_FORBID) && $_FORBID = array();
foreach ($_FORBID as $value) {
	(N_stripos($_POST['atc_title'],$value['word']) || N_stripos($_POST['atc_content'],$value['word'])) && usermsg('word_ban');
}
$_FORBIDDB = $_REPLACE+$_FORBID;
if($job == 'add'){
	if ($_POST['step']!=2) {
		$allowreply_1 = 'CHECKED';
		$forumcache = '';
		foreach ($catedb as $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$forumcache .= "<option value=\"$value[cid]\">$add $value[name]</option>";
		}
		$teamsel  = getteamop($admin_uid);
		include PrintEot('addalbum');footer();
	}else{
		require_once(R_P.'mod/windcode.php');
		$spostnum = $postnum = '';
		list($postnum) = explode(',',$_GROUP['postnum']);
		list($limitnum) = explode(',',$_GROUP['limitnum']);
		$limitnum && ($timestamp - $admindb['lastpost'] < $limitnum) && usermsg('time_limit');
		$postnum && $admindb['todaypost'] >= $postnum && usermsg('post_limit');
		InitGP(array('gdcode','ab_cid','ab_allowreply','ab_ifhide','password','ckpassword','atc_teamid','qanswer','qkey'),'P');
		list($ab_title,$atc_content) = ConCheck($_POST['ab_title'],$_POST['atc_content']);
		$ifwordsfb = 0;
		$cktitle = $ab_title;
		$ckcontent = $atc_content;
		foreach ($_FORBIDDB as $value) {
			$cktitle = N_strireplace($value['word'],$value['wordreplace'],$ab_title);
			$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$atc_content);
		}
		if ($cktitle != $ab_title) {
			$ab_title = $cktitle;
			$ifwordsfb = 1;
		}
		if ($ckcontent != $atc_content) {
			$atc_content = $ckdesc;
			$ifwordsfb = 1;
		}
		$ifcheck = $db_postcheck ? 0 : 1;
		
		//password
		if($password != ''){
			(!$password || strlen($password) < 6) && usermsg('passport_limit');
			$ckpassword != $password && Showmsg('password_confirm');
			$password = md5($password);
		}
		$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
		
		$db->update("INSERT INTO pw_albums(uid,author,password,cid,subject,postdate,lastpost,allowreply,ifcheck,ifhide,ifconvert,descrip) VALUES ('$admin_uid','$admin_name','$password','$ab_cid','$ab_title','$timestamp','$timestamp','$ab_allowreply','$ifcheck','$ab_ifhide','$ifconvert','$atc_content')");
		$aid = $db->insert_id();
		$ifcheck && $db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$ab_cid'");
		updatecache_cate('photo');
		update_albumdb($admin_uid);
		$atc_teamid > 0 && pushitem($aid,$ab_title,$atc_teamid,$type);
		update_bloginfo_cache('albums');
		if($ifcheck == '0'){
			usermsg('operate_success_not_checked',"$user_file?action=post&type=photo&");
		}else{
			usermsg('operate_success',"$user_file?action=post&type=photo&");
		}
	}
}elseif($job == 'modify'){
	if($_POST['step'] != 2){
		$albumdb = $db->get_one("SELECT uid,cid,subject,allowreply,ifhide,descrip,pushlog FROM pw_albums WHERE aid='$aid'");
		(!$albumdb || $albumdb['uid']!=$admin_uid) && usermsg('modify_error');
		$ab_title = $albumdb['subject'];
		$atc_content  = $albumdb['descrip'];
		$pushlog =  $albumdb['pushlog'];
		foreach ($catedb as $value) {
			$add = '';
			for ($i=0;$i<$value['type'];$i++) {
				$add .= '>';
			}
			$slted = ($albumdb['cid']==$value['cid']) ? ' SELECTED' : '';
			$forumcache .= "<option value=\"$value[cid]\"$slted>$add $value[name]</option>";
		}
		${'allowreply_'.(int)$albumdb['allowreply']} = ' CHECKED';
		${'ifhide_'.(int)$albumdb['ifhide']} = ' SELECTED';
		$teamsel  = getteamop($admin_uid);
		if($db_teamifopen == '1'){
			$apushlog = explode("\t",$pushlog);
			foreach($apushlog as $pkey => $pvalue){
				list($pteamid) = explode(',',$pvalue);
				$pteamiddb .= ','.$pteamid;
			}
			foreach($teamsel as $tkey => $tvalue){
				if(strpos($pteamiddb,$tvalue[teamid])){
					$teamsel[$tkey][checked] = 'CHECKED';
				}
			}
		}
		include PrintEot('addalbum');footer();
	}else{
		require_once(R_P.'mod/windcode.php');
		InitGP(array('gdcode','ab_cid','ab_allowreply','ab_ifhide','password','ckpassword','atc_teamid','qanswer','qkey','pushlog'),'P');
		list($ab_title,$atc_content) = ConCheck($_POST['ab_title'],$_POST['atc_content']);
		$ifwordsfb = 0;
		$cktitle = $ab_title;
		$ckcontent = $atc_content;
		foreach ($_FORBIDDB as $value) {
			$cktitle = N_strireplace($value['word'],$value['wordreplace'],$ab_title);
			$ckdesc = N_strireplace($value['word'],$value['wordreplace'],$atc_content);
		}
		if ($cktitle != $ab_title) {
			$ab_title = $cktitle;
			$ifwordsfb = 1;
		}
		if ($ckdesc != $atc_content) {
			$atc_content = $ckdesc;
			$ifwordsfb = 1;
		}
		$ifcheck = $db_postcheck ? 0 : 1;
		
		//password
		if($password != ''){
			(!$password || strlen($password) < 6) && usermsg('passport_limit');
			$ckpassword != $password && Showmsg('password_confirm');
			$password = md5($password);
		}
		$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
		$db->update("UPDATE pw_albums SET cid='$ab_cid',subject='$ab_title',lastpost='$timestamp',allowreply='$ab_allowreply',descrip='$atc_content',ifconvert='$ifconvert',ifhide='$ab_ifhide',password='$password' WHERE aid='$aid'");
		if($db_teamifopen == '1'){
			!is_array($atc_teamid) && $atc_teamid = array();
			foreach ($atc_teamid as $value) {
				if(teamcheck($aid,$value,$type)){
					continue;
				}
				$ckteamid[] = $value;
			}
			$ckteamid > 0 && pushitem($aid,$ab_title,$ckteamid,$type,$pushlog);
		}
		
		updatecache_cate('photo');
		update_albumdb($admin_uid);
		usermsg('operate_success',"$user_file?action=post&type=photo&");
	}
}

function update_albumdb($uid){
	global $db;
	$cachedb = array();
	(int)$albums = 0;
	$query = $db->query("SELECT a.aid,a.subject,a.descrip,a.ifhide,p.ifthumb,p.attachurl AS hpageurl FROM pw_albums a LEFT JOIN pw_photo p ON a.hpagepid=p.pid WHERE a.uid='$uid' AND ifcheck='1' ORDER BY postdate DESC");
	while ($rt = $db->fetch_array($query)) {
		empty($rt[hpageurl]) && $rt[hpageurl] = 'nopic.jpg';
		$cachedb[$rt['aid']] = $rt;
		$albums++;
	}
	if (!empty($cachedb)) {
		Strip_S($cachedb);
		$cachedb = addslashes(serialize($cachedb));
	}
	$itemdb = $db->get_one("SELECT blogs,malbums,bookmarks FROM pw_user WHERE uid='$uid'");
	$items = (int)$itemdb['blogs'] + (int)$itemdb['malbums'] + (int)$itemdb['bookmarks'] + $albums;
	$db->update("UPDATE pw_userinfo SET albumdb='$cachedb' WHERE uid='$uid'");
	$db->update("UPDATE pw_user SET albums='$albums',items='$items' WHERE uid='$uid'");
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
function update_bloginfo_cache($type,$username=null,$uid=null){
	global $db,$tdtime;
	if($type == 'blogs'){
		$tdtcontrol = $db->get_value("SELECT tdtcontrol FROM pw_bloginfo WHERE id='1'");
		if($tdtcontrol != $tdtime){
			$tdtcontrol = $tdtime;
			$tdblogs = 0;
			$db->update("UPDATE pw_bloginfo SET tdblogs='0',tdtcontrol='$tdtime'");
		}
		$totalblogs = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE ifcheck='1'");
		$tdblogs = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE postdate>'$tdtime' AND ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalblogs='$totalblogs',tdblogs='$tdblogs' WHERE id='1'");
	}elseif($type == 'albums'){
		$totalalbums = $db->get_value("SELECT COUNT(*) FROM pw_albums WHERE ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalalbums='$totalalbums' WHERE id='1'");
	}elseif($type == 'malbums'){
		$totalmalbums = $db->get_value("SELECT COUNT(*) FROM pw_malbums WHERE ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalmalbums='$totalmalbums' WHERE id='1'");
	}elseif($type == 'users'){
		$totalmember = $db->get_value("SELECT COUNT(*) FROM pw_user");
		$newmember = $uid.','.$username;
		$db->update("UPDATE pw_bloginfo SET newmember='$newmember',totalmember=$totalmember WHERE id='1'");
	}
	$bloginfodb = "<?php\r\n";
	$bloginfo = $db->get_one("SELECT newmember,totalmember,totalblogs,totalalbums,totalmalbums,tdblogs FROM pw_bloginfo WHERE id='1'");
	foreach($bloginfo as $key => $value){
		$bloginfodb .= "\$$key='$value';\r\n";
	}
	$bloginfodb .= "?>";
	writeover(D_P.'data/cache/bloginfo.php',$bloginfodb);
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
?>