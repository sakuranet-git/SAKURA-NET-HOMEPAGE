# Gemini への技術指示書：SAKURA OS マスコット

> **このファイルは削除しないこと。Claude Code が管理しています。**

## 現在の実装状態（v1.5.6）

### ファイル構成
- `sakura-mascot.png` — マスコット画像（透明背景推奨）
- `mascot.css` — スタイル・アニメーション定義
- `mascot.js` — 振る舞いロジック
- `GEMINI_INSTRUCTION.md` — **このファイル（削除禁止）**

### `sakura-os.html` との連携（変更不要）
- `<head>` に `<link rel="stylesheet" href="/system/SAKURA-NET-Mascot/mascot.css">` 読み込み済み
- `</body>` 直前に `<script src="/system/SAKURA-NET-Mascot/mascot.js"></script>` 読み込み済み
- 左パネルにマスコット ON/OFF トグル設置済み（`#mascot-knob`）
- マスコットはカレンダー下（`#dcal-wrapper`）にデフォルト配置

---

## 絶対に守ってほしいルール

### G1. CSS と JS のサイズを必ず一致させる

`mascot.css` の `.mascot-image-wrap` のサイズを変更した場合は、
**必ず `mascot.js` のキャンバスサイズも同じ値に変更すること。**

```css
/* mascot.css */
.mascot-image-wrap {
    width: 300px;   ← ここを変えたら
    height: 300px;
}
```

```js
// mascot.js — 必ず同じ値に合わせる（3箇所）
canvas.width = 300;              ← ここも
canvas.height = 300;             ← ここも
canvas.style.width = '300px';   ← ここも
canvas.style.height = '300px';  ← ここも
ctx.drawImage(tempImg, 0, 0, 300, 300);  ← ここも
ctx.getImageData(0, 0, 300, 300);        ← ここも
```

### G2. 吹き出しの show/hide の transform を一致させる

`.mascot-bubble`（非表示）と `.mascot-bubble.show`（表示）の **X軸成分を揃えること。**

**NG パターン（ズレが発生する）:**
```css
.mascot-bubble       { transform: translateY(10px) scale(0.8); }          /* X軸なし */
.mascot-bubble.show  { transform: translateX(-50%) translateY(0) scale(1); } /* X軸あり → 跳ぶ！ */
```

**OK パターン（X軸なしで統一）:**
```css
.mascot-bubble       { transform: translateY(10px) scale(0.8); }
.mascot-bubble.show  { transform: translateY(0) scale(1); }
```

### G3. `init()` 内の呼び出し順序を変えない

```js
this._initPosition();   // 1. 位置設定
this._initDrag();       // 2. ドラッグ（必ずここで呼ぶ）
this.updateVisibility(); // 3. 表示状態の同期
```

### G4. `_initPosition()` の先頭に必ず `_loadPosition()` を呼ぶ

**これを削除すると、ドラッグで移動した位置がリロード後に消える。**

```js
_initPosition() {
    // ← 必ずこれを最初に書く
    const saved = this._loadPosition();
    if (saved) {
        this.container.style.left = saved.x + 'px';
        this.container.style.top  = saved.y + 'px';
        return;
    }
    // 以降: dcal-wrapper を使ったデフォルト位置計算...
}
```

### G5. 壁紙パネルにマスコット設定を入れない

ON/OFF トグルは `sakura-os.html` の **左パネル（常時表示）** に配置。
壁紙パネルや他の場所に移動しないこと。

---

## 吹き出しの bottom 値の目安

| `.mascot-image-wrap` のサイズ | `.mascot-bubble` の `bottom` 推奨値 |
|---|---|
| 110px | 120px |
| 150px | 160px |
| 200px | 210px |
| 300px | 310px |

---

## 変更禁止事項（sakura-os.html との連携が壊れる）

- `_loadPosition()` / `_savePosition()` の localStorage キー名（`sakura_mascot_pos`）
- `toggle()` メソッドの localStorage キー名（`sakura_mascot_off`）
- `window.sakuraMascot = new SakuraMascot();` の最終行
- `_initPosition()` 内の `#dcal-wrapper` 参照

---

## 過去に起きたバグ履歴（同じミスをしないために）

| バージョン | バグ内容 | 原因 |
|---|---|---|
| v1.5.2 | ドラッグが動かない | `_initDrag()` の呼び出しを `init()` から削除した |
| v1.5.3 | 白背景が消えない | CSS `mix-blend-mode` では不完全。Canvas API が必要 |
| v1.5.5 | マスコットが小さく表示 | CSS を 200px にしたが JS のキャンバスが 110px のまま |
| v1.5.5 | 吹き出しが表示時に跳ぶ | show/hide の transform X軸が不一致 |
| v1.5.6 | ドラッグ位置がリロードで消える | `_initPosition()` から `_loadPosition()` を削除した |
