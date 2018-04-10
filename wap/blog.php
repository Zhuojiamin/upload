<?php
include_once('global.php');

if($itemid){
	$rt=$db->get_one("SELECT b.cid,b.itemid,b.subject,b.author,bm.content FROM pw_items b LEFT JOIN pw_blog bm ON bm.itemid=b.itemid WHERE b.itemid='$itemid'");
	$rt['subject']=str_replace('>','&gt;',str_replace('<','&lt;',$rt['subject']));
	$rt['content']=preg_replace("/<br>/eis","_br_",$rt['content']);
	$rt['content']=str_replace('>','&gt;',str_replace('<','&lt;',$rt['content']));
	$rt['content']=str_replace("_br_","<BR />",$rt['content']);

	$article  = "<p>标题:$rt[subject]</p>\n";
	$article .= "<p><small>作者:$rt[author]</small></p>\n";
	$article .= "<p>内容:$rt[content]<br/></p>\n";

	$id=1;
	$comment='';
	$query=$db->query("SELECT subject,author,content FROM pw_comment WHERE itemid='$rt[itemid]' ORDER BY id DESC LIMIT 5");
	while($ct=$db->fetch_array($query)){
		if($ct['content']){
			$comment .= "<p>$id.$ct[subject]($ct[author])</p>\n";
			$comment .= "<p>$ct[content]<br/></p>\n";
			$id++;
		}
	}
	if($comment){
		$article .= "<p>最新评论:</p>\n";
		$article .= $comment;
	}
	$article .= "<p align=\"center\"><a href=\"addcomment.php?itemid=$itemid\">发表评论</a></p>\n";

} else{
	$article='-';
}

wap_header('read',$db_blogname);
wap_output($article);
wap_navig();
wap_footer();
?>