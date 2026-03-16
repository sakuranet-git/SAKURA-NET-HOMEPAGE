# RELEASE_NOTES.md

## [v1.0.1] - 2026-03-16

### 修正
- **透過クオリティの改善**: Flood Fillアルゴリズムにより、キャラクター内部の透過を防ぎ背景のみを透明化。
- **表示サイズの拡大**: デフォルトサイズを 200px に変更。
- **技術規約（GEMINI_INSTRUCTION）への準拠**:
  - `mascot.js` のキャンバスサイズを 200px に同期。
  - 初期化メソッドの実行順序を規約通りに修正。
  - 吹き出しのアニメーション（transform）を規約通りに統一。
- **クリーンアップ**: 作業用の一時ファイルをすべて削除。

## [v1.0.0] - 2026-03-16

### 注意事項
- 本アセットは `C:\Users\MYPC\Development\SAKURA-NET-Mascot` に配置されています。
- SAKURA OS本体への組み込みは、付属の `CLAUDE_CODE_INSTRUCTION.md` を使用して Claude Code に依頼してください。
