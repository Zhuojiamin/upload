var ajaxcmt = false;
var mt;
function AddFriendGroup(obj,id){
	open_menu(obj,id,2);
}

function AddFriendGroupSubmit(){
	var url	  = ajaxurl + "?action=addfriendgroupsubmit";
	var param = 'gname=' + GE('gname').value;
	send_request(url,AddFriendGroupSubmit_response,param);
}

function AddFriendGroupSubmit_response(){
	var msg = http_request.responseText;
	msg = msg.split("\t");
	if (msg[0] == 'not_login') {
		alert('您还未登入！');
	} else if (msg[0] == 'empty_gname') {
		alert('输入的组名不能为空！');
	} else if (msg[0] == 'success') {
		GE('nofriendgroup').style.display = 'none';
		var fgbody = GE('fgbody').innerHTML;
		if (!fgbody){
			fgbody = '<li>' + msg[1] + '</li>';
		} else {
			fgbody = fgbody + '<li>' + msg[1] + '</li>';
		}
		GE('fgbody').innerHTML=fgbody;
	} else {
		alert('添加失败！');
	}
	closem();
}

function EditBox(gid){
	GE('edit_' + gid).style.display='';
}

function CloseEditBoxm(gid){
	t = setTimeout("CloseEditBox("+gid+");",700);
}

function CloseEditBox(gid){
	GE('edit_' + gid).style.display='none';
}

function EditFriendGroup(gid,id,gname){
	var editbox_content = '<table style="width:200px;margin:5px;border:1px solid #eee;"><tr><td><span class="fl"><h2>编辑组名称</h2></span><span class="fr" style="cursor:pointer;" onclick="closep();" title="close"><img src="' + imgpath + '/wysiwyg/close.gif" /></span></td></tr><tr><td><input type="text" name="gname" id="gname" value="'+ gname +'"></td></tr><tr><td><input class="bt" type="button" name="save" value="确 定" onclick="EditFriendGroupSubmit('+ gid +');">  <input class="bt" type="button" name="cancel" value="取 消" onclick="closep();"></td></tr></table>';
	GE('edit_friend_box').innerHTML = editbox_content;
	open_menu('edit_friend_box',id,2);
}

function EditFriendGroupSubmit(gid){
	var url	  = ajaxurl + "?action=editfriendgroupsubmit";
	var param = 'gname=' + GE('gname').value + '&gid=' + gid;
	send_request(url,EditFriendGroupSubmit_response,param);
}

function EditFriendGroupSubmit_response(){
	var msg = http_request.responseText;
	msg = msg.split("\t");
	if (msg[0] == 'not_login') {
		alert('您还未登入！');
	} else if (msg[0] == 'empty_gname') {
		alert('输入的组名不能为空！');
	} else if (msg[0] == 'illegal_gid') {
		alert('错误的好友组ID！');
	} else if (msg[0] == 'success') {
		GE('gname_'+msg[1]).innerHTML = msg[2];
	} else {
		alert('修改失败！');
	}
	closem();
}

function DelFriendGroup(gid){
	if(confirm('您确认要删除改分组吗?删除分组后，原先分组中的好友经不再归属于任何分组！')){
		var url	  = ajaxurl + "?action=delfriendgroupsubmit";
		var param = 'gid=' + gid;
		send_request(url,DelFriendGroup_response,param);
	}else{
		return false;
	}
}

function DelFriendGroup_response(){
	var msg = http_request.responseText;
	msg = msg.split("\t");
	if (msg[0] == 'not_login') {
		alert('您还未登入！');
	} else if (msg[0] == 'illegal_gid') {
		alert('错误的好友组ID！');
	} else if (msg[0] == 'success') {
		GE('gid_'+msg[1]).innerHTML = '';
	} else {
		alert('删除失败！');
	}
}