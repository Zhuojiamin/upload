function add_bmhits(itemid,type){
	var url = ajaxurl + '?action=bmhits';
	var param = 'itemid=' + ajax_convert(itemid) + '&type=' + ajax_convert(type);
	send_request(url,AddbmhitsResponse,param);
}

function AddbmhitsResponse(){
	var msg = http_request.responseText;
	msg = msg.split("\t");
	if (msg[0] == 'success'){
		if (msg[2] == '0'){
			GE('bmhits_' + msg[1]).innerHTML = parseInt(GE('bmhits_' + msg[1]).innerHTML)+1;
		}
	}
}