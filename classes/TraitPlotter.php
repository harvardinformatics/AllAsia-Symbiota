<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/Manager.php');
include_once($SERVER_ROOT.'/classes/TraitPolarPlot.php');

class TraitPlotter extends Manager {

	// PROPERTIES
	private $tid;
	private $sid;
	private $stateName;
	private $taxonArr = array();
  private $traitDataArr = array();
	private $plotInstance;
	private $TaxAuthId = 1;


	// METHODS

	// ### Public Methods ###
  public function __construct($type){
		parent::__construct();
		if(strtolower($type) == "polar") {
			$this->plotInstance = new PolarPlot();
		}
	}

	public function __destruct(){
		parent::__destruct();
	}

	public function setSid($sid){
		if(is_numeric($sid) & $sid > 0){
			$this->sid = $sid;
			$this->setTraitState();
		}
	}

	public function setTid($tid){
		if(is_numeric($tid) && $tid > 0){
			$this->tid = $tid;
			$this->setTaxon();
		}
	}

	public function getSciname(){
    if(isset($this->taxonArr['sciname'])){
      $retStr = $this->taxonArr['sciname'];
    } else {
      $retStr = "No scientific name available";
    }
    return $retStr;
  }

	public function getStateName(){
		if(isset($this->stateName)){
			$retStr = $this->stateName;
		} else {
			$retStr = "Trait state unavailable";
		}
		return $retStr;
	}

	public function getViewboxWidth() {
		return $this->plotInstance->getPlotWidth();
	}

	public function getViewboxHeight() {
		return $this->plotInstance->getPlotHeight();
	}

	public function monthlyPolarPlot() {
		if($this->taxonArr['rankid'] > 179) {  // limit to genus and below
			$this->plotInstance->setAxisNumber(12);
			$this->plotInstance->setAxisRotation(15);
			$this->plotInstance->setTickNumber(3);
			$this->plotInstance->setAxisLabels(array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'));
			$this->plotInstance->setDataValues($this->summarizeTraitByMonth());
			return $this->plotInstance->display();
		}
	}

	### Private methods ###
	private function setTaxon(){
		if($this->tid){
			$sql = 'SELECT tid, sciname, author, rankid FROM taxa WHERE (tid = '.$this->tid.') ';
			$rs = $this->conn->query($sql);
			while($r = $rs->fetch_object()){
				$this->taxonArr['tid'] = $r->tid;
				$this->taxonArr['sciname'] = $r->sciname;
				$this->taxonArr['author'] = $r->author;
				$this->taxonArr['rankid'] = $r->rankid;
			}
			$rs->free();
			// Roll up child taxa, then select synonyms of the target and children
			$sql = 'SELECT DISTINCT t.tid FROM taxa t INNER JOIN taxstatus ts ON t.tid = ts.tid WHERE (ts.TidAccepted != ts.tid) AND (ts.taxauthid =' . $this->TaxAuthId . ') AND (ts.tidaccepted IN((SELECT DISTINCT t.tid FROM taxa t INNER JOIN taxstatus ts ON t.tid = ts.tid WHERE (ts.parenttid =' . $this->tid .') AND (ts.TidAccepted = ts.tid) AND (ts.taxauthid =' . $this->TaxAuthId . '))))';
			$rs = $this->conn->query($sql);
			$this->taxonArr['synonymtids'] = array();
			while($r = $rs->fetch_object()){
				$this->taxonArr['synonymtids'][] = $r->tid;
			}
			$rs->free();
		}
	}

	private function setTraitState(){
		if($this->sid){
			$sql = 'SELECT statename FROM tmstates WHERE (stateid = '.$this->sid.') ';
			$rs = $this->conn->query($sql);
			while($r = $rs->fetch_object()){
				$this->stateName = $r->statename;
			}
			$rs->free();
		}
	}

 	private function summarizeTraitByMonth(){
		$countArr = array_fill(0,12,0);  // makes a zero array
		if($this->tid && $this->sid){
			$searchtids = array_merge(array($this->tid), $this->taxonArr['synonymtids']);
			 $sql = 'SELECT o.month, COUNT(*) AS count FROM tmattributes AS a LEFT JOIN omoccurrences AS o ON o.occid = a.occid WHERE o.tidinterpreted IN(' . implode(",", $searchtids) . ') AND a.stateid = ' . $this->sid . ' GROUP BY o.month';
			 $rs = $this->conn->query($sql);
			 while($r = $rs->fetch_object()){
				 if($r->month > 0 && $r->month < 13) {
					 $countArr[$r->month-1] = (int)$r->count;
				 }
			 }
			 $rs->free();
		}
    return $countArr;
  }

}

?>
