<?php
!function_exists('usermsg') && exit('Forbidden');
$basename .= $type ? "&type=$type" : '';
!$db_defaultustyle && $db_defaultustyle = 'default';
if (!$type) {
	if (!$job) {
		$fp = opendir(R_P.'theme/');
		while ($sysname=readdir($fp)) {
			if ($sysname=='.' || $sysname=='..') {
				continue;
			}
			list($name,$author,$date)=explode("\n",readover(R_P."theme/$sysname/info.txt"));
			$name	= str_replace(array('name:',"\r"),'',$name);
			$author = str_replace(array('author:',"\r"),'',$author);
			$date	= str_replace(array('date:',"\r"),'',$date);
			!$name && $name = $sysname;
			!$author && $author = 'Unkown';
			!$date && $date = get_date(filemtime(R_P."theme/$sysname"),'Y-m-d');
			$demopic = $db_blogurl.'/'.showdemopic($sysname);
			$listdb[] = array(
				'sign' => $sysname,
				'demo' => $demopic,
				'name' => $name,
				'author' => $author,
				'date' => $date
			);
		}
		closedir($fp);
		include PrintEot('userskin');footer();
	} elseif ($job == 'use') {
		$admindb['style'] == Char_cv($style) && usermsg('skin_used',"$basename&type=collection");
		$id = $db->get_value("SELECT id FROM pw_userskin WHERE uid='$admin_uid' AND sign='".Char_cv($style)."'");
		$id && usermsg('skin_collectioned',"$basename&type=collection");
		$db->update("UPDATE pw_userinfo SET style='".Char_cv($style)."' WHERE uid='$admin_uid'");
		$demo = showdemopic($style);
		$db->update("INSERT INTO pw_userskin(uid,sign,name,demo) VALUES ('$admin_uid','".Char_cv($style)."','".Char_cv($style)."','".addslashes($demo)."')");
		usermsg('operate_success',"$basename&type=collection");
	} elseif ($job == 'collection') {
		$id = $db->get_value("SELECT id FROM pw_userskin WHERE uid='$admin_uid' AND sign='".Char_cv($style)."'");
		$id && usermsg('skin_collectioned',"$basename&type=collection");
		$demo = showdemopic($style);
		$db->update("INSERT INTO pw_userskin(uid,sign,name,demo) VALUES ('$admin_uid','".Char_cv($style)."','".Char_cv($style)."','".addslashes($demo)."')");
		usermsg('operate_success',"$basename&type=collection");
	}
} elseif ($type=='collection') {
	if (!$job) {
		$query = $db->query("SELECT * FROM pw_userskin WHERE uid='$admin_uid'");
		while ($rt = $db->fetch_array($query)) {
			$rt['demo'] = str_replace($db_blogurl.'/','',$rt['demo']);
			$rt['demo'] = $db_blogurl.'/'.$rt['demo'];
			$listdb[] = $rt;
		}
		include PrintEot('userskin');footer();
	} elseif ($job == 'use') {
		$admindb['style'] == Char_cv($style) && usermsg('skin_used',"$basename&type=collection");
		$diycss = $db->get_value("SELECT diycss FROM pw_userskin WHERE sign='".Char_cv($style)."' AND uid='$admin_uid'");
		if(!empty($diycss)){
			$style = Char_cv($style).'|'.'1';
		}
		$db->update("UPDATE pw_userinfo SET style='".Char_cv($style)."' WHERE uid='$admin_uid'");
		usermsg('operate_success',"$basename&type=collection");
	} elseif ($job == 'return') {
		$id = $db->get_value("SELECT id FROM pw_userskin WHERE uid='$admin_uid' AND sign='".Char_cv($style)."'");
		$id && $db->update("UPDATE pw_userskin SET css='' WHERE id='$id'");
		$db->update("UPDATE pw_userinfo SET style='".Char_cv($style)."' WHERE uid='$admin_uid'");
		usermsg('operate_success',"$basename&type=collection");
	} elseif ($job == 'delete') {
		$id = $db->get_value("SELECT id FROM pw_userskin WHERE uid='$admin_uid' AND sign='".Char_cv($style)."'");
		$id && $db->update("DELETE FROM pw_userskin WHERE id='$id'");
		usermsg('operate_success',"$basename&type=collection");
	} elseif ($job == 'edit') {
		$style = Char_cv($style);
		$syscss = readover(R_P."theme/$style/template/style.css");
		if ($_POST['step'] != '2') {
			@extract($db->get_one("SELECT sign,name,css as usercss FROM pw_userskin WHERE uid='$admin_uid' AND sign='$style'"));
			$usercss = stripslashes($usercss);
			!$usercss && $usercss = $syscss;
			include PrintEot('userskin');footer();
		} else {
			$syscss    = addslashes($syscss);
			$usercss   = str_replace(array('EOT','$','?>'),'',$usercss);
			$usercss   = addslashes($usercss);
			$cksyscss  = str_replace(array("\r","\n","\t",' '),'',$syscss);
			$ckusercss = str_replace(array("\r","\n","\t",' '),'',$usercss);
			$cksyscss == $ckusercss && $usercss = '';
			unset($syscss,$cksyscss,$ckusercss);
			$db->update("UPDATE pw_userskin SET css='$usercss' WHERE uid='$admin_uid' AND sign='$style'");
			$db->update("UPDATE pw_userinfo SET style='".Char_cv($style)."|1' WHERE uid='$admin_uid'");
			usermsg('operate_success',"$basename&type=collection");
		}
	}
}
function showdemopic($dirname){
	if (file_exists(R_P."theme/$dirname/demo.png")) {
		$demopic = "theme/$dirname/demo.png";
	} elseif (file_exists(R_P."theme/$dirname/demo.jpg")) {
		$demopic = "theme/$dirname/demo.jpg";
	} elseif (file_exists(R_P."theme/$dirname/demo.jpeg")) {
		$demopic = "theme/$dirname/demo.jpeg";
	} elseif (file_exists(R_P."theme/$dirname/demo.gif")) {
		$demopic = "theme/$dirname/demo.gif";
	}
	return $demopic;
}
?>