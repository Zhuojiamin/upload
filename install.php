<?php
/***************************************************
*	install.php - installation of PHPwind		   *
*	Author: Fengyu,Yuling,Noizy,zhudong			   *
*	PHPwind (http://www.phpwind.net)			   *
***************************************************/
error_reporting(E_ERROR | E_PARSE);
@set_time_limit(0);
set_magic_quotes_runtime(0);

empty($_POST) && $_POST = array();
empty($_GET)  && $_GET  = array();
if (!get_magic_quotes_gpc()) {
	!empty($_POST)	 && Add_S($_POST);
	!empty($_GET)	 && Add_S($_GET);
	!empty($_COOKIE) && Add_S($_COOKIE);
}
foreach ($_POST as $_key => $_value) {
	!ereg('^\_',$_key) && !isset(${$_key}) && ${$_key} = $_value;
}
foreach ($_GET as $_key => $_value) {
	!ereg('^\_',$_key) && !isset(${$_key}) && ${$_key} = $_value;
}

eval('$__file__=__FILE__;');
define('R_P',getdirname(__FILE__));
define('D_P',R_P);

function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');
if ($_SERVER['HTTP_CLIENT_IP']) {
	$onlineip = $_SERVER['HTTP_CLIENT_IP'];
} elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
	$onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$onlineip = $_SERVER['REMOTE_ADDR'];
}
$onlineip = preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/',$onlineip) ? $onlineip : 'Unknown';

if (!($REQUEST_URI=$_SERVER['REQUEST_URI'])) {
		$REQUEST_URI = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	}
$blogurl = 'http://'.$_SERVER['HTTP_HOST'].substr($REQUEST_URI,0,strrpos($REQUEST_URI,'/'));

ob_start();
@include(R_P.'lang/install_lang.php');
$wind_version='6.0';
$B_url = rawurlencode($_SERVER['HTTP_HOST']);
$systitle = $lang['title_install'];
if ($step) {
	$stepmsg = $lang['step_'.$step];
	$stepleft = $lang['step_'.$step.'_left'];
	$stepright = $lang['step_'.$step.'_right'];
}
require_once(R_P.'lang/header.htm');
file_exists(R_P.'data/install.lock') && Promptmsg('have_file');
$steptitle = $step;

$basename = 'install.php';
if (!$step) {
	$footer = true;
	$lang['log_install'] = str_replace('{#basename}',$basename,$lang['log_install']);
	//$lang['log_partner'] = @file("http://u.phpwind.com/install/partner.php?url=$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]");
	//$lang['log_partner'] = implode('',$lang['log_partner']);
}elseif($step==1) {
	$wind_licence = str_replace(array('  ',"\n"),array('&nbsp; ','<br />'),readover('licence.txt'));
	writeover(R_P.'data/log1.txt',$lang['success_1']);
} elseif ($step==2) {
	include(D_P.'data/sql_config.php');
	$w_check = array(
		'attachment',
		'data',
		'data/sql_config.php',
		'data/cache',
		'data/groupdb',
		'data/style',
		'image',
		'image/upload',
		'ipdata',
		'theme',
		'theme/default',
		'theme/default/template'
	);
	$msg = array();
	foreach ($w_check as $filename) {
		!file_exists(R_P.$filename) && Promptmsg('error_unfind');
		!is_writable(R_P.$filename) && Promptmsg('error_777');
		$msg[] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_2']);
	}
	$msg = implode("\n",$msg);
	writeover(R_P.'data/log2.txt',$msg);
} elseif ($step==3) {
	@set_time_limit(0);
	$content = readover(D_P.'lang/install_wind.sql');
	$content = preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content);
	writeover(D_P.'data/wind.sql',$content);
	include(D_P.'data/sql_config.php');
	!$adminemail && Promptmsg('error_nothing');
	$input = "<input type=\"hidden\" name=\"adminemail\" value=\"$adminemail\">";
	if ($_POST['from']!='prompt') {
		if (!$SERVER || !$SQLUSER || !$SQLPASSWORD || !$SQLNAME || !$SQLZUI || !$INSTALL_NAME || !$password) {
			Promptmsg('error_nothing');
		}
		if ($password !== $password_check) {
			Promptmsg('error_ckpwd');
		}
		$password = md5($password);
		$charset = str_replace('-','',$lang['db_charset']);
		$writedata =
"<?php
/**
* $lang[dbinfo]
*/
\$dbhost = '$SERVER';	// $lang[dbhost]
\$dbuser = '$SQLUSER';	// $lang[dbuser]
\$dbpw = '$SQLPASSWORD';	// $lang[dbpw]
\$dbname = '$SQLNAME';	// $lang[dbname]
\$database = 'mysql';	// $lang[dbtype]
\$PW = '$SQLZUI';	//$lang[dbpre]
\$pconnect = '0';	//$lang[dbpconnect]

/*
$lang[dbcharset]
*/
\$charset='$lang[charsets]';

/**
* $lang[dbmanager]
*/
\$manager='$INSTALL_NAME';	//$lang[dbmanagername]
\$manager_pwd='$password';	//$lang[dbmanagerpwd]


/*
* $lang[sql_pic_att]
*/
\$picpath='$picpath';
\$attachpath='$attachpath';
".'?>';
		writeover(R_P.'data/sql_config.php',$writedata);
		require R_P.'data/sql_config.php';
		require Pcv(R_P.'mod/db_'.$database.'.php');
		$db = new DB($dbhost,$dbuser,$dbpw,'',$pconnect);
		if (!@mysql_select_db($dbname)) {
			if (mysql_get_server_info() > '4.1' && $charset) {
				mysql_query("CREATE DATABASE $dbname DEFAULT CHARACTER SET $charset");
			} else {
				mysql_query("CREATE DATABASE $dbname");
			}
			mysql_error() && Promptmsg('error_nodatabase');
		}
		mysql_select_db($dbname);
		$query = $db->query("SHOW TABLES LIKE '{$PW}user'");
		while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
			$rt[0]==$PW.'user' && Promptmsg('have_install',3);
		}
		$db->free_result($query);
	} else {
		require R_P.'data/sql_config.php';
		require Pcv(R_P.'mod/db_'.$database.'.php');
		$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$pconnect);
	}
	$content = readover(R_P.'lang/install_wind.sql');
	$content = preg_replace("/{#(.+?)}/eis",'$lang[\\1]',$content).'<wind>';
	$content = explode("\n",str_replace(array("\r",";\n"),array('',";<wind>\n"),$content));
	$writearray = array($lang['success_3']);
	$writearray = SQLCreate($content);

	$timestamp	= time();
	$t			= getdate($timestamp+8*3600);
	$tdtime		= (floor($timestamp/3600)-$t['hours'])*3600;
	$db->update("INSERT INTO pw_user (username,password,blogtitle,email,publicmail,groupid,memberid,gender,regdate,rvrc,money,lastvisit,thisvisit,onlineip) VALUES ('$manager','$manager_pwd','$manager','$adminemail','1','3','8','0','$timestamp','1','0','$timestamp','$timestamp','$onlineip')");
	$uid = $db->insert_id();
	$domain = !preg_match('/^[a-zA-Z0-9]{3,12}$/',$manager) ? '' : $manager;
	$db->update("INSERT INTO pw_userinfo (uid,cid,style,domainname,wshownum,headerdb,leftdb) VALUES ('$uid','11','default','$domain','200','$lang[headerdb]','$lang[leftdb]')");
	$newmember = $uid.','.$manager;
	$db->update("UPDATE pw_bloginfo SET newmember='$newmember',tdtcontrol='$tdtime',totalmember='1' WHERE id='1'");
	$writearray[] = $lang['success_3_2'];
	$writearray = implode("\n",$writearray);
	writeover(R_P.'data/log3.txt',$writearray);
	$db->update("UPDATE pw_setting SET db_value='$blogurl' WHERE db_name='db_blogurl'");
	$stepright = $lang['success'];
	for ($i=1;$i<4;$i++) {
		$log .= readover(R_P."data/log$i.txt")."\n";
	}
	$log = str_replace("\n",'<wind>',$log);
} elseif ($step=='finish') {
	$steptitle = '!';
	include(D_P.'data/sql_config.php');
	include(Pcv(R_P."mod/db_{$database}.php"));
	include(R_P.'admin/cache.php');
	$db = new DB($dbhost,$dbuser,$dbpw,$dbname,$pconnect);
	unset($dbhost,$dbuser,$dbpw,$pconnect,$manager,$manager_pwd);
	$db->update("UPDATE pw_setting SET db_value='$blogurl' WHERE db_name='db_blogurl'");
	updatecache();
	for ($i=1;$i<5;$i++) {
		@unlink(D_P."data/log$i.txt");
	}
	if (!is_writeable($basename)) {
		$lang['success_install'] .= "<br /><small><font color=\"red\">$lang[error_delinstall]</font></small>";
	}
	$lang['success_install'] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_install']);
	require(R_P.'lang/install.htm');
	@unlink($basename);
	footer();
}
require(R_P.'lang/install.htm');footer();


function Pcv($filename,$ifcheck=1){
	(ereg("^[http([s]?):\/\/]",$filename) || ($ifcheck && strpos($filename,'..')!==false)) && exit('Forbidden');
	return $filename;
}
function writeover($filename,$data,$method="rb+",$iflock=1,$check=1,$chmod=1){
	$check && strpos($filename,'..')!==false && exit('Forbidden');
	touch($filename);
	$handle=fopen($filename,$method);
	if($iflock){
		flock($handle,LOCK_EX);
	}
	fwrite($handle,$data);
	if($method=="rb+") ftruncate($handle,strlen($data));
	fclose($handle);
	$chmod && @chmod($filename,0777);
}
function readover($filename,$method="rb"){
	strpos($filename,'..')!==false && exit('Forbidden');
	if($handle=@fopen($filename,$method)){
		flock($handle,LOCK_SH);
		$filedata=@fread($handle,filesize($filename));
		fclose($handle);
	}
	return $filedata;
}
function getdirname($path){
	if (empty($path)) {
		return './';
	} elseif (strpos($path,'\\')!==false) {
		return substr($path,0,strrpos($path,'\\')).'/';
	} elseif (strpos($path,'/')!==false) {
		return substr($path,0,strrpos($path,'/')).'/';
	} else {
		return './';
	}
}
function Add_S(&$array){
	foreach ($array as $key => $value) {
		if (!is_array($value)) {
			$array[$key] = addslashes($value);
		} else {
			Add_S($array[$key]);
		}
	}
}

function Char_cv($msg,$unhtml = 'Y'){
	if (is_array($msg)) {
		foreach ($msg as $key => $value) {
			$value = Char_cv($value,$unhtml);
			$msg[$key] = $value;
		}
	} else {
		if ($unhtml != 'N') {
			$msg = str_replace(
				array('&amp;','&nbsp;','"',"'",'<','>'),
				array('&',' ','&quot;','&#39;','&lt;','&gt;'),
				$msg
			);
		}
		$msg = str_replace(
			array("\t","\r",'  '),
			array('&nbsp; &nbsp; ','','&nbsp; '),
			$msg
		);
	}
	return $msg;
}
function footer(){
	require_once(R_P.'lang/footer.htm');
	$output = trim(str_replace(array('<!--<!---->','<!---->',"\r",substr(R_P,0,-1)),'',ob_get_contents()),"\n");
	ob_end_clean();
	ob_start();
	echo $output;
	exit;
}

function Promptmsg($msg,$tostep=null){
	@extract($GLOBALS, EXTR_SKIP);
	require(R_P.'lang/install_lang.php');
	$lang[$msg] && $msg = $lang[$msg];
	$msg = preg_replace("/{#(.+?)}/eis",'$\\1',$msg);
	$url = $backurl = 'javascript:history.go(-1);';
	$backmsg = !empty($tostep) ? $stepleft : '';
	if (!$backmsg) {
		$lang['last'] = $lang['back'];
		@unlink("log$step.txt");
	} else {
		$url = "document.getElementById('install').submit();";
	}
	require(R_P.'lang/promptmsg.htm');
	footer();
}

function SQLCreate($sqlarray,$hack = null) {
	global $db,$charset,$writearray,$lang;
	$query = '';
	foreach ($sqlarray as $value) {
		if ($value[0]!='#') {
			$query .= $value;
			if (substr($value,-7)==';<wind>') {
				$lowquery = strtolower(substr($query,0,5));
				$search = array('<wind>');
				$replace = array('');
				$ckarray = empty($hack) ? array('drop ','creat','alter','inser','repla') : array('creat','alter','inser','repla');
				if (in_array($lowquery,$ckarray)) {
					$continue = empty($hack) ? true : CheckDrop($query);
					if ($lowquery == 'creat') {
						if (!$continue) continue;
						if (!empty($hack)) {
							strpos($query,'IF NOT EXISTS')===false && $query = str_replace('TABLE','TABLE IF NOT EXISTS',$query);
						}
						$tablename = trim(substr($query,0,strpos($query,'(')));
						$tablename = substr($tablename,strrpos($tablename,' ')+1);
						$writearray[] = preg_replace("/{#(.+?)}/eis",'$\\1',$lang['success_3_1']);
						$search[1] = trim(substr(strrchr($value,')'),1));
						$tabtype = substr(strchr($search[1],'='),1);
						$tabtype = substr($tabtype,0,strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
						if ($db->server_info() >= '4.1') {
							$replace[1] = "ENGINE=$tabtype".($charset ? " DEFAULT CHARSET=$charset" : '').';';
						} else {
							$replace[1] = "TYPE=$tabtype;";
						}
					} elseif (in_array($lowquery,array('inser','repla'))) {
						if (!$continue) continue;
						$lowquery == 'inser' && $query = 'REPLACE '.substr($query,6);
					} elseif ($lowquery == 'alter' && !$continue && strpos(strtolower($query),'drop')!==false) {
						continue;
					}
					krsort($search);krsort($replace);
					$query = str_replace($search,$replace,$query);
					$db->query($query);
					$query = '';
				}
			}
		}
	}
	return $writearray;
}

function CheckDrop($query){
	global $db;
	require_once(R_P.'admin/table.php');
	$next = true;
	foreach ($tabledb as $value) {
		if (strpos(strtolower($query),strtolower($value))!==false) {
			$next = false;
			break;
		}
	}
	return $next;
}

function adminmsg(){}
function Showmsg(){}
?>