<?php
require_once('global.php');
$lxheader = 'search';
$_GROUP['allowsearch'] == 0 && Showmsg('group_right');
list($db_opensch,$db_schstart,$db_schend) = explode("\t",$db_opensch);
if ($db_opensch==1 && $groupid != 3 && $groupid != 4) {
	if ($db_schstart < $db_schend && ($t['hours'] < $db_schstart || $t['hours'] >= $db_schend)) {
		Showmsg('search_opensch');
	} elseif ($db_schstart > $db_schend && ($t['hours'] < $db_schstart && $t['hours'] >= $db_schend)) {
		Showmsg('search_opensch');
	}
}
require_once(R_P.'mod/header_inc.php');
$classdb = array('blog','photo','music','bookmark','goods','file');
if (!$action) {
	$catedb = array();
	foreach ($classdb as $value) {
		if (strpos("\t$db_showcate\t","\t$value\t")!==false) {
			@include Pcv(D_P."data/cache/forum_cache_$value.php");
			$catedb[] = array('value' => $value,'name' => $ilang['c'.$value]);
		}
	}
	require_once PrintEot('search');footer();
} elseif ($action == 'dosearch') {
	@set_time_limit(0);
	$sid = (int)GetGP('sid','G');
	$keyword_A = $schdb = array();
	$schedid = $orderby = $skeyword = '';
	if ($sid > 0) {
		$keyword = $kw_area = $author = $at_area = $types = $ciddb = $postdate = $orderby = $sc = $schuid = '';
		@extract($db->get_one("SELECT sorderby,total,skeyword,schedid FROM pw_schindex WHERE sid='$sid'"));
		list($orderby,$sc) = explode('|',$sorderby);
	} else {
		InitGP(array('schuid','keyword','kw_area','author','at_area','types','ciddb','postdate','orderby','sc'),'P');
		$keyword && strlen(trim($keyword)) < 3  && Showmsg('search_word_limit');
		!$postdate && !$keyword && !$author && Showmsg('search_empty');
		!$types && $types = 'blog';
		!in_array($types,$classdb) && Showmsg('undefined_action');
		
		$cidwhere = '';
		foreach ((array)$ciddb as $value) {
			if ($value) {
				if ($value == 'all') {
					$cidwhere = '';
					break;
				}
				$cidwhere .= ($cidwhere ? ',' : '')."'$value'";
			}
		}
		if (!$cidwhere) {
			$sqlwhere = " AND t.type='$types'";
		} else {
			$sqlwhere = " AND t.cid".(strpos($cidwhere,',')!==false ? " IN ($cidwhere)" : " = $cidwhere");
		}
		unset($ciddb,$classdb,$cidwhere,$typewhere);
		$kw_area = (int)$kw_area;
		$at_area = (int)$at_area;
		($orderby != 'replies' && $orderby != 'hits' && $orderby != 'author' && $orderby != 'cid' && $orderby != 'lastpost') && $orderby = 'postdate';
		$schline = md5($schuid."\t".$keyword."\t".$kw_area."\t".$author."\t".$at_area."\t".$sqlwhere."\t".$postdate."\t".$orderby."\t".$sc);
		$sorderby = $orderby.'|'.$sc;
		@extract($db->get_one("SELECT sid,total,skeyword,schedid FROM pw_schindex WHERE schline='$schline' LIMIT 1"));
		
		if (!$schedid) {
			$db->update("DELETE FROM pw_schindex WHERE schtime<$timestamp-3600");
			if (($keyword || $author || !is_numeric($schuid)) && $_GROUP['searchtime']) {
				if ($timestamp - $winddb['lastsearch'] < $_GROUP['searchtime']) {
					$gp_searchtime = $_GROUP['searchtime'];
					Showmsg('search_limit');
				}
				$db->update("UPDATE pw_user SET lastsearch='$timestamp' WHERE uid='$winduid'");
			}
			$sqltable = 'pw_items t';
			$uids = '';
			if ($schuid) {
				if (!is_numeric($schuid)) {
					$errorname = $ilang['lgusername'];
					$errorvalue = '';
					Showmsg('user_not_exists');
				}
				$uids = " AND t.uid = '$schuid'";
			}
			if ($keyword) {
				$keywhere = '';
				$keyword = str_replace(array('%','_'),array('\%','\_'),addslashes(trim($keyword)));
				$skeyword = $keyword;
				$keyword_A = explode('|',$keyword);
				foreach ($keyword_A as $value) {
					if ($value) {
						if ($kw_area == '0') {
							$keywhere .= ($keywhere ? ' OR ' : '')."t.subject LIKE '%$value%'";
						} elseif ($kw_area == '1' && $_GROUP['allowsearch'] == 2) {
							$sqltable = "pw_items t LEFT JOIN pw_$types tm ON t.itemid=tm.itemid";
							$keywhere .= ($keywhere ? ' OR ' : '')."(t.subject LIKE '%$value%' OR tm.content LIKE '%$value%') ";
						}
					}
				}
				if ($keywhere) {
					$keywhere && $sqlwhere .= " AND ($keywhere)";
				} else{
					Showmsg('search_keyword');
				}
			} elseif ($author) {
				!str_replace('*','',$author) && Showmsg('search_keyword');
				$uidwhere = $uids = $distinct = '';
				$author = str_replace(array('%','_'),array('\%','\_'),addslashes(trim($author)));
				$author = str_replace('*','_',$author);
				$attable = 'pw_user';
				$atwhere = 'username';
				if ($at_area > 0) {
					$attable = 'pw_comment';
					$atwhere = 'author';
					$distinct = ' DISTINCT';
				}
				
				$query = $db->query("SELECT$distinct uid FROM $attable WHERE $atwhere LIKE '%$author%'");
				while ($rt = $db->fetch_array($query,'MYSQL_NUM')) {
					$uidwhere .= ($uidwhere ? ',' : '')."'$rt[0]'";
				}
				if ($uidwhere) {
					$sqlwhere .= ' AND t.uid '.(strpos($uidwhere,',')!==false ? "IN ($uidwhere)" : "= $uidwhere");
				} else {
					$errorname = $ilang['lgusername'];
					$errorvalue = $author;
					Showmsg('user_not_exists');
				}
			}
			$uids && $sqlwhere .= $uids;
			$postdate && (int)$postdate < '31536001' && $sqlwhere .= " AND t.postdate > $timestamp-$postdate";
			$total = 0;
			$limit = 'LIMIT '.(!$db_maxresult ? '500' : $db_maxresult);
			$query = $db->query("SELECT t.itemid FROM $sqltable WHERE t.ifcheck='1'$sqlwhere $limit");
			while ($rt = $db->fetch_array($query)) {
				$total++;
				$schedid .= ($schedid ? ',' : '')."$rt[itemid]";
			}
			if ($schedid) {
				$skeywords = $skeyword ? "'$skeyword'" : 'NULL';
				$db->update("INSERT INTO pw_schindex(sorderby,schline,schtime,total,skeyword,schedid) VALUES ('$sorderby','$schline','$timestamp','$total',$skeywords,'$schedid')");
				$sid = $db->insert_id();
			}
		}
	}
	if ($schedid) {
		$scheddb = array();
		(int)$page<1 && $page = 1;
		!$db_perpage && $db_perpage = 30;
		$orderby = "ORDER BY $orderby ".($sc ? 'DESC' : 'ASC');
		$limit = 'LIMIT '.($page-1)*$db_perpage.",$db_perpage";
		$sqlwhere = 'itemid';
		if (strpos($schedid,',')!==false) {
			$sqlwhere .= " IN ('".str_replace(',',"','",$schedid)."')";
		} else {
			$sqlwhere .= " = '$schedid'";
		}
		$keyword_A = $skeyword ? explode('|',$skeyword) : array();
		$query = $db->query("SELECT itemid,uid,type,author,subject,postdate FROM pw_items WHERE $sqlwhere $orderby $limit");
		while ($rt = $db->fetch_array($query)) {
			foreach ($keyword_A as $value) {
				$value && $rt['subject'] = preg_replace('/(?<=[^\w=]|^)('.preg_quote($value,'/').')(?=[^\w=]|$)/si','<font color="red"><u>\\1</u></font>',$rt['subject']);
			}
			$rt['postdate'] = get_date($rt['postdate'],'Y-m-d');
			$scheddb[] = $rt;
		}
		$db->free_result($query);
		if ($total > $db_perpage) {
			require_once(R_P.'mod/page_mod.php');
			$pages = page($total,$page,$db_perpage,"search.php?sid=$sid&");
		}
	} else {
		Showmsg('search_none');
	}
	require_once PrintEot('search');footer();
} else {
	Showmsg('undefined_action');
}
?>