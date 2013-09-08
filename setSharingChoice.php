<?php

require("./tor.class.php");

$tor = new Tor ;

$tor->authenticate();

$response = "peau de balle";

if(!isset($_GET['choice'])) {
  die("make a choice !");
}


switch($_GET['choice']) {

  case '1':
	if(($tor->setconf("BridgeRelay","0"))
	&& ($tor->setconf("ExitPolicy",""))
	&& ($tor->setconf("Nickname",""))
	&& ($tor->setconf("ORPort","0"))
	&& ($tor->setconf("RelayBandwidthBurst",""))
	&& ($tor->setconf("RelayBandwidthRate",""))
	&& ($tor->saveconf())) {
		$response =  "Tor shall be used as client only...";

	} else {
		$response = "There has been an error setting that option..."; 
	}
  break;

  case '2':
    if(isset($_GET['Nickname'], $_GET['ORPort'], $_GET['RelayBandwidthBurst'], $_GET['RelayBandwidthRate'])) {

	if(($tor->setconf("BridgeRelay","0"))
	&& ($tor->setconf("ExitPolicy","reject *:*"))
	&& ($tor->setconf("Nickname",$_GET['Nickname']))
	&& ($tor->setconf("ORPort",$_GET['ORPort']))
	&& ($tor->setconf("RelayBandwidthBurst",$_GET['RelayBandwidthBurst']))
	&& ($tor->setconf("RelayBandwidthRate",$_GET['RelayBandwidthRate']))
	&& ($tor->saveconf())) {
		$response =  "Traffic will be relayed inside the Tor network (non-exit relay)...";

	} else {
		 $response = "There has been an error setting that option..."; 
	}

    }
  break;

  case '3':
    if(isset($_GET['Nickname'], $_GET['ORPort'], $_GET['RelayBandwidthBurst'], $_GET['RelayBandwidthRate'])) {

	if(($tor->setconf("BridgeRelay","0"))
	&& ($tor->setconf("ExitPolicy",""))
	&& ($tor->setconf("Nickname",$_GET['Nickname']))
	&& ($tor->setconf("ORPort",$_GET['ORPort']))
	&& ($tor->setconf("RelayBandwidthBurst",$_GET['RelayBandwidthBurst']))
	&& ($tor->setconf("RelayBandwidthRate",$_GET['RelayBandwidthRate']))
	&& ($tor->saveconf())) {
		$response =  "Traffic will be relayed inside the Tor network (exit relay)...";
	} else {
		$response = "There has been an error setting that option..."; 
	}
    }
  break;

  case '4':
    if(isset($_GET['Nickname'], $_GET['ORPort'], $_GET['RelayBandwidthBurst'], $_GET['RelayBandwidthRate'])) {

	if(($tor->setconf("BridgeRelay","1"))
	&& ($tor->setconf("ExitPolicy","reject *:*"))
	&& ($tor->setconf("Nickname",$_GET['Nickname']))
	&& ($tor->setconf("ORPort",$_GET['ORPort']))
	&& ($tor->setconf("RelayBandwidthBurst",$_GET['RelayBandwidthBurst']))
	&& ($tor->setconf("RelayBandwidthRate",$_GET['RelayBandwidthRate']))
	&& ($tor->saveconf())) {
		$response =  "Traffic will be relayed TO the Tor network...";
	} else {
		$response = "There has been an error setting that option..."; 
	}
    }
  break;

  default:
    die("bad choice");

}


$tor->close();

die($response);
