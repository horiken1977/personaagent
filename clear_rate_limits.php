<?php
/**
 * レート制限ファイルをクリアするユーティリティ
 * セキュリティ: このファイルは使用後すぐに削除してください
 */

// 認証トークン（使用後は削除）
$AUTH_TOKEN = bin2hex(random_bytes(16));
file_put_contents(__DIR__ . '/auth_token.txt', $AUTH_TOKEN);

// リクエストのトークン確認
$requestToken = $_GET['token'] ?? '';
$savedToken = file_exists(__DIR__ . '/auth_token.txt')
    ? trim(file_get_contents(__DIR__ . '/auth_token.txt'))
    : '';

if (empty($savedToken) || $requestToken !== $savedToken) {
    http_response_code(403);
    die('Unauthorized');
}

// レート制限ファイルの削除
$logsDir = __DIR__ . '/logs';
$rateFiles = glob($logsDir . '/rate_*.txt');

if (empty($rateFiles)) {
    echo "レート制限ファイルが見つかりません。\n";
    exit;
}

$deleted = 0;
foreach ($rateFiles as $file) {
    if (unlink($file)) {
        $deleted++;
        echo "削除: " . basename($file) . "\n";
    }
}

echo "\n合計 {$deleted} 個のレート制限ファイルを削除しました。\n";
echo "\n重要: このスクリプトと auth_token.txt を今すぐ削除してください！\n";

// 自己削除（オプション）
if (isset($_GET['self_destruct']) && $_GET['self_destruct'] === 'yes') {
    @unlink(__DIR__ . '/auth_token.txt');
    @unlink(__FILE__);
    echo "\nスクリプトとトークンファイルを削除しました。\n";
}
?>
