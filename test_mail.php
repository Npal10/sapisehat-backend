<?php
$host = 'smtp-relay.brevo.com';
$port = 2525;
$timeout = 10;
$connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

if (is_resource($connection)) {
    echo "SUCCESS: Connected to $host on port $port\n";
    
    // Read the greeting
    $response = fgets($connection, 515);
    echo "Server said: $response";
    
    // Send EHLO
    fwrite($connection, "EHLO localhost\r\n");
    echo fgets($connection, 515);
    
    fclose($connection);
} else {
    echo "FAILED: Could not connect to $host on port $port. Error: $errstr ($errno)\n";
}
