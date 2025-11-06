<?php
/**
 * config.phpのAPIキー読み込みテスト
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== config.php APIキー読み込みテスト ===\n\n";

require_once 'config.php';

echo "1. getApiKeyFromEnv() テスト:\n";
$providers = ['openai', 'claude', 'gemini'];
foreach ($providers as $provider) {
    $key = getApiKeyFromEnv($provider);
    if ($key) {
        echo "   ✓ {$provider}: " . substr($key, 0, 10) . "...\n";
    } else {
        echo "   ✗ {$provider}: 取得できませんでした\n";
    }
}

echo "\n2. getApiKey() テスト:\n";
foreach ($providers as $provider) {
    $key = getApiKey($provider);
    if ($key) {
        echo "   ✓ {$provider}: " . substr($key, 0, 10) . "...\n";
    } else {
        echo "   ✗ {$provider}: 取得できませんでした\n";
    }
}

echo "\n3. 環境変数の存在確認:\n";
$envVars = [
    'OPENAI_API_KEY',
    'ANTHROPIC_API_KEY',
    'GOOGLE_AI_API_KEY'
];
foreach ($envVars as $var) {
    $value = getenv($var);
    if ($value) {
        echo "   ✓ {$var}: " . substr($value, 0, 10) . "...\n";
    } else {
        echo "   ✗ {$var}: 設定されていません\n";
    }
}

echo "\n4. .envファイルの確認:\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "   ✓ .envファイルが存在します\n";
    echo "   パス: {$envPath}\n";
    echo "   サイズ: " . filesize($envPath) . " bytes\n";
    echo "   読み取り可能: " . (is_readable($envPath) ? 'はい' : 'いいえ') . "\n";
} else {
    echo "   ✗ .envファイルが存在しません\n";
}

echo "\n5. index.phpのロジックシミュレーション:\n";
$hasApiKeys = false;
foreach ($providers as $provider) {
    if (getApiKey($provider)) {
        $hasApiKeys = true;
        break;
    }
}
echo "   hasApiKeys: " . ($hasApiKeys ? 'true' : 'false') . "\n";
echo "   リダイレクト先: " . ($hasApiKeys ? 'index.html' : 'setup.php') . "\n";
