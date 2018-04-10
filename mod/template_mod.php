<?php
!function_exists('readover') && exit('Forbidden');

function mod_index(){
	global $db,$timestamp,$db_showcate,$db_cateshow;
	if ($timestamp - @filemtime(D_P.'data/cache/mod_cache.php') > 1*60) {
		@include(D_P.'data/cache/mod_cache.php');
		$ckarray = array('blog' => 'NEWBLOGS','photo' => 'NEWPHOTOS','music' => 'NEWMUSICS','bookmark' => 'NEWBOOKMARKS');
		$unshowcate = '';
		foreach ($ckarray as $key => $value) {
			if (strpos("\t$db_showcate\t","\t$key\t")===false) {
				if (strpos("\t$db_cateshow\t","\t$key\t")===false) {
					$unshowcate .= ($unshowcate ? "\t" : '').$key;
					$db->update("UPDATE pw_module SET ifshow='0' WHERE funcname='$value'");
				}
				${'_'.$ckarray[$key]} = array();
			} else {
				$unshowcate = str_replace($key,'',$db_unshowcate);
				$db->update("UPDATE pw_module SET ifshow='1' WHERE funcname='$value'");
			}
		}
		if (isset($unshowcate)) {
			$db->get_value("SELECT db_name FROM pw_setting WHERE db_name='db_cateshow'") ? $db->update("UPDATE pw_setting SET 
db_value='$unshowcate' WHERE db_name='db_cateshow'") : $db->update("INSERT INTO pw_setting(db_name,db_value) VALUES ('db_cateshow','$unshowcate')");
			updatecache_db();
		}
		$array = array_merge(array
('IMGPLAYER','ALLTAGS','HOTHIT','CLASSATCS','USERFPRINTS','HOTHITS','HOTREPLIES','NEWREPLIES','NEWUSERS','ATCUSERS','CMDUSERS','TEAMCLASS','TEAMATCS','USERSKIN','RANDOMLIST'),$ckarray);
		$gohit = array(); $shownum = 0;
		$query = $db->query("SELECT id,funcname,limitnum,shownum FROM pw_module WHERE uid='0' AND ($timestamp-lastcache>everycache) AND ifshow='1' 
ORDER BY lastcache LIMIT 0,3");
		while ($rt = $db->fetch_array($query)) {
			if (in_array($rt['funcname'],$array)) {
				$db->update("UPDATE pw_module SET lastcache='$timestamp' WHERE id='$rt[id]'");
				!$rt['limitnum'] && $rt['limitnum'] = 10;
				$funcname = 'mod_'.strtolower($rt['funcname']);
				${'_'.$rt['funcname']} = $funcname($rt['limitnum'],$rt['shownum']);
				if ($rt['funcname']=='HOTHITS' && empty($gohit)) {
					list($shownum) = explode(',',$rt['shownum']);
					$gohit = ${'_'.$rt['funcname']};
				}
			}
		}
		$db->free_result($query);
		if (!empty($gohit)) {
			@include(D_P.'mod/windcode.php');
			$_HOTHIT = array_shift($gohit);
			$_HOTHIT['content'] = $db->get_value("SELECT content FROM pw_$_HOTHIT[type] WHERE itemid='$_HOTHIT[itemid]'");
			$_HOTHIT['content'] = convert($_HOTHIT['content']);
			$_HOTHIT['content'] = preg_replace('/\[attachment=([0-9]+)\]/eis',"",strip_tags($_HOTHIT['content']),'-1');
			$_HOTHIT['content'] = substrs($_HOTHIT['content'],200);
			
			foreach ($_HOTHITS as $key => $value) {
				$_HOTHITS[$key]['subject'] = substrs($value['subject'],$shownum);
			}
		}
		$writecache = '';
		foreach ($array as $value) {
			(empty(${'_'.$value}) || !is_array(${'_'.$value})) && ${'_'.$value} = array();
			$writecache .= '$_'.$value.' = '.N_var_export(${'_'.$value}).";\r\n";
		}
		writeover(D_P.'data/cache/mod_cache.php',"<?php\r\n$writecache?>");
	}
}
function updatecache_db(){
	global $db;
	$configdb = "<?php\r\n";
	$query = $db->query("SELECT db_name,db_value FROM pw_setting WHERE db_name LIKE 'db_%'");
	while (@extract(db_cv($db->fetch_array($query)))) {
		$db_name = stripslashes(str_replace(array(';','\\','/','(',')','$'),'',$db_name));
		$configdb .= "\$$db_name='$db_value';\r\n";
	}
	$configdb .= "?>";
	writeover(D_P.'data/cache/config.php',$configdb);
}
function db_cv($array = array()){
	$array = is_array($array) ? array_map('db_cv',$array) : str_replace(array("\\","'"),array("\\\\","\'"),$array);
	return $array;
}
function mod_imgplayer($limitnum,$shownum){
	global $db,$attachpath,$attach_url,$db_showcate;
	list($showtnum) = explode(',',$shownum);
	$sql = '';
	foreach (array('blog','photo','music','bookmark','goods','file') as $value) {
		if (strpos("\t$db_showcate\t","\t$value\t")!==false) {
			$sql .= ($sql ? ',' : '')."'$value'";
		}
	}
	if ($sql) {
		$sql = ' AND i.type'.(strpos($sql,',')!==false ? " IN ($sql)" : "=$sql");

	}
	$cachearray = array();
	$query = $db->query("SELECT i.itemid,i.subject,i.type,i.uid,u.type as ftype,u.attachurl,u.ifthumb FROM pw_upload u LEFT JOIN pw_items i USING(itemid) WHERE i.ifcheck='1' AND i.ifhide='0'$sql ORDER BY i.postdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ftype'] == 'img') {
			strlen($rt['subject']) > 25 && $showtnum = 25;
			$rt['subject'] = substrs($rt['subject'],$showtnum);
			$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
			!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
			if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
				$rt['attachurl'] = "$attachpath/$rt[attachurl]";
			} elseif (file_exists($attach_url/$rt[attachurl])) {
				$rt['attachurl'] = "$attach_url/$rt[attachurl]";
			} else {
				$rt['attachurl'] = "$attachpath/none.gif";
			}
			$cachearray[] = array('itemid' => $rt['itemid'],'subject' => $rt['subject'],'type' => $rt['type'],'uid' => $rt['uid'],'attachurl' => $rt['attachurl']);
		}
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_alltags($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT tagname FROM pw_btags WHERE iflock='0' ORDER BY allnum DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$cachearray[] = array('tagname' => substrs($rt['tagname'],$showtnum),'tagnameurl' => rawurlencode($rt['tagname']));
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_classatcs($limitnum,$shownum){
	list($showtnum) = explode(',',$shownum);
	$teamarray = mod_classcounts();
	$cachearray = array();
	$i = 0;
	foreach ($teamarray as $value) {
		if ($i < $limitnum) {
			$i++;
			$value['name'] = preg_replace('/\<(.+?)\>/is','',$value['name']);
			$cachearray[] = array('cid' => $value['cid'],'name' => substrs($value['name'],$showtnum),'catetype' => $value['catetype'],'counts' => $value['counts']);
		}
	}
	unset($teamarray);
	return $cachearray;
}
function mod_userfprints($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT itemid,uid,author,type,subject,postdate,footprints FROM pw_items WHERE ifcheck='1' AND ifhide='0' ORDER BY footprints DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_hothits($limitnum,$shownum=null){
	global $db;
	$cachearray = array();
	$query = $db->query("SELECT i.itemid,i.uid,i.author,i.type,i.subject,i.postdate,u.icon FROM pw_items i LEFT JOIN pw_user u ON i.uid=u.uid WHERE i.ifcheck='1' AND i.ifhide='0' ORDER BY hits DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$rt['icon'] = showfacedesign($rt['icon']);
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_hotreplies($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT i.itemid,i.uid,i.author,i.type,i.subject,i.postdate,u.icon FROM pw_items i LEFT JOIN pw_user u ON i.uid=u.uid WHERE i.ifcheck='1' AND i.ifhide='0' ORDER BY replies DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$rt['icon'] = showfacedesign($rt['icon']);
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_newreplies($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT i.itemid,i.uid,i.author,i.type,i.subject,i.postdate,u.icon FROM pw_items i LEFT JOIN pw_user u ON i.uid=u.uid WHERE i.ifcheck='1' AND i.ifhide='0' ORDER BY lastreplies DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$rt['icon'] = showfacedesign($rt['icon']);
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_newusers($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT uid,username,icon FROM pw_user ORDER BY regdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['username'] = substrs($rt['username'],$showtnum);
		$rt['icon'] = showfacedesign($rt['icon']);
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_atcusers($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT uid,username,icon FROM pw_user ORDER BY items DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['username'] = substrs($rt['username'],$showtnum);
		$rt['icon'] = showfacedesign($rt['icon']);
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_cmdusers($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT uid,username,icon FROM pw_user WHERE commend='1' ORDER BY uid DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['username'] = substrs($rt['username'],$showtnum);
		$rt['icon'] = showfacedesign($rt['icon']);
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_newblogs($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT itemid,uid,author,type,subject,postdate FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND type='blog' ORDER BY postdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_newphotos($limitnum,$shownum){
	global $db,$attachpath,$attach_url,$imgpath;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	/*
	$query = $db->query("SELECT i.itemid,i.subject,i.type,i.uid,u.type as ftype,u.attachurl,u.ifthumb FROM pw_items i LEFT JOIN pw_upload u ON i.itemid=u.itemid WHERE i.ifcheck='1' AND i.ifhide='0' AND i.type='photo' ORDER BY i.postdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ftype']=='img') {
			$rt['subject'] = substrs($rt['subject'],$showtnum);
			$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
			!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
			if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
				$rt['attachurl'] = "$attachpath/$rt[attachurl]";
			} elseif (file_exists($attach_url/$rt[attachurl])) {
				$rt['attachurl'] = "$attach_url/$rt[attachurl]";
			} else {
				$rt['attachurl'] = "$attachpath/none.gif";
			}
			$cachearray[] = array('itemid' => $rt['itemid'],'subject' => $rt['subject'],'type' => $rt['type'],'uid' => $rt['uid'],'attachurl' => $rt['attachurl']);
		}
	}
	$db->free_result($query);
	*/
	$query = $db->query("SELECT a.aid,a.uid,a.author,a.subject,a.postdate,a.hpagepid,a.descrip,p.attachurl FROM pw_albums a LEFT JOIN pw_photo p ON a.hpagepid=p.pid WHERE a.ifcheck='1' AND a.ifhide='0' ORDER BY a.postdate DESC LIMIT 0,$limitnum");
		while ($rt = $db->fetch_array($query)) {
			$rt['attachurl'] =  $rt['attachurl'] ? $attachpath.'/'.$rt[attachurl] : $imgpath.'/nopic.jpg';
			$rt['subject'] = substrs($rt['subject'],15);
			$rt['descrip'] = substrs($rt['descrip'],15);
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$cachearray[] = $rt;
		}
	$db->free_result($query);
	return $cachearray;
}
function mod_newmusics($limitnum,$shownum){
	global $db,$attachpath,$attach_url;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	/*
	$query = $db->query("SELECT i.itemid,i.subject,i.type,i.uid,u.type as ftype,u.attachurl,u.ifthumb FROM pw_items i LEFT JOIN pw_upload u ON 
i.itemid=u.itemid WHERE i.ifcheck='1' AND i.ifhide='0' AND i.type='music' ORDER BY i.postdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ftype']=='img') {
			$rt['subject'] = substrs($rt['subject'],$showtnum);
			$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
			!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
		}
		if($rt['ftype']!='img'){
			$rt['attachurl'] = 'none.gif';
		}
		if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
			$rt['attachurl'] = "$attachpath/$rt[attachurl]";
		} elseif (file_exists($attach_url/$rt[attachurl])) {
			$rt['attachurl'] = "$attach_url/$rt[attachurl]";
		} else {
			$rt['attachurl'] = "$attachpath/none.gif";
		}
		$cachearray[] = array('itemid' => $rt['itemid'],'subject' => $rt['subject'],'type' => $rt['type'],'uid' => $rt['uid'],'attachurl' => $rt
['attachurl']);
	}
	$db->free_result($query);
	*/
	$query = $db->query("SELECT maid,uid,author,subject,postdate,hpageurl FROM pw_malbums WHERE ifcheck='1' ORDER BY postdate DESC LIMIT 0,$limitnum");
	while($rt = $db->fetch_array($query)){
		$rt[subject] = substrs($rt['subject'],15);
		$rt['postdate'] = date('Y-m-d',$rt['postdate']);
		$rt['hpageurl'] = showhpageurl($rt['hpageurl']);
		$cachearray[] = $rt;
	}
	return $cachearray;
}
/*
function mod_newgoodss($limitnum,$shownum){
	global $db,$attachpath,$attach_url;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT i.itemid,i.subject,i.type,i.uid,u.type as ftype,u.attachurl,u.ifthumb FROM pw_items i LEFT JOIN pw_upload u ON i.itemid=u.itemid WHERE i.ifcheck='1' AND i.ifhide='0' AND i.type='goods' ORDER BY i.postdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['ftype']=='img') {
			$rt['subject'] = substrs($rt['subject'],$showtnum);
			$rt['ifthumb'] && $rt['attachurl'] = str_replace('.','_thumb.',$rt['attachurl']);
			!$rt['attachurl'] && $rt['attachurl'] = 'none.gif';
			if (file_exists(R_P."$attachpath/$rt[attachurl]")) {
				$rt['attachurl'] = "$attachpath/$rt[attachurl]";
			} elseif (file_exists($attach_url/$rt[attachurl])) {
				$rt['attachurl'] = "$attach_url/$rt[attachurl]";
			} else {
				$rt['attachurl'] = "$attachpath/none.gif";
			}
			$cachearray[] = array('itemid' => $rt['itemid'],'subject' => $rt['subject'],'type' => $rt['type'],'uid' => $rt['uid'],'attachurl' => $rt['attachurl']);
		}
	}
	$db->free_result($query);
	return $cachearray;
}
*/
function mod_newbookmarks($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT i.itemid,i.uid,i.author,i.type,i.subject,i.postdate,bm.bookmarkurl FROM pw_items i LEFT JOIN pw_bookmark bm ON i.itemid=bm.itemid WHERE i.ifcheck='1' AND i.ifhide='0' AND i.type='bookmark' ORDER BY i.postdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
/*
function mod_newfiles($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT itemid,uid,author,type,subject,postdate FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND type='file' ORDER BY postdate DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
}
*/
function mod_teamatcs($limitnum,$shownum){
	global $db;
	@include(D_P.'data/cache/forum_cache_team.php');
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT teamid,cid,name FROM pw_team WHERE ifshow!='0' ORDER BY blogs DESC LIMIT 0,$limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['name'] = substrs($rt['name'],$showtnum);
		$rt['cate'] = $_TEAM[$rt['cid']]['name'];
		$cachearray[] = array('teamid' => $rt['teamid'],'cate' => $rt['cate'],'name' => $rt['name']);
	}
	$db->free_result($query);
	return $cachearray;
}
function mod_teamclass($limitnum,$shownum){
	list($showtnum) = explode(',',$shownum);
	$_TEAM = mod_classcounts('TEAM');
	$cachearray = array();
	$i = 0;
	foreach ($_TEAM as $value) {
		if ($i < $limitnum) {
			$i++;
			$value['name'] = preg_replace('/\<(.+?)\>/is','',$value['name']);
			$cachearray[] = array('cid' => $value['cid'],'name' => substrs($value['name'],$showtnum),'counts' => $value['counts']);
		}
	}
	unset($_TEAM);
	return $cachearray;
}
function mod_userskin($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	return $cachearray;
}
function mod_classcounts($type = null){
	$temparray = array();
	$array = array('BLOG','BOOKMARK','MUSIC','PHOTO','TEAM');
	foreach ($array as $value) {
		if (!empty($type) && $value != $type) {
			continue;
		}
		@include(Pcv(D_P."data/cache/forum_cache_".strtolower($value).".php"));
		$temparray += ${'_'.$value};
	}
	foreach ($temparray as $key => $value) {
		$volume[$key]  = $value['counts'];
		$edition[$key] = $value['cid'];
	}
	array_multisort($volume,SORT_DESC,$edition,SORT_DESC,$temparray);
	return $temparray;
}

function mod_randomlist($limitnum,$shownum){
	global $db;
	list($showtnum) = explode(',',$shownum);
	$cachearray = array();
	$query = $db->query("SELECT itemid,uid,author,type,subject,postdate FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND type='blog' ORDER BY Rand() Limit $limitnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['subject'] = substrs($rt['subject'],$showtnum);
		$rt['postdate'] = get_date($rt['postdate'],'y-m-d');
		$cachearray[] = $rt;
	}
	$db->free_result($query);
	return $cachearray;
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
?>