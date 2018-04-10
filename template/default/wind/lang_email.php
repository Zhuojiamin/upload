<?php
!function_exists('readover') && exit('Forbidden');

$lang = array (

'email_check_subject'			=>"激活您在 {$db_blogname} 会员帐号的必要步骤!",
'email_check_content'			=>"{$regname},您好！\n\n{$db_blogname}欢迎您的到来！\n首先您得激活您的用户名(点击下行网址激活,如果用户名是中文请点击下行网址激活)\n{$db_blogurl}/register.php?vip=activating&r_uid={$winduid}&pwd={$timestamp}\n您的注册名为:{$regname}\n您的密码为:{$_POST[regpwd]}\n请尽快删除此邮件，以免别人偷看到您的密码\n\n如果忘了密码，可以到社区写信请管理员重新设定\n请查看社区各版的发贴规则，以免帖子被删除\n社区地址：{$db_bbsurl}\n\n本社区采用 PHPWind 架设,欢迎访问: http://www.phpwind.com",
'email_additional'				=>"From:{$fromemail}\r\nReply-To:{$fromemail}\r\nX-Mailer: PHPWind邮件快递",
'email_welcome_subject'			=>"{$regname},您好,感谢您注册{$db_blogname}",
'email_welcome_content'			=>"{$regname},您好！\n\n{$db_blogname}欢迎您的到来！\n您的注册名为:{$regname}\n您的密码为:{$_POST[regpwd]}\n请尽快删除此邮件，以免别人偷看到您的密码\n\n如果忘了密码，可以到社区写信请管理员重新设定\n请查看社区各版的发贴规则，以免帖子被删除\n社区地址：{$db_bbsurl}\n\n本社区采用 PHPWind 架设,欢迎访问: http://www.phpwind.com",
'email_sendpwd_subject'			=>"{$db_blogname} 密码重发",
'email_sendpwd_content'			=>"请到下面的网址修改密码： \n {$db_blogurl}/sendpwd.php?action=getback&pwuser={$pwuser}&submit={$submit}\n修改后请牢记您的密码\n欢迎来到 {$db_blogname}我们的网址是:{$db_blogurl}\n"
);
?>