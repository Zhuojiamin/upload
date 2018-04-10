<?php
!function_exists('usermsg') && exit('Forbidden');
require_once(R_P.'mod/page_mod.php');
(int)$page<1 && $page = 1;
if($_GROUP['uploadsize']){
	$usersize=$db->get_one("SELECT SUM(size) AS sizes FROM pw_upload WHERE uid='$admin_uid'");
	$width=$usersize['sizes']/$_GROUP['uploadsize'];
	$width=$width*100;
	$width="{$width}%";
	$sizeinfo=$_GROUP['uploadsize'];
	$sizeinfo="<font color=\"red\">{$usersize['sizes']}</font>KB/{$sizeinfo}KB";
}else{
	$sizeinfo="无空间限制";
}
if(empty($step)){
	$sql="uid='$admin_uid'";
	if($type){
		!in_array($type,$item_type) && usermsg('undefine_action');
		$sql .=" AND atype='$type'";
	}
	if($state=='1'){
		$sql .=" AND state=0";
	}
	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_upload WHERE $sql");
	$sum=$rt['count'];
	$pages=page($sum,$page,$db_perpage,"$basename&type=$type&state=$state");
	$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$uploaddb=array();
	$query=$db->query("SELECT * FROM pw_upload WHERE $sql $limit");
	while($rt=$db->fetch_array($query)){
		$rt['uploadtime']=get_date($rt['uploadtime']);
		$uploaddb[]=$rt;
	}
	require_once PrintEot('attachcp');footer();
}elseif($step=="del"){
	if(!$selid = checkselid($selid)){
		usermsg('operate_error');
	}
	$query=$db->query("SELECT uid,attachurl,ifthumb FROM pw_upload WHERE uid='$admin_uid' AND aid IN($selid)");
	while($rt=$db->fetch_array($query)){
		if($rt['ifthumb']){
			$ext = strrchr($rt['attachurl'],'.');
			$name=substr($rt['attachurl'],0,strrpos($rt['attachurl'],'.'));
			$name="$attachpath/{$name}_thumb{$ext}";
			P_unlink(R_P."$name");
		}
		P_unlink(R_P."$attachpath/$rt[attachurl]");
	}
	$db->update("DELETE FROM pw_upload WHERE uid='$admin_uid' AND aid IN($selid)");
	usermsg('operate_success',"$basename&type=$type&state=$state");
}
?>