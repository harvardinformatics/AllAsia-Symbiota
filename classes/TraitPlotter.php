<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/Manager.php');
include_once($SERVER_ROOT.'/classes/TraitPolarPlot.php');

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

 	private function setCalendarPlotData(){
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
    return $countArr;
  }
		// $cppts = array();
		// for($i = 0; $i < 12; $i++) {
		// 	$cppts[] = $this->calPlotCoord($calPlotUnitPoints[$i], $countArr[$i], array('x'=>150,'y'=>150));
		// }
		// return $cppts;


	

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

  public function getCalendarPlotData() {
    return $this->setCalendarPlotData();
  }

}

?>
