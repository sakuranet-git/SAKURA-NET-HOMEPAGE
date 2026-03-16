# Claude Code への作業指示書：SAKURA OS マスコットの実装

## 概要
SAKURA OS（`sakura-os.html`）のデスクトップ左下に、新しく作成したマスコットキャラクターを表示し、設定パネルでON/OFFを切り替えられるようにしてください。

## 準備されたファイル
作業フォルダ：`C:\Users\MYPC\Development\SAKURA-NET-Mascot`
- `sakura-mascot.png`: マスコット画像
- `mascot.css`: マスコットの配置とアニメーション（浮遊、ジャンプ等）
- `mascot.js`: マスコットの振る舞いロジック（クリック反応、LocalStorage連携）

## 具体的な作業手順

### 1. リソースのインポート
`C:\Users\MYPC\Development\SAKURA-OS\sakura-os.html` を編集し、マスコットのリソースを読み込んでください。

- `<head>` 内で `mascot.css` を読み込む。
- `<body>` の最後（`<script>` タグの直前など）で `mascot.js` を読み込む。
- **注意**: パスは環境に合わせて適切に設定してください（例: `/system/SAKURA-NET-Mascot/mascot.css` など）。

### 2. 設定パネルへのスイッチ追加
`sakura-os.html` 内の `wallpaper-panel`（壁紙変更パネル）に、マスコットの表示を切り替えるUIを追加してください。

- **UI要素**: `wp-custom` クラスの周辺、または新しいセクションとして「マスコット表示」のトグルスイッチ（またはボタン）を追加。
- **デザイン**: SAKURA OS の既存デザイン（グラスモーフィズム、ピンクのアクセントカラー）に合わせたプレミアムな外観にしてください。

### 3. 切り替えロジックの実装
追加したスイッチにイベントリスナーを設定してください。

- スイッチクリック時に `window.sakuraMascot.toggle()` を呼び出す。
- 現在の状態（ON/OFF）を `localStorage` の `sakura_mascot_off` キー（値は "true" または "false"）と同期させる。

### 4. 動作確認
- デスクトップ左下にマスコットがふんわりと浮いていること。
- クリックするとマスコットが跳ねて挨拶すること。
- 設定パネルのスイッチで表示・非表示が即座に切り替わること。
- ページをリロードしてもON/OFF設定が維持されていること。

## 完了条件
- `sakura-os.html` が更新され、マスコット機能が完全に動作する。
- `RELEASE_NOTES.md` にマスコット機能追加の旨を追記する。
