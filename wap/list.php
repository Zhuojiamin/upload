<?php
include_once('global.php');
$wap_listnum=10;
if($cid){
	$id=1;
	$list='';
	$query=$db->query("SELECT itemid,subject,postdate FROM pw_items WHERE cid='$cid' AND type='blog' ORDER BY postdate DESC LIMIT $wap_listnum");
	while($rt=$db->fetch_array($query)){
		$list.="<p>$id.<a href=\"blog.php?itemid=$rt[itemid]\">$rt[subject]</a></p>\n";
		$list.="<p>".get_date($rt['postdate'],'Y-m-d')."</p>\n";
		$id++;
	}
} else{
	$list='';
}

wap_header('list',$db_blogname);
wap_output("<p align=\"center\">主题列表\n</p>\n");
wap_output($list);
wap_navig();
wap_footer();
?>