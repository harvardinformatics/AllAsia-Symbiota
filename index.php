<?php
include_once('config/symbini.php');
include_once('content/lang/index.'.$LANG_TAG.'.php');
header("Content-Type: text/html; charset=".$CHARSET);
?>
<html>
<head>
	<title><?php echo $DEFAULT_TITLE; ?> Home</title>
	<?php
	$activateJQuery = true;
	include_once($SERVER_ROOT.'/includes/head.php');
        include_once('includes/googleanalytics.php');
	?>
	<link href="css/quicksearch.css" type="text/css" rel="Stylesheet" />
	<script src="js/jquery-3.2.1.min.js" type="text/javascript"></script>
	<script src="js/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
	<script src="js/symb/api.taxonomy.taxasuggest.js" type="text/javascript"></script>
	<script src="<?PHP echo $CLIENT_ROOT; ?>/js/jquery.slides.js"></script>
	<style>
		#slideshowcontainer{ margin-left:auto; margin-right: auto; }
	</style>
</head>
<body>
	<?php
	include($SERVER_ROOT.'/includes/header.php');
	?>
	<!-- This is inner text! -->
	<div  id="innertext">
		<div style="float:right;width:400px;margin-left:20px">
			<div id="quicksearchdiv">
				<!-- -------------------------QUICK SEARCH SETTINGS--------------------------------------- -->
				<form name="quicksearch" id="quicksearch" action="<?php echo $CLIENT_ROOT; ?>/taxa/index.php" method="get" onsubmit="return verifyQuickSearch(this);">
					<div id="quicksearchtext" ><?php echo (isset($LANG['QSEARCH_SEARCH'])?$LANG['QSEARCH_SEARCH']:'Search Taxon'); ?></div>
					<input id="taxa" type="text" name="taxon" />
					<button name="formsubmit"  id="quicksearchbutton" type="submit" value="Search Terms"><?php echo (isset($LANG['QSEARCH_SEARCH_BUTTON'])?$LANG['QSEARCH_SEARCH_BUTTON']:'Search'); ?></button>
				</form>
			</div>
			<div>
<?php
//---------------------------SLIDESHOW SETTINGS---------------------------------------
//If more than one slideshow will be active, assign unique numerical ids for each slideshow.
//If only one slideshow will be active, leave set to 1. 
$ssId = 1; 

//Enter number of images to be included in slideshow (minimum 5, maximum 10) 
$numSlides = 10;

//Enter width of slideshow window (in pixels, minimum 275, maximum 800)
$width = 350;

//Enter amount of days between image refreshes of images
$dayInterval = 7;

//Enter amount of time (in milliseconds) between rotation of images
$interval = 7000;

//Enter checklist id, if you wish for images to be pulled from a checklist,
//leave as 0 if you do not wish for images to come from a checklist
//if you would like to use more than one checklist, separate their ids with a comma ex. "1,2,3,4"
$clId = '4';

//Enter field, specimen, or both to specify whether to use only field or specimen images, or both
$imageType = 'both';

//Enter number of days of most recent images that should be included 
$numDays = 30;

//---------------------------DO NOT CHANGE BELOW HERE-----------------------------

ini_set('max_execution_time', 120);
include_once($SERVER_ROOT.'/classes/PluginsManager.php');
$pluginManager = new PluginsManager();
echo $pluginManager->createSlideShow($ssId,$numSlides,$width,$numDays,$imageType,$clId,$dayInterval,$interval);
?>
			</div>
		</div>
		<?php
		if($LANG_TAG=='en'){
			?>
                        <h1>Welcome to the Guatemala Biodiversity Portal</h1>
                        <div style="padding: 0px 10px;font-size:130%">
                                <p>
				This portal aims to serve as a collaborative resource to integrate biodiversity data from 
				Guatemala. This portal represents an accessible option for the management and digitization of data from 
				biological collections or field observations gathered by entities and researchers dedicated to the study of biodiversity. 
				The portal also allows the generation of maps, checklists and other interactive projects. The information entered in this portal can 
				be published to the <a href="https://www.gbif.org" target="_blank">Global Biodiversity Information Facility (GBIF)</a>, from where it can be harvested 
				by other regional and international aggregators. The data within the portal are freely available for use, but proper citation is encouraged. 
                                </p>
                                <p>
				The Guatemala Biodiversity Portal is hosted by the <a href="https://biokic.asu.edu" target="_blank">Biodiversity Knowledge Integration Center (BIOKIC)</a> 
				at Arizona State University, USA. For further information or to have a collection profile established, please contact 
				Samanta Orellana (<a href="mailto:sorellana@asu.edu">sorellana@asu.edu</a>) or Zabdi López (<a href="mailto:zmlopez@uvg.edu.gt">zmlopez@uvg.edu.gt</a>)
                                </p>
                        </div>
			<?php
		} 
		else{
			?>
			<h1>Bienvenidos al Portal de Biodiversidad de Guatemala</h1>
			<div style="padding: 0px 10px;font-size:130%">
				<p>
				Este portal está diseñado para funcionar como un recurso colaborativo para la integración de datos de biodiversidad de Guatemala 
				provenientes de distintas fuentes. El portal ofrece una alternativa libre y gratuita para el manejo y digitalización de datos 
				provenientes de colecciones biológicas, así como de observaciones de campo de entidades o investigadores dedicados al estudio de la 
				biodiversidad. El portal, además, permite la generación de mapas, listados de especies y otros proyectos interactivos. 
				La información ingresada en este portal también puede ser añadida a la Instalación 
				<a href="https://www.gbif.org" target="_blank">Global de Información de Biodiversidad -GBIF-</a>, desde donde puede alimentar a otros 
				agregadores de información locales e internacionales. Los datos añadidos al portal están disponibles para ser utilizados por investigadores, 
				estudiantes y público en general, pero se insta a citar adecuadamente el origen de los datos.
				</p>
				<p>
				El portal está alojado en los servidores del Centro de 
				<a href="https://biokic.asu.edu" target="_blank">Integración del Conocimiento de la Biodiversidad (BIOKIC)</a> de la Universidad Estatal de Arizona (ASU), 
				en Estados Unidos. Para más información o para gestionar un perfil por favor comunicarse con 
				Samanta Orellana (<a href="mailto:sorellana@asu.edu">sorellana@asu.edu</a>) o Zabdi López (<a href="mailto:zmlopez@uvg.edu.gt">zmlopez@uvg.edu.gt</a>). 
				</p>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	include($SERVER_ROOT.'/includes/footer.php');
	?>
</body>
</html>
