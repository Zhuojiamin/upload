<?php
!function_exists('usermsg') && exit('Forbidden');
require_once(R_P.'mod/page_mod.php');
require GetLang('user');
(int)$page<1 && $page = 1;
$item_type=array('blog','goods','photo','music','file','bookmark','tag','team','user');
if(empty($step)){
	$sql="c.uid='$admin_uid'";
	empty($ctype) && $ctype='blog';
	!in_array($ctype,$item_type) && usermsg('undefine_action');
	$sql .=" AND c.type='$ctype'";
	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_collections c WHERE $sql");
	$sum=$rt['count'];
	$pages=page($sum,$page,$db_perpage,"$basename&ctype=$ctype&");
	$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$collections=array();
	if($ctype=='blog'){
		$query=$db->query("SELECT i.subject,i.uid AS authorid,c.* FROM pw_collections c LEFT JOIN pw_items i ON c.itemid=i.itemid WHERE $sql $limit");
		while($rt=$db->fetch_array($query)){
			$rt['adddate']=get_date($rt['adddate']);
			$rt['url']="blog.php?do=showone&itemid=$rt[itemid]&type=blog";
			$collections[]=$rt;
		}
	}elseif($ctype=='photo'){
		$query=$db->query("SELECT a.subject,a.uid AS authorid,c.* FROM pw_collections c LEFT JOIN pw_albums a ON c.itemid=a.aid WHERE $sql $limit");
		while($rt=$db->fetch_array($query)){
			$rt['adddate']=get_date($rt['adddate']);
			$uid = (int)$rt['authorid'];
			$aid = (int)$rt['itemid'];
			$rt['url']="blog.php?do=list&uid=$uid&type=photos&job=view&aid=$aid";
			$collections[]=$rt;
		}
	}elseif($ctype=='music'){
		$query=$db->query("SELECT ma.subject,ma.uid AS authorid,c.* FROM pw_collections c LEFT JOIN pw_malbums ma ON c.itemid=ma.maid WHERE $sql $limit");
		while($rt=$db->fetch_array($query)){
			$rt['adddate']=get_date($rt['adddate']);
			$rt['url']="blog.php?do=showone&maid=$rt[itemid]&type=music";
			$collections[]=$rt;
		}
	}elseif($ctype=='user'){
		$query=$db->query("SELECT u.blogtitle,u.username,c.* FROM pw_collections c LEFT JOIN pw_user u ON c.itemid=u.uid WHERE $sql $limit");
		while($rt=$db->fetch_array($query)){
			$rt['adddate']=get_date($rt['adddate']);
			$rt['subject']=$rt['blogtitle'] ? $rt['blogtitle'] : "$rt[username]的空间";
			$rt['url']="blog.php?uid=$rt[itemid]";
			$collections[]=$rt;
		}
	}elseif($type=='team'){
		$query=$db->query("SELECT t.name,t.uid,c.* FROM pw_collections c LEFT JOIN pw_team t ON c.itemid=t.teamid WHERE $sql $limit");
		while($rt=$db->fetch_array($query)){
			$rt['adddate']=get_date($rt['adddate']);
			$rt['subject']=$rt['name'];
			$rt['url']="team.php?teamid=$rt[itemid]";
			$collections[]=$rt;
		}
	}elseif($type=='tag'){
		$query=$db->query("SELECT t.tagname,c.* FROM pw_collections c LEFT JOIN pw_btags t ON c.itemid=t.tagid WHERE $sql $limit");
		while($rt=$db->fetch_array($query)){
			$rt['adddate']=get_date($rt['adddate']);
			$rt['subject']=$rt['tagname'];
			$rt['url']="tags.php?tagname=".rawurlencode($rt['tagname']);
			$collections[]=$rt;
		}
	}else{
		$query=$db->query("SELECT i.subject,i.uid,c.* FROM pw_collections c LEFT JOIN pw_items i ON c.itemid=i.itemid WHERE $sql $limit");
		while($rt=$db->fetch_array($query)){
			$rt['adddate']=get_date($rt['adddate']);
			$rt['url']=$type=='blog' ? "article.php?itemid=$rt[itemid]&type=$type" : "blog.php?do=showone&itemid=$rt[itemid]&type=$type";
			$collections[]=$rt;
		}
	}
	require_once PrintEot('collections');footer();
}elseif($step=="del"){
	if(!$selid = checkselid($selid)){
		usermsg('operate_error');
	}
	$db->update("DELETE FROM pw_collections WHERE uid='$admin_uid' AND id IN($selid)");
	usermsg('operate_success',"$basename&type=$type");
}
?>