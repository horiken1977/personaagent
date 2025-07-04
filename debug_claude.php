<?php
/**
 * Claude API デバッグページ
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Claude API デバッグ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <h1>Claude API デバッグツール</h1>

    <div class="section">
        <h2>1. APIキー状態</h2>
        <?php
        $claudeKey = getApiKey('claude');
        if ($claudeKey) {
            echo '<div class="status success">✅ APIキーが設定されています</div>';
            echo '<pre>長さ: ' . strlen($claudeKey) . ' 文字</pre>';
            echo '<pre>プレフィックス: ' . substr($claudeKey, 0, 10) . '...</pre>';
        } else {
            echo '<div class="status error">❌ APIキーが設定されていません</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>2. API チェックエンドポイントテスト</h2>
        <button onclick="testApiCheck()">api_check.php をテスト</button>
        <div id="apiCheckResult" class="result"></div>
    </div>

    <div class="section">
        <h2>3. 直接API呼び出しテスト</h2>
        <button onclick="testDirectApi()">Claude API を直接テスト</button>
        <div id="directApiResult" class="result"></div>
    </div>

    <div class="section">
        <h2>4. チャットAPI経由テスト</h2>
        <button onclick="testChatApi()">chat API 経由でテスト</button>
        <div id="chatApiResult" class="result"></div>
    </div>

    <script>
    function testApiCheck() {
        const resultDiv = document.getElementById('apiCheckResult');
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '確認中...';
        
        fetch('api_check.php?provider=claude')
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                resultDiv.innerHTML = `
                    <strong>結果:</strong><br>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="status error">エラー: ${error.message}</div>
                    <pre>${error.stack}</pre>
                `;
            });
    }

    function testDirectApi() {
        const resultDiv = document.getElementById('directApiResult');
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '確認中...';
        
        // PHPスクリプトを呼び出してテスト
        fetch('test_claude_api.php')
            .then(response => response.text())
            .then(data => {
                resultDiv.innerHTML = `<pre>${data}</pre>`;
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="status error">エラー: ${error.message}</div>
                `;
            });
    }

    function testChatApi() {
        const resultDiv = document.getElementById('chatApiResult');
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '確認中...';
        
        const testData = {
            provider: 'claude',
            prompt: 'Reply with just "Test successful" if you receive this message.',
            personaId: '1',
            test: true
        };
        
        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(testData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="status success">✅ 成功</div>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="status error">❌ エラー</div>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <div class="status error">エラー: ${error.message}</div>
            `;
        });
    }
    </script>
</body>
</html>