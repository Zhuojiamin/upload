<?php
!function_exists('readover') && exit('Forbidden');
include_once(D_P.'data/cache/itemnav_cache.php');

if (file_exists(D_P."data/style/$skin.php") && strpos($skin,'..')===false) {
	@include Pcv(D_P."data/style/$skin.php");
} elseif (file_exists(D_P."data/style/$db_defaultstyle.php") && strpos($db_defaultstyle,'..')===false) {
	@include Pcv(D_P."data/style/$db_defaultstyle.php");
} else {
	@include D_P.'data/style/wind.php';
}
$cid = GetGP('cid','G');

$tkey = '';
list($db_metatitle,$db_metakeyword,$db_metadescrip) = explode('@:wind:@',$db_metadata);

if($db_htmifopen==1 && $db_dir != '.php?' && !strpos($_SERVER['PHP_SELF'],'.php')){
	$pos = strpos($_SERVER['PHP_SELF'],$db_dir);
}else{
	$pos = strrpos($_SERVER['PHP_SELF'],'.');
}

!$phpfile && $phpfile = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1,$pos-strrpos($_SERVER['PHP_SELF'],'/')-1);
if (!$type) {
	$phpfile == 'cate' && $type = 'blog';
	$phpfile == 'team' && $type = 'team';
}

$catedb = $showheader = $catedb = $showcate = array();
$typedb = array('index' => 'index.php','cblog' => 'cate.php?type=blog','cphoto' => 'cate.php?type=photo','cmusic' => 'cate.php?type=music','cbookmark' => 'bookmark.php','team' => 'team.php','member' => 'member.php');
if($db_teamifopen == 0){
	unset($typedb[team]);
}
$headerdb = array_keys($typedb);
foreach ($headerdb as $value) {
	if ($value[0]=='c' && strpos("\t$db_showcate\t","\t".substr($value,1)."\t")===false) {
		continue;
	}
	!$tkey && (($phpfile == 'cate' && $value=='c'.$type) || $value==$phpfile) && $tkey = $value;
	$phpfile == 'bookmark' && $value == 'cbookmark' && $tkey = 'cbookmark';
	if ($phpfile == 'team' && $value==$type && $db_teamifopen) {
		@include D_P.'data/cache/forum_cache_team.php';
		foreach ($_TEAM as $v) {
			if ($v['_ifshow'] && $v['cup'] == '0') {
				!$cid && $cid = $v['cid'];
				$showcate['team'][] = array('cid' => $v['cid'],'name' => $v['name'],'url' => "$typedb[$value]?cid=$v[cid]");
			}
			if ($v['_ifshow'] && $v['cup'] != '0') {
				!$cid && $cid = $v['cid'];
				$showcate_sub['team'][$v['cup']][] = array('cid' => $v['cid'],'name' => $v['name'],'url' => "$typedb[$value]?cid=$v[cid]");
			}
		}
	}
	if ($phpfile == 'cate' && $value=='c'.$type) {
		$cate = substr($value,1);
		include Pcv(D_P."data/cache/forum_cache_$cate.php");
		$catedb = ${'_'.strtoupper($cate)};
		foreach ($catedb as $v) {
			if ($v['_ifshow'] && $v['cup'] == '0') {
				!$cid && $cid = $v['cid'];
				$showcate[$cate][] = array('cid' => $v['cid'],'name' => $v['name'],'url' => "$typedb[$value]&cid=$v[cid]");
			}
			if ($v['_ifshow'] && $v['cup'] != '0') {
				!$cid && $cid = $v['cid'];
				$showcate_sub[$cate][$v['cup']][] = array('cid' => $v['cid'],'name' => $v['name'],'url' => "$typedb[$value]&cid=$v[cid]");
			}
		}
	}
	$showheader[] = array('name' => $ilang[$value],'url' => $typedb[$value],'sign' => $value);
}
unset($headerdb);
if (!$tkey) {
	if ($lxheader) {
		$tkey = $lxheader;
		$showheader[] = array('name' => $hlang[$tkey]['name'],'url' => $hlang[$tkey]['url'],'sign' => $tkey);
	} else {
		$tkey = 'index';
	}
}
foreach ((array)$_HEADNAV as $key => $value) {
	if ($db_teamifopen == 0 && ereg($value['url'],'team.php')) {
		unset($_HEADNAV[$key]);
	}
}
$showcate[$type] && $showcate[$type] = array_slice($showcate[$type],0,11);
$articleurl = $db_articleurl ? 'article.php?' : 'blog.php?do=showone&';
$particleurl = $db_articleurl ? 'cate.php?type=photo&job=plist&' : 'blog.php?do=list&type=photos&job=view&';
require PrintEot('header');
unset($_HEADNAV);
?>