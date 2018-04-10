<?php
$lang = array (
	'email_empty'		=> 'Email为空',
	
	'modify_error'		=> '日志编辑错误，您要编辑的日志不存在！',
	'must_empty'		=> "必填项：{$ulang['mt'.$value]} 为空",
	
	'operate_error'		=> '没有选择操作对象',
	'operate_success'	=> '完成相应操作',
	'operate_success_not_checked'	=> '请等待管理员审核',
	
	'post_limit'		=> "每天你只能发表 $postnum 篇文章",
	
	'time_limit'		=> "刷新不要快于 $limitnum 秒",
	
	'undefined_action'	=> '非法操作',


'gdcode_error'	=>		"认证码错误",
'qanswer_error'     =>      "验证码答案错误",
'undefine_action'	=>		"无效操作--无权进行此项操作或功能未完成",
'operate_fail'		=>		"操作失败，请检查数据完整性",
'del_success'		=>		"数据删除成功！",
'haveno_leftdb'		=>		"没有填写自定义侧栏任意项",
'have_leftsign'		=>		"此标签已经存在",
'passportfail'		=>		"整合操作失败，请检查数据完整性",
'bbsnamefail'		=>		"论坛用户名不存在，请检查数据完整性",
'type_error'		=>		"没有选择日志所属分类",
'cate_error'		=>		"不能将日志移动或复制到根分类下",
'blog_error'		=>		"标题或内容为空，请返回重先填写！",
'illegal_email'		=>		"信箱不符合检查标准，请确认没有错误",
'city_empty'		=>		'请选择您所在的城市',
'blogtitle_empty'	=>		'博客标题为空',
'oldpwd_fail'		=>		"旧密码不正确",
'face_fail'			=>		"头像信息不正确",
'pwd_fail'			=>		"密码验证错误或两次输入密码不一致，请重新输入",
'domain_same'		=>		"此个性域名已经被注册,请选择其它个性域名",
'domain_limit'		=>		"个性域名长度错误,请控制在 $rg_domainmin - $rg_domainmax 字节以内",
'teamcp_name'		=>		"{$db_teamname}名称为空",
'teamcp_cate'		=>		"请选择{$db_teamname}所属分类",
'team_nameerror'	=>		"该{$db_teamname}名称已经存在，请使用其他名称！",
'pro_manager'		=>		"创始人密码请到后台修改",
'have_pushed'		=>		"该日志已经有人推送过,不能重复推送",
'bbsatc_pusherror'  =>		"您已经推送过这篇文章到您的博客中。",
'bbsatc_tiderror'	=>		"您要推送的文章不存在",
'bbsatc_usererror'	=>		"不能将别人的文章推送到自己的博客中，请检查您当前登录的博客帐号与论坛帐号是否一致<br><br>您可以选择：<a href='login.php?action=quit'><font color=blue>退出重新登录博客</font></a>或到用户设置里面设置论坛用户名",

'sign_limit'		=>		"签名不可超过 $_GROUP[signnum] 字节",
'post_right'		=>		"用户组权限:您所属的用户组没有发表{$lang_user[$type]}的权限!",
'upload_right'		=>		"用户组权限:您所属的用户组没有上传附件的权限!",

'no_teams'			=>		"您还没有{$db_teamname}.",

'same_domain'		=>		"该个性域名已经存在,请另外选择一个名称.",

'have_pass'			=>		"该会员已经通过验证.",

'title_limit'		=>		"文章标题为空或文章标题太长,请控制在$db_titlemax 字节以内.",
'content_limit'		=>		"文章内容长度请控制在 $db_postmin - $db_postmax 字节之间.",

'post_wordsfb'		=>		"警告： 您提交的内容中含有不良言语 '<font color='red'>$banword</font>'！",

'export_group_right'	=>"用户组权限：你所属的用户组没有日志备份的权限",
'upload_group_right'	=>"用户组权限：你所属的用户组没有上传附件的权限",
'msg_group_right'       =>"短消息功能被关闭或您所属的用户组没有使用短消息的权限",
'upload_forum_right'	=>"对不起，本论坛只有特定用户可以上传附件，请返回",
'upload_close'			=>"附件上传功能已关闭",
'upload_size_error'		=>"附件{$atc_attachment_name}超过指定大小{$db_uploadmaxsize}字节",
'upload_type_error'		=>"附件{$atc_attachment_name}的类型不符合准则",
'upload_num_error'		=>"您今天上传的附件已经达到指定个数({$_GROUP[uploadnum]} 个)",
'upload_error'			=>"上传附件失败，造成的原因可能有:附件目录不可写(777)、空间在安全模式下、空间大小已不足。",
'upload_content_error'	=>"附件$atc_attachment_name 内容非法,系统已经将其自动删除!",

'addcustom_success' => "自定义风格添加成功，<a href=\"$basename\"><font color=\"blue\">进入自定义风格编辑</font></a>",
'addcustom_failed'	=> '自定义风格添加失败，您选择的默认风格不存在',

'editcustom_error'=>'相册编辑错误，您要编辑的相册不存在',
'addphoto_error'=>'请选择您要上传的图片',
'album_name' => '相册名称不能为空！',
'photo_name' => '图片名称不能为空！',
'no_album'=>"您还没有创建相册，<a href=\"$basename&job=addalbum\"><font color=\"blue\">创建新相册</font></a>",
'upload_error'				=>'上传的文件错误：非法操作或文件无效!',
'upload_limit'				=>'上传的文件大小不能超过 2M',
'upload_illegal'			=>"上传的文件类型非法",
'editphoto_error'=>'您要编辑的照片不存在',
'delphoto_error'=>'您要删除的照片不存在',

'userskin_error'=>'风格设置错误，此风格不存在，请返回重试！',
'userskin_customlimit'=>'每个用户最多可以拥有五套自定义风格！',
'userskin_commenderror'=>'您已经推荐过此风格，请不要重复推荐同一个风格！',
'upload_linkerror'=>'图片自定义链接必须以‘http’开头',

'team_close'					=> "系统没有开启{$db_teamname}功能",
'team_groupright'				=> "用户组权限：您所属的用户组没有使用{$db_teamname}功能的权限",
'team_limit'					=> "您允许创建的{$db_teamname}个数已满，每个用户最多可以创建{$db_teamlimit}个{$db_teamname}",
'team_img_limit'	=> '你上传得图片过大,请另外选择一张',

'pro_loadimg_error'	=> "上传的头像错误：非法操作或头像无效!",
'pro_loadimg_limit'	=> "你要上传的头像太大，请另外选择一个头像",
'illegal_loadimg'	=> '上传的头像类型非法',
'pro_size_limit'	=> "你上传的头像不能超过 {$_GROUP[upfacew]}x{$_GROUP[upfaceh]}",
'icon_size_limit'	=> "你设置的头像尺寸不能超过 {$_GROUP[upfacew]}x{$_GROUP[upfaceh]}",
'malbumss_size_limit'	=> "你上传的专辑封面不能超过 {$_GROUP[upfacew]}x{$_GROUP[upfaceh]}",

'no_albumright'		=> "用户组权限：你所属的用户组没有使用相册的权限",
'album_numlimit'	=> "用户组权限：您最多允许创建的相册个数：{$_GROUP[albumnum]} 个",

'login_form'	=> "<form action=\"$loginurl\" method='post' name='login'><input type='hidden' name='jumpurl' value='user_index.php'><input type='hidden' name='step' value=2><input type='hidden' name='cktime' value='31536000'><fieldset style='margin:0% 22% 0% 22%;'><legend>登录</legend><table width='85%' cellpadding=5 cellspacing=1 align='center'><tr class=f_one><td width=30%>用户名</td><td> <input type=text size='40' tabindex='1' name='pwuser'></td></tr><tr class=f_one><td>密码</td><td> <input type='password' size='40' tabindex='2' name='pwpwd'></td></tr> </td></tr></table></fieldset><br><center><input type=submit value='提 交' tabindex='4' ></center></form><br><br><script language='JavaScript'>document.login.pwuser.focus();</script>",

'bbsuid_error'		=>		"您当前登录的博客帐号 ({$admin_name}) 没有绑定论坛帐号，不能使用文章推送功能!<br><br>您可以选择：<a href='login.php?action=quit' target='_top'><font color=blue>退出重新登录博客</font></a>",
'username_same' =>"此用户名已经被注册,请选择其它用户名",

'upload_size_limit'	=> "上传失败，您的附件空间已满，请到“上传文件管理”中整理、删除您上传的附件。",

'reply_success'	=> "留言回复成功！",
'empty_music_name'=>"音乐名称不能为空",
'wrong_music_url'=>"错误的音乐链接地址",
'same_music_url'=>"您已经添加了此歌曲，请不要重复提交",
'farfeed_url_error'=>"错误的远程地址",
'datalead_success'=>"您已成功导入{$countitems}条记录！",
'datalead_err'=>"错误的日期格式！",
'check_error'=>"验证码不正确！",
'xlmtype_err'=>"错误的xlm类型！",
'fv_url_erro'=>"错误的url类型",
'export_right'=>"用户组权限:您所属的用户组没有日志导入/备份的权限!",
'fv_name_erro'=>"名称不能为空！",

'goods_modify_error'=>'商品编辑错误，您要编辑的商品不存在！',
'news_modify_error'=>'资讯编辑错误，您要编辑的资讯不存在！',
'bookmark_modify_error'=>'书签编辑错误，您要编辑的书签不存在！',
'bookmark_url_error'=>'书签链接地址为空或非法地址！',

'skin_used'=>'此风格已经使用，请到风格收藏夹里面进行编辑',
'skin_collectioned'=>'此风格已经收藏，请到风格收藏夹里面进行编辑',
'bbsname_empty'=>'帖子列表为空或论坛用户名设置为空,请在修改个人设置中添加论坛用户名',
'word_ban'     => "您的文章中有禁用词：{$value[word]}",
'email_same'   => 'Email相同',
'empty_dirid'  => '请先添加相册',

'msg_num_limit' => "每天只能发送 {$maxsendmsg} 条短消息",
'user_not_exists' => "该用户不存在！",
'empty_aid' => "未创建相册！",
'empty_maid' => "未创建专辑！"
);
?>