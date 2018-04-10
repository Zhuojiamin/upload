<?php
!function_exists('usermsg') && exit('Forbidden');
!in_array($type,$item_type) && exit;

require_once(R_P.'mod/charset_mod.php');
foreach ($_POST as $key => $value) {
	${'utf8_'.$key} = $value;
	${$key} = $db_charset != 'utf-8' ? convert_charset('utf-8',$db_charset,$value) : $value;
}

if ($job == 'add') {
	if ($step=='add') {
		$tagname = trim(TagCv($tagname));
		$tagcount = $db->get_one("SELECT COUNT(*) AS count FROM pw_btags WHERE uid='$admin_uid'");
		$touchtag = $db->get_one("SELECT tagid FROM pw_btags WHERE tagname='$tagname'");
		empty($touchtag) && $db->update("INSERT INTO pw_btags(tagname,uid) VALUES ('$tagname','$admin_uid')");
		header("Content-Type: text/html; charset=utf-8");
		echo $utf8_tagname;exit;
	}
}elseif($job=="modify"){
	$tagnum="{$type}num";
	$touchtagdb=$db->get_one("SELECT k.tags,i.uid FROM pw_{$type} k LEFT JOIN pw_items i ON i.itemid=k.itemid WHERE k.itemid='$itemid'");
	$touchtagdb['uid']!=$admin_uid && exit;
	if($step=='add'){
		$itemid=(int)$itemid;
		if(empty($itemid)){
			echo 3;exit;
		}
		$tagname=trim(TagCv($tagname));
		$tagcount=$db->get_one("SELECT COUNT(*) AS count FROM pw_btags WHERE uid='$admin_uid'");
		$touchtag=$db->get_one("SELECT tagid FROM pw_btags WHERE tagname='$tagname'");
		if(empty($touchtag)){
				$db->update("INSERT INTO pw_btags(tagname,uid,$tagnum,allnum) VALUES ('$tagname','$admin_uid',1,1)");
				$tagid=$db->insert_id();
				$db->update("INSERT INTO pw_taginfo(tagid,uid,itemid,tagtype) VALUES ('$tagid','$admin_uid','$itemid','$type')");
		}else{
			$touchitem=$db->get_one("SELECT tagid FROM pw_taginfo WHERE itemid='$itemid' AND tagtype='$type'");
			if(empty($touchitem['tagid'])){
				$db->update("UPDATE pw_btags SET $tagnum=$tagnum+1,allnum=allnum+1 WHERE tagid='$touchtag[tagid]'");
			}
			$db->update("INSERT INTO pw_taginfo(tagid,uid,itemid,tagtype) VALUES ('$touchtag[tagid]','$admin_uid','$itemid','$type')");
		}
		if(strpos(",$touchtagdb[tags],",",$tagname,")===false){
			$tagdbs = $touchtagdb['tags'] ? "$touchtagdb[tags],$tagname" : $tagname;
			$db->update("UPDATE pw_{$type} SET tags='$tagdbs' WHERE itemid='$itemid'");
		}
		echo $utf8_tagname;exit;
	}elseif($step=='del'){
		$itemid=(int)$itemid;
		
		empty($itemid) && exit;
		$tagdbs=explode(',',$touchtagdb['tags']);
		foreach($tagdbs as $key=>$val){
			if($val != $tagname){
				$tagnewdb .= $tagnewdb ? ",$val" : $val;
			}
		}
		$db->update("UPDATE pw_{$type} SET tags='$tagnewdb' WHERE itemid='$itemid'");
		$touchtag=$db->get_one("SELECT tagid,$tagnum,allnum FROM pw_btags WHERE tagname='$tagname'");
		$touchtag[$tagnum]<2 && $db->update("DELETE FROM pw_taginfo WHERE itemid='$itemid' AND tagtype='$type'");
		if (!empty($touchtag['tagid']) && $touchtag[$tagnum]>0 && $touchtag['allnum']>0) {
			$db->update("UPDATE pw_btags SET $tagnum=$tagnum-1,allnum=allnum-1 WHERE tagid='$touchtag[tagid]'");
		}
	}
}
function TagCv($tag){
	$chars="`~!@#$%^&*()_-+=|\\{}[]:\";',./<>?";
	$len=strlen($chars);
	for($i=0; $i<$len; $i++){
		$tag = str_replace($chars[$i],'',$tag);
	}
	return $tag;
} 
?>