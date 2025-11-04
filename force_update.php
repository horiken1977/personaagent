<?php
/**
 * 強制更新スクリプト - 古いファイルを削除してキャッシュをクリア
 */

$auth = $_GET['auth'] ?? '';
if ($auth !== md5('force_update_2025')) {
    http_response_code(403);
    die('Forbidden');
}

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'actions' => []
];

// chat.jsのタイムスタンプを更新
if (file_exists(__DIR__ . '/chat.js')) {
    touch(__DIR__ . '/chat.js');
    $result['actions'][] = 'chat.js のタイムスタンプを更新';
}

// api.phpのタイムスタンプを更新
if (file_exists(__DIR__ . '/api.php')) {
    touch(__DIR__ . '/api.php');
    $result['actions'][] = 'api.php のタイムスタンプを更新';
}

// レート制限ファイルをすべて削除
$logsDir = __DIR__ . '/logs';
$rateFiles = glob($logsDir . '/rate_*.txt');
foreach ($rateFiles as $file) {
    if (unlink($file)) {
        $result['actions'][] = 'レート制限ファイル削除: ' . basename($file);
    }
}

$result['success'] = true;
$result['message'] = count($result['actions']) . ' 件の操作を実行しました';

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// 自己削除
@unlink(__FILE__);
?>
