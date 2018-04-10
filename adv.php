<?php
require_once('global.php');
InitGP(array(id,type),G);
$id = (int)$id;
if (empty($type)) {
	$rt = $db->get_one("SELECT id FROM pw_advt WHERE id='$id'");
	$rt['id'] && $db->update("UPDATE pw_advt SET hits=hits+1 WHERE id='$id'");
}elseif ($type==2) {
	echo '<script type="text/javascript">';
	echo 'function advck(id) {';
	echo 'var url = "'.$db_blogurl.'/adv.php?id=" + id;';
	echo 'document.getElementById("sendmsg").src = url;';
	echo '}';
	echo '</script>';
	echo '<iframe id="sendmsg" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>';
	echo get_ad($id);
} elseif ($type==3) {
	echo 'document.write("'.addslashes(get_ad($id)).'");';
}
exit;
function get_ad($id){
	include D_P.'data/cache/adv_cache.php';
	if ($_AD[$id]) {
		return '<div onclick="advck('.$id.');" style="width:'.$_AD[$id]['width'].'px;height:'.$_AD[$id]['height'].'px;">'.$_AD[$id]['advdata'].'</div>';
	}
}
?>