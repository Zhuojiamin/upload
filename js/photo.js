function del_photo(pid,aid){
	if(confirm('您确认要删除此图片吗？')){
		var url	  = ajaxurl + "?action=itemcp&job=del";
		var param = "pid=" + pid + "&aid=" + aid;
		send_request(url,del_photo_response,param);
	}
}
function del_photo_response(){
	var pid = http_request.responseText;
	if(pid == ''){
		return;
	}else{
		GE('photo_'+pid).style.display = 'none';
		alert('删除成功');
	}
}

function set_headpage(pid,aid){
		var url	  = ajaxurl + "?action=itemcp&job=headpage";
		var param = "pid=" + pid + "&aid=" + aid;
		send_request(url,set_headpage_response,param);
}

function set_headpage_response(){
	var msg = http_request.responseText;
	if(msg == ''){
		return;
	}else if(msg == 'done'){
		alert('已经是封面页，无须重复设置');
	}else{
		msg = msg.split("\t");
		GE('hpage').src = msg[2];
		alert('操作成功');
	}
}

function check_apassword(uid,aid){
	var pw = GE('album_password').value;
	var url	  = ajaxurl + "?action=album_password";
	var param = "pw=" + pw + "&aid=" + aid + "&uid=" + uid;
	send_request(url,check_apassword_response,param);
}

function check_apassword_response(){
	var msg = http_request.responseText;
	msg = msg.split("\t");
	
	if(msg[0] == 'not_login'){
		alert('您还没有登入，不能访问相册');
	}else if(msg[0] == 'illegal_aid'){
		alert('错误的相册ID');
	}else if(msg[0] == 'empty_pw'){
		alert('密码为空，请输入正确的密码');
	}else if(msg[0] == 'success'){
		alert('登入成功！');
		window.location = "blog.php?do=list&uid=" + msg[1] + "&type=photos&aid=" +msg[2];
	}else if(msg[0] == 'illegal_pwd'){
		alert('密码错误，请重新输入');
		GE('album_password').value = '';
	}
}