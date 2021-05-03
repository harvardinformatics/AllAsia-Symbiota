<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/Manager.php');

class TraitPlotter extends Manager {

	private $tid;
  private $submittedArr = array();
  private $traitDataArr = array();


  public function __construct(){
		parent::__construct();
	}

	public function __destruct(){
		parent::__destruct();
	}

	public function setSid($sid){
		if(is_numeric($sid)){
			$this->sid = $sid;
			$this->setTraitState();
		}
	}

	public function setTid($tid){
		if(is_numeric($tid)){
			$this->tid = $tid;
			$this->setTaxon();
		}
	}

	private function setTaxon(){
		//need to roll up from child taxa!!
		if($this->tid){
			$sql = 'SELECT tid, sciname, author, rankid FROM taxa WHERE (tid = '.$this->tid.') ';
			$rs = $this->conn->query($sql);
			while($r = $rs->fetch_object()){
				$this->submittedArr['tid'] = $r->tid;
				$this->submittedArr['sciname'] = $r->sciname;
				$this->submittedArr['author'] = $r->author;
				$this->submittedArr['rankid'] = $r->rankid;
			}
			$rs->free();
		}
	}

	private function setTraitState(){
		if($this->sid){
			$sql = 'SELECT statename FROM tmstates WHERE (stateid = '.$this->sid.') ';
			$rs = $this->conn->query($sql);
			while($r = $rs->fetch_object()){
				$this->submittedArr['statename'] = $r->statename;
			}
			$rs->free();
		}
	}

	public function getCalendarPlot(){
	    return $this->CalendarPlotData();
	}

	private function CalendarPlotData(){
		$calPlotUnitPoints = array(
		 array('x' => 0.259, 'y' => 0.966),
		 array('x' => 0.707, 'y' => 0.707),
		 array('x' => 0.966, 'y' => 0.259),
		 array('x' => 0.966, 'y' => -0.259),
		 array('x' => 0.707, 'y' => -0.707),
		 array('x' => 0.259, 'y' => -0.966),
		 array('x' => -0.259, 'y' => -0.966),
		 array('x' => -0.707, 'y' => -0.707),
		 array('x' => -0.966, 'y' => -0.259),
		 array('x' => -0.966, 'y' => 0.259),
		 array('x' => -0.707, 'y' => 0.707),
		 array('x' => -0.259, 'y' => 0.966) );

		 // <svg width="240" height="240" id="cdt">
	   //   <line x1="94" y1="217" x2="146" y2="23" class="CalendarPlotSpokeLine" />
	   //   <line x1="146" y1="217" x2="94" y2="23" class="CalendarPlotSpokeLine"/>
	   //   <line x1="191" y1="191" x2="49" y2="49" class="CalendarPlotSpokeLine"/>
	   //   <line x1="217" y1="146" x2="23" y2="94" class="CalendarPlotSpokeLine"/>
	   //   <line x1="191" y1="49" x2="49" y2="191" class="CalendarPlotSpokeLine"/>
	   //   <line x1="23" y1="146" x2="217" y2="94" class="CalendarPlotSpokeLine"/>
	   //   <polyline points="146,23 191,49 217,94 217,146 191,191 146,217 94,217 49,191 23,146 23,94 49,49 94,23 146,23" class="CalendarPlotOuterWebLine" />
	   //   <text class="CalendarPlotText" transform="translate(147,18) rotate(15)">Jan</text>
	   //   <text class="CalendarPlotText" transform="translate(194,46) rotate(45)">Feb</text>
	   //   <text class="CalendarPlotText" transform="translate(221,93) rotate(75)">Mar</text>
	   //   <text class="CalendarPlotText" transform="translate(221,147) rotate(105)">Apr</text>
	   //   <text class="CalendarPlotText" transform="translate(194,194) rotate(135)">May</text>
	   //   <text class="CalendarPlotText" transform="translate(147,221) rotate(165)">Jun</text>
	   //   <text class="CalendarPlotText" transform="translate(93,221) rotate(195)">Jul</text>
	   //   <text class="CalendarPlotText" transform="translate(46,194) rotate(225)">Aug</text>
	   //   <text class="CalendarPlotText" transform="translate(18,147) rotate(255)">Sep</text>
	   //   <text class="CalendarPlotText" transform="translate(18,93) rotate(285)">Oct</text>
	   //   <text class="CalendarPlotText" transform="translate(46,46) rotate(315)">Nov</text>
	   //   <text class="CalendarPlotText" transform="translate(93,18) rotate(345)">Dec</text>

		if($this->tid){
			 $sql = 'SELECT o.month, COUNT(*) AS count FROM tmattributes AS a LEFT JOIN omoccurrences AS o ON o.occid = a.occid WHERE o.tidinterpreted = ' . $this->tid . ' AND a.stateid = ' . $this->sid . ' GROUP BY o.month';
			 $rs = $this->conn->query($sql);
			 $countArr = array_fill(0,12,0);
			 while($r = $rs->fetch_object()){
				 if($r->month > 0 && $r->month < 13) {
					 $countArr[$r->month-1] = (int)$r->count;
				 }
			 }
			 $rs->free();
		}
		$cppts = array();
		for($i = 0; $i < 12; $i++) {
			$cppts[] = $this->calPlotCoord($calPlotUnitPoints[$i], $countArr[$i], array('x'=>150,'y'=>150));
		}
		return $cppts;
	}

	public function CatmullRomSpline($GivenPoint0, $GivenPoint1, $GivenPoint2, $GivenPoint3) {
		return array("p0" => $GivenPoint0, "p1" => $GivenPoint1, "p2" => $GivenPoint2, "p3" => $GivenPoint3);
	}

	private function ti1($pt0, $pt1, $ti, $alpha=0.5) {
		# alpha = 0 ~ standard (uniform)
		# alpha = 0.5 ~ centripetal
		# alpha = 1 ~ chordal
		return sqrt((($pt1['x'] - $pt0['x']) ** 2 + ($pt1['y'] - $pt0['y']) ** 2)) ** $alpha + $ti;
	}

	private function BarryGoldmanSplinePoint($spline, $tVal) {
		$P0 = $spline['p0'];
		$P1 = $spline['p1'];
		$P2 = $spline['p2'];
		$P3 = $spline['p3'];
		$t0 = 0;
		$t1 = $this->ti1($P0, $P1, $t0);
		$t2 = $this->ti1($P1, $P2, $t1);
		$t3 = $this->ti1($P2, $P3, $t2);
		$t = ($t2 - $t1) * $tVal + $t1;
		$nancheck = (($t0 == $t1) + ($t0 == $t2) + ($t0 == $t3) + ($t1 == $t2) + ($t1 == $t3) + ($t2 == $t3));
		if($nancheck > 0) {
			return array('x' => ($P1['x'] + $P2['x'])/2, 'y' => ($P1['x'] + $P2['x'])/2);
			# return the average of the center control points if the Barry Goldman algorithm divides by zero.
		} else {
			$A1 = array(
				'x' => ($t1 - $t) / ($t1 - $t0) * $P0['x'] + ($t - $t0) / ($t1 - $t0) * $P1['x'],
				'y' => ($t1 - $t) / ($t1 - $t0) * $P0['y'] + ($t - $t0) / ($t1 - $t0) * $P1['y']
			);
			$A2 = array(
				'x' => ($t2 - $t) / ($t2 - $t1) * $P1['x'] + ($t - $t1) / ($t2 - $t1) * $P2['x'],
				'y' => ($t2 - $t) / ($t2 - $t1) * $P1['y'] + ($t - $t1) / ($t2 - $t1) * $P2['y']
			);
			$A3 = array(
				'x' => ($t3 - $t) / ($t3 - $t2) * $P2['x'] + ($t - $t2) / ($t3 - $t2) * $P3['x'],
				'y' => ($t3 - $t) / ($t3 - $t2) * $P2['y'] + ($t - $t2) / ($t3 - $t2) * $P3['y']
			);
			$B1 = array(
				'x' => ($t2 - $t) / ($t2 - $t0) * $A1['x'] + ($t - $t0) / ($t2 - $t0) * $A2['x'],
				'y' => ($t2 - $t) / ($t2 - $t0) * $A1['y'] + ($t - $t0) / ($t2 - $t0) * $A2['y']
			);
			$B2 = array(
				'x' => ($t3 - $t) / ($t3 - $t1) * $A2['x'] + ($t - $t1) / ($t3 - $t1) * $A3['x'],
				'y' => ($t3 - $t) / ($t3 - $t1) * $A2['y'] + ($t - $t1) / ($t3 - $t1) * $A3['y']
			);
			$C = array(
				'x' => round(($t2 - $t) / ($t2 - $t1) * $B1['x'] + ($t - $t1) / ($t2 - $t1) * $B2['x'], 1),
				'y' => round(($t2 - $t) / ($t2 - $t1) * $B1['y'] + ($t - $t1) / ($t2 - $t1) * $B2['y'], 1)
			);
		}
		return $C;
	}

	public function drawSpline($spline, $iter) {
		$tInc = 1 / $iter;
		$t = $tInc;
		$firstPt = $this->BarryGoldmanSplinePoint($spline, 0);
		$svgd = "<path class='CalendarPlotFocalCurve' d='M" . $firstPt['x'] . "," . $firstPt['y'] . " L";
		for ($i = 0; $i < $iter; $i++) {
			$pt = $this->BarryGoldmanSplinePoint($spline, $t);
			$svgd .= $pt['x'] . "," . $pt['y'] . " ";
			$t += $tInc;
		}
		return $svgd . "' />";
	}

	private function calPlotCoord($point, $radius, $offset = array('x'=>0,'y'=>0)) {
		return array(
			'x' => ($point['x'] * $radius) + $offset['x'],
			'y' => ($point['y'] * $radius) + $offset['y'] );
	}

	private function BarplotData(){
		$stateID = $this->sid;
		$colorTrue = "orange";
		$svgStr = '<svg viewBox="-10 0 130 110" width="400" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><rect width="10" height="10" fill="lightgray"/><path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="1"/></pattern></defs><rect x="0" y="0" width="120" height="100" fill="url(#grid)" /><text x="0" y="12" text-anchor="end" font-size="4px" fill="black">90%</text><text x="0" y="22" text-anchor="end" font-size="4px" fill="black">80%</text><text x="0" y="32" text-anchor="end" font-size="4px" fill="black">70%</text><text x="0" y="42" text-anchor="end" font-size="4px" fill="black">60%</text><text x="0" y="52" text-anchor="end" font-size="4px" fill="black">50%</text><text x="0" y="62" text-anchor="end" font-size="4px" fill="black">40%</text><text x="0" y="72" text-anchor="end" font-size="4px" fill="black">30%</text><text x="0" y="82" text-anchor="end" font-size="4px" fill="black">20%</text><text x="0" y="92" text-anchor="end" font-size="4px" fill="black">10%</text><text x="5" y="105" text-anchor="middle" font-size="4px" fill="black">Jan</text><text x="15" y="105" text-anchor="middle" font-size="4px" fill="black">Feb</text><text x="25" y="105" text-anchor="middle" font-size="4px" fill="black">Mar</text><text x="35" y="105" text-anchor="middle" font-size="4px" fill="black">Apr</text><text x="45" y="105" text-anchor="middle" font-size="4px" fill="black">May</text><text x="55" y="105" text-anchor="middle" font-size="4px" fill="black">Jun</text><text x="65" y="105" text-anchor="middle" font-size="4px" fill="black">Jul</text><text x="75" y="105" text-anchor="middle" font-size="4px" fill="black">Aug</text><text x="85" y="105" text-anchor="middle" font-size="4px" fill="black">Sep</text><text x="95" y="105" text-anchor="middle" font-size="4px" fill="black">Oct</text><text x="105" y="105" text-anchor="middle" font-size="4px" fill="black">Nov</text><text x="115" y="105" text-anchor="middle" font-size="4px" fill="black">Dec</text>';

		if($this->tid){
			$sql = 'SELECT o.month, COUNT(*) AS count FROM tmattributes AS a LEFT JOIN omoccurrences AS o ON o.occid = a.occid WHERE o.tidinterpreted = ' . $this->tid . ' AND a.stateid = ' . $stateID . ' GROUP BY o.month';
			$rs = $this->conn->query($sql);
			$countArr = array_fill(1,12,0);
			while($r = $rs->fetch_object()){
				if($r->month > 0 && $r->month < 13) {
				 	$countArr[$r->month] = (int)$r->count;
				}
			}
			$traitSum = array_sum($countArr);
			$rs->data_seek(0);
			while($r = $rs->fetch_object()){
				$traitPercent = ($r->count/$traitSum) * 100;
				$xstart = $r->month * 10 - 9;
				$ystart = 100 - $traitPercent;
				$svgStr .= '<rect x=' . $xstart . ' y=' . $ystart . ' height=' . $traitPercent . ' width=9 stroke="none" fill="orange" />';
			}
			$rs->free();
			$svgStr .= '</svg>';
		}
		return $svgStr;
	}

  public function getSciname(){
    if(isset($this->submittedArr['sciname'])){
      $retStr = $this->submittedArr['sciname'];
    } else {
      $retStr = "No scientific name available";
    }
    return $retStr;
  }

	public function getStateName(){
		if(isset($this->submittedArr['statename'])){
			$retStr = $this->submittedArr['statename'];
		} else {
			$retStr = "Trait State Unknown";
		}
		return $retStr;
	}
}

### End of class ############

//$mypoints = array( array('x'=>120, 'y'=>121), array('x'=>143, 'y'=>143), array('x'=>196, 'y'=>140), array('x'=>209, 'y'=>96), array('x'=>182, 'y'=>58), array('x'=>131, 'y'=>80), array('x'=>119, 'y'=>117), array('x'=>120, 'y'=>120), array('x'=>120, 'y'=>120), array('x'=>120, 'y'=>120), array('x'=>120, 'y'=>120), array('x'=>120, 'y'=>120) );

Header("Content-Type: text/html; charset=".$CHARSET);

$tid = array_key_exists("tid",$_REQUEST)?$_REQUEST["tid"]:"";
$sid = array_key_exists("sid",$_REQUEST)?$_REQUEST["sid"]:"";

if(!is_numeric($tid)) $tid = 0;
if(!is_numeric($sid)) $sid = 0;

$traitPlotter = new TraitPlotter();
if($tid) $traitPlotter->setTid($tid);
if($sid) $traitPlotter->setSid($sid);

$mypoints = $traitPlotter->getCalendarPlot();
$mySpline = array();
for($i = 0; $i < count($mypoints); $i++) {
	$ptidx = array();
	for($j = 0; $j < 4; $j++){
		$ptidx[] = ($i + $j) % count($mypoints);
	}
	$mySpline[] = $traitPlotter->CatmullRomSpline($mypoints[$ptidx[0]], $mypoints[$ptidx[1]], $mypoints[$ptidx[2]], $mypoints[$ptidx[3]]);
}
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="http://localhost:8080/BioKIC/css/symb/taxa/traitplot.css">
	<style>
	.column {
  float: left;
  width: 50%;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}
	</style>
</head>
<body>
	<div class="row">
		<div class="column">
			<h2>
				<?php echo $traitPlotter->getSciname(); ?>
			</h2><h3>
				<?php echo $traitPlotter->getStateName(); ?>
			</h3>
			<?php
				echo '<svg>';
				foreach ($mySpline as $k => $v) {
					echo $traitPlotter->drawSpline($v, 10);
				}
				echo '</svg>';
			?>
		</div>

		<div class="column">
</div>
</div>
</body>
</html>
