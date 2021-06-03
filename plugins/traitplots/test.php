<?php
include_once('../../config/symbini.php');
include_once($SERVER_ROOT.'/classes/TraitPlotter.php');
include_once($SERVER_ROOT.'/classes/TraitPolarPlot.php');


Header("Content-Type: text/html; charset=".$CHARSET);

$tid = array_key_exists("tid",$_REQUEST)?$_REQUEST["tid"]:"";
$sid = array_key_exists("sid",$_REQUEST)?$_REQUEST["sid"]:"";

if(!is_numeric($tid)) $tid = 0;
if(!is_numeric($sid)) $sid = 0;

$polarPlot = new PolarPlot();
$polarPlot->setAxisNumber(12);
$polarPlot->setAxisRotation(15);
$polarPlot->setTickNumber(3);
$polarPlot->setAxisLabels(array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'));

$traitPlotter = new TraitPlotter();
if($tid) $traitPlotter->setTid($tid);
if($sid) $traitPlotter->setSid($sid);

$polarPlot->setDataValues($traitPlotter->getCalendarPlotData());

?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="http://localhost:8080/BioKIC/css/symb/taxa/traitplot.css">
	<style>
	.column {
  float: left;
  width: 50%;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}
	</style>
</head>
<body>
	<div class="row">
		<div class="column">
			<h2>
				<?php echo $traitPlotter->getSciname(); ?>
			</h2><h3>
				<?php echo $traitPlotter->getStateName(); ?>
			</h3>
			<?php
				echo '<svg width="500" height="500" viewbox="0 0 ' . $polarPlot->getViewboxWidth() . ' ' . $polarPlot->getViewboxHeight() . '"><g>' . PHP_EOL;
				echo $polarPlot->getPlotSVG();
				echo '</g></svg>';
			?>
		</div>

		<div class="column">
</div>
</div>
</body>
</html>
