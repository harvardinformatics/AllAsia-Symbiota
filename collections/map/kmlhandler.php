<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceMapManager.php');
header("Content-Type: text/html; charset=".$CHARSET);

$recLimit = (isset($_REQUEST['reclimit'])?$_REQUEST['reclimit']:0);
$kmlFields = array_key_exists('kmlFields',$_POST)?$_POST['kmlFields']:null;

$mapManager = new OccurrenceMapManager();
$kmlFilePath = $mapManager->writeKMLFile($recLimit,$kmlFields);
?>