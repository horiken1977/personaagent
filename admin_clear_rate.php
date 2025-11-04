<?php
/**
 * 管理者用レート制限クリアスクリプト
 * 本番環境で直接実行可能
 */

// セキュリティチェック（ローカルからのみ実行可能）
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);
$hasAuth = isset($_GET['auth']) && $_GET['auth'] === md5('clear_rate_limits_2025');

if (!$hasAuth) {
    http_response_code(403);
    die('Forbidden');
}

// レート制限ファイルの削除
$logsDir = __DIR__ . '/logs';
$deleted = [];

if (is_dir($logsDir)) {
    $files = glob($logsDir . '/rate_*.txt');
    foreach ($files as $file) {
        if (unlink($file)) {
            $deleted[] = basename($file);
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'deleted_count' => count($deleted),
    'deleted_files' => $deleted,
    'message' => count($deleted) > 0 ? 'Rate limit files cleared successfully' : 'No rate limit files found'
], JSON_PRETTY_PRINT);

// 自己削除
@unlink(__FILE__);
?>
