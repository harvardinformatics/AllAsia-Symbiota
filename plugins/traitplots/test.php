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

  private function convertToSvgCoords($submittedLat, $submittedLon){
    $tllat = 41.99846;
    $tllon = -124.40962;
    $tlycoord = -55123;
    $tlxcoord = -35597;

    $brlat = 32.53429;
    $brlon = -114.13182;
    $brycoord = 50237;
    $brxcoord = 56047;

    $xslope = ($tlxcoord - $brxcoord) / ($tllon - $brlon);
    $xintercept = $tlxcoord - ($xslope * $tllon);
    $yslope = ($tlycoord - $brycoord) / ($tllat - $brlat);
    $yintercept = $tlycoord - ($yslope * $tllat);

    $x = $xslope * $submittedLon + $xintercept;
    $y = $yslope * $submittedLat + $yintercept;

    return array("x" => strval($x), "y" => strval($y));
  }

  public function traitMapPoints(){
    return $this->getMapPoints();
  }

  private function getMapPoints(){
    $radius = "600";
		$colorTrue = "orange";
		$colorFalse = "gray";
		if(isset($this->sid)){
			$stateID = $this->sid;
		} else {
			$stateID = 0;
		}
		if($this->tid){
      $svgStr = '<g>';
			$sql = 'SELECT DISTINCT o.decimalLatitude, o.decimalLongitude, IF(t.stateid = ' .$stateID. ', "yes", "no") AS targettrait FROM omoccurrences AS o JOIN tmattributes AS t ON o.occid = t.occid WHERE o.tidinterpreted = ' . $this->tid . ' ORDER BY targettrait';
			$rs = $this->conn->query($sql);
			while($r = $rs->fetch_object()){
        if($r->targettrait == "yes") {
          $clr = $colorTrue;
        } else {
          $clr = $colorFalse;
        }
        $svgCoords = $this->convertToSvgCoords($r->decimalLatitude, $r->decimalLongitude);
        $svgStr .= '<circle cx="'.$svgCoords['x'].'" cy="'.$svgCoords['y'].'" r="' .$radius. '" stroke="black" stroke-width="3" fill="'.$clr.'" />';
			}
			$rs->free();
      $svgStr .= '</g>';
		}
    return $svgStr;
  }

	public function getPlot(){
	    return $this->getPlotData();
	}

	private function getPlotData(){
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

Header("Content-Type: text/html; charset=".$CHARSET);

$tid = array_key_exists("tid",$_REQUEST)?$_REQUEST["tid"]:"";
$sid = array_key_exists("sid",$_REQUEST)?$_REQUEST["sid"]:"";

if(!is_numeric($tid)) $tid = 0;
if(!is_numeric($sid)) $sid = 0;

$traitPlotter = new TraitPlotter();
if($tid) $traitPlotter->setTid($tid);
if($sid) $traitPlotter->setSid($sid);

$pointsStr = $traitPlotter->traitMapPoints();

?>
<html>
<head>
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
			<?php echo $traitPlotter->getPlot(); ?>
		</div>

		<div class="column">
</div>
</div>
</body>
</html>



<html>
<head>
  <link rel="stylesheet" type="text/css" href="/Users/cdt/Professional/Projects/CCH2/BioKIC/Symbiota-light/css/symb/taxa/traitplot.css">
  <script type="text/javascript">
  var mypoints = [ {x:120, y:121}, {x:143, y:143}, {x:196, y:140}, {x:209, y:96}, {x:182, y:58}, {x:131, y:80}, {x:119, y:117}, {x:120, y:120}, {x:120, y:120}, {x:120, y:120}, {x:120, y:120}, {x:120, y:120} ];

  function createSpline(p0, p1, p2, p3) {
    return {
      p0: p0,
      p1: p1,
      p2: p2,
      p3: p3
    };
  }

  function splinePoint(spline, tVal) {
    function ti1(pi, pi1, ti) {
      return Math.sqrt(((pi1.x - pi.x) ** 2 + (pi1.y - pi.y) ** 2)) ** 0.5 + ti;
    }
    let P0 = spline.p0;
    let P1 = spline.p1;
    let P2 = spline.p2;
    let P3 = spline.p3;
    let t0 = 0;
    let t1 = ti1(spline.p0, spline.p1, t0);
    let t2 = ti1(spline.p1, spline.p2, t1);
    let t3 = ti1(spline.p2, spline.p3, t2);
    let t = (t2 - t1) * tVal + t1;
    let A1 = {
      x: (t1 - t) / (t1 - t0) * P0.x + (t - t0) / (t1 - t0) * P1.x,
      y: (t1 - t) / (t1 - t0) * P0.y + (t - t0) / (t1 - t0) * P1.y
    }
    let A2 = {
      x: (t2 - t) / (t2 - t1) * P1.x + (t - t1) / (t2 - t1) * P2.x,
      y: (t2 - t) / (t2 - t1) * P1.y + (t - t1) / (t2 - t1) * P2.y
    }
    let A3 = {
      x: (t3 - t) / (t3 - t2) * P2.x + (t - t2) / (t3 - t2) * P3.x,
      y: (t3 - t) / (t3 - t2) * P2.y + (t - t2) / (t3 - t2) * P3.y
    }
    let B1 = {
      x: (t2 - t) / (t2 - t0) * A1.x + (t - t0) / (t2 - t0) * A2.x,
      y: (t2 - t) / (t2 - t0) * A1.y + (t - t0) / (t2 - t0) * A2.y
    };
    let B2 = {
      x: (t3 - t) / (t3 - t1) * A2.x + (t - t1) / (t3 - t1) * A3.x,
      y: (t3 - t) / (t3 - t1) * A2.y + (t - t1) / (t3 - t1) * A3.y
    };
    let C = {
      x: (t2 - t) / (t2 - t1) * B1.x + (t - t1) / (t2 - t1) * B2.x,
      y: (t2 - t) / (t2 - t1) * B1.y + (t - t1) / (t2 - t1) * B2.y
    };
    return C;
  }

  var mySpline1 = createSpline(mypoints[0], mypoints[1], mypoints[2], mypoints[3]);
  var mySpline2 = createSpline(mypoints[1], mypoints[2], mypoints[3], mypoints[4]);
  var mySpline3 = createSpline(mypoints[2], mypoints[3], mypoints[4], mypoints[5]);
  var mySpline4 = createSpline(mypoints[3], mypoints[4], mypoints[5], mypoints[6]);
  var mySpline5 = createSpline(mypoints[4], mypoints[5], mypoints[6], mypoints[7]);
  var mySpline6 = createSpline(mypoints[5], mypoints[6], mypoints[7], mypoints[8]);
  var mySpline7 = createSpline(mypoints[6], mypoints[7], mypoints[8], mypoints[9]);
  var mySpline8 = createSpline(mypoints[7], mypoints[8], mypoints[9], mypoints[10]);
  var mySpline9 = createSpline(mypoints[8], mypoints[9], mypoints[10], mypoints[11]);
  var mySpline10 = createSpline(mypoints[9], mypoints[10], mypoints[11], mypoints[0]);
  var mySpline11 = createSpline(mypoints[10], mypoints[11], mypoints[0], mypoints[1]);
  var mySpline12 = createSpline(mypoints[11], mypoints[0], mypoints[1], mypoints[2]);

  function drawSpline(spl, color, thickness, iter) {
    let tInc = 1 / iter;
    let t = tInc;
    firstPt = splinePoint(spl, 0);
    var svgd = "<path class='CalendarPlotFocalCurve' d='M" + firstPt.x + "," + firstPt.y + " L";
    for (let i = 0; i < iter; i++) {
      pt = splinePoint(spl, t);
      svgd += pt.x + "," + pt.y + " ";
      t += tInc;
    }
    return svgd + "' />";
  }

  function calPlotCoord(point, radius, center) {
    return {
      x: (point.x * radius) + center.x,
      y: (point.y * radius) + center.y
    }
  }
   var calPlotUnitPoints = [
    {x:0.259, y:0.966},
    {x:0.707, y:0.707},
    {x:0.966, y:0.259},
    {x:0.966, y:-0.259},
    {x:0.707, y:-0.707},
    {x:0.259, y:-0.966},
    {x:-0.259, y:-0.966},
    {x:-0.707, y:-0.707},
    {x:-0.966, y:-0.259},
    {x:-0.966, y:0.259},
    {x:-0.707, y:0.707},
    {x:-0.259, y:0.966} ];
  </script>
</head>
<body>
<table>
  <tr><td>j</td><td>0.259</td><td>0.966</td></tr>
  <tr><td>f</td><td>0.707</td><td>0.707</td></tr>
  <tr><td>m</td><td>0.966</td><td>0.259</td></tr>
  <tr><td>a</td><td>0.966</td><td>-0.259</td></tr>
  <tr><td>m</td><td>0.707</td><td>-0.707</td></tr>
  <tr><td>j</td><td>0.259</td><td>-0.966</td></tr>
  <tr><td>j</td><td>-0.259</td><td>-0.966</td></tr>
  <tr><td>a</td><td>-0.707</td><td>-0.707</td></tr>
  <tr><td>s</td><td>-0.966</td><td>-0.259</td></tr>
  <tr><td>o</td><td>-0.966</td><td>0.259</td></tr>
  <tr><td>n</td><td>-0.707</td><td>0.707</td></tr>
  <tr><td>d</td><td>-0.259</td><td>0.966</td></tr>
</table>
  <svg width="240" height="240" id="cdt">
    <line x1="94" y1="217" x2="146" y2="23" class="CalendarPlotSpokeLine" />
    <line x1="146" y1="217" x2="94" y2="23" class="CalendarPlotSpokeLine"/>
    <line x1="191" y1="191" x2="49" y2="49" class="CalendarPlotSpokeLine"/>
    <line x1="217" y1="146" x2="23" y2="94" class="CalendarPlotSpokeLine"/>
    <line x1="191" y1="49" x2="49" y2="191" class="CalendarPlotSpokeLine"/>
    <line x1="23" y1="146" x2="217" y2="94" class="CalendarPlotSpokeLine"/>
    <polyline points="146,23 191,49 217,94 217,146 191,191 146,217 94,217 49,191 23,146 23,94 49,49 94,23 146,23" class="CalendarPlotOuterWebLine" />
    <text class="CalendarPlotText" transform="translate(147,18) rotate(15)">Jan</text>
    <text class="CalendarPlotText" transform="translate(194,46) rotate(45)">Feb</text>
    <text class="CalendarPlotText" transform="translate(221,93) rotate(75)">Mar</text>
    <text class="CalendarPlotText" transform="translate(221,147) rotate(105)">Apr</text>
    <text class="CalendarPlotText" transform="translate(194,194) rotate(135)">May</text>
    <text class="CalendarPlotText" transform="translate(147,221) rotate(165)">Jun</text>
    <text class="CalendarPlotText" transform="translate(93,221) rotate(195)">Jul</text>
    <text class="CalendarPlotText" transform="translate(46,194) rotate(225)">Aug</text>
    <text class="CalendarPlotText" transform="translate(18,147) rotate(255)">Sep</text>
    <text class="CalendarPlotText" transform="translate(18,93) rotate(285)">Oct</text>
    <text class="CalendarPlotText" transform="translate(46,46) rotate(315)">Nov</text>
    <text class="CalendarPlotText" transform="translate(93,18) rotate(345)">Dec</text>

<script type="text/javascript">
  var res = 10;
    document.getElementById("cdt").innerHTML += drawSpline(mySpline1, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline2, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline3, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline4, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline5, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline6, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline7, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline8, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline9, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline10, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline11, res);
    document.getElementById("cdt").innerHTML += drawSpline(mySpline12, res);
  </script>

  </svg>
</body>
</html>
