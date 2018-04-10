<?php
include_once('global.php');
require_once(R_P.'mod/ipfrom_mod.php');
require_once(R_P.'mod/charset_mod.php');
if($itemid && !$winduid){
	wap_msg("您还没有登录，不能发表评论");
}
InitGP(array('step'),P);
wap_header('addcomment',$db_blogname);
if($step != 'addcomment'){
	wap_output("<p align=\"center\"><b>添加评论</b><br/></p>\n");
	wap_output("<p>内容:<input name=\"content\" type=\"text\" /></p>\n");
	wap_output("<p align=\"center\">\n");
	wap_output("<anchor title=\"submit\">确定\n");
	wap_output("<go href=\"addcomment.php?itemid=$itemid\" method=\"post\">\n");
	wap_output("<postfield name=\"content\" value=\"$(content)\" />\n");
	wap_output("<postfield name=\"step\" value=\"addcomment\" />\n");
	wap_output("</go></anchor>\n");
	wap_output("</p>\n");
}else{
	if(!$winduid){
		wap_msg("未登陆，不能发表评论！");
	}
	if(!$_POST['content']){
		wap_msg("主题或内容为空!");
	} else{
		$pwuser = Wap_cv($_POST['pwuser']);
		$content  = Wap_cv($_POST['content']);
		$pwuser=convert_charset('utf-8',$db_charset,$pwuser);
		$content=convert_charset('utf-8',$db_charset,$content);
		$ipfrom  = cvipfrom($onlineip);
		$ifcheck=$db_commentcheck ? 0 : 1;
		$winduid = $db->get_value("SELECT uid FROM pw_user WHERE uid='$winduid'");
		$rt = $db->get_one("SELECT uid,type,cmttext FROM pw_items WHERE itemid = '$itemid'");
		$cmttext = $rt['cmttext'] ? (array)unserialize($rt['cmttext']) : array();
		@include(D_P.'data/cache/wordfb.php');
		$ifwordsfb = 0;
		$ckcontent = $content;
		$_FORBIDDB = (array)$_REPLACE+(array)$_FORBID;
		foreach ($_FORBIDDB as $value) {
			$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
		}
		if ($ckcontent != $content) {
			$content = $ckcontent;
			$ifwordsfb = 1;
		}
		$db->update("INSERT INTO pw_comment(cid,itemid,type,uid,author,authorid,postdate,userip,ipfrom,ifcheck,ifwordsfb,ifconvert,content) VALUES ('$cid','$itemid','$rt[type]','$rt[uid]','$windid','$winduid','$timestamp','$onlineip','$ipfrom','$ifcheck','$ifwordsfb','0','$content')");
		$cmtid = $db->insert_id();
		if ($ifcheck) {
			$cmttext = array_merge(array(array('id' => $cmtid,'author' => $pwuser,'authorid' => $winduid,'picon' => $winddb['icon'],'postdate' => $timestamp,'ifwordsfb' => $ifwordsfb,'ifconvert' => '0','content' => $content)),$cmttext);
			!$db_perpage && $db_perpage = 30;
			$cmttext = array_slice($cmttext,0,$db_perpage);
			Strip_S($cmttext);
			$cmttext = addslashes(serialize($cmttext));
			$db->update("UPDATE pw_items SET replies=replies+1,lastreplies='$timestamp',cmttext='$cmttext' WHERE itemid='$itemid'");
			$winduid && $db->update("UPDATE pw_user SET comments=comments+1 WHERE uid='$uid'");
		} else {
			$success = 'success';
		}
		wap_msg("评论发表成功","blog.php?itemid=$itemid");
	}
}
wap_footer();
?>