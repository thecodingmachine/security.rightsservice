<?php
/*
 * Copyright (c) 2012 David Negrier
 *
 * See the file LICENSE.txt for copying permission.
 */

require_once __DIR__."/../../../autoload.php";

use Mouf\Actions\InstallUtils;
use Mouf\MoufManager;

// Let's init Mouf
InstallUtils::init(InstallUtils::$INIT_APP);

// Let's create the instance
$moufManager = MoufManager::getMoufManager();
if (!$moufManager->instanceExists("rightsService")) {
	$rightsService = $moufManager->createInstance("Mouf\\Security\\RightsService\\MoufRightService");
	$rightsService->setName("rightsService");
	if ($moufManager->instanceExists("errorLogLogger")) {
		$rightsService->getProperty("log")->setValue($moufManager->getInstanceDescriptor("errorLogLogger"));
	}
	
	if ($moufManager->instanceExists("userService")) {
		$userService = $moufManager->getInstanceDescriptor("userService");
		$rightsService->getProperty("userService")->setValue($userService);
		
		$prevValues = $userService->getProperty('authenticationListeners')->getValue();
		$prevValues[] = $rightsService;
		$userService->getProperty('authenticationListeners')->setValue($prevValues);
		
	}
	
}

// Let's rewrite the MoufComponents.php file to save the component
$moufManager->rewriteMouf();

// Finally, let's continue the install
InstallUtils::continueInstall();
?>