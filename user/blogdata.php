<?php
!function_exists('usermsg') && exit('Forbidden');
($_GROUP['ifexport'] == 0) && usermsg('export_group_right');
if ($job != 'lead') {
	if ($_POST['step']!='2') {
		$yeardate = $monthdate = '';
		list($year,$month) = explode('-',get_date($timestamp+24*3600*31,'Y-n'));
		for ($i=2003;$i<2009;$i++) {
			$tempslt = $i==$year ? 'SELECTED' : '';
			$yeardate .= "<option value=\"$i\">$i</option>";
			$yeardates .= "<option value=\"$i\" $tempslt>$i</option>";
		}
		for ($i=1;$i<13;$i++) {
			$tempslt = $i==$month ? 'SELECTED' : '';
			$monthdate .= "<option value=\"$i\">$i</option>";
			$monthdates .= "<option value=\"$i\" $tempslt>$i</option>";
		}
		include PrintEot('blogdata');footer();
	} else {
		require_once(R_P.'mod/rss_mod.php');
		require_once(R_P.'mod/download_mod.php');
		InitGP(array('startyear','startmonth','endyear','endmonth'),'P');
		$Rssnum = 0;
		$starttime = strtotime($startyear.'-'.$startmonth.'-1');
		$endtime = strtotime($endyear.'-'.$endmonth.'-1');
		$Rss = new Rss(array('xml'=>"1.0",'rss'=>"2.0",'encoding'=>$db_charset));
		$query = $db->query("SELECT i.subject,i.author,i.postdate,t.content FROM pw_items i LEFT JOIN pw_blog t ON i.itemid=t.itemid WHERE i.uid='$admin_uid' AND i.type='blog' AND i.ifcheck='1' AND i.postdate>'$starttime' AND i.postdate<'$endtime'");
		while ($rt = $db->fetch_array($query)) {
			$Rssnum++;
			$item = array(
				'title'       =>  $rt['subject'],
				'description' =>  $rt['content'],
				'author'      =>  $rt['author'],
				'pubdate'     =>  gmdate('r',$rt['postdate']),
			);
			Add_S($item);
			$Rss->item($item);
		}
		$db->free_result($query);
		$channel = array(
			'title'			=>  $db_blogname,
			'link'			=>  $db_blogurl,
			'description'	=>  "Latest $Rssnum blogs of whole blog",
			'copyright'		=>  "Copyright(C) $db_blogname",
			'generator'		=>  "LxBlog by PHPWind Studio",
			'lastBuildDate' =>  gmdate('r',$timestamp),
		);
		$image = array(
			'url'		  =>  "$db_blogurl/$imgpath/rss.png",
			'title'		  =>  'LxBlog',
			'link'		  =>  $db_blogurl,
			'description' =>  $db_blogname,
		);
		Add_S($channel);
		Add_S($image);
		$Rss->channel($channel);
		$Rss->image($image);
		$filedb['attachurl'] = D_P.'data/cache/rss.php_cache.php';
		$Rss->generate($filedb['attachurl']);
		$filedb['name'] = get_date($timestamp,'Y-m-d').'.xml';
		Download_file($filedb);exit;
	}
} else {
	if ($_POST['step']!='2') {
		$forumcache = $itemcache = '';
		include_once(D_P.'data/cache/forum_cache_blog.php');
		$catedb = (array)$_BLOG;
		foreach ($catedb as $value) {
			$add = '';
			for ($i=0;$i<$value['type'];$i++) {
				$add .= '>';
			}
			$forumcache .= "<option value=\"$value[cid]\">$add $value[name]</option>";
		}
		$dirdb = $admindb['dirdb'] ? unserialize($admindb['dirdb']) : array();
		$dirdb = (array)$dirdb['blog'];
		foreach ($dirdb as $value) {
	 		$itemcache .= "<option value=\"$value[typeid]\">$value[name]</option>";
		}
		include PrintEot('blogdata');footer();
	} else {
		InitGP(array('farfeed','xmltype','cid','itemtypeid','viewover','sel'),'P');
		require_once(R_P.'mod/windcode.php');
		require_once(R_P.'mod/rss_read.php');
		require_once(R_P.'mod/post_mod.php');
		require_once(R_P.'mod/upload_mod.php');
		require_once(R_P.'mod/ipfrom_mod.php');
		list($db_titlemax,$db_postmin,$db_postmax) = explode(',',$db_lenlimit);
		$_GROUP['attachsize'] && $db_uploadmaxsize = $_GROUP['attachsize'];
		$_GROUP['attachext'] && $db_uploadfiletype = $_GROUP['attachext'];
		$_GROUP['uploadnum'] && $db_attachnum = $_GROUP['uploadnum'];
		$db_uploadfiletype .= ' xml';
		$ipfrom = cvipfrom($onlineip);
		$attachdir .= '/xmltemp';
		if (!is_dir($attachdir)) {
			@mkdir($attachdir);
			@chmod($attachdir,0777);
			@fclose(@fopen($attachdir.'/index.html','w'));
			@chmod($attachdir.'/index.html',0777);
		}
		$uploaddb = UploadFile($admin_uid,1);
		if (empty($uploaddb)) {
			$sel != '1' && !@preg_match('/^http/i',$farfeed) && usermsg('farfeed_url_error');
			$xmlname = trim($farfeed);
		} else {
			$xmlname = $attachdir.'/'.$uploaddb[0]['attachurl'];
		}
		$rsstype = array(
			'1' => 'RSS',
			'2' => 'OBLOG',
			'3' => 'BLOGBUS'
		);
		$RssRead = new RssRead($xmlname,$rsstype[$xmltype]);
		if (substr(phpversion(),0,1)>4 || $a->charset=='UTF') {
			if ($db_charset != 'utf-8') {
				require_once(R_P.'mod/charset_mod.php');
				convertarray($RssRead->items);
			}
		}
		$tempdate = !empty($RssRead->items['ITEM']) ? (array)$RssRead->items['ITEM'] : array();
		if ($viewover) {
			$newdate = array();
			foreach ($tempdate as $key => $value) {
				$newdate[$key]['TITLE'] = substrs(Char_cv($value['TITLE']),30);
				$newdate[$key]['PUBDATE'] = get_date(strtotime($value['PUBDATE']));
			}
			unset($tempdate);
			include PrintEot('blogdata');footer();
		} else {
			@include(D_P.'data/cache/wordfb.php');
			!is_array($_REPLACE) && $_REPLACE = array();
			!is_array($_FORBID) && $_FORBID = array();
			$_FORBIDDB = $_REPLACE+$_FORBID;
			P_unlink($attachdir.$uploaddb[0]['attachurl']);
			$selid = (array)GetGP('selid','P');
			foreach ($selid as $value) {
				(int)$value < 0 && usermsg('operate_error');
			}
			$ifcheck = $db_postcheck ? 0 : 1;
			$countitems = 0;
			foreach ($tempdate as $key => $value) {
				if ($sel && !in_array($key,$selid)) continue;
				$postdate = $db->get_value("SELECT postdate FROM pw_items WHERE uid='$admin_uid' AND type='blog' AND postdate='".strtotime($value['PUBDATE'])."'");
				if (!$postdate) {
					$value['DESCRIPTION'] = str_replace(array('<br>','<br />'),"\t",$value['DESCRIPTION']);
					$value['DESCRIPTION'] = @preg_replace('/\<(.+?)\>/is','',$value['DESCRIPTION']);
					$value['DESCRIPTION'] = str_replace("\t",'<br />',$value['DESCRIPTION']);
					
					$atc_title = Char_cv($value['TITLE']);
					$atc_content = addslashes($value['DESCRIPTION']);
					$ifconvert = ($atc_content==convert($atc_content,$db_post)) ? 0 : 1;
					$ifwordsfb = 0;
					$cktitle = $atc_title;
					$ckcontent = $atc_content;
					foreach ($_FORBIDDB as $value) {
						$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
						$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
					}
					if ($cktitle != $atc_title) {
						$atc_title = $cktitle;
						$ifwordsfb = 1;
					}
					if ($ckcontent != $atc_content) {
						$atc_content = $ckcontent;
						$ifwordsfb = 1;
					}
					$db->update("INSERT INTO pw_items (cid,dirid,uid,author,type,subject,postdate,lastpost,allowreply,ifcheck,ifwordsfb,ifhide) VALUES ('$cid','$itemtypeid','$admin_uid','".addslashes($admin_name)."','blog','$atc_title','$timestamp','$timestamp','1','$ifcheck','$ifwordsfb','0')");
					$itemid = $db->insert_id();
					$db->update("INSERT INTO pw_blog (itemid,userip,ifsign,ipfrom,ifconvert,content) VALUES('$itemid','$onlineip','0', '$ipfrom','$ifconvert','$atc_content')");
					$countitems++;
				}
			}
			$ifcheck && $db->update("UPDATE pw_categories SET counts=counts+'$countitems' WHERE cid='$cid'");
			$userdb = $ifcheck ? array('uid' => $admin_uid,'items' => $admindb['items'],'todaypost' => $admindb['todaypost'],'lastpost' => $admindb['lastpost']) : array();
			update_post($userdb,$countitems);
			usermsg('datalead_success');
		}
	}
}
function update_post($userdb,$countitems){
	global $db,$db_credit,$timestamp,$tdtime;
	if (!empty($userdb)) {
		$memberid = getmemberid($userdb['items']);
		if ($userdb['lastpost'] < $tdtime) {
			$userdb['todaypost'] = 1;
		} else {
			$userdb['todaypost']++;
		}
		list($rvrc,$money) = explode(',',$db_credit);
		$rvrc = floor($rvrc/10)*$countitems;
		$money *= $countitems;
		$db->update("UPDATE pw_user SET memberid='$memberid',blogs=blogs+'$countitems',items=items+'$countitems',todaypost='$userdb[todaypost]',lastpost='$timestamp',rvrc=rvrc+'$rvrc',money=money+'$money' WHERE uid='$userdb[uid]'");
	}
}
function getmemberid($nums){
	global $_gmember;
	$gid = 0;
	foreach ($_gmember as $key => $value) {
		(int)$nums>=$value['creditneed'] && $gid = $key;
	}
	return $gid;
}
function convertarray(&$array){
	global $db_charset;
	foreach ($array as $key=>$value) {
		if (!is_array($value)) {
			$array[$key] = convert_charset('utf-8',$db_charset,$value);
		} else {
			convertarray($array[$key]);
		}
	}
}
?>
<input type="file" aq>