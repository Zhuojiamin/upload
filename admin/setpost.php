<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= "&job=$job";
if ($_POST['step']!=2) {
	if ($job == 'post') {
		$lenlimit = explode(',',$db_lenlimit);
		$ifcheckdb = array('postcheck' => $db_postcheck,'commentcheck' => $db_commentcheck,'transfer' => $db_transfer,'copyctrl' => $db_copyctrl,'autoimg' => $db_autoimg,'setform' => $db_setform,'setassociate' => $db_setassociate,'articleurl' => $db_articleurl);
		ifcheck($ifcheckdb);
	}
	if ($job == 'form') {
		InitGP(array('deal'),'G');
		if(empty($deal)){
			$ifcheckdb = array('setform' => $db_setform);
			ifcheck($ifcheckdb);
			$setformdb=array();
			$query=$db->query("SELECT * FROM pw_setforms");
			while($rt=$db->fetch_array($query)){
				${'c_'.$rt['id']} = $rt['ifopen'] ? 'checked' : '';
				$setformdb[]=$rt;
			}
		}elseif($deal == 'add'){
			$num = 0;
		}elseif($deal == 'edit'){
			InitGP(array('id'));
			@extract($db->get_one("SELECT name,value FROM pw_setforms WHERE id='$id'"));
			!$name && adminmsg('operate_error');
			$setform = unserialize($value);
			$num     = count($setform);
		}
	}
	if ($job == 'smile') {
		InitGP(array('deal'),'G');
		if(empty($deal)){
			$query=$db->query("SELECT * FROM pw_smile WHERE type=0 ORDER BY vieworder");
			while($simles=$db->fetch_array($query)){
			$simledb[]=$simles;
			}
			$shownum = count($simledb);
			@extract($db->get_one("SELECT db_value AS db_smileshownum FROM pw_setting WHERE db_name='db_smileshownum'"));
		}elseif($deal == 'smilemanage'){
			InitGP(array('id'));
			@extract($db->get_one("SELECT * FROM pw_smile WHERE id='$id'"));
			$rs=$db->query("SELECT * FROM pw_smile WHERE type='$id' ORDER BY vieworder");
			$smiles_new = $smiles_old = $smiles = array();
			$picext = array("gif","bmp","jpeg","jpg","png");
			while($smiledb=$db->fetch_array($rs)){
				$smiledb['src']="$imgpath/smile/$path/{$smiledb[path]}";
				$smiles_old[]=$smiledb['path'];
				$smiles[]=$smiledb;
			}
			$smilepath="$imgdir/smile/$path";
		
			$fp=opendir($smilepath);
			$i=0;
			while($smilefile = readdir($fp)){
				if(in_array(strtolower(end(explode(".",$smilefile))),$picext)){
					if(!in_array($smilefile,$smiles_old)){
						$i++;
						$smiles_new[$i]['path']=$smilefile;
						$smiles_new[$i]['src']="$imgpath/smile/$path/$smilefile";
					}
				}
			}
			closedir($fp);
		}elseif($deal == 'delsmile'){
			InitGP(array('smileid','typeid'));
			$db->update("DELETE FROM pw_smile WHERE id='$smileid'");
			updatecache_smile();
			adminmsg('operate_success',"$basename&deal=smilemanage&id=$typeid");
		}elseif($deal == 'delete'){
			InitGP(array('id'));
			$db->update("DELETE FROM pw_smile WHERE id='$id'");
			$db->update("DELETE FROM pw_smile WHERE type='$id'");
			updatecache_smile();
			adminmsg('operate_success');
		}
	}
	if ($job == 'att') {
		$db_uploadmaxsize = ceil($db_uploadmaxsize/1024);
		ifcheck(array('allowupload' => $db_allowupload));
	}
	if ($job=='mini') {
		list($db_thumbw,$db_thumbh)=explode("\t",$db_thumbwh);
		ifcheck(array('thumbifopen' => $db_thumbifopen));
	}
	if ($job=='code') {
		foreach ($db_post as $key => $value) {
			strpos($key,'if')!==false && $ifcheckdb['post'.$key] = $value;
		}
		foreach ($db_sign as $key => $value) {
			strpos($key,'if')!==false && $ifcheckdb['sign'.$key] = $value;
		}
		ifcheck($ifcheckdb);
	}
	if ($job=='credit') {
		list($credit_1,$credit_2,$credit_3,$credit_4,$credit_5,$credit_6) = explode(',',$db_credit);
		$credit_1 /= 10;
		$credit_3 /= 10;
		$credit_5 /= 10;
	}
	if ($job=='ajax') {
		$iconv = $spani = $recode_string = $spanr = $libiconv = $spanl = $mb_convert_encoding = $spanm = '';
		if (!function_exists('iconv')) {
			$iconv = 'DISABLED';
			$spaniconv = 'style="color:#FF0000"';
		}
		if (!function_exists('recode_string')) {
			$recode_string = 'DISABLED';
			$spanr = 'style="color:#FF0000"';
		}
		if (!function_exists('libiconv')) {
			$libiconv = 'DISABLED';
			$spanl = 'style="color:#FF0000"';
		}
		if (!function_exists('mb_convert_encoding')) {
			$mb_convert_encoding = 'DISABLED';
			$spanm = 'style="color:#FF0000"';
		}
		!$db_charsetmod && $db_charsetmod = 'charset_string';
		$db_charsetmod = str_replace('N_','',$db_charsetmod);
		!${$db_charsetmod} && ${$db_charsetmod} = 'CHECKED';
		$span = ${$db_charsetmod} == 'DISABLED' ? 'style="color:#FF0"' : '';
		!$db_iconvpre && $db_iconvpre = 'TRANSLIT';
		!${$db_iconvpre} && ${$db_iconvpre} = 'CHECKED';
	}
	include PrintEot('setpost');footer();
} else {
	$job != 'credit' && $config = GetGP('config','P');
	($job == 'post' || $job=='code') && $config = CheckInt($config);
	if ($job == 'post') {
		$lenlimit = GetGP('lenlimit','P');
		$config['lenlimit'] = implode(',',CheckInt($lenlimit));
	}
	if($job == 'form') {
		if($_POST['deal'] == 'add'){
			InitGP(array('name','value','descipt'),'P');
			(!$name || !$value) && adminmsg('setform_empty');
			$setform = array();
			foreach($value as $k=>$v){
				$setform[] = array($v,$descipt[$k]);
			}
			$setform = addslashes(serialize($setform));
			$db->update("INSERT INTO pw_setforms (name,ifopen,value) VALUES('$name','1','$setform')");
			updatecache_form();
			adminmsg("operate_success");
		}elseif($_POST['deal'] == 'del'){
			InitGP(array('selid','ifopen'),'P');
			!is_array($config) && $config = array();
			updatesetting($config);
			if($selid = checkselid($selid)){
				$db->update("DELETE FROM pw_setforms WHERE id IN($selid)");
			}
			$db->update("UPDATE pw_setforms SET ifopen='0'");
			if($ifopen = checkselid($ifopen)){
				$db->update("UPDATE pw_setforms SET ifopen='1' WHERE id IN($ifopen)");
			}
			updatecache_form();
			adminmsg("operate_success");
		}elseif($_POST['deal'] == 'edit'){
			InitGP(array('id','name','value','descipt'),'P');
			(!$name || !$value) && adminmsg('setform_empty');
			$setform = array();
			foreach($value as $k=>$v){
				$setform[] = array($v,$descipt[$k]);
			}
			$setform = addslashes(serialize($setform));
			$db->update("UPDATE pw_setforms SET name='$name',value='$setform' WHERE id='$id'");
			updatecache_form();
			adminmsg("operate_success");
		}
	}
	if ($job == 'smile') {
		if($_POST['deal'] == 'addsmile'){
			InitGP(array('path','name','vieworder'),'P');
			if(empty($path) || !is_dir("$imgdir/smile/$path")){
				adminmsg('smile_path_error');
			}
			empty($name) && adminmsg('smile_name_error');
			$rs = $db->get_one("SELECT COUNT(*) AS sum FROM pw_smile WHERE path='$path'");
			$rs['sum']>=1 && adminmsg('smile_rename');
			$vieworder=(int)$vieworder;
			$db->update("INSERT INTO pw_smile(path,name,vieworder) VALUES('$path','$name','$vieworder')");
			updatecache_smile();
			adminmsg('operate_success');
		}elseif($_POST['deal']=='editsmiles'){
			InitGP(array('vieworder','smileshownum','name'),'P');
			foreach($vieworder as $key=>$value){
				$value=(int)$value;
				$smilesname=Char_cv($name[$key]);
				$db->update("UPDATE pw_smile SET name='$smilesname',vieworder='$value' WHERE id='$key'");
			}
			
			$db->pw_update(
				"SELECT db_value FROM pw_setting WHERE db_name='db_smileshownum'",
				"UPDATE pw_setting SET db_value='$smileshownum' WHERE db_name='db_smileshownum'",
				"INSERT INTO pw_setting (db_name,db_value) VALUES ('db_smileshownum','$smileshownum')"
			);
			updatecache_smile();
			adminmsg('operate_success');
		}elseif($_POST['deal'] == 'addsmiles'){
			InitGP(array('add','id'),'P');
			foreach($add as $value){
				$db->update("INSERT INTO pw_smile SET path='$value',type='$id'");
			}
			updatecache_smile();
			adminmsg('operate_success',"$basename&deal=smilemanage&id=$id");
		}
	}
	if ($job=='att') {
		$config['attachnum'] = (int)$config['attachnum'];
		$config['uploadmaxsize'] = (int)$config['uploadmaxsize'];
		$config['uploadmaxsize'] *= 1024;
		$config['uploadfiletype'] = ArrayTxt($config['uploadfiletype']);
	}
	if ($job=='mini') {
		$thumbwh = GetGP('thumbwh','P');
		$config['thumbwh'] = implode("\t",CheckInt($thumbwh));
	}
	if ($job=='credit') {
		$credit = GetGP('credit','P');
		$credit[1] *= 10;
		$credit[3] *= 10;
		$credit[5] *= 10;
		$config['credit'] = implode(',',CheckInt($credit));
	}
	!is_array($config) && $config = array();
	$job != 'smile' && updatesetting($config);
	adminmsg('operate_success');
}
?>