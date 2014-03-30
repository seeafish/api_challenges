<?php

include 'auth.php';

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
    if (!is_dir($dir_name)) {
        echo "Invalid directory!\n";
        exit(1);
    }
    
    if (!isset($cont_name) && !isset($dir_name)) {
        if (isset($arguments["l"])) {
            $containerlist = $cf->listContainers();
            while ($container = $containerlist->next()) {
                echo "$container->name\n";
            }
        }
    }
}


// start everything off
echo "Uploading $dir_name into $cont_name...\n";

// store files in an array
$i = 0;
$dh = opendir($dir_name);
while (($file = readdir($dh)) !== false) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    $objects[$i] = $file;
    $i++;
}
closedir($dh);

var_dump($objects);