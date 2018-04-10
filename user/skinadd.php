<?php
!function_exists('usermsg') && exit('Forbidden');
$rt=$db->get_one("SELECT COUNT(*) as count FROM pw_skinconfig WHERE uid='$admin_uid'");
if($rt['count']>=5){
	usermsg('userskin_customlimit');
}
if(!$step){
	$query=$db->query("SELECT id,name,demo FROM pw_skinconfig WHERE uid='-1' OR commend=2");
	while($rt=$db->fetch_array($query)){
		if(empty($rt['demo'])){
			$rt['demo']=$attachpath.'/none.gif';
		}else{
			if(ereg("^http",$rt['demo'])){
				$rt['demo']=$rt['demo'];
			}elseif(file_exists("$imgpath/skin/$rt[demo]")){
				$rt['demo']=$imgpath.'/skin/'.$rt['demo'];
			}
		}
		$skindb[]=$rt;
	}
	require_once PrintEot('skinadd');footer();
}elseif($step=="2"){
	$rt=$db->get_one("SELECT sid,name,config,css FROM pw_skinconfig WHERE id='$id' AND (uid='-1' OR commend=2)");
	if(!$rt){
		usermsg('addcustom_failed');
	}
	$db->update("INSERT INTO pw_skinconfig(sid,uid,name,config,css) VALUES('$rt[sid]','$admin_uid','$rt[name]','$rt[config]','$rt[css]')");
	$id=$db->insert_id();
	$basename="$user_file?action=userskin&job=editcustom&id=$id";
	usermsg('addcustom_success',"$user_file?action=userskin&job=editcustom&id=$id");
}
?>