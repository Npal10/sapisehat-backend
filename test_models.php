<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$key = config('services.gemini.api_key');
$modelsToTest = ['gemini-3.5-flash', 'gemini-3-pro-preview', 'gemini-2.0-flash-001', 'gemini-2.0-flash-lite-001', 'gemini-omni-flash-preview'];

foreach($modelsToTest as $model) {
    $response = Illuminate\Support\Facades\Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $key, [
        'contents' => [
            [
                'parts' => [
                    ['text' => 'Hello']
                ]
            ]
        ]
    ]);
    echo str_pad($model, 25) . " -> " . $response->status() . "\n";
}
