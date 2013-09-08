<?php
// Run from command prompt > php -q websocket.demo.php

$ips = getIPs();
function getIPs($withV6 = true) {
    preg_match_all('/inet'.($withV6 ? '6?' : '').' addr: ?([^ ]+)/', `ifconfig`, $ips);
    return $ips[1];
}


// Basic WebSocket demo echoes msg back to client
include "websocket.class.php";
$master = new WebSocket(gethostbyname($ips[0]),8081);


