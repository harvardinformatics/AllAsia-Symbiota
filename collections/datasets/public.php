<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceDataset.php');
header("Content-Type: text/html; charset=".$CHARSET);

// Datasets
$datasetid = array_key_exists('datasetid',$_REQUEST)?$_REQUEST['datasetid']:0;

if(!is_numeric($datasetid)) $datasetid = 0;

$datasetManager = new OccurrenceDataset();
$dArr = $datasetManager->getPublicDatasetMetadata($datasetid);
$searchUrl = '../../collections/list.php?datasetid='.$datasetid;
$tableUrl = '../../collections/listtabledisplay.php?datasetid='.$datasetid;
$taxaUrl = '../../collections/list.php?datasetid='.$datasetid.'&tabindex=0';
// $downloadUrl = '../../collections/download/index.php?datasetid='.$datasetid;
$ocArr = $datasetManager->getOccurrences($datasetid);
?>
<!DOCTYPE html>
<html lang="<?php echo $LANG_TAG ?>">
	<head>
		<title>Dataset: <?php echo $dArr['name'] ;?></title>
		<?php
		include_once($SERVER_ROOT.'/includes/head.php');
		?>
	</head>
	<body>
		<?php
		$displayLeftMenu = true;
		include($SERVER_ROOT.'/includes/header.php');
		?>
		<div class="navpath">
			<a href="<?php echo htmlspecialchars($CLIENT_ROOT, HTML_SPECIAL_CHARS_FLAGS); ?>/index.php">Home</a> &gt;&gt;
			<b>Dataset: <?php echo $dArr['name'] ;?></b>
		</div>
		<!-- This is inner text! -->
		<div id="innertext">
    <h1>Dataset: <?php echo $dArr['name'] ;?></h1>
    <ul>
      <!-- Metadata -->
      <div><?php echo $dArr['description'] ;?></div>
      <!-- Occurrences Summary -->
      <p>This dataset includes <?php echo count($ocArr); ?> records.</p>

      <p><a class="btn" href="<?php echo htmlspecialchars($searchUrl, HTML_SPECIAL_CHARS_FLAGS) ;?>">View and download samples in this Dataset (List view)</a></p>
      <p><a class="btn" href="<?php echo htmlspecialchars($tableUrl, HTML_SPECIAL_CHARS_FLAGS) ;?>">View samples in this Dataset (Table view)</a></p>
      <p><a class="btn" href="<?php echo htmlspecialchars($taxaUrl, HTML_SPECIAL_CHARS_FLAGS) ;?>">View list of taxa in this Dataset</a></p>
      <!-- <p><a href="#">Download this Dataset</a></p> -->
    </ul>
		</div>
		<?php
		include($SERVER_ROOT.'/includes/footer.php');
		?>
	</body>
</html>
