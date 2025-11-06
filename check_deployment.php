<?php
/**
 * デプロイメント確認スクリプト
 * 本番環境にどのファイルが存在するかをチェック
 */

header('Content-Type: application/json; charset=utf-8');

$files_to_check = [
    'config.php',
    'setup_readonly.php',
    'MIGRATION_GUIDE.md',
    'chat.js',
    'api.php',
    'index.html',
    'chat.html'
];

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'files' => []
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    $results['files'][$file] = [
        'exists' => file_exists($full_path),
        'readable' => is_readable($full_path),
        'size' => file_exists($full_path) ? filesize($full_path) : 0,
        'modified' => file_exists($full_path) ? date('Y-m-d H:i:s', filemtime($full_path)) : null
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
