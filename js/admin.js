var ifcheck = true;
function CheckAll(form){
	for (var i=0;i<form.elements.length-2;i++) {
		var e = form.elements[i];
		if (e.type=='checkbox') {
			e.checked = ifcheck;
		}
	}
	ifcheck = ifcheck == true ? false : true;
}
function Checkdel(form,msg){
	if (GE("delete").checked==1 || GE("delete").value==1) {
		var delcheck = 0;
		for (var i=0;i<form.elements.length-2;i++) {
			var e = form.elements[i];
			if (e.type=='checkbox' && e.checked == true) {
				delcheck = 1;
				break;
			}
		}
		if (delcheck==1 && !confirm(msg)) {
			return false;
		}
	}
	return true;
}
function Changselect(id){
	GE(id).checked = 1;
}
function GE(id) {
	if (document.getElementById && document.getElementById(id)) {
		return document.getElementById(id);
    } else if (document.all && document.all[id]) {
		return document.all[id];
	} else if (document.layers && document.layers[id]) {
		return document.layers[id];
	} else {
		return null;
    }
}
function showselect(type){
	var selects = get_tags(document,'select');
	for(i=0; i<selects.length; i++){
		selects[i].style.visibility = type;
	}
}
function get_tags(parentobj, tag){
	if (typeof parentobj.getElementsByTagName != 'undefined'){
		return parentobj.getElementsByTagName(tag);
	}else if (parentobj.all && parentobj.all.tags){
		return parentobj.all.tags(tag);
	}else{
		return null;
	}
}
function deleteslt(url,deletemsg){
	if (!confirm(deletemsg)) {
		return false;
	} else {
		window.location = url;
	}
}
function level_jump(id,basename){
	window.location = basename + "=" + GE(id).value;
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