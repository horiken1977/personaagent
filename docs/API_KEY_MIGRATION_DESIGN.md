# APIキー管理の環境変数化 - 設計書

## 1. 現在の仕様分析

### 1.1 現在のアーキテクチャ

```
┌─────────────┐
│   ユーザー   │
└──────┬──────┘
       │ APIキーを入力
       ↓
┌─────────────┐
│  setup.php  │ ←── ユーザーがAPIキーを入力
└──────┬──────┘
       │ JSONファイルに保存
       ↓
┌──────────────┐
│api_keys.json │ ←── 平文でAPIキーを保存（セキュリティリスク）
└──────┬───────┘
       │
       ↓
┌──────────────┐
│  config.php  │ ←── getApiKey()関数で読み込み
└──────┬───────┘
       │
       ↓
┌──────────────┐
│   api.php    │ ←── LLM APIを呼び出し
└──────────────┘
```

### 1.2 現在の問題点

| 問題 | 詳細 | リスクレベル |
|------|------|-------------|
| **平文保存** | api_keys.jsonに暗号化なしで保存 | 🔴 高 |
| **Gitリークリスク** | .gitignoreに追加していても、誤ってコミットされる可能性 | 🔴 高 |
| **ファイル権限** | Webサーバーから読み取り可能（パスが知られれば直接アクセス可能） | 🟡 中 |
| **ユーザー入力** | setup.phpでユーザーがAPIキーを直接入力・変更可能 | 🟡 中 |
| **監査ログなし** | APIキーの変更履歴が追跡できない | 🟡 中 |

### 1.3 現在のファイル構成

```
personaagent/
├── setup.php              # APIキー設定画面（ユーザー入力）
├── config.php             # getApiKey()関数（api_keys.json → .env）
├── api_keys.json          # 🔴 平文でAPIキーを保存
├── .env                   # 環境変数（現在は読み込み優先度が低い）
├── .env.example           # サンプル環境変数
└── api.php                # LLM API呼び出し
```

---

## 2. 新仕様の設計

### 2.1 新しいアーキテクチャ

```
┌─────────────┐
│ 管理者のみ   │
└──────┬──────┘
       │ サーバーに直接.envファイルを配置
       ↓
┌──────────────┐
│  .env ファイル│ ←── 環境変数でAPIキーを管理（Gitには含まない）
└──────┬───────┘
       │ Webサーバーの外部に配置（推奨）
       ↓
┌──────────────┐
│  config.php  │ ←── getApiKey()関数で環境変数から読み込み
└──────┬───────┘
       │
       ↓
┌──────────────┐
│   api.php    │ ←── LLM APIを呼び出し
└──────────────┘

🚫 setup.php   ←── 削除または読み取り専用に変更
🚫 api_keys.json ←── 廃止
```

### 2.2 セキュリティ強化ポイント

| 項目 | 対策 | 効果 |
|------|------|------|
| **暗号化不要** | 環境変数として管理（OSレベルの保護） | 🟢 高 |
| **Git管理外** | .envを.gitignoreで完全に除外 | 🟢 高 |
| **ファイル配置** | Webルート外に配置可能 | 🟢 高 |
| **アクセス制限** | setup.phpを削除/無効化 | 🟢 高 |
| **監査** | サーバーログで環境変数の変更を追跡 | 🟢 中 |

### 2.3 .envファイルの配置オプション

#### オプション1: Webルート内（現在）
```
/home/mokumoku/www/persona/.env
```
- **メリット**: デプロイが簡単
- **デメリット**: Webサーバーから読み取り可能（.htaccessで保護必要）

#### オプション2: Webルート外（推奨）
```
/home/mokumoku/.env
/home/mokumoku/www/persona/ （アプリケーション）
```
- **メリット**: Webサーバーから直接アクセス不可
- **デメリット**: config.phpでパスを調整する必要あり

---

## 3. 実装計画

### 3.1 フェーズ1: 環境変数優先への変更（後方互換性維持）

#### 変更ファイル
- `config.php` - getApiKey()関数の優先順位を変更

#### 変更内容

**現在の優先順位:**
1. api_keys.json
2. 環境変数（.env）

**新しい優先順位:**
1. 環境変数（.env）✨
2. api_keys.json（非推奨、警告を表示）

```php
function getApiKey($provider) {
    // 優先度1: 環境変数から取得
    $envKey = getEnvApiKey($provider);
    if ($envKey) {
        return $envKey;
    }

    // 優先度2: JSONファイル（非推奨）
    $jsonKey = getJsonApiKey($provider);
    if ($jsonKey) {
        error_log("WARNING: api_keys.json is deprecated. Please use .env file.");
        return $jsonKey;
    }

    return null;
}
```

### 3.2 フェーズ2: setup.phpの無効化

#### 選択肢A: 完全削除
- setup.phpを削除
- .gitignoreに setup.php を追加（本番環境のみ削除）

#### 選択肢B: 読み取り専用モードに変更（推奨）
- APIキーの表示機能のみ（マスク表示）
- 編集機能を無効化
- 管理者のみアクセス可能（パスワード保護）

```php
// setup.php の新しい動作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    die('APIキーの編集機能は無効化されています。.envファイルを直接編集してください。');
}

// 読み取り専用モード
$maskedKeys = [
    'openai' => maskApiKey(getApiKey('openai')),
    'claude' => maskApiKey(getApiKey('claude')),
    'gemini' => maskApiKey(getApiKey('gemini'))
];
```

### 3.3 フェーズ3: api_keys.jsonの廃止

#### 移行手順
1. .envファイルを作成
2. api_keys.jsonの内容を.envにコピー
3. api_keys.jsonを削除
4. config.phpからJSON読み込みコードを削除

---

## 4. .envファイルの保護設定

### 4.1 .htaccessによる保護（Apache）

```apache
# .htaccess（Webルート）
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

### 4.2 nginx設定（nginx）

```nginx
location ~ /\.env {
    deny all;
    return 404;
}
```

### 4.3 ファイル権限

```bash
# 所有者のみ読み取り可能
chmod 600 .env

# Webサーバーユーザーのみ読み取り可能
chown www-data:www-data .env  # Apacheの場合
chown nobody:nobody .env       # さくらインターネットの場合
```

---

## 5. 移行手順書

### 5.1 開発環境での移行

```bash
# 1. 現在のAPIキーを確認
cat api_keys.json

# 2. .envファイルを作成
cp .env.example .env

# 3. APIキーを.envに設定
nano .env
# OPENAI_API_KEY=sk-proj-...
# ANTHROPIC_API_KEY=sk-ant-...
# GOOGLE_AI_API_KEY=AIza...

# 4. 動作確認
php -r "require 'config.php'; echo getApiKey('openai');"

# 5. api_keys.jsonをバックアップして削除
mv api_keys.json api_keys.json.bak
```

### 5.2 本番環境での移行

```bash
# さくらインターネットのファイルマネージャーまたはSSHで実施

# 1. .envファイルを作成（Webルート外推奨）
nano /home/mokumoku/.env

# 2. APIキーを設定
OPENAI_API_KEY=sk-proj-実際のキー
ANTHROPIC_API_KEY=sk-ant-実際のキー
GOOGLE_AI_API_KEY=AIza実際のキー

# 3. ファイル権限を設定
chmod 600 /home/mokumoku/.env

# 4. config.phpで.envのパスを設定
# require_once __DIR__ . '/../.env'

# 5. 動作確認後、api_keys.jsonを削除
rm /home/mokumoku/www/persona/api_keys.json

# 6. setup.phpを削除または無効化
rm /home/mokumoku/www/persona/setup.php
```

---

## 6. リスク評価と対策

| リスク | 影響度 | 発生確率 | 対策 |
|--------|--------|----------|------|
| .envファイルの誤削除 | 高 | 低 | バックアップ作成、ドキュメント整備 |
| 環境変数の読み込み失敗 | 高 | 低 | フォールバック機能（JSON）を一時的に残す |
| パスの設定ミス | 中 | 中 | 設定検証スクリプトを作成 |
| デプロイ時の.env除外 | 高 | 中 | .gitignoreの厳格化、デプロイチェックリスト |

---

## 7. ロールバック計画

### 問題発生時の復旧手順

```bash
# 1. api_keys.jsonのバックアップを復元
cp api_keys.json.bak api_keys.json

# 2. config.phpを旧バージョンに戻す
git checkout HEAD~1 config.php

# 3. サービス再起動
# Webサーバーの再起動は不要（PHPファイルは即座に反映）

# 4. 動作確認
curl https://mokumoku.sakura.ne.jp/persona/show_errors.php?auth=show_errors_now
```

---

## 8. 今後の拡張

### 8.1 より高度なセキュリティ

- **AWS Secrets Manager** / **Google Secret Manager**の使用
- **暗号化されたストレージ**への移行
- **APIキーのローテーション機能**

### 8.2 監査ログ

```php
// APIキー使用のログ記録
function logApiKeyUsage($provider) {
    $log = [
        'timestamp' => date('c'),
        'provider' => $provider,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];
    file_put_contents('logs/api_usage.log', json_encode($log) . "\n", FILE_APPEND);
}
```

---

## 9. まとめ

### 9.1 実装の優先順位

1. **高優先**: フェーズ1 - 環境変数優先への変更
2. **中優先**: フェーズ2 - setup.phpの無効化
3. **低優先**: フェーズ3 - api_keys.jsonの完全廃止

### 9.2 期待される効果

| 項目 | 改善度 |
|------|--------|
| セキュリティ | ⬆️⬆️⬆️ 大幅向上 |
| 保守性 | ⬆️⬆️ 向上 |
| 運用コスト | ⬆️ わずかに増加（初回のみ） |
| パフォーマンス | → 変化なし |

---

**作成日**: 2025-11-04
**作成者**: Claude Code
**バージョン**: 1.0
