<?php
!function_exists('usermsg') && exit('Forbidden');
InitGP(array('job','maid'));
empty($job) && usermsg('undefined_action');
$basename .= '&type='.$type;
include_once(D_P."data/cache/forum_cache_music.php");
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
	$hpagesrc = $imgpath.'/nopic.jpg';
	
	$uploadface = 'http';
	if (!$db_allowupload) {
		$facedisabled = 'disabled';
	} else {
		$httpstyle = 'none';
		$uploadstyle = '';
	}
	
	if ($uploadface == 'http') {
		$httpstyle = '';
		$uploadstyle = 'none';
	} else {
		$httpstyle = 'none';
		$uploadstyle = '';
	}
	$teamsel  = getteamop($admin_uid);
	include PrintEot('addmalbum');footer();
}else{
	require_once(R_P.'mod/windcode.php');
	$spostnum = $postnum = '';
	list($postnum) = explode(',',$_GROUP['postnum']);
	list($limitnum) = explode(',',$_GROUP['limitnum']);
	$limitnum && ($timestamp - $admindb['lastpost'] < $limitnum) && usermsg('time_limit');
	$postnum && $admindb['todaypost'] >= $postnum && usermsg('post_limit');
	InitGP(array('ab_cid','ab_allowreply','atc_teamid','attachment_1','attachment_2'),'P');
	list($ab_title,$atc_content) = ConCheck($_POST['ab_title'],$_POST['atc_content']);
	$ifwordsfb = 0;
	$cktitle = $ab_title;
	$ckdesc = $atc_content;
	foreach ($_FORBIDDB as $value) {
		$cktitle = N_strireplace($value['word'],$value['wordreplace'],$ab_title);
		$ckdesc = N_strireplace($value['word'],$value['wordreplace'],$atc_content);
	};
	if ($cktitle != $ab_title) {
		$ab_title = $cktitle;
		$ifwordsfb = 1;
	}
	if ($ckdesc != $atc_content) {
		$atc_content = $ckdesc;
		$ifwordsfb = 1;
	}
	$ifcheck = $db_postcheck ? 0 : 1;
	if ($ckulface != 'upload') {
			$attachment_1 && !preg_match('/^http(s)?:\/\//i',$attachment_1) && usermsg('face_fail');
			$hpageurl = $attachment_1;
	} else {
		if (!empty($_FILES['attachment_2']['tmp_name'])) {
			require_once(R_P.'mod/upload_mod.php');
			$hpageurl && !preg_match('/^http(s)?:\/\//i',$hpageurl) && P_unlink("$attachdir/$hpageurl");
			$_GROUP['attachsize'] && $db_uploadmaxsize = $_GROUP['attachsize'];
			$_GROUP['uploadnum'] && $db_attachnum = $_GROUP['uploadnum'];
			list($_GROUP['upfacew'],$_GROUP['upfaceh']) = explode(',',$_GROUP['upfacewh']);
			$db_uploadfiletype = 'gif jpg jepg png';
			$uploaddb = UploadFile($admin_uid,2);
			$hpageurl = $uploaddb[0]['attachurl'];
			$hpageurldb = GetImgSize("$attachdir/$hpageurl");
			if ($hpageurldb['width'] > $_GROUP['upfacew'] || $hpageurldb['height'] > $_GROUP['upfaceh']) {
				P_unlink("$attachdir/$hpageurl");
				if ($uploaddb[0]['ifthumb'] == 1) {
					$ext  = substr(strrchr($hpageurl,'.'),1);
					$name = substr($hpageurl,0,strrpos($hpageurl,'.'));
					P_unlink("$attachdir/{$name}_thumb.{$ext}");
				}
				usermsg('malbumss_size_limit');
			}
		}
	}
	$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
	$db->update("INSERT INTO pw_malbums(uid,author,cid,subject,hpageurl,postdate,lastpost,musics,allowreply,ifcheck,ifconvert,descrip) VALUES ('$admin_uid','$admin_name','$ab_cid','$ab_title','$hpageurl','$timestamp','$timestamp','0','$ab_allowreply','$ifcheck','$ifconvert','$atc_content')");
	$maid = $db->insert_id();
	$ifcheck && $db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$ab_cid'");
	updatecache_cate($type);
	update_malbumdb($admin_uid);
	$atc_teamid > 0 && pushitem($maid,$ab_title,$atc_teamid,$type);
	update_bloginfo_cache('malbums');
	usermsg('operate_success',"$user_file?action=post&type=music");
}
}elseif($job == 'modify'){
	if($_POST['step'] != 2){
		$malbumdb = $db->get_one("SELECT uid,cid,subject,allowreply,hpageurl,descrip,pushlog FROM pw_malbums WHERE maid='$maid'");
		(!$malbumdb || $malbumdb['uid']!=$admin_uid) && usermsg('modify_error');
		$ab_title = $malbumdb['subject'];
		$atc_content  = $malbumdb['descrip'];
		$hpageurl = $post_hpageurl = $malbumdb['hpageurl'];
		$hpagesrc = showhpageurl($malbumdb['hpageurl']);
		$pushlog = $malbumdb['pushlog'];
		if (!preg_match('/^http(s)?:\/\//i',$hpageurl)) {
			$uploadface = 'upload';
			$hpageurl = '';
		}else{
			$uploadface = 'http';
		}
		
		if (!$db_allowupload) {
			$facedisabled = 'disabled';
		} else {
			$httpstyle = 'none';
			$uploadstyle = '';
		}
		
		if ($uploadface == 'http') {
			$httpstyle = '';
			$uploadstyle = 'none';
		} else {
			$httpstyle = 'none';
			$uploadstyle = '';
		}
		foreach ($catedb as $value) {
			$add = '';
			for ($i=0;$i<$value['type'];$i++) {
				$add .= '>';
			}
			$slted = ($malbumdb['cid']==$value['cid']) ? ' SELECTED' : '';
			$forumcache .= "<option value=\"$value[cid]\"$slted>$add $value[name]</option>";
		}
		
		${'allowreply_'.(int)$malbumdb['allowreply']} = ' CHECKED';
		$teamsel  = getteamop($admin_uid);
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
		include PrintEot('addmalbum');footer();
	}else{
		require_once(R_P.'mod/windcode.php');
		InitGP(array('maid','ab_title','post_hpageurl','atc_content','ab_cid','ab_allowreply','attachment_1','ckulface','atc_teamid','pushlog'));
		$hpageurl = $post_hpageurl;
		if ($ckulface != 'upload') {
			$attachment_1 && !preg_match('/^http(s)?:\/\//i',$attachment_1) && usermsg('face_fail');
			$hpageurl = $attachment_1;
		}elseif (!empty($_FILES['attachment_2']['tmp_name'])) {
			require_once(R_P.'mod/upload_mod.php');
			$hpageurl && !preg_match('/^http(s)?:\/\//i',$hpageurl) && P_unlink("$attachdir/$hpageurl");
			$_GROUP['attachsize'] && $db_uploadmaxsize = $_GROUP['attachsize'];
			$_GROUP['uploadnum'] && $db_attachnum = $_GROUP['uploadnum'];
			$db_uploadfiletype = 'gif jpg jepg png';
			$uploaddb = UploadFile($admin_uid,2);
			$hpageurl = $uploaddb[0]['attachurl'];
			$hpageurldb = GetImgSize("$attachdir/$hpageurl");
			if ($hpageurldb['width'] > 100 || $hpageurldb['height'] > 100) {
				P_unlink("$attachdir/$hpageurl");
				if ($uploaddb[0]['ifthumb'] == 1) {
					$ext  = substr(strrchr($hpageurl,'.'),1);
					$name = substr($hpageurl,0,strrpos($hpageurl,'.'));
					P_unlink("$attachdir/{$name}_thumb.{$ext}");
				}
				usermsg('pro_size_limit');
			}
		}
		list($ab_title,$atc_content) = ConCheck($ab_title,$atc_content);
		$ifwordsfb = 0;
		$cktitle = $ab_title;
		$ckdesc = $atc_content;
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
		$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
		$db->update("UPDATE pw_malbums SET cid='$ab_cid',subject='$ab_title',lastpost='$timestamp',allowreply='$ab_allowreply',hpageurl='$hpageurl',ifconvert='$ifconvert',descrip='$atc_content' WHERE maid='$maid'");
		if($db_teamifopen == '1'){
			!is_array($atc_teamid) && $atc_teamid = array();
			foreach ($atc_teamid as $value) {
				if(teamcheck($maid,$value,$type)){
					continue;
				}
				$ckteamid[] = $value;
			}
		}
		$ckteamid > 0 && pushitem($maid,$ab_title,$ckteamid,$type,$pushlog);
		usermsg('operate_success',"$user_file?action=addmalbum&type=music&job=modify&maid=$maid");
	}
}

function update_malbumdb($uid){
	global $db;
	$cachedb = array();
	(int)$malbums = 0;
	$query = $db->query("SELECT maid,subject,descrip,ifcheck,allowreply FROM pw_malbums WHERE uid='$uid' AND ifcheck='1' ORDER BY postdate DESC");
	while ($rt = $db->fetch_array($query)) {
		empty($rt[hpageurl]) && $rt[hpageurl] = 'nopic.jpg';
		$cachedb[$rt['maid']] = $rt;
		$malbums++;
	}
	if (!empty($cachedb)) {
		Strip_S($cachedb);
		$cachedb = addslashes(serialize($cachedb));
	}
	$itemdb = $db->get_one("SELECT blogs,albums,bookmarks FROM pw_user WHERE uid='$uid'");
	$items = (int)$itemdb['blogs'] + (int)$itemdb['albums'] + (int)$itemdb['bookmarks'] + $malbums;
	$db->update("UPDATE pw_userinfo SET malbumdb='$cachedb' WHERE uid='$uid'");
	$db->update("UPDATE pw_user SET malbums='$malbums',items='$items' WHERE uid='$uid'");
}

function showhpageurl($hpageurl){
	global $attachpath,$imgpath;
	if (!$hpageurl) {
		return $imgpath.'/nopic.jpg';
	} elseif (preg_match('/^http/i',$hpageurl)) {
		return $hpageurl;
	} else {
		return $attachpath.'/'.$hpageurl;
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
?>