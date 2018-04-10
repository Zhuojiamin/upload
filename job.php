<?php
require_once("global.php");
require_once(R_P."mod/page_mod.php");
if($action == 'verify'){
	$rt=$db->get_one("SELECT verify FROM pw_user WHERE uid='$r_uid'");
	if($rt){
		if($pwd == $rt['verify']){
			$db->update("UPDATE pw_user SET verify=1 WHERE uid='$r_uid'");
			Showmsg('reg_jihuo_success');
		} else{
			Showmsg('reg_jihuo_fail');
		}
	} else{
		Showmsg('reg_jihuo_fail');
	}
} elseif($action == 'jointeam'){
	if($groupid == 2){
		Showmsg('not_login');
	} else{
		$teamid = GetGP('teamid','G');
		$teamid=(int)$teamid;
		$teamdb=$db->get_one("SELECT teamid,type FROM pw_team WHERE teamid='$teamid'");
		if($teamdb){
			$rt=$db->get_one("SELECT teamid,ifcheck FROM pw_tuser WHERE teamid='$teamid' AND uid='$winduid'");
			if($rt && $rt['ifcheck'] == '1'){
				Showmsg('have_join');
			}elseif($rt && $rt['ifcheck'] == '0'){
				Showmsg('join_success_notcheck');
			}
			if($teamdb['type']){
				$ifcheck=1;
				$db->update("UPDATE pw_team SET bloggers=bloggers+1 WHERE teamid='$teamid'");
				$db->update("INSERT INTO pw_tuser(uid,teamid,joindate,ifcheck) VALUES('$winduid','$teamid','$timestamp','$ifcheck')");
				Showmsg('join_success');
			} else{
				$ifcheck=0;
				$db->update("INSERT INTO pw_tuser(uid,teamid,joindate,ifcheck) VALUES('$winduid','$teamid','$timestamp','$ifcheck')");
				Showmsg('join_success_notcheck');
			}
		} else{
			Showmsg('undefined_action');
		}
	}
}elseif($action=='switch'){
	!$winduid && Showmsg('undefined_action');
	$cur=$cur==0 ? 1 : 0;
	$db->update("UPDATE pw_user SET editor='$cur' WHERE uid='$winduid'");
	$query_string=str_replace('&#61;','=',$query_string);
	refreshto("user_index.php?$query_string",'operate_success');
}elseif($action=='download'){
	$filedb = array();
	$aid = GetGP('aid','G');
	$_GROUP['allowdown'] == '0' && Showmsg('job_attach_group_right');
	if(is_numeric($aid)){
		$filedb = $db->get_one("SELECT * FROM pw_upload WHERE aid='$aid'");
		if(!$filedb['attachurl'] || strpos(dirname($filedb['attachurl'] ),'..')!==false){
			Showmsg('job_attach_error');
		}
		//@extract($rt);
	} else{
		Showmsg('job_attach_error');
	}
	if(!$attach_url && !is_readable(R_P.$attachpath.'/'.$filedb['attachurl'])){
		Showmsg('job_attach_error');
	}
	$filedb['attachurl'] = R_P.$attachpath.'/'.$filedb['attachurl'];
	Download_file($filedb);exit;
}elseif($action=='addfriend'){
	if(empty($touid) || empty($winduid)){
		Showmsg('job_empuid');
	}
	$winduid==$touid && Showmsg('job_ownererro');
	$rt=$db->get_one("SELECT fuid FROM pw_blogfriend WHERE fuid='$touid' AND uid='$winduid'");
	$rt && Showmsg('job_havefriend');
	$db->update("INSERT INTO pw_blogfriend(uid,fuid,fdate) VALUES('$winduid','$touid','$timestamp')");
	Showmsg('job_friendcom');
}
function Download_file($filedb,$downsize="20000") {
	//function by noizy
	set_time_limit(0);
	$http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$path		= P_pathinfo($filedb['attachurl']);
	$fname		= &$filedb['name'];
	$fsize		= sprintf("%u",filesize($filedb['attachurl']));
	$attachment	= 'attachment';
	if (isset($http_user_agent)) {
		if (strpos($http_user_agent, 'msie')!==false) {
			ini_set('zlib.output_compression','Off');
			$path['extension']=='torrent' && $attachment='inline';
			$fname = rawurldecode($filedb['name']);
			$type = 'application/octetstream';
		} elseif (strpos($http_user_agent, 'opera')!==false) {
			$type = 'application/octetstream';
		} else {
			$type = 'application/octet-stream';
		}
	}
	unset($path);
	$fp = '';
	file_exists($filedb['attachurl']) && $fp = fopen($filedb['attachurl'], "rb");
	if (empty($fp)) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	if ($_SERVER['HTTP_RANGE']) {
		if (!preg_match("/^bytes=(\\d+)-(\\d*)$/", $_SERVER['HTTP_RANGE'], $matches)) {
			header("HTTP/1.1 500 Internal Server Error");
			exit;
		}
		$ffrom	= $matches[1];
		$fto	= $matches[2];
		empty($fto) && $fto = $fsize - 1;
		$content_size = $fto - $ffrom + 1;
		header('HTTP/1.1 206 Partial Content');
		header("Content-Range: $ffrom-$fto/$fsize");
		header('Content-Length: '.$content_size);
		header('Content-Type: '.$type);
		header('Content-Disposition: '.$attachment.'; filename='.$fname);
		header('Content-Transfer-Encoding: binary');
		header('X-Powered-By: LxBlog/Noizy');
		fseek($fp,$ffrom);
		$cur_pos = ftell($fp);
		while ($cur_pos !== false && ftell($fp) + $downsize < $fto+1) {
			$downcontent = fread($fp, $downsize);
			echo $downcontent;
			$cur_pos = ftell($fp);
			flush();
		}
		$downcontent = fread($fp, $fto+1-$cur_pos);
		echo $downcontent;
		fclose($fp);
	} else {
		while (ob_get_length() !== false) @ob_end_clean();
		header('HTTP/1.1 200 OK');
		header('Content-Length: '.$fsize);
		header('Content-Type: '.$type);
		header('Content-Disposition: '.$attachment.'; filename='.$fname);
		header('Content-Transfer-Encoding: binary');
		header("Content-Encoding: none");
		header('X-Powered-By: LxBlog/Noizy');
		while ($downcontent = fread($fp, $downsize)) {
			echo $downcontent;
			flush();
		}
		fclose($fp);
	}
}
function P_pathinfo($filepath,$dot=".") {
	if (function_exists('pathinfo')) {
		$path = pathinfo($filepath);
		!$path['filename'] && $path['filename'] = substr($path['basename'],0,strlen(strrchr($filepath,$dot)));
	} else {
		$path['dirname']	= dirname($filepath);
		$path['basename']	= basename($filepath);
		$path['extension']	= array_pop(explode($dot,basename($filepath)));
		$path['filename']	= basename($filepath,strrchr($filepath,$dot)) ?
		basename($filepath,strrchr($filepath,$dot)) :
		substr(basename($filepath),0,strlen(strrchr($filepath,$dot)));
	}
	return $path;
}
?>