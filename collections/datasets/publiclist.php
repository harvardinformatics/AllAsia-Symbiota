<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/OccurrenceDataset.php');
include_once($SERVER_ROOT.'/content/lang/collections/datasets/publiclist.'.$LANG_TAG.'.php');
header('Content-Type: text/html; charset='.$CHARSET);

$datasetManager = new OccurrenceDataset();
?>
<!DOCTYPE html>
<html lang="<?php echo $LANG_TAG ?>">
	<head>
		<title><?= $LANG['PUB_DAT_LIST'] ?></title>
		<?php
		include_once($SERVER_ROOT.'/includes/head.php');
		?>
	</head>
	<body>
		<?php
		include($SERVER_ROOT.'/includes/header.php');
		?>
		<div class="navpath">
			<a href="<?= $CLIENT_ROOT ?>/index.php"> <?= $LANG['H_HOME'] ?> </a> &gt;&gt;
			<b> <?= $LANG['PUB_DAT_LIST'] ?> </b>
		</div>
		<!-- This is inner text! -->
		<div role="main" id="innertext">
			<h1 class="page-heading"><?= $LANG['PUB_DAT_LIST'] ?></h1>
			<ul>
				<?php
				if($datasetArr = $datasetManager->getPublicDatasets()){
					foreach($datasetArr as $category => $categoryArr) {
						if($category) echo '<h3>' . $category . '</h3>';
						foreach($categoryArr as $dsID => $dsObject){
							echo '<li><a href="public.php?datasetid=' . $dsID . '">' . $dsObject->name . '</a></li>';
						}
					}
				}
				else{
					echo $LANG['NO_DATASETS'];
				}
				?>
			</ul>
		</div>
		<?php
		include($SERVER_ROOT.'/includes/footer.php');
		?>
	</body>
</html>
