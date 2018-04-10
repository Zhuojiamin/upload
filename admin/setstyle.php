<?php
!function_exists('adminmsg') && exit('Forbidden');

!$job && $job = 'sys';
!$set && $set = 'list';
$basename .= "&job=$job";
!$db_defaultustyle && $db_defaultustyle = 'default';
if ($set == 'list') {
	if ($_POST['step'] == 2 && $job == 'sys') {
		$selid = GetGP('selid','P');
		N_InArray($db_defaultstyle,$selid) && adminmsg('del_style_error');
		!is_array($selid)  && $selid = array();
		$names = '';
		foreach ($selid as $value) {
			if ($value != 'wind') {
				P_unlink(D_P."data/style/$value.php");
				$names .= ($names ? ',' : '')."'$value'";
			}
		}
		!$names && adminmsg('operate_error');
		$db->update("DELETE FROM pw_style WHERE name IN ($names)");
		updatecache_style();
		adminmsg('operate_success');
	} else {
		$page = GetGP('page','G');
		(int)$page<1 && $page = 1;
		$styles = array();
		$stylename = $redcolor = '';
		if ($job == 'sys') {
			$fp = opendir(D_P.'data/style');
			while ($stylename = readdir($fp)) {
				if (preg_match('/(.+?)\.php$/i',$stylename,$rt)) {
					$rt['name'] = $rt[1];
					$rt['nameurl'] = rawurlencode($rt[1]);
					$rt['dcolor'] = $rt[1] == $db_defaultstyle ? ' style="color:#FF0000"' : '';
					$styles[] = $rt;
				}
			}
		} else {
			$fp = opendir(R_P.'theme');
			while ($stylename = readdir($fp)) {
				if (strpos($stylename,'.')===false && $stylename != '..') {
					$rt['name'] = $stylename;
					$rt['nameurl'] = rawurlencode($stylename);
					$rt['dcolor'] = $stylename == $db_defaultustyle ? ' style="color:#FF0000"' : '';
					$styles[] = $rt;
				}
			}
		}
		closedir($fp);
		$id = ($page-1)*$db_perpage;
		$count = count($styles);
		$styles = array_slice($styles,$id,$db_perpage);
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&set=list&");
		}
	}
} elseif ($set == 'default') {
	$sign = GetGP('sign','G');
	$db_defaultskin = $job == 'sys' ? $db_defaultstyle : $db_defaultustyle;
	if ($job == 'sys') {
		$db_name  = 'db_defaultstyle';
		$db_value = $db_defaultstyle;
	} else {
		$db_name  = 'db_defaultustyle';
		$db_value = $db_defaultustyle;
	}
	if ($db_value != $sign) {
		$db->get_value("SELECT db_name FROM pw_setting WHERE db_name='$db_name'") ? $db->update("UPDATE pw_setting SET db_value='$sign' WHERE db_name='$db_name'") : $db->update("INSERT INTO pw_setting(db_name,db_value) VALUES ('$db_name','$sign')");
		updatecache_db();
	}
	adminmsg('operate_success');
} elseif ($set == 'cp' && $job == 'user') {
	if ($_POST['step'] != 2) {
		$userskin = array();
		InitGP(array('sign','page'),'G');
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT us.id,us.sign,us.name,us.css,u.username FROM pw_userskin us LEFT JOIN pw_user u USING(uid) WHERE us.sign='$sign' ORDER BY us.uid DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['css'] && $userskin[] = $rt;
		}
		$count = $db->get_value("SELECT COUNT(*) FROM pw_userskin WHERE sign='$sign'");
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&set=cp&");
		}
	} else {
		$selid = GetGP('selid','P');
		!is_array($selid)  && $selid = array();
		$ids = '';
		foreach ($selid as $value) {
			$ids .= ($ids ? ',' : '')."'$value'";
		}
		!$ids && adminmsg('operate_error');
		$db->update("DELETE FROM pw_userskin WHERE id IN ($ids)");
		adminmsg('operate_success');
	}
} elseif ($set == 'edit') {
	$sign = GetGP('sign');
	$style = array();
	if ($_POST['step'] != 2) {
		$css_777 = 0;
		if ($job == 'sys') {
			$style = $db->get_one("SELECT stylepath,tplpath FROM pw_style WHERE name='$sign'");
			is_writeable(R_P."template/$style[tplpath]/wind/header.htm") && $css_777 = 1;
			$style['css'] = readover(R_P."template/$style[tplpath]/wind/header.htm");
			$style['css'] = explode('<!--css-->',$style['css']);
			$style['css'] = str_replace(array('$','<style type="text/css">','</style>'),array("\$",'',''),$style['css'][1]);
		} elseif ($job == 'user') {
			$css_777 = 1;
			$style['css'] = $db->get_value("SELECT css FROM pw_userskin WHERE sign='$sign'");
		}
	} else {
		$css = GetGP('css','P');
		$css = '<style type="text/css">'.$css.'</style>';
		if ($job == 'sys') {
			InitGP(array('newstylepath','oldstylepath','newtplpath','oldtplpath'),'P');
			if ($newstylepath != $oldstylepath) {
				if (!is_dir(R_P."$imgdir/$newstylepath")) {
					!@rename(R_P."$imgdir/$oldstylepath",R_P."$imgdir/$newstylepath") && adminmsg('setting_777');
				} else {
					$newstylepath = $oldstylepath;
				}
			}
			if ($newtplpath != $oldtplpath) {
				if (!is_dir(R_P."template/$newtplpath")) {
					!@rename(R_P."template/$oldtplpath",R_P."template/$newtplpath") && adminmsg('setting_777');
				} else {
					$newtplpath = $oldtplpath;
				}
			}
			$tplpathmsg = '';
			if (!is_writeable(R_P."template/$newtplpath/wind/header.htm")) {
				$tplpathmsg = $newtplpath;
				adminmsg('stylesys_777');
			}
			$style  = explode('<!--css-->',readover(R_P."template/$newtplpath/wind/header.htm"));
			$css	= str_replace('EOT','',$css);
			$css	= stripslashes(str_replace('$',"\$",$style[0].'<!--css-->'.$css.'<!--css-->'.$style[2]));
			$db->update("UPDATE pw_style SET stylepath='$newstylepath',tplpath='$newtplpath' WHERE name='$sign'");
			writeover(R_P."template/$newtplpath/wind/header.htm",$css);
			updatecache_style($sign);
		} else {
			$syscss = readover(R_P."theme/$sign/template/style.css");
			$css	= str_replace('EOT','',$css);
			$css	= stripslashes(str_replace('$',"\$",$css));
			$syscss == $css && $css = '';
			$db->update("UPDATE pw_userskin SET css='$css' WHERE sign='$sign'");
		}
		adminmsg('operate_success');
	}
}
include PrintEot('setstyle');footer();
?>