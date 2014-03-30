<?php

include 'auth.php';

use OpenCloud\ObjectStore\Resource\DataObject;

$cf = $cloud->objectStoreService('cloudFiles', 'LON');

$arguments = getopt("c:d:l");

if (empty($arguments)) {
    echo "Usage: php chal3.php -c <container> -d <directory> [-l]:\n" .
    "\t-c: container to upload to (creates if not present)\n" .
    "\t-d: directory to upload from\n" .
    "\t-l: list all available containers\n";
    exit(1);
} else {
    $cont_name = $arguments["c"];
    $dir_name = $arguments["d"];
        
    if (!isset($cont_name) && !isset($dir_name)) {
        if (isset($arguments["l"])) {
            $containerlist = $cf->listContainers();
            while ($container = $containerlist->next()) {
                echo "$container->name\n";
            }
            exit(1);
        }
    }
}

// store files in an array
$i = 0;
if (!is_dir($dir_name)) {
    echo "Invalid directory!\n";
    exit(1);
} elseif ($dh = opendir($dir_name)) {
    // sanitise last slash
    if (substr($dir_name, -1) == "/") {
        $dir_name = substr($dir_name, 0, -1);
    }
    while (($file = readdir($dh)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        $objects[$i]["name"] = $file;
        $objects[$i]["path"] = $dir_name . '/' . $file;
        $i++;
    }
    closedir($dh);
}

$container = $cf->getContainer($cont_name);
echo "Uploading files from $dir_name into container $cont_name...\n";
$container->uploadObjects($objects);