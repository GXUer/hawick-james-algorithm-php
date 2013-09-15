<?php

/*
PHP implementation of Hawick-James's algorithm, an evolution of Johnson's algorithm, for searching and listing all the elementary circuits of a directed graph.

v1.0
*/

//number of vertices
$nVertices;

//start vertex
$start;

//representation of the graph
$Ak;

//list of the blocked vertices
$B;

//boolean control for the blocked vertices
$blocked;

//number of cicruits
$nCircuits = 0;

//lenght of the circuits
$lengthHistogram = array();

//occurrence of a vertex in every circuit
$vertexPopularity = array();

//longest circuit found in the graph
$longestCircuit = array();

//maximum length of a circuit in the graph
$longestLength = 0;

//flag for an explicit enumeration of circuits.
$enumeration = true;

//a stack for collecting vertices of a circuit
$stack = array();

//a reference to the top of the stack
$stackTop;

//the complete list of all circuits found
$cyclesList = array();

/* -------------------------------------- */

//returns the number of all arcs in a graph
function countArcs(){
	global $Ak;
	$nArcs = 0;
	for($i=0;$i<$nArcs;$i++){
		$nArcs = $nArcs + count($Ak[$i]);
	}
	return $nArcs;
}


function newList(){
	$retval = array();
	return $retval;
}

function emptyList(){
	return newList();
}


function addToList($list,$val) {
	$temp = $list;
	$temp[] = $val;
	return $temp;
}


function removeFromList($list,$val){
	$temp = $list;
	$occ = array();
	for($i=0;$i<count($temp);$i++){
		if($temp[$i] == $val)
			$occ[] = $i;
	}
	for($j=0;$j<count($temp)-1;$j++){
		if(in_array($j,$occ))
			unset($temp[$j]);
	}
	$temp = array_values($temp);	
	return $temp;
}


function stackInit($max){
	global $stack, $stackTop;

	$stackTop = 0;
	$stack = array();	
}


function stackPush($val) {
	global $stack,$stackTop, $start;
	$temp = array();
	for($i=0;$i<$stackTop;$i++) {
		$temp[] = $stack[$i];
	}
	if(!in_array($val,$temp)){
		$stack[$stackTop] = $val;
		$stackTop++;
	}
	if($val==$start && $stackTop>1){
		$stack[$stackTop] = $val;
		$stackTop++;
	}

}

function stackSize() {
	global $stackTop;
	return $stackTop;
}


function stackPop() {
	global $stackTop, $stack;
	if($stackTop>0){
		$temp = $stack[$stackTop-1];
		$stackTop--;
	}
	for($i=$stackTop; $i<count($stack); $i++)
		unset($stack[$i]);
	$stack = array_values($stack);
}


function stackClear() {
	global $stackTop, $stack;
	$stackTop = 0;
	$stack = array();
}


function printStack(){
	global $stack, $stackTop, $start, $cyclesList;

	/*print_r("<b>The circuit found:");
	for($i=0;$i<$stackTop;$i++){
		print_r("  $stack[$i]  -");
	}*/
	$stack[$stackTop] = $start;
	$cyclesList[] = $stack;
	//print_r("  $stack[$i].");
	//print_r("</b><br>");
}


function unblock($u) {
	global $B, $blocked, $stack;

	$blocked[$u] = false;	
	for($wPos=0;$wPos<count($B[$u]);$wPos++){
		$w = $B[$u][$wPos];	
		$B[$u] = removeFromList($B[$u],$w);
		if($blocked[$w])
			unblock($w);
	}
}


function circuit($v){	
	global $Ak, $B, $blocked, $nVertices, $start, $stack;
	$found=false;

	stackPush($v);	
	$blocked[$v] = true;

	$lg = count($Ak[$v]);

	for($wPos=0; $wPos<count($Ak[$v]); $wPos++){
		$w = $Ak[$v][$wPos];
		if($w>$start && !$blocked[$w]){
			if(circuit($w))
				$found=true;
		}
		else if($w==$start) {

				printStack();
				$found=true;

				//analytics...
				if($stackTop<=$nVertices){
					$lengthHistogram[$stackTop] +=1;
					$nCircuits++;
					
					if($stackTop>$lenLongest){
						$lenLongest = $stackTop;
						$longestCircuit = $stack;
					}
					for($i=0; $i<$stackTop;$i++){
						$index=$stack[$i];
						$vertexPopularity[$stackTop][$index] +=1:
					}				
				} // ...analytics
		} 
	}
		
	if($found)
		unblock($v);
	else {
		for($wPos=0; $wPos<count($Ak[$v]); $wPos++) {
			$w=$Ak[$v][$wPos];
			if(!($w<$start)){
				if(!in_array($v,$B[$w])){
						$B[$w] = addToList($B[$w],$v);
					}
			}
		}
	}	
	stackPop();
	return $found;
}

function setupGlobals($nVert,$ak){
	$nVertices = $nVert;
	global $Ak, $B, $blocked;
	$B = array();
	$blocked = array();
	
	for($i=0;$i<$nVert;$i++){
		$B[] = emptyList();		
		$blocked[] = false;
	}
	$Ak = $ak;
	stackInit($nVert);	

}

function launchHawickJames($ak,$nv){
	global $Ak, $B, $blocked, $nVertices, $start, $cyclesList, $enumeration;
	$nVertices = $nv;
	setupGlobals($nVertices,$ak);
	stackClear();
	$start=0;

	while($start<$nVertices){
		stackClear();
		if($enumeration)
			for($i=0;$i<$nVertices;$i++){
				$blocked[$i] = false;
				$B[$i] = emptyList();
		}
		circuit($start);
		$start++;
	}
	return $cyclesList;
}

?>
