<?php

// Load Composer autoloader.
require 'vendor/autoload.php';

// Use declaration.
use SparkyApiClient\SparkyApiClient;

// Try to execute the API request.
try {
    // Initialize.
    $sparkyApi = new SparkyApiClient('SPARKY_API_HOST', 'SPARKY_API_KEY');
    
    // Do a request.
    $sparkyApi->request('post', '/endpoint', [
        'foo' => 'Bar',
        'biz' => 'Buz',
    ]);

    // Get the request response.
    $requestResponse = $sparkyApi->getResponse();

    // Dump.
    var_dump($requestResponse);
}
catch (Exception $e) {
    // Show error message.
    echo 'Error: ' . $e->getMessage();
}
