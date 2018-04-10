<?php
!function_exists('readover') && exit('Forbidden');
function page($count,$page,$perpage,$url,$mao=''){
	global $tablecolor;
	$commer = '';
	if (strpos($url,'&')!==false) {
		substr($url,-1)!='&' && $commer = '&';
	} else {
		substr($url,-1)!='?' && $commer = '?';
	}
	substr($url,-1) != $commer && $url .= $commer;
	$numofpage = ceil($count/$perpage);
	if ($page>$numofpage) {
		if ($numofpage==1 && $page>1) {
			ObHeader($url);
			return '';
		}
		$page = $numofpage;
	}
	$total = $numofpage;
//	$max && $numofpage > $max && $numofpage=$max;
	if($numofpage <= 1 || !is_numeric($page)){
		return '';
	}else{
		$pages="<div class=\"pages\"><a href=\"{$url}page=1$mao\" style=\"font-weight:bold\">&laquo;</a>";
		$flag=0;
		for($i=$page-3;$i<=$page-1;$i++){
			if($i<1) continue;
			$pages.="<a href=\"{$url}page=$i$mao\">$i</a>";
		}
		$pages.="<b> $page </b>";
		if($page<$numofpage){
			for($i=$page+1;$i<=$numofpage;$i++){
				$pages.="<a href=\"{$url}page=$i$mao\">$i</a>";
				$flag++;
				if($flag==4) break;
			}
		}
		$pages.="<input type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){ location='{$url}page='+this.value;return false;}\"><a href=\"{$url}page=$numofpage$mao\" style=\"font-weight:bold\">&raquo;</a> Pages: ( $page/$total total )</div>";
		return $pages;
	}
}
/*function page($count,$page,$per,$url,$T=1){
	global $tablecolor,$page;

	$pre=3;
	$next=4;

	$sum=ceil($count/$per);
	$page > $sum && $page=$sum;
	(!is_numeric($page) || $page <1) && $page=1;

	if ($sum <= 1){
		return;
	}else{
		$ret="<a href='$url&page=1'>&laquo;</a>";
		$flag=0;
		for($i=$page-$pre;$i <= min($sum,$page+$next);$i++){
			if($i<1) continue;
			$ret.=$i==$page ? "<b>$page</b>" : "<a href='$url&page=$i'>&nbsp;$i&nbsp;</a>";
		}
		$ret.="<input align='absbottom' type='text' size='2' onkeydown=\"javascript: if(window.event.keyCode==13) window.location='{$url}&page='+this.value;\"><a href='$url&page=$sum'>&raquo;</a> Pages:($page/$sum total)";
		return $ret;
	}
}*/
?>