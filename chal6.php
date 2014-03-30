<?php

include 'auth.php';

use OpenCloud\ObjectStore\Resource\DataObject;

$cf = $cloud->objectStoreService('cloudFiles', 'LON');

$arguments = getopt("n:");

if (empty($arguments)) {
    echo "Usage: php chal6.php -n <name>:\n" .
    "\t-n: name of CDN container to create\n";
    exit(1);
} else {
    $cont_name = $arguments["n"];
}

echo "Creating container $cont_name...\n";
$container = $cf->createContainer($cont_name);
echo "Enabling CDN on container $cont_name...\n";
$container->enableCdn();
