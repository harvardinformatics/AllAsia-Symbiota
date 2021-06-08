<?php

/*
 * A class for bi- or uni-variate bar plots.
 *
 *  Christopher D. Tyrrell, 2021
 *
 * This class is for the plot view and should be used in conjunction with the
 * TraitPlotter.php controller class.
 *
 * Default plot size is 400 x 400 px, but svg is scalable to any size by setting
 * viewport (width, height attributes) to desired sizes, and setting the viewbox
 * to match the specified plot size (e.g., 0 0 400 400). Line widths are
 * controlled using css classes (e.g., BarPlotAxisLine).
*/

class BarPlot {

  private $PlotClass;
  private $PlotId;
  private $PlotWidth = 400;
  private $PlotHeight = 400;
  private $PlotOrigin;
  private $PlotPadding = 4;       // the distance between the axis and its label, in pixels
  private $PlotMargin = 16;       // space for label text, in pixels (browser font default = 16px)
  private $AxisRotation = 0;      // 0 = vertical bars, 1 = horizontal bars
  private $AxisLength;
  private $AxisLabels = array();
  private $TickNumber = 2;        // 1 = the outer edge, >1 = outer + inner, 0 = spokes only
  private $TickScale;
  private $DataValues = array();  // Future: consider making this into a 2D array holding data series
  public $ShowScale = 1;          // 1 = scale values shown, 0 = scale values hidden


  ## METHODS ##

  public function __construct($className = 'BarPlot', $id = ''){
    $this->PlotClass = $className;
    $this->PlotId = $id;
    $this->resetPlotDimensionValues();
	}

	public function __destruct(){
	}


  ### Public Methods ###

  public function setAxisRotation($r) {
    if(is_numeric($r)) {
      $this->AxisRotation = $r;
    }
  }

  public function setTickNumber($n) {
    // max out ticks at the number of pixels in the axis since they're not visible anyway.
    if(is_numeric($n)) {
      if($n > $this->AxisLength) { $n = $this->AxisLength; }
      $this->TickNumber = $n;
    }
  }

  public function setAxisLabels($l) {
    if(is_array($l)) {
      // if(count($l) != $this->AxisNumber) {
      //   trigger_error("number of labels does not match axis number;", E_USER_WARNING);
      // }
      $this->AxisLabels = $l;
    }
  }

  public function setPlotMargin($m) {
    if(is_numeric($m)) {
      $this->PlotMargin = $m;
    }
  }

  public function setDataValues($d) { //check for numeric
    // if(is_array($d) && (count($d) == $this->AxisNumber)) {
    //   $this->DataValues = $d;
    //   $this->setTickScale();
    //   $this->setSplines();
    //   return 1;
    // } else {
    //   return 0;
    // }
  }

  public function setPlotDimensions($h, $w = -1) {
    if($w < 0) { $w = $h; }
    if(is_numeric($w) && is_numeric($h)) {
      $this->PlotHeight = $h;
      $this->PlotWidth = $w;
      $this->resetPlotDimensionValues();
    }
  }

  public function getPlotWidth() {
    return $this->PlotWidth;
  }

  public function getPlotHeight() {
    return $this->PlotHeight;
  }

  public function display() {
    if($this->ShowScale) {
    //   return $this->axisSVG() . ' ' . $this->tickSVG() . ' ' . $this->axisLabelSVG() . ' ' . $this->splineSVG();
    // } else {
    //   return $this->axisSVG() . ' ' . $this->tickSVG() . ' ' . $this->scaleSVG() . ' ' . $this->axisLabelSVG() . ' ' . $this->splineSVG();
    }
  }


  ### Private Methods ###
  private function resetPlotDimensionValues() {
    $this->PlotOrigin = array('x' => 0, 'y' => $this->PlotHeight * -1);
    $this->AxisLength = array('x' => $this->PlotWidth - $this->PlotPadding - $this->PlotMargin, 'y' => $this->PlotHeight - $this->PlotPadding - $this->PlotMargin);
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

}

?>
