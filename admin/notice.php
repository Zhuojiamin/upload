<?php
!function_exists('adminmsg') && exit('Forbidden');
$notice = array();
if ($job == 'cp') {
	if ($_POST['step']!=2) {
		(int)$page<1 && $page = 1;
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$query = $db->query("SELECT aid,vieworder,author,startdate,subject FROM pw_notice ORDER BY vieworder,startdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'],30);
			$rt['startdate'] = get_date($rt['startdate'],'Y-m-d');
			$notice[] = $rt;
		}
		$db->free_result($query);
		$count = $db->get_value('SELECT COUNT(*) FROM pw_notice');
		$pages = '';
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&job=cp&");
		}
	} else {
		$basename .= "&job=cp";
		InitGP(array('orders','selid'),'P',0);
		$aids = '';
		!is_array($orders) && $orders = array();
		!is_array($selid)  && $selid = array();
		$orders = CheckInt($orders);
		$selid  = CheckInt($selid);
		foreach ($selid as $value) {
			if ((int)$value > 0) {
				unset($orders[$value]);
				$aids .= ($aids ? ',' : '')."'$value'";
			}
		}
		if ($aids) {
			$sqlwhere = strpos($aids,',')===false ? "=$aids" : " IN ($aids)";
			$db->update("DELETE FROM pw_notice WHERE aid{$sqlwhere}");
		}
		foreach ($orders as $key => $value) {
			$key = (int)$key;
			$db->update("UPDATE pw_notice SET vieworder='$value' WHERE aid='$key'");
		}
		updatecache_novosh('no');
		adminmsg('operate_success');
	}
} elseif ($job == 'ort') {
	!$set && $set = 'add';
	$basename .= "&set=$set";
	$aid = GetGP('aid');
	$aid = (int)$aid;
	if ($_POST['step']!=2) {
		$atc_content = '';
		if ($set == 'edit' && $aid > 0) {
			$notice = $db->get_one("SELECT vieworder,startdate,url,subject,content FROM pw_notice WHERE aid='$aid'");
			$atc_content = $notice['content'];
		}
	} else {
		InitGP(array('vieworder','url'),'P');
		InitGP(array('subject','atc_content'),'P',0);
		$basename .= '&job=ort';
		!$url && !$atc_content && adminmsg('notice_empty');
		require_once(R_P.'mod/post_mod.php');
		$vieworder = (int)$vieworder;
		$subject = Char_cv($subject);
		if ($url) {
			$url = str_replace(array('"',"'",'\\'),'',$url);
			$atc_content = '';
		} else {
			$atc_content = AutoUrl($atc_content);
			$atc_content = Char_cv($atc_content,'N');
		}
		if ($set != 'edit') {
			$db->update("INSERT INTO pw_notice (vieworder,author,startdate,url,subject,content) VALUES ('$vieworder','".addslashes($admin_name)."','$timestamp','$url','$subject','$atc_content')");
		} else {
			$basename .= "&aid=$aid";
			$db->update("UPDATE pw_notice SET vieworder='$vieworder',author='".addslashes($admin_name)."',startdate='$timestamp',url='$url',subject='$subject',content='$atc_content' WHERE aid='$aid'");
		}
		updatecache_novosh('no');
		adminmsg('operate_success');
	}
}
include PrintEot('notice');footer();
?>