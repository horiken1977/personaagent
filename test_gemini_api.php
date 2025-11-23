<?php
/**
 * Gemini API直接テスト
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Gemini API直接テスト ===\n\n";

require_once 'config.php';

// APIキー取得
$apiKey = getApiKey('gemini');
if (!$apiKey) {
    echo "✗ Gemini APIキーが取得できません\n";
    exit(1);
}

echo "✓ APIキー取得成功: " . substr($apiKey, 0, 10) . "...\n\n";

// config.phpからエンドポイントを取得
$providers = getConfig('llm_providers');
$endpoint = $providers['gemini']['endpoint'];
$url = $endpoint . '?key=' . $apiKey;

echo "エンドポイント: {$endpoint}\n";
echo "モデル: " . $providers['gemini']['model'] . "\n\n";

// テストリクエスト
$data = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => 'こんにちは！テストメッセージです。'
                ]
            ]
        ]
    ],
    'generationConfig' => [
        'maxOutputTokens' => 100,
        'temperature' => 0.7
    ]
];

echo "リクエストデータ:\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// cURL実行
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true
]);

echo "APIリクエスト送信中...\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTPステータスコード: {$httpCode}\n\n";

if ($curlError) {
    echo "✗ cURLエラー: {$curlError}\n";
    exit(1);
}

echo "レスポンス:\n";
echo $response . "\n\n";

$responseData = json_decode($response, true);

if ($httpCode >= 400) {
    echo "✗ APIエラー\n";
    if (isset($responseData['error'])) {
        echo "エラーコード: " . ($responseData['error']['code'] ?? 'N/A') . "\n";
        echo "エラーメッセージ: " . ($responseData['error']['message'] ?? 'N/A') . "\n";
        echo "エラー詳細: " . json_encode($responseData['error'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    exit(1);
}

if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    echo "✓ API呼び出し成功\n";
    echo "応答テキスト: " . $responseData['candidates'][0]['content']['parts'][0]['text'] . "\n";
} else {
    echo "✗ 予期しないレスポンス形式\n";
    echo "レスポンス構造: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
