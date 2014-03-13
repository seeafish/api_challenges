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

echo "Creating $num Ubuntu 13.10 512MB servers named\n";
for ($i = 1; $i <= $num; $i++) {
    echo "$hostname$i\n";

    // populate an array to create servers
    $srv_details[$i] = array(
        'name' => $hostname.$i,
        'image' => '5a8759da-dca8-40ce-86fa-fa029a58a999',
        'flavor' => 2,
        'networks' => array(
            $cs->network(Network::RAX_PUBLIC),
            $cs->network(Network::RAX_PRIVATE)
        )
    );
}

// instantiate the $srv object for creation and polling
$srv = $cs->server();
$response = $srv->create($srv_details[2]);

