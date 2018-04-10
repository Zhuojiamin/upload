<?php
include_once('global.php');

$cates='';
foreach($_BLOG as $key => $value){
	$add='';
	for($i=0;$i<$value['type'];$i++){
		$add.="&gt;";
	}
	$cates.="<option value=\"$key\">$add$value[name]</option>\n";
}

wap_header('add',$db_blogname);
if($itemid){
	wap_output("<p align=\"center\"><b>添加文章</b><br/></p>\n");
	wap_output("<p>用户:<input name=\"pwuser\" type=\"text\" /></p>\n");
	wap_output("<p>内容:<input name=\"content\" type=\"text\" /></p>\n");
	wap_output("<p align=\"center\">\n");
	wap_output("<anchor title=\"submit\">确定\n");
	wap_output("<go href=\"addcomment.php?itemid=$itemid\" method=\"post\">\n");
	wap_output("<postfield name=\"pwuser\" value=\"$(pwuser)\" />\n");
	wap_output("<postfield name=\"content\" value=\"$(content)\" />\n");
	wap_output("</go></anchor>\n");
	wap_output("</p>\n");
} else{
	wap_output("<p align=\"center\"><b>添加文章</b><br/></p>\n");
	//wap_output("<p>用户:<input name=\"pwuser\" type=\"text\" /></p>\n");
	//wap_output("<p>密码:<input name=\"pwpwd\" type=\"text\" /></p>\n");
	wap_output("<p>标题:<input name=\"subject\" type=\"text\" /></p>\n");
	wap_output("<p>内容:<input name=\"content\" type=\"text\" /></p>\n");
	wap_output("<p>分类:<select name=\"cid\">\n");
	wap_output($cates);
	wap_output("</select></p>\n");
	wap_output("<p align=\"center\">\n");
	wap_output("<anchor title=\"submit\">确定\n");
	wap_output("<go href=\"addblog.php\" method=\"post\">\n");
	wap_output("<postfield name=\"pwuser\" value=\"$winduid\" />\n");
	//wap_output("<postfield name=\"pwpwd\" value=\"$(pwpwd)\" />\n");
	wap_output("<postfield name=\"subject\" value=\"$(subject)\" />\n");
	wap_output("<postfield name=\"content\" value=\"$(content)\" />\n");
	wap_output("<postfield name=\"cid\" value=\"$(cid)\" />\n");
	wap_output("</go></anchor>\n");
	wap_output("</p>\n");
}
wap_footer();
?>