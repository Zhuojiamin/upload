<?php
/**
 * Copyright (c) 2003-07  PHPWind.net. All rights reserved.
 * 
 * @filename: postnew.php
 * @author: Noizy (noizyfeng@gmail.com), QQ:7703883
 * @modify: Mon Mar 19 18:21:07 CST 2007
 */
!defined('USERPOST') && exit('Forbidden');
if ($_POST['step']!=2) {
	$atc_title = $html_CK = $ifsign_CK = $atc_content = $allowreply_0 = '';
	$icon1_0 = $allowreply_1 = $icon2_0 = 'CHECKED';
	$ifhide_0 = 'SELECTED';
	if ($type == 'bookmark') {
		foreach ($dirdb as $value) {
			$itemcache .= "<option id=\"dirop$value[typeid]\" value=\"$value[typeid]\">$value[name]</option>";
			$itemarray[$value['typeid']] = array('name' => $value['name'],'vieworder' => (int)$value['vieworder']);
		}
	}elseif ($type == 'music') {
		empty($malbumdb) && $malbumdb = array();
		foreach ($malbumdb as $value) {
			$itemcache .= "<option id=\"malbumop$value[maid]\" value=\"$value[maid]\">$value[subject]</option>";
		}
	}elseif($type == 'photo') {
		empty($albumdb) && $albumdb = array();
		foreach ($albumdb as $value) {
			$itemcache .= "<option id=\"albumop$value[aid]\" value=\"$value[aid]\">$value[subject]</option>";
		}
	}
	else{
		if ($db_cbbbsopen=='1') {
			require_once(R_P.'mod/passport.php');
			$dbcbbbbsfid = !empty($db_cbbbbsfid) ? explode(',',$db_cbbbbsfid) : array();
			$bbsfcache = '';
			$_BBSFDB = UpdateBbsForum();
			foreach ($_BBSFDB as $value) {
				if(in_array($value[fid],$dbcbbbbsfid)){
					continue;
				}
				$bbsfcache .= "<option value=\"$value[fid]\">$value[option]</option>";
			}
			unset($_BBSFDB);
		}
		InitGP('teamid',G);
		if($db_teamifopen == '1' && !empty($teamid)){
			$query = $db->query("SELECT uid FROM pw_tuser WHERE teamid='$teamid' AND ifcheck='1'");
			while($rt = $db->fetch_array($query)){
				$tuserdb[] = $rt[uid];
			}
			empty($tuserdb) && $tuserdb=array();
			!in_array($admin_uid,$tuserdb) && usermsg('not_join');
			${'checked_'.$teamid} = 'checked';
		}
		foreach ($catedb as $value) {
			$add = '';
			for ($i=0;$i<$value['type'];$i++) {
				$add .= '>';
			}
			$forumcache .= "<option value=\"$value[cid]\">$add $value[name]</option>";
		}
		foreach ($dirdb as $value) {
			$itemcache .= "<option id=\"dirop$value[typeid]\" value=\"$value[typeid]\">$value[name]</option>";
			$itemarray[$value['typeid']] = array('name' => $value['name'],'vieworder' => (int)$value['vieworder']);
		}
	}
	require_once PrintEot('post');footer();
} else {
	$spostnum = $postnum = '';
	list($postnum) = explode(',',$_GROUP['postnum']);
	list($limitnum) = explode(',',$_GROUP['limitnum']);
	$limitnum && ($timestamp - $admindb['lastpost'] < $limitnum) && usermsg('time_limit');
	$postnum && $admindb['todaypost'] >= $postnum && usermsg('post_limit');
	InitGP(array('atc_ifsign','atc_autourl','gdcode','atc_iconid1','atc_iconid2','atc_cid','atc_dirid','atc_aid','atc_tagdb','atc_allowreply','atc_ifhide','atc_bbsfid','atc_teamid','qanswer','qkey'),'P');
	$items = (int)$admindb['items'] + (int)$admindb['albums'] + (int)$admindb['musics'];
	if ($items < $postgd) {
		$cknum = GetCookie('cknum');
		Cookie('cknum','',0);
		if (!$gdcode || !SafeCheck(explode("\t",StrCode($cknum,'DECODE')),$gdcode)) {
			usermsg('gdcode_error');
		}
	}

	if ($postq=='1' && $db_question){
		$answer = unserialize($db_answer);
		$qanswer != $answer[$qkey] && usermsg('qanswer_error');
	}
	if($type == 'blog') {
		$attachdb = (array)$_POST['attachdb'];
		list($atc_title,$atc_content) = ConCheck($_POST['atc_title'],$_POST['atc_content']);
		$atc_ifsign = $atc_ifsign ? 1 : 0;
		($_GROUP['htmlcode'] && $_POST['atc_htmlcode']) && $atc_ifsign += 2;
		$atc_content = Atc_cv($atc_content,$atc_ifsign);
		$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
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
		$atc_cid		= (int)$atc_cid;
		$atc_dirid		= (int)$atc_dirid;
		$atc_iconid1 	= (int)$atc_iconid1;
		$atc_iconid2 	= (int)$atc_iconid2;
		$ifcheck		= $db_postcheck ? 0 : 1;
		$atc_allowreply = (int)$atc_allowreply;
		$atc_ifhide 	= (int)$atc_ifhide;
		$atc_bbsfid 	= (int)$atc_bbsfid;
		//$atc_teamid 	= (int)$atc_teamid;
		$ipfrom 		= cvipfrom($onlineip);
		$atc_iconid = $atc_iconid1.','.$atc_iconid2;
		$db->update("INSERT INTO pw_items (cid,bbsfid,dirid,uid,author,type,icon,subject,postdate,lastpost,allowreply,ifcheck,ifwordsfb,ifhide) VALUES ('$atc_cid','$atc_bbsfid','$atc_dirid','$admin_uid','".addslashes($admin_name)."','$type','$atc_iconid','$atc_title','$timestamp','$timestamp','$atc_allowreply','$ifcheck','$ifwordsfb','$atc_ifhide')");
		$itemid = $db->insert_id();
		$newtagdb = array();
		if (is_array($atc_tagdb)) {
			foreach ($atc_tagdb as $key => $value) {
				is_numeric($key) && $value && $newtagdb[$key] = $value;
			}
		}
		$atc_tagdb = AddTag($newtagdb,$type,$itemid);
		$ifcheck && $db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$atc_cid'");
		updatecache_cate($type);
		$userdb = $ifcheck ? array('uid' => $admin_uid,'type' => $type,'items' => $admindb['items'],'todaypost' => $admindb['todaypost'],'lastpost' => $admindb['lastpost']) : array();
		update_post($userdb);
		UploadSQL($admin_uid,$itemid,$atc_cid,$type);
		$db->update("INSERT INTO pw_$type (itemid,tags,userip,ifsign,ipfrom{$intofeild},ifconvert,content) VALUES('$itemid','$atc_tagdb','$onlineip','$atc_ifsign','$ipfrom'{$updatefeile},'$ifconvert','$atc_content')");
		if ($db_cbbbsopen=='1' && (int)$atc_bbsfid>1) {
			$uploads = $db->get_value("SELECT uploads FROM pw_items WHERE itemid='$itemid'");
			$uploads = unserialize($uploads);
			if (is_array($uploads)) {
				foreach ($uploads as $key => $upload) {
					//is_numeric($key) && $value && $uploads = $value;
					$upload = GetAttachUrl($upload);
					if ($upload['type']!='img') {
						$a_url = "<a href=\"$db_blogurl/job.php?action=download&itemid=$itemid&aid=$upload[aid]\" target=\"_blank\"><font color=\"red\">$upload[name]</font></a>";
						$attachdb['down'][$upload['aid']] = array($upload['name'],$upload['size'],$upload['type'],$upload['desc'],$a_url);
						
					} else {
						$a_url = cvpic($upload['attachurl'],$db_post['picwidth'],$db_post['picheight'],1);
						$attachdb['pic'][$upload['aid']]  = array($a_url,$upload['desc']);
					}
					$attachmentdb[$upload['aid']] = ($upload['desc'] ? "<b>$upload[desc]</b><br />" : '')."$a_url";
				}
			}
			$aids = array();
			$atc_content = attachment($atc_content,$db_post['times']);
			foreach($aids as $aid){
				if ($attachdb['pic'][$aid]) {
					unset($attachdb['pic'][$aid]);
				}
				if($attachdb['down'][$aid]){
					unset($attachdb['down'][$aid]);
				}
				unset($attachmentdb[$aid]);
			}
			include(GetLang('bbs'));
			$atc_content .= '<br /><br />';
			if(is_array($attachdb['pic'])){
				foreach ($attachdb['pic'] as $key => $value){
					if($value[1]){
						$atc_content .= $lang['bbs_blogdescrip'].$value[1].'<br />';
					}
					$atc_content .= $lang['bbs_blogimg'].$value[0].'<br />';
				}
			}
			if(is_array($attachdb['down'])){
				foreach ($attachdb['down'] as $key => $value){
					if($value[3]){
						$atc_content .= $lang['bbs_blogdescrip'].$value[3].'<br />';
					}
					$atc_content .= $lang['bbs_blogattach'].$value[4].'<br />';
				}
			}
			$atc_content = addslashes($atc_content);
			/*
			if($uploads['type'] == 'img' && !empty($uploads['type'])){
				$atc_content .= '</br></br></br><font color="red">blog图片:</font></br><img src="'.$db_blogurl.'/'.$attpath.'/'.$uploads['attachurl'].'"/>';
			}elseif(!empty($uploads['type'])){
				$atc_content .= '</br></br></br>blog附件:<a href="'.$db_blogurl.'/'.$attpath.'/'.$uploads['attachurl'].'"/><font color="red">'.$uploads['name'].'</font></a>';
			}else{
				$atc_content .= '';
			}
*/
			require_once(R_P.'mod/passport.php');
			IntoBbsForum($atc_bbsfid,$itemid,$type);
		} else {
			$atc_bbsfid = 0;
		}
		!empty($atc_teamid) && pushitem($itemid,$atc_title,$atc_teamid,$type);
		update_bloginfo_cache('blogs');
		if($ifcheck == '0'){
			usermsg('operate_success_not_checked',"$user_file?action=itemcp&type=$type");
		}else{
			usermsg('operate_success',"$user_file?action=itemcp&type=$type");
		}
	}elseif ($type == 'photo') {
		empty($_FILES['attachment_1']['name']) && usermsg('empty_photo',"$user_file?action=post&type=$type");
		empty($atc_aid) && usermsg('empty_aid',"$user_file?action=addalbum&type=$type&job=add");
		$photos = 0;
		$db_uploadfiletype = $db_uploadphototype;
		@extract($db->get_one("SELECT cid,photos FROM pw_albums WHERE aid='$atc_aid'"));
		$photoes = UploadSQL($admin_uid,$atc_aid,$cid,$type);
		!is_array($photoes) && $photoes=array();
		foreach($photoes as $key => $value){
			$newtagdb[$key] = $value[tags];
			$newphotos++;
		}
		$photos = $photos + $newphotos;
		$atc_tagdb = AddTag($newtagdb,$type,$atc_aid);
		$db->update("UPDATE pw_albums SET photos='$photos' WHERE aid='$atc_aid'");
		update_ppost($photos);
		usermsg('operate_success',"$user_file?action=itemcp&type=$type");
	} elseif ($type == 'music') {
		InitGP(array('maid','singer','name','tags','murl','descrip'),'P');
        empty($maid) && usermsg('empty_maid',"$user_file?action=addmalbum&type=$type&job=add");
		$music_num = 0;
		if($name){
			foreach($name as $key => $value){
				if(!empty($value)){
					$name_ck1[]=$value;
					$murl_ck1[]=$murl[$key];
					$tags_ck1[]=$tags[$key];
					$singer_ck1[]=$singer[$key];
					$descrip_ck1[]=$descrip[$key];
				}
			}
		}
		if($murl_ck1){
			foreach($murl_ck1 as $key => $value){
				if(!empty($value)){
					$name_ck[]=$name_ck1[$key];
					$murl_ck[]=$value;
					$tags_ck[]=$tags_ck1[$key];
					$singer_ck[]=$singer_ck1[$key];
					$descrip_ck[]=$descrip_ck1[$key];
				}
			}
		}
		empty($name_ck) && usermsg('empty_music');
		foreach($name_ck as $key =>$value){
			$musicdb[] = array(name=>$value,singer=>$singer_ck[$key],tags=>$tags_ck[$key],murl=>$murl_ck[$key],descrip=>$descrip_ck[$key]);
		}
		foreach($musicdb as $key => $value){
			!preg_match('/^http(s)?:\/\//i',$value[murl]) && usermsg('url_error');
			if(!$value[name] || strlen($value[name])>$db_titlemax){
				usermsg('title_limit');
			}
			if(strlen($value[descrip])>$db_postmax){
				usermsg('content_limit');
			}
			$ifwordsfb = 0;
			$ckname = $value[name];
			$ckdescrip = $value[descrip];
			$cktags = $value[tags];
			foreach ($_FORBIDDB as $fvalue) {
				$ckname = N_strireplace($fvalue['word'],$fvalue['wordreplace'],$value[name]);
				$ckdescrip = N_strireplace($fvalue['word'],$fvalue['wordreplace'],$value[descrip]);
				$cktags = N_strireplace($fvalue['word'],$fvalue['wordreplace'],$value[tags]);
			}
			if ($ckname != $value[name]) {
				$musicdb[$key][name] = $ckname;
				$musicdb[$key][ifwordsfb] = 1;
			}
			if ($ckdescrip != $value[descrip]) {
				$musicdb[$key][descrip] = $ckdescrip;
				$musicdb[$key][ifwordsfb] = 1;
			}
			if ($cktags != $value[tags]) {
				$musicdb[$key][tags] = $cktags;
				$musicdb[$key][ifwordsfb] = 1;
			}
		}
		foreach($musicdb as $key => $value){
			$db->update("INSERT INTO pw_music(uid,maid,name,murl,posttime,singer,tags,descrip,ifwordsfb) VALUES ('$admin_uid','$maid','$value[name]','$value[murl]','$timestamp','$value[singer]','$value[tags]','$vlaue[descrip]','$value[ifwordsfb]')");
			$mid = $db->insert_id();
			$newtagdb[$mid] = $value[tags];
			$music_num++;
		}
		AddTag($newtagdb,$type,$maid);
		update_mpost($music_num);
		$db->update("UPDATE pw_malbums SET musics=musics+'$music_num' WHERE maid='$maid'");
		usermsg('operate_success',"$user_file?action=itemcp&type=music&job=list&maid=$maid&");
	} elseif ($type == 'bookmark') {
		InitGP(array('bookmarkurl','atc_title','atc_content'),'P');
		(!$bookmarkurl || !preg_match("/^http|mms/i",$bookmarkurl)) && usermsg('bookmark_url_error');
		list($atc_title,$atc_content) = ConCheck($atc_title,$atc_content);
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
		$atc_dirid		= (int)$atc_dirid;
		$atc_ifhide     = (int)$atc_ifhide;
		$ifcheck		= $db_postcheck ? 0 : 1;
		$ipfrom 		= cvipfrom($onlineip);
		$db->update("INSERT INTO pw_items (dirid,uid,author,type,subject,postdate,lastpost,ifcheck,ifwordsfb,ifhide) VALUES ('$atc_dirid','$admin_uid','".addslashes($admin_name)."','bookmark','$atc_title','$timestamp','$timestamp','$ifcheck','$ifwordsfb','$atc_ifhide')");
		$itemid = $db->insert_id();
		$userdb = $ifcheck ? array('uid' => $admin_uid,'type' => $type,'items' => $admindb['items'],'todaypost' => $admindb['todaypost'],'lastpost' => $admindb['lastpost']) : array();
		update_post($userdb);
		$db->update("INSERT INTO pw_$type (itemid,userip,ipfrom,bookmarkurl,content) VALUES('$itemid','$onlineip','$ipfrom','$bookmarkurl','$atc_content')");
		if($ifcheck == '0'){
			usermsg('operate_success_not_checked',"$user_file?action=itemcp&type=$type");
		}else{
			P_unlink(D_P."data/cache/bookmarkcache.php");
			usermsg('operate_success',"$user_file?action=itemcp&type=$type");
		}
	}
}
function AddTag($tagdb,$tagtype,$tid){
	global $db,$admin_uid,$admin_name,$atc_title,$timestamp;
	$return = '';
	if (!$tid) {
		return $return;
	}
	$tagdb = array_unique($tagdb);
	$tagnum = $tagtype.'num';

	foreach ($tagdb as $key => $value) {
		$itemid = (($tagtype == 'photo') || ($tagtype == 'music') ? $key : $tid);
		if ($value) {
			$value = trim(Tag_cv($value));
			$tagid = $db->get_value("SELECT tagid FROM pw_btags WHERE tagname='$value'");
			if ($tagid) {
				$db->update("UPDATE pw_btags SET $tagnum=$tagnum+1,allnum=allnum+1 WHERE tagid='$tagid'");
				$db->update("INSERT INTO pw_taginfo (tagid,tagname,uid,itemid,tagtype,author,subject,addtime) VALUES ('$tagid','$value','$admin_uid','$itemid','$tagtype','".addslashes($admin_name)."','$atc_title','$timestamp')");
				$return .= ($return ? ',' : '').$value;
			} else {
				$db->update("INSERT INTO pw_btags(uid,tagname,$tagnum,allnum) VALUES ($admin_uid,'$value','1','1')");
				$tagid = $db->insert_id();
				$db->update("INSERT INTO pw_taginfo (tagid,tagname,uid,itemid,tagtype,author,subject,addtime) VALUES ('$tagid','$value','$admin_uid','$itemid','$tagtype','".addslashes($admin_name)."','$atc_title','$timestamp')");
				$return .= ($return ? ',' : '').$value;
			}
		}
	}
	return $return;
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

function update_mpost($music_num){
	global $db,$timestamp,$tdtime,$admindb;
	$db->update("UPDATE pw_user SET musics=musics+$music_num,lastpost='$timestamp' WHERE uid='$admindb[uid]'");
}

function update_ppost($photo_num){
	global $db,$timestamp,$tdtime,$admindb;
	$db->update("UPDATE pw_user SET photos=photos+$photo_num,lastpost='$timestamp' WHERE uid='$admindb[uid]'");
}


function Tag_cv($tag){
	$chars = "`~!@#$%^&*()_-+=|\\{}[]:\";',./<>?";
	$len = strlen($chars);
	for ($i=0; $i<$len; $i++) {
		$tag = str_replace($chars[$i],'',$tag);
	}
	return $tag;
}
function getmemberid($nums){
	global $_gmember;
	$gid = 0;
	foreach ($_gmember as $key => $value) {
	(int)$nums>=$value['creditneed'] && $gid = $key;
	}
	return $gid;
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

function GetAttachUrl($array){
	global $db_blogurl,$attach_url,$attachpath;
	if ($array['type']=='img') {
		$a_url = $attach_url ? $attach_url : "$db_blogurl/$attachpath";
		$array['picurl'] = "$a_url/$array[attachurl]";
		N_stripos($array['picurl'],'login') && $array['picurl'] = N_strireplace('login','log in',$array['picurl']);
		$array['attachurl'] = $array['picurl'];
		if ($array['ifthumb']) {
			$attach_ext = strrchr($array['attachurl'],'.');
			$array['attachurl'] = str_replace($attach_ext,"_thumb{$attach_ext}",$array['attachurl']);
		}
	}
	return $array;
}
?>