<?php
include_once('../../../config/symbini.php');

$requestType = '';
$requestData =  new \stdClass;
$requestData->type = false;
$clientData = array_key_exists('data',$_REQUEST)?$_REQUEST['data']:'';
$action = array_key_exists('action',$_REQUEST)?$_REQUEST['action']:'';
if (isset($GBIF_TESTMODE) && $GBIF_TESTMODE){
    $GBIF_url = 'https://api.gbif-uat.org/v1/';
}
else {
    $GBIF_url = 'https://api.gbif.org/v1/';
}


//TODO sanitize clientData

switch ($action){
    case "createGbifInstallation":
        $GBIF_url .= 'installation';
        $requestType = 'POST';
		$requestData->organizationKey = $clientData->organizationKey;
		$requestData->type = "SYMBIOTA_INSTALLATION";
		$requestData->title = $clientData->title;
        break;

    case "createGbifDataset":
        $GBIF_url .= 'dataset';
        $requestType = 'POST';
		$requestData->installationKey = $clientData->installationKey;
		$requestData->publishingOrganizationKey = $clientData->publishingOrganizationKey;
		$requestData->title  = $clientData->title;
		$requestData->type = "OCCURRENCE";
        break;

    case "createGbifEndpoint":
        $GBIF_url .= $clientData->datasetkey.'/dataset';
        $requestType = 'POST';
		$requestData->type = 'DWC_ARCHIVE';
        $requestData->url  = $clientData->dwcUri;
        break;
    
    case "datasetExists":
        $requestType = 'GET';
        $collectionIdentifierUrl = $_SERVER["SERVER_NAME"]."/collections/misc/collprofiles.php?collid=".$clientData->collid;
        $GBIF_url .= "dataset?identifier=" . $clientData->url;
        break;

    default:
        http_response_code(400); 
        echo '{"response":"No action set"}';
        exit;
}

$result = '';
$loginStr = $GBIF_USERNAME.':'.$GBIF_PASSWORD;
$requestHeaders = array(
    'Content-Type: application/json',
    'Accept: application/json'
);

$ch = curl_init($GBIF_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestType);

if ($requestData->type){
    $data = json_encode($requestData, JSON_FORCE_OBJECT);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $requestHeaders['Content-Length'] = strlen($data); 
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
if($requestType != 'GET'){
    curl_setopt($ch, CURLOPT_USERPWD, $loginStr);
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);

$result = curl_exec($ch);


echo str_replace('"','',$result);
?>