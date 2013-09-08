<?php

require("./tor.class.php");

$tor = new Tor ;

$tor->authenticate();

$options = array(
	array('name'=> 'Nickname',            'value' => $tor->getconf("Nickname")),
	array('name'=> 'ORPort',              'value' => $tor->getconf("QRPort")),
	array('name'=> 'RelayBandwidthBurst', 'value' => $tor->getconf("RelayBandwidthBurst")),
	array('name'=> 'RelayBandwidthRate',  'value' => $tor->getconf("RelayBandwidthRate"))
);


$response = "peau de balle";

if((trim($tor->getconf("BridgeRelay")) == "0")
&& (trim($tor->getconf("ExitPolicy")) == "")
&& (trim($tor->getconf("ORPort")) == "0")) {
	$response =  array(
          'msg'         => 'Tor is setup as client only...',
          'tormode'     => 'client only',
          'trafficmode' => '100% available',
          'mode'        => '1'
        );
} elseif((trim($tor->getconf("BridgeRelay")) == "0")
&& (trim($tor->getconf("ExitPolicy")) == "reject *:*")
&& (trim($tor->getconf("ORPort")) != "0")) {
	$response = array(
          'msg'         => 'Traffic relayed inside the Tor network (non-exit relay)...',
          'tormode'     => 'non exit relay',
          'trafficmode' => '100% used',
          'mode'        => '2'
        );
} elseif((trim($tor->getconf("BridgeRelay")) == "0")
&& (trim($tor->getconf("ExitPolicy")) == "")
&& (trim($tor->getconf("ORPort")) != "0")) {
	$response = array(
          'msg'         => 'Traffic relayed inside the Tor network (exit relay)...',
          'tormode'     => 'exit relay',
          'trafficmode' => '100% used',
          'mode'        => '3'
        );
} elseif((trim($tor->getconf("BridgeRelay")) == "1")
&& (trim($tor->getconf("ExitPolicy")) == "reject *:*")
&& (trim($tor->getconf("ORPort")) != "0")) {
	$response = array(
          'msg'         => 'Traffic relayed TO the Tor network...',
          'tormode'     => 'tor relay',
          'trafficmode' => '100% used',
          'mode'        => '4'
        );
} else {
	$response = array(
          'msg'   => 'The config isn\'t correctly set...',
          'error' => true
        );
}

$response['options'] = $options;

$tor->close();

die(json_encode(array('response' => $response)));
