<?php

// Load Composer autoloader.
require 'vendor/autoload.php';

// Use declaration.
use SparkyApiClient\SparkyApiClient;

// Try to execute the API request.
try {
    // Initialize with signature key (private key) for signed requests.
    $sparkyApi = new SparkyApiClient('SPARKY_API_HOST', 'SPARKY_API_KEY', 'SPARKY_API_SIGNATURE_KEY');
    
    // Do a request (will be automatically signed).
    $sparkyApi->request('post', '/endpoint', [
        'foo' => 'Bar',
        'biz' => 'Buz',
    ]);
    
    // Get the request response.
    $requestResponse = $sparkyApi->getRequestResponse();
    
    // Dump.
    var_dump($requestResponse);
}
catch (Exception $e) {
    // Show error message.
    echo 'Error: ' . $e->getMessage();
}
