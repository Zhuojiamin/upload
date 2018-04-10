<?php
error_reporting(0);
define('D_P',getrssdirname(__FILE__));

$cid = (int)$_GET['cid'];
$uid = (int)$_GET['uid'];
$fileid = 'index';
if ($cid) {
	$fileid = "cid$cid";
} elseif ($uid) {
	$fileid = "uid$cid";
}

$Rss_newnum = 20;
$Rss_listnum = 20;
$Rss_updatetime = 0;

if (time()-@filemtime(D_P."data/cache/rss.php_{$fileid}_cache.xml") > $Rss_updatetime*60) {
	require_once('global.php');
	require_once(R_P.'mod/rss_mod.php');
	if ($cid && !$type) {
		$type = $db->get_value("SELECT catetype FROM pw_categories WHERE cid='$cid'");
	}
	(!$type || !in_array($type,array('blog','photo','music','bookmark','goods','file','team','user'))) && $type = 'blog';
	@include_once(D_P."data/cache/forum_cache_{$type}.xml");
	$catedb = ${'_'.strtoupper($type)};
	$description = "Latest $Rss_newnum blogs of whole blog";
	$sql = "AND i.type='$type' ORDER BY i.postdate DESC LIMIT $Rss_newnum";
	if ($cid) {
		$description = addslashes("Latest $Rss_newnum blogs of ".$catedb[$cid]['name']);
		$sql = "AND i.cid='$cid' ORDER BY i.postdate DESC LIMIT $Rss_listnum";
	} elseif ($uid) {
		$blogtitle = $db->get_value("SELECT blogtitle FROM pw_user WHERE uid='$uid'");
		$description = addslashes("Latest $Rss_newnum blogs of $blogtitle");
		$sql = "AND i.uid='$uid' AND i.type='$type' ORDER BY i.postdate DESC LIMIT $Rss_listnum";
	}
	$db_blogname = addslashes($db_blogname);
	$channel = array(
		'title'			=>  $db_blogname,
		'link'			=>  $db_blogurl,
		'description'	=>  $description,
		'copyright'		=>  "Copyright(C) $db_blogname",
		'generator'		=>  "LxBlog by PHPWind Studio",
		'lastBuildDate' =>  gmdate('r',$timestamp)
	);
	$image = array(
		'url'		  =>  "$db_blogurl/$imgpath/rss.png",
		'title'		  =>  'LxBlog',
		'link'		  =>  $db_blogurl,
		'description' =>  $db_blogname
	);
	$Rss = new Rss(array('xml'=>"1.0",'rss'=>"2.0",'encoding'=>$db_charset));
	$Rss->channel($channel);
	$Rss->image($image);
	$articleurl = $db_articleurl ? 'article.php?' : 'blog.php?do=showone&';
	$query = $db->query("SELECT i.itemid,i.cid,i.subject,i.author,i.postdate,b.content FROM pw_items i LEFT JOIN pw_{$type} b USING(itemid) WHERE i.ifcheck='1' AND i.ifhide='0' $sql");
	while ($rt = $db->fetch_array($query)) {
		$item = array(
			'title'		  => $rt['subject'],
			'description' => $rt['content'],
			'link'		  => "$db_blogurl/{$articleurl}type=$type&cid=$rt[cid]&itemid=$rt[itemid]",
			'author'	  => $rt['author'],
			'category'	  => $catedb[$rt['cid']]['name'],
			'pubdate'	  => gmdate('r',$rt['postdate'])
		);
		foreach ($item as $key => $value) {
			$value = preg_replace('/<(.+?)>/i','',addslashes($value));
			$item[$key] = $value;
		}
		$Rss->item($item);
	}
	$Rss->generate(D_P."data/cache/rss.php_{$fileid}_cache.xml");
}
header('Content-type: application/xml');
@readfile(D_P."data/cache/rss.php_{$fileid}_cache.xml");
exit;
function getrssdirname($path){
	if (function_exists('getdirname')) {
		return getdirname($path);
	} else {
		if (strpos($path,'\\')!==false) {
			return substr($path,0,strrpos($path,'\\')).'/';
		} elseif (strpos($path,'/')!==false) {
			return substr($path,0,strrpos($path,'/')).'/';
		} else {
			return './';
		}
	}
}
?>