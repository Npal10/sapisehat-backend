<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$apiKey = config('services.gemini.api_key');

function testModel($model, $apiKey) {
    echo "Testing $model...\n";
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
    
    $payload = [
        "contents" => [
            ["parts" => [["text" => "Hello!"]]]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP $httpcode\n";
    $json = json_decode($response, true);
    if ($httpcode == 200) {
        echo "Success!\n\n";
    } else {
        echo $response . "\n\n";
    }
}

testModel('gemini-1.5-flash', $apiKey);
testModel('gemini-1.5-pro', $apiKey);
