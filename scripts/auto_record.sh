#!/bin/bash

# Claude Code 自動記録スクリプト
# Hook機能により定期的に実行される

PROJECT_DIR="/Users/aa479881/Library/CloudStorage/OneDrive-IBM/Personal/development/personaagent"
CLAUDE_MD="$PROJECT_DIR/CLAUDE.md"
LOG_FILE="$PROJECT_DIR/logs/auto_record.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# ログディレクトリの作成
mkdir -p "$PROJECT_DIR/logs"

# 最後の記録から2時間以上経過している場合のみ実行
LAST_RECORD_FILE="$PROJECT_DIR/logs/last_record_time"
CURRENT_TIME=$(date +%s)

if [ -f "$LAST_RECORD_FILE" ]; then
    LAST_TIME=$(cat "$LAST_RECORD_FILE")
    TIME_DIFF=$((CURRENT_TIME - LAST_TIME))
    
    # 2時間 = 7200秒
    if [ $TIME_DIFF -lt 7200 ]; then
        echo "$TIMESTAMP: スキップ (前回記録から${TIME_DIFF}秒)" >> "$LOG_FILE"
        exit 0
    fi
fi

# 現在時刻を記録
echo "$CURRENT_TIME" > "$LAST_RECORD_FILE"

# CLAUDE.mdの末尾に自動記録セクションを追加
cat << EOF >> "$CLAUDE_MD"

---
## 自動記録 ($TIMESTAMP)

### セッション継続中
- Hook機能により自動記録実行
- 前回記録時刻: $(date -r "$LAST_TIME" '+%Y-%m-%d %H:%M:%S' 2>/dev/null || echo "初回")
- 現在時刻: $TIMESTAMP

### アクティビティ
- Claude Code セッション継続中
- プロジェクト: PersonaAgent
- 作業ディレクトリ: $PROJECT_DIR

EOF

# ログに記録
echo "$TIMESTAMP: 自動記録完了" >> "$LOG_FILE"

# 成功を通知（オプション）
# osascript -e "display notification \"CLAUDE.md自動更新完了\" with title \"Claude Code Hook\""