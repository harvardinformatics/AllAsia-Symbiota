<?php
include_once('../config/symbini.php');
include_once($SERVER_ROOT.'/content/lang/imagelib/search.'.$LANG_TAG.'.php');
include_once($SERVER_ROOT.'/classes/ImageLibrarySearch.php');
header('Content-Type: text/html; charset='.$CHARSET);

$taxonType = isset($_REQUEST['taxontype']) ? filter_var($_REQUEST['taxontype'], FILTER_SANITIZE_NUMBER_INT) : 0;
$useThes = array_key_exists('usethes',$_REQUEST) ? filter_var($_REQUEST['usethes'], FILTER_SANITIZE_NUMBER_INT) : 0;
$taxaStr = isset($_REQUEST['taxa']) ? htmlspecialchars($_REQUEST['taxa'], HTML_SPECIAL_CHARS_FLAGS) : '';
$phUid = array_key_exists('phuid',$_REQUEST) ? filter_var($_REQUEST['phuid'], FILTER_SANITIZE_NUMBER_INT) : 0;
$tags = array_key_exists('tags',$_REQUEST) ? htmlspecialchars($_REQUEST['tags'], HTML_SPECIAL_CHARS_FLAGS) : '';
$keywords = array_key_exists('keywords',$_REQUEST) ? htmlspecialchars($_REQUEST['keywords'], HTML_SPECIAL_CHARS_FLAGS) : '';
$imageCount = isset($_REQUEST['imagecount']) ? htmlspecialchars($_REQUEST['imagecount'], HTML_SPECIAL_CHARS_FLAGS) : 'all';
$imageType = isset($_REQUEST['imagetype']) ? filter_var($_REQUEST['imagetype'], FILTER_SANITIZE_NUMBER_INT) : 0;
$pageNumber = array_key_exists('page',$_REQUEST) ? filter_var($_REQUEST['page'], FILTER_SANITIZE_NUMBER_INT) : 1;
$cntPerPage = array_key_exists('cntperpage',$_REQUEST) ? filter_var($_REQUEST['cntperpage'], FILTER_SANITIZE_NUMBER_INT) : 200;
$catId = array_key_exists('catid',$_REQUEST) ? htmlspecialchars($_REQUEST['catid'], HTML_SPECIAL_CHARS_FLAGS) : 0;
$action = array_key_exists('submitaction',$_REQUEST) ? htmlspecialchars($_REQUEST['submitaction'], HTML_SPECIAL_CHARS_FLAGS) : '';

if(!$useThes && !$action) $useThes = 1;
if(!$taxonType && isset($DEFAULT_TAXON_SEARCH)) $taxonType = $DEFAULT_TAXON_SEARCH;
if(!$catId && isset($DEFAULTCATID) && $DEFAULTCATID) $catId = $DEFAULTCATID;

//Sanitation
if(!is_numeric($pageNumber)) $pageNumber = 100;
if(!is_numeric($cntPerPage)) $cntPerPage = 100;
if(!preg_match('/^[,\d]+$/', $catId)) $catId = 0;
if(preg_match('/[^\D]+/', $action)) $action = '';

$imgLibManager = new ImageLibrarySearch();
$imgLibManager->setTaxonType($taxonType);
$imgLibManager->setUseThes($useThes);
$imgLibManager->setTaxaStr($taxaStr);
$imgLibManager->setPhotographerUid($phUid);
$imgLibManager->setTags($tags);
$imgLibManager->setKeywords($keywords);
$imgLibManager->setImageCount($imageCount);
$imgLibManager->setImageType($imageType);
if(isset($_REQUEST['db'])) $imgLibManager->setCollectionVariables($_REQUEST);
?>
<!DOCTYPE html>
<html lang="<?php echo $LANG_TAG ?>">
<head>
	<title><?php echo $DEFAULT_TITLE; ?> Image Library</title>
	<meta name='keywords' content='Search, Images, Taxon' />
	<?php
	include_once($SERVER_ROOT.'/includes/head.php');
	include_once($SERVER_ROOT.'/includes/googleanalytics.php');
	?>
	<link href="<?php echo htmlspecialchars($CSS_BASE_PATH, HTML_SPECIAL_CHARS_FLAGS); ?>/symbiota/collections/listdisplay.css" type="text/css" rel="stylesheet" />
	<link href="<?php echo htmlspecialchars($CSS_BASE_PATH, HTML_SPECIAL_CHARS_FLAGS); ?>/symbiota/collections/sharedCollectionStyling.css" type="text/css" rel="stylesheet" />
	<link href="<?php echo $CSS_BASE_PATH; ?>/jquery-ui.css" type="text/css" rel="stylesheet">
	<style>
		fieldset{ padding: 15px }
		fieldset legend{ font-weight:bold }
	</style>
	<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-3.7.1.min.js" type="text/javascript"></script>
	<script src="<?php echo $CLIENT_ROOT; ?>/js/jquery-ui.min.js" type="text/javascript"></script>
	<script src="../js/symb/collections.index.js?ver=2" type="text/javascript"></script>
	<script type="text/javascript">
		var clientRoot = "<?php echo $CLIENT_ROOT; ?>";

		jQuery(document).ready(function($) {
			$('#tabs').tabs({
				<?php if($action) echo 'active: 1,'; ?>
				beforeLoad: function( event, ui ) {
					$(ui.panel).html("<p>Loading...</p>");
				}
			});
		});
	</script>
	<script src="../js/symb/api.taxonomy.taxasuggest.js?ver=4" type="text/javascript"></script>
	<script src="../js/symb/imagelib.search.js?ver=2" type="text/javascript"></script>
</head>
<body>
	<?php
	$displayLeftMenu = (isset($imagelib_searchMenu)?$imagelib_searchMenu:false);
	include($SERVER_ROOT.'/includes/header.php');
	?>
	<div class="navpath">
		<a href="../index.php"> <?php echo htmlspecialchars($LANG['H_HOME'], HTML_SPECIAL_CHARS_FLAGS) ?> </a> &gt;&gt;
		<a href="index.php"> <?php echo htmlspecialchars($LANG['IMG_LIB'], HTML_SPECIAL_CHARS_FLAGS) ?> </a> &gt;&gt;
		<b> <?php echo htmlspecialchars($LANG['IMG_SEARCH'], HTML_SPECIAL_CHARS_FLAGS) ?> </b>

	</div>
	<!-- This is inner text! -->
	<div id="innertext">
		<div id="tabs" style="margin:0px;">
			<ul>
				<li><a href="#criteriadiv">Search Criteria</a></li>
				<?php
				if($action == 'search'){
					?>
					<li><a href="#imagesdiv"><span id="imagetab">Images</span></a></li>
					<?php
				}
				?>
			</ul>
			<form name="imagesearchform" id="imagesearchform" action="search.php" method="post">
				<div id="criteriadiv">
					<div class="flex-form">
						<div style="margin-top: 1.5px">
							<label for="taxontype"><?php echo htmlspecialchars($LANG['TAXON_TYPE'], HTML_SPECIAL_CHARS_FLAGS) ?>: </label>
							<select id="taxontype" name="taxontype">
								<?php
								for($h=1;$h<6;$h++){
									echo '<option value="'.$h.'" '.($imgLibManager->getTaxonType()==$h?'SELECTED':'').'>'.$LANG['SELECT_1-'.$h].'</option>';
								}
								?>
							</select>
						</div>
						<div>
							<label for="taxa"><?php echo htmlspecialchars($LANG['TAXON'], HTML_SPECIAL_CHARS_FLAGS) ?>: </label>
							<input id="taxa" name="taxa" type="text" style="width:450px;" value="<?php echo $imgLibManager->getTaxaStr(); ?>" title="Separate multiple names w/ commas" autocomplete="off" />
						</div>
						<div>
							<input id ="usethes" name="usethes" type="checkbox" value="1" <?php if(!$action || $imgLibManager->getUseThes()) echo 'CHECKED'; ?> >
							<label for="usethes"><?php echo htmlspecialchars($LANG['USE_THES'], HTML_SPECIAL_CHARS_FLAGS) ?> </label>
						</div>
					</div>
					<div class="flex-form">
						<div>
							<label for="phuid"><?php echo htmlspecialchars($LANG['PHU_ID'], HTML_SPECIAL_CHARS_FLAGS) ?>: </label>
							<select id="phuid" name="phuid">
								<option value="">All Image Contributors</option>
								<option value="">-----------------------------</option>
								<?php
								$uidList = $imgLibManager->getPhotographerUidArr();
								foreach($uidList as $uid => $name){
									echo '<option value="'.$uid.'" '.($imgLibManager->getPhotographerUid()==$uid?'SELECTED':'').'>'.$name.'</option>';
								}
								?>
							</select>
						</div>
					</div>
					<?php
					if($tagArr = $imgLibManager->getTagArr()){
						?>
						<div class="flex-form">
							<div>
								<label for="tags"><?php echo htmlspecialchars($LANG['IMG_TAGS'], HTML_SPECIAL_CHARS_FLAGS) ?>: </label>
								<select id="tags" name="tags" >
									<option value="">Select Tag</option>
									<option value="">--------------</option>
									<?php
									foreach($tagArr as $k){
										echo '<option value="'.$k.'" '.($imgLibManager->getTags()==$k?'SELECTED ':'').'>'.$k.'</option>';
									}
									?>
								</select>
							</div>	
						</div>
						<?php
					}
					?>
					<!--
					<div style="clear:both;margin-bottom:5px;">
						Image Keywords:
						<input type="text" id="keywords" style="width:350px;" name="keywords" value="<?php //echo $imgLibManager->getKeywordSuggest(); ?>" title="Separate multiple keywords w/ commas" />
					</div>
					 -->
					<?php
					$collList = $imgLibManager->getFullCollectionList($catId);
					$specArr = (isset($collList['spec'])?$collList['spec']:null);
					$obsArr = (isset($collList['obs'])?$collList['obs']:null);
					?>
						<div class="flex-form">
							<fieldset>
									<legend> <?php echo htmlspecialchars($LANG['IMG_COUNT'], HTML_SPECIAL_CHARS_FLAGS) ?>: </legend>
								
								<input class="top-breathing-room-rel-sm" id="countAll" type="radio" name="imagecount" value="0" CHECKED><label for="countAll"> <?php echo htmlspecialchars($LANG['COUNT_ALL'], HTML_SPECIAL_CHARS_FLAGS) ?></label> <br/>
								<input id="countTaxon" type="radio" name="imagecount" value="1"><label for="countTaxon"> <?php echo htmlspecialchars($LANG['COUNT_TAXON'], HTML_SPECIAL_CHARS_FLAGS) ?></label> <br/>
								
								<?php
							if($specArr){
								?>
								<input id="countSpecimen" type="radio" name="imagecount" value="2"><label for="countSpecimen"> <?php echo htmlspecialchars($LANG['COUNT_SPECIMEN'], HTML_SPECIAL_CHARS_FLAGS) ?></label> <br/>
								<?php
							}
							?>
							</fieldset>
						</div>
					<div>
						<div class="flex-form">
							<fieldset>
								<legend> <?php echo htmlspecialchars($LANG['IMG_TYPE'], HTML_SPECIAL_CHARS_FLAGS) ?>: </legend>
								
								<input class="top-breathing-room-rel-sm" id="typeAll" type="radio" name="imagetype" value="0" CHECKED> <label for="typeAll">  <?php echo htmlspecialchars($LANG['TYPE_ALL'], HTML_SPECIAL_CHARS_FLAGS) ?> </label> <br/>
								<input id="typeSpecimen" type="radio" name="imagetype" value="1" > <label for="typeSpecimen">  <?php echo htmlspecialchars($LANG['TYPE_SPECIMEN'], HTML_SPECIAL_CHARS_FLAGS) ?> </label> <br/>
								<input id="typeObs" type="radio" name="imagetype" value="2" > <label for="typeObs">  <?php echo htmlspecialchars($LANG['TYPE_OBS'], HTML_SPECIAL_CHARS_FLAGS) ?> </label> <br/>
								<input id="typeField" type="radio" name="imagetype" value="3" > <label for="typeField">  <?php echo htmlspecialchars($LANG['TYPE_FIELD'], HTML_SPECIAL_CHARS_FLAGS) ?> </label> <br/>
								
								<script src="../js/symb/imagelib.radiobuttons.js"></script>
							</fieldset>
						</div>
						<div class="flex-form">
							<div>
								<button name="submitaction" type="submit" value="search" class="load-button"> <?php echo htmlspecialchars($LANG['LOAD_IMAGES'], HTML_SPECIAL_CHARS_FLAGS) ?></button>
							</div>
						</div>
					</div>
					<?php
					if($specArr || $obsArr){
						?>
						<div id="collection-div" style="margin:15px;clear:both;display:<?php echo ($imgLibManager->getImageType() == 1 || $imgLibManager->getImageType() == 2?'':'none'); ?>">
							<fieldset>
								<legend>Collections</legend>
								<div id="specobsdiv">
									<div class="specimen-header-margin">
										<h2><?php echo $LANG['SPECIMEN_COLLECTIONS'] ?></h2>
									</div>
									<div style="margin:0px 0px 10px 5px;">
										<input id="dballcb" name="db[]" class="specobs" value='all' type="checkbox" onclick="selectAll(this);" checked />
								 		<?php echo (isset($LANG['SELECT_ALL'])?$LANG['SELECT_ALL']:'Select/Deselect all'); ?>
									</div>
									<?php
									$imgLibManager->outputFullCollArr($specArr, $catId, 'Specimen', 'Specimens');
									$hrAndHeaderText = '<div class="specimen-header-margin"><hr/><h2>' . $LANG['OBSERVATION_COLLECTIONS'] . '</h2></div>';
									if($specArr && $obsArr) echo $hrAndHeaderText;
									$imgLibManager->outputFullCollArr($obsArr, $catId, 'Observation', 'Observations');
									?>
								</div>
							</fieldset>
						</div>
						<?php
					}
					?>
				</div>
			</form>
			<?php
			if($action == 'search'){
				?>
				<div id="imagesdiv">
					<div id="imagebox">
						<?php
						$imageArr = $imgLibManager->getImageArr($pageNumber,$cntPerPage);
						$recordCnt = $imgLibManager->getRecordCnt();
						echo '<div style="margin-bottom:5px">Search criteria: '.$imgLibManager->getSearchTermDisplayStr().'</div>';
						if($imageArr){
							$lastPage = ceil($recordCnt / $cntPerPage);
							$startPage = ($pageNumber > 4?$pageNumber - 4:1);
							$endPage = ($lastPage > $startPage + 9?$startPage + 9:$lastPage);
							$url = 'search.php?'.$imgLibManager->getQueryTermStr().'&submitaction=search';
							$pageBar = '<div style="float:left" >';
							if($startPage > 1){
								$pageBar .= '<span class="pagination" style="margin-right:5px;"><a href="' . htmlspecialchars($url , HTML_SPECIAL_CHARS_FLAGS). '&page=1">First</a></span>';
								$pageBar .= '<span class="pagination" style="margin-right:5px;"><a href="' . htmlspecialchars($url , HTML_SPECIAL_CHARS_FLAGS). '&page=' . htmlspecialchars((($pageNumber - 10) < 1 ?1:$pageNumber - 10), HTML_SPECIAL_CHARS_FLAGS) . '">&lt;&lt;</a></span>';
							}
							for($x = $startPage; $x <= $endPage; $x++){
								if($pageNumber != $x){
									$pageBar .= '<span class="pagination" style="margin-right:3px;"><a href="' . htmlspecialchars($url , HTML_SPECIAL_CHARS_FLAGS). '&page=' . htmlspecialchars($x, HTML_SPECIAL_CHARS_FLAGS) . '">' . htmlspecialchars($x, HTML_SPECIAL_CHARS_FLAGS) . '</a></span>';
								}
								else{
									$pageBar .= "<span class='pagination' style='margin-right:3px;font-weight:bold;'>".$x."</span>";
								}
							}
							if(($lastPage - $startPage) >= 10){
								$pageBar .= '<span class="pagination" style="margin-left:5px;"><a href="' . htmlspecialchars($url , HTML_SPECIAL_CHARS_FLAGS). '&page=' . htmlspecialchars((($pageNumber + 10) > $lastPage?$lastPage:($pageNumber + 10)), HTML_SPECIAL_CHARS_FLAGS) . '">&gt;&gt;</a></span>';
								if($recordCnt < 10000) $pageBar .= '<span class="pagination" style="margin-left:5px;"><a href="' . htmlspecialchars($url , HTML_SPECIAL_CHARS_FLAGS). '&page=' . htmlspecialchars($lastPage, HTML_SPECIAL_CHARS_FLAGS) . '">Last</a></span>';
							}
							$pageBar .= '</div><div style="float:right;margin-top:4px;margin-bottom:8px;">';
							$beginNum = ($pageNumber - 1)*$cntPerPage + 1;
							$endNum = $beginNum + $cntPerPage - 1;
							if($endNum > $recordCnt) $endNum = $recordCnt;
							$pageBar .= "Page ".$pageNumber.", records ".number_format($beginNum)."-".number_format($endNum)." of ".number_format($recordCnt)."</div>";
							$paginationStr = $pageBar;
							echo '<div style="width:100%;">'.$paginationStr.'</div>';
							echo '<div style="clear:both;margin:5 0 5 0;"><hr /></div>';
							echo '<div style="width:98%;margin-left:auto;margin-right:auto;">';
							$occArr = array();
							$collArr = array();
							if(isset($imageArr['occ'])){
								$occArr = $imageArr['occ'];
								unset($imageArr['occ']);
								$collArr = $imageArr['coll'];
								unset($imageArr['coll']);
							}
							foreach($imageArr as $imgArr){
								$imgId = $imgArr['imgid'];
								$imgUrl = $imgArr['url'];
								$imgTn = $imgArr['thumbnailurl'];
								if($imgTn){
									$imgUrl = $imgTn;
									if($IMAGE_DOMAIN && substr($imgTn,0,1)=='/') $imgUrl = $IMAGE_DOMAIN.$imgTn;
								}
								elseif($IMAGE_DOMAIN && substr($imgUrl,0,1)=='/'){
									$imgUrl = $IMAGE_DOMAIN.$imgUrl;
								}
								?>
								<div class="tndiv" style="margin-bottom:15px;margin-top:15px;">
									<div class="tnimg">
										<?php
										$anchorLink = '';
										if($imgArr['occid']){
											$anchorLink = '<a href="#" onclick="openIndPU('.$imgArr['occid'].');return false;">';
									  	}
										else{
											$anchorLink = '<a href="#" onclick="openImagePopup('.$imgId.');return false;">';
										}
										echo $anchorLink.'<img src="'.$imgUrl.'" /></a>';
										?>
									</div>
									<div>
										<?php
										$sciname = $imgArr['sciname'];
										if(!$sciname && $imgArr['occid'] && $occArr[$imgArr['occid']]['sciname']) $sciname = $occArr[$imgArr['occid']]['sciname'];
										if($sciname){
											if(strpos($imgArr['sciname'],' ')) $sciname = '<i>'.$sciname.'</i>';
											if($imgArr['tid']) echo '<a href="#" onclick="openTaxonPopup('.$imgArr['tid'].');return false;" >';
											echo $sciname;
											if($imgArr['tid']) echo '</a>';
											echo '<br />';
										}
										$photoAuthor = '';
										$authorLink = '';
										if($imgArr['uid']){
											$photoAuthor = $uidList[$imgArr['uid']];
											if(strlen($photoAuthor) > 23){
												$nameArr = explode(',',$photoAuthor);
												$photoAuthor = array_shift($nameArr);
											}
										}
										if($imgArr['occid']){
											$authorLink = '<a href="#" onclick="openIndPU('.$imgArr['occid'].');return false;">';
											if(!$photoAuthor){
												if($occArr[$imgArr['occid']]['recordedby']) $photoAuthor = $occArr[$imgArr['occid']]['recordedby'];
												else{
													if(strpos($occArr[$imgArr['occid']]['catnum'], $collArr[$occArr[$imgArr['occid']]['collid']]) !== 0)
														$photoAuthor = $collArr[$occArr[$imgArr['occid']]['collid']].': ';
													$photoAuthor .=  $occArr[$imgArr['occid']]['catnum'];
												}
											}
										}
										if(!$authorLink) $authorLink = $anchorLink;
										echo $authorLink.htmlspecialchars($photoAuthor).'</a>';
										?>
									</div>
								</div>
								<?php
							}
							echo "</div>";
							if($lastPage > $startPage){
								echo "<div style='clear:both;margin:5 0 5 0;'><hr /></div>";
								echo '<div style="width:100%;">'.$paginationStr.'</div>';
							}
							?>
							<div style="clear:both;"></div>
							<?php
						}
						else{
							echo '<h3>No images exist matching your search criteria. Please modify your search and try again.</h3>';
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
	include($SERVER_ROOT.'/includes/footer.php');
	?>
</body>
</html>