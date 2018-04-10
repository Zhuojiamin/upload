function show_itemtype(){
	obj = document.getElementById('itemtype');
	if (!obj){
		return;
	}
	if(obj.style.display == ""){
		obj.style.display ='none';
	}else{
		obj2 = GE("itemtype_button");
		obj.style.top	= findPosY(obj2)+20;
		obj.style.left	= findPosX(obj2);
		obj.style.display	= "";
	}
}
function add_itemtype(){
	name = GE("typename").value;
	order = GE("typeorder").value;
	if(name){
		var url	  = user_file + "?action=itemtype&job=add&type=" + itemtype;
		var param = "typename=" + ajax_convert(name) + '&order=' + ajax_convert(order);
		send_request(url,add_itemtype_response,param);
	}else{
		alert('分类名称不能为空！');
	}
}
function add_itemtype_response(){
	var typeid;
	typeid = http_request.responseText;
	if(!typeid){
		return;
	}

	_typename = GE("typename").value;
	_typeorder = GE("typeorder").value;
	GE("typename").value = '';
	GE("typeorder").value = '';

	obj = GE("itemtype_list");

	tb_row = document.createElement("tr");
	tb_row.id = 'itemtype_' + typeid
	obj.appendChild(tb_row);

	tb_cell = document.createElement("td");
	tb_cell.align='center';
	tb_row.appendChild(tb_cell);

	input = document.createElement('input');
	input.id = 'typename_' + typeid;
	input.type = 'text';
	input.size = 15;
	input.className ='input';
	input.maxLength = 50;
	input.value = _typename;
	tb_cell.appendChild(input);

	tb_cell = document.createElement("td");
	tb_cell.innerHTML = 
'<input type="text" size="5" class="input" id="typeorder_' + typeid + '" value="' + _typeorder + '"> &nbsp; ' +
'<a href="javascript:" onclick="edit_itemtype(' + typeid + ')" onmouseover="this.style.textDecoration=\'underline\';" onmouseout="this.style.textDecoration=\'none\';">修改</a>'+
' <a href="javascript:" onclick="del_itemtype(' + typeid + ')" onmouseover="this.style.textDecoration=\'underline\';" onmouseout="this.style.textDecoration=\'none\';">删除</a>';

	tb_row.appendChild(tb_cell);

	op = document.createElement("option");
	op.id = 'itemtype_op_' + typeid;
	op.appendChild(document.createTextNode(_typename));
	op.value = typeid;
	GE("itemtype_select").appendChild(op);
}
function edit_itemtype(id){
	newname = GE('typename_' + id).value;
	neworder = GE('typeorder_' + id).value;
	if(!newname){
		alert('分类名称不能为空！');
		return;
	}
	var url	  = user_file + "?action=itemtype&job=edit&type=" + itemtype + "&typeid=" + id;
	var param = "newname=" + ajax_convert(newname) + '&neworder=' + ajax_convert(neworder);
	send_request(url,edit_itemtype_response,param);
}
function edit_itemtype_response(){
	id = http_request.responseText;
	if(id){
		GE('itemtype_op_' + id).text=GE('typename_' + id).value;
		alert('分类编辑成功！');
	} else {
		alert('分类编辑失败！');
	}
}
function del_itemtype(id){
	if(confirm('您确认要删除此分类？')){
		var url	  = user_file + "?action=itemtype&job=del&type=" + itemtype + "&typeid=" + id;
		var param = "";
		send_request(url,del_itemtype_response,param);
	}
}
function del_itemtype_response(){
	id = http_request.responseText;
	if(id){
		GE("itemtype_list").removeChild(GE('itemtype_' + id));
		GE("itemtype_select").removeChild(GE('itemtype_op_' + id));
	}else{
		alert('分类删除失败！');
	}
}