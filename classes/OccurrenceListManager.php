<?php
include_once("OccurrenceManager.php");
include_once("OccurrenceAccessStats.php");

class OccurrenceListManager extends OccurrenceManager{

	private $recordCount = 0;
	private $sortArr = array();

 	public function __construct(){
 		parent::__construct();
 	}

	public function __destruct(){
 		parent::__destruct();
	}

	public function getSpecimenMap($pageRequest,$cntPerPage){
		$returnArr = Array();
		$canReadRareSpp = false;
		if($GLOBALS['USER_RIGHTS']){
			if($GLOBALS['IS_ADMIN'] || array_key_exists("CollAdmin", $GLOBALS['USER_RIGHTS']) || array_key_exists("RareSppAdmin", $GLOBALS['USER_RIGHTS']) || array_key_exists("RareSppReadAll", $GLOBALS['USER_RIGHTS'])){
				$canReadRareSpp = true;
			}
		}
		$occArr = array();
		$sqlWhere = $this->getSqlWhere();
		if(!$this->recordCount || $this->reset){
			$this->setRecordCnt($sqlWhere);
		}
		$sql = 'SELECT o.occid, c.collid, c.institutioncode, c.collectioncode, c.collectionname, c.icon, '.
			'o.catalognumber, o.family, o.sciname, o.scientificnameauthorship, o.tidinterpreted, o.recordedby, o.recordnumber, o.eventdate, o.year, o.startdayofyear, o.enddayofyear, '.
			'o.country, o.stateprovince, o.county, o.locality, o.decimallatitude, o.decimallongitude, o.localitysecurity, o.localitysecurityreason, '.
			'o.habitat, o.substrate, o.minimumelevationinmeters, o.maximumelevationinmeters, o.observeruid, c.sortseq '.
			'FROM omoccurrences o LEFT JOIN omcollections c ON o.collid = c.collid ';
		$sql .= $this->getTableJoins($sqlWhere).$sqlWhere;
		//Don't allow someone to query all occurrences if there are no conditions
		if(!$sqlWhere) $sql .= 'WHERE o.occid IS NULL ';

		if($this->sortArr){
			$sql .= 'ORDER BY '.implode(',',$this->sortArr);
		}
		else{
			$sql .= 'ORDER BY c.sortseq, c.collectionname ';
			$pageRequest = ($pageRequest - 1)*$cntPerPage;
		}
		$sql .= ' LIMIT '.$pageRequest.",".$cntPerPage;
		//echo "<div>Spec sql: ".$sql."</div>";
		$result = $this->conn->query($sql);
		if($result){
			while($row = $result->fetch_object()){
				$securityClearance = ($row->localitysecurity?false:true);
				if(!$securityClearance){
					if($canReadRareSpp) $securityClearance = true;
					elseif(array_key_exists("CollEditor", $GLOBALS['USER_RIGHTS']) && in_array($row->collid,$GLOBALS['USER_RIGHTS']["CollEditor"])) $securityClearance = true;
					elseif(array_key_exists("RareSppReader", $GLOBALS['USER_RIGHTS']) && in_array($row->collid,$GLOBALS['USER_RIGHTS']["RareSppReader"])) $securityClearance = true;
				}
				$returnArr[$row->occid]['collid'] = $row->collid;
				$returnArr[$row->occid]['instcode'] = $this->cleanOutStr($row->institutioncode);
				$returnArr[$row->occid]['collcode'] = $this->cleanOutStr($row->collectioncode);
				$returnArr[$row->occid]['collname'] = $this->cleanOutStr($row->collectionname);
				$returnArr[$row->occid]['icon'] = $row->icon;
				$returnArr[$row->occid]["catnum"] = $this->cleanOutStr($row->catalognumber);
				$returnArr[$row->occid]["family"] = $this->cleanOutStr($row->family);
				if($securityClearance || $row->localitysecurity == 1){
					$returnArr[$row->occid]["sciname"] = ($row->sciname?$this->cleanOutStr($row->sciname):'undetermined');
					$returnArr[$row->occid]["tid"] = $row->tidinterpreted;
					$returnArr[$row->occid]["author"] = $this->cleanOutStr($row->scientificnameauthorship);
				}
				$returnArr[$row->occid]["collector"] = $this->cleanOutStr($row->recordedby);
				$returnArr[$row->occid]["country"] = $this->cleanOutStr($row->country);
				$returnArr[$row->occid]["state"] = $this->cleanOutStr($row->stateprovince);
				$returnArr[$row->occid]["county"] = $this->cleanOutStr($row->county);
				$returnArr[$row->occid]["obsuid"] = $row->observeruid;
				$returnArr[$row->occid]['localitysecurity'] = $row->localitysecurity;
				if($securityClearance || $row->localitysecurity == 2){
					$locStr = str_replace('.,',',',$row->locality);
					if($row->decimallatitude && $row->decimallongitude) $locStr .= ', '.$row->decimallatitude.' '.$row->decimallongitude;
					$locStr = $this->cleanOutStr(trim($locStr,' ,;'));
					if($row->localitysecurity){
						$locStr .= '<div style="color:orange">Note: ';
						if($row->localitysecurity == 1){
							$locStr .= 'locality ';
						}
						elseif($row->localitysecurity == 2){
							$locStr .= 'taxonomic ';
						}
						elseif($row->localitysecurity == 3){
							$locStr .= 'locality &amp; taxonomic ';
						}
						$locStr .= 'details are being redacted from non-authorized users</div>';
					}
					$returnArr[$row->occid]["locality"] = $locStr;
					$returnArr[$row->occid]["collnum"] = $this->cleanOutStr($row->recordnumber);
					$returnArr[$row->occid]["date"] = $row->eventdate;
					$returnArr[$row->occid]["habitat"] = $this->cleanOutStr($row->habitat);
					$returnArr[$row->occid]['substrate'] = $this->cleanOutStr($row->substrate);
					$elevStr = $row->minimumelevationinmeters;
					if($row->maximumelevationinmeters) $elevStr .= ' - '.$row->maximumelevationinmeters;
					$returnArr[$row->occid]["elev"] = $elevStr;
					$occArr[] = $row->occid;
				}
				else{
					$returnArr[$row->occid]["locality"] = '<span style="color:red;">detailed locality information protected</span>';
				}
			}
			$result->free();
		}
		//Set images
		if($occArr){
			$sql = 'SELECT o.collid, o.occid, i.thumbnailurl '.
				'FROM omoccurrences o INNER JOIN images i ON o.occid = i.occid '.
				'WHERE o.occid IN('.implode(',',$occArr).')';
			$rs = $this->conn->query($sql);
			$previousOccid = 0;
			while($r = $rs->fetch_object()){
				if($r->occid != $previousOccid) $returnArr[$r->occid]['img'] = $r->thumbnailurl;
				$previousOccid = $r->occid;
			}
			$rs->free();
		}
		//Set access statistics
		if($occArr){
			$statsManager = new OccurrenceAccessStats();
			$statsManager->recordAccessEventByArr($occArr,'list');
		}
		return $returnArr;
	}

	private function setRecordCnt($sqlWhere){
		if($sqlWhere){
			$sql = "SELECT COUNT(DISTINCT o.occid) AS cnt FROM omoccurrences o ".$this->getTableJoins($sqlWhere).$sqlWhere;
			//echo "<div>Count sql: ".$sql."</div>";
			$result = $this->conn->query($sql);
			if($result){
				if($row = $result->fetch_object()){
					$this->recordCount = $row->cnt;
				}
				$result->free();
			}
		}
	}

	public function getRecordCnt(){
		return $this->recordCount;
	}

	public function addSort($field,$direction){
		$this->sortArr[] = trim($field.' '.$direction);
	}

	public function getCloseTaxaMatch($name){
		$retArr = array();
		$searchName = $this->cleanInStr($name);
		$sql = 'SELECT tid, sciname FROM taxa WHERE soundex(sciname) = soundex(?)';
		$stmt = $this->conn->prepare($sql);
		$stmt->bind_param('s', $searchName);
		$stmt->execute();
		$stmt->bind_result($tid, $sciname);
		while($stmt->fetch()){
			if($searchName != $sciname) $retArr[$tid] = $sciname;
		}
		$stmt->close();
		return $retArr;
	}
}
?>