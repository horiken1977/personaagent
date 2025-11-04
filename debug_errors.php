<?php
/**
 * エラーログ確認用デバッグスクリプト
 */

// 認証
$auth = $_GET['auth'] ?? '';
if ($auth !== md5('debug_errors_2025')) {
    http_response_code(403);
    die('Forbidden');
}

$logsDir = __DIR__ . '/logs';
$errorLog = $logsDir . '/error.log';
$errorLog2024 = $logsDir . '/error_2025-07-04.log';

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'logs' => []
];

// 最新のエラーログを読み込み
if (file_exists($errorLog)) {
    $lines = file($errorLog, FILE_IGNORE_NEW_LINES);
    $result['logs']['error.log'] = array_slice($lines, -50); // 最新50行
}

if (file_exists($errorLog2024)) {
    $lines = file($errorLog2024, FILE_IGNORE_NEW_LINES);
    $result['logs']['error_2025-07-04.log'] = array_slice($lines, -50); // 最新50行
}

// PHPエラーログ
if (ini_get('error_log')) {
    $phpErrorLog = ini_get('error_log');
    if (file_exists($phpErrorLog)) {
        $lines = file($phpErrorLog, FILE_IGNORE_NEW_LINES);
        $result['logs']['php_error.log'] = array_slice($lines, -50);
    }
}

// APIキーの存在確認
require_once 'config.php';
$result['api_keys_exist'] = [
    'openai' => !empty(getApiKey('openai')),
    'claude' => !empty(getApiKey('claude')),
    'gemini' => !empty(getApiKey('gemini'))
];

// レート制限ファイルの状態
$rateFiles = glob($logsDir . '/rate_*.txt');
$result['rate_limit_files'] = count($rateFiles);

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// 自己削除
@unlink(__FILE__);
?>
