<?php
/**
 * エラー表示スクリプト
 */

$auth = $_GET['auth'] ?? '';
if ($auth !== 'show_errors_now') {
    http_response_code(403);
    die('Forbidden');
}

// PHPエラーを表示
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHPエラー表示テスト</h1>";

// APIテスト
echo "<h2>API疎通テスト</h2>";

require_once 'config.php';

echo "<p>Config loaded successfully</p>";

// APIキーの存在確認
$providers = ['openai', 'claude', 'gemini'];
echo "<h3>APIキー確認</h3><ul>";
foreach ($providers as $provider) {
    $key = getApiKey($provider);
    $exists = !empty($key);
    $masked = $exists ? substr($key, 0, 10) . '...' : 'なし';
    echo "<li>{$provider}: " . ($exists ? '✓' : '✗') . " ({$masked})</li>";
}
echo "</ul>";

// 簡易APIテスト
echo "<h3>簡易APIテスト（OpenAI）</h3>";
$openaiKey = getApiKey('openai');
if ($openaiKey) {
    $ch = curl_init('https://api.openai.com/v1/models');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $openaiKey
        ],
        CURLOPT_TIMEOUT => 5
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "<p>HTTP Status: {$httpCode}</p>";
    if ($httpCode === 200) {
        echo "<p style='color: green;'>✓ OpenAI API接続成功</p>";
    } else {
        echo "<p style='color: red;'>✗ OpenAI API接続失敗</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
    }
}

// 自己削除
@unlink(__FILE__);
?>
