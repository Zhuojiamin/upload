<?php
require_once('global.php');
require_once(R_P.'mod/header_inc.php');

InitGP(array('province','city','gender','orderby','sc'));
$userdb = $sltdb = array();
$sqlwhere = $addpage = $shownav = '';
if ($province) {
	$sltdb['province'] = $province;
	$sqlwhere = "province='$province'";
	$addpage .= "province=".rawurlencode($province)."&";
	$shownav .= " &raquo; <a href=\"member.php?province=".rawurlencode($province)."\">$province</a>";
}
if ($city) {
	$sltdb['city'] = $city;
	$sqlwhere .= ($sqlwhere ? ' AND' : '')." city='$city'";
	$addpage .= "city=".rawurlencode($city)."&";
	$shownav .= " &raquo; <a href=\"member.php?city=".rawurlencode($city)."\">$city</a>";
}
if (strlen($gender)>0) {
	$sltdb['gender'][$gender] = ' SELECTED';
	$gender = (int)$gender;
	$sqlwhere .= ($sqlwhere ? ' AND' : '')." gender='$gender'";
	$addpage .= "gender=$gender&";
}
strpos($sqlwhere,'province')===false && $sqlwhere = "province!=''".($sqlwhere ? " AND$sqlwhere" : '');
$orderby != 'regdate' && $orderby != 'lastvisit' && $orderby = 'username';
$sltdb['orderby'][$orderby] = ' SELECTED';
$sc != 'desc' && $sc = 'asc';
$sltdb['sc'][$sc] = ' SELECTED';
$addpage .= ($orderby == 'username' ? '' : "orderby=$orderby&")."sc=$sc&";
$orderby = " ORDER BY $orderby $sc";
(int)$page<1 && $page = 1;
!$db_perpage && $db_perpage = 30;
$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
$query = $db->query("SELECT uid,username,gender,icon,province,city,qq,msn,regdate,lastvisit,site,commend FROM pw_user WHERE $sqlwhere $orderby $limit");
while ($rt = $db->fetch_array($query)) {
	$rt['genderurl'] = $rt['gender'];
	$rt['gender'] = $ilang['gender'.$rt['gender']];
	$rt['icon'] = showfacedesign($rt['icon']);
	$rt['provinceurl'] = rawurlencode($rt['province']);
	$rt['cityurl'] = rawurlencode($rt['city']);
	$rt['regdate'] = get_date($rt['regdate'],'Y-m-d');
	$rt['lastvisit'] = get_date($rt['lastvisit'],'Y-m-d');
	$rt['site'] = substrs($rt['site'],25);
	$userdb[] = $rt;
}
$db->free_result($query);
$count = $db->get_value("SELECT COUNT(*) FROM pw_user WHERE $sqlwhere");
if ($count > $db_perpage) {
	require_once(R_P.'mod/page_mod.php');
	$pages = page($count,$page,$db_perpage,"member.php?$addpage");
}
require_once PrintEot('member');footer();
function N_var_export($input,$f = 1,$t = null) {
	$output = '';
	if (is_array($input)) {
		$output .= "array(\r\n";
		foreach ($input as $key => $value) {
			$output .= $t."\t".N_var_export($key,$f,$t."\t").' => '.N_var_export($value,$f,$t."\t");
			$output .= ",\r\n";
		}
		$output .= $t.')';
	} elseif (is_string($input)) {
		$output .= $f ? "'".str_replace(array("\\","'"),array("\\\\","\'"),$input)."'" : "'$input'";
	} elseif (is_int($input) || is_double($input)) {
		$output .= "'".(string)$input."'";
	} elseif (is_bool($input)) {
		$output .= $input ? 'true' : 'false';
	} else {
		$output .= 'NULL';
	}
	return $output;
}
?>