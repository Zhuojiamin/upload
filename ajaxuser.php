<?php
error_reporting(E_ERROR | E_PARSE);

@ini_set('zend.ze1_compatibility_mode',false);
@ini_set('magic_quotes_runtime',false);

$t_array	= explode(' ',microtime());
$P_S_T  	= $t_array[0]+$t_array[1];
$timestamp  = time();
define('R_P',getdirname(__FILE__));
define('D_P',R_P);

!$_SERVER['PHP_SELF'] && $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
$admin_file = $_SERVER['PHP_SELF'];
define('AJAXUSER',true);

require_once(R_P.'user/global.php');
require_once(R_P.'mod/ajax_mod.php');
if ($action=='atcdir') {
	if ($job == 'add') {
		$type = GetGP('type','G');
		$dirorder = (int)$dirorder;
		$db->update("INSERT INTO pw_itemtype(uid,type,name,vieworder) VALUES('$uid','$type','$dirname','$dirorder')");
		$dirid = $db->insert_id();
		update_dirdb($uid);
		echo $dirid;exit;
	} elseif ($job == 'edit') {
		(!is_numeric($dirid) || !$dirname) && exit;
		$dirorder = (int)$dirorder;
		$db->update("UPDATE pw_itemtype SET name='$dirname',vieworder='$dirorder' WHERE typeid='$dirid'");
		update_dirdb($uid);
		echo $dirid;exit;
	} elseif ($job == 'delete') {
		!is_numeric($dirid) && exit;
		$db->update("DELETE FROM pw_itemtype WHERE typeid='$dirid'");
		update_dirdb($uid);
		echo $dirid;exit;
	}
} elseif ($action=='atctag') {
	if ($job == 'add') {
		$tagname = trim(Tag_cv($tagname));
		$cktagid = $db->get_value("SELECT tagid FROM pw_btags WHERE tagname='$tagname'");
		!$cktagid && $db->update("INSERT INTO pw_btags(tagname,uid) VALUES ('$tagname','$uid')");
		header("Content-Type: text/html; charset=utf-8");
		ShowResponseText($utf8_tagname);
	} elseif ($job == 'edit') {
		(int)$itemid<1 && exit;
		$type = GetGP('type','G');
		$tagdb = $db->get_one("SELECT im.tags,i.uid,i.author,i.subject,i.postdate FROM pw_{$type} im LEFT JOIN pw_items i ON im.itemid=i.itemid WHERE im.itemid='$itemid'");
		$tagdb['uid']!=$uid && exit;
		$tagname = trim(Tag_cv($tagname));
		$tagid   = $db->get_value("SELECT tagid FROM pw_btags WHERE tagname='$tagname'");
		$typenum = $type.'num';
		if (!$tagid) {
			$db->update("INSERT INTO pw_btags(tagname,uid,$typenum,allnum) VALUES ('$tagname','$uid',1,1)");
			$tagid = $db->insert_id();
		} else {
			$titagid = $db->get_value("SELECT tagid FROM pw_taginfo WHERE itemid='$itemid'");
			!$titagid && $db->update("UPDATE pw_btags SET $typenum=$typenum+1,allnum=allnum+1 WHERE tagid='$tagid'");
		}
		$taghash = substr(md5("{$db_hash}tags{$tagname}"),0,10);
		P_unlink(D_P."data/cache/tags_$taghash.php");
		$db->update("INSERT INTO pw_taginfo(tagid,tagname,uid,author,itemid,tagtype,subject,addtime) VALUES ('$tagid','$tagname','$uid','".addslashes($tagdb[author])."','$itemid','$type','".addslashes($tagdb[subject])."','$tagdb[postdate]')");
		if (strpos(",$tagdb[tags],",",$tagname,")===false) {
			$tagdb['tags'] .= ($tagdb['tags'] ? ',' : '').$tagname;
			$db->update("UPDATE pw_{$type} SET tags='$tagdb[tags]' WHERE itemid='$itemid'");
		}
		ShowResponseText($utf8_tagname);
	} elseif ($job == 'delete') {
		(int)$itemid<1 && exit;
		$type = GetGP('type','G');
		$tagdb = $db->get_value("SELECT tags FROM pw_{$type} WHERE itemid='$itemid'");
		$tagdb = explode(',',$tagdb);
		$typenum = $type.'num';
		$newtagdb = '';
		foreach ($tagdb as $value) {
			if ($value && $value != $tagname) {
				$newtagdb .= ($newtagdb ? ',' : '').$value;
			}
		}
		$db->update("UPDATE pw_{$type} SET tags='$newtagdb' WHERE itemid='$itemid'");
		$tagdb = $db->get_one("SELECT tagid,$typenum,allnum FROM pw_btags WHERE tagname='$tagname'");
		$tagdb[$typenum]<2 && $db->update("DELETE FROM pw_taginfo WHERE itemid='$itemid'");
		if ($tagdb['tagid'] && $tagdb[$typenum]>0 && $tagdb['allnum']>0) {
			$db->update("UPDATE pw_btags SET $typenum=$typenum-1,allnum=allnum-1 WHERE tagid='$tagdb[tagid]'");
		}
		$taghash = substr(md5("{$db_hash}tags{$tagname}"),0,10);
		P_unlink(D_P."data/cache/tags_$taghash.php");
		echo 'lxblog';exit;
	}
} elseif ($action=='upload') {
	if ($job == 'add') {
		InitGP(array('uid','mode'),'G');
		$db_uploadmaxsize = $_GET['db_uploadmaxsize'];
		$db_uploadfiletype = $_GET['db_uploadfiletype'];
		$db_attachnum = $_GET['db_attachnum'];
		require_once(R_P.'mod/upload_mod.php');
		$uploaddb = UploadSQL($uid,0,0,'',$mode);
		empty($uploaddb) && Uploadmsg('upload_error',$mode);
		foreach ($uploaddb as $value) {
			$aid = $value['aid'];
			$name = $value['name'];
			$size = $value['size'];
			$desc = $value['desc'];
			$url = "$attachpath/$value[attachurl]";
			break;
		}
		echo "<script language=\"JavaScript1.2\">parent.UploadFileResponse('$mode','$aid','$size','$desc','$name','$name','$url');</script>";exit;
	} elseif ($job == 'edit') {
		exit;
	} elseif ($job == 'delete') {
		InitGP(array('aid','uid','itemid'),'G');
		$rt = $db->get_one("SELECT attachurl,ifthumb FROM pw_upload WHERE aid='$aid'");
		if (!empty($rt)) {
			P_unlink("$attachdir/$rt[attachurl]");
			if ($rt['ifthumb'] == 1) {
				$ext  = substr(strrchr($rt['attachurl'],'.'),1);
				$name = substr($rt['attachurl'],0,strrpos($rt['attachurl'],'.'));
				P_unlink(R_P."$attachdir/{$name}_thumb.{$ext}");
			}
		}
		$db->update("DELETE FROM pw_upload WHERE aid='$aid'");
		$_FILES = $attachdb = array();
		require_once(R_P.'mod/upload_mod.php');
		UploadSQL($uid,$itemid,0,'blog');
		echo "<script language=\"JavaScript1.2\">parent.DeleteFileResponse('$aid');</script>";exit;
	}
} elseif ($action=='quickpost') {
	require_once(R_P.'mod/post_mod.php');
	require_once(R_P.'mod/windcode.php');
	require_once(R_P.'mod/ipfrom_mod.php');
	@include(D_P.'data/cache/wordfb.php');
	(!$subject || !$content || !$cid) && exit('operate_fail');
	$_GROUP['allowpost'] == '0' && exit('post_right');
	list(,,,$postgd) = explode("\t",$db_gdcheck);
	if ($admindb['items'] < $postgd) {
		$cknum = GetCookie('cknum');
		Cookie('cknum','',0);
		if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
			exit('gdcode_error');
		}
	}
	list(,,$postq) = explode("\t",$db_qcheck);
	if ($admindb['items'] < $postq && $postq != '0' && !empty($db_question)){
		$answer = unserialize($db_answer);
		($qanswer != $answer[$qkey]) && exit('qanswer_error');
	}
	
	list($limitnum) = explode(',',$_GROUP['limitnum']);
	list($postnum) = explode(',',$_GROUP['postnum']);
	$limitnum && ($timestamp - $admindb['lastpost'] < $limitnum) && exit("time_limit\t$limitnum");
	$postnum && $admindb['todaypost'] >= $postnum && exit("post_limit\t$postnum");
	list($db_titlemax,$db_postmin,$db_postmax) = explode(',',$db_lenlimit);
	list($atc_title,$atc_content) = ConCheck($subject,$content);
	$atc_content = Atc_cv($atc_content,0);
	$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
	!is_array($_REPLACE) && $_REPLACE = array();
	!is_array($_FORBID) && $_FORBID = array();
	foreach ($_FORBID as $value) {
		if (N_stripos($atc_title,$value['word']) || N_stripos($ckcontent,$value['word'])) {
			exit('word_ban');
		}
	}
	$_FORBIDDB = $_REPLACE+$_FORBID;
	$ifwordsfb = 0;
	$cktitle = $atc_title;
	$ckcontent = $atc_content;
	foreach ($_FORBIDDB as $value) {
		$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
		$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
	}
	if ($cktitle != $atc_title) {
		$atc_title = $cktitle;
		$ifwordsfb = 1;
	}
	if ($ckcontent != $atc_content) {
		$atc_content = $ckcontent;
		$ifwordsfb = 1;
	}
	$ifcheck = $db_postcheck ? 0 : 1;
	$ifhide = $ifhide ? 1 : 0;
	$ipfrom = cvipfrom($onlineip);
	$db->update("INSERT INTO pw_items(cid,uid,author,type,subject,postdate,allowreply,ifcheck,ifwordsfb,ifhide) VALUES ('$cid','$admin_uid','".addslashes($admin_name)."','blog','$atc_title','$timestamp','1','$ifcheck','$ifwordsfb','$ifhide')");
	$itemid = $db->insert_id();
	$db->update("INSERT INTO pw_blog(itemid,userip,ifsign,ipfrom,ifconvert,content) VALUES('$itemid','$onlineip','0','$ipfrom','$ifconvert','$atc_content')");
	$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$cid'");
	$userdb = $ifcheck ? array('uid' => $admin_uid,'type' => 'blog','items' => $admindb['items'],'todaypost' => $admindb['todaypost'],'lastpost' => $admindb['lastpost']) : array();
	update_post($userdb);
	updatecache_cate('blog');
	update_bloginfo_cache('blogs');
} elseif($action=='extend'){
	InitGP(array('type'));
	if($type=='pwcode'){
		$code  = array();
		$query = $db->query("SELECT * FROM pw_windcode");
		while($rt = $db->fetch_array($query)){
			$rt['descrip'] = str_replace("\n","|",$rt['descrip']);
			$code[] = $rt;
		}
	} else{
		@include_once(D_P.'data/cache/setform.php');//writeover(R_P.'test.txt',$formid);
		//$formid = (int)InitGP(array('formid'));
		if($type == 'setform'){
			$setform = array();
			if(isset($setformdb[$formid])){
				$setform = $setformdb[$formid];
			}
		}
		//$setform = implode(',',$id);
		
	}
	require_once PrintEot('setform');ajax_footer();
} elseif($action=='itemcp'){
	if($job == 'del'){
		(int)$pid<1 || (int)$aid<1 && exit;
		$tags = $db->get_value("SELECT tags FROM pw_photo WHERE pid='$pid'");
		if(!empty($tags)){
			$db->update("UPDATE pw_btags SET photonum=photonum-1,allnum=allnum-1 WHERE tagname='$tags'");
			$db->update("DELETE FROM pw_taginfo WHERE itemid='$pid'");
		}
		
		$photo = $db->get_one("SELECT uid,attachurl,ifthumb FROM pw_photo WHERE pid='$pid'");
		$cmtcount = (int)$db->get_value("SELECT COUNT(*) FROM pw_comment WHERE ifcheck='1' AND uid='$photo[uid]' AND itemid='$pid' AND type='photo'");
		$fptcount = (int)$db->get_value("SELECT pfootprints FROM pw_photo WHERE pid='$pid'");
		P_unlink("$attachdir/$photo[attachurl]");
		if ($photo['ifthumb'] == 1) {
			$ext  = substr(strrchr($photo['attachurl'],'.'),1);
			$name = substr($photo['attachurl'],0,strrpos($photo['attachurl'],'.'));
			P_unlink("$attachdir/{$name}_thumb.{$ext}");
		}
		$db->update("DELETE FROM pw_photo WHERE pid='$pid'");
		$db->update("DELETE FROM pw_footprint WHERE itemid = '$pid'");
		$db->update("UPDATE pw_albums SET photos=photos-1,footprints=footprints-$fptcount,replies=replies-$cmtcount WHERE aid='$aid'");
		$db->update("UPDATE pw_user SET photos=photos-1,comments=comments-$cmtcount WHERE uid='$photo[uid]'");
		echo "$pid";exit;
	}elseif($job == 'headpage'){
		(int)$pid<1 && (int)$aid<1 && exit;
		$ckhpageid = $db->get_value("SELECT hpagepid FROM pw_albums where aid='$aid'");
		$db->get_value("SELECT ifhpage FROM pw_photo WHERE pid='$pid'") && !empty($ckhpageid) && exit('done');
		$pre_pid = $db->get_value("SELECT pid FROM pw_photo WHERE ifhpage='1'");
		$db->update("UPDATE pw_albums SET hpagepid='$pid' WHERE aid='$aid'");
		$db->update("UPDATE pw_photo SET ifhpage='0' WHERE ifhpage='1'");
		$db->update("UPDATE pw_photo SET ifhpage='1' WHERE pid='$pid'");
		$query = $db->query("SELECT a.aid,a.subject,a.descrip,p.attachurl AS hpageurl,p.ifthumb FROM pw_albums a LEFT JOIN pw_photo p ON a.hpagepid=p.pid WHERE a.uid='$admin_uid' ORDER BY a.postdate DESC");
		while ($rt = $db->fetch_array($query)) {
			empty($rt['hpageurl']) && $rt['hpageurl'] = 'nopic.jpg';
			$cachedb[$rt['aid']] = $rt;
		}
		$cachedb = serialize($cachedb);
		$db->update("UPDATE pw_userinfo SET albumdb='$cachedb' WHERE uid='$admin_uid'");
		$hpageurl = $db->get_value("SELECT attachurl AS hpageurl FROM pw_photo WHERE pid='$pid'");
		if (file_exists(R_P."$attachpath/$hpageurl")) {
			$hpageurl = "$attachpath/$hpageurl";
		} elseif (file_exists($attach_url/$hpageurl)) {
			$hpageurl = "$attach_url/$hpageurl";
		} else {
			$hpageurl = "$attachpath/none.gif";
		}
		$return = "$pre_pid\t$pid\t$hpageurl";
		echo "$return";exit;
	}elseif($job == 'delmalbums'){
		(int)$cid<1 || (int)$maid<1 && exit;
		(int)$musics = 0;
		$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$cid' AND type='music'");
		$query = $db->query("SELECT mid FROM pw_music WHERE maid='$maid'");
		while($rt = $db->fetch_array($query)){
			$mids .= ($mids ? ',' : '')."'$rt[mid]'";
			$musics++;
		}
		if($mids){
			$mids = strpos($mids,',')===false ? "=$mids" : " IN ($mids)";
			$query = $db->query("SELECT tagid,COUNT(*) AS tagnum FROM pw_taginfo WHERE itemid{$mids} AND tagtype='music' GROUP BY tagid");
			while ($rt = $db->fetch_array($query)) {
				if ($rt['tagid']) {
					$db->update("UPDATE pw_btags SET allnum=allnum-$rt[tagnum],musicnum=musicnum-$rt[tagnum] WHERE tagid='$rt[tagid]'");
				}
			}
		}
		$query = $db->query("SELECT uid FROM pw_comment WHERE itemid='$maid'");
		while ($rt = $db->fetch_array($query)) {
			$userdb[$rt['uid']]['comments']++;
		}
		empty($userdb) && $userdb = array();
		foreach($userdb as $key => $value){
			if ((int)$value['comments'] > 0) {
				$updatesql .= ($updatesql ? ',' : '')."comments=comments-'$value[comments]'";
			}
			$updatesql && $db->update("UPDATE pw_user SET $updatesql WHERE uid='$key'");
		}
		$db->update("UPDATE pw_user SET malbums=malbums-1,musics=musics-$musics WHERE uid='$admin_uid'");
		$db->update("DELETE FROM pw_comment WHERE itemid='$maid' AND type='music'");
		$db->update("DELETE FROM pw_malbums WHERE maid='$maid'");
		$db->update("DELETE FROM pw_music WHERE maid='$maid'");
		$db->update("DELETE FROM pw_taginfo WHERE itemid{$mids}");
		update_malbumdb($admin_uid);
		update_bloginfo_cache('malbums');
		echo "$maid";exit;
	}
} elseif($action=='equatemsg'){
	require_once(R_P.'mod/passport.php');
	$maxbbsmid = explode(',',$admindb['maxbbsmid']);
	$maxbbsmid = $type == 'rebox' ? $maxbbsmid[0] : $maxbbsmid[1];
	$bbsmsgs = GetBbsMsg($maxbbsmid,$type,$state);
	!is_array($bbsmsgs) && $bbsmsgs = array();
	foreach($bbsmsgs as $key => $value){
		$db->update("INSERT INTO pw_msgs (touid,fromuid,username,type,bbsmid,ifnew,title,mdate,content) VALUES ($value[touid],$value[fromuid],'$value[username]','$value[type]','$value[mid]',$value[ifnew],'$value[title]',$value[mdate],'$value[content]')");
		 ($maxbbsmid < $value['mid']) && $maxbbsmid = $value['mid'];
	}
	$bbsmids = $db->get_value("SELECT maxbbsmid FROM pw_userinfo WHERE uid='$admin_uid'");
	$bbsmids = explode(',',$bbsmids);
	if($type == 'rebox'){
		if(!empty($bbsmids)){
			$bbsmids[0] = $maxbbsmid;
		}else{
			$bbsmids[0] = $maxbbsmid;
			$bbsmids[1] = '';
		}
	}elseif($type == 'sebox'){
		if(!empty($bbsmids)){
			$bbsmids[1] = $maxbbsmid;
		}else{
			$bbsmids[0] = '';
			$bbsmids[1] = $maxbbsmid;
		}
	}
	
	$bbsmids && $maxbbsmid = implode(',',$bbsmids);
	
	$db->update("UPDATE pw_userinfo SET maxbbsmid='$maxbbsmid' WHERE uid='$admin_uid'");
	echo $type;exit;
}elseif($action=='mhits') {
	$count = $db->get_value("SELECT mid FROM pw_music WHERE mid='$mid'");
	!$count && exit('illegal_tid');
	$db->update("UPDATE pw_music SET mhits=mhits+1 WHERE mid='$mid'");
	$db->update("UPDATE pw_malbums SET hits=hits+1 WHERE maid='$maid'");
	exit("success\t$mid");
}elseif($action == 'addfriendgroupsubmit') {
	!$admin_uid && exit('not_login');
	!$gname && exit('empty_gname');
	$db->update("INSERT INTO pw_blogfriendg(uid,gname) VALUES ('$admin_uid','$gname')");
	if($db_charset!='utf-8'){
		$gname = ajax_convert($gname,'utf-8',$db_charset);
	}
	exit("success\t$gname");
}elseif($action == 'editfriendgroupsubmit'){
	!$admin_uid && exit('not_login');
	!$gname && exit('empty_gname');
	$gnameck = $db->get_value("SELECT gname FROM pw_blogfriendg WHERE gid='$gid'");
	!$gnameck && exit('illegal_gid');
	$db->update("UPDATE pw_blogfriendg SET gname='$gname' WHERE gid='$gid'");
	if($db_charset!='utf-8'){
		$gname = ajax_convert($gname,'utf-8',$db_charset);
	}
	exit("success\t$gid\t$gname");
}elseif($action == 'delfriendgroupsubmit'){
	!$admin_uid && exit('not_login');
	$gnameck = $db->get_value("SELECT gname FROM pw_blogfriendg WHERE gid='$gid'");
	!$gnameck && exit('illegal_gid');
	$db->update("UPDATE pw_blogfriend SET gid='0' WHERE gid='$gid'");
	$db->update("DELETE FROM pw_blogfriendg WHERE gid='$gid'");
	exit("success\t$gid");
}
if (!function_exists('P_unlink')) {
	function P_unlink($filename){
		strpos($filename,'..')!==false && exit('Forbidden');
		@unlink($filename);
	}
}
function getdirname($path){
	if (strpos($path,'\\')!==false) {
		return substr($path,0,strrpos($path,'\\')).'/';
	} elseif (strpos($path,'/')!==false) {
		return substr($path,0,strrpos($path,'/')).'/';
	} else {
		return './';
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
function update_post($userdb){
	global $db,$db_credit,$timestamp,$tdtime;
	if (!empty($userdb)) {
		$memberid = getmemberid($userdb['items']);
		$typenum = $userdb['type'].'s';
		if ($userdb['lastpost'] < $tdtime) {
			$userdb['todaypost'] = 1;
		} else {
			$userdb['todaypost']++;
		}
		list($rvrc,$money) = explode(',',$db_credit);
		$rvrc = floor($rvrc/10);
		$db->update("UPDATE pw_user SET memberid='$memberid', $typenum=$typenum+1,items=items+1,todaypost='$userdb[todaypost]',lastpost='$timestamp',rvrc=rvrc+'$rvrc',money=money+'$money' WHERE uid='$userdb[uid]'");
	}
}
function getmemberid($nums){
	global $_gmember;
	$gid = 0;
	foreach ($_gmember as $key => $value) {
		(int)$nums>=$value['creditneed'] && $gid = $key;
	}
	return $gid;
}

function ajax_footer(){
	global $db_charset,$db_obstart;
	$output = ob_get_contents();
	$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
	if($db_charset!='utf-8'){
		$output = ajax_convert($output,'utf-8',$db_charset);
	}
	ob_end_clean();
	$db_obstart == 1 && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	echo $output;exit;
}

function ajax_convert($str,$to_encoding,$from_encoding='utf-8'){
	if(function_exists('mb_convert_encoding')){
		return mb_convert_encoding($str,$to_encoding,$from_encoding);
	} else{
		require_once(R_P.'wap/chinese.php');
		$chs = new Chinese($from_encoding,$to_encoding);
		return $chs->Convert($str);
	}
}

function update_malbumdb($uid){
	global $db;
	$cachedb = array();
	(int)$malbums = 0;
	$query = $db->query("SELECT maid,subject,descrip,ifcheck,allowreply FROM pw_malbums WHERE uid='$uid' ORDER BY postdate DESC");
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