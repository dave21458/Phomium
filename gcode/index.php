<?php
	include_once 'gcode.php';
	getGCode();
?>

<html>
<head>
<title> Gcode Editor</title>
<script>
ts = new Date().getTime();
</script>
<script type="text/javascript" src="jquery-ui-1.11.4.custom\jquery.js"></script>
<script type="text/javascript" src="jquery-ui-1.11.4.custom\jquery-ui.js"></script>
<script type="text/javascript" src="jquery-ui-1.11.4.custom\jquery-ui.min.js"></script>
<script type="text/javascript" src="ajax.js"></script>
<script>
sload = new Date().getTime();
</script>
<script type="text/javascript" src="model.js"></script>
<script>
eload = new Date().getTime();
</script>
<link rel="stylesheet" type="text/css" href="jquery-ui-1.11.4.custom\jquery-ui.min.css" media="screen" />
<style>
.canvas{
	border:1px solid #000000;
	position:absolute;
	left:10;
	top:50;
}
.lSlider{
	position:absolute;
	left:520;
	top:50;
}
.sSlider{
	position:absolute;
	left:10;
	top:600;
}
.info{
	position:absolute;
	left:530;
	top 50;
	width:350;
	border:1px solid #000000;
	padding : 5 5 5 5;
}
.gcode{
	position:absolute;
	left:780;
	top:10;
	height:750;
	width:400;
	overflow-y: auto;
	border:1px solid #000000;
}
.gcodeline{
	color:grey;
}
</style>
	
</head>
<body>


<label for = 'measure'> Measured </label> <strong id= 'measure'> X0 : Y0 : L0 </strong>
<br/>
<div id='display'>
	<canvas id = 'gcodeCanvas' width = 500 height = 500 class = 'canvas' ></canvas>
	<div class = 'lSlider' id = 'lSlider'>
		<div id = 'layerSlider' ></div>
	</div>
	<div class = 'sSlider' id = 'sSlider'>
		<div id = 'segmentSlider' ></div>
	</div>
</div>
<div id = 'info' class = 'info'>
	<h2 id = 'modelDataLabel'>Model Data</h2>
	<div id = 'modelData'>
		File Name : <strong id = 'fName'>0</strong><br/>
		Total Layers : <strong id = 'totLay'>0</strong><br/>
		Total Segments : <strong id = 'totSeg'>0</strong><br/>
		Total Moves : <strong id = 'totMov'>0</strong><br/>
		Total Retracts : <strong id = 'totRet'>0</strong><br/>
		Total Printed Length : <strong id = 'totPriLen'>0</strong><br/>
		Total Move Length : <strong id = 'totMovLen'>0</strong><br/>
		Total Extruded  : <strong id = 'totExt'>0</strong>
	</div>
	<h2 id = 'layerDataLabel'>Layer Data</h2>
	<div id = 'layerData'>
		<label for = 'laySel'>Selected Layer </label> <input id = 'laySel' type = 'number' value = '1' min = '1' max = '1' onchange = 'changeLayer(this.value)'> <br/>
		Layer Height : <strong id = 'layHigh'> .3 </strong><br/>
		Segments : <strong id = 'laySeg'> 0 </strong><br/>
		Moves : <strong id = 'layMov'> 0 </strong><br/>
		Retracts : <strong id = 'layRet'> 0 </strong><br/>
		Total Extruded  : <strong id = 'layExt'>0</strong>
	</div>
	<h2 id = 'segmentDataLabel'>Segment Data</h2>
	<div id = 'segmentData'>
		Segment # : <strong id = 'segNum'> 0 </strong><br/> 
		Extruded Length/Rate : <strong id = 'segExt'> 0 </strong><br/>
		Print Length/Speed : <strong id = 'segLen'> 0 </strong><br/>
		Move Length/Speed :  <strong id = 'segMov'> 0 </strong><br/>
		Retract Length/Speed :  <strong id = 'segRet'> 0 </strong><br/>
		GCode :  <strong id = 'line'>  </strong>
	</div>
	<h2 id = 'optionsLabel'> Options</h2>
	<div id = "options">
		<input type = 'checkbox' id = 'optMove' onchange = 'this.checked ? options.showMoves = 1:options.showMoves = 0;showLayers();'/>Show Moves<br/>
		<input type = 'checkbox' id = 'optRetract' onchange = 'this.checked ? options.showRetracts = 1:options.showRetracts = 0;showLayers()'/>Show Retracts  <br/>
		<input type = 'checkbox' id = 'optPrevLay' onchange = 'this.checked ? options.showPrevLayers = 1:options.showPrevLayers = 0;showLayers();'/>Show Previous Layers (will cause lagging) <br/>
		<input type = 'checkbox' id = 'optAllLay' onchange = 'this.checked ? options.showAllLayers = 1:options.showAllLayers = 0;showLayers();'/>Show All Layers (not yet implemented)<br/>
		<input type = 'checkbox' id = 'optgcode' onchange = 'this.checked ? options.showGcode = 1:options.showGcode = 0;showLayers();'/>Show Gcode; 
	</div>
</div>
<div id = 'gcodeFile' class = 'gcode'></div>
<script>


function getFile()
{
	if(phomium.result){
		clearLayer();
		ctx.font = "30px Arial";
		ctx.strokeText("Parsing File " ,30,30);
		ajaxReq(LoadModel,'gcode.php',"file=" + phomium.resultText + "\\" + phomium.resultArray[0]);
	}
}

function LoadModel(scriptName) {
   ctx.font = "30px Arial";
   ctx.strokeText("Loading Data " ,30,60);
   var script = document.createElement('script');
   script.src = scriptName[1];
   script.onload = function(){
	   updateModelInfo();
	   showGcode();
	   changeLayer(1);
	};
	document.head.appendChild(script);
}

function showLayers()
{
	lts = new Date().getTime();
	clearLayer();
	if(options.showPrevLayers){
		ctx.globalAlpha = .3;
		for(var cnt1 = 1;cnt1 < curLayer;cnt1++){
			drawLayer(cnt1);
		}
	}
	ctx.globalAlpha = 1;
	drawLayer(curLayer);
	drawScale();
	upMeasure();
	updateLayerInfo();
	te = new Date().getTime();
}

function changeLayer(lay)
{
	if(lay > model.length)lay = model.length;
	curLayer = lay;
	clearLayer();
	drawTo = model[curLayer].printSegments;
	showLayers();
	$("#segmentSlider").slider("option","max",model[curLayer].printSegments);
	$("#segmentSlider").slider("option","value",model[curLayer].printSegments);
	$("#layerSlider").slider("option","value",lay);
	
}

function drawLayer(layer)
{
	var pCnt = 0;
	var mCnt = 0;
	var rCnt = 0;
	var sCnt = 0;
	var hasMove = -1;
	var hasRetract = -1;
	var print = model[layer].print;
	var move = model[layer].move;
	var retract = model[layer].retract;
	offset.pCenterX = offset.centerX;
	offset.pCenterY = offset.centerY;
	offset.centerX = (offset.px - offset.x);
	offset.centerY = (offset.py - offset.y); 
	layer == curLayer ? sCnt = drawTo:sCnt = model[layer].printSegments
	while(pCnt < sCnt){
		ctx.beginPath();
		pCnt == sCnt-1 ? ctx.setLineDash([5,10]):ctx.setLineDash([1,0]);
		if(rCnt < retract.length){
			if(retract[rCnt].line <= print[pCnt].line){
				if(options.showRetracts){
					retract[rCnt].de > 0 ? ctx.fillStyle = colors.retractIn: ctx.fillStyle = colors.retractOut ;
					drawCircle(retract[rCnt]);
				}
				if(pCnt == sCnt - 1)hasRetract = rCnt;
				rCnt++;
			}
		}
		if(mCnt < move.length){
			if(move[mCnt].line <= print[pCnt].line){
			if(options.showMoves){
				ctx.strokeStyle = colors.move;
				drawLine(move[mCnt]);
			}
			if(pCnt == sCnt - 1)hasMove = mCnt;
			mCnt++;
			}
		}
		ctx.strokeStyle = colors.print;
		drawLine(print[pCnt]);
		pCnt++;
	}
	ctx.setLineDash([1,0])
	if(layer == curLayer)updateSegmentInfo(pCnt - 1,hasRetract,hasMove);
}

function drawLine(data)
{
	var sx = (data.px * zoom) + offset.x;
	var sy = (data.py * zoom) + offset.y;
	var ex = (data.x * zoom) + offset.x;
	var ey = (data.y * zoom) + offset.y
	ctx.moveTo(sx,sy);
	ctx.lineTo(ex,ey);
	ctx.stroke();
}

function drawCircle(data)
{
	ctx.arc((data.x * zoom) + offset.x,(data.y * zoom )+ offset.y,retractCirRad, 0, 2 * Math.PI);
	ctx.fill();
}

function drawScale()
{
	ctx.beginPath();
	ctx.strokeStyle = colors.text;
	ctx.globalAlpha = .3;
	ctx.globalCompositeOperation = "lighter";
	ctx.font = "15px Arial";
	ctx.strokeText("Zoom Level:" + (Math.round(zoom * 10) / 10) + "  Center X" + (Math.round(((offset.px - offset.x)/zoom * 1000))/1000)+ " : Y" + (Math.round(((offset.py - offset.y)/zoom * 1000))/1000),30,30);
	ctx.moveTo(offset.px - 50,offset.py);
	ctx.lineTo(offset.px + 50,offset.py);
	ctx.stroke();
	ctx.moveTo(offset.px,offset.py - 50);
	ctx.lineTo(offset.px ,offset.py+ 50);
	ctx.stroke();
	ctx.globalAlpha = 1;
}

function clearLayer()
{
	ctx.clearRect(0,0,gc.width,gc.height);
}

function showGcode()
{
	var str ="";
	$("#gcodeFile").html(str);
	for(var cnt = 1;cnt < model.gcode.length;cnt++){
		str += "<span class = 'gcodeline' id = 'g" + cnt + "'>";
		str += "<strong>"+ cnt + "</strong> " + model.gcode[cnt];
		str += "</span><br/>";
		
	}
	$("#gcodeFile").html($("#gcodeFile").html() + str);
}

function zoomer(e)
{
	if(!e.button == 1){
		pZoom = zoom;
		if((e.wheelDelta < 0 && zoom > .2) || (e.wheelDelta > 0 && zoom < 50)){
			e.wheelDelta > 0 ? zoom += 1:zoom  -= 1;
			clearLayer();
			showLayers();
		}
	}
}

function mover(e)
{
	if(e.button == 0){
		offset.x += e.clientX - pMouseX;
		offset.y += e.clientY - pMouseY;
		pMouseX = e.clientX;
		pMouseY = e.clientY;
		clearLayer();
		showLayers();
	}
}

function startKeys()
{
	document.addEventListener("keydown",keys, false);
}

function keys(e)
{
	switch(e.keyIdentifier){
		case 'Down':
			offset.y++;
			break;
		case 'Up':
			offset.y--;
			break;
		case 'Right':
			offset.x++;
			break;
		case 'Left':
			offset.x--;
			break;
		case "U+004B":
		case "U+00BB":
			pZoom = zoom;
			zoom += .1;
			break;
		case "U+004D":
		case "U+00BD":
			pZoom = zoom;
			zoom -= .1;
			break;
	}
	clearLayer();
	showLayers();
}
		

function startMover(e)
{
	if(e.button == 0){
		gc.addEventListener("mousemove",mover,false);
		pMouseX = e.clientX;
		pMouseY = e.clientY;
	}
	if(e.button == 1) measurer(e);	
}

function stopMover(e)
{
	gc.removeEventListener("mousemove",mover);
	document.removeEventListener("keydown",keys);
}

function measurer(e)
{
	if(e.button == 1){
		offset.msrX = offset.x;
		offset.msrY = offset.y;
		msrLen = 0;
		startKeys();
		upMeasure();
	}
}

function upMeasure()
{
	var x = Math.round(((offset.x - offset.msrX)/zoom)*100)/100;
	var y = Math.round(((offset.y - offset.msrY)/zoom)*100)/100;
	var len = Math.sqrt(Math.pow(x,2) + Math.pow(y,2))
	len = Math.round(len * 100)/100;
	$("#measure").html("X" + x + " : Y" + y + " :Length " + len);
}

function updateModelInfo()
{
	$("#fName").html(model.name);
	$("#totLay").html(layCnt);
	$("#totSeg").html(totalSegments);
	$("#totMov").html(totalMoves);
	$("#totRet").html(totalRetracts);
	$("#totPriLen").html(Math.round(totalPrintLen) + "mm");
	$("#totMovLen").html(Math.round(totalMoveLen) + "mm");
	$("#totExt").html(Math.round(totalExtrudeLen * 10)/10 + "mm");
	$("#layerSlider").slider("option","max",layCnt);
}

function updateLayerInfo()
{
	var layerExtr = 0;
	$("#layHigh").html(layers_z[curLayer - 1]);
	laySelect.value = curLayer;
	$("#laySeg").html(model[curLayer].print.length);
	$("#layMov").html(model[curLayer].move.length);
	$("#layRet").html(model[curLayer].retract.length);
	model[curLayer].print.forEach(function(val,indx,arr){layerExtr += val.de});
	$("#layExt").html(Math.round(layerExtr *10)/10);
}

function updateSegmentInfo(seg,ret,move)
{
	$("#segNum").html((seg + 1) + " of " + model[curLayer].print.length);
	$("#segLen").html((Math.round(model[curLayer].print[seg].len *10)/10) + "/" + model[curLayer].print[seg].f);
	$("#line").html(model.gcode[model[curLayer].print[seg].line]);
	$("#segExt").html((Math.round(model[curLayer].print[seg].de *100)/100) + "/" + (Math.round((model[curLayer].print[seg].de / model[curLayer].print[seg].len)*10000)/10000) );
	if(ret < 0){
		$("#segRet").html("NA");
	}else{
		$("#segRet").html( (model[curLayer].retract[ret].de) + "/" + (model[curLayer].retract[ret].f));
	}
	if(move < 0){
		$("#segMov").html("NA");
	}else{
		$("#segMov").html( (Math.round(model[curLayer].move[move].len *10)/10) + "/" + (model[curLayer].move[move].f));
	}
	if(options.showGcode){
		$("#gcodeFile").css("visibility","visible");
		var gline = model[curLayer].print[seg].line;
		$("#g"+pGline).css("color","gray");
		$("#g"+gline).css("color","red");
		pGline = gline;
		if(gline > 21)gline -= 20;
		document.getElementById("g"+gline).scrollIntoView();
	}else{
		$("#gcodeFile").css("visibility","hidden");
	}
}

var zoom = 5;
var offset = {x:300,y:300,px:300,py:300,msrX:300,msrY:300,centerX:0,centerY:0,pCenterX:0,pCenterY:0};
var pMouseX = 0;
var pMouseY = 0;
var pZoom = 0;
var curLayer = 1;
var retractCirRad = 5;
var options = {showMoves:0,showRetracts:0,showPrevLayers:0,showAllLayers:0,showGcode:0};
var laySelect = document.getElementById('laySel');
var colors = {print:"#ff0000",move:"#0000ff",retractIn: "#00ff00",retractOut:"#00ffff",text: "#000000"};
var drawTo = model[curLayer].printSegments;
var pGline = 1;
var gc = document.getElementById('gcodeCanvas');
var ctx = gc.getContext('2d');
gc.height = offset.y * 2;
gc.width = offset.x * 2;
if (gc.addEventListener) {
	gc.addEventListener("mousewheel", zoomer, false);
	gc.addEventListener("mousedown", startMover, false);
	gc.addEventListener("mouseup",stopMover, false);
	gc.addEventListener("mouseover", startKeys, false);
	gc.addEventListener("mouseout",stopMover, false);
	//gc.addEventListener("click",measurer, false);
}
offset.centerX = (offset.px - offset.x)/zoom;
offset.centerY = (offset.py - offset.y)/zoom;
laySelect.max = layCnt ;
laySelect.value = curLayer;
$("#layHigh").html(layers_z[curLayer - 1]);
showGcode();
showLayers();
$("#layerSlider").slider({
	orientation:'vertical',
	min:1,
	max:model.length - 1,
	step:1,
	value:1,
	slide:function(e,ui){changeLayer(ui.value);laySelect.value = ui.value}
});
$(".lSlider").css({"left":parseInt($("#gcodeCanvas").css("left"),10)+gc.width + 20,"height":gc.height});
$(".ui-slider").css({'height':gc.height});
$("#segmentSlider").slider({
	orientation:'horizontal',
	min:0,
	max:model[curLayer].printSegments ,
	step:1,
	value:model[curLayer].printSegments -1,
	slide:function(e,ui){drawTo = ui.value;clearLayer();showLayers()}
});
$(".sSlider").css({"width":gc.width ,"top":parseInt($("#gcodeCanvas").css("top"),10)+gc.height + 20});
$("#segmentSlider").css({'width':gc.width});
$(".info").css({"left":parseInt($(".lSlider").css("left"),10)+ 30});
$(".gcode").css({"left":parseInt($(".info").css("left"),10)+ parseInt($(".info").css("width"),10)+ 20});
$(".ui-widget-content").css("background","blue");
$(".ui-slider-handle.ui-state-default").css("background","green")
updateModelInfo();

</script>

</body>
</html>
