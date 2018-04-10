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
define('AJAXADMIN',true);

require_once(R_P.'admin/admincp.php');
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
		exit;
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
		foreach ($uploaddb as $value) {
			$aid = $value['aid'];
			$name = $value['name'];
			$size = $value['size'];
			$desc = $value['desc'];
			$url = "$attachpath/$value[attachurl]";
			break;
		}
		echo "<script language=\"JavaScript1.2\">parent.UploadFileResponse('$mode','$aid','$size','$desc','$name','$url');</script>";exit;
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
		UploadSQL($uid,$itemid);
		echo "<script language=\"JavaScript1.2\">parent.DeleteFileResponse('$aid');</script>";exit;
	}
}
if (!function_exists('P_unlink')) {
	function P_unlink($filename){
		strpos($filename,'..')!==false && exit('Forbidden');
		@unlink($filename);
	}
}
function ckrightset($rightset,$action,$job = null){
	$rightset = array_keys($rightset);
	$check = !empty($job) ? $action.'_'.$job : '';
	foreach ($rightset as $value) {
		if (strpos($value,'_')!==false) {
			if ($value == $check) return true;
		} else {
			if ($value == $action) return true;
		}
	}
	return false;
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
?>