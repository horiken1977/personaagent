<?php
/**
 * APIキー設定確認画面（読み取り専用）
 *
 * 【重要】
 * このページはAPIキーの設定状況を確認するためのものです。
 * APIキーの編集機能は無効化されています。
 *
 * APIキーを変更するには、サーバー上の .env ファイルを直接編集してください。
 */

require_once 'config.php';

// 編集機能は完全に無効化
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'APIキーの編集機能は無効化されています。',
        'message' => 'セキュリティ向上のため、APIキーは .env ファイルで管理されています。サーバー管理者に連絡して .env ファイルを編集してください。'
    ]);
    exit;
}

/**
 * APIキーをマスク表示
 */
function maskApiKey($key) {
    if (empty($key)) {
        return '未設定';
    }

    $length = strlen($key);

    if ($length <= 10) {
        return str_repeat('*', $length);
    }

    // 最初の8文字と最後の4文字以外をマスク
    $visible_start = substr($key, 0, 8);
    $visible_end = substr($key, -4);
    $masked_length = $length - 12;

    return $visible_start . str_repeat('*', $masked_length) . $visible_end;
}

/**
 * APIキーのソース（.envまたはJSONファイル）を判定
 */
function getApiKeySource($provider) {
    $envKey = getApiKeyFromEnv($provider);
    if ($envKey) {
        return '.env（推奨）';
    }

    $jsonKey = getApiKeyFromJson($provider);
    if ($jsonKey) {
        return 'api_keys.json（非推奨）';
    }

    return '未設定';
}

// APIキーの設定状況を確認
$apiKeys = [
    'openai' => [
        'name' => 'OpenAI (GPT-4)',
        'key' => getApiKey('openai'),
        'source' => getApiKeySource('openai'),
        'status' => !empty(getApiKey('openai'))
    ],
    'claude' => [
        'name' => 'Anthropic (Claude)',
        'key' => getApiKey('claude'),
        'source' => getApiKeySource('claude'),
        'status' => !empty(getApiKey('claude'))
    ],
    'gemini' => [
        'name' => 'Google (Gemini)',
        'key' => getApiKey('gemini'),
        'source' => getApiKeySource('gemini'),
        'status' => !empty(getApiKey('gemini'))
    ]
];

$hasAnyKey = false;
foreach ($apiKeys as $provider => $info) {
    if ($info['status']) {
        $hasAnyKey = true;
        break;
    }
}

// 非推奨のapi_keys.jsonが使用されているか確認
$usingDeprecatedJson = false;
foreach ($apiKeys as $provider => $info) {
    if ($info['status'] && strpos($info['source'], 'api_keys.json') !== false) {
        $usingDeprecatedJson = true;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APIキー設定確認 | 北米市場調査AIエージェント</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .readonly-notice {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .readonly-notice h2 {
            color: #856404;
            margin-top: 0;
        }

        .deprecated-warning {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .deprecated-warning h3 {
            color: #721c24;
            margin-top: 0;
        }

        .api-key-status {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .api-key-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e1e8ed;
        }

        .api-key-row:last-child {
            border-bottom: none;
        }

        .api-key-info {
            flex: 1;
        }

        .api-key-name {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .api-key-value {
            font-family: monospace;
            color: #666;
            margin-top: 5px;
        }

        .api-key-source {
            font-size: 0.85rem;
            color: #888;
            margin-top: 5px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-configured {
            background: #d4edda;
            color: #155724;
        }

        .status-not-configured {
            background: #f8d7da;
            color: #721c24;
        }

        .migration-instructions {
            background: #e7f3ff;
            border: 1px solid #2196F3;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }

        .migration-instructions h3 {
            color: #1976D2;
            margin-top: 0;
        }

        .migration-instructions pre {
            background: #263238;
            color: #aed581;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 0.9rem;
        }

        .btn-group {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 900px; margin: 50px auto; padding: 20px;">
        <h1>APIキー設定確認</h1>

        <div class="readonly-notice">
            <h2>📌 読み取り専用モード</h2>
            <p>
                セキュリティ向上のため、APIキーの編集機能は無効化されています。<br>
                APIキーを変更するには、サーバー上の <code>.env</code> ファイルを直接編集してください。
            </p>
        </div>

        <?php if ($usingDeprecatedJson): ?>
        <div class="deprecated-warning">
            <h3>⚠️ 非推奨の設定方法が使用されています</h3>
            <p>
                一部のAPIキーが <code>api_keys.json</code> から読み込まれています。<br>
                セキュリティリスクを軽減するため、<code>.env</code> ファイルへの移行を推奨します。
            </p>
        </div>
        <?php endif; ?>

        <div class="api-key-status">
            <h2>現在の設定状況</h2>

            <?php foreach ($apiKeys as $provider => $info): ?>
            <div class="api-key-row">
                <div class="api-key-info">
                    <div class="api-key-name"><?php echo htmlspecialchars($info['name']); ?></div>
                    <div class="api-key-value"><?php echo htmlspecialchars(maskApiKey($info['key'])); ?></div>
                    <div class="api-key-source">ソース: <?php echo htmlspecialchars($info['source']); ?></div>
                </div>
                <div>
                    <span class="status-badge <?php echo $info['status'] ? 'status-configured' : 'status-not-configured'; ?>">
                        <?php echo $info['status'] ? '✓ 設定済み' : '✗ 未設定'; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$hasAnyKey): ?>
        <div class="migration-instructions">
            <h3>APIキーの設定方法</h3>
            <p>以下の手順で <code>.env</code> ファイルにAPIキーを設定してください：</p>

            <pre># .envファイルの作成
cp .env.example .env

# .envファイルを編集
nano .env  # またはviなど</pre>

            <p><code>.env</code> ファイルに以下の形式でAPIキーを記述してください：</p>

            <pre>OPENAI_API_KEY=sk-proj-your-openai-api-key
ANTHROPIC_API_KEY=sk-ant-your-claude-api-key
GOOGLE_AI_API_KEY=AIza-your-gemini-api-key</pre>
        </div>
        <?php elseif ($usingDeprecatedJson): ?>
        <div class="migration-instructions">
            <h3>📋 .envへの移行手順</h3>
            <p><code>api_keys.json</code> から <code>.env</code> へ移行することを推奨します：</p>

            <pre># 1. 現在のapi_keys.jsonをバックアップ
cp api_keys.json api_keys.json.bak

# 2. .envファイルを作成
cp .env.example .env

# 3. api_keys.jsonの内容を.envにコピー
# OpenAI, Claude, Gemini の各APIキーを .env に記述

# 4. 動作確認後、api_keys.jsonを削除
rm api_keys.json</pre>
        </div>
        <?php endif; ?>

        <div class="btn-group">
            <a href="index.html" class="btn btn-primary">メインページに戻る</a>
        </div>
    </div>
</body>
</html>
