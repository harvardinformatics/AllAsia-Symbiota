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
        include_once($SERVER_ROOT.'/includes/googleanalytics.php');
        ?>
        <link href="css/quicksearch.css" type="text/css" rel="Stylesheet" />
        <script src="js/jquery-3.2.1.min.js" type="text/javascript"></script>
        <script src="js/jquery-ui-1.12.1/jquery-ui.min.js" type="text/javascript"></script>
        <script type="text/javascript">
                var clientRoot = "<?php echo $CLIENT_ROOT; ?>";
        </script>
        <script src="js/symb/api.taxonomy.taxasuggest.js" type="text/javascript"></script>
        <style>
                #slideshowcontainer{
                        border: 2px solid black;
                        border-radius:10px;
                        padding:10px;
                        margin-left: auto;
                        margin-right: auto;
                }
        </style>
</head>
<body>
        <?php
        include($SERVER_ROOT.'/includes/header.php');
        ?>
        <!-- This is inner text! -->
        <div id="innertext">
                <p>Asia is the largest continent on Earth, and includes the world’s tallest mountains, lowest landscapes, and habitats ranging from arctic tundra to tropical 
                        rainforests and mangroves to deserts. The plants of this region are incredibly diverse in their identities and functions. More than one-third of the 
                        world’s 350,000 plant species grow in Asia and include tiny alpine cushion plants, medicinal herbs, ancient crops, and some of the planet’s tallest 
                        rainforest trees. But documentation of this diversity remains inaccessible and research about it is difficult because most herbarium specimens of 
                        Asian plants have not been digitized. The All Asia Thematic Collections Network (TCN) will mobilize online 15 million specimens of Asian plants 
                        currently housed in the US and around the world. The project will especially focus on digitizing specimens from the unique and critically endangered 
                        biodiversity hotspots of Southeast Asia and the Himalaya-Hengduan region. These mobilized digital data will accelerate research to conserve 
                        endangered plant species and understand the interacting effects of evolution and global environmental change on plant species diversity.
                </p>
        </div>
        <?php
        include($SERVER_ROOT.'/includes/footer.php');
        ?>
</body>
</html>
