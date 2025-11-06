<?php
/**
 * Gemini利用可能モデル一覧取得
 */

header('Content-Type: text/plain; charset=utf-8');

require_once 'config.php';

$apiKey = getApiKey('gemini');
if (!$apiKey) {
    echo "✗ Gemini APIキーが取得できません\n";
    exit(1);
}

echo "=== Gemini利用可能モデル一覧 ===\n\n";

// ListModels API
$url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $apiKey;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "✗ エラー (HTTP {$httpCode}): {$response}\n";
    exit(1);
}

$data = json_decode($response, true);

if (!isset($data['models'])) {
    echo "✗ モデル一覧が取得できませんでした\n";
    exit(1);
}

echo "利用可能なモデル:\n\n";

foreach ($data['models'] as $model) {
    $name = $model['name'] ?? 'N/A';
    $displayName = $model['displayName'] ?? 'N/A';
    $supportedMethods = $model['supportedGenerationMethods'] ?? [];

    echo "モデル名: {$name}\n";
    echo "表示名: {$displayName}\n";
    echo "サポートメソッド: " . implode(', ', $supportedMethods) . "\n";

    // generateContent がサポートされているか
    if (in_array('generateContent', $supportedMethods)) {
        echo "✓ generateContent対応\n";
    }

    echo "\n";
}
