<?php
!function_exists('N_strireplace') && exit('Forbidden');
($uid != $winduid) && $windgroup['allowread']=='0' && showmsg('read_limit');
$articleurl = $do != 'showone' ? 'article.php?' : 'blog.php?do=showone&';
$dirid = $blogdb['dirid'];
$blogdb['icon'] = explode(',',$blogdb['icon']);
$blogdb['transfer'] = explode(',',$blogdb['transfer']);
$blogdb['postdate'] = get_date($blogdb['postdate'],'Y-m-d');
$blogdb['ifsign']<2 && $blogdb['content'] = nl2br($blogdb['content']);

InitGP(array('goto'),'G');
if(!empty($goto)){
	$postdate = $db->get_value("SELECT postdate FROM pw_items WHERE itemid=$itemid");
	if($do == 'showone'){
		$url = "{$articleurl}uid=$uid&type=$type&cid=$cid";
		if($uid == $winduid){
			$ifhide = '';
		}elseif(!empty($winduid) && strpos(",$blogdb[friends],",",$winduid,")!==false){
			$ifhide = " AND ifhide!='1'";
		}else{
			$ifhide = " AND ifhide='0'";
		}
		$sqladd = "AND uid='$uid' AND type='$type' AND ifcheck='1'$ifhide ORDER BY postdate";
	}else{
		$url = "{$articleurl}type=$type&cid=$cid";
		$sqladd = "AND type='$type' AND cid=$cid AND ifcheck='1' AND ifhide='0' ORDER BY postdate";
	}
	if($goto == 'previous'){
		$previous = $db->get_one("SELECT itemid FROM pw_items WHERE postdate>$postdate $sqladd ASC LIMIT 1 ");
		if($previous){
			ObHeader("$url&itemid=$previous[itemid]");
		} else{
			ObHeader("$url&itemid=$itemid");
		}
	}elseif($goto == 'next'){
		$next = $db->get_one("SELECT itemid FROM pw_items WHERE postdate<$postdate $sqladd DESC LIMIT 1 ");
		if($next){
			ObHeader("$url&itemid=$next[itemid]");
		} else{
			ObHeader("$url&itemid=$itemid");
		}
	}else{
		Showmsg('undefined_action');
	}
}
$db->update("UPDATE pw_items SET hits=hits+1 WHERE itemid='$itemid'");
if (!$blogdb['ifwordsfb']) {
	$ifwordsfb = 0;
	$cktitle = $blogdb['subject'];
	$ckcontent = $blogdb['content'];
	foreach ($_FORBIDDB as $value) {
		$cktitle = N_strireplace($value['word'],$value['wordreplace'],$cktitle);
		$ckcontent = N_strireplace($value['word'],$value['wordreplace'],$ckcontent);
	}
	if ($cktitle != $blogdb['subject']) {
		$blogdb['subject'] = $cktitle;
		$ifwordsfb = 1;
	}
	if ($ckcontent != $blogdb['content']) {
		$blogdb['content'] = $ckcontent;
		$ifwordsfb = 1;
	}
	if ($ifwordsfb) {
		$db->update("UPDATE pw_items SET ifwordsfb='1',subject='".addslashes($cktitle)."' WHERE itemid='$itemid'");
		$db->update("UPDATE pw_$type SET content='".addslashes($ckcontent)."' WHERE itemid='$itemid'");
	}
}
$blogdb['ifconvert'] && $blogdb['content'] = convert($blogdb['content'],$db_post);
$blogdb['content'] = str_replace('[more]','',$blogdb['content']);
strpos($blogdb['content'],'[s:')!==false && $blogdb['content'] = showsmile($blogdb['content']);
$blogdb['uploads'] = $blogdb['uploads'] ? unserialize($blogdb['uploads']) : array();
$ispic = 0;
if ($do == 'showone') {
	if ($type == 'photo' || $type == 'goods') {
		$ispic = 1;
		if ($blogdb['absoluteurl']) {
			$blogdb['absoluteurl'] = unserialize($blogdb['absoluteurl']);
			foreach ($blogdb['absoluteurl'] as $v) {
				$blogdb['picurl'][] = $blogdb['attachurl'][] = $v;
				$blogdb['descrip'][] = $blogdb['subject'];
			}
			
		}
		$blogdb['absoluteurl'] = null;
	}
	$type == 'music' && $blogdb['musicurl'] = unserialize($blogdb['musicurl']);
	$type == 'file' && $blogdb['absoluteurl'] = unserialize($blogdb['absoluteurl']);
	if ($type == 'music') {
		foreach ($blogdb['musicurl'] as $key => $value) {
			if (eregi("\.(rm|rmvb|ra|ram)$",$value['url'])) {
				$blogdb['musicurl'][$key]['type'] = 'rm';
			} elseif (eregi("\.(qt|mov|4mv)$",$value['url'])) {
				$blogdb['musicurl'][$key]['type'] = 'qt';
			} else {
				$blogdb['musicurl'][$key]['type'] = 'wmv';
			}
		}
	}
}
$attachdb['pic'] = $attachdb['down'] = array();
foreach ($blogdb['uploads'] as $upload) {
	$upload = GetAttachUrl($upload);
	if ($upload['type']!='img') {
		$attachdb['down'][$upload['aid']] = array($upload['name'],$upload['size'],$upload['type'],$upload['desc']);
		$a_url = "<a href=\"job.php?action=download&itemid=$itemid&aid=$upload[aid]\" target=\"_blank\"><font color=\"red\">$upload[name]</font></a>";
	} else {
		$a_url = cvpic($upload['attachurl'],$db_post['picwidth'],$db_post['picheight'],1);
		$attachdb['pic'][$upload['aid']]  = array($a_url,$upload['desc']);
	}
	if ($ispic==1 && $upload['type']=='img') {
		$blogdb['picurl']['ul_'.$upload['aid']] = $upload['picurl'];
		$blogdb['descrip']['ul_'.$upload['aid']] = $upload['descrip'] ? $upload['descrip'] : $blogdb['subject'];
		$blogdb['attachurl']['ul_'.$upload['aid']] = $upload['attachurl'];
	}
	$attachmentdb[$upload['aid']] = ($upload['desc'] ? "<b>$upload[desc]</b><br />" : '')."$a_url";
}
$absoluteurl = unserialize($blogdb['absoluteurl']);
if(!empty($absoluteurl)){
foreach ($absoluteurl as $pic){
	$pic = cvpic($pic,$db_post['picwidth'],$db_post['picheight'],1);
	$attachdb['pic'][] = array($pic,''); 
	}
}

if ($ispic) {
	if (!empty($blogdb['picurl'])) {
		list($blogdb['style']) = explode('|',$blogdb['style']);
		foreach ($blogdb['picurl'] as $key => $value) {
			!$blogdb['attachurl'][$key] && $blogdb['attachurl'][$key] = "theme/$blogdb[style]/images/nopic.gif";
			$unsetid = str_replace('ul_','',$key);
			unset($attachdb['pic'][$unsetid]);
			unset($attachmentdb[$unsetid]);
			$blogdb['picurl'] = $value;
		}
	}
	!$blogdb['picurl'] && $blogdb['picurl'] = "theme/$blogdb[style]/images/nopic.gif";
	if ($type == 'goods') {
		include_once(D_P.'mod/pay_mod.php');
		if ($blogdb['pay99bill']) {
			$blogdb['pay99bill'] = MakeButton($blogdb['pay99bill'],$blogdb['subject'],$blogdb['price'],0,0,3);
		}
		if ($blogdb['paypal']) {
			$blogdb['paypal'] = MakeButton($blogdb['paypal'],$blogdb['subject'],$blogdb['price'],0,0,1);
		}
		if ($blogdb['alipay']) {
			$blogdb['alipay'] = $blogdb['feemode'] ? MakeButton($blogdb['alipay'],$blogdb['subject'],$blogdb['price'],0,0,2) : MakeButton($goods['alipay'],$blogdb['subject'],$blogdb['price'],$blogdb['maillfee'],$blogdb['expressfee'],2);
		}
	}
}
$aids = array();
$blogdb['content'] = attachment($blogdb['content'],$db_post['times']);
$db_copyctrl && $blogdb['content'] = preg_replace('/<br \/>/eis',"copyctrl()",$blogdb['content']);
$klink = $blogdb['klink'] ? unserialize($blogdb['klink']) : array();
$blogdb['content'] = keywordlink($blogdb['content'],$klink);
foreach($aids as $aid){
	if ($attachdb['pic'][$aid]) {
		unset($attachdb['pic'][$aid]);
	}
	if($attachdb['down'][$aid]){
		unset($attachdb['down'][$aid]);
	}
	unset($attachmentdb[$aid]);
}
$blogdb['footprints'] = (int)$blogdb['footprints'];
${'digest_'.(int)$blogdb['digest']} = 'CHECKED';
$blogdb['tagsdb'] = array();
if ($blogdb['tags']) {
	$taginfo = array_unique(explode(',',$blogdb['tags']));
	foreach ($taginfo as $key => $value) {
		if ($value) {
			$tagname = rawurlencode($value);
			$blogdb['tagsdb'][$key] = array('name' => $value,'tagname' => $tagname);
		} else {
			unset($blogdb['tagsdb'][$key]);
		}
	}
}
unset($blogdb['tags']);
if ($blogdb['replies']) {
	(int)$page<1 && $page = 1;
	if ((int)$blogdb['cshownum'] < 1) {
		$blogdb['cshownum'] = $db_perpage ? $db_perpage : 30;
	}
	$start = ($page-1)*$blogdb['cshownum'];
	$blogdb['cmttext'] = $blogdb['cmttext'] ? (array)unserialize($blogdb['cmttext']) : array();
	$cmttextnum = count($blogdb['cmttext']);
	if ($cmttextnum > $start) {
		$blogdb['cmttext'] = array_slice($blogdb['cmttext'],$start,$blogdb['cshownum']);
	} else {
		$blogdb['cmttext'] = array();
		$query = $db->query("SELECT c.id,c.author,c.authorid,c.postdate,c.ifwordsfb,c.ifconvert,c.content,c.replydate,c.reply,u.icon as picon FROM pw_comment c LEFT JOIN pw_user u ON c.authorid=u.uid WHERE c.ifcheck='1' AND c.itemid='$itemid' ORDER BY c.postdate DESC LIMIT $start,$blogdb[cshownum]");
		while ($rt = $db->fetch_array($query)) {
			//$rt['replydate'] = get_date($rt['replydate']);
			$blogdb['cmttext'][] = array('id' => $rt['id'],'author' => $rt['author'],'authorid' => $rt['authorid'],'picon' => $rt['picon'],'postdate' => $rt['postdate'],'ifwordsfb' => $rt['ifwordsfb'],'ifconvert' => $rt['ifconvert'],'content' => $rt['content'],'replydate' => $rt['replydate'],'reply' => $rt['reply']);
		}
		$db->free_result($query);
	}
	foreach ($blogdb['cmttext'] as $key => $value) {
		$blogdb['cmttext'][$key]['picon'] = showfacedesign($value['picon']);
		$blogdb['cmttext'][$key]['postdate'] = get_date($value['postdate']);
		$blogdb['cmttext'][$key]['replydate'] = get_date($value['replydate']);
		if (!$value['ifwordsfb']) {
			$ifwordsfb = 0;
			$ckcontent = $value['content'];
			foreach ($_FORBIDDB as $v) {
				$ckcontent = N_strireplace($v['word'],$v['wordreplace'],$ckcontent);
			}
			if ($ckcontent != $value['content']) {
				$value['content'] = $ckcontent;
				$ifwordsfb = 1;
			}
			$ifwordsfb && $db->update("UPDATE pw_comment SET ifwordsfb='1',content='".addslashes($ckcontent)."' WHERE id='$value[id]'");
		}
		$value['content'] = nl2br($value['content']);
		$blogdb['cmttext'][$key]['content'] = $value['ifconvert'] ? convert($value['content'],$db_post) : $value['content'];
		strpos($blogdb['cmttext'][$key]['content'],'[s:')!==false && $blogdb['cmttext'][$key]['content'] = showsmile($blogdb['cmttext'][$key]['content']);
	}
	if ($blogdb['replies'] > $blogdb['cshownum']) {
		require_once(R_P.'mod/page_mod.php');
		$pages = page($blogdb['replies'],$page,$blogdb['cshownum'],"{$articleurl}type=$type&itemid=$itemid&");
	}
}
$windgroup['closecmt'] && $blogdb['allowreply'] = 0;
$rawwindid = $ckurl = '';
if ($blogdb['allowreply']) {
	list(,,,,$cmtgd) = explode("\t",$db_gdcheck);
	if (!$cmtgd) {
		list($cmtgd) = explode(',',$blogdb['gdcheck']);
	}
	if ($cmtgd) {
		$rawwindid = (!$windid) ? 'guest' : rawurlencode($windid);
		$ckurl = str_replace('?','',$ckurl);
	}
	list(,,,,$cmtq) = explode("\t",$db_qcheck);
	list($blogdb['gbqcheck'],$blogdb['cmtqcheck']) = explode(',',$blogdb['qcheck']);
	if(!$cmtq){
		$ifcmtq = $blogdb['cmtqcheck'] ? '1' : '0';
	}else{
		$ifcmtq = '1';
	}
}
$blogdb['dirname'] = $dirdb[$blogdb['dirid']]['name'];

//处理登入用户的个人分类信息，在转载的时候选择分类用
if($winddb['dirdb'] && $db_transfer == '1'){
	$winddirdb = $winddb['dirdb'] ? unserialize($winddb['dirdb']) : array();
	$winddirdb = (array)$winddirdb['blog'];
	foreach ($winddirdb as $value) {
		$transfer_itemcache .= "<option id=\"dirop$value[typeid]\" value=\"$value[typeid]\">$value[name]</option>";
	}
}
if($catedb && $db_transfer == '1'){
	foreach ($catedb as $value) {
		$add = '';
		for ($i=0;$i<$value['type'];$i++) {
			$add .= '>';
		}
		$transfer_forumcache .= "<option value=\"$value[cid]\">$add $value[name]</option>";
	}
}


function GetAttachUrl($array){
	global $db_blogurl,$attach_url,$attachpath;
	if ($array['type']=='img') {
		$a_url = $attach_url ? $attach_url : "$db_blogurl/$attachpath";
		$array['picurl'] = "$a_url/$array[attachurl]";
		N_stripos($array['picurl'],'login') && $array['picurl'] = N_strireplace('login','log in',$array['picurl']);
		$array['attachurl'] = $array['picurl'];
		if ($array['ifthumb']) {
			$attach_ext = strrchr($array['attachurl'],'.');
			$array['attachurl'] = str_replace($attach_ext,"_thumb{$attach_ext}",$array['attachurl']);
		}
	}
	return $array;
}

function keywordlink($content,$klink){
	foreach($klink as $key => $value){
		$content = N_strireplace($value[0],'<a href="'.$value[1].'" title="'.$value[2].'" target="_blank">'.$value[0].'</a>',$content);
	}
	return $content;
}
?>