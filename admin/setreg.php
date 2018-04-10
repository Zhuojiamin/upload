<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
!in_array($job,array('reg','login')) && adminmsg('undefined_action');
include_once(D_P.'data/cache/dbreg.php');
if ($_POST['step']!=2) {
	if ($job != 'login') {
		list($rg_minlen,$rg_maxlen) = explode("\t",$rg_reglen);
		list($rg_rvrc,$rg_money) = explode("\t",$rg_regcredit);
		$rg_rvrc = floor($rg_rvrc/10);
		$rg_needdb = !empty($rg_needdb) ? explode("\t",$rg_needdb) : array();
		$regslt = '<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center"><tr>';
		$num = 0;
		foreach ($rglang as $key => $value) {
			if (in_array($key,array('uid','email'))) {
				continue;
			}
			$num++;
			$htm_tr = $num % 2 == 0 ? '</tr><tr>' : '';
			$r_ck = in_array($key,$rg_needdb) ? 'CHECKED' : '';
			$regslt .= "<td><input type=\"checkbox\" name=\"needdb[]\" value=\"$key\" $r_ck>$value</td>$htm_tr";
		}
		$regslt .= '</tr></table>';
		$ifcheckdb = array('allowregister' => $rg_allowregister,'showpermit' => $rg_showpermit,'showdetail' => $rg_showdetail,'allowsameip' => $rg_allowsameip,'ifcheck' => $rg_ifcheck,'lower' => $rg_lower);
		ifcheck($ifcheckdb);
	} else {
		$lg_logindb = !empty($lg_logindb) ? explode("\t",$lg_logindb) : array();
		$loginslt = '<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center"><tr>';
		$num = 0;
		foreach ($rglang as $key => $value) {
			if (in_array($key,array('uid','email','domainname'))) {
				$num++;
				$htm_tr = $num % 3 == 0 ? '</tr><tr>' : '';
				$l_ck = in_array($key,$lg_logindb) ? 'CHECKED' : '';
				$loginslt .= "<td><input type=\"checkbox\" name=\"logindb[]\" value=\"$key\" $l_ck>$value</td>$htm_tr";
			}
		}
		$loginslt .= '</tr></table>';
		$lg_qtdb = $lg_qtdb ? explode("\n",$lg_qtdb) : array();
	}
	include PrintEot('setreg');footer();
} else {
	if ($job != 'login') {
		if ($set == 'set') {
			InitGP(array('regdb','reglen','regcredit'),'P','0');
			$regdb['permit'] = Char_cv($regdb['permit'],'N');
			$regdb['banname'] = ArrayTxt(Char_cv($regdb['banname']),',');
			$regdb['reglen'] = implode("\t",CheckInt($reglen));
			$regdb['regcredit'] = implode("\t",CheckInt($regcredit));
		}
		if ($set == 'need') {
			$needdb = GetGP('needdb','P');
			$regdb['needdb'] = $regdb['unneeddb'] = '';
			foreach ($rglang as $key => $value) {
				if (!in_array($key,array('uid','email'))) {
					if (N_InArray($key,$needdb)) {
						$regdb['needdb']	.= ($regdb['needdb'] ? "\t" : '').$key;
					} else {
						$regdb['unneeddb']  .= ($regdb['unneeddb'] ? "\t" : '').$key;
					}
				}
			}
		}
		!is_array($regdb) && $regdb = array();
		updatesetting($regdb,'rg_','updatecache_rl');
	} else {
		if ($set == 'set') {
			$logindb = GetGP('logindb','P');
			$lgndb['logindb'] = '';
			foreach ($rglang as $key => $value) {
				if (N_InArray($key,$logindb)) {
					$lgndb['logindb'] .= ($lgndb['logindb'] ? "\t" : '').$key;
				}
			}
		}
		if ($set == 'qt') {
			$question = Char_cv(GetGP('question','P','0'),'N');
			InitGP(array('qtnum','selid','answer'),'P');
			$lgndb['qtnum'] = (int)$qtnum;
			$lgndb['qtdb']  = '';
			$lg_qtdb = explode("\n",$lg_qtdb);
			foreach ($lg_qtdb as $key => $value) {
				if (!N_InArray($key,$selid)) {
					$lgndb['qtdb'] .= ($lgndb['qtdb'] ? "\n" : '').$value;
				}
			}
			if ($question && $answer) {
				$lgndb['qtdb'] = $question."\t".$answer.($lgndb['qtdb'] ? "\n$lgndb[qtdb]" : '');
			}
		}
		!is_array($lgndb) && $lgndb = array();
		updatesetting($lgndb,'lg_','updatecache_rl');
	}
	adminmsg('operate_success');
}

?>