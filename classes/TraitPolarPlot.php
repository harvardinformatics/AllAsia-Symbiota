<?php

/*
 * A class for multivariate polar coordinate plots
 *  (a.k.a. radar charts or spider/star plots).
 *
 * Default plot size is 400 x 400 px, but svg is scalable to any size by setting
 * viewport (width, height attributes) to desired sizes and viewbox to 0 0 400
 * 400. Line widths are controlled using css classes (e.g., PolarPlotAxisLine).
*/

class PolarPlot {

  private $PlotClass;
  private $PlotId;
  private $PlotWidth = 400;
  private $PlotHeight = 400;
  private $PlotCenter;
  private $PlotPadding = 4;       // gap between axis and label in pixels
  private $PlotMargin = 16;       // space for label text in pixels (browser font default = 16px)
  private $AxisNumber = 5;        // number of plot axes
  private $AxisRotation = 0;      // degrees clockwise from top dead center
  private $AxisLength;
  private $AxisLabels = array();
  private $TickNumber = 2;
  private $DataValues = array();
  private $RadInterval;
  private $RadTopPosition;
  public $PlotSVG;



  public function __construct($className = 'PolarPlot', $id = ''){
    $this->PlotClass = $className;
    $this->PlotId = $id;
    $this->PlotCenter = array('x' => $this->PlotWidth / 2, 'y' => $this->PlotHeight / 2);
    $this->AxisLength = min($this->PlotWidth, $this->PlotHeight)/2 - $this->PlotPadding - $this->PlotMargin;
    $this->setRadInterval();
    $this->setRadTopPosition();
	}

	public function __destruct(){
	}

  public function setAxisNumber($n) {
    $this->AxisNumber = $n;
    $this->setRadInterval();
  }

  public function setAxisRotation($r) {
    $this->AxisRotation = $r;
    $this->setRadTopPosition();
  }

  public function setTickNumber($n) {
    $this->TickNumber = $n;
  }

  public function setAxisLabels($l) {
    $this->AxisLabels = $l;
  }

  public function setPlotMargin($m) {
    $this->PlotMargin = $m;
  }

  public function setDataValues($d) {
    $this->DataValues = $d;
  }

  public function getViewboxWidth() {
    return $this->PlotWidth;
  }

  public function getViewboxHeight() {
    return $this->PlotHeight;
  }

  public function getPlotSVG() {
    $mySpline = array();
    for($i = 0; $i < count($this->DataValues); $i++) {
    	$ptidx = array();
    	for($j = 0; $j < 4; $j++){
    		$ptidx[] = ($i + $j) % count($this->DataValues);
    	}
    	$mySpline[] = $this->CatmullRomSpline($this->DataValues[$ptidx[0]], $this->DataValues[$ptidx[1]], $this->DataValues[$ptidx[2]], $this->DataValues[$ptidx[3]]);
    }
    $splineSVG = '';
    foreach ($mySpline as $k => $v) {

      // $cppts = array();
  		// for($i = 0; $i < 12; $i++) {
  		// 	$cppts[] = $this->calPlotCoord($calPlotUnitPoints[$i], $countArr[$i], array('x'=>150,'y'=>150));
  		// }
  		// return $cppts;

      $splineSVG .= $this->drawSpline($v, 10);
    }
    return $this->axisSVG() . ' ' . $this->tickSVG() . ' ' . $this->scaleSVG() . ' ' . $this->axisLabelSVG() . ' ' . $splineSVG;
  }

  private function setRadInterval() {
    $this->RadInterval = (2 * pi()) / $this->AxisNumber;
  }

  private function setRadTopPosition() {
    $this->RadTopPosition = (pi() / 2) - deg2rad($this->AxisRotation);
  }

  private function axisSVG() {
    $svgStr = '';
    $radPos = $this->RadTopPosition;
    for($i = 0; $i < $this->AxisNumber; $i++) {
      $x2 = round($this->PlotCenter['x'] + $this->AxisLength * cos($radPos), 0);
      $y2 = round($this->PlotCenter['y'] - $this->AxisLength * sin($radPos), 0);
      $svgStr .= '<line x1="' . $this->PlotCenter['x'] . '" y1="' . $this->PlotCenter['y'] . '" x2="' . $x2 . '" y2="' . $y2 . '" class="' . $this->PlotClass . 'AxisLine" />';
      $radPos -= $this->RadInterval;
    }
    return $svgStr;
  }

  private function tickSVG() {
    $svgStr = '';
    if(isset($this->TickNumber) && $this->TickNumber) {
      $radPos = $this->RadTopPosition;
      $tickInterval = $this->AxisLength/$this->TickNumber;
      for($j = 1; $j <= $this->TickNumber; $j++){
        $tickRadius = round($tickInterval * $j, 1);
        $svgStr .= '<polyline class="' . $this->PlotClass . 'TickLine" points="';
        for($i = 0; $i <= $this->AxisNumber; $i++) {
          $x2 = round($this->PlotCenter['x'] + $tickRadius * cos($radPos), 0);
          $y2 = round($this->PlotCenter['y'] - $tickRadius * sin($radPos), 0);
          $svgStr .= $x2 . ',' . $y2 . ' ';
          $radPos -= $this->RadInterval;
        }
        $svgStr .= '" />';
      }
    }
    return $svgStr;
  }

  private function scaleSVG() {
    $svgStr = '';
    if(empty($this->DataValues)) {
      return $svgStr;
    }
    if(isset($this->TickNumber) && $this->TickNumber) {
      $s1 = max($this->DataValues) / $this->TickNumber;
      $s2 = 10 ** (round(log10($s1), 0) - 1);
      $s3 = ceil($s1 / $s2) * $s2;
      $tickInterval = $this->AxisLength/$this->TickNumber;
      for($j = 1; $j <= $this->TickNumber; $j++){
        $tickRadius = $this->PlotCenter['y'] - round($tickInterval * $j, 1);
        $svgStr .= '<text transform="translate(' . $this->PlotCenter['x'] . ',' . $tickRadius . ')" class="' . $this->PlotClass . 'ScaleText">' . $s3 * $j . '</text>';
      }
    }
    return $svgStr;
  }

  private function axisLabelSVG() {
    $svgStr = '';
    $radPos = $this->RadTopPosition;
    $degRotation = $this->AxisRotation;
    for($i = 0; $i < $this->AxisNumber; $i++) {
      if(isset($this->AxisLabels[$i])) { $label = $this->AxisLabels[$i]; } else { $label = $i; }
      $x2 = round($this->PlotCenter['x'] + ($this->AxisLength + $this->PlotPadding) * cos($radPos), 0);
      $y2 = round($this->PlotCenter['y'] - ($this->AxisLength + $this->PlotPadding) * sin($radPos), 0);
      $svgStr .= '<text transform="translate(' . $x2 . ',' . $y2 . ') rotate(' . $degRotation . ')" class="' . $this->PlotClass . 'LabelText">' . $label . '</text>';
      $radPos -= $this->RadInterval;
      $degRotation += rad2deg($this->RadInterval);
    }
    return $svgStr;
  }

  private function CatmullRomSpline($GivenPoint0, $GivenPoint1, $GivenPoint2, $GivenPoint3) {
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
    var_dump($spline);
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
    $svgd = "<path class='" . $this->PlotClass . "FocalCurve' d='M" . $firstPt['x'] . "," . $firstPt['y'] . " L";
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

}
?>
