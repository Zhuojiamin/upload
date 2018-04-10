var agt = navigator.userAgent.toLowerCase();
var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));

function equate_msg(type){
	if(confirm('您确定要同步论坛短消息，并且删除论坛中的短消息，请按＂确定＂，不删除论坛短消息请按＂取消＂！')){
		var url	  = ajaxurl + "?action=equatemsg";
		var param = "type=" + type + "&state=" + 1;
		send_request(url,equate_msg_response,param);
	} else {
		var url	  = ajaxurl + "?action=equatemsg";
		var param = "type=" + type + "&state=" + 0;
		send_request(url,equate_msg_response,param);
	}
}

function equate_msg_response(){
	var msg = http_request.responseText;
	if(msg == 'sebox' || msg == 'rebox'){
		alert('同步短消息成功！');
		if(msg == 'rebox'){
			window.location = "user_index.php?action=message&";
		}else if(msg == 'sebox'){
			window.location = "user_index.php?action=message&type=sendbox";
		}
	}else{
		alert('同步短消息失败！');
	}
}