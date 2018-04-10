
function GetImgSize(img,MaxWidth,MaxHeight){ 
//	if(img.readyState!="complete"){//没有加载完毕
//		img.width=MaxWidth; 
//		img.height=MaxHeight;
//	}else{
		if(img.offsetWidth<=MaxWidth && img.offsetHeight<=MaxHeight){
			 return;
		}else{
			 if(MaxHeight*img.offsetWidth/img.offsetHeight>=MaxWidth){
				img.width=MaxWidth; 
				img.height=MaxWidth*img.offsetHeight/img.offsetWidth;
			 }else{
				img.height=MaxHeight;
				img.width=MaxHeight*img.offsetWidth/img.offsetHeight;
			  }
		 }
//	}
}
function MediaPlay(Type,Obj,strURL,intWidth,intHeight){
	if(Obj.innerHTML!='单击播放媒体文件'){
	    Obj.innerHTML = '单击播放媒体文件';
	}else{
		switch(Type){
			case "swf":
				tmpstr='关闭媒体播放<object codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+intWidth+'" height="'+intHeight+'"><param name="movie" value="'+strURL+'" /><param name="quality" value="high" /><param name="AllowScriptAccess" value="never" /><embed src="'+strURL+'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'+intWidth+'" height="'+intHeight+'" /></object><br />[<a target=_blank href='+strURL+'>Full Screen</a>]';
				break;
			case "wmv":
				tmpstr='关闭媒体播放<object classid="clsid:22D6F312-B0F6-11D0-94AB-0080C74C7E95" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,0,02,902" type="application/x-oleobject" standby="Loading..." width="'+intWidth+'" height="'+intHeight+'"><param name="FileName" VALUE="'+strURL+'" /><param name="ShowStatusBar" value="-1" /><param name="AutoStart" value="true" /><embed type="application/x-mplayer2" pluginspage="http://www.microsoft.com/Windows/MediaPlayer/" src="'+strURL+'" autostart="true" width="'+intWidth+'" height="'+intHeight+'" /></object>';
				break;
			case "rm":
				tmpstr='关闭媒体播放<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'+intWidth+'" height="'+intHeight+'"><param name="SRC" value="'+strURL+'" /><param name="CONTROLS" VALUE="ImageWindow" /><param name="CONSOLE" value="one" /><param name="AUTOSTART" value="true" /><embed src="'+strURL+'" nojava="true" controls="ImageWindow" console="one" width="'+intWidth+'" height="'+intHeight+'" /></object>'+
                '<br/><object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'+intWidth+'" height="32" /><param name="CONTROLS" value="StatusBar" /><param name="AUTOSTART" value="true" /><param name="CONSOLE" value="one" /><embed src="'+strURL+'" nojava="true" controls="StatusBar" console="one" width="'+intWidth+'" height="24" /></object>'+'<br /><object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'+intWidth+'" height="32" /><param name="CONTROLS" value="ControlPanel" /><param name="AUTOSTART" value="true" /><param name="CONSOLE" value="one" /><embed src="'+strURL+'" nojava="true" controls="ControlPanel" console="one" width="'+intWidth+'" height="24" autostart="true" loop="false" /></object>';
				break;
			case "qt":
				tmpstr='关闭媒体播放<embed src="'+strURL+'" autoplay="true" loop="false" controller="true" playeveryframe="false" cache="false" scale="TOFIT" bgcolor="#000000" kioskmode="false" targetcache="false" pluginspage="http://www.apple.com/quicktime/" />';
		}
		Obj.innerHTML = tmpstr;
	}
}	
function CopyCode(obj){
	var js = document.body.createTextRange();
	js.moveToElementText(obj);
	js.select(); 
	js.execCommand("Copy");
}
function delblog(id){
	if(confirm('您确认要删除此篇文章？')){
		var url=ajxurl+"/blogcp.php?action=delblog&selid="+id;
		itemid=id;
		send_request(url,del_blog);
	}
}
function del_blog(){
	if(http_request.responseText==2){
		GE(itemid).style.display='none';
	}else{
		alert(http_request.responseText);
	}
}
function delconfirm(){
	if(confirm('确定删除？')) {	
		return true;
	}else{
		return false;
	}
}
function addtoclt(id,type){
	if(id){
		var url=ajxurl+"/ajax.php?action=addtoclt&itemid="+id+"&type="+type;
		send_request(url,addto_clt);
	}
}
function addto_clt(){
	var msg='';
	switch(http_request.responseText){
		case '2':msg="请先登录";
			break;
		case '1':msg="添加成功";
			break;
		case '4':msg="您已经收藏了此内容，请不要重复添加";
			break;
		case '3':msg="类型错误或文章不存在！";
			break;
	}
		alert(msg);
}
function getcate(type){
	if(type){
		var url=ajxurl + "/ajax.php?action=getcate&type="+type;
		send_request(url,get_cate);
	}else{
		GE("scid").innerHTML="";
	}
}
function get_cate(){
	GE("scid").innerHTML=http_request.responseText;
}
function advck(id){
	if(id){
		var url=ajxurl + "/adv.php?id="+id;
		send_request(url);
	}
}

function commentcheck()
{
	if(document.form1.content.value == "")
	{
		alert("评论内容为空!");
		document.form1.content.focus();
		return false;
	}
}
function replaceimg(url){
	GE('img_container').src=url;
}
function commendtomenu(){
	var url=ajxurl + "/commendto.php?action=commendtomenu";
	send_request(url,commendto_menu);
}
function commendto_menu(){
	if(http_request.responseText==2){
		alert('请先登陆');
	}else{
		GE('commendtomenu').innerHTML=http_request.responseText+'<input type="button" value="推 荐" class="btn" onclick="commendto();" />';
	}
}
function commendto(){
	var touid=GE('fuid').value;
	var url=ajxurl + "/commendto.php?action=commendto&itemid="+itemid+"&touid="+touid;
	send_request(url,commend_to);
}
function commend_to(){
	if(http_request.responseText==2){
		alert('请先登陆');
	}else if(http_request.responseText==3){
		alert('没有此位好友');
	}else if(http_request.responseText==4){
		alert('错误的文章id');
	}else{
		GE('commendtomenu').innerHTML='<input type="button" value="推荐好友" onclick="commendtomenu();" class="btn" />';
		alert('推荐成功');
	}
}
function postcomment(){
	var guestname	= GE("guestname").value;
	var content 	= GE("content").value;
	var ifcode  	= GE("ifcode").value;
	if (itemid < 1) {
		alert('非法操作！');
		return false;
	}
	if (guestname == '') {
		alert('回复用户名不能为空！');
		return false;
	}
	if (content == '') {
		alert('回复内容不能为空！');
		return false;
	}
	var addurl = '';
	if (ifcode) {
		addurl = "&gdcode=" + GE("gdcode").value;
	}
	var url	  = ajxurl + "/cmtajax.php?action=new";
	var param = "itemid=" + itemid + "&guestname=" + ajax_convert(guestname) + "&content=" + ajax_convert(content) + addurl;
	send_request(url,creply_response,param);
}
function creply_response(){
	if (GE("ifcode").value != '') {
		GE("ck").src = 'ck.php?windid=' + GE("ifcode").value;
	}
	if (http_request.responseText == 'gdfalse') {
		alert('验证码错误，请重新输入');
	} else {
		if (type == 'blog') {
			GE("sum").innerHTML = parseInt(GE("sum").innerHTML)+1;
		}
		GE("content").value = '';
		if (actdo == 'showone') {
			ifdelete = allow == '1' ? "<div class=\"fr\"><a style=\"cursor:pointer;\" onclick=\"deletecomment(" + nextid + ");\">删除</a></div>" : '';
			GE("cdisplay").style.display = '';
			GE("ajaxreply").innerHTML = 
		  "<a name=\"c" + nextid + "\"></a> <dl> <dd class=\"comment-pic left\"><img src=\""+ nexticon +"\" width=\"40\" /></dd> <dd>" + ifdelete + "<a href=\"" + nexturl + "\" class=\"big b\">" + GE("guestname").value + "</a> <span class=\"gray\">" + nextdate +"</span> Says:</dd> <dd class=\"comment-content\"><div>" + http_request.responseText + "</div></dd> </dl>" + GE("ajaxreply").innerHTML;
			GE("sums").innerHTML = parseInt(GE("sums").innerHTML)+1;
		} else if (actdo == 'index') {
			var ifdelete;
			if (allow == '1') {
				ifdelete = "<a class=\"more fr\" style=\"cursor:pointer;\" onclick=\"deletecomment(" + nextid + ");\">删除</a>";
			}
			GE("ajaxreply").innerHTML = 
		  "<div class=\"c\"></div> <div class=\"reply-a\" id=\"" + nextid + "\"> <div class=\"reply-b\"> <div class=\"reply-c\"> <a href=\"" + nexturl + "\" class=\"b blue\">" + GE("guestname").value + "</a> (" + nextdate + ")回复： &nbsp;" + ifdelete + "</div> <div>" + http_request.responseText + "</div> </div> </div>" + GE("ajaxreply").innerHTML;
		}
		nextid = parseInt(nextid) + 1;
		scroll(0,0);
	}
}
function deletecomment(id){
	if (confirm('您确认要删除此篇回复？')) {
		var url = ajxurl + "/cmtajax.php?action=del";
		var param = "id=" + id;
		itemid=id;
		send_request(url,delete_response,param);
	}
}
function delete_response(){
	if (http_request.responseText=='1') {
		alert("此回复不存在");
	} else if (http_request.responseText=='2') {
		alert("你没有删除的权限");
	} else if (http_request.responseText=='ok') {
		GE(itemid).style.display = 'none';
		if (actdo == 'showone') {
			if (type == 'blog') {
				GE("sum").innerHTML = parseInt(GE("sum").innerHTML)-1;
				if (parseInt(GE("sum").innerHTML)<0) {
					GE("sum").innerHTML = 0;
				}
			}
			GE("sums").innerHTML = parseInt(GE("sums").innerHTML)-1;
			if (parseInt(GE("sums").innerHTML)<0) {
				GE("sums").innerHTML = 0;
			}
		}
	} else {
		alert("非法操作");
	}
}
//footprint function noizy
function addfootprint(id){
	if (id) {
		itemid = id;
		var url = ajxurl + "/fprintajax.php?action=add";
		var param = "itemid=" + itemid;
		send_request(url,add_footprint,param);
	}
}
function add_footprint(){
	if (http_request.responseText == '') {
		alert('非法操作');
	} else if (http_request.responseText == 'havenotlogin') {
		alert('未登陆不能推荐!');
	} else if (http_request.responseText == 'havenotfoot') {
		alert('文章id非法');
	} else if (http_request.responseText == 'haveprint') {
		alert('您已经参与推荐');
	} else {
		if (addfoot=='article') {
			GE("footdisplay").style.display = '';
			GE("newid").innerHTML = http_request.responseText;
		}
		GE("numfoot" + itemid).innerHTML = parseInt(GE("numfoot" + itemid).innerHTML)+1;
	}
}
//gbook function
function postmsg(){
	var guestname	= GE("guestname").value;
	var content 	= GE("content").value;
	var ifcode  	= GE("ifcode").value;
	var actdo		= GE("actdo").value;
	if (uid < 1) {
		alert('非法操作！');
		return false;
	}
	if (guestname == '') {
		alert('留言用户名不能为空！');
		return false;
	}
	if (content == '') {
		alert('留言内容不能为空！');
		return false;
	}
	var addurl = '';
	if (ifcode != '') {
		addurl = "&gdcode=" + GE("gdcode").value;
	}
	var url	  = ajxurl + "/gbookajax.php?action=new";
	var param = "actdo=" + actdo + "&uid=" + uid + "&guestname=" + ajax_convert(guestname) + "&content=" + ajax_convert(content) + addurl;
	send_request(url,gpost_response,param);
}
function gpost_response(){
	if (GE("ifcode").value != '') {
		GE("ck").src = 'ck.php?windid=' + GE("ifcode").value;
	}
	if (http_request.responseText == 'gdfalse') {
		alert('验证码错误，请重新输入');
		return false;
	} else {
//		if (GE("actdo").value == 'showone') {
			allows = allow == '1' ? "<div class=\"fr\"><a style=\"cursor:pointer;\" onclick=\"showreplybox('" + nextid + "');\">[回复]</a> <a style=\"cursor:pointer;\" onclick=\"delmsg('" + nextid + "');\">[删除]</a></div>" : '';
			GE("ajaxgbook").innerHTML = 
		  "<a name=\"" + nextid + "\"></a><dl id=\"dl_" + nextid + "\"><dd class=\"comment-pic left\"><img src=\"" + nexticon + "\" width=\"40\" /></dd><dd>" + allows + "<a href=\"" + nexturl + "\" class=\"big b\">" + GE("guestname").value + "</a> <span class=\"gray\">" + nextdate + "</span> Says:</dd><dd class=\"comment-content\"><div>" + http_request.responseText + "</div> <span id=\"sr_" + nextid + "\"></span> <div id=\"r_" + nextid + "\"> </div> </dd></dl>" + GE("ajaxgbook").innerHTML;
		/*} else if (GE("actsdo").value == 'index') {
			if (GE("ifdelete").value == '1') {
				var ifdelete = "<a class=\"more fr\" style=\"cursor:pointer;\" onclick=\"deletecomment(" + GE("actscid").value + ");\">删除</a>";
			}
			GE("ajaxreply").innerHTML = 
		  "<div class=\"c\"></div><div class=\"reply-a \" id=\"" + GE("actscid").value + "\"><div class=\"reply-b\"><div class=\"reply-c\"><a href=\"" + nexturl + "\" class=\"b blue\">" + GE("actsid").value + "</a>(" + GE("actsdate").value + ")回复： &nbsp;" + ifdelete + "</div><div>" + GE("actscontent").value + "</div></div></div>" + GE("ajaxgbook").innerHTML;
		}*/
		GE("gdisplay").style.display = '';
		GE("content").value = '';
		GE("sum").innerHTML = parseInt(GE("sum").innerHTML)+1;
		nextid = parseInt(nextid) + 1;
	}
}
function showreplybox(id){
//	if (GE("actdo").value == 'showone') {
		rgmsg = GE("r_content_" + id) ? GE("r_content_" + id).innerText : '';
		if (GE("r_" + id).style.display == '') {
			GE("r_" + id).style.display = 'none';
			GE("s_" + id).style.display = '';
			GE("s_" + id).innerHTML =
			"<div class=\"re-comment\"><b>回复：</b><div><textarea class=\"ip\" id=\"reply\" tabindex=\"4\" name=\"reply\" rows=\"10\" style=\"width:93%\">" + rgmsg + "</textarea></div><input valign=\"middle\" class=\"bt\" type=\"button\" value=\"回复\" onclick=\"replymsg(" + id + ")\"></div>";
		} else {
			GE("r_" + id).style.display = '';
			GE("s_" + id).style.display = 'none';
			GE("s_" + id).innerHTML = '';
		}
	/*} else if (GE("actdo").value=="index") {
		if(GE("reply_" + id).style.display=="none"){
			GE("reply_" + id).style.display="";
			GE("r_div_" + id).style.display="none";
		}else{
			GE("reply_" + id).style.display="none";
			GE("r_div_" + id).style.display="";
			promsg = GE("r_content_" + id) ? GE("r_content_" + id).innerText : '';
			GE("r_div_" + id).innerHTML =
			"<div id=\"g_reply\">" +
				"<div style=\"padding:1px;margin-bottom:5px;\">" +
					"<textarea id=\"r_box_" + id +"\" style=\"width:346px;height:80px;\">" + promsg + "</textarea> " +
				"</div>" +
				"<div style=\"text-align:right;width:350px;\">" + 
				" <input type=\"button\" value=\"回复\" style=\"background-color:#EEEEEE;border:1px solid #333333;\" onclick=\"replymsg(" + id + ")\"> " +
				"</div>" +
			"</div><br>";
		}
	}*/
}
function replymsg(id){
	if (GE("reply").value == '') {
		alert('留言内容不能为空！');
		return false;
	}
	rid = id;
	var reply = GE("reply").value;
	var url	  = ajxurl + "/gbookajax.php?action=reply";
	var param = "uid=" + uid + "&id=" + id + "&reply=" + ajax_convert(reply);
	send_request(url,greply_response,param);
}
function greply_response(){
	if (http_request.responseText == 'false') {
		alert('非法操作');
		return false;
	} else {
		GE("s_" + rid).innerHTML = '';
		GE("r_" + rid).style.display = '';
		GE("r_" + rid).innerHTML = "<div class=\"re-comment\"> <b>" + replyid + " 于 " + nextdate + " 回复：</b> <div id=\"r_content_" + rid + "\">" + http_request.responseText + "</div> </div>";
	}
}
function delmsg(id){
	if (confirm('您确认要删除此条留言！')) {
		gid = id;
		var url = ajxurl + "/gbookajax.php?action=delete";
		var param = "uid=" + uid + "&id=" + id;
		send_request(url,gdelete_response,param);
	}
}
function gdelete_response(){
	if (http_request.responseText == 'false') {
		alert('非法操作');
		return false;
	} else if (http_request.responseText == 'deletefalse') {
		alert('没有删除权限');
		return false;
	} else {
		GE('dl_' + gid).style.display = "none";
		GE("sum").innerHTML = parseInt(GE("sum").innerHTML)-1;
	}
}