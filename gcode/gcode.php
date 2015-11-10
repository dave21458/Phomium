<?php
if(array_key_exists('file',$_REQUEST))getGcode();

function getGCode()
{
	if(!(array_key_exists('file',$_REQUEST)))$_REQUEST['file'] = 'title.gcode';
	if(file_exists($_REQUEST['file'])){
		$codes = ["x","y","z","e","m","g","f"];
		$gfs=fopen($_REQUEST['file'],"r");
		$tmp = fopen('model.js',"w+");
		fwrite($tmp,"var startTime = ".microtime(true). ";\n");
		$moveCnt = 0;
		$printCnt = 0;
		$retractCnt = 0;
		$totMove = 0;
		$totRetract = 0;
		$totSegments = 0;
		$totMoveLen = 0;
		$totPrintLen = 0;
		$totExtr = 0;
		$x = 0;
		$y = 0;
		$z = 0;
		$e = 0;
		$px = 0;
		$py = 0;
		$pz = 0;
		$pe = 0;
		$feed = 0;
		$comm = "M";
		$commVal = 0;
		$line = 1;
		$print = 0;
		$moveLen = 0;
		$fLine = '';
		$deltaE = 0;
		$layer = 0;
		$pLayer = -1;
		$str = '';
		$str .= "var model = [];\n";
		$str .= "model.name = '" . str_replace("\\","/",$_REQUEST['file']). "';\n";
		$str .="model[$layer] = [];\n";
		$str .="model[$layer].print = [];\n";
		$str .="model[$layer].move = [];\n";
		$str .="model[$layer].retract = [];\n";
		$str .="model[$layer].command = [];\n";
		$str .="model['gcode'] = [];\n";
		$str .= "var layers_z = [];\n";
		$str .= "var segmentCnt = [];\n";
		fwrite($tmp,$str);
		$str = "";
		while($fLine = fgets($gfs)){
			$fLine = trim($fLine);
			fwrite($tmp,"model['gcode'][$line]='$fLine';\n");
			$fLine = strtolower($fLine);
			if(substr($fLine,0,1) == ';' || strlen($fLine) < 2){
				$line++;
				continue;
			}
			
			if(strpos($fLine,";") !== false) $fLine = substr($fLine,0,strpos($fLine,";"));
			$lines = explode(' ',$fLine );
			$px = $x;
			$py = $y;
			$pz = $z;
			$pe = $e;  
			foreach($lines as $a){
				if(array_search(substr($a,0,1),$codes) === false)continue;
				$val = substr($a,1,strlen($a));
				switch(substr($a,0,1)){
					case 'g':
						$comm = 'G';
						$commVal = $val;
						break;
					case 'x':
						$x = $val;
						break;
					case 'y':
						$y = 0-$val;
						break;
					case 'z':
						$z = $val;
						break;
					case 'e':
						$e = $val;
						break;
					case 'f':
						$feed = $val;
						break;
					case 'm':
						$comm = 'M';
						$commVal = $val;
						break;
					default:
						break;
				}
			}
			if($comm == 'G' && $commVal == 92)$pe = $deltaE;
			$deltaX=$x - $px;
			$deltaY=$y - $py;
			$deltaZ=$z - $pz;
			$deltaE=$e - $pe;
			$deltaE && ($deltaX || $deltaY) ? $print = 1:$print = 0;
			if($deltaX > 0 && $deltaY > 0){
				$moveLen = abs(sqrt(pow($deltaX,2)+pow($deltaY,2)));
			}else{
				$moveLen = abs($deltaX) + abs($deltaY);
			}
			if($deltaE > 0 && $moveLen != 0 && $pLayer != $z){
				$str .="model[$layer].printSegments = $printCnt;\n";
				$str .="model[$layer].moveSegments = $moveCnt;\n";
				$layer++;
				$pLayer = $z;
				$str .="model[$layer] = {};\n";
				$str .="model[$layer].print = [];\n";
				$str .="model[$layer].move = [];\n";
				$str .="model[$layer].retract = [];\n";
				$str .="model[$layer].command = [];\n";
				$str .= "layers_z.push($z);\n";
				$totMove += $moveCnt;
				$totRetract += $retractCnt;
				$totSegments += $printCnt;
				$printCnt = 0;
				$moveCnt = 0;
				$retractCnt = 0;
			}
			if($print){
				$str .= "model[$layer].print[$printCnt] = {line:$line,x:$x,y:$y,f:$feed,de:$deltaE,len:$moveLen,px:$px,py:$py};\n";
				$totPrintLen += $moveLen;
				$totExtr += $deltaE;
				//$str .= "model[$layer].print[$printCnt] = {line:$line,x:$x,y:$y,z:$z,e:$e,f:$feed,comm:'$comm',commVal:$commVal,printing:$print,de:$deltaE,len:$moveLen,gcode:'$fLine',px:$px,py:$py,pz:$pz,pe:$pe};\n";
				$printCnt++;
			}
			if($moveLen != 0 && $print == 0){
				$str .= "model[$layer].move[$moveCnt] = {line:$line,x:$x,y:$y,f:$feed,len:$moveLen,px:$px,py:$py};\n";
				$totMoveLen += $moveLen;
				$moveCnt++;
			}
			if($comm == "G" && $moveLen == 0 && $deltaE != 0){
				$str .= "model[$layer].retract[$retractCnt] = {line:$line,x:$x,y:$y,f:$feed,de:$deltaE};\n";
				$retractCnt++;
			}
			$line++;
			fwrite($tmp,$str);
			$str = "";
		}
		$totMove += $moveCnt;
		$totRetract += $retractCnt;
		$totSegments += $printCnt;
		$str = "var layCnt = layers_z.length;\n";
		$str .= "var totalRetracts = $totRetract;\n";
		$str .= "var totalMoves = $totMove;\n";
		$str .= "var totalSegments = $totSegments;\n";
		$str .= "var totalPrintLen = $totPrintLen;\n";
		$str .= "var totalExtrudeLen = $totExtr;\n";
		$str .= "var totalMoveLen = $totMoveLen;\n";
		$str .="model[$layer].printSegments = $printCnt;\n";
		$str .="model[$layer].moveSegments = $moveCnt;\n";
		fwrite($tmp, $str);
		fwrite($tmp,"var endTime = ".microtime(true) . ";\n");
		echo "<meta &&&model.js&&&/>";
		fclose($tmp);
	}	
	
}

?>

	
	