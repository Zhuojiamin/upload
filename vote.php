<?php
require_once("global.php");
!$winduid && Showmsg('not_login');

$vid = (int)$_GET['vid'];
$voteuser=array();

$votedb=$db->get_one("SELECT * FROM pw_blogvote WHERE id='$vid'");
empty($votedb) && Showmsg('empty_vote');
$query=$db->query("SELECT id,item,num,voteduid FROM pw_voteitem WHERE vid='$vid'");
while($rt=$db->fetch_array($query)){
	$votedb['items'][$rt[id]]=$rt;
	if($groupid==3 && $_GET['step']=='viewvoter'){
		if($rt[voteduid]){
			$query2=$db->query("SELECT username,uid FROM pw_user WHERE uid IN($rt[voteduid])");
			while($rt2=$db->fetch_array($query2)){
				$voteuser[$rt[id]][]=$rt2;
			}
		}
	}
	if(strpos(",$rt[voteduid],",",$winduid,")!==false){
		$havevote=true;
	}
	$rt['num']>$maxvote && $maxvote=$rt['num'];
	$sumvote+=$rt['num'];
}
if(empty($votedb['_ifview']) && $groupid!=3){
	!$havevote && Showmsg('not_votevie');
}
require_once(R_P.'mod/header_inc.php');
require_once PrintEot('vote');footer();
?>