<?php
// This file validates that a "splash" instance exists.
// If not, an alert is raised.
if (!isset($_REQUEST["selfedit"]) || $_REQUEST["selfedit"]!="true") {
	require_once '../../../../../Mouf.php';
	$selfedit="false";
} else {
	require_once '../../../../../mouf/MoufManager.php';
	MoufManager::initMoufManager();
	require_once '../../../../../MoufUniversalParameters.php';
	require_once '../../../../../mouf/MoufAdmin.php';
	$selfedit="true";
}

// Note: checking rights is done after loading the required files because we need to open the session
// and only after can we check if it was not loaded before loading it ourselves...
require_once '../../../../../mouf/direct/utils/check_rights.php';

$jsonObj = array();

$instanceExists = MoufManager::getMoufManager()->instanceExists('rightsService');

if ($instanceExists) {
	$jsonObj['code'] = "ok";
	$jsonObj['html'] = "'rightsService' instance found";
} else {
	$jsonObj['code'] = "warn";
	$jsonObj['html'] = "Unable to find the 'rightsService' instance. 
If you plan to use the RightsService package, it is usually a good idea to create an instance of the MoufRightService class (or a subclass) named 'rightsService'. Click here to <a href='".ROOT_URL."mouf/mouf/newInstance?instanceName=rightsService&instanceClass=MoufRightService&selfedit=".$selfedit."'>create an instance of the MoufRightService class named 'rightsService'</a>.";
}

echo json_encode($jsonObj);
exit;

?>