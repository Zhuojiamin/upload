<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
if ($_POST['step']!=2) {
	if ($job == 'set') {
		$styledb = $ustyledb = '';
		$fp = opendir(D_P.'data/style/');
		while ($skinfile = readdir($fp)) {
			if (preg_match('/(.+?)\.php$/i',$skinfile,$rt)) {
				$styleselected = ($rt[1]==$db_defaultstyle) ? 'SELECTED' : '';
				$styledb .= "<option value=\"$rt[1]\" $styleselected>$rt[1]</option>";
			}
		}
		closedir($fp);
		$fp = opendir(R_P.'theme');
		while ($skinfile = readdir($fp)) {
			if (strpos($skinfile,'.')===false && $skinfile!='..') {
				list($stylename) = explode("\n",str_replace("\r",'',readover(R_P."theme/$skinfile/info.txt")));
				$stylename = str_replace('name:','',$stylename);
				!$stylename && $stylename = $skinfile;
				$styleselected = $skinfile==$db_defaultustyle ? 'SELECTED' : '';
				$ustyledb .= "<option value=\"$skinfile\" $styleselected>$stylename</option>";
			}
		}
		closedir($fp);
		list($db_opensch,$db_schstart,$db_schend) = explode("\t",$db_opensch);
		${'charset_'.str_replace('-','',$db_charset)} = ${'schstart_'.$db_schstart} = ${'schend_'.$db_schend} = ${'zone_'.$zero.str_replace('.','_',abs($db_timedf))} = 'SELECTED';
		$check_24  = 'CHECKED';
		if ($db_datefm) {
			if (strpos($db_datefm,'h:i A')) {
				$db_datefm = str_replace(' h:i A', '',$db_datefm);
				$check_12  = 'CHECKED';
			} else {
				$db_datefm = str_replace(' H:i', '',$db_datefm);
			}
			$db_datefm = str_replace('m', 'mm', $db_datefm);
			$db_datefm = str_replace('n', 'm', $db_datefm);
			$db_datefm = str_replace('d', 'dd', $db_datefm);
			$db_datefm = str_replace('j', 'd', $db_datefm);
			$db_datefm = str_replace('y', 'yy', $db_datefm);
			$db_datefm = str_replace('Y', 'yyyy', $db_datefm);
		} else {
			$db_datefm = 'yyyy-mm-dd';
		}
		$ifcheckdb = array('blogifopen' => $db_blogifopen,'debug' => $db_debug,'opensch' => $db_opensch,'footertime' => $db_footertime);
		ifcheck($ifcheckdb);
	}
	if ($job == 'core') {
		$db_onlinetime /= 60;
		list($db_facesize,$db_faceh,$db_facew) = explode("\t",$db_facesize);
		list($rg_domainmin,$rg_domainmax) = explode("\t",$db_domainlen);
		$ifcheckdb = array('lp' => $db_lp,'obstart' => $db_obstart,'forcecharset' => $db_forcecharset,'ifjump' => $db_ifjump,'ifonlinetime' => $db_ifonlinetime,'uploadface' => $db_uploadface,'userdomain' => $db_userdomain);
		ifcheck($ifcheckdb);
	}
	if ($job == 'code') {
		list($admingd,$reggd,$logingd,$postgd,$cmtgd,$gbookgd,$msggd) = explode("\t",$db_gdcheck);
		$ifcheckdb = array('admingd' => $admingd,'reggd' => $reggd,'logingd' => $logingd,'cmtgd' => $cmtgd,'gbookgd' => $gbookgd,'msggd' => $msggd);
		ifcheck($ifcheckdb);
	}
	$job == 'meta' && list($db_metatitle,$db_metakeyword,$db_metadescrip) = explode('@:wind:@',$db_metadata);
	if ($job == 'cate') {
		$catesigndb = array('blog','photo','music','bookmark');
		$showcate = explode("\t",$db_showcate);
		foreach ($catesigndb as $value) {
			if (in_array($value,$showcate)) {
				${'cate_'.$value} = 'CHECKED';
			}
		}
	}
	if ($job == 'mail') {
		${'mailmethod_'.$db_ml['mailmethod']}='checked';
		$ifcheckdb = array('mailifopen' => $db_ml['mailifopen'],'smtpauth' => $db_ml['smtpauth']);
		ifcheck($ifcheckdb);
		$ml_smtphost = $db_ml['smtphost'];
		$ml_smtpport = $db_ml['smtpport'];
		$ml_smtpfrom = $db_ml['smtpfrom'];
		$ml_smtpuser = $db_ml['smtpuser'];
		//ifcheck($ml_mailifopen,'mailifopen');
		//ifcheck($ml_smtpauth,'smtpauth');
		$ml_smtppass = substr($db_ml['smtppass'],0,1).'********'.substr($db_ml['smtppass'],-1);
		$ml_smtphelo = $db_ml['smtphelo'];
		$ml_smtpmxmailname = $db_ml['smtpmxmailname'];
		$ml_mxdns = $db_ml['mxdns'];
		$ml_mxdnsbak = $db_ml['mxdnsbak'];
	}
	include PrintEot('setting');footer();
} else {
	if ($job=='set') {
		InitGP(array('config','schcontrol','time_f'),'P','0');
		$config['whyblogclose'] = Char_cv($config['whyblogclose'],'N');
		$config['blogname'] = Char_cv($config['blogname']);
		$config['blogurl'] = Char_cv($config['blogurl']);
		$config['datefm'] = Char_cv($config['datefm']);
		if ($config['datefm']) {
			$time_f = (int)$time_f;
			$config['datefm'] = (strpos($config['datefm'],'mm')!==false) ? str_replace('mm','m',$config['datefm']) : str_replace('m','n',$config['datefm']);
			$config['datefm'] = (strpos($config['datefm'],'dd')!==false) ? str_replace('dd','d',$config['datefm']) : str_replace('d','j',$config['datefm']);
			$config['datefm'] = str_replace('yyyy','Y',$config['datefm']);
			$config['datefm'] = str_replace('yy','y',$config['datefm']);
			$timefm = ($time_f=='12') ? ' h:i A' :' H:i';
			$config['datefm'] .= $timefm;
		} else {
			$config['datefm']='Y-n-j H:i';
		}
		$config['opensch'] = implode("\t",Char_cv($schcontrol));
		$config['ceoconnect'] = Char_cv($config['ceoconnect']);
		$config['ceoemail'] && !preg_match('/^[-a-zA-Z0-9_\.]{3,}+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/',$config['ceoemail']) && adminmsg('illegal_email');
		$config['ceoemail'] = Char_cv($config['ceoemail']);
		$config['icp'] = Char_cv($config['icp']);
		$config['icpurl'] = Char_cv($config['icpurl']);
	}
	if ($job == 'core') {
		InitGP(array('config','facesize','domainlen'),'P');
		$config['cvtime'] = (int)$config['cvtime'];
		$config['refreshtime'] = (int)$config['refreshtime'];
		$config['onlinetime'] = (int)$config['onlinetime']*60;
		$config['maxresult'] = (int)$config['maxresult'];
		$config['facesize'] = implode("\t",CheckInt($facesize));
		$config['domainlen'] = implode("\t",CheckInt($domainlen));
		$config['domainhold'] = ArrayTxt($config['domainhold'],' ',array('www','bbs','blog'));
	}
	if ($job == 'code') {
		$gdcheck = GetGP('gdcheck','P','0');
		$config['gdcheck'] = implode("\t",CheckInt($gdcheck));
	}
	if ($job == 'meta') {
		$metadata = GetGP('metadata','P');
		$config['metadata'] = implode('@:wind:@',$metadata);
	}
	if ($job == 'cate') {
		$showcate = GetGP('showcate','P');
		$config['showcate'] = implode("\t",$showcate);
	}
	if ($job == 'mail') {
		InitGP(array('config'),'P');
		//include_once(D_P.'data/cache/mail_config.php');
		$ml_smtppass = substr($ml_smtppass,0,1).'********'.substr($ml_smtppass,-1);
		foreach($config as $key=>$value){
			if($$key != $value){
				$rt=$db->get_one("SELECT db_name FROM pw_setting WHERE db_name='$key'");
				if($rt['db_name']==$key){
					$db->update("UPDATE pw_setting SET db_value='$value' WHERE db_name='$key'");
				} else{
					$db->update("INSERT INTO pw_setting(db_name,db_value) VALUES ('$key','$value')");
				}
			}
		}
	}
	!is_array($config) && $config = array();
	updatesetting($config);
	adminmsg('operate_success');
}
?>