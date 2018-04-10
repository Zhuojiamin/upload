<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";

include_once(D_P.'data/cache/adv_cache.php');
if ($job != 'ort') {
	if ($_POST['step'] != 2) {
		$pages = '';
		$advdb = array();
		(int)$page<1 && $page = 1;
		$start = ($page-1)*$db_perpage;
		$advdb = array_slice($_AD,$start,$db_perpage);
		unset($_AD);
		foreach ($advdb as $key => $value) {
			$advdb[$key]['postdate'] = get_date($value['postdate'],'Y-m-d');
			$advdb[$key]['type'] = $otherlang['advtype_'.$value['type']];
		}
		$count = count($advdb);
		if ($count > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($count,$page,$db_perpage,"$basename&$addpage");
		}
	} else {
		$selid = GetGP('selid','P',0);
		$ids = '';
		!is_array($selid)  && $selid = array();
		$selid  = CheckInt($selid);
		foreach ($selid as $value) {
			if ((int)$value > 0) {
				$ids .= ($ids ? ',' : '')."'$value'";
			}
		}
		if ($ids) {
			$db->update("DELETE FROM pw_advt WHERE id IN ($ids)");
			updatecache_adv();
		}
		adminmsg('operate_success');
	}
} else {
	!$set && $set = 'add';
	$basename .= "&set=$set";
	$id = GetGP('id');
	$id = (int)$id;
	if ($_POST['step'] != 2) {
		if ($set == 'add') {
			$_AD = array();
			$type_1 = 'CHECKED';
		} elseif ($set == 'edit') {
			${'type_'.$_AD[$id]['type']} = 'CHECKED';
		} elseif ($set == 'show') {
			foreach ($_AD as $key => $value) {
				if ($key == $id) {
					$showcode = RunCode($value,$value['type']);
					$runcode  = str_replace("{\$_AD[$id][advdata]}",$value['advdata'],$showcode);
					break;
				}
			}
		}
	} else {
		InitGP(array('subject','width','height','type'),'P');
		$advdata = Char_cv(GetGP('advdata','P',0),'N');
		(!$subject || !$advdata) && adminmsg('operate_error');
		$type	= (int)$type;
		$width	= (int)$width;
		$height = (int)$height;
		if ($set == 'add') {
			$db->update("INSERT INTO pw_advt (subject,hits,width,height,postdate,type,advdata) VALUES ('$subject','0','$width','$height','$timestamp','$type','$advdata')");
		} elseif ($set == 'edit') {
			$db->update("UPDATE pw_advt SET subject='$subject',width='$width',height='$height',type='$type',advdata='$advdata' WHERE id='$id'");
			$basename .= "&id=$id";
		}
		updatecache_adv();
		adminmsg('operate_success');
	}
}
include PrintEot('setadv');footer();
function RunCode($array,$type=1){
	global $db_blogurl;
	if (!is_array($array)) return '';
	$return = '';
	if ($type == 1) {
		$return = "<div onclick=\"advck($array[id]);\" style=\"width:{$array[width]}px;height:{$array[height]}px;\">{\$_AD[$array[id]][advdata]}</div><!-- $array[subject] -->";
	} elseif ($type == 2) {
		$return = "<iframe src=\"$db_blogurl/adv.php?id=$array[id]&type=2\"  width=\"$array[width]\" height=\"$array[height]\" marginwidth=\"0\" frameborder=\"0\" scrolling=\"no\" allowTransparency=\"true\"></iframe><!--$array[subject]-->";
	} elseif ($type == 3) {
		$return = "<script language=\"JavaScript\" src=\"$db_blogurl/adv.php?id=$array[id]&type=3\"></script><!--$array[subject]-->";
	}
	return $return;
}
?>