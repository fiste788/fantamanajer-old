function ismaxlength(obj,maxLenght)
{
	var mlength=maxLenght;
	if (obj.getAttribute && obj.value.length>mlength)
	{
		var cursor = obj.selectionEnd;
		var scroll = obj.scrollTop;
		alert("Hai raggiunto il massimo di caratteri consentito")
		obj.value=obj.value.substring(0,mlength);
		obj.selectionEnd = cursor;
		obj.scrollTop = scroll;
	}
	document.getElementById(obj.name + 'Cont').value = mlength - obj.value.length;
}
function setSelectOptions(do_check)
{
	var selectObject = document.forms['newsletter'].elements['selezione'];
	var selectCount = selectObject.length;
	for (var i = 0; i < selectCount; i++)
		selectObject.options[i].selected = do_check;
	return true;
}