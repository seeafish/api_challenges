<?php

include 'auth.php';

use OpenCloud\Common\Constants\State;

$arguments = getopt("i:d:s:n:u:p:");

if (empty($arguments["i"]) || empty($arguments["d"]) || empty($arguments["s"])) {
    echo "Usage: php chal5.php -i <instance name> -d <disk size> -s <instance size> " .
    "[-n <db name>] [-u <username>] [-p <password>]\n" .
    "\t-i: name of instance\n" .
    "\t-d: size of instance disk (1 - 150 - in GB)\n" .
    "\t-s: memory size (512MB, 1GB, 2GB, 4GB, 8GB, 16GB)\n" .
    "\t-n: name of db\n" .
    "\t-u: db username\n" .
    "\t-p: db user password\n";
    exit(1);
} else {
    $instance_name = $arguments["i"];
    $disk_size = $arguments["d"];
    
    switch (strtolower($arguments["s"])) {
        case "512mb":
            $flavour = 1;
            break;
        case "1gb":
            $flavour = 2;
            break;
        case "2gb":
            $flavour = 3;
            break;
        case "4gb":
            $flavour = 4;
            break;
        case "8gb":
            $flavour = 5;
            break;
        case "16gb":
            $flavour = 6;
            break;
        default:
            echo "Invalid size, please use one of 512MB, 1GB, 2GB, 4GB, 8GB, 16GB\n";
            exit(1);
    }

    if (isset($arguments["n"])) {
        $db_name = $arguments["n"];
    }
    if (isset($arguments["u"])) {
        $db_user = $arguments["u"];
    }
    if (isset($arguments["p"])) {
        $db_pass = $arguments["p"];
    }
}
echo "$instance_name\n$disk_size\n$flavour\n$db_name\n$db_user\n$db_pass\n";
exit;
