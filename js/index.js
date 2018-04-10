var agt = navigator.userAgent.toLowerCase();
var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var linkcookie_name = 0;
var linkobj  = GE('showtable');
var linkobjs  = GE('linkmenu');
var tempnum = new Array();
var voteid = new Array();
var n = 0;
var timer;
imgpics  = imgpics.split('|');
imglinks = imglinks.split('|');
imgtexts = imgtexts.split('|');
setIntervals();
showdivlist('showlist1','hotatc');
showdivlist('showlist2','hotatcs');
showdivlist('showlist3','newusers');
if (showlist1) {
	showdivlist('showlist4','newblogs');
}
if (showlist2) {
	showdivlist('showlist5','newbookmarks');
}
document.onkeydown = function() {
	if (window.event.keyCode==27) {
		if (linkobj.style.display == '') {
			Link_close();
		}
	}
}
Link_close();
function Link_open(id){
	if (linkobj.style.display=='') {
		Link_close();
		return false;
	}
	linkcookie_name = 1;
	mouseover_open(id);
}
function mouseover_open(id){
	if (linkcookie_name == 1) {
		var left = findPosX(GE(id)) + ietruebody().scrollLeft;
		var top  = findPosY(GE(id)) + ietruebody().scrollTop;
		linkobj.innerHTML = linkobjs.innerHTML;
		linkobj.className = linkobjs.className;
		linkobj.style.filter = 'alpha(opacity=96);opacity:0.96;';
		linkobj.style.display = '';
		if (left + linkobj.offsetWidth > ietruebody().scrollLeft + ietruebody().clientWidth) {
			left -= linkobj.offsetWidth;
		}
		if (linkobj.offsetHeight + top > ietruebody().scrollTop + ietruebody().clientHeight) {
			top -= linkobj.offsetHeight;
		} else {
			top += 15;
		}
		linkobj.style.top = top + 'px';
		linkobj.style.left = left + 'px';
	}
	return false;
}
function doc_mousedown(e){
	var e = is_ie ? event : e;
	var _x	= is_ie ? e.x : e.pageX;
	var _y	= is_ie ? e.y + ietruebody().scrollTop : e.pageY;
	var _x1 = linkobj.offsetLeft;
	var _x2 = linkobj.offsetLeft + linkobj.offsetWidth;
	var _y1 = linkobj.offsetTop - 25;
	var _y2 = linkobj.offsetTop + linkobj.offsetHeight;
	if (_x<_x1 || _x>_x2 || _y<_y1 || _y>_y2) {
		Link_close();
	}
}
function findPosX(obj){
	var curleft = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	} else if (obj.x) {
		curleft += obj.x;
	}
	return curleft - ietruebody().scrollLeft;
}
function findPosY(obj){
	var curtop = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	} else if (obj.y) {
		curtop += obj.y;
	}
	return curtop - ietruebody().scrollTop;
}
function ietruebody(){
	return (document.compatMode && document.compatMode!="BackCompat") ? document.documentElement : document.body;
}
function AddLink(){
	if (!winduid) {
		alert('您还没有登陆或注册，暂时不能使用此功能！');
		return false;
	}
	if (GE('linkname').value == '' || GE('linkurl').value == '' || GE('linkdescrip').value == '') {
		alert('请认真填写每项');
		return false;
	}
	var url = ajaxurl + '?action=link';
	var param = 'name=' + ajax_convert(GE('linkname').value) + '&url=' + ajax_convert(GE('linkurl').value) + '&descrip=' + ajax_convert(GE('linkdescrip').value) + '&logo=' + ajax_convert(GE('linklogo').value);
	send_request(url,AddLinkResponse,param);
}
function AddLinkResponse(){
	if (http_request.responseText == 'not_login') {
		alert('请注册后再申请');
	} else if (http_request.responseText == 'operate_fail') {
		alert('操作失败，请检查数据完整性');
	} else if (http_request.responseText == 'have_link') {
		alert('你的申请还没批准，请耐心等待');
	} else if (http_request.responseText == 'have_limit') {
		alert('一个用户只能申请一次');
	} else {
		alert('添加链接成功，请等待审核');
	}
	GE('linkname').value = '';
	GE('linkurl').value = '';
	GE('linkdescrip').value = '';
	GE('linklogo').value = '';
	Link_close();
	return false;
}
function Link_close(){
	linkcookie_name = 0;
	linkobj.innerHTML = '';
	linkobj.className = '';
	linkobj.style.display = 'none';
	if (is_ie) {
		document.detachEvent('mousedown',doc_mousedown);
	} else {
		document.removeEventListener('mousedown',doc_mousedown,true);
	}
	return false;
}
function showdivlist(showlist,show){
	var obj  = GE(showlist);
	var objs = obj.getElementsByTagName('a');
	for (var i=0;i<objs.length;i++) {
		if (objs[i].id==show) {
			if (GE(show + 'div').style.display == 'none') {
				GE(show).className = 'tabA1A tabA1B';
				GE(show + 'div').style.display = '';
			}
		} else {
			GE(objs[i].id).className = 'tabA1A';
			GE(objs[i].id + 'div').style.display = 'none';
		}
	}
}
function CheckVote(obj,id,maxkey){
	if ((obj.type=='checkbox' || obj.type=='radio') && maxkey!=0) {
		if (!tempnum[id]) {
			tempnum[id] = 0;
		}
		if (!voteid[id]) {
			voteid[id] = '';
		}
		if (obj.checked) {
			if(obj.type == 'checkbox'){
				if (tempnum[id]==maxkey) {
					alert("限选" + maxkey + "项");
					return false;
				}
				voteid[id] += (voteid[id] ? '|' : '') + obj.value;
				tempnum[id]++;
			} else {
				voteid[id] = obj.value;
				tempnum[id] = 1;
			}
		} else {
			tempnum[id]--;
		}
	}
}
function AddVote(id){
	var url = ajaxurl + '?action=vote';
	var param = '';
	if (!winduid) {
		alert('您还没有登陆或注册，暂时不能投票！');
		return false;
	}
	if (!tempnum[id]) {
		alert('没有选择任意项');
		return false;
	}
	param = 'votenum=' + tempnum[id] + '&voteids=' + voteid[id] + '&vid=' + ajax_convert(id);
	send_request(url,AddVoteResponse,param);
}
function AddVoteResponse(){
	var result = http_request.responseText;
	var obj  = GE('showvote');
	var objs = obj.getElementsByTagName('input');
	for (var i=0;i<objs.length;i++) {
		if (objs[i].type=='checkbox') {
			objs[i].checked = false;
			tempnum = new Array();
			voteid = new Array();
		}
	}
	if (result == '') {
		alert('非法操作');
	} else if (result == 'not_login') {
		alert('您还没有登陆或注册，暂时不能投票！');
	} else if (result == 'erro_voteid') {
		alert('没有选择任意项');
	} else if (result == 'have_voted') {
		alert('您已经参与此项投票，谢谢！');
	} else {
		alert('投票成功！');
	}
	return false;
}
function Img_Play(){
	if (n > imgnums) {
		n=0;
	}
	Img_Show(n);
	n++;
}
function Img_Show(n){
	if (imgpics[n]) {
		if (document.all) {
			document.all.player.style.filter = 'progid:DXImageTransform.Microsoft.Fade(duration=1,overlap=0.25)';
			document.all.player.filters[0].Apply();
		}
		GE('imgpic').style.display = '';
		GE('imgpic').src = imgpics[n];
		GE('imglink').href = imglinks[n];
		if (imgtexts[n]) {
			GE('imgtext').innerHTML = '<a href="' + imglinks[n] + '" target="_blank"><b>' + imgtexts[n] + '</b></a>';
			GE('imgtext').style.display = '';
		} else {
			GE('imgtext').style.display = 'none';
		}
		if (document.all) {
			document.all.player.style.visibility = "visible";
			document.all.player.filters[0].Play();
		}
	}
}
function setIntervals(){
	timer = setInterval('Img_Play()',3000);
}
function clearIntervals(){
	clearInterval(timer);
}
function doRandom(){
	GE('randomlist').innerHTML = '数据载入中...';
	var maxid = parseInt(GE('maxrandomlist').innerHTML);
	var url = ajaxurl + '?action=dorandom';
	var param = 'maxid=' + maxid;
	send_request(url,doRandomResponse,param);
}

function doRandomResponse(){
	var msg = http_request.responseText;
	msg = msg.split("\t");
	var randomlist = '';
	for(var i in msg){
		var onemsg = msg[i].split("|");
		var m = parseInt(i)+1;
		randomlist += '<li>'+ m +'. <a href="'+ articleurl + 'type=' + onemsg[3] + '&itemid=' +onemsg[0] +'" target="_blank">'+ onemsg[4]  +'</a></li>';
	}
	GE('randomlist').innerHTML = randomlist;
	GE('maxrandomlist').innerHTML = m;
}