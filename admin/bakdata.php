<?php
!function_exists('adminmsg') && exit('Forbidden');
@set_time_limit(0);
!$job && $job = 'bakout';
$step = GetGP('step');
if ($job == 'bakout' || $job == 'repair') {
	if ($step == 1 || $step == 2) {
		InitGP(array('do','selid'),'P');
		!is_array($selid)  && $selid = array();
		if ($do != 'repair' && $do != 'optimize') {
			$writedata = "\n";
			$db->query('SET SQL_QUOTE_SHOW_CREATE = 0');
			InitGP(array('sizelimit','go','tabledb','pre','start','tableid','rows','fnum'));
			$basename .= "&job=$job";
			if (empty($selid)) {
				!$tabledb && adminmsg('operate_error');
				$selid = explode('|',$tabledb);
			}
			$go = (int)$go;
			if (!$go) {
				empty($selid) && adminmsg('operate_error');
				$sizelimit/=2;
				$tabledb = implode('|',$selid);
				$go = 1; $start = 0;
				$pre = get_date($timestamp,'md').'_'.randstr(10);
				$writedata .= bakuptable($selid,$step);
			}
			$stop = (int)$stop;
			$start = (int)$start;
			$tableid = (int)$tableid > 0 ? $tableid-1 : 0;
			$count = count($selid);
			$f_num = ceil($go/2);
			$go++;
			$writedata .= trim(bakupdata($selid,$step));
			$f_num != $fnum && $writedata = "#\n# LxBlog BakFile\n# Version:".$blog_version."\n# Time: ".get_date($timestamp,'Y-m-d H:i')."\n# PHPwind: http://www.phpwind.net\n# LxBlog: http://www.lxblog.net\n# --------------------------------------------------------\n\n$writedata";
			writeover(D_P.'data/pw_'.$pre.'_'.$f_num.'.sql',$writedata,'ab');
			if ($stop==1) {
				$t_name = $PW.$selid[$tableid-1];
				$basename .= "&step=$step&start=$start&tableid=$tableid&sizelimit=$sizelimit&go=$go&pre=$pre&tabledb=$tabledb&rows=$rows&fnum=$f_num";
				adminmsg('bakup_step');
			} else {
				$bakfile = '';
				if ($go>1) {
					for ($i=1;$i<=$f_num;$i++) {
						$bakfile .= "<a href=\"data/pw_{$pre}_$i.sql\">pw_{$pre}_$i.sql</a><br />";
					}
				}
				adminmsg('bakup_out');
			}
		} else {
			$msgdb = repairdata($do,$selid,$step);
		}
	} else {
		$othortable = array();
		$tabledb = array('advt', 'albums', 'bbsclass', 'blog', 'blogfriend', 'bloginfo', 'blogvote', 'bookmark', 'carticle', 'categories', 'collections', 'comment', 'footprint', 'gbook', 'group', 'hobby', 'hobbyitem', 'itemnav', 'items', 'itemtype', 'lcustom', 'malbums', 'module', 'msgs', 'music', 'notice', 'photo', 'replace', 'rightset', 'schindex', 'setforms', 'setting', 'share', 'smile', 'style', 'taginfo', 'btags', 'tblog', 'team', 'tgbook', 'tuser', 'upload', 'user', 'userhobby', 'userinfo', 'userskin', 'voteitem', 'bgsqlcv');
		$query = $db->query('SHOW TABLES');
		while ($rt = $db->fetch_array($query,'MYSQL_NUM')){
			$value = str_replace($PW,'',$rt[0]);
			!in_array($value,$tabledb) && $othortable[] = $rt[0];
		}
	}
} elseif ($job == 'bakin') {
	if ($step != 1) {
		$filedb = $predb = $bk = array();
		$fp = opendir(D_P.'data');
		while ($datafile = readdir($fp)) {
			if (preg_match('/^pw\_(([a-zA-Z0-9_]+)\_([0-9]+))\.sql$/i',$datafile,$match)) {
				$fps = fopen(D_P."data/$datafile",'rb');
				$info = fread($fps,185);
				fclose($fps);
				$rt = explode("\n",$info);
				$bk['name'] = $datafile;
				$bk['version'] = substr($rt[2],10);
				$bk['time'] = substr($rt[3],8);
				$bk['fullname'] = $match[1];
				$bk['pre'] = $match[2];
				$bk['num'] = $match[3];
				$predb[$match[2]] = (int)$predb[$match[2]]+1;
				$filedb[] = $bk;
			}
		}
		closedir($fp);
	} else {
		$basename .= '&job=bakin';
		InitGP(array('pre','count','go','selid'));
		!is_array($selid)  && $selid = array();
		if (empty($selid)) {
			if ($pre) {
				(int)$go < 1 && $go = 1;
				$count = (int)$count;
				bakindata(D_P."data/pw_{$pre}_$go.sql");
				$go++;
				if ($count > 1 && $go <= $count) {
					$i = $go-1;
					$basename .= "&step=1&go=$go&count=$count&pre=$pre";
					adminmsg('bakup_in');
				}
				updatecache();
			} else {
				adminmsg('operate_error');
			}
		} else {
			foreach ($selid as $value) {
				P_unlink(D_P."data/pw_$value.sql");
			}
		}
		adminmsg('operate_success');
	}
}
function repairdata($do,$tabledb,$step) {
	global $db,$PW;
	$table = $sql = '';
	$msgdb = array();
	foreach ($tabledb as $key => $value) {
		$value = ($step == 1 ? $PW : '').$value;
		$table .= ($table ? ',' : '')."`$value`";
	}
	if ($do == 'repair') {
		$sql = 'REPAIR';
	} elseif ($do == 'optimize') {
		$sql = 'OPTIMIZE';
	}
	if ($sql && $table) {
		$query = $db->query("$sql TABLE $table EXTENDED ");
		while ($rt = $db->fetch_array($query)) {
			$rt['Table']  = substr(strrchr($rt['Table'] ,'.'),1);
			$msgdb[] = $rt;
		}
	}
	return $msgdb;
}
function bakindata($filename) {
	global $db,$charset;
	$query	  = '';
	$sqlarray = file($filename);
	foreach ($sqlarray as $value) {
		$value = trim(str_replace(array("\r","\n"),'',$value));
		if (!empty($value) && $value[0]!='#') {
			$query .= $value;
			if (preg_match('/\;$/i',$value)) {
				if (preg_match('/^CREATE/i',$query)) {
					$extra1 = trim(substr(strrchr($value,')'),1));
					$tabtype = substr(strchr($extra1,'='),1);
					$tabtype = substr($tabtype,0,strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
					if ($db->server_version() >= '40100') {
						$extra2 = "ENGINE=$tabtype".($charset ? " DEFAULT CHARSET=$charset;" : ';');
					} else {
						$extra2 = "TYPE=$tabtype;";
					}
					$query = str_replace($extra1,$extra2,$query);
				} elseif (preg_match('/^INSERT/i',$query)) {
					$query = 'REPLACE '.substr($query,6);
				}
				$db->query($query);
				$query = '';
			}
		}
	}
}
function bakupdata($tabledb,$step){
	global $start,$tableid,$count,$db,$sizelimit,$stop,$rows,$PW;
	$stop = 0;
	$bakupdata = '';
	for ($i=$tableid;$i<$count;$i++) {
		$table = ($step == 1 ? $PW : '').$tabledb[$i];
		$quetable = "`$table`";
		$rt = $db->get_one("SHOW TABLE STATUS LIKE '$table'");
		$rows = $rt['Rows'];
		$query = $db->query("SELECT * FROM $quetable LIMIT $start,100000");
		$num_F = mysql_num_fields($query);
		while ($data = mysql_fetch_row($query)) {
			$start++;
			$bakupdata .= "INSERT INTO $table VALUES ('".mysql_escape_string($data[0])."'";
			$datadb = '';
			for ($j=1;$j<$num_F;$j++) {
				$bakupdata .= ",'".mysql_escape_string($data[$j])."'";
			}
			$bakupdata .= ");\n";
			if ($sizelimit && strlen($bakupdata) > $sizelimit*1000) {
				$stop = 1;
				break;
			}
		}
		$db->free_result($query);
		$bakupdata .= "\n";
		if ($stop==1) {
			if ($start>=$rows) {
				$i++;
				$rows = $start = 0;
			}
			$tableid = $i+1;
			break;
		}
		$start = 0;
	}
	return $bakupdata;
}
function bakuptable($tabledb,$step){
	global $db,$PW;
	$return = '';
	foreach ($tabledb as $table) {
		$table = ($step == 1 ? $PW : '').$table;
		$quetable = "`$table`";
		$return .= "DROP TABLE IF EXISTS $table;\n";
		$rt = $db->get_one("SHOW CREATE TABLE $quetable",0);
		$rt[1] = str_replace($rt[0],$table,$rt[1]);
		$return .= $rt[1].";\n\n";
	}
	return $return;
}
function randstr($lenth){
	mt_srand((double)microtime() * 1000000);
	$randval = '';
	for ($i=0;$i<$lenth;$i++) {
		$randval .= mt_rand(0,9);
	}
	$randval = substr(md5($randval),mt_rand(0,32-$lenth),$lenth);
	return $randval;
}
include PrintEot('bakdata');footer();
?>