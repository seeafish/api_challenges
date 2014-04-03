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
    
    // sanitise disk size by stripping out non numbers
    $disk_size = preg_replace('/[^0-9*]/', '', $arguments["d"]);
    if (!($disk_size >= 1 && $disk_size <= 150)) {
        echo "Incorrect disk size, please enter a number between 1 - 150 (GB)\n";
        exit(1);
    }
        
    // convert flavour
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
    } else {
        $db_name = $arguments["i"] . 'db1';
    }
    if (isset($arguments["u"])) {
        $db_user = $arguments["u"];
    } else {
        $db_user = $db_name . 'user1';
    }
    if (isset($arguments["p"])) {
        $db_pass = $arguments["p"];
    } else {
        $db_pass = genPassword();
    }
}

// create instance
echo "Creating instance $instance_name\n";
$db = $cloud->databaseService('cloudDatabases', 'LON');
$db_instance = $db->instance();

$db_instance->name = $instance_name;
$db_instance->volume = new stdClass();
$db_instance->volume->size = $disk_size;
$db_instance->flavor = $db->flavor($flavour);
$db_instance->create();

echo "Waiting for instance to finish building...\n";
$db_instance->waitFor(State::ACTIVE, null, function ($db_instance) {
    echo "Status: $db_instance->status\n";
});

// create db and user
echo "Creating new database. Connections details:\n" .
"\nInstance hostname: $db_instance->hostname\n" .
"DB name: $db_name\n" .
"DB user: $db_user\n" .
"DB password: $db_pass\n\n";

$new_db = $db_instance->database();
$new_db->name = $db_name;
$new_db->create();

// could do something with $db_instance->databaseList() here
// to check if the new db is there before a user is created
// but the existing functionality seemingly works fine

$new_user = $db_instance->user();
$new_user->name = $db_user;
$new_user->password = $db_pass;
$new_user->databases = array($db_name);
$new_user->create();

echo "Done!\n";


// why reinvent the wheel...
function genPassword($length = 12) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@$&*-=+?";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}
