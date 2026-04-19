# SAKURA-NET プレミアムUI 改修履歴 (RELEASE_NOTES)

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
