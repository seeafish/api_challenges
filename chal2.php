<?php

include 'auth.php';

use OpenCloud\Compute\Constants\Network;

$cs = $cloud->computeService('cloudServersOpenStack', 'LON');

// get arg list
$arguments = getopt("u:");
if (empty($arguments)) {
    echo "Usage: chal2.php -u UUID:\n";
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
echo "Creating image and server $clone_name...\n";
#$srv->createImage($clone_name);

// find a store image object
#$img = getImage($cs, $clone_name);
$img = getImage($cs, 'web1-2014-03-22-09:48');

// wait for image to save
echo "Waiting for image to save...\n";
while (true) {
    #sleep(30);
    sleep(2);
    if ($img->status != 'SAVING') {
        echo "Image saved!\n";
        break;
    }
    echo "Image status: $img->status\n";
    #$img = getImage($cs, $clone_name);
    $img = getImage($cs, 'web1-2014-03-22-09:48');
}

// create server
$srv = $cs->server();



function getImage($cs, $img_name) {
    $imagelist = $cs->imageList();
    while ($image = $imagelist->next()) {
        if (strpos($image->name, $img_name) !== false) {
            $img = $image;
        }
    }
    return $img;
}