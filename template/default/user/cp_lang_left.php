<?php
$side_name = array(
	'icon'		=> '博主资料',
	'notice'	=> '用户公告',
	'calendar'	=> '日  历',
	'search'	=> '文章搜索',
	'info'		=> '个人统计',
	'player'	=> '播 放 器',
	'link'		=> '友情链接',
	'team'		=> '圈子信息',
	'comment'	=> '最新评论',
	'archive'	=> '存　档',
	'lastvisit'	=> '最近访问',
	'userclass'	=> '个人分类',
	'friends'	=> '好友'
);
$index_name = array(
	'blog'		=> '日志',
	'photo'		=> '相册',
	'bookmark'	=> '书签',
	'music'		=> '音乐',
	'team'		=> "$db_teamname",
	'gbook'		=> '留言',
	'bbs'       => '论坛'
);
$left_name = array(
	'blog'		=> '日志文章',
	'year'		=> '年',
	'month'		=> '月',
	'day'		=> '日',
	'newphoto'	=> '相册列表',
	'newalbumn' => '最新相册',
	'bloglist' => '日志分类',
	'musiclist' => '音乐分类',
	'photolist' => '相册分类',
	'photoslist' => '相册分类',
	'bookmarklist' => 'Tags列表',
	'bookmarklists' => '书签列表',
	'musiclists' => '音乐下载',
	'music' => '音乐',
	'gbook' => '留言',
	'team' => $db_teamname
);
$menuhead = array(
	'index' 	=> array('首页',"$user_file"),
	'blog'		=> array('日志',"$user_file?action=post&type=blog"),
	'photo' 	=> array('相册',"$user_file?action=post&type=photo"),
	'music' 	=> array('音乐',"$user_file?action=post&type=music"),
	'bookmark'  => array('书签',"$user_file?action=post&type=bookmark"),
	'teamcp'	=> array("$db_teamname","$user_file?action=teamcp"),
	'schfriend' => array('好友',"$user_file?action=schfriend"),
	'userinfo'	=> array('用户设置',"$user_file?action=userinfo"),
	'userindex'	=> array('首页定制',"$user_file?action=userindex"),
	'userskin'  => array('风格定制',"$user_file?action=userskin"),
	'attachcp'  => array('个人工具',"$user_file?action=attachcp"),
	'message'   => array('短消息',"$user_file?action=message")
);
$menulang = array(
	'blog' => array(
		'blogpost'	  => array('添加日志',"$user_file?action=post&type=blog"),
		'blogitemcp'  => array('日志管理',"$user_file?action=itemcp&type=blog"),
		'blogcomment' => array('评论管理',"$user_file?action=comment&type=blog"),
		'blogdata'	  => array('日志备份',"$user_file?action=blogdata"),
		'bbsatc'	  => array('论坛帖子推送',"$user_file?action=bbsatc")
	),
	'photo' => array(
		'photopost' 	=> array('上传图片',"$user_file?action=post&type=photo"),
		'photoitemcp'	=> array('相册管理',"$user_file?action=itemcp&type=photo"),
		'photocomment'	=> array('评论管理',"$user_file?action=comment&type=photo"),
		'photoaddalbum'	=> array('添加相册',"$user_file?action=addalbum&type=photo&job=add")
	),
	'music' => array(
		'musicpost'    => array('添加音乐',"$user_file?action=post&type=music"),
		'musicitemcp'  => array('专辑管理',"$user_file?action=itemcp&type=music"),
		'musiccomment' => array('评论管理',"$user_file?action=comment&type=music"),
		'musicaddmalbum' => array('添加专辑',"$user_file?action=addmalbum&type=music&job=add"),
	),
	'bookmark' => array(
		'bookmarkpost'	  => array('添加书签',"$user_file?action=post&type=bookmark"),
		'bookmarkitemcp'  => array('书签管理',"$user_file?action=itemcp&type=bookmark")
	),
	'teamcp' => array(
		'teamcp'	 => array("我的{$db_teamname}","$user_file?action=teamcp"),
		'tusercheck' => array("{$db_teamname}会员管理","$user_file?action=tusercheck"),
		'jointeam'	 => array("我加入的{$db_teamname}","$user_file?action=jointeam"),
		'teamblog'	 => array("{$db_teamname}文章管理","$user_file?action=teamblog")
	),
	'schfriend' => array(
		'schfriend' => array('我的好友',"$user_file?action=schfriend"),
		'carticle'	=> array('好友推荐文章',"$user_file?action=carticle")
	),
	'userinfo' => array(
		'userinfo'		=> array('修改个人设置',"$user_file?action=userinfo"),
		'gbook'			=> array('管理我的留言',"$user_file?action=gbook"),
		'commend'		=> array('申请推荐博客',"$user_file?action=commend"),
		'userhobby'		=> array('个人爱好设置',"$user_file?action=userhobby"),
		'userklink'		=> array('自定义关键字链接',"$user_file?action=userklink")
	),
	'userindex' => array(
		'userindex' 		=> array('页面设置',"$user_file?action=userindex"),
		'headeruserindex'	=> array('导航调用',"$user_file?action=userindex&type=header"),
		'leftuserindex' 	=> array('侧栏设置',"$user_file?action=userindex&type=left"),
		'userlink'			=> array('链接管理',"$user_file?action=userlink")
	),
	'userskin' => array(
		'userskin'			 => array('系统风格',"$user_file?action=userskin"),
		'collectionuserskin' => array('风格收藏夹',"$user_file?action=userskin&type=collection")
	),
	'attachcp' => array(
		'attachcp'		=> array('附件管理',"$user_file?action=attachcp"),
		'collections'	=> array('收藏管理',"$user_file?action=collections")
	),
	'message' => array(
		'message'		=> array('收件箱',"$user_file?action=message"),
		'sendboxmessage'=> array('发件箱',"$user_file?action=message&type=sendbox"),
		'writemessage'	=> array('写新消息',"$user_file?action=message&type=write"),
		'scoutmessage'	=> array('消息跟踪',"$user_file?action=message&type=scout"),
		'readmessage'	=> array('读消息',"$user_file?action=message&type=read")
	)
);
$ulang = array(
	'custom'		=> '自定义侧栏',
	'add'			=> '添加',
	'edit'			=> '编辑',
	'none'			=> '未定义',
	'ifcheck_1' 	=> '已审',
	'ifcheck_0' 	=> '<span style="color:red">未审</span>',
	'lguid'			=> '用户ID',
	'lgemail'		=> 'Email',
	'lgdomainname'	=> '个性域名',
	'mtblogtitle'   => '博客标题',
	'mtdomainname'  => '个性域名',
	'mtgender'      => '性别',
	'mtqq'          => 'OICQ',
	'mtmsn'         => 'MSN',
	'mtyahoo'       => 'YAHOO',
	'mtsite'        => '个人主页',
	'mtprovince'    => '城市',
	'mtcity'        => '城市',
	'mtyear'        => '生日',
	'mtmonth'       => '生日',
	'mtday'         => '生日',
	'mtcid'         => '博客分类',
	'mtsignature'   => '个性签名',
	'mtintroduce'   => '自我简介'
);
?>