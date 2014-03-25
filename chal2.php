<?php

include 'auth.php';

use OpenCloud\Compute\Constants\Network;
use OpenCloud\Compute\Constants\ServerState;

// creating cs object here to use in usage if required
$cs = $cloud->computeService('cloudServersOpenStack', 'LON');

// get arg list
$arguments = getopt("u:");
if (empty($arguments)) {
    echo "Usage: chal2.php -u UUID:\n";
    $serverlist = $cs->serverList();
    $num_servers = count($serverlist);
    echo "Here is a list of servers to choose from:\n";
    if ($num_servers == 0) {
        echo "No servers!\n";
    } else {
        while ($server = $serverlist->next()) {
            echo "$server->id\t$server->name\n";
        }
    }
    exit(1);
} else {
    $uuid = $arguments["u"];
}

// set timezone for date function
date_default_timezone_set('UTC');

// get server and kick off image
$srv = $cs->server($uuid);
$clone_name = $srv->name . '-' . date('Y-m-d-H:i');
echo "Creating image and server $clone_name...\n";
$srv->createImage($clone_name);

// find a store image object
$img = getImage($cs, $clone_name);

// wait for image to save
echo "Waiting for image to save...\n";
while (true) {
    sleep(30);
    if ($img->status != 'SAVING') {
        echo "Image saved!\n";
        break;
    }
    echo "Image status: $img->status\n";
    $img = getImage($cs, $clone_name);
}

// already have the $img, just need the flavour
foreach ($srv->flavor as $key => $val) {
    if ($key == 'id') {
        $build_flavour = $cs->flavor($val);
        break;
    }
}

// populate server array with relevant values
$srv_details = array(
    'name' => $clone_name,
    'image' => $img,
    'flavor' => $build_flavour,
    'networks' => array(
        $cs->network(Network::RAX_PUBLIC),
        $cs->network(Network::RAX_PRIVATE)
    )
);

// create new server object and kick off build
$srv = $cs->server();

// callback stuff - don't want any output as it's annoying
$callback = function($srv) {
    if (!empty($srv->error)) {
        var_dump($srv->error);
        exit(1);
    }
};

try {
    $srv->create($srv_details);
    echo "Waiting on $srv->name to finish building...\n";
    $srv->waitFor(ServerState::ACTIVE, 600, $callback);
    echo "Details for $srv->name:\n" .
    "PublicIP\t\tPassword\n" .
    $srv->ip() . "\t\t$srv->adminPass\n";
} catch (\Guzzle\Http\Exception\BadResponseException $e) {
    $responseBody = (string) $e->getResponse()->getBody();
    $statusCode = $e->getResponse()->getStatusCode();
    $headers = $e->getResponse()->getHeaderLines();
    echo sprintf("Status: %s\nBody: %s\nHeaders: %s", $statusCode, $responseBody, implode(', ', $headers));
}


function getImage($cs, $img_name) {
    $imagelist = $cs->imageList();
    while ($image = $imagelist->next()) {
        if (strpos($image->name, $img_name) !== false) {
            $img = $image;
        }
    }
    return $img;
}