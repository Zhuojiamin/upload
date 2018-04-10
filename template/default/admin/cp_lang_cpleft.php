<?php
$cplang = array(
	'yes'			=> '是',
	'no'			=> '否',
	'diy'			=> '常用选项',
	'dbinfo'		=> '以下变量需根据您的服务器说明档修改',
	'dbhost'		=> '数据库服务器',
	'dbuser'		=> '数据库用户名',
	'dbpw'			=> '数据库密码',
	'dbname'		=> '数据库名',
	'database'		=> '数据库类型',
	'PW'			=> '表区分符',
	'pconnect'		=> '是否持久连接',
	'charset'		=> "MYSQL编码设置\r\n如果您的论坛出现乱码现象，需要设置此项来修复\r\n请不要随意更改此项，否则将可能导致论坛出现乱码现象",
	'ma_info'		=> '博客创始人,拥有博客所有权限',
	'ma_name'		=> '管理员用户名',
	'ma_pwd'		=> '管理员密码',
	'pic_att'		=> '图片附件目录配置'
);
$rglang = array(
	'uid'			=> '用户ID（uid）',
	'email'			=> 'Email',
	'blogtitle'		=> '博客标题(不选博客标题为用户名)',
	'domainname'	=> '个性域名(必须开启2级域名功能才有效,只能由数字和字母组成)',
	'gender'		=> '性 别',
	'qq'			=> 'OICQ',
	'msn'			=> 'MSN',
	'yahoo'			=> 'YAHOO',
	'site'			=> '个人主页',
	'city' 			=> '城 市',
	'bday'			=> '生 日',
	'cid'			=> '博客分类',
	'style'			=> '用户风格',
	'signature' 	=> '个性签名',
	'introduce' 	=> '自我简介'
);
$catelang = array(
	'blog'		=> '日志',
	'photo'		=> '相册',
	'music'		=> '音乐',
	'bookmark'	=> '书签',
	'gbook'		=> '留言',
	'hobby'		=> '爱好'
);
$otherlang = array(
	'digest_0'		=> '--',
	'digest_1'		=> '精华I',
	'digest_2'		=> '精华II',
	'digest_3'		=> '精华III',
	'mod_title'		=> '主题: ',
	'mod_content'	=> '内容: ',
	'advtype_1'		=> '直接显示',
	'advtype_2'		=> 'iframe调用',
	'advtype_3'		=> 'Javascript调用'
);
$lefthead = array(
	'manager'	=> '创始人',
	'config'	=> '核心',
	'combine'	=> '整合',
	'user'		=> '用户',
	'cate'		=> '分类',
	'team'		=> "{$db_teamname}",
	'cache'		=> '模块&缓存',
	'msgcp' 	=> '信息管理',
	'safe'		=> '安全过滤',
	'systool'	=> '系统工具'
);
$leftmanager = array(
	'name'			=> '创始人选项',
	'option'		=> array(
		'manager' => array(
			'rightset'	=> array('后台权限设置',"$admin_file?action=manager&job=rightset"),
			'manager'	=> array('修改论坛创始人',"$admin_file?action=manager&job=manager"),
			'diy'		=> array('常用选项定制',"$admin_file?action=manager&job=diy")
		)
	)
);
$leftlang = array(
	'config' => array(
		'0' => array(
			'name' => '核心功能设置',
			'option' => array(
				'setting' => array(
					'set'	=> array('基本功能',"$admin_file?action=setting&job=set"),
					'core'  => array('核心功能',"$admin_file?action=setting&job=core"),
					'code'  => array('认证码',"$admin_file?action=setting&job=code"),
					'meta'	=> array('搜索引擎优化',"$admin_file?action=setting&job=meta"),
					'cate'	=> array('前台分类显示',"$admin_file?action=setting&job=cate"),
					'mail'  => array('邮件设置',"$admin_file?action=setting&job=mail")
				)
			)
		),
		'1' => array(
			'name' => '自定义导航设置',
			'option' => array(
				'setnav' => array(
					'cp'	=> array('导航管理',"$admin_file?action=setnav&job=cp"),
					'ort'	=> array('导航添加',"$admin_file?action=setnav&job=ort")
				)
			)
		),
		'2' => array(
			'name' => '注册登陆设置',
			'option' => array(
				'setreg' => array(
					'reg'	=> array('注册设置',"$admin_file?action=setreg&job=reg"),
					'login' => array('登陆设置',"$admin_file?action=setreg&job=login")
				)
			)
		),
		'3' => array(
			'name' => '目录部署设置',
			'option' => array(
				'setdir' => array(
					'dir' => array('动态目录',"$admin_file?action=setdir&job=dir"),
					'html' => array('静态目录',"$admin_file?action=setdir&job=html")
				)
			)
		),
		'4' => array(
			'name' => '发表相关设置',
			'option' => array(
				'setpost' => array(
					'post'		=> array('发表设置',"$admin_file?action=setpost&job=post"),
					'form' 		=> array('预设日志格式',"$admin_file?action=setpost&job=form"),
					'smile' 	=> array('表情设置',"$admin_file?action=setpost&job=smile"),
					'att'		=> array('附件设置',"$admin_file?action=setpost&job=att"),
					'mini'		=> array('图片缩略',"$admin_file?action=setpost&job=mini"),
					'code'		=> array('Wind Code',"$admin_file?action=setpost&job=code"),
					'credit'	=> array('发表积分',"$admin_file?action=setpost&job=credit"),
					'ajax'		=> array('ajax设置',"$admin_file?action=setpost&job=ajax")
				)
			)
		)
	),
	'combine' => array(
		'5' => array(
			'name' => '通行整合设置',
			'option' => array(
				'setcombine' => array(
					'passport'		=> array('通行证',"$admin_file?action=setcombine&job=passport"),
					'bbscombine'	=> array('与论坛数据整合',"$admin_file?action=setcombine&job=bbscombine"),
					'allowbbsfid'	=> array('推送版块权限',"$admin_file?action=setcombine&job=allowbbsfid"),
					'js'			=> array('JS调用数据',"$admin_file?action=setcombine&job=js")
				)
			)
		)
	),
	'user' => array(
		'6' => array(
			'name' => '用户管理设置',
			'option' => array(
				'usercp'	=> array('用户管理',"$admin_file?action=usercp"),
				'userort' 	=> array('添加用户',"$admin_file?action=userort")
			)
		),
		'7' => array(
			'name' => '用户组管理设置',
			'option' => array(
				'setgroup' => array(
					'stats' => array('用户组统计',"$admin_file?action=setgroup&job=stats"),
					'level' => array('用户组管理',"$admin_file?action=setgroup&job=level")
				)
			)
		)
	),
	'cate' => array(
		'8' => array(
			'name' => '分类基本设置',
			'option' => array(
				'catecp' => array(
					'blog'  	=> array('日志分类',"$admin_file?action=catecp&job=blog"),
					'photo' 	=> array('相册分类',"$admin_file?action=catecp&job=photo"),
					'music' 	=> array('音乐分类',"$admin_file?action=catecp&job=music"),
					'user'		=> array('用户分类',"$admin_file?action=catecp&job=user"),
					'team'		=> array("{$db_teamname}分类","$admin_file?action=catecp&job=team")
				)
			)
		),
		'9' => array(
			'name' => '分类内容设置',
			'option' => array(
				'cateatc' => array(
					'blog'  	=> array('日志内容',"$admin_file?action=cateatc&job=blog"),
					'photo' 	=> array('相册内容',"$admin_file?action=cateatc&job=photo"),
					'music' 	=> array('音乐内容',"$admin_file?action=cateatc&job=music"),
					'bookmark'  => array('书签内容',"$admin_file?action=cateatc&job=bookmark"),
					'gbook' 	=> array('留言内容',"$admin_file?action=cateatc&job=gbook")
				)
			)
		),
		'10' => array(
			'name' => '分类评论设置',
			'option' => array(
				'catecmt' => array(
					'blog'  	=> array('日志评论',"$admin_file?action=catecmt&job=blog"),
					'photo' 	=> array('相册评论',"$admin_file?action=catecmt&job=photo"),
					'music' 	=> array('音乐评论',"$admin_file?action=catecmt&job=music")
				)
			)
		)
	),
	'team' => array(
		'11' => array(
			'name' => "{$db_teamname}管理设置",
			'option' => array(
				'teamcp' => array(
					'set' => array("{$db_teamname}设置","$admin_file?action=teamcp&job=set"),
					'list'  => array("{$db_teamname}管理","$admin_file?action=teamcp&job=list"),
				),
				'teamatc' => array("{$db_teamname}文章管理","$admin_file?action=teamatc")
			)
		)
	),
	'cache' => array(
		'12' => array(
			'name' => '模块&缓存',
			'option' => array(
				'setmodule'	=> array('模块管理设置',"$admin_file?action=setmodule"),
				'setcache' 	=> array('缓存数据更新',"$admin_file?action=setcache")
			)
		)
	),
	'msgcp' => array(
		'13' => array(
			'name' => '短消息管理',
			'option' => array(
				'message' => array(
					'set'  => array('短消息设置',"$admin_file?action=message&job=set"),
					'send' => array('短消息群发',"$admin_file?action=message&job=send")
				)
			)
		),
		'14' => array(
			'name' => '公告管理设置',
			'option' => array(
				'notice' => array(
					'cp'  => array('公告管理',"$admin_file?action=notice&job=cp"),
					'ort' => array('公告添加',"$admin_file?action=notice&job=ort")
				)
			)
		),
		'15' => array(
			'name' => '广告管理设置',
			'option' => array(
				'setadv' => array(
					'cp'  => array('广告管理',"$admin_file?action=setadv&job=cp"),
					'ort' => array('广告添加',"$admin_file?action=setadv&job=ort")
				)
			)
		),
		'16' => array(
			'name' => '投票数据管理',
			'option' => array(
				'setvote' => array(
					'cp'  => array('投票管理',"$admin_file?action=setvote&job=cp"),
					'ort' => array('投票添加',"$admin_file?action=setvote&job=ort")
				)
			)
		),
		'17' => array(
			'name' => '爱好管理设置',
			'option' => array(
				'sethobby' => array(
					'class' => array('爱好分类',"$admin_file?action=sethobby&job=class"),
					'cp'	=> array('爱好管理',"$admin_file?action=sethobby&job=cp")
				)
			)
		),
		'18' => array(
			'name' => '标签Tag管理',
			'option' => array(
				'settag' => array('Tag管理',"$admin_file?action=settag")
			)
		),
		'19' => array(
			'name' => '风格管理设置',
			'option' => array(
				'setstyle' => array(
					'sys'  => array('网站风格管理',"$admin_file?action=setstyle&job=sys"),
					'user' => array('个人风格管理',"$admin_file?action=setstyle&job=user")
				)
			)
		),
		'20' => array(
			'name' => '网站辅助管理',
			'option' => array(
				'setother' => array(
					'ads'	=> array('宣传设置',"$admin_file?action=setother&job=ads"),
					'link'	=> array('友情链接管理',"$admin_file?action=setother&job=link")
				)
			)
		)
	),
	'safe' => array(
		'21' => array(
			'name' => '网站安全过滤',
			'option' => array(
				'setword' => array('词语过滤',"$admin_file?action=setword"),
				'setques' => array('验证问题设置',"$admin_file?action=setques"),
				'setsafe' => array(
					'ipban' => array('IP 禁止',"$admin_file?action=setsafe&job=ipban"),
					'other'	=> array('其他安全',"$admin_file?action=setsafe&job=other")
				)
			)
		)
	),
	'systool' => array(
		'22' => array(
			'name' => '数据库管理',
			'option' => array(
				'bakdata' => array(
					'bakout' => array('数据库备份',"$admin_file?action=bakdata&job=bakout"),
					'bakin'  => array('数据库恢复',"$admin_file?action=bakdata&job=bakin"),
					'repair' => array('数据库修复',"$admin_file?action=bakdata&job=repair")
				)
			)
		)
	)
);
?>