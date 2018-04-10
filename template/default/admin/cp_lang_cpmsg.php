<?php
$lang = array(
	'bakup_in'					=> "正在导入第{$i}卷备份文件，程序将自动导入余下备份文件...",
	'bakup_out'					=> "已全部备份,备份文件保存在data目录下，备份文件为<br />$bakfile",
	'bakup_step'				=> "正在备份数据库表 $t_name: 共 $rows 条记录，已经备份至 $start  条记录<br /><br />已生成 $f_num 个备份文件，程序将自动备份余下部分",
	'blogtitle_empty'			=> '博客标题为空',
	
	'catecup_error1'			=> '所属分类设置错误：不能将分类本身设置为自己的所属分类',
	'catecup_error2'			=> '所属分类设置错误：不能将所属分类设置为自己的子分类',
	'cate_atcexist'				=> '请将此分类下的文章先移动或删除，再删除此分类',
	'cate_empty'				=> '博客分类为空',
	'cate_exist'				=> '此分类已经存在，请从新填写',
	'cate_hobbyexist'			=> '请将此分类下的爱好先移动或删除，再删除此分类',
	'cate_nameempty'			=> '分类名称为空，请填写',
	'cate_teamexist'			=> '请将此分类下的朋友圈先移动或删除，再删除此分类',
	'cate_userexist'			=> '请将此分类下的用户先移动或删除，再删除此分类',
	'content_limit'				=> "内容为空或内容长度错误,请控制在 $db_postmin - $db_postmax 字节以内",

	'domain_same'				=> '此个性域名已经被注册,请选择其它个性域名',
	'del_style_error'			=> '不能删除默认风格,请先更换默认风格',
	
	'gdcode_error'				=> '认证码错误',
	
	'hobby_nameempty'			=> '爱好名称为空，请填写',
	'hobby_cateempty'			=> '请选择爱好分类',
	
	'illegal_bbsid'				=> '整合用户名与用户名不一致，请修改',
	'illegal_bbspwd'			=> '通行整合功能已开，系统禁止修改密码',
	'illegal_blogtitle'			=> '此博客标题包含不可接受字符或被管理员屏蔽,请选择其它博客标题',
	'illegal_ckpassword'		=> '密码验证失败，请确认',
	'illegal_domain'			=> '此个性域名包含不可接受字符或被管理员屏蔽,请选择其它个性域名',
	'illegal_domainlenght'		=> "个性域名为空或个性域名长度错误,请控制在 $rg_domainmin - $rg_domainmax 字节以内(只能是数字于字母组合)",
	'illegal_email'				=> '信箱不符合检查标准，请确认没有错误',
	'illegal_password'			=> '密码包含不可接受字符',
	'illegal_pwdlenght'			=> '密码不能少于6位',
	'illegal_request'			=> '非法操作，请返回重试!',
	'illegal_userlenght'		=> "用户名为空或用户名长度错误,请控制在 $rg_minlen - $rg_maxlen 字节以内",
	'illegal_username'			=> '用户名包含不可接受字符',
	'illegal_userwords'			=> '警告： 您的用户名被禁用！',
	'installfile_exists'		=> 'install.php 文件仍然在您的服务器上，请马上利用 FTP 来将其删除！！ 当您删除之后，刷新本页面重新进入管理中心。',
	
	'level_delete'				=> '不能删除默认组、系统组(组ID为:3、4、5、6、7)',
	'level_error'				=> '更改用户组错误，只允许会员组更改为默认用户组',
	'login_error'				=> "密码错误,您还可以尝试{$L_left}次",
	
	'manager_right'				=> '只有创始人才能管理和编辑管理员帐号',

	'notice_empty'				=> '公告链接、内 容不能同时为空，请检查',
	
	'operate_error'				=> '没有选择操作对象',
	'operate_fail'				=> '操作失败，请检查数据完整性',
	'operate_success'			=> '完成相应操作',
	
	'password_confirm'			=> '两次输入密码不一致，请重新输入',
	
	'sql_config'				=> '站点管理员信息错误不存在，请重新上传 sql_config.php文件',
	'setting_777'				=> '无法更改图片或附件目录名,请确认属性777问题',
	'stylesys_777'				=> "请将此文件(template/$tplpathmsg/wind/header.htm)属性设置为 777 可写模式",
	
	'title_limit'				=> "标题为空或标题长度大于 $db_titlemax",
	
	'updatecache_job'			=> "正在更新{$start}到{$end}项",
	'undefined_action'			=> '非法操作：无权进行此项操作或功能未完成',
	'unite_havesub' 			=> '源分类含有子分类，请先移除源分类的所有子分类，再进行合并操作。',
	'upload_error'				=> '上传附件失败，造成的原因可能有:附件目录不可写(777)、空间在安全模式下、空间大小已不足。',
	'upload_content_error'		=> "附件$atc_attachment_name 内容非法,系统已经将其自动删除!",
	'upload_size_error'			=> "'附件超过指定大小{$db_uploadmaxsize}字节。",
	'upload_type_error'			=> '附件的类型错误，不允许上传此类附件。',
	'user_not_exists'			=> "用户{$errorname}不存在",
	'username_same'				=> '此用户名已经被注册,请选择其它用户名',
	'word_error'				=> '您提交的内容中含有‘&lt;iframe’‘&lt;script’‘&lt;meta’等系统禁用的HTML标签，请联系论坛创始人解决。',
	'smile_path_error'          => '未指定路径或者该图片路径不存在',
	'smile_name_error'			=> '表情组名称不能为空',
	'smile_rename'				=> '名称重复',
	'forum_cate'				=>'◆-',
	'forum_cate1'				=>'■-',
	'forum_cate2'				=>'●-',
	'forum_cate3'				=>'▲-',
	'cbbbbs_not_open'           =>'与论坛数据整合未开启',
	'not_update_bbsfid_cache'   =>'版块缓存信息未更新，当整合后该缓存在每次发表日志时更新'
);
?>