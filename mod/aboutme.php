<?php
!function_exists('N_strireplace') && exit('Forbidden');

require_once(R_P.'data/cache/forum_cache_user.php');
$blogdb['gender'] = $blogdb['gender'] == 0 ? $ilang['gender0'] : ($blogdb['gender'] == '1' ? $ilang['gender1'] : $ilang['gender2']);
$blogdb[cid] = $_USER[$blogdb[cid]][name];

$blogdb[qq] 		= (int)$blogdb[qq];
$blogdb[blogs] 		= (int)$blogdb[blogs];
$blogdb[albums]		= (int)$blogdb[albums];
$blogdb[photos] 	= (int)$blogdb[photos];
$blogdb[malbums] 	= (int)$blogdb[malbums];
$blogdb[musics] 	= (int)$blogdb[musics];
$blogdb[views] 		= (int)$blogdb[views];
$blogdb[comments] 	= (int)$blogdb[comments];


include_once(getPath("aboutme"));
?>