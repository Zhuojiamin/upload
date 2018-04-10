<?php
!function_exists('readover') && exit('Forbidden');

if ($admin_name) {
	$ckbbsid  = $admin_name;
	$ckbbsuid = $admindb['bbsuid'];
} elseif ($windid) {
	$ckbbsid  = $windid;
	$ckbbsuid = $winddb['bbsuid'];
} elseif ($userdb) {
	$ckbbsid  = $userdb['username'];
	$ckbbsuid = $userdb['uid'];
} else {
	$ckbbsid  = $ckbbsuid = '';
}
$ckarray = $db_cbbbsopen && $ckbbsid && ($db->server_version() < '40100' || $db_cbbbscharset == $charset) ? CheckBbsAll($ckbbsid) : array();

if (!$db_cbbbsopen || (strpos($_SERVER['PHP_SELF'],"$user_file?action=userinfo")===false && empty($ckarray))) {
	Showtruemsg('passportfail');
}
$ckbbsid = $ckarray['username'];
$ckbbusid = $ckarray['uid'];
function CheckBbsAll($bbsid){
	global $db,$db_cbbbsdbname,$PW,$db_cbbbspre,$db_sqlname,$db_sqlpre,$db_cbbbsurl,$db_cbbbsattachdir,$db_cbbbsimgdir;
	$db->select_db($db_cbbbsdbname);
	$PW = $db_cbbbspre;
	$rt = $db->get_one("SELECT uid,username,password,icon FROM pw_members WHERE username='".Char_cv($bbsid)."'");
	$db->select_db($db_sqlname);
	$PW = $db_sqlpre;
	if (!$rt['uid']) {
		return false;
	} else {
		$bbsicon = explode('|',$rt['icon']);
		if ($bbsicon[1] == 2 || $bbsicon[1] == 3) {
			$rt['icon'] = !preg_match('/^http/i',$bbsicon[0]) ? "$db_cbbbsurl/$db_cbbbsattachdir/upload/$bbsicon[0]" : $bbsicon[0];
		} else {
			$rt['icon'] = "$db_cbbbsurl/$db_cbbbsimgdir/face/$bbsicon[0]";
		}
		return $rt;
	}
}
function GetForumList($tids=''){
	global $db,$db_cbbbsdbname,$PW,$db_cbbbspre,$db_sqlname,$db_sqlpre,$tid,$ckbbsuid,$page,$db_perpage;
	$_BBSFDB = $blogdb = array();
	$sql = $fids = '';
	@include(D_P.'data/cache/bbsforum_cache.php');
	$_BBSFDB = UpdateBbsForum();
	$db->select_db($db_cbbbsdbname);
	$PW = $db_cbbbspre;
	if (is_numeric($tid)) {
		strpos(",$tids,",",$tid,")!==false && Showtruemsg('bbsatc_pusherror');
		$sql .= ($sql ? ' AND' : '')." tid='$tid'";
		$authorid = $db->get_value("SELECT authorid FROM pw_threads WHERE tid='$tid'");
		!$authorid && Showtruemsg('passportfail');
		$ckbbsuid != $authorid && Showtruemsg('bbsatc_usererror');
	}
	if ($tids) {
		$query  = $db->query("SELECT tid FROM pw_threads WHERE tid IN ($tids)");
		$tids	= '';
		while ($rt = $db->fetch_array($query)) {
			$tids .= ($tids ? ',' : '')."$rt[tid]";
		}
		$tids && $sql .= ($sql ? ' AND' : '')." tid NOT IN ($tids)";
	}
	foreach ($_BBSFDB as $value) {
		(int)$value['fid']>0 && $fids .= ($fids ? ',' : '')."'$value[fid]'";
	}
	$fids && $sql .= ($sql ? ' AND' : '')." fid IN ($fids)";
	$sql && $sql = " authorid='$ckbbsuid' AND $sql";
	$count = $db->get_value("SELECT COUNT(*) FROM pw_threads WHERE$sql");
	$limit = " LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$query = $db->query("SELECT tid,fid,subject,postdate FROM pw_threads WHERE$sql ORDER BY fid,postdate DESC$limit");
	$db->select_db($db_sqlname);
	$PW = $db_sqlpre;
	while ($rt = $db->fetch_array($query)) {
		$rt['name'] 	= $_BBSFDB[$rt['fid']]['name'];
		$rt['postdate'] = get_date($rt['postdate']);
		$rt['subject']	= substrs($rt['subject'],30);
		$blogdb[] = $rt;
	}
	return array($blogdb,$count,$tids);
}
function IntoBbsForum($bbsfid,$itemid,$dbtype){
	global $db,$db_cbbbsdbname,$PW,$db_cbbbspre,$db_blogurl,$admin_uid,$atc_content,$ckbbsid,$ckbbsuid,$atc_title,$timestamp,$onlineip,$usesign,$ipfrom,$ifconvert,$db_sqlname,$db_sqlpre,$db_post;
	$foruminfo = $_BBSFDB = $ptabledb = array();
	include(D_P.'data/cache/bbsforum_cache.php');
	!empty($_BBSFDB[$bbsfid]) && $foruminfo = $_BBSFDB[$bbsfid];
	unset($_BBSFDB);
	$bgatceurl = "$db_blogurl/".($dbtype=='blog' ? 'article.php?' : 'blog.php?do=showone&')."type=$dbtype&itemid=$itemid";
	include(GetLang('bbs'));
	$content = $lang['content']."\r\n".$atc_content;
	
	$db->select_db($db_cbbbsdbname);
	$PW = $db_cbbbspre;
	$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name='db_ptable' OR db_name='db_tlist'");
	while ($rt = $db->fetch_array($query)) {
		$ptabledb[$rt['db_name']] = $rt['db_value'];
	}
	$db->update("INSERT INTO pw_threads (fid,author,authorid,subject,ifcheck,type,postdate,lastpost,lastposter,hits,replies,topped,special,ptable) VALUES ('$bbsfid','".addslashes($ckbbsid)."','$ckbbsuid','$atc_title','1','0','$timestamp','$timestamp','".addslashes($ckbbsid)."','1','0','0','0','$ptabledb[db_ptable]')");
	$tid = $db->insert_id();
	$pw_tmsgs = ChangTmsgs($tid);
	unset($ptabledb);
	$bgifconvert = $ifconvert == '0' ? '1' : '2';
	$db->update("INSERT INTO $pw_tmsgs(tid,userip,ifsign,buy,ipfrom,ifconvert,ifwordsfb,content) VALUES('$tid','$onlineip','$usesign','','$ipfrom','$bgifconvert','1','$content')");
	$lastpost = substrs($atc_title,26)."\t".addslashes($ckbbsid)."\t".$timestamp."\t".'read.php?tid='.$tid.'&page=e#a';
	$db->update("UPDATE pw_forumdata SET lastpost='$lastpost',tpost=tpost+1,article=article+1,topic=topic+1 WHERE fid='$bbsfid'");
	if ($foruminfo['type'] == 'sub') {
		$lastpost = ($foruminfo['password'] != '' || $foruminfo['allowvisit'] != '' || $foruminfo['f_type'] == 'hidden') ? '' : ",lastpost='$lastpost'";
		if ($lastpost) {
			$db->update("UPDATE pw_forumdata SET tpost=tpost+1,article=article+1,topic=topic+1{$lastpost} WHERE fid='$foruminfo[fup]'");
			$rt = $db->get_one("SELECT fup,type FROM pw_forums WHERE fid='$foruminfo[fup]'");
			($rt['type'] == 'sub') && $db->update("UPDATE pw_forumdata SET tpost=tpost+1,article=article+1,topic=topic+1{$lastpost} WHERE fid='$rt[fup]'");
		}
	}
	$db->select_db($db_sqlname);
	$PW = $db_sqlpre;
	return true;
}
function ContentBbs($tids){
	global $db,$db_cbbbsdbname,$PW,$db_cbbbspre,$ckbbsuid,$db_cbbbsurl,$db_cbbbsattachdir,$db_sqlname,$db_sqlpre,$db_post,$db_cbbbsimgdir;
	$tiddb = $tmsgdb = $postdb = $ptabledb = $ptabledbs = array();
	$db->select_db($db_cbbbsdbname);
	$PW = $db_cbbbspre;
	$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name='db_ptable' OR db_name='db_tlist'");
	while ($rt = $db->fetch_array($query)) {
		$ptabledb[$rt['db_name']] = $rt['db_value'];
	}
	$tmsgdb[] = 'pw_tmsgs';
	$tlistdb = $ptabledb['db_tlist'] ? unserialize($ptabledb['db_tlist']) : array();
	foreach ($tlistdb as $key => $value) {
		((int)$key>0) && $tmsgdb[] = 'pw_tmsgs'.$key;
	}
	require_once(R_P.'mod/windcode.php');
	$a_url = $bbstids = '';
	foreach ($tmsgdb as $pw_tmsgs) {
		$query = $db->query("SELECT t.tid,t.fid,t.author,t.subject,t.postdate,t.lastpost,t.locked,t.ptable,tm.aid,tm.userip,tm.userip,tm.ipfrom,tm.ifconvert,tm.content FROM $pw_tmsgs tm LEFT JOIN pw_threads t ON tm.tid=t.tid WHERE t.authorid='$ckbbsuid' AND t.tid IN ($tids)");
		while ($rt = $db->fetch_array($query)) {
			Add_S($rt);
			$bbstids .= ($bbstids ? ',' : '')."'$rt[tid]'";
			$ptabledbs[] = $rt['ptable'];
			if($rt['aid']){
				$attachdb = $aids = array();
				$attachs = unserialize(stripslashes($rt['aid']));
				if (is_array($attachs)) {
					foreach ($attachs as $at) {
						if ($at['type']!='img' || $at['needrvrc']!=0) {
							$a_url = "<a href='".$db_cbbbsurl."/job.php?action=download&tid=".$rt[tid]."&aid=".$at[aid]."' target='_blank'><font color=red>".$at[name]."</font></a>";
							$attachdb['down'][$at['aid']] = array($at['name'],$at['size'],$at['type'],$at['descrip']);
						} else {
							$a_url = "$db_cbbbsurl/$db_cbbbsattachdir/$at[attachurl]";
							$a_url = cvpic($a_url,$db_post['picwidth'],$db_post['picheight'],1);
							$attachdb['pic'][$at['aid']]  = array($a_url,$at['descrip']);
						}
						$attachdb['ment'][$at['aid']] = "$a_url<br />";
						$at['descrip'] && $attachdb['ment'][$at['aid']] = "<b>$at[descrip]</b>".$attachdb['ment'][$at['aid']];
					}
					unset($attachs);
					$rt['content'] = attachment($rt['content'],$db_post['times']);
					foreach ($aids as $value) {
						if ($attachdb['pic'][$value]) {
							unset($attachdb['pic'][$value]);
						}
						if($attachdb['down'][$value]){
							unset($attachdb['down'][$value]);
						}
						unset($attachdb['ment'][$value]);
					}
					if ($attachdb['pic']) {
						foreach ($attachdb['pic'] as $key => $value) {
							$rt['content'] .= $attachdb['ment'][$key];
						}
					}
					if ($attachdb['down']) {
						foreach($attachdb['down'] as $key => $value){
							$rt['content'] .= $attachdb['ment'][$key];
						}
					}
					unset($aids,$attachdb);
				}
			}
			$tiddb[]=$rt;
		}
	}
	if ($bbstids) {
		$query2 = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while($rt2 = $db->fetch_array($query2)){
			$postdb[] = $rt2[Name];
		}
		foreach ($postdb as $pw_posts) {
			$query = $db->query("SELECT tid,author,ifconvert,content,postdate,userip,ipfrom FROM $pw_posts WHERE tid IN ($bbstids)");
			while ($rt = $db->fetch_array($query)) {
				Add_S($rt);
				$tiddb['comment'][] = $rt;
			}
		}
	}
	$db->select_db($db_sqlname);
	$PW = $db_sqlpre;
	return $tiddb;
}
function ChangTmsgs($tid){
	global $ptabledb;
	!$ptabledb['db_tlist'] && $pw_tmsgs = 'pw_tmsgs';
	$tlistdb = $ptabledb['db_tlist'] ? unserialize($ptabledb['db_tlist']) : array();
	foreach ($tlistdb as $key => $value) {
		if ((int)$key>0 && $tid>(int)$value) {
			$pw_tmsgs = 'pw_tmsgs'.$key;
			break;
		}
	}
	return $pw_tmsgs;
}
function UpdateBbsForum(){
	$_BBSFDB = $forum = $forumdb = $subdb1 = $subdb2 = array();
	@include(D_P.'data/cache/bbsforum_cache.php');
	$fids = '';
	list($_BBSFDB,$forum) = CheckForumArray($_BBSFDB);
	if (empty($_BBSFDB)) {
		foreach ($forum as $key => $value) {
			if ($value['type']=='forum') {
				$value['option'] = $value['name'];
				$forumdb[$key] = $value;
			} else {
				if ($forum[$value['fup']]['type']=='forum') {
					$value['option'] = '>'.$value['name'];
					$subdb1[$key] = $value;
				} else {
					$value['option'] = '>>'.$value['name'];
					$subdb2[$key] = $value;
				}
			}
		}
		foreach ($forumdb as $key => $forums) {
			$_BBSFDB[$key] = $forums;
			foreach ($subdb1 as $key1 => $sub1) {
				if ($sub1['fup']==$forums['fid']) {
					$_BBSFDB[$key1] = $sub1;
					foreach ($subdb2 as $key2 => $sub2) {
						$sub2['fup']==$sub1['fid'] && $_BBSFDB[$key2] = $sub2;
					}
				}
			}
		}
		unset($forum,$forumdb,$forums,$subdb1,$subdb2);
		$wthreaddb .= "\$_BBSFDB=".N_var_export($_BBSFDB).";\r\n\r\n";
		writeover(D_P.'data/cache/bbsforum_cache.php',"<?php\r\n$wthreaddb?>");
	}
	return $_BBSFDB;
}
function CheckForumArray($_BBSFDB=array()){
	global $db,$db_cbbbsdbname,$PW,$db_cbbbspre,$db_sqlname,$db_sqlpre;
	$fids = '';
	$forum = array();
	$db->select_db($db_cbbbsdbname);
	$PW = $db_cbbbspre;
	$bforums = $db->get_value("SELECT hk_value FROM pw_hack WHERE hk_name='bg_forums'");
	if (!$bforums) {
		$brecycle = $db->get_value("SELECT db_value FROM pw_config WHERE db_name='db_recycle'");
		$brecycle && $fids = " AND fid!='$brecycle'";
	} else {
		$bforumdb = explode(',',$bforums);
		foreach ($bforumdb as $value) {
			$value && $fids .= ($fids ? ',' : '')."'$value'";
		}
		$fids = " AND fid IN ($fids)";
	}
	$query = $db->query("SELECT fid,fup,type,name FROM pw_forums WHERE (f_type='forum' OR f_type='former')$fids AND type!='category' ORDER BY vieworder");
	while ($rt = $db->fetch_array($query)) {
		$rt['name'] = preg_replace('/\<(.+?)\>/is','',$rt['name']);
		$rt['name'] = str_replace(array('<','>'),array('&lt;','&gt;'),$rt['name']);
		$forum[$rt['fid']] = $rt;
	}
	$db->free_result($query);
	(count($_BBSFDB) != count($forum)) && $_BBSFDB = array();
	!empty($_BBSFDB) && $forum = array();
	$db->select_db($db_sqlname);
	$PW = $db_sqlpre;
	return array($_BBSFDB,$forum);
}
function CheckGroup($bbsuid){
	global $db,$db_cbbbsdbname,$PW,$db_cbbbspre,$db_sqlname,$db_sqlpre;
	$db->select_db($db_cbbbsdbname);
	$PW = $db_cbbbspre;
	$groups = $db->get_value("SELECT hk_value FROM pw_hack WHERE hk_name='bg_groups'");
	$pwbbs  = $db->get_one("SELECT groupid,memberid FROM pw_members WHERE uid='$bbsuid'");
	$db->select_db($db_sqlname);
	$PW = $db_sqlpre;
	empty($pwbbs) && Showtruemsg('undefined_action');
	$groupid = $pwbbs['groupid'] == '-1' ? $pwbbs['memberid'] : $pwbbs['groupid'];
	if ($groups && strpos($groups,",$groupid,")===false) {
		return false;
	}
	return true;
}
if (!function_exists('N_var_export')) {
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
}

function GetBbsMsg($bbsmid,$type,$state){
	 global $db,$db_cbbbsdbname,$PW,$db_cbbbspre,$db_sqlname,$db_sqlpre,$ckbbsuid,$db_post,$face;
	 $db->select_db($db_cbbbsdbname);
	 $PW = $db_cbbbspre;
	 $face = GetBbsSmiles();
	 $pw_msg = $PW.'msg';
	 $sql = '';
	 $bbsmid && ($sql .= ($sql ? ' AND' : '')." mid>'{$bbsmid}'");
	 $sql .= ($sql ? ' AND' : '').($type == 'sebox' ? " type='sebox'" : " type='rebox'");
	 $sql .= ($sql ? ' AND' : '').($type == 'sebox' ? " fromuid='$ckbbsuid'" : " touid='$ckbbsuid'");
	 $query = $db->query("SELECT * FROM {$pw_msg} WHERE{$sql}");
	 while($rt = db_cv($db->fetch_array($query))){
	 	if(strpos($rt['content'],'[s:')!==false){
				$rt['content'] = preg_replace("/\[s:(.+?)\]/eis","postcache('\\1')",$rt['content'],$db_post['times']);
		}
	 	$bbsmsgs[] = $rt;
	 }
	 $state == '1' && $bbsmsgs && $db->update("DELETE FROM {$pw_msg} WHERE{$sql}");
	 $db->select_db($db_sqlname);
	 $PW = $db_sqlpre;
	 return $bbsmsgs;
}


function GetBbsSmiles(){
	global $db,$db_cbbbsdbname,$PW,$db_cbbbspre;
	$db->select_db($db_cbbbsdbname);
	$PW = $db_cbbbspre;
	$pw_smiles = $PW.'smiles';
	$query = $db->query("SELECT * FROM $pw_smiles WHERE type='0' ORDER BY vieworder");
	while(@extract(db_cv($db->fetch_array($query)))){
		 $rt = $db->query("SELECT * FROM $pw_smiles WHERE type='$id' ORDER BY vieworder");
		 while($smiles = db_cv($db->fetch_array($rt))){
			 $face[$smiles['id']] = $path.'/'.$smiles['path'];
		 }
	 }
	 $db->free_result($query,$rt);
	 return $face;
}

function db_cv($array = array()){
	$array = is_array($array) ? array_map('db_cv',$array) : str_replace(array("\\","'"),array("\\\\","\'"),$array);
	return $array;
}
?>