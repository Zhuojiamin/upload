var ColorHex = new Array('00','33','66','99','CC','FF')
var SpColorHex = new Array('FF0000','00FF00','0000FF','FFFF00','00FFFF','FF00FF')

function EditStyle(obj,id){
	var url	  = ajaxurl + "?action=getstyleinfo";
	var param = '';
	send_request(url,EditStyle_response,param);
	//GE('bgcolor_text').value = document.body.style.backgroundColor;
	//GE('bannerimg_text').value = GE('header').style.backgroundImage;
	//GE('bgimg_text').value = document.body.style.backgroundImage;
	open_menu(obj,id,2);
}

function EditStyle_response(){
	var userdiycss = http_request.responseText;
	userdiycss = userdiycss.split("\t");
	GE('bgcolor_text').value = userdiycss[0];
	GE('bannerimg_text').value = userdiycss[1];
	GE('bgimg_text').value = userdiycss[2];
}

function ShowC(obj,id){
	var colorbox = '';
	colorbox = '<table cellspacing="0">';
	for (i=0; i<2; i++) {
		for (j=0; j<6; j++) {
			colorbox = colorbox+'<tr height=12>'
			colorbox = colorbox+'<td width=11 style="cursor:pointer;background-color:#000000" onClick="SetC(\'000000\')">'
			if (i == 0) {
				colorbox = colorbox+'<td width=11 style="cursor:pointer;background-color:#'+ColorHex[j]+ColorHex[j]+ColorHex[j]+'" onClick="SetC(\''+ColorHex[j]+ColorHex[j]+ColorHex[j]+'\')">';
			} else {
				colorbox = colorbox+'<td width=11 style="cursor:pointer;background-color:#'+SpColorHex[j]+'" onClick="SetC(\''+SpColorHex[j]+'\')">'
			} 
			colorbox = colorbox+'<td width=11 style="cursor:pointer;background-color:#000000" onClick="SetC(\'000000\')">'
			for (k=0;k<3;k++) {
				for (l=0;l<6;l++) {
					colorbox=colorbox+'<td width=11 style="cursor:pointer;background-color:#'+ColorHex[k+i*3]+ColorHex[l]+ColorHex[j]+'" onClick="SetC(\''+ColorHex[k+i*3]+ColorHex[l]+ColorHex[j]+'\')">'
				}
			}
		}
	}
	colorbox = colorbox + '<tr><td colspan="21" style="text-align:center;background-color:#999999;cursor:pointer;"><a onClick="SetC(\'FFFFFF\')">使用白色</a></td></tr></table>';
	GE(obj).innerHTML = colorbox;
	open_menu_second(obj,id,1);
}
function SetC(colorID){
	GE('bgcolor_text').value = '#' + colorID;
	document.body.style.backgroundColor ='#' + colorID;
}

function ChangeC(){
	document.body.style.backgroundColor = GE('bgcolor_text').value;
}

function SetBanner(url){
	url = GE('bannerimg_text').value;
	GE('header').style.backgroundImage="url("+url+")";
	GE('header').style.backgroundRepeat="no-repeat";
}

function SetBgImg(url){
	url = GE('bgimg_text').value;
	document.body.style.backgroundImage="url("+url+")";
	document.body.style.backgroundRepeat="no-repeat";
}

function ChangeBgImgStyle(id){
	if(id == 'bgtype'){
		if(GE('bgtype').checked == false){
			document.body.style.backgroundRepeat="no-repeat";
		}else if(GE('bgtype').checked == true){
			document.body.style.backgroundRepeat="repeat";
		}
	}else if (id == 'nobg'){
		if(GE('nobg').checked == false){
			document.body.style.backgroundImage="";
		}else if(GE('nobg').checked == true){alert(document.body.style.backgroundImage);
			document.body.style.backgroundImage='';
		}
	}
}

function SaveStyle(){
	var bgcolor = GE('bgcolor_text').value;
	var bannerimg = GE('bannerimg_text').value;
	var bgimg = GE('bgimg_text').value;
	var bgtype = GE('bgtype').checked == true ? '1' : '0';
	var url	  = ajaxurl + "?action=savestyle";
	var param = "bgcolor=" + ajax_convert(bgcolor) + "&bannerimg=" + ajax_convert(bannerimg) + "&bgimg="+ ajax_convert(bgimg) + "&bgtype="+bgtype;
	send_request(url,save_style_response,param);
}

function save_style_response(){
	var msg = http_request.responseText;
	if(msg == 'bgcolor_error'){
		alert('背景颜色格式错误！');
		GE('bgcolor_text').value = '';
	}else if(msg == 'bannerimg_error'){
		alert('banner图链接格式错误！');
		GE('bannerimg_text').value = '';
	}else if(msg == 'bgimg_error'){
		alert('背景图链接格式错误！');
		GE('bannerimg_text').value = '';
	}else if(msg == 'success'){
		alert('风格编辑成功！');
		closem();
	}
}

function RevertStyle(){
	var url	  = ajaxurl + "?action=revertstyle";
	var param = "";
	send_request(url,revert_style_response,param);
}

function revert_style_response(){
	var msg = http_request.responseText;
	if(msg == 'success'){
		window.location.reload();
		alert('还原成功！');
	}
}




