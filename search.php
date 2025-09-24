<?php

use Moddix\IpMatcher\IpMatcher;

require_once './vendor/autoload.php';

$data = json_decode(file_get_contents('data.json'), true);
$target = '77.88.2.1';

$ipSearcher = new IpMatcher();
$result = $ipSearcher->contains($target, $data);

var_dump(compact('target', 'result'));
