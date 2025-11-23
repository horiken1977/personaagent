<?php
/**
 * チャットAPI（api.php）の直接テスト
 * 実際のチャット画面と同じリクエストをシミュレート
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== チャットAPI (api.php) テスト ===\n\n";

// api.phpへPOSTリクエストを送信
$apiUrl = 'https://mokumoku.sakura.ne.jp/persona/api.php';

$testData = [
    'provider' => 'gemini',
    'prompt' => 'こんにちは。あなたは誰ですか？',
    'personaId' => 1
];

echo "リクエストURL: {$apiUrl}\n";
echo "リクエストデータ:\n";
echo json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30
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
        echo "エラーメッセージ: " . $responseData['error'] . "\n";
    }
} else {
    if (isset($responseData['response'])) {
        echo "✓ API呼び出し成功\n";
        echo "応答テキスト: " . substr($responseData['response'], 0, 200) . "...\n";
    } else {
        echo "✗ 予期しないレスポンス形式\n";
    }
}
