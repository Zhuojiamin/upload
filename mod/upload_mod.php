<?php
!function_exists('readover') && exit('Forbidden');

function UploadSQL($uid,$itemid,$cid=0,$atype = null,$mode = null){
	global $db,$attachdb,$timestamp,$atc_content;
	$uploaddb = !empty($_FILES) ? UploadFile($uid,$mode) : array();
	$aid = '';
	$bakdb = $returndb = array();
	if (empty($uploaddb)) {
		if (!empty($attachdb)) {
			$uploaddb = $attachdb;
			$aids = '';
			foreach ($uploaddb as $value) {
				$value = (int)$value;
				if ($value > 0) {
					$aids .= ($aids ? ',' : '')."'$value'";
				}
			}
			if($atype == 'blog'){
				$aids && $db->update("UPDATE pw_upload SET cid='$cid',itemid='$itemid',atype='$atype',state='1' WHERE aid IN ($aids)");
			}elseif($atype == 'photo'){
				$aids && $db->update("UPDATE pw_photo SET cid='$cid',aid='$itemid' WHERE pid IN ($aids)");
			}
		}
	} else {
		if($atype == 'blog'){
			$aids = '';
			foreach ($uploaddb as $key => $value) {
				$value['name'] = addslashes($value['name']);
				$state = 1;
				if ((int)$itemid == 0 || (int)$cid == 0) {
					$atype = '';
					$state = $itemid = $cid = 0;
				}
				$db->update("INSERT INTO pw_upload SET cid='$cid',itemid='$itemid',uid='$uid',name='$value[name]',type='$value[type]',size='$value[size]',attachurl='$value[attachurl]',uploadtime='$timestamp',descrip='$value[descrip]',atype='$atype',state='$state',ifthumb='$value[ifthumb]'");
				$aid = $db->insert_id();
				$bakdb[$aid] = array(
					'aid'		=> $aid,
					'name'		=> stripslashes($value['name']),
					'type'		=> $value['type'],
					'attachurl' => $value['attachurl'],
					'size'		=> $value['size'],
					'hits'		=> $value['hits'],
					'desc'		=> $value['descrip']
				);
				$atc_content = str_replace("[upload=$value[id]]","[attachment=$aid]",$atc_content);
			}
		}elseif($atype == 'photo'){
			$pids = '';
			$allowreply = $db->get_value("SELECT allowreply FROM pw_albums WHERE aid='$itemid'");
			foreach ($uploaddb as $key => $value) {
				$value['name'] = addslashes($value['name']);
				$state = 1;
				if ((int)$itemid == 0 || (int)$cid == 0) {
					$state = $itemid = $cid = 0;
				}
				$db->update("INSERT INTO pw_photo SET cid='$cid',aid='$itemid',uid='$uid',name='$value[name]',attachurl='$value[attachurl]',uploadtime='$timestamp',tags='$value[tags]',descrip='$value[descrip]',pallowreply='$allowreply',ifthumb='$value[ifthumb]'");
				$pid = $db->insert_id();
				$returndb[$pid] = array(
					'pid'		=> $pid,
					'name'		=> stripslashes($value['name']),
					'tags'      => $value['tags'],
					'attachurl' => $value['attachurl'],
					'desc'		=> $value['descrip'],
					'ifthumb'   => $value[ifthumb]
				);
			}
			$query = $db->query("SELECT pid,name,tags,uploadtime,phits,attachurl,descrip,ifthumb FROM pw_photo WHERE aid='$itemid'");
			while ($rt = $db->fetch_array($query)) {
				$bakdb[$rt['pid']] = array(
					'pid'		=> $rt['pid'],
					'name'		=> stripslashes($rt['name']),
					'tags'		=> $rt['tags'],
					'attachurl' => $rt['attachurl'],
					'phits'		=> $rt['phits'],
					'descrip'	=> $rt['descrip'],
					'ifthumb'   => $rt['ifthumb']
				);
			}
			empty($bakdb) && $bakdb=$returndb;
			$uploads = !empty($bakdb) ? addslashes(serialize($bakdb)) : '';
			(int)$itemid > 0 && $db->update("UPDATE pw_albums SET uploads='$uploads' WHERE aid='$itemid'");
		}
	}
	if($atype == 'blog'){
		$where = '';
		if (!$aids) {
			(int)$itemid > 0 && $where = "itemid = '$itemid'";
		} else {
			$where = "aid IN ($aids)";
		}
		$where && $aid = '';
		if ($where) {
			$query = $db->query("SELECT aid,name,type,size,hits,attachurl,descrip FROM pw_upload WHERE $where");
			while ($rt = $db->fetch_array($query)) {
				$returndb[$rt['aid']] = array(
					'aid'		=> $rt['aid'],
					'name'		=> stripslashes($rt['name']),
					'type'		=> $rt['type'],
					'attachurl' => $rt['attachurl'],
					'size'		=> $rt['size'],
					'hits'		=> $rt['hits'],
					'desc'		=> $rt['descrip']
				);
			}
			$db->free_result($query);
		} elseif ($aid && !empty($bakdb)) {
			$returndb = $bakdb;
		}
		$uploads = !empty($returndb) ? addslashes(serialize($returndb)) : '';
		(int)$itemid > 0 && $db->update("UPDATE pw_items SET uploads='$uploads' WHERE itemid='$itemid'");
	}
	return $returndb;
}
function UploadFile($uid,$mode = null){
	global $_GROUP,$db,$admin_uid,$db_attachnum,$db_uploadmaxsize,$db_uploadfiletype,$timestamp,$db_attachdir,$attachpath,$attachdir,$db_thumbifopen,$db_thumbwh;
	$filedb = $attachdb = $descdb = array();
	foreach ($_FILES as $key => $value) {
		$i = (int)substr($key,11);
		if (!empty($mode) && $i != $mode) continue;
		$tmp_name = is_array($value) ? $value['tmp_name'] : ${$key};
		$descdb[$key] = Char_cv($_POST['atc_desc'.$i]);
		$tagdb[$key] = Char_cv($_POST['atc_tags'.$i]);
		$i > 0 && $i <= $db_attachnum && if_uploaded_file($tmp_name) && $filedb[$key] = $value;
	}
	unset($_FILES);
	foreach ($filedb as $key => $value) {
		$i = (int)substr($key,11);
		if (is_array($value)) {
			$atc_attachment = $value['tmp_name'];
			$atc_attachment_name = $value['name'];
			$atc_attachment_size = $value['size'];
		} else {
			$atc_attachment = ${$key};
			$atc_attachment_name = ${$key.'_name'};
			$atc_attachment_size = ${$key.'_size'};
		}
		$atc_attachment_size > $db_uploadmaxsize && Uploadmsg('upload_size_error',$i);
		@extract($db->get_one("SELECT SUM(size) AS tsizes FROM pw_upload WHERE uid='$admin_uid'"));
		$_GROUP['uploadsize'] && $tsizes >= $_GROUP['uploadsize'] && Uploadmsg('upload_size_limit',$i);
		$extdb = explode(' ',strtolower($db_uploadfiletype));
		$attach_ext = strtolower(substr(strrchr($atc_attachment_name,'.'),1));
		(!$attach_ext || !N_InArray($attach_ext,$extdb)) && Uploadmsg('upload_type_error',$i);
		$attach_ext = preg_replace("/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i","scp_\\1",$attach_ext);
		$randvar = substr(md5($timestamp+$i),10,15);
		$fileurl = "{$uid}_{$randvar}";
		if ($attachdir == R_P.$attachpath) {
			$savedir = '';
			if ($db_attachdir == '2') {
				$savedir = 'Type_'.$attach_ext;
			} elseif ($db_attachdir == '3') {
				$savedir = 'Mon_'.date('ym');
			} elseif ($db_attachdir == '4') {
				$savedir = 'Day_'.date('ymd');
			}
			if ($savedir) {
				if (!is_dir("$attachdir/$savedir")) {
					@mkdir("$attachdir/$savedir");
					@chmod("$attachdir/$savedir",0777);
					@fclose(@fopen("$attachdir/$savedir".'/index.html','w'));
					@chmod("$attachdir/$savedir".'/index.html',0777);
				}
				$fileurl = $savedir.'/'.$fileurl;
			}
		}
		$source = "$attachdir/{$fileurl}.{$attach_ext}";
		if (strpos($source,'..')!==false || strpos($source,'.php.')!==false || eregi("\.php$",$source)) {
			Uploadmsg('upload_error',$i);
		}
		$thumburl = "$attachdir/{$fileurl}_thumb.{$attach_ext}";
		if (function_exists('move_uploaded_file') && @move_uploaded_file($atc_attachment,$source)) {
			@chmod($source,0777);
		} elseif (@copy($atc_attachment, $source)) {
			@chmod($source,0777);
		} elseif (is_readable($atc_attachment)) {
			writeover($source,readover($atc_attachment));
			!file_exists($source) && Uploadmsg('upload_error',$i);
			@chmod($source,0777);
		} else {
			Uploadmsg('upload_error',$i);
		}
		$type = 'zip';
		if (in_array($attach_ext,array('txt','zip','rar','gif','jpg','png','bmp','swf'))) {
			if (in_array($attach_ext,array('gif','jpg','png','bmp','swf'))) {
				if (!GetImgSize($source,$attach_ext)) {
					P_unlink($source);
					Uploadmsg('upload_content_error',$i);
				}
				$attach_ext != 'swf' && $type = 'img';
			} elseif ($attach_ext == 'txt') {
				$safecheckdb = readover($source);
				if (strpos($safecheckdb,'onload')!==false && strpos($safecheckdb,'submit')!==false && strpos($safecheckdb,'post')!==false && strpos($safecheckdb,'form')!==false) {
					P_unlink($source);
					Uploadmsg('upload_content_error',$i);
				}
				$type = 'txt';
			}
		}
		$ifthumb = 0;
		if ($type=='img' && $db_thumbifopen) {
			require_once(R_P.'mod/makethumb.php');
			list($db_thumbw,$db_thumbh) = explode("\t",$db_thumbwh);
			MakeThumb($source,$thumburl,$db_thumbw,$db_thumbh,$db_thumbcolor) && $ifthumb = 1;
		}
		$size = ceil(filesize($source)/1024);
		$name = addslashes($atc_attachment_name);
		$fileuplodeurl = "{$fileurl}.{$attach_ext}";
		$descrip = $descdb[$key];
		$tags = $tagdb[$key];
		$attachdb[] = array(
			'id'       	=> $i,
			'name'		=> stripslashes($name),
			'type'		=> $type,
			'size'		=> $size,
			'attachurl' => $fileuplodeurl,
			'descrip'	=> $descrip,
			'tags'      => $tags,
			'ifthumb'	=> $ifthumb,
		);
	}
	return $attachdb;
}
function if_uploaded_file($tmp_name){
	if (!$tmp_name || $tmp_name=='none') {
		return false;
	} elseif (function_exists('is_uploaded_file') && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))) {
		return false;
	} else {
		return true;
	}
}
function Uploadmsg($msg,$num){
	if (function_exists('usermsg') && !defined('AJAXUSER')) {
		usermsg($msg);
	} elseif (function_exists('adminmsg') && !defined('AJAXADMIN')) {
		adminmsg($msg);
	} elseif (function_exists('Showmsg') && !defined('AJAX')) {
		Showmsg($msg);
	} else {
		echo "<script language=\"JavaScript1.2\">parent.Showmsg('$msg','$num');</script>";exit;
	}
}
function GetImgSize($srcFile,$srcExt = null){
	empty($srcExt) && $srcExt = strtolower(substr(strrchr($srcFile,'.'),1));
	$srcdata = array();
	if (function_exists('read_exif_data') && ($srcExt=='jpg' || $srcExt=='jpeg' || $srcExt=='jpe' || $srcExt=='jfif')) {
		$datatemp = read_exif_data($srcFile);
		$srcdata['width'] = $datatemp['COMPUTED']['Width'];
		$srcdata['height'] = $datatemp['COMPUTED']['Height'];
		$srcdata['type'] = 2;
		unset($datatemp);
	} else {
		list($srcdata['width'],$srcdata['height'],$srcdata['type']) = getimagesize($srcFile);
	}
	empty($srcdata) && $srcdata = false;
	return $srcdata;
}

function uploadsavepath($attach_ext){
	global $db_attachdir,$winduid,$attachpath,$timestamp,$attthumburl;
	$fileuplodeurl = "{$winduid}_".substr(md5($timestamp),10,15);
	if($db_attachdir) {
		switch($db_attachdir) {
			case 2: $savedir = 'Type_'.$attach_ext; break;
			case 3: $savedir = 'Mon_'.get_date($timestamp,'ym'); break;
			case 4: $savedir = 'Day_'.get_date($timestamp,'ymd'); break;
		}
		if(!is_dir(R_P."$attachpath/$savedir")) {
			@mkdir(R_P."$attachpath/$savedir");
			@chmod(R_P."$attachpath/$savedir", 0777);
			@fclose(@fopen(R_P."$attachpath/$savedir".'/index.html', 'w'));
			@chmod(R_P."$attachpath/$savedir".'/index.html', 0777);
		}
		$fileuplodeurl = $savedir.'/'.$fileuplodeurl;
	}
	$attthumburl=$fileuplodeurl.'_thumb'.".$attach_ext";
	return $fileuplodeurl.".$attach_ext";
}
?>