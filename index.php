<?php
include_once('config/symbini.php');
if($LANG_TAG == 'en' || !file_exists($SERVER_ROOT.'/content/lang/templates/index.'.$LANG_TAG.'.php')) include_once($SERVER_ROOT.'/content/lang/templates/index.en.php');
else include_once($SERVER_ROOT.'/content/lang/templates/index.'.$LANG_TAG.'.php');
header('Content-Type: text/html; charset=' . $CHARSET);
?>
<!DOCTYPE html>
<html lang="<?php echo $LANG_TAG ?>">
<head>
	<title><?php echo $DEFAULT_TITLE; ?> <?php echo $LANG['HOME']; ?></title>
	<?php
	include_once($SERVER_ROOT . '/includes/head.php');
	include_once($SERVER_ROOT . '/includes/googleanalytics.php');
	?>
</head>
<body>
	<?php
	include($SERVER_ROOT . '/includes/header.php');
	?>
	<div class="navpath"></div>
	<main id="innertext">
		<h1 class="page-heading screen-reader-only"><?php echo $DEFAULT_TITLE; ?> <?php echo $LANG['HOME']; ?></h1>
		<?php
		if($LANG_TAG == 'es'){
			?>
			<div>
				<h1 class="headline">Bienvenidos</h1>
				<p>Este portal de datos se ha establecido para promover la colaboración... Reemplazar con texto introductorio en inglés</p>
			</div>
			<?php
		}
		elseif($LANG_TAG == 'fr'){
			?>
			<div>
				<h1 class="headline">Bienvenue</h1>
				<p>Ce portail de données a été créé pour promouvoir la collaboration... Remplacer par le texte d'introduction en anglais</p>
			</div>
			<?php
		}
		else{
			//Default Language
			?>
			<div>
				<h1>All Asia Thematic Collections Network: Digitizing Asian Vascular Plant Specimens</h1>
				<p>
					The All Asia Thematic Collections Network, launched in 2021 with NSF funding and led by Harvard University Herbaria, is a U.S. initiative to digitize over 3 million Asian vascular plant specimens from museums, universities, and botanical gardens.
				</p>
			</div>
			<div style="float:right"><img src="images/layout/mountain.jpg" style="width:400px;margin:0px 60px" /></div>
			<div style="float:right;margin-top:10px;margin-bottom:20px"><img src="images/layout/fog.jpg" style="width:400px;margin:0px 60px" /></div>
			<div>
				<h2>Why Focus on Asian Vascular Plants?</h2>
				<p>
					Asia, the largest continent, boasts diverse habitats from tundra to rainforests, hosting over one-third of the world's 350,000 plant species. These range from alpine plants to rainforest giants, and include several biodiversity hotspots. Unfortunately, this diversity faces threats from human activity and climate change. The specimens digitized represent the largest U.S. collections of Asian vascular plants, tracing back some of the earliest floral explorations and species discoveries in Asia.
				</p>
			</div>
			<div>
				<p>
					The digital records serve myriad scientific uses, from biodiversity research to climate change studies, and non-traditional applications like virus transmission modeling and machine-vision algorithms. Available free-of-charge, these records support scientific, historical, cultural, and artistic endeavors.
				</p>
				<p>
					<a href="<?= $CLIENT_ROOT . '/collections/search/index.php'?>">Search the database now!</a>
				</p>
			</div>
			<?php
		}
		?>
	</main>
	<?php
	include($SERVER_ROOT . '/includes/footer.php');
	?>
</body>
</html>
