<?php
!function_exists('usermsg') && exit('Forbidden');

!$type && $type = 'index';
$basename = $basename.'&type='.$type;
$updateindex = $extra = '';
if ($sign == 'link') {
	ObHeader("$user_file?action=userlink");
}
if ($type=='index') {
	@extract($db->get_one("SELECT wshownum,pshownum,cshownum,flashurl,bmusicurl FROM pw_userinfo WHERE uid='$admin_uid'"));
	if ($_POST['step']=='2') {
		if (is_array($config)) {
			(empty($config['wshownum']) || !is_numeric($config['wshownum']) || $config['wshownum']<1) && $config['wshownum'] = '200';
			$config['wshownum'] = (int)$config['wshownum'];
			$config['pshownum'] = (int)$config['pshownum'];
			$config['cshownum'] = (int)$config['cshownum'];
			foreach ($config as $key => $value) {
				$c_value = ${$key};
				if ($c_value!=$value) {
					$value = trim($value);
					$updateindex .= $extra."$key='$value'";
					$extra		  = ',';
				}
			}
		}
	}
} elseif ($type=='header') {
	$headerdb = $newheaderdb = array();
	@extract($db->get_one("SELECT headerdb FROM pw_userinfo WHERE uid='$admin_uid'"));
	$headerdb = unserialize($headerdb);

	
/*
$headerdb = Array
(
    'blog' => Array
        (
            'name' => '日志',
            'sign' => 'blog',
            'url' => 'blog.php?do=list&uid=1&type=blog',
            'note' => '',
            'ifshow' => '1',
            'order' => '0'
        ),

    'photo' => Array
        (
            'name' => '相册',
            'sign' => 'photo',
            'url' => 'blog.php?do=list&uid=1&type=photo',
            'note' => '',
            'ifshow' => '1',
            'order' => '1'
        ),

    'music' => Array
        (
            'name' => '音乐',
            'sign' => 'music',
            'url' => 'blog.php?do=list&uid=1&type=music',
            'note' => '',
            'ifshow' => '1',
            'order' => '2'
        ),

    'bookmark' => Array
        (
            'name' => '书签',
            'sign' => 'bookmark',
            'url' => 'blog.php?do=list&uid=1&type=bookmark',
            'note' => '',
            'ifshow' => '1',
            'order' => '3'
        ),

    'team' => Array
        (
            'name' => '圈子',
            'sign' => 'team',
            'url' => 'blog.php?do=list&uid=1&type=team',
            'note' => '',
            'ifshow' => '1',
            'order' => '4'
        ),

    'gbook' => Array
        (
            'name' => '留言',
            'sign' => 'gbook',
            'url' => 'blog.php?do=list&uid=1&type=gbook',
            'note' => '',
            'ifshow' => '1',
            'order' => '5'
        ),
     
    'bbs' => array
        (
             'name' => '论坛',
             'sign' => 'gbook',
             'url' => 'blog.php?do=bbs&uid=1',
             'note' => '',
             'ifshow' => '1',
             'order' => '6'
        ),

    'aboutme' => Array
        (
            'name' => '关于我',
            'sign' => 'aboutme',
            'url' => 'blog.php?do=aboutme&uid=1&type=aboutme',
            'note' => '',
            'ifshow' => '1',
            'order' => '7'
        ),

    'phpwind' => Array
        (
            'name' => 'PHPWind',
            'sign' => 'phpwind',
            'url' => 'http://www.phpwind.net',
            'note' => '',
            'ifshow' => '0',
            'order' => '8'
        )
);


	$headerdb['aboutme']['name'] = '关于我';
	$headerdb['aboutme']['sign'] = 'about';
	$headerdb['aboutme']['url']  = 'blog.php?do=about&uid=1';
	$headerdb['aboutme']['note'] = '';
	$headerdb['aboutme']['ifshow'] = '1';
	$headerdb['aboutme']['order'] = '8';
	print_r(serialize($headerdb));exit;
*/

	foreach ($index_name as $key => $value) {
		$headerdb[$key]['name'] = $value;
		$headerdb[$key]['sign'] = $key;
		if($key != 'bbs'){
			$headerdb[$key]['url']  = "blog.php?do=list&uid=$admin_uid&type=$key";
		}else{
			$headerdb[$key]['url']  = "blog.php?do=bbs&uid=$admin_uid";
		}
	}
	if($db_cbbbsopen != '1'){
		unset($headerdb['bbs']);
	}
	unset($index_name);
	if ($job == 'delete') {
		foreach ($headerdb as $key => $value) {
			$key != $sign && $newheaderdb[$key] = $value;
		}
		Strip_S($newheaderdb);
		$newheaderdb && $updateindex = "headerdb='".addslashes(serialize($newheaderdb))."'";
	} else {
		$indexdb = array('blog','photo','bookmark','goods','file','music');
		if ($_POST['step']!='2') {
			$index_order = $ifsystem = array();
			foreach ($headerdb as $key => $value) {
				$ifsystem[$key] = 0;
				if (@in_array($key,$indexdb)) {
					if (strpos(",$_GROUP[module],",",$key,")===false) {
						continue;
					}
					$ifsystem[$key] = 1;
				}
				($key=='team' || $key=='gbook' || $key=='aboutme' || $key=='bbs') && $ifsystem[$key] = 1;
				$ifsystem[$key] && $headerdb[$key]['urls'] = substrs($value['url'],33);
				(!$value['name'] || !$value['note'] || $value['name'] == $value['note']) && $headerdb[$key]['note'] = '';
				ifcheck($value['ifshow'],$key.'ifshow');
				$index_order[$key] = (int)$value['order'];
			}
			@asort($index_order);
		} else {
			if (is_array($config)) {
				foreach ($config as $key => $value) {
					$value['ifshow'] = (int)$value['ifshow'];
					$value['order'] = (int)$value['order'];
					$newheaderdb[$value['sign']] = $value;
				}
				if (is_array($newconfig) && $newconfig['sign']) {
					array_key_exists($newconfig['sign'],$headerdb) && usermsg('have_leftsign');
					$newheaderdb[$newconfig['sign']] = $newconfig;
				}
				foreach ($newheaderdb as $key => $value) {
					foreach ($value as $k => $v) {
						$v = str_replace(array('"','\'','\\',' '),'',$v);
						$v = Char_cv(trim($v));
						$value[$k] = $v;
					}
					($value['sign'] != $value['oldsign'] && (array_key_exists($value['sign'],$headerdb) || in_array($value['sign'],$indexdb))) && usermsg('have_leftsign');
					unset($value['oldsign']);
					$newheaderdb[$key] = $value;
				}
				Strip_S($newheaderdb);
				$newheaderdb && $updateindex = "headerdb='".addslashes(serialize($newheaderdb))."'";
			}
		}
	}
} elseif ($type=='left') {
	$leftdb = array();
	@extract($db->get_one("SELECT leftdb FROM pw_userinfo WHERE uid='$admin_uid'"));
	$leftdb = unserialize($leftdb);
	$sidedb = array('icon','notice','calendar','search','info','player','link','comment','team','archive','lastvisit','userclass','friends');
	foreach ($side_name as $key => $value) {
		$leftdb[$key]['name'] = $value;
		$leftdb[$key]['sign'] = $key;
	}
	if ($job == 'delete') {
		foreach ($leftdb as $key => $value) {
			$key != $sign && $newleftdb[$key] = $value;
		}
		Strip_S($newleftdb);
		$newleftdb && $updateindex = "leftdb='".addslashes(serialize($newleftdb))."'";
		$db->update("DELETE FROM pw_lcustom WHERE authorid='$admin_uid' AND sign='$sign'");
	} else {
		if ($_POST['step']!='2') {
			$side_order = $ifsystem = array();
			!$job && $job = 'list';
			if ($job == 'add') {
				$leftdb = array();
				$sign = '';
			} elseif ($job == 'edit') {
				$rt = $db->get_one("SELECT subject,content FROM pw_lcustom WHERE authorid='$admin_uid' AND sign='$sign'");
				!array_key_exists($sign,$leftdb) && usermsg('undefined_action');
				in_array($sign,array('icon','calendar','search','info','player','link','comment','archive','lastvisit','userclass')) && usermsg('undefined_action');
				$leftdb[$sign]['note'] = $rt['subject'];
				$leftdb[$sign]['content'] = $rt['content'];
			} elseif ($job == 'list') {
				foreach ($leftdb as $key => $value) {
					$ifsystem[$key] = @in_array($key,$sidedb) ? 1 : 0;
					(!$value['name'] || !$value['note'] || $value['name'] == $value['note']) && $leftdb[$key]['note'] = '';
					ifcheck($value['ifshow'],$key.'ifshow');
					$side_order[$key] = (int)$value['order'];
				}
				@asort($side_order);
				foreach ($side_order as $key => $value) {
					$side_order[$key] = $leftdb[$key];
				}
				unset($leftdb,$side_name);
			}
		} else {
			$newleftdb = array();
			if ($job == 'add') {
				if (is_array($newconfig)) {
					(!$newconfig['sign'] || !$newconfig['note'] || !$newconfig['content']) && usermsg('haveno_leftdb');
					$newconfig['sign'] == 'custom' && usermsg('have_leftsign');
					foreach ($leftdb as $key => $value) {
						$newconfig['sign']==$key && usermsg('have_leftsign');
						$newleftdb[$key] = $value;
					}
					unset($leftdb);
					$id = $db->get_value("SELECT id FROM pw_lcustom WHERE authorid='$admin_uid' AND sign='{$newconfig[sign]}'");
					$id && usermsg('have_leftsign');
					$newconfig['order'] = '0';
					foreach ($newconfig as $key => $value) {
						$value = str_replace(array('"','\'','\\',' '),'',$value);
						$value = Char_cv(trim($value));
						$newconfig[$key] = $value;
					}
					$newleftdb[$newconfig['sign']] = $newconfig;
					$db->update("INSERT INTO pw_lcustom(authorid,sign,setdate,subject,content) VALUES('$admin_uid','{$newconfig['sign']}','$timestamp','{$newconfig['note']}','{$newconfig['content']}')");
					unset($newleftdb[$newconfig['sign']]['content']);
					Strip_S($newleftdb);
					$newleftdb && $updateindex = "leftdb='".addslashes(serialize($newleftdb))."'";
				}
			} elseif ($job == 'edit') {
				if (is_array($newconfig)) {
					(!$newconfig['sign'] || !$newconfig['note'] || !$newconfig['content']) && usermsg('haveno_leftdb');
					foreach ($leftdb as $key => $value) {
						($newconfig['sign'] != $oldsign && $newconfig['sign'] == $key) && usermsg('have_leftsign');
						if ($key == $oldsign) {
							$newleftdb[$newconfig['sign']] = array('name' => Char_cv(trim($newconfig['name'])),'note' => Char_cv(trim($newconfig['note'])),'sign' => Char_cv(trim($newconfig['sign'])),'ifshow' => $value['ifshow'],'order' => $value['order']);
						} else {
							$newleftdb[$key] = $value;
						}
					}
					unset($leftdb);
					Strip_S($newleftdb);
					$newleftdb && $updateindex = "leftdb='".addslashes(serialize($newleftdb))."'";
					$id = $db->get_value("SELECT id FROM pw_lcustom WHERE authorid='$admin_uid' AND sign='".Char_cv(trim($newconfig['sign']))."'");
					if ($oldsign != Char_cv(trim($newconfig['sign']))) {
						$id && usermsg('have_leftsign');
					}
					$id ? $db->update("UPDATE pw_lcustom SET sign='{$newconfig['sign']}',setdate='$timestamp',subject='{$newconfig['note']}',content='{$newconfig['content']}' WHERE authorid='$admin_uid' AND sign='$oldsign'") : $db->update("INSERT INTO pw_lcustom(authorid,sign,setdate,subject,content) VALUES('$admin_uid','{$newconfig['sign']}','$timestamp','{$newconfig['note']}','{$newconfig['content']}')");
				}
			} elseif ($job == 'list') {
				$updates = array();
				foreach ($config as $key => $value) {
					!in_array($value['sign'],$sidedb) && $updates[$value['oldsign']] = $value['sign'];
					$value['ifshow'] = (int)$value['ifshow'];
					$value['order'] = (int)$value['order'];
					$newleftdb[$value['sign']] = $value;
				}
				foreach ($newleftdb as $key => $value) {
					foreach ($value as $k=>$v) {
						$v = str_replace(array('"','\'','\\',' '),'',$v);
						$v = Char_cv(trim($v));
						$value[$k] = $v;
					}
					($value['sign'] != $value['oldsign'] && (array_key_exists($value['sign'],$leftdb) || in_array($value['sign'],$sidedb))) && usermsg('have_leftsign');
					unset($value['oldsign']);
					$newleftdb[$key] = $value;
				}
				Strip_S($newleftdb);
				$newleftdb && $updateindex = "leftdb='".addslashes(serialize($newleftdb))."'";
				foreach ($updates as $key => $value) {
					if ($key != $value) {
						$id = $db->get_value("SELECT id FROM pw_lcustom WHERE authorid='$admin_uid' AND sign='$value'");
						$id && usermsg('have_leftsign');
						$db->update("UPDATE pw_lcustom SET sign='$value' WHERE authorid='$admin_uid' AND sign='$key'");
					}
				}
			}
		}
	}
}
if (($_POST['step']=='2' || $job == 'delete') && $updateindex) {
	$db->update("UPDATE pw_userinfo SET $updateindex WHERE uid='$admin_uid'");
	$basename = str_replace('&type=index','',$basename);
	usermsg('operate_success',$basename);
} else {
	include PrintEot('userindex');footer();
}
?>