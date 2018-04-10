<?php
!function_exists('adminmsg') && exit('Forbidden');

function updatecache(){
	updatecache_db();
	updatecache_rl();
	updatecache_itemnav();
	updatecache_cate();
	updatecache_level();
	updatecache_group();
	updatecache_hc();
	updatecache_adv();
	updatecache_style();
	updatecache_word();
	updatecache_novosh();
	updatecache_smile();
	updatecache_form();
	update_hobby();
}
function updatecache_db(){
	global $db;
	$configdb = "<?php\r\n";
	$query = $db->query("SELECT db_name,db_value FROM pw_setting WHERE db_name LIKE 'db_%'");
	while (@extract(db_cv($db->fetch_array($query)))) {
		$db_name	= key_cv($db_name);
		$db_name	= stripslashes($db_name);
		$configdb  .= "\$$db_name='$db_value';\r\n";
	}
	$configdb .= "?>";
	writeover(D_P.'data/cache/config.php',$configdb);
}
function updatecache_rl(){
	global $db;
	$regdb = "<?php\r\n";
	$query = $db->query("SELECT db_name,db_value FROM pw_setting WHERE db_name LIKE 'rg_%' OR db_name LIKE 'lg_%'");
	while (@extract(db_cv($db->fetch_array($query)))) {
		$db_name = key_cv($db_name);
		$db_name = stripslashes($db_name);
		$regdb	.= "\$$db_name='$db_value';\r\n";
	}
	$regdb .= "?>";
	writeover(D_P.'data/cache/dbreg.php',$regdb);
}
function updatecache_itemnav(){
	global $db;
	$writeitemnav = '';
	$navhead = $navfoot = $navdb = array();
	$query = $db->query("SELECT * FROM pw_itemnav WHERE _ifshow='1' ORDER BY vieworder ASC");
	while ($rt = db_cv($db->fetch_array($query))) {
		$navdb = explode(',',$rt['type']);
		foreach ($navdb as $value) {
			in_array($value,array('head','foot')) && ${'nav'.$value}[] = array('url' => $rt['url'],'_ifblank' => $rt['_ifblank'],'name' => $rt['name']);
		}
	}
	$writeitemnav .= '$_HEADNAV = '.N_var_export($navhead,0).";\r\n\r\n";
	$writeitemnav .= '$_FOOTNAV = '.N_var_export($navfoot,0).";\r\n";
	writeover(D_P.'data/cache/itemnav_cache.php',"<?php\r\n$writeitemnav?>");
}
function updatecache_cate($catetype = null){
	global $db;
	$where = !empty($catetype) ? "WHERE catetype='$catetype'" : '';
	$db->update("UPDATE pw_categories SET type='0',cupinfo='' $where".($where ? " AND" : "WHERE")." cup='0'");
	$catedb = $subdb = array();
	$typedb = array('blog','bookmark','music','photo','team','user');
	$query = $db->query("SELECT cid,cup,type,name,descrip,cupinfo,counts,vieworder,_ifshow,catetype,fid FROM pw_categories $where ORDER BY vieworder,cid");
	while ($rt = db_cv($db->fetch_array($query))) {
		if (in_array($rt['catetype'],$typedb)) {
			P_unlink(D_P."data/cache/cate_cid_$rt[cid].php");
			!is_array(${'_'.$rt['catetype']}) && ${'_'.$rt['catetype']} = array();
			$rt['name']	   = Char_cv($rt['name'],'N');
			$rt['descrip'] = Char_cv($rt['descrip'],'N');
			if ($rt['cup'] == 0) {
				$catedb[] = $rt;
			} else {
				$subdb[$rt['cup']][] = $rt;
			}
		}
	}
	foreach ($catedb as $cate) {
		if (empty($cate)) continue;
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($subdb[$cate['cid']])) continue;
		${'_'.$cate['catetype']} += get_subcate($subdb,$cate['cid']);
	}
	foreach ($typedb as $value) {
		if (!empty($catetype) && $value != $catetype) {
			continue;
		}
		$writecache = '$_'.strtoupper($value).' = '.N_var_export(${'_'.$value},0).";\r\n";
		writeover(D_P."data/cache/forum_cache_{$value}.php","<?php\r\n$writecache?>");
	}
}
function get_subcate($array,$cid,$type = 0,$cupinfo = null){
	global $db;
	$type++;
	$cupinfo .= (empty($cupinfo) ? '' : ',').$cid;
	foreach ($array[$cid] as $cate) {
		if (empty($cate)) continue;
		$sql = '';
		if ($cate['type'] != $type) {
			$cate['type'] = $type;
			$sql .= "type='$type'";
		}
		if ($cate['cupinfo'] != $cupinfo) {
			$cate['cupinfo'] = $cupinfo;
			$sql && $sql .= ',';
			$sql .= "cupinfo='$cupinfo'";
		}
		$sql && $db->update("UPDATE pw_categories SET $sql WHERE cid='$cate[cid]'");
		${'_'.$cate['catetype']}[$cate['cid']] = $cate;
		if (empty($array[$cate['cid']])) {
			continue;
		}
		${'_'.$cate['catetype']} += get_subcate($array,$cate['cid'],$type,$cupinfo);
	}
	return ${'_'.$cate['catetype']};
}
function updatecache_level(){
	global $db;
	$writegroup = '';
	$typedb = array('default','system','member','special');
	$query = $db->query('SELECT gid,type,title,img,creditneed FROM pw_group ORDER BY creditneed,gid');
	while ($rt = db_cv($db->fetch_array($query))) {
		if (in_array($rt['type'],$typedb)) {
			!is_numeric($rt['creditneed']) && $rt['creditneed'] = 20*pow(2,$rt['creditneed']);
			!is_array(${'_'.$rt['type']}) && ${'_'.$rt['type']} = array();
			${'_'.$rt['type']}[$rt['gid']] = array('title' => $rt['title'],'img' => (int)$rt['img'],'creditneed' => (int)$rt['creditneed']);
		}
	}
	foreach ($typedb as $value) {
		$writegroup .= "\$_g{$value} = ".N_var_export(${'_'.$value},0).";\r\n";
	}
	writeover(D_P.'data/cache/level_cache.php',"<?php\r\n$writegroup?>");
}
function updatecache_group($gid = null){
	global $db;
	$groupdb = array();
	if (empty($gid)) {
		$query = $db->query("SELECT gid,type,ifdefault,mright,admincp FROM pw_group WHERE ifdefault='0'");
		while ($rt = db_cv($db->fetch_array($query))) {
			$groupdb[] = $rt;
		}
	} else {
		$groupdb[] = db_cv($db->get_one("SELECT gid,type,ifdefault,mright,admincp FROM pw_group WHERE gid='$gid'"));
	}
	foreach ($groupdb as $group) {
		$group['mright'] = $group['mright'] ? unserialize($group['mright']) : array();
		foreach ($group['mright'] as $key => $value) {
			$group[$key] = $value;
		}
		$group['mright'] = null;
		unset($group['mright']);
		$writegroup = '$_GROUP = '.N_var_export($group,0).";\r\n";
		writeover(D_P."data/groupdb/group_{$group[gid]}.php","<?php\r\n$writegroup?>");
	}
}
function updatecache_hc(){
	global $db;
	$hcdb = array();
	$query = $db->query("SELECT * FROM pw_hobby ORDER BY vieworder");
	while ($rt = db_cv($db->fetch_array($query))) {
		$rt['name'] = Char_cv($rt['name'],'N');
		$hcdb[$rt['id']] = $rt;
	}
	writeover(D_P.'data/cache/hcate_cache.php',"<?php\r\n\$_HCATE = ".N_var_export($hcdb,0).";\r\n?>");
}
function updatecache_adv(){
	global $db;
	$advdb = array();
	$query = $db->query("SELECT * FROM pw_advt ORDER BY postdate DESC");
	while ($rt = db_cv($db->fetch_array($query))) {
		$advdb[$rt['id']] = $rt;
	}
	writeover(D_P.'data/cache/adv_cache.php',"<?php\r\n\$_AD = ".N_var_export($advdb,0).";\r\n?>");
}
function updatecache_style($name = null){
	global $db;
	$writestyle = $sql = $configdb = '';
	!empty($name) && $sql = " WHERE name='$name'";
	$query = $db->query("SELECT name,stylepath,tplpath FROM pw_style{$sql}");
	while (@extract(db_cv($db->fetch_array($query)))) {
		$configdb .= "\$stylepath = '$stylepath';\r\n";
		$configdb .= "\$tplpath = '$tplpath';";
		writeover(D_P."data/style/$name.php","<?php\r\n$configdb\r\n?>");
	}
}
function updatecache_word($wdtype = null){
	global $db;
	$replace = $forbid = array();
	$sql = '';
	if (!empty($wdtype)) {
		include(D_P.'data/cache/wordfb.php');
		if ($wdtype == 'replace' && !empty($_FORBID)) {
			$forbid = $_FORBID;
		} elseif ($wdtype == 'forbid' && !empty($_REPLACE)) {
			$replace = $_REPLACE;
		}
		$sql = " WHERE type='$wdtype'";
	}
	$query = $db->query("SELECT id,word,wordreplace,type FROM pw_replace{$sql}");
	while (@extract(db_cv($db->fetch_array($query)))) {
		if ($word && $wordreplace) {
			${$type}[$id] = array('id' => $id,'word' => $word,'wordreplace' => $wordreplace);
		}
	}
	writeover(D_P.'data/cache/wordfb.php',"<?php\r\n\$_REPLACE = ".N_var_export($replace,0).";\r\n\$_FORBID = ".N_var_export($forbid,0).";\r\n?>");
}
function updatecache_novosh($novo = null){
	global $db;
	@include(D_P.'data/cache/novosh_cache.php');
	empty($_NOTICE) && $_NOTICE = array();
	empty($_VOTEDB) && $_VOTEDB = array();
	empty($_SHARE) && $_SHARE = array();
	empty($_SHARELOGO) && $_SHARELOGO = array();
	$writecache = '';
	if (empty($novo) || $novo=='no') {
		$_NOTICE = array();
		$query = $db->query("SELECT aid,url,subject,startdate FROM pw_notice ORDER BY vieworder,startdate DESC LIMIT 0,3");
		while ($rt = db_cv($db->fetch_array($query))) {
			$rt['subject'] = substrs($rt['subject'],30);
			$_NOTICE[] = $rt;
		}
	}
	if (empty($novo) || $novo=='vo') {
		$_VOTEDB = array();
		$query = $db->query("SELECT id,subject,voteitem,type,maxnum,_ifview FROM pw_blogvote WHERE _ifshow='1' ORDER BY votedate DESC LIMIT 0,1");
		while ($rt = db_cv($db->fetch_array($query))) {
			$rt['voteitem'] = $rt['voteitem'] ? unserialize($rt['voteitem']) : array();
			if ((int)$rt['type'] != 1 || $rt['maxnum'] == 1) {
				$rt['type'] = 0;
				$rt['maxnum'] = 1;
			}
			$_VOTEDB = $rt;
		}
	}
	if (empty($novo) || $novo=='sh') {
		$_SHARE = $_SHARELOGO = array();
		$query = $db->query("SELECT name,url,descrip,logo FROM pw_share WHERE ifcheck='1' ORDER BY threadorder,sid DESC");
		while ($rt = db_cv($db->fetch_array($query))) {
			if ($rt['logo']) {
				$_SHARELOGO[] = array('logo' => $rt['logo'],'url' => $rt['url'],'descrip' => $rt['descrip']);
			} else {
				$_SHARE[] = array('name' => $rt['name'],'url' => $rt['url'],'descrip' => $rt['descrip']);
			}
		}
	}
	$writecache .= '$_NOTICE = '.N_var_export($_NOTICE,0).";\r\n";
	$writecache .= '$_VOTEDB = '.N_var_export($_VOTEDB,0).";\r\n";
	$writecache .= '$_SHARE = '.N_var_export($_SHARE,0).";\r\n\$_SHARELOGO = ".N_var_export($_SHARELOGO,0);
	writeover(D_P.'data/cache/novosh_cache.php',"<?php\r\n$writecache?>");
}
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
function updatesetting($array = array(),$pre = 'db_',$upfunc = 'updatecache_db'){
	global $db;
	if (empty($array)) return false;
	$configdb = array();
	$query = $db->query("SELECT db_name,db_value FROM pw_setting WHERE db_name LIKE '{$pre}_%'");
	while (@extract(db_cv($db->fetch_array($query)))) {
		$configdb[$db_name] = $db_value;
	}
	$db->free_result($query);
	foreach ($array as $key => $value) {
		$newdbname = $pre.$key;
		$c_key = $GLOBALS[$newdbname];
		$value = trim($value);
		if (strpos($key,'_')!==false) {
			$c_db		= explode('_',$key);
			$newdbname  = $pre.$c_db[0];
			$c_db_array = $GLOBALS[$newdbname];
			$c_key		= $c_db_array[$c_db[1]];
			$newdbname	= $pre.str_replace('_',"[\'",$key)."\']";
		}
		if ($c_key!=$value || $configdb[$newdbname]!=$value) {
			$db->get_value("SELECT db_name FROM pw_setting WHERE db_name='$newdbname'") ? $db->update("UPDATE pw_setting SET db_value='$value' WHERE db_name='$newdbname'") : $db->update("INSERT INTO pw_setting(db_name,db_value) VALUES ('$newdbname','$value')");
		}
	}
	unset($configdb,$array);
	$upfunc && $upfunc();
	return true;
}
function updatecache_smile(){
	global $db;
	$smile="\$smile=array(\r\n"; //表情
	$smiles="\$smiles=array(\r\n"; //表情组
	$jssmile="var smile=new Array();\n";
	$jssmiles="var smiles=new Array();\n";
	$jssmiledb="var smiledb=new Array();\n";
	$count=0;
	@extract($db->get_one("SELECT db_value AS db_smileshownum FROM pw_setting WHERE db_name='db_smileshownum'"));
	$rs=$db->query("SELECT * FROM pw_smile WHERE type=0 ORDER BY vieworder");
	while(@extract(db_cv($db->fetch_array($rs)))){
		if($count==0){
			$jsdefault="var defaultsmile='$path';\nvar db_smileshownum='$db_smileshownum';\n\n";
			$count=1;
		}
		$smiles.="\t'$path'=>array(\r\n";
		$smiles.="\t\t'name'=>'$name',\r\n";
		$smiles.="\t\t'child'=>array(";
		$jssmiles.="smiles['$path'] = [";
		$jssmiledb.="smiledb['$path'] = '$name';\n";
		$query=$db->query("SELECT * FROM pw_smile WHERE type='$id' ORDER BY vieworder");
		while($rs2=db_cv($db->fetch_array($query))) {
			$smile.="\t'$rs2[id]'=>'$path/$rs2[path]',\r\n";
			$smiles.="'$rs2[id]',";
			
			$jssmile.="smile[$rs2[id]]='$path/$rs2[path]';\n";
			$jssmiles.="$rs2[id],";
		}
		$smiles.="),\r\n";
		$smiles.="\t),\r\n";
		$jssmiles.="];\n";
	}
	$smiles.=");\r\n";
	$smile.=");";
	writeover(D_P."data/cache/smile.js",$jsdefault.$jssmiledb."\n".$jssmile."\n".$jssmiles);
	writeover(D_P."data/cache/smile_cache.php","<?php\r\n".$smiles.$smile."\r\n?>");
}

function updatecache_form(){
	global $db;
	$rs=$db->query("SELECT id,name,value FROM pw_setforms WHERE ifopen='1'");
	$setform="<?php\n\$setformdb=array(\n";
	while($rt=$db->fetch_array($rs)){
		$rt['value'] = unserialize($rt['value']);
		$setformdb[$rt['id']] = $rt;
		$setformdb[] = $rt;
	}
	$setformdb = "\$setformdb=".pw_var_export($setformdb).";\r\n";
	writeover(D_P.'data/cache/setform.php',"<?php\r\n".$setformdb."?>");
}

function update_hobby(){
	global $db;

	$query=$db->query("SELECT * FROM pw_hobby ORDER BY vieworder");
	while($rt=db_cv($db->fetch_array($query))){
		$hobbydb[$rt['id']]=$rt;
	}
	$hobbycache.="\$_HOBBY=".pw_var_export($hobbydb).";\r\n\r\n";

	writeover(D_P.'data/cache/hobby_cache.php',"<?php\r\n$hobbycache?>");
}

function write_config($setting=array()){
	global $cplang;
	!is_array($setting) && adminmsg('illegal_request');
	include D_P.'data/sql_config.php';
	empty($pconnect) && $pconnect=0;
	if (!empty($setting)) {
		$setting['user'] && $manager	 = $setting['user'];
		$setting['pwd']  && $manager_pwd = $setting['pwd'];
		$setting['pic']  && $picpath	 = $setting['pic'];
		$setting['att']  && $attachpath  = $setting['att'];
	}
	$writetofile = 
"<?php
/**
* $cplang[dbinfo]
*/
\$dbhost = '$dbhost';			//$cplang[dbhost]
\$dbuser = '$dbuser';			//$cplang[dbuser]
\$dbpw = '$dbpw';				//$cplang[dbpw]
\$dbname = '$dbname';			//$cplang[dbname]
\$database = '$database';			//$cplang[database]
\$PW = '$PW';					//$cplang[PW]
\$pconnect = '$pconnect';		//$cplang[pconnect]

/**
* $cplang[charset]
*/
\$charset = '$charset';

/**
* $cplang[ma_info]
*/
\$manager = '$manager';			//$cplang[ma_name]
\$manager_pwd = '$manager_pwd';	//$cplang[ma_pwd]

/**
* $cplang[pic_att]
*/
\$picpath = '$picpath';
\$attachpath = '$attachpath';
".'?>';
	writeover(D_P.'data/sql_config.php',$writetofile);
}
function db_cv($array = array()){
	$array = is_array($array) ? array_map('db_cv',$array) : str_replace(array("\\","'"),array("\\\\","\'"),$array);
	return $array;
}
function key_cv($key){
	$key = str_replace(array(';','\\','/','(',')','$'),'',$key);
	return $key;
}
function ifcheck($array = array(),$yn = 'Y_N'){
	!is_array($array) && adminmsg('undefined_actions');
	list($y,$n) = explode('_',$yn);
	foreach ($array as $key => $value) {
		global ${$key.'_'.$y},${$key.'_'.$n};
		if ($value) {
			${$key.'_'.$y} = 'CHECKED';
			${$key.'_'.$n} = '';
		} else {
			${$key.'_'.$y} = '';
			${$key.'_'.$n} = 'CHECKED';
		}
	}
}
function Strip_S(&$array){
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (!is_array($value)) {
				$array[$key] = stripslashes($value);
			} else {
				Strip_S($array[$key]);
			}
		}
	}
}
function ArrayTxt($arraytxt,$extra = ' ',$cut = array()){
	$writewrite = $commer = '';
	$array = explode($extra,$arraytxt);
	$array = array_unique($array);
	foreach ($array as $value) {
		if ($value && (empty($cut) || !N_InArray($value,$cut))) {
			$writewrite .= $commer.trim($value);
			$commer = $extra;
		}
	}
	return $writewrite;
}
function CheckInt($array){
	if (is_array($array)) {
		$array = array_map('CheckInt',$array);
	} else {
		$array = (int)$array;
	}
	return $array;
}
function CheckHtml($array){
	if (is_array($array)) {
		$array = array_map('CheckHtml',$array);
	} else {
		$array = htmlspecialchars($array);
	}
	return $array;
}
function substrs($content,$length = null,$add=' ..'){
	global $db_charset;
	if (empty($length)) return $content;
	if (strlen($content) > $length) {
		if ($db_charset!='utf-8') {
			$retstr = '';
			for ($i = 0; $i < $length - 2; $i++) {
				$retstr .= ord($content[$i]) > 127 ? $content[$i].$content[++$i] : $content[$i];
			}
			return $retstr.$add;
		} else {
			return utf8_trim(substr($content,0,$length)).$add;
		}
	} else {
		return $content;
	}
}
function utf8_trim($str){
	$hex = '';
	for ($i=strlen($str)-1;$i>=0;$i-=1) {
		$hex .= ' '.ord($str[$i]);
		$ch   = ord($str[$i]);
		if (($ch & 128)==0 || ($ch & 192)==192) {
			return substr($str,0,$i);
		}
	}
	return($str.$hex);
}
function P_unlink($filename){
	strpos($filename,'..')!==false && exit('Forbidden');
	@unlink($filename);
}
function get_date($timestamp,$timeformat = null){
	global $db_datefm,$db_timedf,$_datefm,$_timedf;
	if (empty($timeformat)) {
		$timeformat = $_datefm ? $_datefm : $db_datefm;
	}
	if ($_timedf) {
		$offset = $_timedf=='111' ? 0 : $_timedf;
	} else {
		$offset = $db_timedf=='111' ? 0 : $db_timedf;
	}
	return gmdate($timeformat,$timestamp+$offset*3600);
}
function N_InArray($needle,$haystack){
	if (is_array($haystack) && in_array($needle,$haystack)) {
		return true;
	}
	return false;
}

function pw_var_export($input,$f = 1,$t = null) {
	$output = '';
	if (is_array($input)) {
		$output .= "array(\r\n";
		foreach ($input as $key => $value) {
			$output .= $t."\t".pw_var_export($key,$f,$t."\t").' => '.pw_var_export($value,$f,$t."\t");
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