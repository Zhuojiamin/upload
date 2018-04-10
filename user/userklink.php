<?php
!function_exists('usermsg') && exit('Forbidden');

if(empty($job)){
	$klinks = array();	
	$rt    = $db->get_one("SELECT klink FROM pw_userinfo WHERE uid='$admin_uid'");
	if($rt['klink']){
		$klinks = unserialize($rt['klink']);
	}
	require_once PrintEot('userklink');footer();
} elseif($job=='update'){
	$ids = $klinks = array();
	if(is_array($klinkname)){
		foreach($klinkname as $key => $value){
			if(!@in_array($key,$selid)){
				$value[0] = Char_cv($value[0]);
				$value[1] = Char_cv($value[1]);
				$value[2] = Char_cv($value[2]);
				$value[3] = Char_cv($value[3]);
				$klinks[]  = $value;
			}
		}
	}
	if($newklink[0] && $newklink[1]){
		$newklink[0] = Char_cv($newklink[0]);
		$newklink[1] = Char_cv('http://'.$newklink[1].'/');
		$newklink[2] = Char_cv($newklink[2]);
		$newklink[3] = Char_cv($newklink[3]);
		$klinks[]    = $newklink;
	}
	foreach($klinks as $key=>$value){
		foreach($value as $k=>$val){
			$klinks[$key][$k] = str_replace(array('"',"'","\\"),'',$val);
		}
	}
	$klinks = serialize($klinks);
	$db->pw_update(
		"SELECT uid FROM pw_userinfo WHERE uid='$admin_uid'",
		"UPDATE pw_userinfo SET klink='$klinks' WHERE uid='$admin_uid'",
		"INSERT INTO pw_userinfo SET uid='$admin_uid',klink='$klinks'"
	);
	usermsg('operate_success',$basename);
}
?>