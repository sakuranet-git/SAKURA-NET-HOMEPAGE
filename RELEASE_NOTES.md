# SAKURA-NET プレミアムUI 改修履歴 (RELEASE_NOTES)

## [v2.9.5.1] - 2026-04-20 — hotfix（:focus-visible スコープ修正）

### 🐛 修正
- `style.css`: v2.9.5 で追加した `:focus-visible` ブロックが `@media screen and (max-width: 568px) { ... }` 内に誤って配置されており、デスクトップ幅（>568px）でキーボードフォーカスリングが表示されない不具合を修正
- @media の閉じ `}` を追加し、`:focus-visible` ルール群を @media 外へ移動（CSSグローバルスコープで適用）

### 📦 バックアップ
- `backups/v2.9.5.1_pre-hotfix/style.css`

### 📤 サーバーアップロード対象（1ファイル）
| ファイル | サーバーパス |
|---|---|
| style.css | `https://sakuranet-co.jp/style.css` |

### 🔍 検証方法
- Chrome DevTools で `?` ボタン Tab 押下 → サクラピンク輪郭（2px solid #c82054）が可視化されることを確認

---

## [v2.9.5] - 2026-04-20 — Phase 2E-A アクセシビリティ改善（セマンティック化・alt改善・:focus-visible）

### ♿ a11y 改善 — 3項目（見た目変化なし）

#### ① セマンティック main タグ化（全10HTML）
- `<div id="content" role="main">` → **`<main id="content">`** へ置換
  - 対象: `index / company / service / access / contact / recruit / news / concept / privacy / pay`
  - クラスは全て保持（`.site-content .sp-content .page-xxx`）・CSSへの影響なし
  - 冗長な `role="main"` 属性を削除（`<main>` タグで暗黙的に付与される）

#### ② alt属性の記述的改善（2HTML / 7箇所）
- **concept.html**: `alt="CEO"` → `alt="株式会社さくらねっと 代表取締役"`
- **service.html**: 6箇所の非記述的 alt を具体化
  - `images` → `PCサポート・トラブル解決`
  - `service-s01` → `SAKURA-NET Total Security クラウド型ネットワークセキュリティ`
  - `service-s02` → `年間・月間PC保守契約サービス`
  - `images` → `法人向けセキュリティSIM`
  - `download` → `WatchGuard UTMセキュリティ`
  - `images1` → `UniFi Security Gateway`

#### ③ `:focus-visible` キーボードフォーカススタイル追加（style.css）
- マウス操作時は非表示・キーボード Tab 操作時のみサクラピンク（`#c82054`）輪郭表示
- 対象要素: `a / button / input / select / textarea / [tabindex]`
- 既存 :focus ルールと競合しない設計（`:focus:not(:focus-visible)` で outline:none）

### ✅ 安全性検証
- 全10HTML のタグ整合性（div/main/header/footer 各 open=close）**完全一致**
- 旧パターン `<div id="content" role="main">` 残存 **0件**
- CSS で `div#content` / `div.site-content` セレクタ使用 **0件**（影響なし）
- 視覚的変化: **なし**（意味タグの置換とalt文言修正・フォーカス時のみ可視）

### 📦 バックアップ
- `backups/v2.9.5_pre-a11y/`（10HTML + style.css）

### 📤 サーバーアップロード対象（10ファイル）
| ファイル | サーバーパス |
|---|---|
| index.html | `https://sakuranet-co.jp/index.html` |
| company.html | `https://sakuranet-co.jp/company.html` |
| service.html | `https://sakuranet-co.jp/service.html` |
| access.html | `https://sakuranet-co.jp/access.html` |
| contact.html | `https://sakuranet-co.jp/contact.html` |
| recruit.html | `https://sakuranet-co.jp/recruit.html` |
| news.html | `https://sakuranet-co.jp/news.html` |
| concept.html | `https://sakuranet-co.jp/concept.html` |
| privacy.html | `https://sakuranet-co.jp/privacy.html` |
| pay.html | `https://sakuranet-co.jp/pay.html` |
| style.css | `https://sakuranet-co.jp/style.css` |

### ⚙ 補足
- Phase 2E-A は「見た目変化なし」スコープのみ実施
- ④ 色コントラスト検証＋微調整は未実施（要UI承認・Phase 2E-B 候補）
- `custom.html / custom1.html / status.html / remote.html` はスコープ外（今回未改修）
- 作業用 Python スクリプト `_a11y_semantic.py` は `trash/phase2e_2026-04-20/` へ退避

---

## [v2.9.4] - 2026-04-19 — Phase 2C `/data/` 監査整理（旧HTML・旧CSS・未参照画像・旧スクリプト）

### 🗑 trash 移動（合計 19ファイル / 約 1.13MB 削減）
- **旧HTML 12本**: `access/company/concept/contact/custom/custom1/index/news/pay/privacy/recruit/service.html`
  - ルート直下に現役版あり・`/data/` 版は未参照の旧バージョン
- **スナップショット**: `index_snapshot_20260223.html`（1本）
- **旧CSS**: `style.css`（ルート直下 `style.css` の旧コピー / 131KB）
- **未参照画像**: `IMG_6578.PNG`（573KB）/ `img_brand.png`（96KB）/ `udmoromax.png`（76KB）
  - `pay.html` が参照する `img_brand.png` は `/img/` 側・`/data/` の同名は別物
- **旧スクリプト**: `apply_responsive.ps1` / `apply_sakura.py`

### ✅ 残置必須（1本）
- **`/data/status_data.json`** — `status_api.php` が使用中（稼働データ）

### ✅ 安全性検証
- 全HTML/CSS/JS/PHP で `data/` 参照を grep → **残る参照は `status_data.json` のみ**
- 本番ファイルの壊れた参照 **0件**
- trash 移動先: `trash/phase2c_2026-04-19/`（最終削除はユーザーが `rm -rf trash/` で実施）

### 📊 `/data/` 整理後
- 整理前: 20ファイル / 約 1.2MB
- 整理後: 1ファイル / 3バイト（`status_data.json` のみ）

### ⚙ 補足
- 作業用 Python スクリプトは trash へ同梱退避
- サーバー側への変更なし（今回は**ローカルのみ**の整理）
- 次タスク: Phase 2B-WebP（残画像最適化・効果小で見送り候補） or Phase 2D/2E（UI承認必要）

### 📤 サーバーアップロード対象
**なし**（ローカル整理のみ・サーバーは現状維持）

---

## [v2.9.3] - 2026-04-19 — Phase 2B img/ 大掃除（未参照画像・PDF・ZIP・DOCX整理）

### 🗑 trash 移動（合計 322ファイル / 約 136.6MB 削減）
- **重複ヘッダー画像**: `header-main2.png` 〜 `header-main55.png`（54本 / 33.3MB）
  - 参照中の `header-main.png` は残置（style.cssで使用）
- **未参照 file*.pdf**: `file3`, `file5`, `file7`, `file8`, `file11`, `file13`〜`file20`, `file23`〜`file200`.pdf（180本 / 91.9MB）
  - posts/ が参照する `file1, 2, 4, 6, 9, 10, 12, 21, 22`.pdf は残置
- **画像フォルダ内のZIP**: `img/*.zip`（66本 / 10.4MB）
- **未参照DOCX**: `file2.docx` 〜 `file23.docx`（22本 / 0.95MB）
  - posts/ が参照する `file1.docx` は残置

### ✅ 安全性検証
- 全HTML/CSS/JS で画像参照を grep → **本番ファイルの壊れた参照 0件**
- 必須ファイル存在確認: `header-main.png` / `logo.png` / `ogp.jpg` / `favicon.ico` / file1-22.pdf / file1.docx すべて OK
- trash 移動先: `trash/phase2b_2026-04-19/`（最終削除はユーザーが `rm -rf trash/` で実施）

### 📊 img/ 整理後
- 整理前: 1,803ファイル / 163.5MB
- 整理後: 1,481ファイル / 約27MB
- **削減: 約 136.6MB（83%減）**

### ⚙ 補足
- 誤削除防止のため Tier 1/2 のみ実施（明らかな重複・未参照のみ）
- Tier 3（その他の未参照PNG/JPG ~50MB）は将来の個別検証時に判断
- サーバー側への変更なし（今回は**ローカルのみ**の整理）
- 次タスク: Phase 2C（`/data/` 監査）

### 📤 サーバーアップロード対象
**なし**（ローカル整理のみ・サーバーは現状維持）

---

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
