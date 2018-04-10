<?php
!function_exists('usermsg') && exit('Forbidden');

if(empty($job)){
	$links = array();	
	$rt    = $db->get_one("SELECT link FROM pw_userinfo WHERE uid='$admin_uid'");
	if($rt['link']){
		$links = unserialize($rt['link']);
	}
	require_once PrintEot('userlink');footer();
} elseif($job=='update'){
	$ids = $links = array();
	if(is_array($linkname)){
		foreach($linkname as $key => $value){
			if(!@in_array($key,$selid)){
				$value[0] = Char_cv($value[0]);
				$value[1] = Char_cv($value[1]);
				$value[2] = Char_cv($value[2]);
				$value[3] = Char_cv($value[3]);
				$links[]  = $value;
			}
		}
	}
	if($newlink[0] && $newlink[1]){
		$newlink[0] = Char_cv($newlink[0]);
		$newlink[1] = Char_cv('http://'.$newlink[1].'/');
		$newlink[2] = Char_cv($newlink[2]);
		$newlink[3] = Char_cv($newlink[3]);
		$links[]    = $newlink;
	}
	foreach($links as $key=>$value){
		foreach($value as $k=>$val){
			$links[$key][$k] = str_replace(array('"',"'","\\"),'',$val);
		}
	}
	$links = serialize($links);
	$db->pw_update(
		"SELECT uid FROM pw_userinfo WHERE uid='$admin_uid'",
		"UPDATE pw_userinfo SET link='$links' WHERE uid='$admin_uid'",
		"INSERT INTO pw_userinfo SET uid='$admin_uid',link='$links'"
	);
	usermsg('operate_success',$basename);
}
?>