<?php
!function_exists('usermsg') && exit('Forbidden');
include_once(R_P.'mod/upload_mod.php');

if($do=="upload"){
	!$db_allowupload && jsmsg('upload_close');
	!$_GROUP['upload'] && jsmsg('upload_group_right');

	if($mode=='0'){
		list($attachment_name,$attachment_size,$attachment) = uploadcheck($_FILES['attachment']);
		if(!$attachment){
			jsmsg('upload_error');
		}

		$check = uploadright($attachment_size);
		if($check=='size_error'){
			jsmsg('upload_size_error');
		}elseif($check=='size_limit'){
			jsmsg('upload_size_limit');
		}

		list($attach_ext,$type) = uploadfileext($attachment_name,$db_uploadfiletype);
		if(!$attach_ext){
			jsmsg('upload_type_error');
		}

		$fileuplodeurl = uploadsavepath($attach_ext);
		$source = R_P.$attachpath.'/'.$fileuplodeurl;

		if(!uploadfile($attachment,$source)){
			jsmsg('upload_error');
		}

		if(!savecheck($source,$attachment_name)){
			jsmsg('upload_content_error');
		}

		$size = ceil(filesize($source)/1024);
		$descrip = Char_cv($descrip);

		if($type=='img' && $db_thumbifopen){
			include_once(R_P.'mod/makethumb.php');
			$filname="$attachpath/$fileuplodeurl";
			$attthumburl="$attachpath/$attthumburl";
			list($db_thumbw,$db_thumbh)=explode("\t",$db_thumbwh);
			MakeThumb($filname,$attthumburl,$db_thumbw,$db_thumbh,$db_thumbcolor) && $ifthumb=1;
		}

		$db->update("INSERT INTO pw_upload SET cid='0',itemid='0',uid='$admin_uid',name='$attachment_name',type='$type',size='$size',attachurl='$fileuplodeurl',uploadtime='$timestamp',descrip='$descrip',atype='',state='0',ifthumb='$ifthumb'");
		$aid=$db->insert_id();

		echo "<script language=\"JavaScript1.2\">parent.uploadfile_response('$aid','$size','$descrip','$fileuplodeurl','$attachment_name');</script>";exit;
	}
}elseif($do=="del"){
	$db->update("DELETE FROM pw_upload WHERE aid='$aid' AND uid='$admin_uid'");
	echo "<script language=\"JavaScript1.2\">parent.delupload_response('$aid');</script>";exit;
}elseif($do=="editdescrip"){
	$newdescrip = Char_cv($newdescrip);

	$db->update("UPDATE pw_upload SET descrip='$newdescrip' WHERE aid='$aid' AND uid='$admin_uid'");
	echo "<script language=\"JavaScript1.2\">parent.editdescrip_response('$aid');</script>";exit;
}
?>