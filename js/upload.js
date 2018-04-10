var aid = 0;

function newAttach(){
	if(!IsElement('attach'))
		return;
	aid++;
	attachnum--;
	var s = GE('att_mode').firstChild.cloneNode(true);
	var id = aid;
	s.id = 'att_div' + id;
	var tags = s.getElementsByTagName('input');
	for(var i=0;i<tags.length;i++){
		tags[i].name += id;
		tags[i].id = tags[i].name;
		if(tags[i].name == 'attachment_'+id){
			tags[i].onchange = function(){upAttach(id);};
		}
	}
	GE('attach').appendChild(s);
}
function upAttach(id){
	var div  = GE('att_div'+id);
	var path = GE('attachment_'+id).value;
	var attach_ext = path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
	if(allow_ext != '  ' && (attach_ext == '' || allow_ext.indexOf(attach_ext) == -1)){
		if(IsElement('att_span'+id)){
			div.removeChild(GE('att_span'+id));
		}
		if(path != '') alert('附件类型不匹配!');
		return false;
	}
	GE('attachment_'+id).onmouseover = function(){viewimg(id)};
	if(!IsElement('att_span'+id)){
		var span = document.createElement("span");
		if(type == 'blog'){
			var s    = document.createElement("a");
			s.className    = 'abtn';
			s.unselectable = 'on';
			s.onclick      = function(){addattach(id)};
			s.innerHTML    = '插入';
			span.appendChild(s);
		}
		var s    = document.createElement("a");
		s.className    = 'abtn';
		s.unselectable = 'on';
		s.onclick      = function(){delupload(id)};
		s.innerHTML    = '删除';
		span.appendChild(s);
		span.id = 'att_span'+id;
		span.style.marginLeft = '1px';
		div.appendChild(span);
	}
	if(attachnum>0 && GE('attach').lastChild.id == 'att_div'+id){
		newAttach();
	}
}
function viewimg(id){
	var path = GE('attachment_'+id).value;
	var attach_ext = path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
	if(!is_ie || !in_array(attach_ext,['jpg','gif','png','bmp','jpeg']))
		return;
	GE('viewimg').innerHTML = getimage(path,320);
	open_menu('viewimg','att_div'+id,3);
}
function getimage(path,maxwh){
	var str = '<div style="padding:6px"><img src="' + path + '" ';
	var img = new Image();
	img.src = path;
	if(img.width>maxwh || img.width>maxwh){
		str += ((img.width/img.height)>1 ? 'width' : 'height') + '="' + maxwh + '" ';
	}
	str += '/></div>';
	return str;
}

function delupload(id){
	GE('att_div'+id).parentNode.removeChild(GE('att_div'+id));
	attachnum++;
	if(GE('attach').hasChildNodes()==false){
		newAttach();
	}
}
