# SAKURA-NET プレミアムUI 改修履歴 (RELEASE_NOTES)

## [v2.9.2] - 2026-04-19 — Phase 2A 不要ファイル整理（ゴミ箱パターン）

### 🗑 trash 移動（合計 67ファイル / 約 88.9MB 削減）
- **古い navigation スクリプト**: `navigation1.js` 〜 `navigation52.js`（52本）
  - 現役 `navigation.js` / `navigation53.js` は残置（index.html で使用中）
- **旧SP版ZIP**: `sp21_20200612.zip` / `sp21_20210616.zip` / `sp21_20220706.zip` / `sp21_20250220.zip`（計 約 89MB）
- **旧スクリプト**: `apply_new_fixes.py` / `apply_new_fixes2.py` / `apply_responsive_all.ps1` / `apply_responsive_all.py` / `apply_safe.ps1` / `apply_safe2.ps1` / `apply_sakura.py` / `run_safe2.ps1` / `update_privacy_sakura.ps1` / `update_privacy_sakura.py`（10本）
- **スナップショット**: `index_snapshot_20260223.html`（ルート直下の1本）

### ✅ 安全性検証
- 全9HTML + posts/ + style.css で `navigation[0-9]+\.js` / `sp21_` / `apply_` / `run_safe` / `update_privacy` を grep → **参照0件確認済み**
- trash 移動先: `trash/phase2a_2026-04-19/`（最終削除はユーザーが `rm -rf trash/` で実施）

### ⚙ 補足
- ファイル削除ではなく **ゴミ箱パターン**（`mkdir -p trash && mv`）で実施
- サーバー側への変更なし（今回は**ローカルのみ**の整理）
- 次タスク: Phase 2B（画像WebP化・lazy loading）

### 📤 サーバーアップロード対象
**なし**（ローカル整理のみ・サーバーは現状維持）

---

## [v2.9.1] - 2026-04-19 — ロゴ・ファビコン・アプリアイコン配置

### 🎨 ブランド画像アセット配置（Gemini作成）
- **`img/logo.png`** — 512×512px / RGBA / 246KB（schema.org Organization logo 用・背景透過）
- **`img/apple-touch-icon.png`** — 180×180px / RGB / 18KB（iOS ホーム追加アイコン・背景 `#fff0f5`）
- **`favicon.ico`** — 16×16 + 32×32 + 48×48 マルチレイヤー / 6.0KB（ルート直下配置）

### ✅ 検証結果
- 全9HTMLページの `<link rel="icon">` / `<link rel="apple-touch-icon">` パス整合性OK
- PIL による ICO レイヤー検証: `[(16,16),(32,32),(48,48)]` 確認済み
- JSON-LD Organization の `logo` プロパティが参照する `/img/logo.png` 実体配置完了
  （v2.9.0時点で未配置だった画像アセットが揃い、リッチリザルトの完全適用条件を満たした）

### 📤 サーバーアップロード対象
| ファイル | サーバーパス |
|---|---|
| img/logo.png | `https://sakuranet-co.jp/img/logo.png` |
| img/apple-touch-icon.png | `https://sakuranet-co.jp/img/apple-touch-icon.png` |
| favicon.ico | `https://sakuranet-co.jp/favicon.ico` |

### ⚙ 補足
- 初回納品された `favicon.ico` は 16×16 単一レイヤーだったため Gemini に差し戻し再作成→正式マルチレイヤーで受領
- バックアップ: `backups/v2.9.1_logo-assets/`（3ファイル）
- 依頼書: `GEMINI_LOGO_REQUEST.md` / `GEMINI_FAVICON_REDO.md`

---

## [v2.9.0] - 2026-04-19 — Phase 1 SEO基礎整備

### 🎯 SEO・構造化データ（全9ページ一括対応）
- **GA4移行**: 旧 `UA-123359498-1` → `G-8DZV2NE7C1` へ全ページ置換
- **canonical**: 全ページに絶対URLの正規化URLを追加
- **OGP強化**: `og:image`（`/img/ogp.jpg` 1200×630px・Gemini作成）+ `og:image:width/height` + `og:locale` を全ページに追加
- **Twitter Card**: `summary_large_image` 形式のカード情報を全ページに追加
- **robots meta**: `index,follow,max-image-preview:large` を全ページに追加
- **favicon / apple-touch-icon**: 全ページに `<link rel="icon">` / `<link rel="apple-touch-icon">` を追加（※画像ファイル自体は後日配置予定）
- **title / meta description リライト**: 全9ページでSEO意図に沿った形に書き換え（60字/120字ガイドライン準拠）

### 🏢 JSON-LD 構造化データ実装
- **index.html**: `@graph` 形式で Organization + LocalBusiness + WebSite を実装（住所・TEL・営業時間・地域対応情報）
- **service.html**: Service スキーマ4種（UniFi導入 / SAKURA-NET光 / 防犯カメラ・入退室管理 / UTM・IP-PBX・VPN）を実装

### 🗂 SEOインフラ新規ファイル
- `sitemap.xml` — 全9URLを image-sitemap含めて記載
- `robots.txt` — `/system/` `/data/` `/backup/` `/backups/` `/trash/` を除外・sitemap参照追加
- `manifest.json` — PWA対応マニフェスト（ブランドカラー `#c82054` / `#fff0f5`）

### ⚙ 補足
- `img/ogp.jpg`（109KB、1200×630px）Gemini作成・ブランドカラー/キャッチコピー準拠
- バックアップ: `backups/v2.8.2_pre-seo/`（10ファイル）
- **未配置**: `favicon.ico` / `apple-touch-icon.png` / `img/logo.png`（Gemini追加依頼予定）
- **次フェーズ**: Phase 2（WebP化・lazy loading・不要ファイル整理）

### 📤 サーバーアップロード対象
| ファイル | サーバーパス |
|---|---|
| index.html / company.html / service.html / access.html / contact.html / recruit.html / news.html / concept.html / privacy.html | `https://sakuranet-co.jp/` 各ルート |
| sitemap.xml | `https://sakuranet-co.jp/sitemap.xml` |
| robots.txt | `https://sakuranet-co.jp/robots.txt` |
| manifest.json | `https://sakuranet-co.jp/manifest.json` |
| img/ogp.jpg | `https://sakuranet-co.jp/img/ogp.jpg` |

---

## [v2.8.2] - 2026-04-15
### ✨ 新規資産加工 (Asset Processing)
- **電子角印の背景透過化**: `電子角印.bmp` を元に、背景の白枠を完全に透過処理した `電子角印_transparent.png` を生成。
- **配置場所**: `C:\Users\MYPC\Desktop\電子スタンプdeta\電子スタンプdeta\`
- **バックアップ**: 元ファイルは `電子角印_orig.bmp` として同ディレクトリに保存。


## [v2.8.1] - 2026-04-01
### 🛠 修正内容 (Critical Bug Fix)
- **FAQ記事デザイン同期の不備解消**: `faq1`, `faq2`, `faq3` が2018年当時の古いデザインであった問題を、最新プレミアムUIテンプレートへ一括変換。
- **物理ファイルの配置適正化**: アップロード用ディレクトリ `CTO\posts\` 直下に、最新化した全HTML（faq1-4, news12-13）を配置。これにより、WinSCPでのアップロードが即座に反映される状態に改善。
- **デッドリンク/幽霊ファイルの整理**: 管理画面外で存在していた `faq4.html`, `news13.html` 等のテスト記事も一律プレミアムデザインへ統一。

## [v2.8.0] - 2026-04-01
### ✨ 新規実装
- **プレミアム・マガジン風UIの導入**: グラスモーフィズム調カード、サクラアクセントライン、レスポンシブ対応を統合した最新UIマスターテンプレートの策定。
- **主要ページの刷新**: `index.html`, `news.html`, `posts/news10.html` をプレミアムUIへ更新。
