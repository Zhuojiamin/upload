<?php
/**
 * Copyright (c) 2003-07  PHPWind.net. All rights reserved.
 * 
 * @filename: postmodify.php
 * @author: Noizy (noizyfeng@gmail.com), QQ:7703883
 * @modify: Mon Mar 19 18:20:53 CST 2007
 */
!defined('USERPOST') && exit('Forbidden');

if ($_POST['step']!=2) {
	if($type == 'blog' || $type == 'bookmark'){
		$itemdb = $db->get_one("SELECT * FROM pw_$type t LEFT JOIN pw_items i USING(itemid) WHERE t.itemid='$itemid'");
	    (!$itemdb || $itemdb['uid']!=$admin_uid) && usermsg('modify_error');
		$atc_title = $itemdb['subject'];
		$html_CK = (int)$itemdb['ifsign'] < 2 ? '' : 'CHECKED';
		$ifsign_CK = ($itemdb['ifsign']==1 || $itemdb['ifsign']==3) ? 'CHECKED' : '';
		$atc_content = (strpos($itemdb['content'],$db_blogurl)!==false) ? str_replace(array('p_w_picpath','p_w_upload','<','>'),array($picpath,$attachpath,'&lt;','&gt;'),$itemdb['content']) : str_replace(array('<','>'),array('&lt;','&gt;'),$itemdb['content']);
		$icon = explode(',',$itemdb['icon']);
		$pushlog = $itemdb['pushlog'];
		${'icon1_'.(int)$icon[0]} = ${'icon2_'.(int)$icon[1]} = ${'allowreply_'.(int)$itemdb['allowreply']} = ' CHECKED';
		foreach ($catedb as $value) {
			$add = '';
			for ($i=0;$i<$value['type'];$i++) {
				$add .= '>';
			}
			$slted = ($itemdb['cid']==$value['cid']) ? ' SELECTED' : '';
			$forumcache .= "<option value=\"$value[cid]\"$slted>$add $value[name]</option>";
		}
		foreach ($dirdb as $value) {
			$slted = ($itemdb['dirid']==$value['typeid']) ? ' SELECTED' : '';
			$itemcache .= "<option id=\"dirop$value[typeid]\" value=\"$value[typeid]\"$slted>$value[name]</option>";
			$itemarray[$value['typeid']] = array('name' => $value['name'],'vieworder' => (int)$value['vieworder']);
		}
		$tagdisplay = 'none';
		if ($itemdb['tags']) {
			$tagname = explode(',',$itemdb['tags']);
			$tagname[0] && $tagdisplay = '';
		}
		${'ifhide_'.(int)$itemdb['ifhide']} = ' SELECTED';
		$itemdb['uploads'] && $uploaddb = unserialize($itemdb['uploads']);
		if ($type == 'file') {
			$filetype = $itemdb['type'];
			unset($itemdb['type']);
		}
		@extract($itemdb); unset($itemdb);
		/*
		if ($type == 'file') {
			$absoluteurl = $absoluteurl ? unserialize($absoluteurl) : array();
			$osdb = explode(',',$os);
			foreach($osdb as $value){
				${'os_'.$value} = 'CHECKED';
			}
			unset($osdb);
		} elseif ($type == 'goods') {
			${'quality_'.$quality} = ${'feemode_'.$feemode} = 'CHECKED';
		} elseif ($type == 'music') {
			$musicurl = $musicurl ? unserialize($musicurl) : array();
		} elseif ($type == 'photo') {
			$absoluteurl = $absoluteurl ? unserialize($absoluteurl) : array();
		}
		*/
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
	}elseif($type == 'music'){
		InitGP('mid');
		$musicdb = $db->get_one("SELECT * FROM pw_music WHERE mid='$mid'");
	    (!$musicdb || $musicdb['uid']!=$admin_uid) && usermsg('modify_error');
	    $name = $musicdb['name'];
	    $singer = $musicdb['singer'];
	    $murl = $musicdb['murl'];
	    $tags = $musicdb['tags'];
	    $descrip = $musicdb['descrip'];
	}
	require_once PrintEot('post');footer();
} else {
	InitGP(array('gdcode','qanswer','qkey'),'P');
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
	if($type == 'blog'){
		InitGP(array('atc_ifsign','atc_autourl','atc_iconid1','atc_iconid2','atc_cid','atc_dirid','atc_tagdb','atc_allowreply','atc_ifhide','atc_bbsfid','atc_teamid','atc_pushlog'),'P');
		$updatefeile = '';
		$type != 'blog' && !$updatefeile && usermsg('undefined_action');

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
		$atc_allowreply = (int)$atc_allowreply;
		$atc_ifhide 	= (int)$atc_ifhide;
		$atc_bbsfid 	= (int)$atc_bbsfid;
		$ipfrom 		= cvipfrom($onlineip);
		$atc_iconid     = $atc_iconid1.','.$atc_iconid2;
		if ((int)$atc_oldcid != $atc_cid && $itemdb['ifcheck']) {
			$db->update("UPDATE pw_categories SET counts=counts-1 WHERE cid='$atc_oldcid'");
			$db->update("UPDATE pw_categories SET counts=counts+1 WHERE cid='$atc_cid'");
			updatecache_cate($type);
		}
		$db->update("UPDATE pw_items SET cid='$atc_cid',dirid='$atc_dirid',icon='$atc_iconid',subject='$atc_title',allowreply='$atc_allowreply',ifwordsfb='$ifwordsfb',ifhide='$atc_ifhide' WHERE itemid='$itemid'");
		$newtagdb = array();
		if (is_array($atc_tagdb)) {
			foreach ($atc_tagdb as $key => $value) {
				is_numeric($key) && $value && $newtagdb[$key] = $value;
			}
			$atc_tagdb = implode(',',array_unique($newtagdb));
		}
		UploadSQL($admin_uid,$itemid,$atc_cid,$type);
		$db->update("UPDATE pw_$type SET tags='$atc_tagdb',ifsign='$atc_ifsign'$updatefeile,ifconvert='$ifconvert',content='$atc_content' WHERE itemid='$itemid'");
		updatecache_cate($type);
		if($db_teamifopen == '1'){
			!is_array($atc_teamid) && $atc_teamid = array();
			foreach ($atc_teamid as $value) {
				if(teamcheck($itemid,$value,$type)){
					continue;
				}
				$ckteamid[] = $value;
			}
		}
		$ckteamid > 0 && pushitem($itemid,$atc_title,$ckteamid,$type,$pushlog);
		usermsg('operate_success',"$user_file?action=itemcp&type=$type");
	}elseif($type == 'music'){
		InitGP(array('mid','name','singer','murl','tags','descrip','oldtags'),'P');
		!preg_match('/^http(s)?:\/\//i',$murl) && usermsg('url_error');
		if(!$name || strlen($vname)>$db_titlemax){
			usermsg('title_limit');
		}
		if(strlen($descrip)>$db_postmax){
			usermsg('content_limit');
		}
		$ifwordsfb = 0;
		$ckname = $name;
		$ckdescrip = $descrip;
		$cktags = $tags;
		foreach ($_FORBIDDB as $fvalue) {
			$ckname = N_strireplace($fvalue['word'],$fvalue['wordreplace'],$name);
			$ckdescrip = N_strireplace($fvalue['word'],$fvalue['wordreplace'],$descrip);
			$cktags = N_strireplace($fvalue['word'],$fvalue['wordreplace'],$tags);
		}
		if ($ckname != $name) {
			$name = $ckname;
			$ifwordsfb = 1;
		}
		if ($ckdescrip != $descrip) {
			$descrip = $ckdescrip;
			$ifwordsfb = 1;
		}
		if ($cktags != $tags) {
			$tags = $cktags;
			$ifwordsfb = 1;
		}
		$db->update("UPDATE pw_music SET name='$name',murl='$murl',singer='$singer',tags='$tags',descrip='$descrip' WHERE mid='$mid'");
		if(!empty($tags)){
			$cktags = $db->get_one("SELECT * FROM pw_btags WHERE tagname='$tags'");
			if(!empty($cktags)){
			   $db->update("UPDATE pw_btags SET musicnum=musicnum+1 WHERE tagname='$tags'");
			}else{
			   $db->update("INSERT INTO pw_btags(uid,tagname,musicnum) VALUES ('$admin_uid','$tags','1')");
			}
			if(!empty($oldtags) && $oldtags != $tags){
				$db->update("UPDATE pw_taginfo SET tagname='$tags' WHERE itemid='$mid' AND tagtype='music'");
				$db->update("UPDATE pw_btags SET musicnum=musicnum-1 WHERE tagname='$tags'");
				//$cktags = $db->get_one("SELECT * FROM pw_btags WHERE tagname='$tags'");
				//if(!empty($cktags)){
					//$db->update("UPDATE pw_btags SET musicnum=musicnum+1 WHERE tagename='$tags'");
				//}else{
					//$db->update("INSERT INTO pw_btags(uid,tagname,musicnum) VALUES ('$admin_uid','$tags','1')");
				///}
			}elseif(empty($oldtags)){
			   $tagid = $db->get_value("SELECT tagid FROM pw_btags WHERE tagname='$tags'");
			   $db->update("INSERT INTO pw_taginfo (tagid,tagname,uid,author,itemid,tagtype,addtime) VALUES ('$tagid','$tags','$admin_uid','$admin_name','$mid','music','$timestamp')");
			}
		}else{
			if(!empty($oldtags)){
				$db->update("DELETE FROM pw_taginfo WHERE itemid='$mid' AND tagtype='music'");
				$db->update("UPDATE pw_btags SET musicnum=musicnum-1 WHERE tagname='$oldtags'");
			}
		}
		usermsg('operate_success',"$basename&job=$job&mid=$mid&");
	}elseif($type == 'bookmark'){
		InitGP(array('bookmarkurl','atc_title','atc_content','atc_dirid','atc_ifhide'),'P');
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
		$db->update("UPDATE pw_items SET dirid='$atc_dirid',subject='$atc_title',ifwordsfb='$ifwordsfb',ifhide='$atc_ifhide' WHERE itemid='$itemid'");
		$db->update("UPDATE pw_bookmark SET bookmarkurl='$bookmarkurl',content='$atc_content' WHERE itemid='$itemid'");
		usermsg('operate_success',"$user_file?action=itemcp&type=$type");
	}
}
?>