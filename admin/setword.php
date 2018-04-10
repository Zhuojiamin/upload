<?php
!function_exists('adminmsg') && exit('Forbidden');

@include(D_P.'data/cache/wordfb.php');
!$job && $job = 'replace';
if ($job == 'replace' || $job == 'forbid') {
	$typedb = CheckHtml(${'_'.strtoupper($job)});
	if ($_POST['step'] != 2) {
		$page = GetGP('page','G');
		(int)$page<1 && $page = 1;
		$id = ($page-1)*$db_perpage;
		$count = count($typedb);
		$typedb = array_slice($typedb,$id,$db_perpage);
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&job=$job&");
		}
	} else {
		$basename .= "&job=$job";
		InitGP(array('word','reword','selid'),'P');
		$ids = '';
		!is_array($word) && $word = array();
		!is_array($reword) && $reword = array();
		!is_array($selid) && $selid = array();
		$selid  = CheckInt($selid);
		foreach ($selid as $value) {
			if ((int)$value > 0) {
				unset($word[$value]);
				$ids .= ($ids ? ',' : '')."'$value'";
			}
		}
		$ids && $db->update("DELETE FROM pw_replace WHERE id IN ($ids)");
		foreach ($word as $key => $value) {
			$key = (int)$key;
			$value = preg_quote($value);
			if ($typedb[$key]['word'] != $value || $typedb[$key]['wordreplace'] != $reword[$key]) {
				$db->update("UPDATE pw_replace SET word='$value',wordreplace='$reword[$key]' WHERE id='$key'");
			}
		}
		updatecache_word($job);
		adminmsg('operate_success');
	}
} elseif ($job == 'add' && $_POST['step'] == 2) {
	$basename .= "&job=$job";
	InitGP(array('word','reword','type'),'P');
	(empty($word) || empty($reword)) && adminmsg('operate_fail');
	!$type && $type = 'replace';
	foreach ($word as $key => $value) {
		$value = preg_quote($value);
		if ($value && $reword[$key]) {
			$db->update("INSERT INTO pw_replace(word,wordreplace,type) VALUES ('$value','$reword[$key]','$type')");
		}
	}
	updatecache_word($type);
	adminmsg('operate_success');
}
include PrintEot('setword');footer();
?>