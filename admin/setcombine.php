<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
if ($_POST['step']!=2) {
	if ($job == 'passport') {
		!$db_ppttype && $db_ppttype = 'client';
		${'type_'.$db_ppttype} = 'CHECKED';
		$credit = array('rvrc','money','credit');
		foreach ($credit as $value) {
			${$value.'_checked'} = strpos(",$db_pptcredit,",",$value,")!==false ? 'CHECKED' : '';
		}
		$db_ppturls = str_replace(',',"\r\n",$db_ppturls);
		ifcheck(array('pptifopen' => $db_pptifopen));
	}
	if ($job == 'bbscombine') {
		$mysqlintversion = $db->server_version();
		ifcheck(array('cbbbsopen' => $db_cbbbsopen));
	}
	
	if ($job == 'allowbbsfid'){
		!$db_cbbbsopen && adminmsg('cbbbbs_not_open');
		if(!file_exists(R_P.'data/cache/bbsforum_cache.php')){
			adminmsg('not_update_bbsfid_cache');
		}
		$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		require_once(R_P.'data/cache/bbsforum_cache.php');
		$dbcbbbbsfid = !empty($db_cbbbbsfid) ? explode(',',$db_cbbbbsfid) : array();
		foreach ($dbcbbbbsfid as $value) {
				${'bbsfid_'.$value} = 'CHECKED';
		}
		require_once GetLang('cpmsg');
		foreach ($_BBSFDB as $key => $value){
			if($value[type] == 'forum'){
				$_BBSFDB[$key][name] = $space.$lang[forum_cate].$_BBSFDB[$key][name];
			}elseif($value[type] == 'sub'){
				if($_BBSFDB[$value[fup]][type] == 'forum'){
					$_BBSFDB[$key][name] = $space.$space.$lang[forum_cate1].$_BBSFDB[$key][name];
				}else{
					$_BBSFDB[$key][name] = $space.$space.$space.$lang[forum_cate2].$_BBSFDB[$key][name];
				}
			}
		}
	}
	if($job == 'js'){
		InitGP('deal');
		if($deal == 'set'){
			ifcheck(array(jsifopen => $db_jsifopen));
		}
	}
	include PrintEot('setcombine');footer();
} else {
	$config = GetGP('config','P');
	if ($job == 'passport') {
		if ($config['ppttype'] == 'server') {
			$serverdb = GetGP('serverdb','P','0');
			$config['ppturls'] = ArrayTxt(str_replace("\n",',',Char_cv($serverdb['ppturls'])),',');
		} else {
			$pptcredit = GetGP('pptcredit','P');
			$config['pptcredit'] = !empty($pptcredit) ? implode(',',$pptcredit) : '';
		}
		!is_array($config) && $config = array();
		updatesetting($config);
		adminmsg('operate_success');
	}
	if($job == 'allowbbsfid') {
		$bbsfid = GetGP('bbsfid','P');
		$config['cbbbbsfid'] = !empty($bbsfid) ? implode(',',$bbsfid) : '';
		!is_array($config) && $config = array();
		updatesetting($config);
		adminmsg('operate_success');
	}
	if($job == 'js'){
		InitGP('deal');
		if($deal == 'set'){
			!is_array($config) && $config = array();
			updatesetting($config);
			adminmsg('operate_success');
		}else{
			InitGP(array('advert'),'',0);
			$code  = str_replace('EOT','',$advert);
			$code1 = htmlspecialchars(stripslashes($code));
			$code2 = stripslashes($code);
			include PrintEot('setcombine');footer();
		}
	}
	if($job == 'bbscombine'){
		updatesetting($config);
		adminmsg('operate_success');
	}
}
?>