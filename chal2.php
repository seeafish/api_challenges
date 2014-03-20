<?php
// Outline:
// 1. Take UUID as argument and snapshot the server
// 2. If not UUID is specified, print a list of available servers
// 3. Error handling the imageList loop if no image is found? Maybe...

include 'auth.php';

use OpenCloud\Compute\Constants\Network;

$cs = $cloud->computeService('cloudServersOpenStack', 'LON');

// get arg list
$arguments = getopt("u:");
if (empty($arguments)) {
    echo 'Usage: chal2.php -u UUID:\n';
    $serverlist = $cs->serverList();
    echo "Here is a list of servers to choose from:\n";
    while ($server = $serverlist->next()) {
        echo "$server->id\t$server->name\n";
    }
    exit(1);
} else {
    if (!empty($arguments["u"])) {
        $uuid = $arguments["u"];
    } else {
        echo "Please specify a server UUID to clone\n";
        exit(1);
    }
}

// set timezone for date function
date_default_timezone_set('UTC');

// get server and kick off image
$srv = $cs->server($uuid);
$clone_name = $srv->name . '-' . date('Y-m-d-H:i');
//$srv->createImage($clone_name);

// find image and store object
$imagelist = $cs->imageList();
while ($image = $imagelist->next()) {
    //if (strpos($image->name, $clone_name) !== false) {
    if (strpos($image->name, 'web1-2014-03-20-15:26') !== false) {
        $img = $image;
    }
}

// wait for image to finish
while ($img->status == 'SAVING') {
    echo "saving...\n";
    sleep(10);
}