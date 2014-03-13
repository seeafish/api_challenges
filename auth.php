<?php

include 'credentials.php';
require 'vendor/autoload.php';

use OpenCloud\Rackspace;

// using US_IDENTITY_ENDPOINT since it's global anyway...
$cloud = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => getenv('username'),
    'apiKey'   => getenv('apiKey')
));
