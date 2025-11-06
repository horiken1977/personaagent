# APIキー管理の.env移行ガイド

## 概要

セキュリティ強化のため、APIキーの管理方法を`api_keys.json`から`.env`ファイルに移行します。

---

## 本番環境での移行手順（さくらインターネット）

### ステップ1: .envファイルの作成

```bash
# SSH または ファイルマネージャーでアクセス

# 1. Webルート外に.envファイルを作成（推奨）
cd /home/mokumoku
nano .env
```

または

```bash
# 2. Webルート内に作成（.htaccessで保護）
cd /home/mokumoku/www/persona
nano .env
```

### ステップ2: APIキーを.envに記述

```bash
# .envファイルの内容

# OpenAI API
OPENAI_API_KEY=sk-proj-実際のOpenAIキー

# Anthropic Claude API
ANTHROPIC_API_KEY=sk-ant-実際のClaudeキー

# Google Gemini API
GOOGLE_AI_API_KEY=AIza実際のGeminiキー

# デバッグモード（本番環境ではfalse）
DEBUG_MODE=false
```

### ステップ3: ファイル権限の設定

```bash
# 所有者のみ読み取り可能に設定
chmod 600 .env

# 所有者を確認
ls -la .env
```

### ステップ4: config.phpのパス設定（Webルート外に配置した場合）

`config.php`の11行目付近を編集：

```php
// .envファイルの読み込み（Webルート外に配置した場合）
if (file_exists(__DIR__ . '/../.env')) {
    $envLines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // ... 以下省略
}
```

### ステップ5: 動作確認

`show_errors.php`で確認：

```
https://mokumoku.sakura.ne.jp/persona/show_errors.php?auth=show_errors_now
```

- すべてのAPIキーが「✓」表示されることを確認

### ステップ6: api_keys.jsonのバックアップと削除

```bash
# バックアップ
cp api_keys.json api_keys.json.bak

# 動作確認後、削除
rm api_keys.json
```

### ステップ7: setup.phpの置き換え

```bash
# 旧setup.phpを削除
rm setup.php

# 新しい読み取り専用版に置き換え
mv setup_readonly.php setup.php
```

---

## ローカル開発環境での移行手順

### ステップ1: .envファイルの作成

```bash
# プロジェクトルートで実行
cp .env.example .env
```

### ステップ2: APIキーを設定

```bash
nano .env

# または
code .env  # VS Codeで編集
```

```.env
OPENAI_API_KEY=sk-proj-あなたのOpenAIキー
ANTHROPIC_API_KEY=sk-ant-あなたのClaudeキー
GOOGLE_AI_API_KEY=AIzaあなたのGeminiキー
DEBUG_MODE=true
```

### ステップ3: 動作確認

```bash
# PHPでAPIキーが読み込めるか確認
php -r "require 'config.php'; echo getApiKey('openai');"
```

### ステップ4: api_keys.jsonを削除

```bash
# バックアップを作成
mv api_keys.json api_keys.json.bak

# または完全に削除
rm api_keys.json
```

---

## .htaccessによる.envの保護（Webルート内に配置した場合）

`.htaccess`ファイルを作成または編集：

```.htaccess
# .envファイルへの直接アクセスを禁止
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files ".env.*">
    Order allow,deny
    Deny from all
</Files>
```

---

## トラブルシューティング

### 問題1: APIキーが読み込まれない

**確認事項:**
1. .envファイルのパスが正しいか
2. ファイルの権限（600）が設定されているか
3. 環境変数の名前が正しいか（`OPENAI_API_KEY`など）

**デバッグ:**

```php
<?php
// debug_env.php
require 'config.php';

echo "OpenAI: " . (getApiKey('openai') ? '設定済み' : '未設定') . "\n";
echo "Claude: " . (getApiKey('claude') ? '設定済み' : '未設定') . "\n";
echo "Gemini: " . (getApiKey('gemini') ? '設定済み' : '未設定') . "\n";
?>
```

### 問題2: Webルート外の.envが読み込まれない

**config.phpのパスを確認:**

```php
// 相対パスで上位ディレクトリを指定
if (file_exists(__DIR__ . '/../.env')) {
    $envLines = file(__DIR__ . '/../.env', ...);
}
```

### 問題3: 古いapi_keys.jsonが使われ続ける

**確認:**
```bash
# api_keys.jsonが存在しないか確認
ls -la api_keys.json

# 存在する場合は削除
rm api_keys.json
```

---

## セキュリティチェックリスト

- [ ] .envファイルが`.gitignore`に含まれている
- [ ] .envファイルの権限が600に設定されている
- [ ] api_keys.jsonが削除されている
- [ ] setup.phpが読み取り専用版に置き換えられている
- [ ] .htaccessで.envへのアクセスが禁止されている（Webルート内の場合）
- [ ] すべてのLLMプロバイダーでAPIキーが正しく読み込まれる

---

## ロールバック手順（問題発生時）

```bash
# 1. api_keys.jsonのバックアップを復元
cp api_keys.json.bak api_keys.json

# 2. config.phpを旧バージョンに戻す
git checkout HEAD~1 config.php

# 3. 動作確認
curl https://mokumoku.sakura.ne.jp/persona/show_errors.php?auth=show_errors_now
```

---

**更新日**: 2025-11-04
**作成者**: Claude Code
**関連ドキュメント**: [API_KEY_MIGRATION_DESIGN.md](docs/API_KEY_MIGRATION_DESIGN.md)
