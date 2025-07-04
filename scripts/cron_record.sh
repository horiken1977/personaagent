#!/bin/bash

# 定期実行用スクリプト（2時間ごと）
# VSCodeでClaude Codeが動いている場合のみ実行

# Claude Codeのプロセスをチェック
if pgrep -f "Visual Studio Code" > /dev/null && pgrep -f "claude" > /dev/null; then
    # Claude Codeが動いている場合のみ記録
    /Users/aa479881/Library/CloudStorage/OneDrive-IBM/Personal/development/personaagent/scripts/auto_record.sh
fi