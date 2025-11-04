<?php
/**
 * レート制限ファイルをクリアするユーティリティ
 * セキュリティ: このファイルは使用後すぐに削除してください
 */

// 簡易的な認証（IPアドレスベース - より安全な方法を推奨）
$confirmToken = $_GET['confirm'] ?? '';

if ($confirmToken !== 'clear_all_rate_limits_now') {
    http_response_code(403);
    echo json_encode([
        'error' => 'Unauthorized',
        'message' => '認証が必要です。URLに ?confirm=clear_all_rate_limits_now を追加してください。'
    ]);
    exit;
}

// レート制限ファイルの削除
$logsDir = __DIR__ . '/logs';
$rateFiles = glob($logsDir . '/rate_*.txt');

$result = [
    'success' => true,
    'files_found' => count($rateFiles),
    'files_deleted' => 0,
    'deleted_files' => []
];

if (empty($rateFiles)) {
    $result['message'] = 'レート制限ファイルが見つかりません。';
} else {
    foreach ($rateFiles as $file) {
        if (unlink($file)) {
            $result['files_deleted']++;
            $result['deleted_files'][] = basename($file);
        }
    }
    $result['message'] = "{$result['files_deleted']} 個のレート制限ファイルを削除しました。";
}

// 自己削除
if (isset($_GET['self_destruct']) && $_GET['self_destruct'] === 'yes') {
    @unlink(__FILE__);
    $result['self_destruct'] = true;
    $result['message'] .= ' スクリプトを削除しました。';
}

header('Content-Type: application/json');
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
