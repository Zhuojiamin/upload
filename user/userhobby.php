<?php
!function_exists('usermsg') && exit('Forbidden');
include_once(D_P.'data/cache/hcate_cache.php');
if($job=='ajax'){
	require_once(R_P.'mod/charset_mod.php');
	if ($db_charset != 'utf-8') {
		foreach ($_POST as $key => $value) {
			${$key} = convert_charset('utf-8',$db_charset,$value);
		}
	}
	if($name){
		$hid=(int)$hid;
		if($hid){
			$name=Char_cv($name);
			$db->update("INSERT INTO pw_hobbyitem(hid,name,ifcheck) VALUES('$hid','$name',0)");
		}
	}
}
if(empty($step)){
	$hobbydb=$userhobby=array();
	$query=$db->query("SELECT * FROM pw_hobbyitem WHERE ifcheck=1 ORDER BY vieworder");
	while($rt=$db->fetch_array($query)){
		$hobbydb[$rt['hid']][]=$rt;
	}
	$rt = $db->get_one("SELECT hobbydb FROM pw_userinfo WHERE uid='$admin_uid'");
	$rt['hobbydb'] = unserialize($rt['hobbydb']);
	!is_array($rt['hobbydb']) && $rt['hobbydb'] = array();
	foreach ($rt['hobbydb'] as $key => $value) {
		$userhobby[$key] = (int)$key;
	}
	require_once PrintEot('userhobby');footer();
}elseif($_POST['step']==2){
	$uphobby = '';
	if (!empty($userhobby)  && is_array($userhobby)) {
		$db->update("DELETE FROM pw_userhobby WHERE uid='$admin_uid'");
		$userhobby = array_unique($userhobby);
		$uphobby = 'INSERT INTO pw_userhobby(uid,hobbyid) VALUES';
		$hadd = $update = '';
		foreach($userhobby as $key=>$val){
			$uphobby .= "$hadd('$admin_uid','$val')";
			$update .= "$hadd'$val'";
			$hadd = ',';
		}
	}
	$uphobby && $db->update($uphobby);
	if ($update) {
		$hobbydb = array();
		$query = $db->query("SELECT id,name FROM pw_hobbyitem WHERE id IN ($update) AND ifcheck=1 ORDER BY vieworder");
		while ($rt=$db->fetch_array($query)) {
			$hobbydb[$rt['id']] = $rt['name'];
		}
		Strip_S($hobbydb);
		$hobbydb && $db->update("UPDATE pw_userinfo SET hobbydb='".addslashes(serialize($hobbydb))."' WHERE uid='$admin_uid'");
	}
	usermsg('operate_success',$basename);
}
		
?>