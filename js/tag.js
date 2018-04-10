function addtag(tagname){
	if (tagcheck(tagname)){
		var url	  = user_file + "?action=tag&job="+ job +"&step=add&type="+ itemtype +"&itemid=" + itemid;
		var param = "tagname=" + ajax_convert(tagname);
		send_request(url,addtag_response,param);
	}
}

function addtag_response(){
	tagname=http_request.responseText;
	if (tagname==3) {
		alert("错误的itemid");
	} else {
		if (GE("showtagdisplay").style.display=='none') {
			GE("showtagdisplay").style.display='';
		}
		GE("blogtags").innerHTML = GE("blogtags").innerHTML + '<a href="javascript:" title="点击删除此TAG" onclick="deltag(this)" style="padding-right:6px;">'+tagname+'<input name="tags[]" type=hidden value="' + tagname + '"></a>';
		GE('newtg').value='';
	}
}

function deltag(obj){
	if(confirm('确定删除TAG')) {
		if(job=='add'){
			deltag_response(obj)
		}else if(job=='modify'){
			var tagname=obj.innerText;
			var url	= user_file + "?action=tag&job="+job+"&step=del&type="+itemtype+"&itemid=" + itemid;
			var param = "tagname=" + ajax_convert(tagname);
			send_request(url,deltag_response(obj),param);
		}
	}
}

function deltag_response(obj){
	obj.innerHTML="";
	obj.style.display = "none";
}

function tagcheck(str){
	str = str.trim();
	var len=str.length;

	if(len<2 || len>10){
		alert("TAG长度错误");
		return false;
	}

	var chars="`~!@#$%^&*()_-+=|\\{}[]:\";',./<>?";
	for (var i = 0; i <len ; i++){
		var tmp = chars.indexOf(str.charAt(i));
		if(tmp>=0){
			alert("TAG含有非法字符 ‘" + str.charAt(i) + '’');
			return false;
		}

	}
	return true;
}