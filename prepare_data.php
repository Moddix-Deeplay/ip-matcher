<?php

use Moddix\IpMatcher\IpMatcher;

require_once './vendor/autoload.php';

$data = json_decode(file_get_contents(__DIR__ . '/ips.json'), true);

$ipSearch = new IpMatcher();
foreach ($data as $ip) {
    $ipSearch->addSubnet($ip);
}

$ipSearch->prepare();

$subnets = $ipSearch->getSubnets();

// ---

$json = json_encode($subnets);
file_put_contents(__DIR__ . '/data.json', $json);

//$serialize_string = serialize($subnets);
//file_put_contents(__DIR__ . '/data.txt', $serialize_string);

//file_put_contents('data.php', '<?php return ' . var_export($subnets, true) . ';');
