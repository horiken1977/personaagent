<?php
/**
 * デプロイ先パス診断スクリプト
 * このファイルがどこにデプロイされているかを表示
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== デプロイ先パス診断 ===\n\n";

echo "1. __FILE__: " . __FILE__ . "\n";
echo "2. __DIR__: " . __DIR__ . "\n";
echo "3. getcwd(): " . getcwd() . "\n";
echo "4. realpath('.'): " . realpath('.') . "\n\n";

echo "5. このディレクトリ内のファイル:\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $path = __DIR__ . '/' . $file;
        echo "   - {$file}";
        if (is_dir($path)) {
            echo " [DIR]";
        } else {
            echo " (" . filesize($path) . " bytes, " . date('Y-m-d H:i:s', filemtime($path)) . ")";
        }
        echo "\n";
    }
}

echo "\n6. 期待されるファイル確認:\n";
$expected_files = [
    'setup_readonly.php',
    'check_deployment.php',
    'diagnostic_marker.txt',
    'config.php',
    'index.html'
];

foreach ($expected_files as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    echo "   - {$file}: " . ($exists ? '✓ 存在' : '✗ 存在しない') . "\n";
    if ($exists) {
        echo "      サイズ: " . filesize($path) . " bytes\n";
        echo "      更新日時: " . date('Y-m-d H:i:s', filemtime($path)) . "\n";
    }
}

echo "\n7. SERVER情報:\n";
echo "   - DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "   - SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "   - SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "\n";
