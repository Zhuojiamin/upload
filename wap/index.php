<?php
include_once('global.php');
InitGP(array('prog'));
!$prog && $prog='index';
$list='';

foreach($_BLOG as $key => $value){
	$add='';
	for($i=0;$i<$value['type'];$i++){
		$add.="&gt;";
	}
	$list.="<p>{$add}<a href=\"list.php?cid=$key\">$value[name]</a></p>\n";
}

wap_header('index',$db_blogname);
if($prog=='index'){
	wap_output("<p><a href=\"index.php?prog=cate\">浏览文章</a></p>\n");
	wap_output("<p><a href=\"add.php\">添加文章</a></p>\n");
	wap_output("<p><a href=\"index.php?prog=bloginfo\">站点信息</a></p>\n");
} elseif($prog=='cate'){

	wap_output("<p><b>分类:</b></p>\n");
	wap_output($list);
} elseif($prog=='bloginfo'){
	$rt=$db->get_one("SELECT * FROM pw_bloginfo WHERE id=1");
	wap_output("<p>会员数:<br/>$rt[totalmember]</p>\n");
	wap_output("<p>最会注册会员:<br/>$rt[newmember]</p>\n");
} elseif($prog=='login'){
	InitGP(array('pwuser','pwpwd'),'P');
	if($windid){
		wap_msg('login_have');
	} elseif($pwuser && $pwpwd){
		wap_login($pwuser,$pwpwd);
	}
    wap_output("<p>用户名:<input name=\"pwuser\" type=\"text\" /></p>");
    wap_output("<p>密码:<input name=\"pwpwd\" type=\"password\" /></p>");
    wap_output("<p align=\"center\">");
    wap_output("<anchor title=\"submit\">");
    wap_output("确定");
    wap_output("<go href=\"index.php?prog=login\" method=\"post\">");
    wap_output("<postfield name=\"pwuser\" value=\"$(pwuser)\" />");
    wap_output("<postfield name=\"pwpwd\" value=\"$(pwpwd)\" />");
	wap_output("</go></anchor></p>");
} elseif($prog=='loginout'){
	Loginout();
	wap_msg('成功退出');
}
wap_footer();
?>