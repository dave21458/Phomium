var ajaxDone = true;
var xmlHttp;
var destObj;
var ajaxArr=Array();
var t_ajax;
var ajaxObj = {};

function ajax()
{
	var xmlHttp=null;
	try
	{
	  // Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}catch (e){
	  // Internet Explorer
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}catch (e){
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}
	
function upDateObj()
{
	if(xmlHttp.readyState==4)
	{
		var rTxt=xmlHttp.responseText;
		if(rTxt.length<2)
		{
			ajaxDone=true;
			return;
		}
		var dests = rTxt.split("&&&");
		if(!typeof(destObj)){
			for(var cnt=1;cnt < dests.length;cnt=cnt+2)
			{
				if(dests[cnt+0].substr(0,2)=="js")
				{
					var Obj =dests[cnt+0].substr(3,dests[cnt+0].length)		
					ajaxObj[Obj] =  dests[cnt+1];
					continue;
				}
				if(dests[cnt+0]=="alert")
				{
					confirm(dests[cnt+1],"Filer Information",0x10);
					continue;
				}else{
					document.getElementById(dests[cnt+0]).innerHTML=dests[cnt+1];
				}
			}
		}
		if(typeof(destObj) == "function")destObj(dests);
		if(!document.getElementById(destObj) && typeof(destObj) != "function")ajaxObj[destObj]=dests[0];
		if(document.getElementById(destObj) && dests[0].length > 0)document.getElementById(destObj).innerHTML=dests[0];
		ajaxDone=true;
	}
}

function ajaxReq(id,srcUrl,data)
{
	//var dataArr=data.split("&");
	var ajaxdata=[id,srcUrl,data];
	ajaxArr.push(ajaxdata);
	//ajaxExecCnt++;
	if(ajaxArr.length==1 && ajaxDone)ajaxExec();
	t_ajax=setTimeout("ajaxExec()",100);
}
  
function ajaxExec()
{
	if(ajaxDone && ajaxArr.length > 0)
	{
		var ajaxdata = ajaxArr.shift();
		xmlHttp = ajax();
		if (xmlHttp==null)
		{
		  alert ("Your browser does not support AJAX!");
		  return;
		} 
		destObj=ajaxdata[0];
		ajaxDone=false;
		xmlHttp.onreadystatechange=upDateObj;
		var txt = ajaxdata[1]+"?"+ajaxdata[2];
		xmlHttp.open("GET",txt,true);
		xmlHttp.send(null);
	}else{
		if(ajaxArr.length == 0)
		{
			clearTimeout(t_ajax);
		}else{
			t_ajax=setTimeout("ajaxExec()",100);
		}
	}
}
