<?php

include 'auth.php';

$dns = $cloud->dnsService('cloudDNS', 'LON');

$arguments = getopt("d:r:i:");

if (empty($arguments["d"]) || empty($arguments["r"]) || empty($arguments["i"])) {
    echo "Usage: php chal4.php -d <domain to update> -r <record (prepended to domain)> " .
    "-i <ip address>\n" .
    "\texample: php chal4.php -d example.com -r news -i 1.2.3.4\n" . 
    "\tcreates news.example.com pointing to 1.2.3.4\n";
    exit(1);
} else {
    $current_domain = $arguments["d"];
    // check if domain exists on account
    $domainlist = $dns->domainList();
    while ($dom = $domainlist->next()) {
        if ($dom->name == $current_domain) {
            $dom_id = $dom->id;
        } else {
            echo "Invalid domain\n";
            exit(1);
        }
    }
    $fqdn = $arguments["r"] . '.' . $current_domain;
    $ip = $arguments["i"];
}

// create record
echo "Creating A record $fqdn to point to $ip...\n";
$domain = $dns->domain($dom_id);
$record = $domain->record(array(
    'type' => 'A',
    'name' => $fqdn,
    'data' => $ip
));
$record->create();

echo "Done!\n";
