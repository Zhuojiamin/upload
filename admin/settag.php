<?php
!function_exists('adminmsg') && exit('Forbidden');

!$job && $job = 'cp';
if ($job == 'cp') {
	InitGP(array('page','username','keyword','orderby','sc','perpage'));
	$sql = $addpage = $pages = $feildslt = $leftjoin = '';
	$tagdb = array();
	$typedb = array('blog','bookmark','music','photo');
	foreach ($typedb as $key => $value) {
		unset($typedb[$key]);
		$feildslt .= ",{$value}num";
		$typedb[$value.'num'] = $catelang[$value];
	}
	$typenumdb = array_keys($typedb);
	if (strlen($username) > 0) {
		$sql .= ($sql ? ' AND' : '')." u.username LIKE '%".str_replace('*','%',$username)."%'";
		$addpage .= "username=$username&";
		$leftjoin = 'LEFT JOIN pw_user u USING(uid)';
	}
	if (strlen($keyword) > 0) {
		$sql .= ($sql ? ' AND' : '')." t.tagname LIKE '%".str_replace('*','%',$keyword)."%'";
		$addpage .= "keyword=$keyword&";
	}
	$where = $sql ? "WHERE $sql" : '';
	!$orderby && $orderby = 'blognum';
	$sc != 'desc' && $sc = 'asc';
	if ((int)$perpage < 1) {
		$perpage = $db_perpage ? $db_perpage : 30;
	}
	$addpage .= "orderby=$orderby&sc=$sc&perpage=$perpage&";
	$orderby = " ORDER BY $orderby $sc";
	(int)$page<1 && $page = 1;
	$limit = 'LIMIT '.($page-1)*$perpage.",$perpage";
	$query = $db->query("SELECT tagid,tagname$feildslt,iflock FROM pw_btags t $leftjoin $where $orderby $limit");
	while ($rt = $db->fetch_array($query)) {
		$tagdb[] = $rt;
	}
	$db->free_result($query);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_btags t $leftjoin $where");
	if ($count > $perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$perpage,"$basename&$addpage");
	}
} elseif ($job == 'edit') {
	$tagid = GetGP('tagid');
	if ($_POST['step'] != 2) {
		$tagname = $db->get_value("SELECT tagname FROM pw_btags WHERE tagid='$tagid'");
	} else {
		$tagname = GetGP('tagname');
		!$tagname && adminmsg('operate_error');
		$db->update("UPDATE pw_btags SET tagname='$tagname' WHERE tagid='$tagid'");
		adminmsg('operate_success');
	}
} elseif ($job == 'delete') {
	if ($_POST['step'] == 2) {
		$tagids = '';
		$selid = GetGP('selid','P');
		$deal = GetGP('deal','P');
		$lock = GetGP('lock','P');
		empty($selid) && $selid = array();
		foreach ($selid as $value) {
			if ((int)$value > 0) {
				$tagids .= ($tagids ? ',' : '')."'$value'";
			}
		}
		!$tagids && adminmsg('operate_error');
		$sqlwhere = strpos($tagids,',')===false ? "=$tagids" : " IN ($tagids)";
		if($deal == 'deltag'){
			$db->update("DELETE FROM pw_btags WHERE tagid{$sqlwhere}");
			$db->update("DELETE FROM pw_taginfo WHERE tagid{$sqlwhere}");
		}elseif($deal == 'lock'){
			$iflock = ($lock == '0' ? '1' : '0');
			$db->update("UPDATE pw_btags SET iflock='{$iflock}' WHERE tagid{$sqlwhere}");
		}
	}
	adminmsg('operate_success');
}
include PrintEot('settag');footer();
?>