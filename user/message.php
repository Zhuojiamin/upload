<?php
!function_exists('usermsg') && exit('Forbidden');

list($db_msgopen,$db_msgsound,$db_msgreg) = explode("\t",$db_msgcfg);
($db_msgopen == 0 || $_GROUP[allowmsg] == 0) && usermsg('msg_group_right');
$maxmsg = !empty($_GROUP[msgmax]) ? $_GROUP[msgmax] : $db_msgmax;

$pubmsg = $readmsg = $delmsg = $pages = '';
if(in_array($type,array('read','del')) || empty($type)){
	$pubmsg = $db->get_one("SELECT readmsg,delmsg FROM pw_userinfo WHERE uid='$admin_uid'");
	@extract($pubmsg);
}
$msg_gid = $admindb['groupid'];
if(!$type){
	/*
	if($state != 1){
		$sqladd = ' AND bbsmid=-1';
	}else{
		require_once(R_P.'mod/passport.php');
		$bbsmsgs = GetBbsMsg($admindb['bbsmids']);
		$sqladd = ' AND bbsmid!=-1';
		!is_array($bbsmsgs) && $bbsmsgs = array();
		foreach($bbsmsgs as $key => $value){
			$db->update("INSERT INTO pw_msgs (touid,fromuid,username,type,bbsmid,ifnew,title,mdate,content) VALUES ($value[touid],$value[fromuid],'$value[username]','$value[type]','$value[mid]',$value[ifnew],'$value[title]',$value[mdate],'$value[content]')");
			$bbsmids[] = $value['mid'];
		}
		$bbsmids && $bbsmids = implode(',',$bbsmids);
		$bbsmids = ($admindb['bbsmids'] .= ((!empty($admindb['bbsmids']) && !empty($bbsmids)) ? (','.$bbsmids) : $bbsmids));
		$db->update("UPDATE pw_userinfo SET bbsmids='$bbsmids' WHERE uid='$admin_uid'");
	}
	*/
	$pubnew = $prnew = 0;
	$pmsgdb = array();
	$newread= $newdel = '';
	$query  = $db->query("SELECT mid,fromuid,togroups,username,type,title,mdate FROM pw_msgs WHERE type='public' AND togroups LIKE '%,$msg_gid,%' AND mdate>'$admindb[regdate]' ORDER BY mdate DESC LIMIT 20");
	while($msginfo=$db->fetch_array($query)){
		if($delmsg && strpos(",$delmsg,",",$msginfo[mid],")!==false){
			$newdel .= $newdel ? ','.$msginfo['mid'] : $msginfo['mid'];
			continue;
		}
		if($readmsg && strpos(",$readmsg,",",$msginfo[mid],")!==false){
			$newread .= $newread ? ','.$msginfo['mid'] : $msginfo['mid'];
			$msginfo['ifnew']=0;
		} else{
			$pubnew = 2;
			$msginfo['ifnew']=1;
		}
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']=get_date($msginfo['mdate']);
		$msginfo['from']=$msginfo['username'];
		$pmsgdb[]=$msginfo;
	}
	if($readmsg || $delmsg){
		$newread = $newread ? msgsort($newread) : '';
		$newdel  = $newdel  ? msgsort($newdel)  : '';
		if($readmsg!=$newread || $delmsg!=$newdel){
			$db->update("UPDATE pw_userinfo SET readmsg='$newread',delmsg='$newdel' WHERE uid='$admin_uid'");
		}
	}
	$msgcount = $db->get_value("SELECT COUNT(*) FROM pw_msgs WHERE touid='$admin_uid' AND type='rebox'");
	$msgstart = $msgcount > $maxmsg ? $msgcount-$maxmsg : 0;
	$msgtoall = $msgcount > $maxmsg ? $maxmsg : $msgcount;
	(int)$page<1 && $page = 1;
	empty($db_perpage) && $db_perpage = 20;
	$limit = 'LIMIT '.(($page-1)*$db_perpage+$msgstart).','.$db_perpage;
	$query = $db->query("SELECT mid,touid,fromuid,username,bbsmid,ifnew,title,mdate FROM pw_msgs WHERE touid='$admin_uid' AND type='rebox'{$sqladd}  ORDER BY mdate DESC $limit");
	while($rt = $db->fetch_array($query)){
		$rt['mdate'] = date('y-m-d H:i',$rt['mdate']);
		$rt['ifnew'] && $prnew = 1;
		$rt['ifnew'] = ($rt['ifnew']==0 ? '是' : '否');
		$rt['title'] = substrs($rt['title'],40);
		$messages[] = $rt;
	}
	$db->free_result($query);
	if($msgtoall > $db_perpage){
		require_once(R_P.'mod/page_mod.php');
		$pages = page($msgtoall,$page,$db_perpage,"$basename&");
	}
	$msgpercent = round($msgcount/$maxmsg*100);
	
	$msgstart > 0 && $prnew = 1;
	
	$ifnew = $pubnew + $prnew;
	if($ifnew != $admindb['newpm']){
		$db->update("UPDATE pw_userinfo SET newpm='$ifnew' WHERE uid='$admin_uid'");
	}
	
	include PrintEot('message');footer();
	
}elseif($type=='sendbox'){
	(int)$page<1 && $page = 1;
	empty($db_perpage) && $db_perpage = 20;
	$limit = 'LIMIT ('.($page-1)*$db_perpage.','.$db_perpage.')';
	$query = $db->query("SELECT mid,touid,togroups,fromuid,username,bbsmid,title,mdate FROM pw_msgs WHERE fromuid='$admin_uid' AND type='sebox' ORDER BY mdate DESC");
	while($rt = $db->fetch_array($query)){
		$rt['mdate'] = date('y-m-d H:i',$rt['mdate']);
		$rt['title'] = substrs($rt['title'],40);
		$messages[] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_msgs WHERE fromuid='$admin_uid' AND type='sebox'");
	if($count > $db_perpage){
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"$basename");
	}
	include PrintEot('message');footer();
	
} elseif($type=="scout"){
	/**
	* 消息跟踪
	*/
	(int)$page<1 && $page = 1;
	empty($db_perpage) && $db_perpage = 20;
	$limit = 'LIMIT '.($page-1)*$db_perpage.','.$db_perpage;
	$query = $db->query("SELECT ms.mid,ms.fromuid,ms.touid,ms.username,ms.ifnew,u.username AS toname,ifnew,title,mdate FROM pw_msgs ms LEFT JOIN pw_user u ON u.uid=ms.touid WHERE type='rebox' AND fromuid='$admin_uid' ORDER BY mdate DESC $limit");
	while($msginfo=$db->fetch_array($query)){
		$msginfo['title']=substrs($msginfo['title'],35);
		$msginfo['mdate']= date('y-m-d H:i',$msginfo['mdate']);
		$msginfo['from']=$admin_uid;
		$msginfo['to']=$msginfo['toname'];
		$messages[]=$msginfo;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_msgs WHERE fromuid='$admin_uid' AND type='sebox'");
	if($count > $db_perpage){
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"$basename");
	}
	require_once(PrintEot('message'));footer();

}elseif($type=='write'){
	$maxsendmsg = !empty($_GROUP[maxsendmsg]) ? $_GROUP[maxsendmsg] : $db_msgmaxsend;
	if($maxsendmsg){
		$tdmsg = $db->get_value("SELECT COUNT(*) AS tdmsg FROM pw_msgs WHERE fromuid='$admin_uid' AND mdate>'$tdtime' AND type='rebox'");
		$tdmsg>=$maxsendmsg && usermsg('msg_num_limit');
	}
	list(,,,,,,$msggd) = explode("\t",$db_gdcheck);
	list(,,,,,$msgq)	= explode("\t",$db_qcheck);

	if ($msggd == 1) {
		$rawwindid = (!$admin_name) ? 'guest' : rawurlencode($admin_name);
		$ckurl = str_replace('?','',$ckurl);
	} else {
		$rawwindid = $ckurl = '';
	}
	
	
	require_once(R_P.'mod/post_mod.php');
	InitGP(array('remid','edmid','touid'));
	if(empty($_POST[step])){
		//回复
		if(is_numeric($remid)){
			$reinfo=$db->get_one("SELECT fromuid,touid,username,type,title,content FROM pw_msgs WHERE mid='$remid' AND type='rebox' AND touid='$admin_uid'");
			if($reinfo){
				$msgid="value=\"$reinfo[username]\"";
				$subject=strpos($reinfo['title'],'Re:')===false ? 'Re:'.$reinfo['title']:$reinfo['title'];
				$atc_content="[quote]".trim(substrs(preg_replace("/\[quote\](.+?)\[\/quote\]/is",'',$reinfo['content']),100))."[/quote]\n\n";
			}
		//消息跟踪中的编辑短消息	
		}elseif(is_numeric($edmid)){
			$edinfo=$db->get_one("SELECT g.touid,g.type,g.title,g.content,u.username FROM pw_msgs g LEFT JOIN pw_user u ON g.touid=u.uid WHERE g.mid='$edmid' AND g.fromuid='$admin_uid' AND g.ifnew='1' AND g.type='rebox'");
			if($edinfo){
				$msgid="value=\"$edinfo[username]\"";
				$subject=$edinfo['title'];
				$atc_content=$edinfo['content'];
			}
		//在个人主页中点击发送短消息
		}elseif(is_numeric($touid)){
			$reinfo=$db->get_one("SELECT username FROM pw_user WHERE uid='$touid'");
			$msgid="value=\"$reinfo[username]\"";
		}else{
			$msgid='';
		}
		/*
		elseif(is_numeric($edmid)){
			$edinfo=$db->get_one("SELECT g.touid,g.type,g.title,g.content,u.username FROM pw_msgs g LEFT JOIN pw_user u ON g.touid=u.uid WHERE g.mid='$edmid' AND g.fromuid='$admin_uid' AND g.ifnew='1' AND g.type='rebox'");
			if($edinfo){
				$msgid="value=\"$edinfo[username]\"";
				$subject=$edinfo['title'];
				$atc_content=$edinfo['content'];
			}
		}*/
	}elseif($_POST[step] == '1'){
		InitGP(array('pwuser','msg_title','atc_content','ifsave','gdcode','qanswer','qkey'),'P');
		if ($msggd == '1') {
			$cknum = GetCookie('cknum');
			Cookie('cknum','',0);
			if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
				usermsg('gdcode_error');
			}
		}
		if ($msgq=='1' && $db_question){
			$answer = unserialize($db_answer);
			$qanswer != $answer[$qkey] && usermsg('qanswer_error');
		}
		
		list($db_titlemax,$db_postmin,$db_postmax) = explode(',',$db_lenlimit);
		list($msg_title,$atc_content) = ConCheck($msg_title,$atc_content);
		list($tockuid,$tockusername) = UserCheck($pwuser);
		$user = $db->get_one("SELECT uid,username FROM pw_user WHERE uid='$tockuid'");
		if($edmid && $user['username']==$pwuser){
			 $db->update("UPDATE pw_msgs SET title='$msg_title',mdate='$timestamp',content='$atc_content' WHERE mid='$edmid' AND fromuid='$admin_uid' AND ifnew='1'");
		}elseif($remid || $tockuid){
			$db->update("INSERT INTO pw_msgs (touid,fromuid,username,type,bbsmid,ifnew,title,mdate,content) VALUES ('$tockuid','$admin_uid','$admin_name','rebox','-1','1','$msg_title',$timestamp,'$atc_content')");
		}
		if($ifsave == 'Y'){
			$db->update("INSERT INTO pw_msgs (touid,fromuid,username,type,bbsmid,ifnew,title,mdate,content) VALUES ('$tockuid','$admin_uid','$tockusername','sebox','-1','0','$msg_title',$timestamp,'$atc_content')");
		}
		$db->update("UPDATE pw_userinfo SET newpm='1' WHERE uid='$tockuid'");
		usermsg('operate_success',"$basename");
	}
	include PrintEot('message');footer();
	
}elseif($type == 'read'){
	InitGP('job',G);
	if($job == 'pri'){
		InitGP('towhere',G);
		$towhere == 'receivebox' && $db->update("UPDATE pw_msgs SET ifnew=0 WHERE mid=$mid");
		if($towhere == 'sendbox'){
			$towhere = '&towhere='.$towhere;
		}elseif($towhere == 'scout'){
			$towhere = '&towhere='.$towhere;
		}elseif($towhere == 'receivebox'){
			$towhere = '&towhere='.$towhere;	
		}else{
			$towhere = '';
		}
		require_once(R_P.'mod/windcode.php');
		$msginfo = $db->get_one("SELECT mid,username,type,title,mdate,content FROM pw_msgs WHERE mid=$mid");
		$msginfo['username'] = ($msginfo['type'] == 'sebox' ? $admin_name : $msginfo['username']);
		$msginfo['mdate'] = date('y-m-d H:i',$msginfo['mdate']);
		$msginfo['content'] = nl2br($msginfo['content']);
		$msginfo['content'] = convert($msginfo['content'],$db_post);
		strpos($msginfo['content'],'[s:')!==false && $msginfo['content'] = showsmile($msginfo['content']);
		getusermsg($admin_uid,$admindb['newpm']);
	  	include PrintEot('message');footer();
	}elseif($job == 'pub'){
		require_once(R_P.'mod/windcode.php');
		InitGP(array('mid'));
		$msginfo = $db->get_one("SELECT mid,fromuid,touid,username,ifnew,title,mdate,content FROM pw_msgs WHERE mid='$mid' AND type='public' AND togroups LIKE '%,$msg_gid,%'");
		if($msginfo){
			$msginfo['content'] = str_replace("\n","<br>",$msginfo['content']);
			$msginfo['content'] = convert($msginfo['content'],$db_post);
			if(strpos($msginfo['content'],'[s:')!==false){
				$msginfo['content'] = showsmile($msginfo['content']);
			}
			$msginfo['title']   = str_replace('&ensp;$','$', $msginfo['title']);
			$msginfo['content'] = str_replace('&ensp;$','$', $msginfo['content']);
			$msginfo['mdate']   = get_date($msginfo['mdate']);
			$msginfo['content'] = str_replace("\$email",$admindb['email'],$msginfo['content']);
			$msginfo['content'] = str_replace("\$windid",$admin_uid,$msginfo['content']);
			
			if(strpos(",$readmsg,",",$msginfo[mid],")===false){
				$readmsg .= $readmsg ? ','.$msginfo['mid'] : $msginfo['mid'];
				$readmsg=msgsort($readmsg);
				$db->update("UPDATE pw_userinfo SET readmsg='$readmsg' WHERE uid='$admin_uid'");
			}
			getusermsg($admin_uid,$admindb['newpm'],'pub');
		} else{
			usermsg('msg_error');
		}
		require_once(PrintEot('message'));footer();
	}
}elseif($type=='del'){
	
	/*
	InitGP(array('delids','delid','towhere','pdelid','pdelids'));
	if(is_array(delids)){
		foreach (delids as $value) {
			if ((int)$value > 0) {
				$mids .= ($mids ? ',' : '')."'$value'";
			}
		}
		!$mids && usermsg('operate_error');
		$mids = strpos($mids,',')===false ? "=$mids" : " IN ($mids)";
		$db->update("DELETE FROM pw_msgs WHERE mid{$mids}");
	}elseif(!empty($delid)){
		$db->update("DELETE FROM pw_msgs WHERE mid='$delid'");
	}else{
		usermsg('operate_error');
	}
	
	if(is_array(pdelids)){
		
	}
	getusermsg($admin_uid,$admindb['newpm']);
	usermsg('del_success',"$basename&type=$towhere");
	
	*/
	
	InitGP(array('pdelid','delid','towhere','pdelids','delids'));
	if(!is_numeric($delids)){
		$delids='';
		if(is_array($delid)){
			foreach($delid as $value){
				is_numeric($value) && $delids.=$value.',';
			}
			$delids && $delids=substr($delids,0,-1);
		}
	}
	if($towhere == 'receivebox'){
		if(!is_numeric($pdelids)){
			$pdelids = '';
			if(is_array($pdelid)){
				foreach($pdelid as $value){
					is_numeric($value) && $pdelids.=$value.',';
				}
				$pdelids && $pdelids=substr($pdelids,0,-1);
			}
		}
	} else{
		$pdelids = '';
	}
	!$delids && !$pdelids && usermsg('del_error');
	
	if($delids){
		if($towhere=='receivebox'){
			$sql = " AND type='rebox' AND touid='$admin_uid'";
		} elseif($towhere=='sendbox'){
			$sql = " AND type='sebox' AND fromuid='$admin_uid'";
		} else{
			$sql = " AND type='rebox' AND fromuid='$admin_uid' AND ifnew=1";
		}
		$db->update("DELETE FROM pw_msgs WHERE mid IN($delids) $sql");
		getusermsg($admin_uid,$admindb['newpm']);
	}
	if($pdelids){
		$msginfo = '';
		$readmsg = ','.$readmsg.',';
		$delmsg  = $delmsg ? ','.$delmsg.',' : ',';
		$deliddb = explode(',',$pdelids);
		foreach($deliddb as $key=>$val){
			if(strpos("$readmsg",",$val,")!==false){
				$readmsg=str_replace(",$val,",",",$readmsg);
			}
			if(strpos($delmsg,",$val,")===false){
				$delmsg.=$val.',';
			}
		}
		$readmsg = msgsort(substr($readmsg,1,-1));
		$delmsg  = msgsort(substr($delmsg,1,-1));
		$db->update("UPDATE pw_userinfo SET readmsg='$readmsg',delmsg='$delmsg' WHERE uid='$admin_uid'");
		getusermsg($admin_uid,$admindb['newpm'],'pub');
	}
	usermsg('operate_success',"$basename&");
}


function getusermsg($admin_uid,$ifnew,$type=''){
	global $db;
	if($type==''){
		$rs=$db->get_one("SELECT ifnew FROM pw_msgs WHERE touid='$admin_uid' AND ifnew='1' AND type='rebox' LIMIT 1");
		if(!$rs){
			if($ifnew==1 || $ifnew==3){
				$ifnew--;
				$db->update("UPDATE pw_userinfo SET newpm='$ifnew' WHERE uid='$admin_uid'");
			}
		} else{
			if($ifnew==0 || $ifnew==2){
				$ifnew++;
				$db->update("UPDATE pw_userinfo SET newpm='$ifnew' WHERE uid='$admin_uid'");
			}
		}
	} else{
		global $msg_gid,$readmsg,$delmsg,$admindb;
		$checkmsg = '0';
		$readmsg && $checkmsg .=','.$readmsg;
		$delmsg  && $checkmsg .=','.$delmsg;
		$rt=$db->get_one("SELECT mid FROM pw_msgs WHERE type='public' AND togroups LIKE '%,$msg_gid,%' AND mid NOT IN ($checkmsg) AND mdate>'$admindb[regdate]' LIMIT 1");
		if(!$rt){
			if($ifnew==3 || $ifnew==2){
				$ifnew-=2;
				$db->update("UPDATE pw_userinfo SET newpm='$ifnew' WHERE uid='$admin_uid'");
			}
		} else{
			if($ifnew==0 || $ifnew==1){
				$ifnew+=2;
				$db->update("UPDATE pw_userinfo SET newpm='$ifnew' WHERE uid='$admin_uid'");
			}
		}
	}
}

function msgsort($msgstr){
	if(empty($msgstr)){
		return '';
	}
	$msgdb=explode(',',$msgstr);
	arsort($msgdb);
	$newmsg=implode(',',$msgdb);
	return $newmsg;
}


?>