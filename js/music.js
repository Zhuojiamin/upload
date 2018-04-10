function del_malbums(cid,maid){
	if(confirm('删除专辑将删除该专辑下的所有音乐，您确认要执行此操作吗？')){
		var url	  = ajaxurl + "?action=itemcp&job=delmalbums";
		var param = "maid=" + maid + "&cid=" + cid;
		send_request(url,del_music_response,param);
	}
}

function del_music_response(){
	var maid = http_request.responseText;
	if(maid == ''){
		return;
	}else{
		alert('删除成功');
		window.location = 'user_index.php?action=itemcp&type=music';
	}
}
function add_mhits(maid,mid){
	var url = ajaxurl + '?action=mhits';
	var param = 'maid=' + ajax_convert(maid) + '&mid=' + ajax_convert(mid);
	send_request(url,AddmhitsResponse,param);
}

function AddmhitsResponse(){
	var msg = http_request.responseText;
	msg = msg.split("\t");
	if (msg[0] == 'success'){
		GE('mhits_' + msg[1]).innerHTML = parseInt(GE('mhits_' + msg[1]).innerHTML)+1;
	}
}

function setbgmusic(mid){
	if (!winduid) {
		alert('您还没有登陆或注册，暂时不能使用此功能！');
		return false;
	}
	var url = ajaxurl + '?action=setbgmusic';
	var param = 'mid=' + ajax_convert(mid);
	send_request(url,SetbgmusicResponse,param);
}

function SetbgmusicResponse(){
	var msg = http_request.responseText;
	if(msg == 'success'){
		alert('设置成功！');
	}
}