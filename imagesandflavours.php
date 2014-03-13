<?php

include 'auth.php';

// arguments accepted
$arguments = getopt("if");

if (empty($arguments)) {
    echo "Usage: imagesandflavours.php\n" .
    "\t-i:\tGet list of images\n" .
    "\t-f:\tGet list of flavours\n";
    exit(1);
} else {
    if (isset($arguments["i"])) {
        printImages($cloud);
    }
    if (isset($arguments["f"])) {
        printFlavours($cloud);
    }
}

function printImages($cloud) {
    // print a list of image names and associated IDs
    echo "\n\n ---- Available images ----\n";
    $cs = $cloud->computeService('cloudServersOpenStack', 'LON');
    $imagelist = $cs->imageList();
    while ($image = $imagelist->next()) {
        echo "$image->name\t$image->id\n";
    }
}

function printFlavours($cloud) {
    // print a list of flavours and associated IDs
    echo "\n\n ---- Available flavours ----\n";
    $cs = $cloud->computeService('cloudServersOpenStack', 'LON');
    $flavourlist = $cs->flavorList();
    while ($flavour = $flavourlist->next()) {
        echo "$flavour->name\t$flavour->id\n";
    }
}
