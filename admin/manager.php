<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
if ($job == 'rightset') {
	if (!$set) {
		$groupdb = array();
		$query = $db->query("SELECT gid,type,title FROM pw_group WHERE admincp='1'");
		while ($rt=$db->fetch_array($query)) {
			if (($rt['type']=='system' || $rt['type']=='special') && $rt['gid']!='6' && $rt['gid']!='7') {
				unset($rt['type']);
				$groupdb[$rt['gid']] = $rt;
			}
		}
		$db->free_result($query);
		ksort($groupdb);
	} elseif ($set == 'edit') {
		$gid = GetGP('gid','G');
		if ($_POST['step']!=2) {
			$rightselect = '';
			$valuedb = $db->get_value("SELECT value FROM pw_rightset WHERE gid='$gid'");
			$rightdb = $valuedb ? unserialize($valuedb) : array();
			foreach ($leftlang as $left) {
				foreach ($left as $title => $array) {
					$rightselect .= "<tr class=\"head_2\"><td>$array[name]</td></tr><tr class=\"b\"><td>";
					foreach ($array['option'] as $key => $value) {
						if (isset($value[0])) {
							$k_check	= $rightdb[$key] ? 'CHECKED' : '';
							$rightselect .= "<div style=\"width:25%;float:left\"><input type=\"checkbox\" name=\"rightdb[$key]\" value=\"1\" $k_check />$value[0]</div>";
						} else {
							foreach ($value as $k => $v) {
								$c_key = $key.'_'.$k;
								$k_check = $rightdb[$c_key] ? 'CHECKED' : '';
								$rightselect .= "<div style=\"width:25%;float:left\"><input type=\"checkbox\" name=\"rightdb[$c_key]\" value=\"1\" $k_check />$v[0]</div>";
							}
						}
					}
					$rightselect .= '</td></tr>';
				}
			}
		} else {
			$basename .= "&set=$set&gid=$gid";
			$rightdb = GetGP('rightdb','P');
			Strip_S($rightdb);
			$rightdb = addslashes(serialize($rightdb));
			$rt 	 = $db->get_one("SELECT gid,value FROM pw_rightset WHERE gid='$gid'");
			if ($rt['value'] != $rightdb) {
				if ($rt['gid']) {
					$db->update("UPDATE pw_rightset SET value='$rightdb' WHERE gid='$gid'");
				} else {
					$db->update("INSERT INTO pw_rightset VALUES ('$gid','$rightdb')");
				}
			}
			adminmsg('operate_success');
		}
	}
} elseif ($job == 'manager') {
	if ($_POST['step']!=2) {
		include PrintEot('manager');footer();
	} else {
		include_once(D_P.'data/cache/dbreg.php');
		InitGP(array('username','password','check_pwd','ceoemail'),'P');
		if ($password) {
			$check_pwd!=$password && adminmsg('password_confirm');
			$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
			foreach ($S_key as $value) {
				strpos($password,$value)!==false && adminmsg('illegal_password');
			}
			$password = md5($password);
		} else {
			$password = $manager_pwd;
		}
		$uid = $db->get_value("SELECT uid FROM pw_user WHERE username='$username'");
		if (!$uid) {
			if ($username!=$admin_name) {
				$errorname = $username;
				adminmsg('user_not_exists');
			} else {
				$usermid = key($_gmember);
				require_once(GetLang('cpreg'));
				list($rg_rvrc,$rg_money) = explode("\t",$rg_regcredit);
				$db->update("INSERT INTO pw_user(username,password,blogtitle,email,publicmail,groupid,memberid,gender,regdate,rvrc,money,lastvisit,thisvisit,verify) VALUES ('$username','$password','$username','$ceoemail','1','3','$usermid','0','$timestamp','$rg_rvrc','$rg_money','$timestamp','$timestamp','1')");
				$uid = $db->insert_id();
				$db->update("INSERT INTO pw_userinfo(uid,style,domainname,wshownum,headerdb,leftdb) VALUES ('$uid','$db_defaultustyle','$domainname','200','$headerdb','$leftdb')");
				$db->update("UPDATE pw_bloginfo SET newmember='$username',totalmember=totalmember+1 WHERE id='1'");
				update_bloginfo_cache('users',$username,$uid);
			}
		} else {
			$db->update("UPDATE pw_user SET password='$password',groupid='3' WHERE username='$username'");
		}
		$setting = array('user' => $username, 'pwd' => $password);
		$setting['user'] && write_config($setting);
		adminmsg('operate_success');
	}
} elseif ($job == 'diy') {
	if ($_POST['step']!=2) {
		$diyselect = '';
		$db_diy = $db_diy ? explode(',',$db_diy) : array('setting_set','setuser_cp','setusergroup_level','setmodule_cp','setsafe_word');
		foreach ($leftlang as $left) {
			foreach ($left as $title => $array) {
				$diyselect .= "<tr class=\"head_2\"><td>$array[name]</td></tr><tr class=\"b\"><td>";
				foreach ($array['option'] as $key => $value) {
					if (isset($value[0])) {
						$k_check	= in_array($key,$db_diy) ? 'CHECKED' : '';
						$diyselect .= "<div style=\"width:25%;float:left\"><input type=\"checkbox\" name=\"diy[]\" value=\"$key\" $k_check />$value[0]</div>";
					} else {
						foreach ($value as $k => $v) {
							$c_key = $key.'_'.$k;
							$k_check	= in_array($c_key,$db_diy) ? 'CHECKED' : '';
							$diyselect .= "<div style=\"width:25%;float:left\"><input type=\"checkbox\" name=\"diy[]\" value=\"$c_key\" $k_check />$v[0]</div>";
						}
					}
				}
				$diyselect .= '</td></tr>';
			}
		}
	} else {
		$diy = GetGP('diy','P');
		$diy = is_array($diy) ? implode(',',$diy) : '';
		$db_value = $db->get_value("SELECT db_value FROM pw_setting WHERE db_name='db_diy'");
		if ($diy!=$db_diy || $diy!=$db_value) {
			if (!$db_value) {
				$db->update("INSERT INTO pw_setting(db_name,db_value) VALUES ('db_diy','$diy')");
			} else {
				$db->update("UPDATE pw_setting SET db_value='$diy' WHERE db_name='db_diy'");
			}
			updatecache_db();
		}
		adminmsg('operate_success');
	}
} else {
	adminmsg('undefined_action');
}
include PrintEot('manager');footer();

function update_bloginfo_cache($type,$username=null,$uid=null){
	global $db,$tdtime;
	if($type == 'blogs'){
		$tdtcontrol = $db->get_value("SELECT tdtcontrol FROM pw_bloginfo WHERE id='1'");
		if($tdtcontrol != $tdtime){
			$tdtcontrol = $tdtime;
			$tdblogs = 0;
			$db->update("UPDATE pw_bloginfo SET tdblogs='0',tdtcontrol='$tdtime'");
		}
		$totalblogs = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE ifcheck='1'");
		$tdblogs = $db->get_value("SELECT COUNT(*) FROM pw_items WHERE postdate>'$tdtime' AND ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalblogs='$totalblogs',tdblogs='$tdblogs' WHERE id='1'");
	}elseif($type == 'albums'){
		$totalalbums = $db->get_value("SELECT COUNT(*) FROM pw_albums WHERE ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalalbums='$totalalbums' WHERE id='1'");
	}elseif($type == 'malbums'){
		$totalmalbums = $db->get_value("SELECT COUNT(*) FROM pw_malbums WHERE ifcheck='1'");
		$db->update("UPDATE pw_bloginfo SET totalmalbums='$totalmalbums' WHERE id='1'");
	}elseif($type == 'users'){
		$totalmember = $db->get_value("SELECT COUNT(*) FROM pw_user");
		$newmember = $uid.','.$username;
		$db->update("UPDATE pw_bloginfo SET newmember='$newmember',totalmember=$totalmember WHERE id='1'");
	}
	$bloginfodb = "<?php\r\n";
	$bloginfo = $db->get_one("SELECT newmember,totalmember,totalblogs,totalalbums,totalmalbums,tdblogs FROM pw_bloginfo WHERE id='1'");
	foreach($bloginfo as $key => $value){
		$bloginfodb .= "\$$key='$value';\r\n";
	}
	$bloginfodb .= "?>";
	writeover(D_P.'data/cache/bloginfo.php',$bloginfodb);
}
?>