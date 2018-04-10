<?php
!function_exists('readover') && exit('Forbidden');

function gethottag($orderby,$num){
	global $db;
	$tagdb = array();
	$query = $db->query("SELECT tagname,tagid FROM pw_btags WHERE iflock!=1 ORDER BY $orderby DESC LIMIT 0,$num");
	while ($rt = $db->fetch_array($query)) {
		$tagdb[$rt['tagid']] = $rt['tagname'];
	}
	return $tagdb;
}
function getclttag($uid,$num){
	global $db;
	$tagdb = array();
	$query = $db->query("SELECT t.tagname FROM pw_collections c LEFT JOIN pw_btags t ON c.itemid=t.tagid WHERE c.uid='$uid' AND c.type='tag' ORDER BY adddate DESC LIMIT 0,$num");
	while ($rt = $db->fetch_array($query)) {
		$tagdb[] = $rt['tagname'];
	}
	return $tagdb;
}
function getteamop($uid){
	global $db;
	$teamsel = array();
	$query = $db->query("SELECT t.teamid,t.name FROM pw_tuser tu LEFT JOIN pw_team t ON t.teamid=tu.teamid WHERE tu.uid='$uid' AND tu.ifcheck='1'");
	while ($rt = $db->fetch_array($query)) {
		$teamsel[] = array('teamid' => $rt['teamid'],'name' => $rt[name]);
	}
	return $teamsel;
}
function ConCheck($subject,$content){
	global $db_titlemax,$db_postmin,$db_postmax,$picpath,$attachpath,$db_blogurl;
	$subject = trim($subject);
	if (!$subject || strlen($subject) > $db_titlemax) {
		Showtruemsg('title_limit');
		return false;
	}
	if (strlen(trim($content)) > $db_postmax || strlen(trim($content)) < $db_postmin) {
		Showtruemsg('content_limit');
		return false;
	}
	$subject = Char_cv($subject);
	$_POST['atc_autourl'] && $content = AutoUrl($content);
	(strpos($content,$db_blogurl)!==false) && $content = str_replace(array($picpath,$attachpath),array('p_w_picpath','p_w_upload'),$content);
	return array($subject,$content);
}
function AutoUrl($message){
	global $db_autoimg;
	if ($db_autoimg==1) {
		$message = preg_replace(
			array(
				"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+\.gif)/i",
				"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+\.jpg)/i"
			),
			array(
				'[img]\\1\\3[/img]',
				'[img]\\1\\3[/img]'
			),
			' '.$message
		);
	}
	$message = preg_replace(
		array(
			"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+)/i",
			"/(?<=[^\]a-z0-9\/\-_.~?=:.])([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/i"
		),
		array(
			'[url]\\1\\3[/url]',
			'[email]\\0[/email]'
		),
		' '.$message
	);
	$message = substr($message,1);
	return $message;
}
function Atc_cv($message,$usesign){
	if ($usesign<2) {
		$message = Char_cv($message);
	} else {
		$message = preg_replace(
			array('/<script.*>.*<\/script>/is',"/<(([^\"']|\"[^\"]*\"|'[^']*')*?)>/eis"),
			array('',"jscv('\\1')"),
			str_replace('.','&#46;',$message)
		);
	}
	return $message;
}
function jscv($code){
	$code = str_replace('\\"','"',$code);
	$code = preg_replace(
		array('/[\s]on[\w]+\s*=\s*(\\\"|\\\\\').+?\\1/is','/[\s]on[\w]+\s*=[^\s]*/is'),
		'',
		$code
	);
	return '<'.$code.'>';
}
function pushitem($itemid,$title,$teamid,$type,$pushlog = null){
	global $db,$admin_uid,$timestamp;
	$teamids = $extra = '';
	!is_array($teamid) && $teamid = array();
	foreach ($teamid as $value) {
		if ((int)$value > 0) {
			$teamids .= ($teamids ? ',' : '')."'$value'";
		}
	}
	$teamids = strpos($teamids,',')===false ? "=$teamids" : " IN ($teamids)";
	$query = $db->query("SELECT t.name,t.teamid FROM pw_tuser tu LEFT JOIN pw_team t ON t.teamid=tu.teamid WHERE tu.uid='$admin_uid' AND tu.teamid{$teamids} AND tu.ifcheck='1'");
	while($rt=$db->fetch_array($query)){
		if (empty($rt[name])) continue;
		$pushlogdb .= (!empty($pushlog) ? $pushlog : '')."$rt[teamid],$rt[name]\t";
		if($type == 'blog'){
			$L_JOIN = 'LEFT JOIN pw_items i ON tb.itemid=i.itemid';
			$db->update("UPDATE pw_blog SET pushlog='$pushlogdb' WHERE itemid='$itemid'");
			$db->update("UPDATE pw_team SET blogs=blogs+1,items=items+1,lastid='$itemid' WHERE teamid='$rt[teamid]'");
		}elseif($type == 'photo'){
			$L_JOIN = 'LEFT JOIN pw_albums i ON tb.itemid=i.aid';
			$db->update("UPDATE pw_albums SET pushlog='$pushlogdb' WHERE aid='$itemid'");
			$db->update("UPDATE pw_team SET albums=albums+1,items=items+1,lastid='$itemid' WHERE teamid='$rt[teamid]'");
		}elseif($type == 'music'){
			$L_JOIN = 'LEFT JOIN pw_malbums i ON tb.itemid=i.maid';
			$db->update("UPDATE pw_malbums SET pushlog='$pushlogdb' WHERE maid='$itemid'");
			$db->update("UPDATE pw_team SET malbums=malbums+1,items=items+1,lastid='$itemid' WHERE teamid='$rt[teamid]'");
		}
		$db->update("INSERT INTO pw_tblog(itemid,uid,teamid,postdate,subject,type) VALUES('$itemid','$admin_uid','$rt[teamid]','$timestamp','$title','$type')");
		$team = $db->get_one("SELECT t.teamid,i.cid,t.name,bloggers,t.blogs,t.albums,t.malbums,tb.itemid,tb.subject,tb.type FROM pw_team t LEFT JOIN pw_tblog tb ON t.teamid=tb.teamid {$L_JOIN} WHERE t.teamid='$rt[teamid]' ORDER BY tb.postdate DESC LIMIT 0,1");
	$query1 = $db->query("SELECT uid FROM pw_tuser WHERE admin=$admin_uid AND teamid='$rt[teamid]' AND ifcheck='1'");
	while($user = $db->fetch_array($query1)){
		$teamdb = $db->get_value("SELECT teamdb FROM pw_userinfo WHERE uid='$user[uid]'");
		$teamdb = unserialize($teamdb);
		empty($teamdb) && $teamdb = array();
		$teamdb[$rt[teamid]]['teamid'] = $team[teamid];
		$teamdb[$rt[teamid]]['name'] = $team[name];
		$teamdb[$rt[teamid]]['cid'] = $team[cid];
		$teamdb[$rt[teamid]]['bloggers'] = $team[bloggers];
		$teamdb[$rt[teamid]]['cid'] = $team[cid];
		$teamdb[$rt[teamid]]['blogs'] = $team[blogs];
		$teamdb[$rt[teamid]]['albums'] = $team[albums];
		$teamdb[$rt[teamid]]['malbums'] = $team[malbums];
		$teamdb[$rt[teamid]]['itemid'] = $team[itemid];
		$teamdb[$rt[teamid]]['subject'] = $team[subject];
		$teamdb[$rt[teamid]]['type'] = $team[type];
		Strip_S($teamdb);
		$teamdb && $teamdb = addslashes(serialize($teamdb));
		$db->update("UPDATE pw_userinfo SET teamdb='$teamdb' WHERE uid='$user[uid]'");
		unset($teamdb);
	}
		unset($team);
}
	unset($pushlogdb);
	return true;
	/*
	$name = $db->get_value("SELECT t.name FROM pw_tuser tu LEFT JOIN pw_team t ON t.teamid=tu.teamid WHERE tu.uid='$admin_uid' AND tu.teamid='$teamid' AND tu.ifcheck='1'");
	if (!$name) {
		return false;
	}
	$pushlogdb = (!empty($pushlog) ? $pushlog : '')."$teamid,$name\t";
	if($type == 'blog'){
		$L_JOIN = 'LEFT JOIN pw_items i ON tb.itemid=i.itemid';
		$db->update("UPDATE pw_blog SET pushlog='$pushlogdb' WHERE itemid='$itemid'");
		$db->update("UPDATE pw_team SET blogs=blogs+1,items=items+1,lastid='$itemid' WHERE teamid='$teamid'");
	}elseif($type == 'photo'){
		$L_JOIN = 'LEFT JOIN pw_albums i ON tb.itemid=i.aid';
		$db->update("UPDATE pw_albums SET pushlog='$pushlogdb' WHERE aid='$itemid'");
		$db->update("UPDATE pw_team SET albums=albums+1,items=items+1,lastid='$itemid' WHERE teamid='$teamid'");
	}elseif($type == 'music'){
		$L_JOIN = 'LEFT JOIN pw_malbums i ON tb.itemid=i.maid';
		$db->update("UPDATE pw_malbums SET pushlog='$pushlogdb' WHERE maid='$itemid'");
		$db->update("UPDATE pw_team SET malbums=malbums+1,items=items+1,lastid='$itemid' WHERE teamid='$teamid'");
	}
	$db->update("INSERT INTO pw_tblog(itemid,uid,teamid,postdate,subject,type) VALUES('$itemid','$admin_uid','$teamid','$timestamp','$title','$type')");
	*/
}

function UserCheck($pwuser){
	global $db;
	$pwuser = trim($pwuser);
	$uid = $db->get_value("SELECT uid FROM pw_user WHERE username='$pwuser'");
	if(!$uid){
		Showtruemsg('user_not_exists');
		return false;
	}
	return array($uid,$pwuser);
}

function teamcheck($itemid,$teamid,$type){
	global $db;
	if($type == 'blog'){
		$pushlog = $db->get_value("SELECT pushlog FROM pw_blog WHERE itemid='$itemid'");
	}elseif($type == 'photo'){
		$pushlog = $db->get_value("SELECT pushlog FROM pw_albums WHERE aid='$itemid'");
	}elseif($type == 'music'){
		$pushlog = $db->get_value("SELECT pushlog FROM pw_malbums WHERE maid='$itemid'");
	}
	$pushlog = explode("\t",$pushlog);
	foreach($pushlog as $key => $value){
		list($pteamid) = explode(',',$value);
		$pteamiddb[] = $pteamid;
	}
	if(in_array($teamid,$pteamiddb)){
		return true;
	}
}
?>