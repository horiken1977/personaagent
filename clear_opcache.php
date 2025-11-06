<?php
/**
 * OPcacheクリアスクリプト
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== PHP OPcache クリア ===\n\n";

if (function_exists('opcache_reset')) {
    $result = opcache_reset();
    if ($result) {
        echo "✓ OPcacheをクリアしました\n";
    } else {
        echo "✗ OPcacheのクリアに失敗しました\n";
    }
} else {
    echo "ℹ OPcacheが有効になっていないか、opcache_reset関数が利用できません\n";
}

echo "\n=== config.phpの確認 ===\n\n";

require_once 'config.php';

$providers = getConfig('llm_providers');

if (isset($providers['gemini'])) {
    echo "Gemini設定:\n";
    echo "  モデル名: " . $providers['gemini']['model'] . "\n";
    echo "  エンドポイント: " . $providers['gemini']['endpoint'] . "\n";
} else {
    echo "✗ Gemini設定が見つかりません\n";
}

echo "\nこのページにアクセスした後、アプリケーションを再度お試しください。\n";
