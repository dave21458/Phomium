<?php

function getGCode()
{
	if(!(array_key_exists('file',$_REQUEST)))$_REQUEST['file'] = 'C:\Users\Dave\Documents\Acad Drawings\daves robot\frame\case outer v2.gcode';
	if((array_key_exists('file',$_REQUEST))){
		$codes = ["x","y","z","e","m","g"];
		$gfs=fopen($_REQUEST['file'],"r");
		$cnt = 0;
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
		echo "var model = [];\n";
		echo "model[$layer] = {};\n";
		echo "model[$layer].printed = [];\n";
		echo "var layers_z = [];\n";
		while($fLine = fgets($gfs)){
			$fLine = strtolower(trim($fLine));
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
						$y = $val;
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
				echo "segmentCnt[$layer] = $cnt;\n";
				$layer++;
				$pLayer = $z;
				echo "model[$layer] = {};\n";
				echo "model[$layer].printed = []\n;";
				echo "layers_z.push($z);\n";
				$cnt = 0;
			}
			echo "model[$layer].printed[$cnt] = {line:$line,x:$x,y:$y,z:$z,e:$e,f:$feed,comm:'$comm',commVal:$commVal,printing:$print,de:$deltaE,len:$moveLen,gcode:'$fLine',px:$px,py:$py,pz:$pz,pe:$pe};\n";
			$cnt++;
			$line++;
		}
		echo "var layCnt = layers_z.length;\n;";
	}
}


?>

	
	