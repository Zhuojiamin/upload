<?php
!function_exists('usermsg') && exit('Forbidden');

require_once(R_P.'mod/passport.php');
if ($job != 'update') {
	$page = GetGP('page','G');
	$forumcache = '';
	(int)$page<1 && $page = 1;
	!$db_perpage && $db_perpage = 30;
	list($blogdb,$count,$tids) = GetForumList($admindb['bbstids']);
	empty($blogdb) && usermsg('bbsname_empty','user_index.php?action=userinfo');
	$tids != $admindb['bbstids'] && $db->update("UPDATE pw_userinfo SET bbstids='$tids' WHERE uid='$admin_uid'");
	include_once(D_P.'data/cache/forum_cache_blog.php');
	$catedb = (array)$_BLOG;
	foreach ($catedb as $key => $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$forumcache .= "<option value=\"$value[cid]\">$add $value[name]</option>";
	}
	$dirdb = $dirdb = $admindb['dirdb'] ? unserialize($admindb['dirdb']) : array();
	$dirdb[blog] = $dirdb[blog] ? $dirdb[blog] : array();
	foreach ($dirdb[blog] as $value) {
		$itemcache .= "<option id=\"dirop$value[typeid]\" value=\"$value[typeid]\">$value[name]</option>";
		$itemarray[$value['typeid']] = array('name' => $value['name'],'vieworder' => (int)$value['vieworder']);
	}
	if ($count > $db_perpage) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($count,$page,$db_perpage,"$basename&");
	}
	require_once PrintEot('bbsatc');footer();
} else {
	InitGP(array('cidto','dirto'));
	$cmtdb = $ckcmt = array();
	if (!$selid = checkselid($selid)) {
		$basename = "javascript:history.go(-1);";
		usermsg('operate_error');
	}
	$atcdb = ContentBbs($selid);
	$cmtdb = (array)$atcdb['comment'];
	unset($atcdb['comment']);
	$ifchecka = $db_blogcheck ? 0 : 1;
	$ifcheckc = $db_commentcheck ? 0 : 1;
	foreach ($atcdb as $atc) {
		$allowreply = !$atc['locked'] ? 1 : 0;
		$ifhide = $atc['locked'] ? 1 : 0;
		if (!$atc['locked']) {
			$allowreply = 1;
			$ifhide 	= 0;
		} else {
			$allowreply = 0;
			$ifhide 	= 1;
		}
		$db->update("INSERT INTO pw_items(cid,bbsfid,dirid,uid,author,type,icon,subject,postdate,lastpost,allowreply,folder,ifcheck,ifhide) VALUES ('$cidto','$atc[fid]','$dirto','$admin_uid','$admin_name','blog','','$atc[subject]','$atc[postdate]','$atc[lastpost]','$allowreply','1','$ifchecka','$ifhide')");
		$itemid = $db->insert_id();
		$db->update("INSERT INTO pw_blog(itemid,tags,userip,ipfrom,ifsign,ifconvert,content) VALUES('$itemid','','$atc[userip]','$atc[ipfrom]','$atc[ifsign]','$atc[ifconvert]','".addslashes($atc[content])."')");
		$ckcmt[$atc['tid']] = $itemid;
	}
	$bbstids = implode(',',array_keys($ckcmt));
	$admindb['bbstids'] && $bbstids && $bbstids .= ','.$admindb['bbstids'];
	$bbstids && $db->update("UPDATE pw_userinfo SET bbstids='$bbstids' WHERE uid='$admin_uid'");
	foreach ($cmtdb as $cmt) {
		$db->update("INSERT INTO pw_comment(cid,itemid,type,uid,author,authorid,postdate,userip,ipfrom,ifcheck,ifconvert,content) VALUES('$cidto','{$ckcmt[$cmt['tid']]}','blog','$admin_uid','$cmt[author]','0','$cmt[postdate]','$cmt[userip]','$cmt[ipfrom]','$ifcheckc','$cmt[ifconvert]','$cmt[content]')");
		$db->update("UPDATE pw_items SET replies=replies+1 WHERE itemid='{$ckcmt[$cmt['tid']]}'");
	}
	$db->update("UPDATE pw_user SET blogs=blogs+'".count($atcdb)."' WHERE uid='$admin_uid'");
	$db->update("UPDATE pw_categories SET counts=counts+'".count($atcdb)."' WHERE cid='$cidto'");
	usermsg('operate_success');
}
?>