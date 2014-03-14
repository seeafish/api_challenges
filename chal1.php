<?php

include 'auth.php';

use OpenCloud\Compute\Constants\Network;

// get arg list
$arguments = getopt("h:n::");
if (empty($arguments)) {
    echo "Usage: chal1.php -h [-n]:\n" .
    "\t-h:\tHostname of servers\n" .
    "\t-n:\tNumber of servers to create (default: 3)\n";
    exit(1);
} else {
    if (!empty($arguments["h"])) {
        $hostname = $arguments["h"];
    } else {
        echo "Please enter a hostname\n";
        exit(1);
    }

    if (!empty($arguments["n"])) {
        $num = $arguments["n"];
    } else {
        $num = 3;
    }
}

// creating the $cs object here if the scripts ever needs to
// be able to customise the image/flavour
$cs = $cloud->computeService('cloudServersOpenStack', 'LON');

// get images and flavours
$images = $cs->imageList();
$flavours = $cs->flavorList();

// as much I hate this, loop through and find the objects required
while ($image = $images->next()) {
	if (strpos($image->id, 'bbb6b40f-fc70-4e7e-ab63-42b4a0d47997') !== false) {
        $build_image = $image;
        break;
    }
}

$flavours = $cs->flavorList();
while ($flavour = $flavours->next()) {
	if (strpos($flavour->name, '512MB Standard Instance') !== false) {
		$build_flavour = $flavour;
		break;
	}
}

// populate array of servers to build
echo "Creating $num $build_image->name $build_flavour->name servers named\n";
for ($i = 1; $i <= $num; $i++) {
    echo "$hostname$i\n";

    // populate an array to create servers
    $srv_details[$i] = array(
        'name' => $hostname.$i,
        'image' => $build_image,
        'flavor' => $build_flavour,
        'networks' => array(
            $cs->network(Network::RAX_PUBLIC),
            $cs->network(Network::RAX_PRIVATE)
        )
    );
}

// instantiate the $srv object for creation and polling
$srv = $cs->server();

foreach ($srv_details as $server) {
    try {
        $srv->create($server);
    } catch (\Guzzle\Http\Exception\BadResponseException $e) {
        $responseBody = (string) $e->getResponse()->getBody();
        $statusCode = $e->getResponse()->getStatusCode();
        $headers = $e->getResponse()->getHeaderLines();
        echo sprintf("Status: %s\nBody: %s\nHeaders: %s", $statusCode, $responseBody, implode(', ', $headers));
    }
}
