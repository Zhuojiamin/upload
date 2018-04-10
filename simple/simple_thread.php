<?php
!function_exists('readover') && exit('Forbidden');

(!$type || !in_array($type,array('blog','photo','music','bookmark','goods','file'))) && Showmsg('undefined_action');

$itemdb = array();
@include_once Pcv(D_P."data/cache/forum_cache_{$type}.php");
$catedb = ${'_'.strtoupper($type)};
$catename = $ilang['c'.$type];
$title = $catedb[$cid]['name'];
$count = $catedb[$cid]['counts'];
$cupinfo = explode(',',$catedb[$cid]['cupinfo']);
$cupnav = '';
foreach ($cupinfo as $value) {
	$value && $cupnav .= "<a href='simple/index.php?type=$type&cid=".$catedb[$value]['cid']."'>".$catedb[$value]['name'].'</a>';
}
unset($cupinfo);
$cupnav && $cupnav = "($cupnav)";

$pages = smpage($count,$page,$pagesize,"simple/index.php?c=$cid");
$start = $pagesize*($page-1);
if($type == 'blog' || $type == 'bookmark'){
	$query = $db->query("SELECT itemid,subject,replies FROM pw_items WHERE ifcheck='1' AND ifhide='0' AND cid='$cid' ORDER BY postdate DESC LIMIT $start,$pagesize");
}elseif($type == 'photo'){
	$query = $db->query("SELECT aid,subject,photos,replies FROM pw_albums WHERE ifcheck='1' AND locked='0' AND ifcheck='1' AND ifhide='0' AND cid='$cid' ORDER BY postdate DESC LIMIT $start,$pagesize");
}elseif($type == 'music'){
	$query = $db->query("SELECT maid,subject,musics,replies FROM pw_malbums WHERE ifcheck='1' AND locked='0' AND ifcheck='1' AND cid='$cid' ORDER BY postdate DESC LIMIT $start,$pagesize");
}

while ($rt = $db->fetch_array($query)) {
	$itemdb[] = $rt;
}
$db->free_result($query);

require_once PrintEot('simple_thread');
?>