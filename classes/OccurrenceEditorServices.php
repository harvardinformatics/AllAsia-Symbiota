<?php
include_once($SERVER_ROOT.'/config/dbconnection.php');

class OccurrenceEditorServices {

	private $conn;

	public function __construct(){
		$this->conn = MySQLiConnectionFactory::getCon('readonly');
	}

	public function __destruct(){
		if($this->conn !== false) $this->conn->close();
	}

	//AJAX query calls
	public function getSpeciesSuggest($term){
		$retArr = Array();
		$term = preg_replace('/[^a-zA-Z()\-. ]+/', '', $term);
		$term = preg_replace('/\s{1}x{1}\s{0,1}$/i', ' _ ', $term);
		$term = preg_replace('/\s{1}x{1}\s{1}/i', ' _ ', $term);

		// Enable scientific name entry shortcuts: 2-3 letter codes separated by spaces, e.g. "pse men"
		// From collections/editor/rpc/getassocspp.php

		// Split the search string by spaces if there are any.
		$str1 = ''; $str2 = ''; $str3 = '';
		$strArr = explode(' ',$term);
		$strCnt = count($strArr);
		$str1 = $strArr[0];
		if($strCnt > 1){
			$str2 = $strArr[1];
		}
		if($strCnt > 2){
			$str3 = $strArr[2];
		}

		// Construct the SQL query
		$sql = 'SELECT DISTINCT tid, sciname FROM taxa WHERE unitname1 LIKE "'.$str1.'%" ';
		if($str2){
			$sql .= 'AND unitname2 LIKE "'.$str2.'%" ';
		}
		if($str3){
			$sql .= 'AND unitname3 LIKE "'.$str3.'%" ';
		}
		$sql .= 'ORDER BY sciname';

		// If the search term has an infraspecific separator, use the old version of the SQL, otherwise, no matches will be returned
		if(array_intersect($strArr, array("var.", "ssp.", "nothossp.", "f.", "×", "x", "†"))) $sql = 'SELECT DISTINCT tid, sciname FROM taxa WHERE sciname LIKE "'.$term.'%" ';

		$rs = $this->conn->query($sql);
		while ($r = $rs->fetch_object()){
			$retArr[] = array('id' => $r->tid, 'value' => $r->sciname);
		}
		$rs->free();
		return $retArr;
	}

	public function getGeography($term, $target, $parentTerm){
		$retArr = Array();
		$sql = 'SELECT DISTINCT countryname AS term FROM lkupcountry WHERE countryname LIKE "'.$this->cleanInStr($term).'%" ';
		if($target == 'state'){
			$sql = 'SELECT DISTINCT s.statename AS term FROM lkupstateprovince s ';
			$sqlWhere = 'WHERE s.statename LIKE "'.$this->cleanInStr($term).'%" ';
			if($parentTerm){
				$sql .= 'INNER JOIN lkupcountry c ON s.countryid = c.countryid ';
				$sqlWhere .= 'AND c.countryname = "'.$this->cleanInStr($parentTerm).'" ';
			}
			$sql .= $sqlWhere;
		}
		elseif($target == 'county'){
			$sql = 'SELECT DISTINCT c.countyname AS term FROM lkupcounty c ';
			$sqlWhere = 'WHERE c.countyname LIKE "'.$this->cleanInStr($term).'%" ';
			if($parentTerm){
				$sql .= 'INNER JOIN lkupstateprovince s ON c.stateid = s.stateid ';
				$sqlWhere .= 'AND s.statename = "'.$this->cleanInStr($parentTerm).'" ';
			}
			$sql .= $sqlWhere;
		}
		elseif($target == 'municipality'){
			$sql = 'SELECT DISTINCT m.municipalityname AS term FROM lkupmunicipality m ';
			$sqlWhere = 'WHERE m.municipalityname LIKE "'.$this->cleanInStr($term).'%" ';
			if($parentTerm){
				$sql .= 'INNER JOIN lkupstateprovince s ON m.stateid = s.stateid ';
				$sqlWhere .= 'AND s.statename = "'.$this->cleanInStr($parentTerm).'" ';
			}
			$sql .= $sqlWhere;
		}
		$rs = $this->conn->query($sql);
		while ($r = $rs->fetch_object()) {
			$retArr[] = $r->term;
		}
		$rs->free();
		sort($retArr);
		return $retArr;
	}

	public function getPaleoGtsParents($term){
		$retArr = Array();
		$sql = 'SELECT gtsid, gtsterm, rankid, rankname, parentgtsid FROM omoccurpaleogts WHERE rankid > 10 AND gtsterm = "'.$this->cleanInStr($term).'"';
		$parentId = '';
		do{
			$rs = $this->conn->query($sql);
			if($r = $rs->fetch_object()){
				if($parentId == $r->parentgtsid){
					$parentId = 0;
				}
				else{
					$retArr[] = array("rankid" => $r->rankid, "value" => $r->gtsterm);
					$parentId = $r->parentgtsid;
				}
			}
			else $parentId = 0;
			$rs->free();
			$sql = 'SELECT gtsid, gtsterm, rankid, rankname, parentgtsid FROM omoccurpaleogts WHERE rankid > 10 AND gtsid = '.$parentId;
		}while($parentId);
		return $retArr;
	}

	//Misc functions
	protected function cleanInStr($str){
		$newStr = trim($str);
		$newStr = preg_replace('/\s\s+/', ' ',$newStr);
		$newStr = $this->conn->real_escape_string($newStr);
		return $newStr;
	}
}
?>