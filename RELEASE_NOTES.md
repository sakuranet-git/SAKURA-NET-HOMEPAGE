## [v3.0.19] - 2026-04-27 - company.html設立月補完・sitemap.xml更新

### 変更内容
- `company.html` — 設立年「2002年」→「2002年2月」に修正（概要テキスト・テーブル両方）
- `sitemap.xml` — 全ページのlastmodを `2026-04-27` に更新
- `sitemap.xml` — 新規5ページを追加登録
  - sakura-net-hikari.html（priority: 0.8）
  - unifi-network.html（priority: 0.8）
  - unifi-security.html（priority: 0.8）
  - sakura-net-mobile.html（priority: 0.7）
  - support.html（priority: 0.6）

---

## [v3.0.18] - 2026-04-27 - sakura-net-hikari.html 約款・別料金表セクション追加

### 変更内容
- `sakura-net-hikari.html` — 「Flow」後にTermsセクションを追加
  - SAKURA-NET光 サービス約款 → `https://sakuranet-co.jp/img/SAKURA-NET_Hikari_Service_Terms.html`
  - SAKURA-NET光 別料金表 → `https://sakuranet-co.jp/img/SAKURA-NET_Hikari_Service_Terms_Sub.html`

---

## [v3.0.16] - 2026-04-27 - service.htmlリンク全修正

### 変更内容
- `service.html` — 全サービスカードのリンク先を正しいページに修正

| サービス | 旧リンク | 新リンク |
|---|---|---|
| SAKURA-NET光 | posts/service1.html（Total Security）| sakura-net-hikari.html |
| UniFiネットワーク | posts/service2.html（PC保守契約）| unifi-network.html |
| Mobile/SIM | posts/service8.html（SAKURA-NET光）| sakura-net-mobile.html（作成予定）|
| 防犯カメラ | posts/service4.html（IT教室）| unifi-security.html |
| 入退室管理 | posts/service5.html（VPS）| unifi-security.html |
| クラウドUTM | posts/service7.html（IT SUPPORT）| posts/service6.html |
| クラウドVPS | posts/service6.html（UTM）| posts/service5.html |
| 法人ITサポート | posts/service3.html（ISP取次）| posts/service7.html |

---

## [v3.0.15] - 2026-04-27 - ヒーロースライドへイラスト追加・サービスカード画像化

### 変更内容
- `img/hero_cloud.png` — Gemini生成イラスト追加（クラウドインフラ）
- `img/hero_network.png` — Gemini生成イラスト追加（ITネットワーク抽象）
- `img/hero_office.png` — Gemini生成イラスト追加（モダンオフィス）
- `index.html` — ヒーロースライド3枚に右側アクセント画像を追加
  - スライド1（SAKURA-NET光）: `hero_cloud.png`
  - スライド2（UniFiネットワーク）: `hero_network.png`
  - スライド3（セキュリティ）: `hero_office.png`
- `index.html` — サービスカード5・6のアイコン（☁️・💻）を画像（`hero_cloud.png`/`hero_network.png`）に差し替え
- `style.css` — ヒーロー画像に `opacity:0.92` + スライド背景色グラデーションフェード追加

---

## [v3.0.6] - 2026-04-26 - 隠しリンク位置修正・SAKURA-NET光リンク修正

### 変更内容
- `index.html` — フッター社名「株式会社さくらねっと（SAKURA-NET）」の **（SAKURA-NET）** 部分に隠しリンク（`http://www.sakura-mode.net/cgi-bin/cbag/ag.exe?`）を設定（視覚的に同色・同スタイル）
- `index.html` — SAKURA-NET光「詳しく見る」リンクを `posts/service1.html`（誤）→ `posts/service8.html`（正：ひかり回線サービス）に修正（Solutions セクション・ヒーロースライド両方）

---

## [v3.0.5] - 2026-04-26 - pay.html レイアウト修正

### 変更内容
- `pay.html` — 決済画像レイアウト修正：`img_brand.png` を1列目（左寄せ）、`download2.jpg` / `Airpayqr.webp` / `rakutenpay.webp` を2列目（左寄せ）に整理
- `pay.html` — `img_brand.png` の `max-width` を 640px に拡大

---

## [v3.0.4] - 2026-04-26 - フッターロゴ差し替え・隠しリンク追加

### 変更内容
- 全ルートHTML（13件）・posts/（40件）のフッターロゴをテキスト → `logo_header_dark_bg.png` 画像に差し替え
- フッターロゴ内に隠しリンク追加（`http://www.sakura-mode.net/cgi-bin/cbag/ag.exe?`）

---

## [v3.0.3] - 2026-04-26 - ヘッダーロゴ画像差し替え

### 変更内容
- `img/logo_header_light_bg.png` — ヘッダー用ロゴ画像追加（450×60px、白背景用）
- `img/logo_header_dark_bg.png` — ダーク背景用ロゴ画像追加
- 全ルートHTML（13件）・posts/（40件）— ヘッダーロゴをテキスト組み合わせ → `logo_header_light_bg.png` 画像に差し替え（height:48px）

---

## [v3.0.2] - 2026-04-26 - ロゴ実装

### 変更内容
- `img/logo_icon.png` — Gemini作成の新ロゴアイコン追加（512×512 フラット）
- `img/logo_main.png` — ロゴフルバージョン追加（1200×1200）
- `img/apple-touch-icon.png` — logo_icon.png から 180×180 に差し替え
- 全ルートHTMLファイル（13件）— ヘッダーロゴにアイコン画像を追加
- posts/ 全HTMLファイル（40件）— ヘッダーロゴにアイコン画像を追加（パス: `../img/logo_icon.png`）

---

## [v3.0.1] - 2026-04-26 - バグ修正

### 変更内容
- `pay.html` — クレジットカード画像を `img_brand.webp`（低画質）→ `img_brand.png`（高画質）に変更
- `status.html` — v2 旧テンプレートから v3 ヘッダー/フッターへ換装（status-card CSS・API JS は全保持）

---

## [v3.0.0] - 2026-04-26 - MIMEYOI "Quiet Luxury" 全面リデザイン

### 概要
サイト全体を v2 系から MIMEYOI インスパイアの "Quiet Luxury" デザインへ完全刷新。
CSS・JS・全ページ（ルート15ページ + posts/40ページ）を統一テンプレートで再構築。

### 新デザインシステム
- **カラー:** ホワイト基調 / さくらピンク `#c97b8d` アクセント
- **フォント:** Outfit（英字）/ Noto Sans JP（日本語）
- **アニメーション:** `c-word` 文字アニメーション + `easeOut cubic-bezier(0.16,1,0.3,1)`
- **レイアウト:** BEM ライク命名（`l-`/`c-`/`p-`/`js-` プレフィックス）

### 変更ファイル（ルート）
- `style.css` — 完全書き直し（~650行、CSS変数・ダークCTA・カバーローダー等）
- `js/main.js` — 新規作成（カバーアニメ・ヘッダー・ワードアニメ・ヒーロースライダー・スクロールアニメ・ナビ画像切替）
- `index.html` — ヒーロースライダー（3枚）・Solutions・Stats・News・CTA
- `service.html` — ネットワーク/セキュリティ/クラウド&サポート 3セクション
- `company.html` — 企業概要テーブル11行 + 沿革14行（JSON-LD Organization）
- `concept.html` — 経営方針 + 代表メッセージ
- `access.html` — 地図埋め込み + 交通案内テーブル（JSON-LD Place）
- `contact.html` — 新規問い合わせ / 既存顧客 / Stripe連携
- `news.html` — news_api.php fetch 完全保持 + v3 UI
- `recruit.html` — 募集要項テーブル
- `pay.html` — 決済画像グリッド + 方法一覧テーブル
- `custom.html` — 代理店ダークヒーロー + 募集6項目
- `custom1.html` — サポート概要5セクション（ハラスメント対応方針含む）
- `privacy.html` — プライバシーポリシー + 特定商取引法テーブル
- `remote.html` — **変更なし**（RustDesk独立ページ）

### 変更ファイル（posts/ 40ファイル一括）
- 全ファイルのヘッダー/フッターを v3 テンプレートに置換
- 記事本文コンテンツ（inline CSS + article/div）は**そのまま保持**
- `../js/main.js` 参照に統一、旧 `navigation.js` 除去

### バックアップ
- `backups/v3.0.0_pre-redesign_20260426/` — 69ファイル（リデザイン前フルバックアップ）

---
## [v2.9.53] - 2026-04-26 - postsフォルダ 文字化け全修復（news1〜6 再作成）
### 変更内容
- `posts/news1.html` 〜 `posts/news6.html` を文字化けのため完全再作成
  - news1: 営業時間内・外の受付について（2020年06月02日）
  - news2: 各種お取次ぎのご案内（2020年05月01日）
  - news3: クラウド型VPS・セキュリティサービスを開始（2021年06月16日）
  - news4: 夏季休業期間のお知らせ（2024年07月01日）
  - news5: 値段改定のお知らせ（2024年05月10日）
  - news6: 名称変更のお知らせ（2024年07月01日）
- 全ファイルをMIMEYOIデザイン（`#c97b8d` / `#a85c70` / `#ead8dd`）で統一
- 前回セッションで復元済みのnews7〜13・faq1〜9はそのまま維持

### バックアップ
- `backups/v2.9.47_pre-encoding-fix/posts/` に修復前バックアップ保存済み（前セッション）

**変更ファイル:** posts/news1.html / news2.html / news3.html / news4.html / news5.html / news6.html / RELEASE_NOTES.md

---
## [v2.9.52] - 2026-04-25 - タイトル帯の短文化と見出し色調整（部分反映）
### 変更内容
- `index.html` の H1 を `株式会社さくらねっと` へ短縮し、補足文を追加
- `company.html` / `contact.html` / `recruit.html` / `access.html` / `custom.html` / `pay.html` / `custom1.html` の見出し色をやわらかい濃グレーへ調整
- 上記各ページに1行のサブコピーを追加し、長い説明をタイトルから分離
- `concept.html` / `news.html` はファイル使用中のため今回未反映

### バックアップ
- `backups/v2.9.52/` に更新前バックアップを保存

**変更ファイル:** index.html / company.html / contact.html / recruit.html / access.html / custom.html / pay.html / custom1.html / RELEASE_NOTES.md

---
## [v2.9.51] - 2026-04-25 - service.html タイトル文字組み調整
### 変更内容
- `service.html` の H1 `サービス一覧` のタイポグラフィを調整
- フォントウェイトを軽くし、字間と行間を整えて上品な見え方へ改善
- サブコピーとの間隔も少し広げ、タイトル帯の重さを緩和

### バックアップ
- `backups/v2.9.51/` に更新前 `service.html` / `RELEASE_NOTES.md` を保存

**変更ファイル:** service.html / RELEASE_NOTES.md

---
## [v2.9.50] - 2026-04-25 - service.html タイトル帯の文言整理
### 変更内容
- `service.html` のページタイトルH1を長文から `サービス一覧` へ変更
- 補足説明をサブコピーとして1行追加し、見た目の重さを軽減しつつ内容理解を維持

### バックアップ
- `backups/v2.9.50/` に更新前 `service.html` / `RELEASE_NOTES.md` を保存

**変更ファイル:** service.html / RELEASE_NOTES.md

---
## [v2.9.49] - 2026-04-25 - SAKURA-NET光ロゴを service.html へ実装
### 変更内容
- `service.html` の「SAKURA-NET光 + UniFi機器」カードへ `img/sakura-net-hikari-logo-primary.svg` を追加
- カード見出しと製品画像の間にロゴ帯を新設し、既存レイアウトを崩さずサービスロゴとして視認性を確保

### バックアップ
- `backups/v2.9.49/` に更新前 `service.html` / `RELEASE_NOTES.md` を保存

**変更ファイル:** service.html / RELEASE_NOTES.md

---
## [v2.9.48] - 2026-04-25 - SAKURA-NET光ロゴ正式格納
### 変更内容
- Gemini作成の `SAKURA-NET光` ロゴ最終SVGデータを HP側 `img/` に正式格納
- 用途別に以下4ファイルへ整理
  - `img/sakura-net-hikari-logo-primary.svg`
  - `img/sakura-net-hikari-logo-whitebg.svg`
  - `img/sakura-net-hikari-logo-wide.svg`
  - `img/sakura-net-hikari-logo-small.svg`
- 今後のサイト実装、資料転用、WordPress移行時に再利用しやすい命名へ統一

### バックアップ
- `backups/v2.9.48/` に更新前 `RELEASE_NOTES.md` を保存

**変更ファイル:** img/sakura-net-hikari-logo-primary.svg / img/sakura-net-hikari-logo-whitebg.svg / img/sakura-net-hikari-logo-wide.svg / img/sakura-net-hikari-logo-small.svg / RELEASE_NOTES.md

---
## [v2.9.47] - 2026-04-25 - MIMEYOIトーンの固定ページ横展開（7ページ）
### 変更内容
- `concept.html` / `news.html` / `recruit.html` / `access.html` / `custom.html` / `pay.html` / `custom1.html` のヘッダー背景を `#fffdfd` に統一
- キャッチフレーズ、ナビ、ページタイトル、フッターを `#c97b8d` / `#a85c70` / `#ead8dd` / `#f8f4f4` ベースへ調整
- 会社情報ボックスの太い枠線と影を弱め、タイトル帯の背景を薄グレー寄りに変更
- フッターの区切り線とコピー色を落ち着かせ、トップ・サービス・会社概要・お問い合わせページとのトーン差を縮小

### バックアップ
- `backups/v2.9.47/` に更新前バックアップを保存

**変更ファイル:** concept.html / news.html / recruit.html / access.html / custom.html / pay.html / custom1.html / RELEASE_NOTES.md

---
# SAKURA-NET 繝励Ξ繝溘い繝UI 謾ｹ菫ｮ螻･豁ｴ (RELEASE_NOTES)

## [v2.9.46] - 2026-04-25 窶・MIMEYOI 繝・じ繧､繝ｳ 3繝壹・繧ｸ讓ｪ螻暮幕・・ervice / contact / company・・
### 耳 繝・じ繧､繝ｳ邨ｱ荳
- `service.html` / `contact.html` / `company.html` 縺ｫ index.html 縺ｨ蜷後§ MIMEYOI 繧ｫ繝ｩ繝ｼ繧ｷ繧ｹ繝・Β繧帝←逕ｨ
- CSS螟画焚繝悶Ο繝・け・・:root { --sakura: #c97b8d; ... }`・峨ｒ蜷・・繝ｼ繧ｸ `</head>` 逶ｴ蜑阪↓霑ｽ蜉
- `body { -webkit-font-smoothing: antialiased; }` 霑ｽ蜉

### 耳 螟画峩蜀・ｮｹ・亥・3繝壹・繧ｸ蜈ｱ騾夲ｼ・- 繝倥ャ繝繝ｼ閭梧勹: `#fff0f5` 竊・`#fffdfd`・・g-base・・- 繧ｳ繝ｳ繝・Φ繝・・繝・ム繝ｼ閭梧勹: 蜷御ｸ・- 繧ｭ繝｣繝・メ繝輔Ξ繝ｼ繧ｺ: `#c82054` 竊・`#c97b8d`縲》ext-shadow 蜑企勁
- 莨夂､ｾ諠・ｱ繝懊ャ繧ｯ繧ｹ: border 2px #ffc1d3 竊・1px #ead8dd縲｜ox-shadow 蜑企勁
- 繝翫ン繝斐Ν: color `#c82054` 竊・`#c97b8d`縲｜order 2px 竊・1px #ead8dd縲”over bg `#e03164` 竊・`#a85c70`縲《hadow蜑企勁縲》ranslateY -3px 竊・-2px
- 繝壹・繧ｸ繧ｿ繧､繝医Ν閭梧勹: `#fff4f7` 竊・`#f8f4f4`縲｜order 竊・#ead8dd
- H1: `#c82054` 竊・`#2f2a2a`・域悽譁・牡繝ｻ關ｽ縺｡逹縺・◆繝医・繝ｳ・・- 繝輔ャ繧ｿ繝ｼ閭梧勹: `#fff4f7` 竊・`#f8f4f4`縲｜order-top 3px 竊・1px #ead8dd
- 繝輔ャ繧ｿ繝ｼcopyright繝ｻ繝励Λ繧､繝舌す繝ｼ繝ｪ繝ｳ繧ｯ: `#c82054` 竊・`#6f6262`

### 耳 service.html 蝗ｺ譛牙､画峩
- SEO intro繧ｻ繧ｯ繧ｷ繝ｧ繝ｳ: bg #fffafb 竊・#f8f4f4縲｜order 2px 竊・1px #ead8dd縲《hadow蜑企勁
- 繧ｵ繝ｼ繝薙せ繧ｫ繝ｼ繝栄3: bg `#c82054` 竊・`#c97b8d`縲《hadow蜑企勁
- .sakura-card: border #ffc1d3 竊・#ead8dd縲《hadow蜑企勁縲”over border 竊・#c97b8d
- .sakura-card header: bg `#fff0f5` 竊・`#f8f4f4`縲｜order-bottom dashed #ffc1d3 竊・#ead8dd
- .sakura-card h4/a: `#c82054` 竊・`#c97b8d`
- .sakura-btn: bg `#e03164` 竊・`#a85c70`
- OEM/UniFi/Mobile/DoRACOON 繧ｫ繝ｼ繝・ border 2px 竊・1px #ead8dd縲∝・驛ｨ繧｢繧ｯ繧ｻ繝ｳ繝医き繝ｩ繝ｼ譖ｴ譁ｰ

### 耳 contact.html 蝗ｺ譛牙､画峩
- .sakura-dl-container: bg `#fff0f5` 竊・`#f8f4f4`縲｜order 竊・#ead8dd縲《hadow蜑企勁
- .sakura-dl-title: color `#c82054` 竊・`#c97b8d`縲｜order 2px dashed 竊・1px
- .sakura-definition-list dt: color `#c82054` 竊・`#c97b8d`縲｜order 竊・#ead8dd
- .sakura-info-box: border 2px 竊・1px #ead8dd縲《hadow蜑企勁
- 繧ｵ繝昴・繝医メ繧ｱ繝・ヨ繝懊ち繝ｳ: bg `#e03164` 竊・`#a85c70`縲《hadow蜑企勁
- 繝上う繝ｩ繧､繝医ヱ繝ｩ繧ｰ繝ｩ繝・ bg `#fff0f5` 竊・`#f8f4f4`縲｜order-left 5px #ffc1d3 竊・4px #ead8dd

### 耳 company.html 蝗ｺ譛牙､画峩
- .sakura-definition-list・医げ繝ｪ繝・ラ蝙具ｼ・ border `#ffc1d3` 竊・`#ead8dd`
- dt閭梧勹: `#fff4f7` 竊・`#f8f4f4`縲…olor `#c82054` 竊・`#c97b8d`
- 莨夂､ｾ讎りｦ√う繝ｳ繝医Οdiv: bg `#fff4f7` 竊・`#f8f4f4`縲｜order 竊・#ead8dd
- 繧ｵ繝悶ユ繧ｭ繧ｹ繝・ `#a08090` 竊・`#6f6262`
- 豐ｿ髱ｩ繧ｳ繝ｳ繝・リ: bg `#fff4f7` 竊・`#f8f4f4`
- 螻雁・逡ｪ蜿ｷ繝ｻ繧､繝ｳ繝懊う繧ｹ繝ｪ繝ｳ繧ｯ: `#c82054` 竊・`#c97b8d`

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.46_pre-mimeyoi-3pages/service.html`
- `backups/v2.9.46_pre-mimeyoi-3pages/contact.html`
- `backups/v2.9.46_pre-mimeyoi-3pages/company.html`

**螟画峩繝輔ぃ繧､繝ｫ:** service.html / contact.html / company.html / RELEASE_NOTES.md

---

## [v2.9.45] - 2026-04-25 窶・繝九Η繝ｼ繧ｹ谺・ヵ繧ｩ繝ｼ繝ｫ繝舌ャ繧ｯ菫ｮ豁｣繝ｻnews12 繧偵い繝ｼ繧ｫ繧､繝悶↓霑ｽ蜉

### 菅 繝舌げ菫ｮ豁｣
- `index.html`: 繝ｭ繝ｼ繧ｫ繝ｫ file:// 陦ｨ遉ｺ譎ゅ↓ Chrome 縺・fetch 繧偵ヶ繝ｭ繝・け縺励ル繝･繝ｼ繧ｹ縺碁撼陦ｨ遉ｺ縺ｫ縺ｪ繧句撫鬘後ｒ菫ｮ豁｣
  - 繧､繝ｳ繝ｩ繧､繝ｳ JSON 繝・・繧ｿ・・<script id="news-inline-data" type="application/json">`・峨ｒ霑ｽ蜉
  - API繝ｻarchive fetch 荳｡譁ｹ縺悟､ｱ謨励＠縺溷ｴ蜷医・繝輔か繝ｼ繝ｫ繝舌ャ繧ｯ縺ｨ縺励※譛譁ｰ5莉ｶ繧堤｢ｺ螳溘↓陦ｨ遉ｺ
- `posts/news_archive.html`: news12・医＆縺上ｉ繧､繝ｳ繧ｿ繝ｼ繝阪ャ繝医Γ繝ｳ繝・リ繝ｳ繧ｹ縺ｮ縺顔衍繧峨○ / 2026-04-01・峨ｒ霑ｽ險・
### 卵・・繧ｴ繝溘ヵ繧｡繧､繝ｫ蜃ｦ逅・- .tmp.* 繝輔ぃ繧､繝ｫ 25譛ｬ繧・trash/ 縺ｫ遘ｻ蜍・
### 堂 繧､繝ｳ繝ｩ繧､繝ｳ髱咏噪繝九Η繝ｼ繧ｹ繝・・繧ｿ・域怙譁ｰ5莉ｶ・・1. 縺輔￥繧峨う繝ｳ繧ｿ繝ｼ繝阪ャ繝医Γ繝ｳ繝・リ繝ｳ繧ｹ縺ｮ縺顔衍繧峨○ - 2026-04-01
2. SAKURA-NET繧ｵ繝ｼ繝薙せ螂醍ｴ・ｴ・ｬｾ・域怙譁ｰ迚茨ｼ・ 2026-02-22
3. 閾ｨ譎ゆｼ第･ｭ縺ｮ縺顔衍繧峨○ - 2025-11-01
4. SAKURA-IPPBX蛻ｩ逕ｨ髢句ｧ九・縺顔衍繧峨○ - 2025-10-01
5. SAKURA-NET蜈蛾幕蟋九・縺顔衍繧峨○ - 2024-11-01

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.45_pre-news-fix/index.html`
- `backups/v2.9.45_pre-news-fix/posts/news_archive.html`

**螟画峩繝輔ぃ繧､繝ｫ:** index.html / posts/news_archive.html / RELEASE_NOTES.md

---

## [v2.9.44] - 2026-04-25 窶・index.html MIMEYOI鬚ｨ縲碁撕縺九↑鬮倡ｴ壽─縲榊ｱ謇謾ｹ蝟・
### 耳 繝・じ繧､繝ｳ謾ｹ蝟・ｼ・0鬆・岼・・- CSS螟画焚 `--sakura / --sakura-deep / --sakura-border / --bg-base / --bg-light / --text-main / --text-sub` 霑ｽ蜉
- `body { -webkit-font-smoothing: antialiased; }` 縺ｫ繧医ｋ繝輔か繝ｳ繝医Ξ繝ｳ繝繝ｪ繝ｳ繧ｰ謾ｹ蝟・- `@keyframes fadeUp` 霑ｽ蜉繝ｻ繝偵・繝ｭ繝ｼ繧ｻ繧ｯ繧ｷ繝ｧ繝ｳ・・#hero-flex`・峨↓ 0.7s 繧｢繝九Γ繝ｼ繧ｷ繝ｧ繝ｳ驕ｩ逕ｨ
- 繝倥ャ繝繝ｼ繝ｻcontenthead 閭梧勹: `#fff0f5` 竊・`#fffdfd`・医け繝ｪ繝ｼ繝ｳ繝帙Ρ繧､繝茨ｼ・- 繧ｭ繝｣繝・メ繝輔Ξ繝ｼ繧ｺ: `color #c82054` 竊・`#c97b8d`縲～text-shadow` 髯､蜴ｻ
- 諠・ｱ繝懊ャ繧ｯ繧ｹ: `border 2px #ffc1d3` 竊・`1px #ead8dd`縲～box-shadow` 髯､蜴ｻ
- 繝壹・繧ｸ繧ｿ繧､繝医Ν閭梧勹: `#fff4f7` 竊・`#f8f4f4`縲｜order 竊・`#ead8dd`
- H1: `color #c82054` 竊・`#2f2a2a`・医ム繝ｼ繧ｯ繝・く繧ｹ繝茨ｼ・- 隱ｲ鬘後・繝・け繧ｹ: `bg #fff4f7` 竊・`#f8f4f4`縲｜order 竊・`#ead8dd`縲”3/CTA繝・く繧ｹ繝・竊・`#c97b8d`
- 繧ｽ繝ｪ繝･繝ｼ繧ｷ繝ｧ繝ｳ隕句・縺・ `border-bottom 2px #e03164` 竊・`1px #ead8dd`縲～color` 竊・`#c97b8d`
- 繧ｽ繝ｪ繝･繝ｼ繧ｷ繝ｧ繝ｳ繧ｫ繝ｼ繝・譫・ `border #e0e0e0` 竊・`#ead8dd`縲～box-shadow` 髯､蜴ｻ
- 繧ｫ繝ｼ繝・h4 濶ｲ: `#c82054` 竊・`#c97b8d`・亥・5譫夲ｼ・- 繝翫ン pill 繝懊ち繝ｳ: `border 2px #ffc1d3` 竊・`1px #ead8dd`縲∬牡 竊・`#c97b8d`縲”over 竊・`#a85c70`縲《hadow 髯､蜴ｻ
- 繝九Η繝ｼ繧ｹ谺・CSS: border/shadow/gradient 繧・`#c97b8d/#a85c70/#ead8dd` 繝医・繝ｳ縺ｫ邨ｱ荳
- 繧ｹ繝・・繧ｿ繧ｹ繧｢繝ｩ繝ｼ繝医ヰ繝翫・: bg `#fff0f5` 竊・`#f8f4f4`縲｜order `2px #ffc1d3` 竊・`1px #ead8dd`縲〕eft accent `5px #c82054` 竊・`4px #c97b8d`
- CTA 繧ｵ繝悶ユ繧ｭ繧ｹ繝・ `#a08090` 竊・`#6f6262`
- 繝輔ャ繧ｿ繝ｼ: bg `#fff4f7` 竊・`#f8f4f4`縲～border-top 3px` 竊・`1px #ead8dd`
- 繝輔ャ繧ｿ繝ｼ copyright繝ｻ繝励Λ繧､繝舌す繝ｼ繝ｪ繝ｳ繧ｯ: `#c82054` 竊・`#6f6262`

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.44_pre-mimeyoi/index/index.html` 縺ｫ譖ｴ譁ｰ蜑阪ｒ菫晏ｭ俶ｸ医∩

**螟画峩繝輔ぃ繧､繝ｫ:** index.html / RELEASE_NOTES.md

---

## [v2.9.43] - 2026-04-25 窶・faq_archive.html 縺ｫ jirei1.html 縺ｸ縺ｮ蜀・Κ繝ｪ繝ｳ繧ｯ霑ｽ蜉

### 迫 蜀・Κ蟆守ｷ夊ｿｽ蜉
- `posts/faq_archive.html` 縺ｮFAQ荳隕ｧ荳矩Κ縺ｫ縲悟ｰ主・莠倶ｾ九阪ヶ繝ｭ繝・け繧定ｿｽ蜉
- jirei1.html 縺ｸ縺ｮ繝ｪ繝ｳ繧ｯ繝懊ち繝ｳ繧・contact CTA 縺ｮ荳翫↓驟咲ｽｮ
- contact CTA 縺ｯ縺昴・縺ｾ縺ｾ邯ｭ謖・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.43_pre-jirei-link/posts/faq_archive.html` 縺ｫ譖ｴ譁ｰ蜑阪ｒ菫晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** posts/faq_archive.html / RELEASE_NOTES.md

---

## [v2.9.42] - 2026-04-25 窶・faq_archive.html 繧偵・繝ｬ繝溘い繝繧ｹ繧ｿ繧､繝ｫ縺ｫ蜈ｨ髱｢譖ｴ譁ｰ

### 耳 繧ｹ繧ｿ繧､繝ｫ謾ｹ蝟・- `posts/faq_archive.html` 繧呈立繝・Φ繝励Ξ繝ｼ繝医せ繧ｿ繧､繝ｫ縺九ｉ蛟句挨FAQ繝壹・繧ｸ蜷檎ｭ峨・縺輔￥繧峨ヴ繝ｳ繧ｯ繧ｹ繧ｿ繧､繝ｫ縺ｫ蜈ｨ髱｢譖ｸ縺咲峩縺・- 繝倥ャ繝繝ｼ繝ｻ繝翫ン繝ｻ繝輔ャ繧ｿ繝ｼ繧・faq5縲彷aq9 縺ｨ邨ｱ荳
- FAQ荳隕ｧ繧・`.faq-item` 繧ｫ繝ｼ繝牙ｽ｢蠑擾ｼ医・繝舌・繧｢繝九Γ繝ｼ繧ｷ繝ｧ繝ｳ莉倥″・峨↓蛻ｷ譁ｰ
- meta description 繧定ｿｽ蜉
- 繝壹・繧ｸ荳矩Κ縺ｫ contact.html 縺ｸ縺ｮCTA繧定ｿｽ蜉

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.42_pre-faq-archive-restyle/posts/faq_archive.html` 縺ｫ譖ｴ譁ｰ蜑阪ｒ菫晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** posts/faq_archive.html / RELEASE_NOTES.md

---

## [v2.9.41] - 2026-04-25 窶・FAQ 5譛ｬ繝ｻ蟆主・莠倶ｾ・1譛ｬ縺ｮ譁ｰ隕剰ｿｽ蜉

### 統 螟画峩蜀・ｮｹ
- `posts/faq5.html` 譁ｰ隕丈ｽ懈・・啣. UniFi縺ｮ蟆主・雋ｻ逕ｨ縺ｯ縺ｩ縺ｮ縺上ｉ縺・°縺九ｊ縺ｾ縺吶°・・- `posts/faq6.html` 譁ｰ隕丈ｽ懈・・啣. SAKURA-NET蜈峨・譛磯｡肴侭驥代→蝗樒ｷ夐溷ｺｦ繧呈蕗縺医※縺上□縺輔＞
- `posts/faq7.html` 譁ｰ隕丈ｽ懈・・啣. 豕穂ｺｺIT繧ｵ繝昴・繝医・縺ｩ縺薙∪縺ｧ蟇ｾ蠢懊＠縺ｦ繧ゅｉ縺医∪縺吶°・・- `posts/faq8.html` 譁ｰ隕丈ｽ懈・・啣. 繝ｪ繝｢繝ｼ繝医〒蟇ｾ蠢懊＠縺ｦ繧ゅｉ縺医∪縺吶°・溷・蠑ｵ雋ｻ縺ｯ縺九°繧翫∪縺吶°・・- `posts/faq9.html` 譁ｰ隕丈ｽ懈・・啣. 髦ｲ迥ｯ繧ｫ繝｡繝ｩ繧・・騾螳､邂｡逅・・蟾･莠九ｂ蟇ｾ蠢懊＠縺ｦ縺・∪縺吶°・・- `posts/jirei1.html` 譁ｰ隕丈ｽ懈・・壹仙ｰ主・莠倶ｾ九大､ｧ髦ｪ蟶ょ・縺ｮ蟆丞｣ｲ讌ｭ讒倥∈UniFi繝阪ャ繝医Ρ繝ｼ繧ｯ・矩亟迥ｯ繧ｫ繝｡繝ｩ繧剃ｸ諡ｬ蟆主・
- `posts/faq_archive.html` 譖ｴ譁ｰ・喃aq5縲彷aq9 縺ｮ5莉ｶ繧剃ｸ隕ｧ縺ｫ霑ｽ蜉
- `posts/faq4.html` ・医ユ繧ｹ繝医ヵ繧｡繧､繝ｫ・峨ｒ trash/ 縺ｫ遘ｻ蜍・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.41_pre-faq-jirei/posts/faq_archive.html` 縺ｫ譖ｴ譁ｰ蜑阪ｒ菫晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** posts/faq5-faq9.html / posts/jirei1.html / posts/faq_archive.html / RELEASE_NOTES.md

---

## [v2.9.40] - 2026-04-25 窶・FAQ繝ｻ蟆主・莠倶ｾ九・SEO險ｭ險域嶌繧定ｿｽ蜉

### 統 螟画峩蜀・ｮｹ
- `SEO_CONTENT_PLAN_FAQ_CASES.md` 繧呈眠隕丈ｽ懈・
- FAQ譯医∝ｰ主・莠倶ｾ区｡医∫漁縺・､懃ｴ｢繝・・繝槭∝ｮ溯｣・婿驥昴，laude Code蜷代￠萓晞ｼ譁・ｒ謨ｴ逅・- 谺｡繝輔ぉ繝ｼ繧ｺ縺ｮSEO蠑ｷ蛹悶・蝨溷床縺ｨ縺励※蛻ｩ逕ｨ縺ｧ縺阪ｋ蜀・ｮｹ繧呈枚譖ｸ蛹・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.40/` 縺ｫ `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** SEO_CONTENT_PLAN_FAQ_CASES.md / RELEASE_NOTES.md

---

## [v2.9.39] - 2026-04-25 窶・繧ｽ繝ｪ繝･繝ｼ繧ｷ繝ｧ繝ｳ繧ｫ繝ｼ繝臥判蜒集ebP霆ｽ驥丞喧

### 笞｡ 繝代ヵ繧ｩ繝ｼ繝槭Φ繧ｹ謾ｹ蝟・- card_*.png (險・.35MB) 竊・card_*.webp quality82 (險・64KB) 縺ｫ螟画鋤・育ｴ・3%蜑頑ｸ幢ｼ・  - card_sakura_hikari: 581KB 竊・42KB
  - card_unifi_network: 639KB 竊・59KB
  - card_unifi_protect: 553KB 竊・33KB
  - card_unifi_access: 577KB 竊・30KB
- index.html 縺ｮ img 蜿ら・繧・.png 竊・.webp 縺ｫ4邂・園螟画峩
- 蜈・・ .png 繝輔ぃ繧､繝ｫ縺ｯ繝舌ャ繧ｯ繧｢繝・・縺ｨ縺励※ img/ 縺ｫ菫晄戟

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.39_pre-webp-optimize/` 縺ｫ `index.html` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / RELEASE_NOTES.md / img/card_*.webp・域眠隕・轤ｹ・・
---

## [v2.9.38] - 2026-04-25 窶・index.html 繧ｽ繝ｪ繝･繝ｼ繧ｷ繝ｧ繝ｳ繧ｫ繝ｼ繝臥判蜒冗ｵｱ荳

### 笨ｨ 謾ｹ蝟・- 縲後た繝ｪ繝･繝ｼ繧ｷ繝ｧ繝ｳ繝ｻ繝医ヴ繝・け繧ｹ縲・繧ｫ繝ｼ繝峨ｒ逕ｻ蜒丈ｻ倥″縺ｧ邨ｱ荳
  - SAKURA-NET蜈・竊・`card_sakura_hikari.png`
  - UniFi繝阪ャ繝医Ρ繝ｼ繧ｯ 竊・`card_unifi_network.png`
  - UniFi Protect 竊・`card_unifi_protect.png`・井ｸ矩Κ譌ｧ逕ｻ蜒上ｒ髯､蜴ｻ縺嶺ｸ企Κ縺ｫ邨ｱ荳・・  - UniFi Access 竊・`card_unifi_access.png`
- 蜈ｨ繧ｫ繝ｼ繝・`height:160px; object-fit:cover` 縺ｧ鬮倥＆繝ｻ豈皮紫繧貞ｮ悟・邨ｱ荳
- 雋繝槭・繧ｸ繝ｳ謇区ｳ輔〒繧ｫ繝ｼ繝我ｸ企Κ繝輔Λ繝・す繝･驟咲ｽｮ繝ｻ譌｢蟄倥Ξ繧､繧｢繧ｦ繝亥ｴｩ繧後↑縺・
### 名 譁ｰ隕冗判蜒擾ｼ・mg/縺ｫ驟咲ｽｮ・・- `img/card_sakura_hikari.png`
- `img/card_unifi_network.png`
- `img/card_unifi_protect.png`
- `img/card_unifi_access.png`

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.38_pre-solution-cards/` 縺ｫ `index.html` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / RELEASE_NOTES.md

---

## [v2.9.37] - 2026-04-25 窶・index.html 繝偵・繝ｭ繝ｼ逕ｻ蜒丞ｷｮ縺玲崛縺医・OGP譖ｴ譁ｰ繝ｻ讖溷勣繝薙ず繝･繧｢繝ｫ霑ｽ蜉

### 笨ｨ 謾ｹ蝟・- 繝偵・繝ｭ繝ｼ逕ｻ蜒上ｒ `modern_network.webp` 竊・`fv_main_visual.png`・域ｳ穂ｺｺ繧ｪ繝輔ぅ繧ｹ繝ｻ縺輔￥繧芽牡繝・・繝橸ｼ峨↓蟾ｮ縺玲崛縺・- 繝偵・繝ｭ繝ｼ譛ｬ譁・ｸ九↓蝨ｰ蝓溷ｯ・捩繝・く繧ｹ繝・陦瑚ｿｽ蜉・亥､ｧ髦ｪ豺蟾晏玄諡轤ｹ ・・豕穂ｺｺ蟆る摩 ・・2002蟷ｴ蜑ｵ讌ｭ ・・蜈ｨ蝗ｽ繝ｪ繝｢繝ｼ繝亥ｯｾ蠢懶ｼ・- UniFi Protect繧ｫ繝ｼ繝峨↓ `unifi_equipment_visual.png`・・P+髦ｲ迥ｯ繧ｫ繝｡繝ｩ・峨ｒ霑ｽ蜉
- OGP繝ｻTwitter Card 逕ｻ蜒上ｒ `ogp.jpg` 竊・`ogp_2026.png` 縺ｫ譖ｴ譁ｰ

### 名 譁ｰ隕冗判蜒擾ｼ・mg/縺ｫ驟咲ｽｮ・・- `img/fv_main_visual.png` 窶・繝偵・繝ｭ繝ｼ逕ｨ
- `img/unifi_equipment_visual.png` 窶・UniFi讖溷勣繝薙ず繝･繧｢繝ｫ逕ｨ
- `img/ogp_2026.png` 窶・OGP逕ｨ

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.37_pre-index-image-update/` 縺ｫ `index.html` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / RELEASE_NOTES.md

---

## [v2.9.36] - 2026-04-25 窶・service.html 蝟ｶ讌ｭ譎る俣繝舌げ菫ｮ豁｣繝ｻ蜀・Κ蟆守ｷ壼ｼｷ蛹・
### 菅 繝舌げ菫ｮ豁｣
- CTA繝懊ち繝ｳ縺ｮ蝟ｶ讌ｭ譎る俣 `10:00-17:00` 竊・`10:00-19:00` 縺ｫ菫ｮ豁｣

### 笨ｨ 謾ｹ蝟・- `href="#"` 縺縺｣縺滉ｸｻ蜉帙し繝ｼ繝薙せ繧ｫ繝ｼ繝峨・繝ｪ繝ｳ繧ｯ繧・`contact.html` 縺ｫ螟画峩・・niFi繝ｻSAKURA-NET蜈峨・Mobile繝ｻDoRACOON・・- UniFi繝阪ャ繝医Ρ繝ｼ繧ｯ繝ｻ繧ｻ繧ｭ繝･繝ｪ繝・ぅ讖溷勣繧ｫ繝ｼ繝峨↓縲後＃逶ｸ隲・・縺願ｦ狗ｩ阪ｊ縺ｯ縺薙■繧峨阪・繧ｿ繝ｳ霑ｽ蜉
- SAKURA-NET Total IT SUPPORT繧ｫ繝ｼ繝峨↓縲後＃逶ｸ隲・・縺願ｦ狗ｩ阪ｊ縺ｯ縺薙■繧峨阪・繧ｿ繝ｳ霑ｽ蜉
- 繧ｯ繝ｩ繧ｦ繝蔚TM繧ｫ繝ｼ繝峨↓縲後＃逶ｸ隲・・縺願ｦ狗ｩ阪ｊ縺ｯ縺薙■繧峨阪・繧ｿ繝ｳ霑ｽ蜉

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.36_pre-service-fix/` 縺ｫ `service.html` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** service.html / RELEASE_NOTES.md

---

## [v2.9.35] - 2026-04-25 窶・company.html 蝟ｶ讌ｭ譎る俣繝舌げ菫ｮ豁｣繝ｻ菫｡鬆ｼ譚先侭霑ｽ蜉

### 菅 繝舌げ菫ｮ豁｣
- CTA繝懊ち繝ｳ縺ｮ蝟ｶ讌ｭ譎る俣 `10:00-17:00` 竊・`10:00-19:00` 縺ｫ菫ｮ豁｣

### 笨ｨ 謾ｹ蝟・- 蜀帝ｭ繝懊ャ繧ｯ繧ｹ縺ｫ菫｡鬆ｼ繝昴う繝ｳ繝・陦瑚ｿｽ蜉・・002蟷ｴ險ｭ遶・・・螟ｧ髦ｪ蟶よｷ蟾晏玄譛ｬ遉ｾ ・・雉・悽驥・・・蠕捺･ｭ蜩｡謨ｰ ・・蜈ｨ蝗ｽ繝ｪ繝｢繝ｼ繝亥ｯｾ蠢懶ｼ・- 髮ｻ豌鈴壻ｿ｡莠区･ｭ螻雁・逡ｪ蜿ｷ縺ｫ縲鯉ｼ育ｷ丞漁逵∝ｱ雁・貂医∩莠区･ｭ閠・ｼ峨崎｣懆ｶｳ霑ｽ險・- 繧､繝ｳ繝懊う繧ｹ逡ｪ蜿ｷ縺ｫ縲鯉ｼ磯←譬ｼ隲区ｱよ嶌逋ｺ陦御ｺ区･ｭ閠・匳骭ｲ貂医∩・峨崎｣懆ｶｳ霑ｽ險・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.35_pre-company-fix/` 縺ｫ `company.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** company.html / RELEASE_NOTES.md

---

## [v2.9.34] - 2026-04-25 窶・contact.html 蝟ｶ讌ｭ譎る俣繝舌げ菫ｮ豁｣繝ｻ逶ｸ隲・ｾ玖ｿｽ蜉

### 菅 繝舌げ菫ｮ豁｣
- TEL谺・・蝟ｶ讌ｭ譎る俣 `10・・0・・7:00` 竊・`10・・0・・9・・0` 縺ｫ菫ｮ豁｣
- CTA繝懊ち繝ｳ縺ｮ蝟ｶ讌ｭ譎る俣 `10:00-17:00` 竊・`10:00-19:00` 縺ｫ菫ｮ豁｣

### 笨ｨ 謾ｹ蝟・- 縲梧眠隕上＃逶ｸ隲・・縺秘｣邨｡蜈医阪・荳翫↓縲後％繧薙↑縺皮嶌隲・′螟壹＞縺ｧ縺吶港<ul>` 5莉ｶ繧定ｿｽ蜉・・VR謾ｹ蝟・ｼ・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.34_pre-contact-fix/` 縺ｫ `contact.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** contact.html / RELEASE_NOTES.md

---

## [v2.9.33] - 2026-04-25 窶・index.html CTA蝟ｶ讌ｭ譎る俣繝舌げ菫ｮ豁｣繝ｻ螳牙ｿ・枚霑ｽ蜉

### 菅 繝舌げ菫ｮ豁｣
- CTA繝懊ち繝ｳ縺ｮ蝟ｶ讌ｭ譎る俣縺・`10:00-17:00` 縺ｨ隱､縺｣縺ｦ縺・◆縺ｮ繧・`10:00-19:00` 縺ｫ菫ｮ豁｣・医・繝・ム繝ｼ縺ｨ荳堺ｸ閾ｴ縺縺｣縺滂ｼ・
### 笨ｨ 謾ｹ蝟・- 邨ｱ荳CTA縺ｮlead譁・忰蟆ｾ縺ｫ縲悟・蝗ｽ繝ｪ繝｢繝ｼ繝亥ｯｾ蠢懊ｂ謇ｿ繧翫∪縺吶ゅ阪ｒ霑ｽ險・- CTA蜀・↓繧ｵ繝悶ユ繧ｭ繧ｹ繝茨ｼ域ｳ穂ｺｺ蟆る摩 ・・螳悟・莠育ｴ・宛縺ｧ荳∝ｯｧ蟇ｾ蠢・・・蟆主・縺九ｉ驕狗畑繧ｵ繝昴・繝医∪縺ｧ荳雋ｫ蟇ｾ蠢懶ｼ峨ｒ霑ｽ蜉

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.33_pre-cta-fix/` 縺ｫ `index.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / RELEASE_NOTES.md

---

## [v2.9.32] - 2026-04-25 窶・Claude Code蜷代￠谺｡繝輔ぉ繝ｼ繧ｺ謖・､ｺ譖ｸ繧定ｿｽ蜉

### 統 螟画峩蜀・ｮｹ
- `CLAUDE_CODE_NEXT_PHASE_BRIEF.md` 繧呈眠隕丈ｽ懈・
- 迴ｾ蝨ｨ縺ｮ謾ｹ蝟・憾豕√∝ｴｩ繧後ｄ縺吶＞繝昴う繝ｳ繝医∵ｬ｡繝輔ぉ繝ｼ繧ｺ縺ｮ蜆ｪ蜈磯・ｽ阪，laude Code蜷代￠萓晞ｼ譁・ｒ謨ｴ逅・- 縲悟､ｧ隕乗ｨ｡繝ｪ繝・じ繧､繝ｳ縺ｧ縺ｯ縺ｪ縺丞ｱ謇謾ｹ蝟・阪ｒ谺｡繝輔ぉ繝ｼ繧ｺ縺ｮ蝓ｺ譛ｬ譁ｹ驥昴→縺励※譏取枚蛹・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.32/` 縺ｫ `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** CLAUDE_CODE_NEXT_PHASE_BRIEF.md / RELEASE_NOTES.md

---

## [v2.9.31] - 2026-04-24 窶・title / meta description / OGP譁・ｨ繧呈怙邨りｪｿ謨ｴ

### 笨ｨ 螟画峩蜀・ｮｹ
- `index.html` 縺ｮ title / description / OGP / Twitter譁・ｨ繧偵ゞniFi蟆主・繝ｻ繝阪ャ繝医Ρ繝ｼ繧ｯ讒狗ｯ峨・豕穂ｺｺIT繧ｵ繝昴・繝郁ｻｸ縺ｸ隱ｿ謨ｴ
- `contact.html` 縺ｮ meta 譁・ｨ繧・`縺雁撫縺・粋繧上○繝ｻ縺皮嶌隲㌔ 霆ｸ縺ｫ謨ｴ逅・- `company.html` 縺ｮ meta 譁・ｨ繧・`莨∵･ｭ讎りｦ√・莨夂､ｾ諠・ｱ` 縺ｨ莠区･ｭ蜀・ｮｹ縺御ｼ昴ｏ繧句ｽ｢縺ｸ隱ｿ謨ｴ
- `service.html` 縺ｮ meta 譁・ｨ繧剃ｸｻ蜉帙し繝ｼ繝薙せ縺悟・縺ｫ莨昴ｏ繧玖｡ｨ迴ｾ縺ｸ謨ｴ逅・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.31/` 縺ｫ `index.html` `contact.html` `company.html` `service.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / contact.html / company.html / service.html / RELEASE_NOTES.md

---

## [v2.9.30] - 2026-04-24 窶・繧ｵ繝ｼ繝薙せ繝壹・繧ｸ蜀帝ｭ縺ｮ荳ｻ蜉幄ｨｴ豎ゅｒ謨ｴ逅・
### 笨ｨ 螟画峩蜀・ｮｹ
- `service.html` 蜀帝ｭ縺ｮ譯亥・譁・ｒ隱ｿ謨ｴ縺励∽ｸｻ蜉帙し繝ｼ繝薙せ繧・`UniFi蟆主・繝ｻSAKURA-NET蜈峨・髦ｲ迥ｯ繧ｫ繝｡繝ｩ繝ｻ蜈･騾螳､邂｡逅・・豕穂ｺｺIT繧ｵ繝昴・繝・ 縺ｨ譏守｢ｺ蛹・- 蟇ｾ蠢懊し繝ｼ繝薙せ荳隕ｧ縺ｮ鬆・ｺ上ｒ謨ｴ逅・＠縲∽ｸｻ蜉帙し繝ｼ繝薙せ縺悟・縺ｫ莨昴ｏ繧九ｈ縺・ｪｿ謨ｴ
- 譌｢蟄倥き繝ｼ繝我ｸ隕ｧ繧・ｩｳ邏ｰ繧ｳ繝ｳ繝・Φ繝・・讒矩縺ｯ邯ｭ謖・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.30/` 縺ｫ `service.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** service.html / RELEASE_NOTES.md

---

## [v2.9.29] - 2026-04-24 窶・莨夂､ｾ讎りｦ√・繝ｼ繧ｸ縺ｮ蜀帝ｭ隕∫ｴ・ｒ霑ｽ蜉

### 笨ｨ 螟画峩蜀・ｮｹ
- `company.html` 縺ｮ莨∵･ｭ讎りｦ∽ｸ隕ｧ縺ｮ蜑阪↓縲∽ｺ区･ｭ蜀・ｮｹ縺ｨ蠑ｷ縺ｿ繧剃ｼ昴∴繧玖ｦ∫ｴ・・繝・け繧ｹ繧定ｿｽ蜉
- 螟ｧ髦ｪ諡轤ｹ縲∵ｳ穂ｺｺ蜷代￠繝阪ャ繝医Ρ繝ｼ繧ｯ讒狗ｯ峨ゞniFi蟆主・縲・亟迥ｯ縲∝・騾螳､邂｡逅・！T繧ｵ繝昴・繝医ｒ譏守｢ｺ蛹・- 譌｢蟄倥・莨夂､ｾ諠・ｱ繝ｻ豐ｿ髱ｩ繝ｻ隧ｳ邏ｰ諠・ｱ縺ｮ讒矩縺ｯ邯ｭ謖・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.29/` 縺ｫ `company.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** company.html / RELEASE_NOTES.md

---

## [v2.9.28] - 2026-04-24 窶・縺雁撫縺・粋繧上○繝壹・繧ｸ縺ｮ譁ｰ隕冗嶌隲・ｰ守ｷ壹ｒ謨ｴ逅・
### 笨ｨ 螟画峩蜀・ｮｹ
- `contact.html` 蜀帝ｭ縺ｫ譁ｰ隕冗嶌隲・髄縺代・譯亥・繝懊ャ繧ｯ繧ｹ繧定ｿｽ蜉
- `髮ｻ隧ｱ繝ｻFAX / E-mail` 隕句・縺励ｒ `譁ｰ隕上＃逶ｸ隲・・縺秘｣邨｡蜈・ 縺ｸ螟画峩
- 譌｢蟄倥・縺雁ｮ｢讒伜髄縺鷹°逕ｨ譯亥・縺ｫ隕句・縺励ｒ霑ｽ蜉縺励∵眠隕冗嶌隲・髄縺第ュ蝣ｱ縺ｨ蛹ｺ蛻･縺励ｄ縺吶￥謨ｴ逅・- 繝ｬ繧､繧｢繧ｦ繝域ｧ矩縺ｯ邯ｭ謖√＠縲∵枚險荳ｭ蠢・〒謾ｹ蝟・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.28/` 縺ｫ `contact.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** contact.html / RELEASE_NOTES.md

---

## [v2.9.27] - 2026-04-24 窶・繝ｭ繝ｼ繧ｫ繝ｫ陦ｨ遉ｺ逕ｨ繝九Η繝ｼ繧ｹ谺・ヵ繧ｩ繝ｼ繝ｫ繝舌ャ繧ｯ霑ｽ蜉

### 肌 菫ｮ豁｣蜀・ｮｹ
- `index.html` 縺ｮ繝九Η繝ｼ繧ｹ蜿門ｾ怜・逅・ｒ謾ｹ蝟・- 蜈ｬ髢狗腸蠅・〒縺ｯ蠕捺擂騾壹ｊ `/system/news_api.php` 繧剃ｽｿ逕ｨ
- `file://` 陦ｨ遉ｺ繧・API 螟ｱ謨玲凾縺ｯ `posts/news_archive.html` 縺九ｉ譛譁ｰ繝九Η繝ｼ繧ｹ繧呈歓蜃ｺ縺励※陦ｨ遉ｺ
- 繝ｭ繝ｼ繧ｫ繝ｫ遒ｺ隱肴凾縺ｧ繧ゅヨ繝・・縺ｮ繝九Η繝ｼ繧ｹ繝ｻ縺顔衍繧峨○谺・′陦ｨ遉ｺ縺輔ｌ繧九ｈ縺・ｯｾ蠢・
### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.27/` 縺ｫ `index.html` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / RELEASE_NOTES.md

---

## [v2.9.26] - 2026-04-24 窶・邱頑･蠕ｩ譌ｧ・医ヨ繝・・縺ｮ螟ｧ隕乗ｨ｡繝ｬ繧､繧｢繧ｦ繝亥､画峩繧貞叙繧頑ｶ医＠・・
### 竢ｪ 蠕ｩ譌ｧ蜀・ｮｹ
- `index.html` `contact.html` `style.css` 繧・`backups/v2.9.25/` 繝吶・繧ｹ縺ｸ蠕ｩ譌ｧ
- 繝医ャ繝励・繝ｼ繧ｸ縺ｮ螟ｧ隕乗ｨ｡繝ｬ繧､繧｢繧ｦ繝亥､画峩繧貞叙繧頑ｶ医＠縲∵里蟄倥・螳牙ｮ壹Ξ繧､繧｢繧ｦ繝医∈謌ｻ縺・- 蠕ｩ譌ｧ蠕後∝ｴｩ繧後↑縺・ｯ・峇縺ｧ繝医ャ繝励・H1繝ｻ繝偵・繝ｭ繝ｼ譁・ｨ繝ｻCTA譁・ｨ縺ｮ縺ｿ蜀崎ｪｿ謨ｴ
- 繝九Η繝ｼ繧ｹ谺・・蛻晄悄譁・ｨ繧・`繝九Η繝ｼ繧ｹ荳隕ｧ繝壹・繧ｸ` 縺ｸ縺ｮ譯亥・莉倥″縺ｫ螟画峩

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.26/` 縺ｫ蠕ｩ譌ｧ蜑阪・ `index.html` `contact.html` `style.css` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / contact.html / style.css / RELEASE_NOTES.md

---

## [v2.9.25] - 2026-04-24 窶・繝医ャ繝苓ｨｴ豎ゅ・蝠上＞蜷医ｏ縺帛ｰ守ｷ壹・OEM陦ｨ迴ｾ謾ｹ蝟・
### 笨ｨ 螟画峩蜀・ｮｹ
- `index.html` 繧貞・讒区・縺励√ヨ繝・・縺ｮH1繧・`螟ｧ髦ｪ縺ｮ繧ｪ繝輔ぅ繧ｹIT縲√☆縺ｹ縺ｦ繧偵・縺ｨ縺､縺ｮ遯灘哨縺ｧ縲Ａ 縺ｫ螟画峩
- 繝医ャ繝励・繝ｼ繧ｸ縺ｫ `繧医￥縺ゅｋ隱ｲ鬘形 `荳ｻ隕√し繝ｼ繝薙せ` `驕ｸ縺ｰ繧後ｋ逅・罰` `蟆主・縺ｮ豬√ｌ` `莨夂､ｾ諠・ｱ` 繧ｻ繧ｯ繧ｷ繝ｧ繝ｳ繧定ｿｽ蜉
- 繝医ャ繝励・繝輔ぃ繝ｼ繧ｹ繝医ン繝･繝ｼ縺ｫ辟｡譁咏嶌隲④TA縺ｨ莨夂､ｾ諠・ｱ繧ｵ繝槭Μ繝ｼ繧定ｿｽ蜉
- 繝九Η繝ｼ繧ｹ谺・・蛻晄悄陦ｨ遉ｺ繧・`隱ｭ縺ｿ霎ｼ縺ｿ荳ｭ...` 縺九ｉ縲√ル繝･繝ｼ繧ｹ荳隕ｧ縺ｸ縺ｮ譯亥・縺､縺阪・繝ｬ繝ｼ繧ｹ繝帙Ν繝縺ｸ螟画峩
- `service.html` 縺ｮOEM繧ｻ繧ｯ繧ｷ繝ｧ繝ｳ縺九ｉ `笳銀雷笳義 繝励Ξ繝ｼ繧ｹ繝帙Ν繝陦ｨ迴ｾ繧帝勁蜴ｻ縺励∝・髢句髄縺第枚險縺ｸ菫ｮ豁｣
- `contact.html` 縺ｫ譁ｰ隕冗嶌隲・髄縺大ｰ守ｷ壹ヶ繝ｭ繝・け繧定ｿｽ蜉縺励∵里蟄倬｡ｧ螳｢蜷代￠驕狗畑譯亥・縺ｨ蛻・屬
- `style.css` 縺ｫ證冶牡繝九Η繝ｼ繝医Λ繝ｫ + Notion繝ｩ繧､繧ｯ縺ｪ蜈ｱ騾壹け繝ｩ繧ｹ鄒､繧定ｿｽ蜉

### 翌 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.25/` 縺ｫ `index.html` `service.html` `contact.html` `style.css` `RELEASE_NOTES.md` 繧剃ｿ晏ｭ・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / service.html / contact.html / style.css / RELEASE_NOTES.md

---

## [v2.9.24] - 2026-04-24 窶・繝ｭ繝ｼ繝ｫ繝舌ャ繧ｯ・・2.9.22迥ｶ諷九↓蠕ｩ蜈・ｼ・
### 竢ｪ 繝ｭ繝ｼ繝ｫ繝舌ャ繧ｯ蜀・ｮｹ
- **v2.9.23 (Notion Design System驕ｩ逕ｨ)** 繧貞叙繧頑ｶ医＠
- index.html / style.css 繧・v2.9.22 迥ｶ諷九∈蠕ｩ蜈・- 逅・罰・哢otion Blue驕ｩ逕ｨ縺ｫ繧医ｊ縺輔￥繧峨・縺｣縺ｨ縺ｮ繝斐Φ繧ｯ繝悶Λ繝ｳ繝峨′螟ｱ繧上ｌ縺溽ぜ

### 迴ｾ蝨ｨ縺ｮ譛牙柑繝輔ぉ繝ｼ繧ｺ
- Phase 3-A 譁咎≡繧ｵ繝槭Μ繝ｼ・・2.9.17・・- Phase 3-B 繧ｿ繧､繝昴げ繝ｩ繝輔ぅ・・nter / Noto Sans JP・・- Phase 3-C 繧ｹ繝槭・蠑ｷ蛹厄ｼ・obile-cta-bar・・- Phase 3-D 繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ繧｢繝九Γ・・croll-animate.js・・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / style.css

---

## [v2.9.23] - 2026-04-24 窶・~~Phase 3-G: Notion Design System 驕ｩ逕ｨ~~ 窶ｻv2.9.24縺ｧ繝ｭ繝ｼ繝ｫ繝舌ャ繧ｯ貂医∩

### 耳 螟画峩蜀・ｮｹ
- **繧ｫ繝ｩ繝ｼ繝・・繝槫姐譁ｰ**: 繝斐Φ繧ｯ邉ｻ (#c82054) 竊・Notion Blue (#0075de) + 證冶牡繝九Η繝ｼ繝医Λ繝ｫ
- **閭梧勹濶ｲ**: `#fff0f5` / `#fff4f7` 竊・`#ffffff` / `#f6f5f4` (Notion warm white)
- **繝懊・繝繝ｼ**: 繝斐Φ繧ｯ繝懊・繝繝ｼ 竊・繧ｦ繧｣繧ｹ繝代・繝懊・繝繝ｼ `1px solid rgba(0,0,0,0.1)`
- **繧ｫ繝ｼ繝峨・繧ｷ繝｣繝峨え**: Notion 4螻､繧ｷ繝｣繝峨え繧ｹ繧ｿ繝・け驕ｩ逕ｨ
- **繝・く繧ｹ繝郁牡**: `rgba(0,0,0,0.95)` warm near-black 縺ｫ邨ｱ荳
- **CTA繝懊ち繝ｳ**: Notion Blue (#0075de) / hover: #005bab
- **繝輔か繝ｳ繝・*: Inter + Noto Sans JP (繝ｬ繝ｳ繝繝ｪ繝ｳ繧ｰ蠑ｷ蛹悶・letter-spacing霑ｽ蜉)
- **繝上Φ繝舌・繧ｬ繝ｼ繝｡繝九Η繝ｼ**: 繝斐Φ繧ｯ 竊・Notion Blue
- **繝｢繝舌う繝ｫCTA繝舌・**: 繝斐Φ繧ｯ 竊・Notion Blue
- **繝九Η繝ｼ繧ｹ繧ｫ繝ｼ繝峨・繧ｹ繝・・繧ｿ繧ｹ繝舌リ繝ｼ**: Notion Blue 繧｢繧ｯ繧ｻ繝ｳ繝・- **譁咎≡譌ｩ隕玖｡ｨ繝ｻUnified CTA**: Notion 繧ｹ繧ｿ繧､繝ｫ邨ｱ荳
- CSS螟画焚 (`--n-blue`, `--n-warm-white`, `--n-shadow` 遲・ 蟆主・

### 迴ｾ蝨ｨ縺ｮ譛牙柑繝輔ぉ繝ｼ繧ｺ
- Phase 3-A 譁咎≡繧ｵ繝槭Μ繝ｼ・・2.9.17・・- Phase 3-B 繧ｿ繧､繝昴げ繝ｩ繝輔ぅ・・nter / Noto Sans JP・・- Phase 3-C 繧ｹ繝槭・蠑ｷ蛹厄ｼ・obile-cta-bar・・- Phase 3-D 繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ繧｢繝九Γ・・croll-animate.js・・- **Phase 3-G Notion Design System・・2.9.23・・* 竊・NEW

**螟画峩繝輔ぃ繧､繝ｫ:** index.html / style.css

---

## [v2.9.22] - 2026-04-20 窶・繝ｭ繝ｼ繝ｫ繝舌ャ繧ｯ・・2.9.19迥ｶ諷九↓蠕ｩ蜈・ｼ・
### 竢ｪ 繝ｭ繝ｼ繝ｫ繝舌ャ繧ｯ蜀・ｮｹ
- **v2.9.20 (繝偵・繝ｭ繝ｼ繝ｪ繝九Η繝ｼ繧｢繝ｫ)** 縺ｨ **v2.9.21 (繝輔か繝ｳ繝亥ｷｮ謌ｻ縺・** 繧貞叙繧頑ｶ医＠
- index.html / style.css 繧・v2.9.19 迥ｶ諷九∈蠕ｩ蜈・- 逅・罰・壹ヲ繝ｼ繝ｭ繝ｼ謾ｹ菫ｮ ﾃ・譌ｧ繝輔か繝ｳ繝亥ｷｮ謌ｻ縺励〒蜈ｨ菴薙ョ繧ｶ繧､繝ｳ縺悟乾蛹悶＠縺溽ぜ

### 迴ｾ蝨ｨ縺ｮ譛牙柑繝輔ぉ繝ｼ繧ｺ
- Phase 3-A 譁咎≡繧ｵ繝槭Μ繝ｼ・・2.9.17・・- Phase 3-B 繧ｿ繧､繝昴げ繝ｩ繝輔ぅ・・nter / Noto Sans JP・・- Phase 3-C 繧ｹ繝槭・蠑ｷ蛹厄ｼ・obile-cta-bar・・- Phase 3-D 繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ繧｢繝九Γ・・croll-animate.js・・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / style.css

---

## [v2.9.21] - 2026-04-20 窶・繝輔か繝ｳ繝医・繝｡繝ｼ繝ｫ繝ｪ繝ｳ繧ｯ菫ｮ豁｣・遺ｻv2.9.22縺ｧ繝ｭ繝ｼ繝ｫ繝舌ャ繧ｯ貂茨ｼ・
### 肌 菫ｮ豁｣蜀・ｮｹ・亥叙繧頑ｶ医＠・・- 繝輔か繝ｳ繝医ｒ蜈・・繝偵Λ繧ｮ繝・繝｡繧､繝ｪ繧ｪ邉ｻ縺ｫ謌ｻ縺呻ｼ・nter/Noto Sans JP 蜑企勁縲；oogle Fonts隱ｭ縺ｿ霎ｼ縺ｿ髯､蜴ｻ・・- `info@sakuranet-co.jp` 縺ｮmailto繝ｪ繝ｳ繧ｯ繧偵・繝ｬ繝ｼ繝ｳ繝・く繧ｹ繝医↓螟画峩

**螟画峩繝輔ぃ繧､繝ｫ:** index.html / style.css

---

## [v2.9.20] - 2026-04-20 窶・繝偵・繝ｭ繝ｼ繝ｪ繝九Η繝ｼ繧｢繝ｫ・・hase 3-E・会ｼ遺ｻv2.9.22縺ｧ繝ｭ繝ｼ繝ｫ繝舌ャ繧ｯ貂茨ｼ・
### 減 繝偵・繝ｭ繝ｼ繧ｻ繧ｯ繧ｷ繝ｧ繝ｳ蛻ｷ譁ｰ・亥叙繧頑ｶ医＠・・
- 繧ｰ繝ｩ繝・・繧ｷ繝ｧ繝ｳ閭梧勹・・fff0f5竊・fce4ed・会ｼ九し繧ｯ繝ｩ闃ｱ縺ｳ繧峨い繝九Γ・・SS keyframes・・- 1谺｡CTA繝懊ち繝ｳ縲檎┌譁咏嶌隲・・縺薙■繧峨搾ｼ医ヴ繝ｳ繧ｯ繧ｰ繝ｩ繝・ｼ会ｼ矩崕隧ｱCTA霑ｽ蜉
- 繧ｭ繝｣繝・メ繧ｳ繝斐・繧・`hero-lead`・・.3em, bold, #c82054・峨↓蠑ｷ蛹・- `prefers-reduced-motion` 蟇ｾ蠢懶ｼ郁干縺ｳ繧峨い繝九Γ辟｡蜉ｹ蛹厄ｼ・- 繧ｹ繝槭・蟇ｾ蠢懶ｼ・TA蟷・00%繝ｻ繝代ョ繧｣繝ｳ繧ｰ隱ｿ謨ｴ・・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / style.css

---

## [v2.9.19] - 2026-04-20 窶・繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ繧｢繝九Γ・・hase 3-D・・
### 笨ｨ IntersectionObserver fade-in 繧｢繝九Γ繝ｼ繧ｷ繝ｧ繝ｳ

- 譁ｰ隕・ `scroll-animate.js` 窶・繝壹・繧ｸ隕∫ｴ縺檎判髱｢蜀・↓蜈･繧九→ fade-in + slide-up
- 蟇ｾ雎｡隕∫ｴ: `.sp-page-title` / `.unified-cta` / `.pricing-summary` / `#hero-flex`
- `prefers-reduced-motion` 蟇ｾ蠢懶ｼ医い繝九Γ辟｡蜉ｹ蛹厄ｼ・- `style.css` 縺ｫ `.sa-hidden` / `.sa-visible` 繧ｯ繝ｩ繧ｹ霑ｽ蜉

**螟画峩繝輔ぃ繧､繝ｫ:** scroll-animate.js・域眠隕擾ｼ・/ style.css / 蜈ｨ10繝壹・繧ｸ・・cript 繧ｿ繧ｰ霑ｽ蜉・・
---

## [v2.9.18] - 2026-04-20 窶・繧ｹ繝槭・迚亥ｼｷ蛹厄ｼ・hase 3-C・・
### 導 繧ｹ繝槭・蝗ｺ螳咾TA繝舌・繝ｻ繧ｿ繝・・繧ｿ繝ｼ繧ｲ繝・ヨ繝ｻ繝上Φ繝舌・繧ｬ繝ｼ謾ｹ蝟・
- **C-1**: 蜈ｨ10繝壹・繧ｸ縺ｫ繧ｹ繝槭・荳矩Κ蝗ｺ螳咾TA繝舌・霑ｽ蜉・磯崕隧ｱ繝ｻ繝輔か繝ｼ繝繝懊ち繝ｳ・・- **C-2**: 繝｢繝舌う繝ｫ繝｡繝九Η繝ｼ螻暮幕譎ゅ・繧ｿ繝・・繧ｿ繝ｼ繧ｲ繝・ヨ譛蟆・4px縺ｫ諡｡螟ｧ
- **C-3**: 繝上Φ繝舌・繧ｬ繝ｼ繝懊ち繝ｳ繧偵ヶ繝ｩ繝ｳ繝峨き繝ｩ繝ｼ・・c82054竊・e03164繧ｰ繝ｩ繝・ｼ峨↓繝ｪ繝・じ繧､繝ｳ
- `body { padding-bottom: 56px }` 繧偵せ繝槭・縺ｮ縺ｿ驕ｩ逕ｨ縺励∝崋螳壹ヰ繝ｼ縺ｸ縺ｮ髫繧後ｒ髦ｲ豁｢

**螟画峩繝輔ぃ繧､繝ｫ:** style.css / index.html / company.html / service.html / access.html / contact.html / recruit.html / news.html / concept.html / privacy.html / pay.html

---

## [v2.9.17] - 2026-04-20 窶・邨ｱ荳CTA繝悶Ο繝・け 蜈ｨ繝壹・繧ｸ螻暮幕・・hase 3-B・・
### 到 CTA繝悶Ο繝・け pay.html 縺ｸ霑ｽ蜉

- pay.html 縺ｫ縺ｮ縺ｿ邨ｱ荳CTA繝悶Ο繝・け・磯崕隧ｱ繝ｻ繝輔か繝ｼ繝・峨ｒ霑ｽ蜉・井ｻ・繝壹・繧ｸ縺ｯv2.9.9貂医∩・・
**螟画峩繝輔ぃ繧､繝ｫ:** pay.html

---

## [v2.9.16] - 2026-04-20 窶・繧ｿ繧､繝昴げ繝ｩ繝輔ぅ蛻ｷ譁ｰ Google Fonts 蟆主・・・hase 3-A・・
### 筈 Inter + Noto Sans JP 霑ｽ蜉

- 蜈ｨ10繝壹・繧ｸ縺ｫ Google Fonts・・nter / Noto Sans JP・峨ｒ霑ｽ蜉
- style.css body font-family 縺ｮ蜈磯ｭ縺ｫ `'Inter', 'Noto Sans JP'` 繧定ｿｽ蜉
- 譌｢蟄倥・繧ｷ繧ｹ繝・Β繝輔か繝ｳ繝茨ｼ医ヲ繝ｩ繧ｮ繝弱・繝｡繧､繝ｪ繧ｪ遲会ｼ峨・繝輔か繝ｼ繝ｫ繝舌ャ繧ｯ縺ｨ縺励※邯ｭ謖・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / company.html / service.html / access.html / contact.html / recruit.html / news.html / concept.html / privacy.html / pay.html / style.css

---

## [v2.9.15] - 2026-04-20 窶・逕ｻ蜒集ebP螟画鋤・・hase 2・・
### 名・・img繧淡ebP蛹悶〒螟ｧ蟷・ｻｽ驥丞喧

ffmpeg縺ｧ10譫壹ｒWebP螟画鋤・・uality 85・峨ょ・繝輔ぃ繧､繝ｫ縺ｯimg/縺ｫ菫晄戟縲・
| 逕ｻ蜒・| 螟画鋤蜑・| 螟画鋤蠕・| 蜑頑ｸ帷紫 |
|---|---|---|---|
| udm-pro-max.png | 76KB | 5KB | -93% |
| page-0001.jpg | 21KB | 4KB | -79% |
| service-s01.jpg | 33KB | 8KB | -76% |
| img_brand.png | 95KB | 42KB | -56% |
| Airpayqr.JPG | 41KB | 20KB | -50% |
| rakutenpay.JPG | 36KB | 20KB | -46% |
| modern_network.png | 34KB | 21KB | -40% |
| service-s02.jpg | 13KB | 4KB | -66% |
| images.png | 窶・| 螟画鋤貂医∩ | 窶・|
| download.jpg | 12KB | 9KB | -29% |

窶ｻ`images.jpg`(5KB)繝ｻ`images1.jpg`(13%縺ｮ縺ｿ)繝ｻ`download2.jpg`(蠅怜刈)縺ｯ螟画鋤蟇ｾ雎｡螟・
**螟画峩繝輔ぃ繧､繝ｫ:** index.html / service.html / concept.html / pay.html・・rc螻樊ｧ螟画峩・・
---

## [v2.9.14] - 2026-04-20 窶・蜈ｨimg繧ｿ繧ｰ縺ｫ lazy loading 霑ｽ蜉・・hase 2・・
### 笞｡ 繝代ヵ繧ｩ繝ｼ繝槭Φ繧ｹ謾ｹ蝟・窶・`loading="lazy" decoding="async"` 霑ｽ蜉

| 繝輔ぃ繧､繝ｫ | 霑ｽ蜉謨ｰ |
|---|---|
| index.html | 1譫・|
| service.html | 7譫・|
| concept.html | 1譫・|
| pay.html | 4譫・|

蜷郁ｨ・3譫壹ゅヲ繝ｼ繝ｭ繝ｼ莉･螟悶・蜈ｨ繧ｳ繝ｳ繝・Φ繝・判蜒上′繧ｹ繧ｯ繝ｭ繝ｼ繝ｫ譎ゅ・縺ｿ隱ｭ縺ｿ霎ｼ縺ｾ繧後ｋ縺溘ａ蛻晄悄LCP縺梧隼蝟・・
---

## [v2.9.13] - 2026-04-20 窶・access.html 繝｡繧ｿ繧ｿ繧ｰ菴乗園隱､險倅ｿｮ豁｣

### 肌 access.html 縺ｮ meta description / OGP / Twitter Card 菴乗園繧剃ｿｮ豁｣

`譛ｨ蟾晄悽逕ｺ4-3-4 5F` 竊・`譛ｨ蟾晄擲4-3-34 5F`・・邂・園・・
**螟画峩邂・園:** access.html 陦・0繝ｻ陦・9繝ｻ陦・7

---

## [v2.9.12] - 2026-04-20 窶・JSON-LD菴乗園隱､險倅ｿｮ豁｣

### 肌 index.html 縺ｮ讒矩蛹悶ョ繝ｼ繧ｿ菴乗園繧剃ｿｮ豁｣

JSON-LD・・rganization繝ｻLocalBusiness・峨・ `streetAddress` 縺後梧惠蟾晄悽逕ｺ4-3-4 5F縲阪→隱､險倥＆繧後※縺・◆縺溘ａ縲∵ｭ｣縺励＞菴乗園縲梧惠蟾晄擲4-3-34 5F縲阪↓菫ｮ豁｣縲よ悽譁⑨TML縺ｯ譌｢縺ｫ豁｣縺励°縺｣縺溘◆繧∝ｽｱ髻ｿ縺ｪ縺励・
**螟画峩邂・園:** index.html 陦・0繝ｻ陦・9・・SON-LD 2邂・園・・
---

## [v2.9.11] - 2026-04-20 窶・譁咎≡譌ｩ隕玖｡ｨ繧ｻ繧ｯ繧ｷ繝ｧ繝ｳ霑ｽ蜉・・hase 3-#4B・・
### 腸 service.html 縺ｫ縲御ｸｻ隕√し繝ｼ繝薙せ譁咎≡譌ｩ隕玖｡ｨ縲阪ｒ霑ｽ蜉

譌｢蟄倥・蜈ｬ髢倶ｾ｡譬ｼ縺ｮ縺ｿ繧剃ｽｿ逕ｨ縺励◆6繧ｫ繝ｼ繝画ｧ区・縺ｮ譁咎≡譌ｩ隕玖｡ｨ繧・service.html 荳矩Κ・育ｵｱ荳CTA繝悶Ο繝・け逶ｴ蜑搾ｼ峨↓譁ｰ險ｭ縲ゅΘ繝ｼ繧ｶ繝ｼ縺御ｾ｡譬ｼ繧貞叉蠎ｧ縺ｫ謚頑升縺ｧ縺阪∝撫縺・粋繧上○蜑阪・諢乗晄ｱｺ螳壹ｒ蠕梧款縺励☆繧気V謾ｹ蝟・命遲悶・
### 搭 謗ｲ霈峨し繝ｼ繝薙せ・域里蟄伜・髢倶ｾ｡譬ｼ縺ｮ縺ｿ繝ｻ謐城縺ｪ縺暦ｼ・
| # | 繧ｵ繝ｼ繝薙せ | 萓｡譬ｼ陦ｨ遉ｺ |
|---|---|---|
| 1 | Total IT SUPPORT | 譛磯｡・4,000蜀・・|
| 2 | 繧ｯ繝ｩ繧ｦ繝蔚TM | 譛磯｡・2,500蜀・・|
| 3 | VPS繧ｵ繝ｼ繝舌・ | 譛磯｡・550蜀・・|
| 4 | PC繧ｵ繝昴・繝茨ｼ域ｳ穂ｺｺ・・| 蜃ｺ蠑ｵ雋ｻ + 菴懈･ｭ莉｣ |
| 5 | PC繧ｵ繝昴・繝茨ｼ亥倶ｺｺ・・| 蜃ｺ蠑ｵ雋ｻ + 菴懈･ｭ莉｣ |
| 6 | IT繝ｻ繝代た繧ｳ繝ｳ謨吝ｮ､ | 谿ｵ髫主宛譁咎≡ |

**豕ｨ驥・*: 縲娯ｻ萓｡譬ｼ縺ｯ遞取栢・剰ｩｳ邏ｰ繝ｻ譛譁ｰ繝励Λ繝ｳ縺ｯ蝟ｶ讌ｭ諡・ｽ薙∪縺ｧ縺雁撫縺・粋繧上○縺上□縺輔＞縲ゅ・
### 耳 繝・じ繧､繝ｳ莉墓ｧ・
- 繝斐Φ繧ｯ繝悶Λ繝ｳ繝臥ｶｭ謖・ｼ・#c82054`蝓ｺ隱ｿ繝ｻ`#ffc1d3`繧｢繧ｯ繧ｻ繝ｳ繝医・`#ffd4e0`繝懊・繝繝ｼ・・- 繧ｫ繝ｼ繝・ 逋ｽ閭梧勹 / 隗剃ｸｸ12px / 阮・ヴ繝ｳ繧ｯ繝懊・繝繝ｼ / 繝帙ヰ繝ｼ縺ｧ豬ｮ縺堺ｸ翫′繧・- 萓｡譬ｼ: `#c82054` 縺ｧ蠑ｷ隱ｿ陦ｨ遉ｺ・・.6em 螟ｪ蟄暦ｼ・- 繝ｬ繧ｹ繝昴Φ繧ｷ繝・ PC 3蛻・/ 繧ｿ繝悶Ξ繝・ヨ 2蛻暦ｼ・60px・・/ 繧ｹ繝槭・ 1蛻暦ｼ・40px・・- 繧ｿ繝・・鬆伜沺: 譛蟆・8px・医い繧ｯ繧ｻ繧ｷ繝薙Μ繝・ぅ遒ｺ菫晢ｼ・- 隕句・縺・ `h2 #pricing-summary-heading` + `aria-labelledby`

### 唐 螟画峩繝輔ぃ繧､繝ｫ

| 繝輔ぃ繧､繝ｫ | 螟画峩蜀・ｮｹ |
|---|---|
| style.css | 譛ｫ蟆ｾ縺ｫ `.pricing-summary*` CSS 繧堤ｴ・10陦瑚ｿｽ蜉・・2.9.11繝悶Ο繝・け・・|
| service.html | 邨ｱ荳CTA繝悶Ο繝・け逶ｴ蜑阪↓ `<section class="pricing-summary">` 繧呈諺蜈･ |

### 笨・讀懆ｨｼ

- 譌｢蟄倥・繧ｵ繝ｼ繝薙せ險倅ｺ倶ｸ隕ｧ・・5繧ｫ繝ｼ繝会ｼ峨・蝠・刀繝ｪ繧ｹ繝茨ｼ・繧ｫ繝ｼ繝会ｼ峨・邨ｱ荳CTA縺ｫ蠖ｱ髻ｿ縺ｪ縺・- CSS霑ｽ蜉縺ｮ縺ｿ・域里蟄倥そ繝ｬ繧ｯ繧ｿ螟画峩縺ｪ縺暦ｼ・- 繝斐Φ繧ｯ繝悶Λ繝ｳ繝峨・譌｢蟄倥ヵ繝・ち繝ｼ繝ｻ繝倥ャ繝繝ｼ縺ｯ辟｡螟画峩

### 孱・・繧ｻ繝ｼ繝輔ユ繧｣

- 繝舌ャ繧ｯ繧｢繝・・: `backups/v2.9.11_pre-pricing/` 縺ｫ service.html / style.css 繧剃ｿ晏ｭ・- 譌｢蟄俶ｩ溯・縺ｸ縺ｮ蜑ｯ菴懃畑縺ｪ縺暦ｼ域眠隕上そ繧ｯ繧ｷ繝ｧ繝ｳ繝ｻ譁ｰ隕修SS繧ｯ繝ｩ繧ｹ縺ｮ縺ｿ・・
### 豆 繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡

| 繝ｭ繝ｼ繧ｫ繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| service.html | `/service.html` |
| style.css | `/style.css` |

---

## [v2.9.10] - 2026-04-20 窶・驕髫斐し繝昴・繝医Μ繝ｳ繧ｯ荳諡ｬ菫ｮ豁｣

### 識 蜈ｨ13繝壹・繧ｸ縺ｮ縲碁□髫斐し繝昴・繝医・縺薙■繧峨阪Μ繝ｳ繧ｯ繧定・遉ｾ繝壹・繧ｸ縺ｫ蟾ｮ縺玲崛縺・
蠕捺擂縺ｮ Microsoft Store・・uick Assist・峨∈縺ｮ螟夜Κ繝ｪ繝ｳ繧ｯ繧偵∬・遉ｾ縺ｮ驕髫斐し繝昴・繝域｡亥・繝壹・繧ｸ `remote.html` 縺ｫ螟画峩縲ょｰ守ｷ壹ｒ閾ｪ遉ｾ蜀・↓邨ｱ荳縺励∝茜逕ｨ閠・′霑ｷ繧上★閾ｪ遉ｾ繧ｵ繝昴・繝域ュ蝣ｱ縺ｫ蛻ｰ驕斐〒縺阪ｋ繧医≧縺ｫ縺励◆縲・
### 迫 螟画峩蜀・ｮｹ

| 鬆・岼 | 螟画峩蜑・| 螟画峩蠕・|
|---|---|---|
| URL | `https://apps.microsoft.com/detail/9p7bp5vnwkx5?hl=ja-JP&gl=JP` | `https://sakuranet-co.jp/remote.html` |
| 繝ｪ繝ｳ繧ｯ譁・ｨ | 驕髫斐し繝昴・繝医・縺薙■繧・| ・亥､画峩縺ｪ縺暦ｼ・|
| `target="_blank"` | 邯ｭ謖・| 邯ｭ謖・|

### 唐 螟画峩繝輔ぃ繧､繝ｫ・・3繝壹・繧ｸ繝ｻ蜷・邂・園・・
| 繝輔ぃ繧､繝ｫ | 螟画峩邂・園 |
|---|---|
| index.html / company.html / concept.html / service.html / access.html / contact.html / recruit.html / news.html / privacy.html / custom.html / custom1.html / pay.html / status.html | 繝倥ャ繝繝ｼ莨夂､ｾ諠・ｱ繝懊ャ繧ｯ繧ｹ蜀・・縲碁□髫斐し繝昴・繝医・縺薙■繧峨阪Μ繝ｳ繧ｯ href 縺ｮ縺ｿ |

### 笨・讀懆ｨｼ

- 譌ｧURL蜈ｨ莉ｶ蜑企勁遒ｺ隱・ `apps.microsoft.com/detail/9p7bp5vnwkx5` 竊・0莉ｶ
- 譁ｰURL蜈ｨ莉ｶ蜿肴丐遒ｺ隱・ `sakuranet-co.jp/remote.html` 竊・13莉ｶ・亥・繝壹・繧ｸ蜷・莉ｶ・・- `remote.html` 繝輔ぃ繧､繝ｫ蟄伜惠遒ｺ隱・ 笨・- 譌｢蟄倥Ξ繧､繧｢繧ｦ繝医・繝・じ繧､繝ｳ縺ｫ蠖ｱ髻ｿ縺ｪ縺暦ｼ・ref螻樊ｧ縺ｮ縺ｿ鄂ｮ謠幢ｼ・
### 孱・・繧ｻ繝ｼ繝輔ユ繧｣

- 繝舌ャ繧ｯ繧｢繝・・: `backups/v2.9.10_link-fix/` 縺ｫ蜈ｨ13 HTML繧剃ｿ晏ｭ・- 螟画峩遽・峇: `href` 螻樊ｧ縺ｮ縺ｿ繝ｻ莉悶・螻樊ｧ繝ｻ繝・く繧ｹ繝医・讒矩縺ｯ荳蛻・､画峩縺ｪ縺・
### 豆 繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡

| 繝ｭ繝ｼ繧ｫ繝ｫ繝輔ぃ繧､繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| index.html / company.html / concept.html / service.html / access.html / contact.html / recruit.html / news.html / privacy.html / custom.html / custom1.html / pay.html / status.html | `/`・医し繧､繝医Ν繝ｼ繝茨ｼ・|

---

## [v2.9.9] - 2026-04-20 窶・Phase 3-#5 邨ｱ荳CTA繝悶Ο繝・け霑ｽ蜉

### 識 蜈ｨ繝壹・繧ｸ縺ｫCTA・・all To Action・峨ヶ繝ｭ繝・け繧定ｿｽ蜉

蝠上＞蜷医ｏ縺佞V縺ｮ蠅怜刈繧堤岼逧・↓縲∽ｸｻ隕・繝壹・繧ｸ縺ｮ繝輔ャ繧ｿ繝ｼ逶ｴ蜑阪↓邨ｱ荳繝・じ繧､繝ｳ縺ｮCTA繝悶Ο繝・け繧定ｿｽ蜉縲る崕隧ｱ縺ｨ縺雁撫縺・粋繧上○繝輔か繝ｼ繝縺ｸ縺ｮ蟆守ｷ壹ｒ譏守｢ｺ蛹悶・
### ｧｩ 霑ｽ蜉莉墓ｧ・
| 鬆・岼 | 蜀・ｮｹ |
|---|---|
| 驟咲ｽｮ | 蜈ｨ9繝壹・繧ｸ 繝輔ャ繧ｿ繝ｼ逶ｴ蜑搾ｼ・olophon縺ｮ逶ｴ蜑搾ｼ・|
| 讒区・ | 2繝懊ち繝ｳ: 到 髮ｻ隧ｱ `06-7777-2720` / 笨・縺雁撫縺・粋繧上○繝輔か繝ｼ繝 |
| 繝・じ繧､繝ｳ | 繝斐Φ繧ｯ繝悶Λ繝ｳ繝臥ｶｭ謖・ｼ・#c82054` 荳ｻ繝懊ち繝ｳ / `#fdf2f6竊・fff4f7` 閭梧勹繧ｰ繝ｩ繝・ｼ・|
| 繝ｬ繧ｹ繝昴Φ繧ｷ繝・| SP (竕､640px) 縺ｯ邵ｦ荳ｦ縺ｳ繝ｻ繧ｿ繝・・鬆伜沺 48px莉･荳・|
| 繧｢繧ｯ繧ｻ繧ｷ繝薙Μ繝・ぅ | `aria-labelledby` / `aria-label` / 繧ｻ繝槭Φ繝・ぅ繝・け `<section>` |
| 繝帙ヰ繝ｼ蜉ｹ譫・| `translateY(-2px)` + shadow蠑ｷ蛹厄ｼ・.2s transition・・|

### 唐 螟画峩繝輔ぃ繧､繝ｫ

| 繝輔ぃ繧､繝ｫ | 螟画峩 |
|---|---|
| style.css | `.unified-cta` 邉ｻ 110陦・霑ｽ險假ｼ域里蟄倥せ繧ｿ繧､繝ｫ螟画峩縺ｪ縺暦ｼ・|
| index.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| company.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| service.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| access.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| contact.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| recruit.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| news.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| concept.html | CTA繝悶Ο繝・け謖ｿ蜈･ |
| privacy.html | CTA繝悶Ο繝・け謖ｿ蜈･ |

### 笨・讀懆ｨｼ

- 繝ｭ繝ｼ繧ｫ繝ｫ PC・・280ﾃ・00・・ index/contact 蟠ｩ繧後↑縺・笨・- 繝ｭ繝ｼ繧ｫ繝ｫ SP・・90ﾃ・44・・ 邵ｦ荳ｦ縺ｳ繝ｻ繧ｿ繝・・鬆伜沺OK 笨・- 繝斐Φ繧ｯ繝悶Λ繝ｳ繝臥ｶｭ謖√・譌｢蟄篭I髱樒ｴ螢・笨・
### 孱・・繧ｻ繝ｼ繝輔ユ繧｣

- 繝舌ャ繧ｯ繧｢繝・・: `backups/v2.9.9_pre-cta/`・・HTML + style.css・・- 菴懈･ｭ繧ｹ繧ｯ繝ｪ繝励ヨ: `trash/phase3-5_2026-04-20/` 縺ｸ騾驕ｿ

### 豆 繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡

| 繝ｭ繝ｼ繧ｫ繝ｫ | 繧ｵ繝ｼ繝舌・ |
|---|---|
| style.css | `/style.css` |
| index.html | `/index.html` |
| company.html | `/company.html` |
| service.html | `/service.html` |
| access.html | `/access.html` |
| contact.html | `/contact.html` |
| recruit.html | `/recruit.html` |
| news.html | `/news.html` |
| concept.html | `/concept.html` |
| privacy.html | `/privacy.html` |

---

## [v2.9.8] - 2026-04-20 窶・Phase 2E-B 譛ｬ譁・ユ繧ｭ繧ｹ繝医・AA螳悟・蛹・
### 笙ｿ `#e03164` 譛ｬ譁・牡繧但A蝓ｺ貅・(4.5:1) 驕疲・濶ｲ縺ｸ

v2.9.6 縺ｧ謐ｮ縺育ｽｮ縺九ｌ縺ｦ縺・◆ `#e03164`・育區閭梧勹縺ｧ 4.39:1 竕・AA 4.5 譛ｪ驕費ｼ峨・縺・■縲・*譛ｬ譁・・谿ｵ關ｽ繝ｻ繝ｪ繝ｳ繧ｯ縺ｪ縺ｩ蟆上ユ繧ｭ繧ｹ繝育畑騾斐・縺ｿ** 繧・`#c82054` (5.47:1 笨・ 縺ｫ鄂ｮ謠帙りｦ句・縺励・繧｢繧､繧ｳ繝ｳ繝ｻ繧ｰ繝ｩ繝・・繧ｷ繝ｧ繝ｳ繝ｻ閭梧勹縺ｯ **諢丞峙逧・↓謐ｮ縺育ｽｮ縺・*縲・
| 謖・ｨ・| v2.9.7 | v2.9.8 |
|---|---|---|
| 譛ｬ譁・`color: #e03164` | 豺ｷ蝨ｨ | **0莉ｶ** |
| `#e03164` 谿句ｭ假ｼ磯撼繝・く繧ｹ繝茨ｼ・| 178 | 109・・con/bg/gradient/heading・・|
| `#e03164` 縺ｮAA蟇ｾ蠢懊ユ繧ｭ繧ｹ繝・| 譛ｪ驕・| **蜈ｨ驕疲・** |

### 識 鄂ｮ謠帙Ο繧ｸ繝・け・郁・蜍募愛螳・+ 莠ｺ謇九Ξ繝薙Η繝ｼ・・
| 蛻､螳・| 莉ｶ謨ｰ | 蟇ｾ雎｡ |
|---|---|---|
| **CHANGE** (#e03164 竊・#c82054) | **69** | `<p>` / `<a>` / 谿ｵ關ｽ蜀・`<span>` / CSS繝ｫ繝ｼ繝ｫ蜀・・蟆上ユ繧ｭ繧ｹ繝・|
| KEEP・医い繧､繧ｳ繝ｳ・・| ~52 | `<span style="color:#e03164; margin-right:5px">` 邨ｵ譁・ｭ苓｣・｣ｾ |
| KEEP・郁レ譎ｯ繝ｻ譫繝ｻ蠖ｱ・・| ~40 | `background` / `border` / `linear-gradient` / `box-shadow` |
| KEEP・郁ｦ句・縺暦ｼ・| ~17 | `<h1>`縲彖<h6>` 縺ｯ Large Text 3:1 蝓ｺ貅悶〒譌｢縺ｫ驕疲・ |

### 唐 螟画峩繝輔ぃ繧､繝ｫ・・3繝輔ぃ繧､繝ｫ繝ｻ69鄂ｮ謠幢ｼ・
| 繝輔ぃ繧､繝ｫ | 鄂ｮ謠帶焚 |
|---|---|
| index.html | 6 |
| service.html | 9 |
| concept.html | 2 |
| pay.html | 3 |
| privacy.html | 15 |
| news.html | 7 |
| recruit.html | 4 |
| contact.html | 5 |
| access.html | 4 |
| company.html | 5 |
| status.html | 3 |
| custom.html | 3 |
| custom1.html | 3 |

### 孱・・螳牙・遲・- 繝励Ο繝代ユ繧｣蜷阪〒繝輔ぅ繝ｫ繧ｿ・・background*` / `border*` / `gradient` 縺ｯ蟇ｾ雎｡螟厄ｼ・- 隕ｪ繧ｿ繧ｰ霑ｽ霍｡縺ｧ繝槭Ν繝√Λ繧､繝ｳ `<h*>` 髢句ｧ九ち繧ｰ繧呈､懷・縺励せ繧ｭ繝・・
- 繧｢繧､繧ｳ繝ｳ繝代ち繝ｼ繝ｳ `margin-right: 5px` 縺ｮ邨ｵ譁・ｭ耀pan縺ｯ繧ｹ繧ｭ繝・・
- 繝ｭ繝ｼ繧ｫ繝ｫ縺ｧ PC/SP + privacy.html 繧堤岼隕也｢ｺ隱搾ｼ亥ｴｩ繧後↑縺暦ｼ・
### 逃 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.8_pre-contrast/` 縺ｫ 13HTML繝輔ぃ繧､繝ｫ繧帝驕ｿ

### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡・・3繝輔ぃ繧､繝ｫ・・| 繝ｭ繝ｼ繧ｫ繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| index.html | `/index.html` |
| service.html | `/service.html` |
| concept.html | `/concept.html` |
| pay.html | `/pay.html` |
| privacy.html | `/privacy.html` |
| news.html | `/news.html` |
| recruit.html | `/recruit.html` |
| contact.html | `/contact.html` |
| access.html | `/access.html` |
| company.html | `/company.html` |
| status.html | `/status.html` |
| custom.html | `/custom.html` |
| custom1.html | `/custom1.html` |

---

## [v2.9.7] - 2026-04-20 窶・Phase 2D CSS譛ｪ菴ｿ逕ｨ繧ｻ繝ｬ繧ｯ繧ｿ蜑頑ｸ・
### ｧｹ style.css 縺ｮ荳崎ｦ√そ繝ｬ繧ｯ繧ｿ荳諡ｬ蜑企勁

HPB・・BM繝帙・繝繝壹・繧ｸ繝薙Ν繝繝ｼ・峨♀繧医・ WordPress 繝・Φ繝励Ξ繝ｼ繝育罰譚･縺ｮ譛ｪ菴ｿ逕ｨ繧ｻ繝ｬ繧ｯ繧ｿ繧呈彫蜴ｻ縺励√せ繧ｿ繧､繝ｫ繧ｷ繝ｼ繝医ｒ繧ｹ繝ｪ繝蛹悶・
| 謖・ｨ・| v2.9.6 | v2.9.7 | 蜑頑ｸ・|
|---|---|---|---|
| 陦梧焚 | 4,695 | **3,467** | **-1,228陦鯉ｼ・26.2%・・* |
| 繧ｵ繧､繧ｺ | 127KB | **85KB** | **-41.5KB・・32.7%・・* |
| 繝ｫ繝ｼ繝ｫ荳ｭ諡ｬ蠑ｧ | 380 | 277 | -103繝悶Ο繝・け |

### 卵・・蜑企勁縺励◆譛ｪ菴ｿ逕ｨ繧ｯ繝ｩ繧ｹ・・5遞ｮ鬘橸ｼ・- **HPB谿矩ｪｸ**: `.hpb-more-entry` / `.hpb-viewtype-content` / `.hpb-viewtype-simple` / `.hpb-viewtype-thumbnail`
- **WordPress谿矩ｪｸ**: `.by-author` / `.cat-links` / `.comments-link` / `.tags-links` / `.sep` / `.nav-next` / `.nav-previous` / `.navigation-post` / `.current-menu-item` / `.current-menu-ancestor` / `.current_page_ancestor` / `.menu-item-has-children`
- **HPB sp邉ｻ**: `.sp-button` / `.sp-column` / `.sp-form` / `.sp-list` / `.sp-table` / `.sp-google-map` / `.sp-yahoo-map` / `.sp-item-gallery`
- **縺昴・莉・*: `.accordion` / `.masonry` / `.grid` / `.searchform` / `.col-title` / `.column-body` / `.column-label` / `.row-title` / `.toggled-on` / `.vertical` / `.item-gallery-thumbnail`

### 卵・・蜑企勁縺励◆譛ｪ菴ｿ逕ｨID・・遞ｮ鬘橸ｼ・`#sp-image-1/3/4`, `#sp-list-1/2/3/4`

### 孱・・螳牙・遲・- 繧ｫ繝ｳ繝樣｣邨舌そ繝ｬ繧ｯ繧ｿ縺ｯ **菴ｿ逕ｨ荳ｭ縺ｮ繧ｻ繝ｬ繧ｯ繧ｿ繧呈ｮ九＠縲∵悴菴ｿ逕ｨ縺ｮ縺ｿ繧貞炎髯､**
- @media 繝悶Ο繝・け蜀・Κ繧ょ・蟶ｰ逧・↓蜃ｦ逅・- 邱ｨ髮・燕蠕後〒 brace balance 讀懆ｨｼ・・77/277 荳閾ｴ・・- 繝ｭ繝ｼ繧ｫ繝ｫ (PC/SP) 縺ｧ陦ｨ遉ｺ遒ｺ隱肴ｸ医∩

### 逃 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.7_pre-css-cleanup/style.css`

### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡・・繝輔ぃ繧､繝ｫ・・| 繝輔ぃ繧､繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| style.css | `https://sakuranet-co.jp/style.css` |

---

## [v2.9.6] - 2026-04-20 窶・Phase 2E-B 繧｢繧ｯ繧ｻ繧ｷ繝薙Μ繝・ぅ・郁牡繧ｳ繝ｳ繝医Λ繧ｹ繝域隼蝟・ｼ・
### 笙ｿ WCAG AA 貅匁侠 窶・style.css 濶ｲ繧ｳ繝ｳ繝医Λ繧ｹ繝井ｿｮ豁｣・・9邂・園・・
| # | 譌ｧ濶ｲ | 譁ｰ濶ｲ | 豈費ｼ育區閭梧勹/隧ｲ蠖楢レ譎ｯ・・| 鄂ｮ謠帶焚 |
|---|---|---|---|---|
| 竭 | `#f0a7a7` | `#c82054` | 1.95:1 竊・**5.51:1** 笨・| 4 |
| 竭｡ | `#c76b6b` | `#8c2b47` | 2.78:1 (on #ffd9d9) 竊・**邏・.5:1** 笨・| 17 |
| 竭｢ | `#929292` | `#767676` | 3.12:1 竊・**4.54:1** 笨・| 3 |
| 竭｣ | `#eb6a6a` | `#c82054` | 3.07:1 竊・**5.51:1** 笨・| 5 |

### 耳 隕冶ｦ夂噪蜉ｹ譫・- 豺｡縺・し繝ｼ繝｢繝ｳ/繧ｳ繝ｼ繝ｩ繝ｫ邉ｻ繧貞ｻ・ｭ｢縺励√Γ繧､繝ｳ繝斐Φ繧ｯ `#c82054` 縺ｫ邨ｱ蜷・竊・繝悶Λ繝ｳ繝臥ｵｱ荳諢溘′蜷台ｸ・- SP繝翫ン繝ｻ繧ｵ繝悶Γ繝九Η繝ｼ縺ｮ蟆剰ｱ・牡 `#c76b6b` 繧偵ｄ繧・ｿ・＞ `#8c2b47` 縺ｫ螟画峩・郁ｪｭ縺ｿ繧・☆縺募髄荳奇ｼ・- 繧ｵ繝匁枚蟄励げ繝ｬ繝ｼ繧偵ｏ縺壹°縺ｫ豼・￥・・#929292` 竊・`#767676`・・
### 搭 譁ｹ驥・- **繝悶Λ繝ｳ繝芽牡 `#c82054` / `#e03164` 縺ｯ邯ｭ謖・*
- `#e03164` on #ffffff・・.38:1・峨・螟ｧ繝・く繧ｹ繝亥渕貅厄ｼ・:1・牙粋譬ｼ縺ｮ縺溘ａ**謐ｮ縺育ｽｮ縺・*

### 逃 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.6_pre-a11y-color/style.css`

### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡・・繝輔ぃ繧､繝ｫ・・| 繝輔ぃ繧､繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| style.css | `https://sakuranet-co.jp/style.css` |

### 剥 讀懆ｨｼ譁ｹ豕・- Web繝ｻSP荳｡譁ｹ縺ｧ陦ｨ遉ｺ遒ｺ隱搾ｼ医ョ繧ｶ繧､繝ｳ蟠ｩ繧後↑縺励・譁・ｭ苓ｦ冶ｪ肴ｧ蜷台ｸ奇ｼ・- PageSpeed Insights / axe DevTools 縺ｧ繧ｳ繝ｳ繝医Λ繧ｹ繝磯＆蜿阪・貂帛ｰ代ｒ遒ｺ隱・
---

## [v2.9.5.1] - 2026-04-20 窶・hotfix・・focus-visible 繧ｹ繧ｳ繝ｼ繝嶺ｿｮ豁｣・・
### 菅 菫ｮ豁｣
- `style.css`: v2.9.5 縺ｧ霑ｽ蜉縺励◆ `:focus-visible` 繝悶Ο繝・け縺・`@media screen and (max-width: 568px) { ... }` 蜀・↓隱､縺｣縺ｦ驟咲ｽｮ縺輔ｌ縺ｦ縺翫ｊ縲√ョ繧ｹ繧ｯ繝医ャ繝怜ｹ・ｼ・568px・峨〒繧ｭ繝ｼ繝懊・繝峨ヵ繧ｩ繝ｼ繧ｫ繧ｹ繝ｪ繝ｳ繧ｰ縺瑚｡ｨ遉ｺ縺輔ｌ縺ｪ縺・ｸ榊・蜷医ｒ菫ｮ豁｣
- @media 縺ｮ髢峨§ `}` 繧定ｿｽ蜉縺励～:focus-visible` 繝ｫ繝ｼ繝ｫ鄒､繧・@media 螟悶∈遘ｻ蜍包ｼ・SS繧ｰ繝ｭ繝ｼ繝舌Ν繧ｹ繧ｳ繝ｼ繝励〒驕ｩ逕ｨ・・
### 逃 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.5.1_pre-hotfix/style.css`

### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡・・繝輔ぃ繧､繝ｫ・・| 繝輔ぃ繧､繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| style.css | `https://sakuranet-co.jp/style.css` |

### 剥 讀懆ｨｼ譁ｹ豕・- Chrome DevTools 縺ｧ `?` 繝懊ち繝ｳ Tab 謚ｼ荳・竊・繧ｵ繧ｯ繝ｩ繝斐Φ繧ｯ霈ｪ驛ｭ・・px solid #c82054・峨′蜿ｯ隕門喧縺輔ｌ繧九％縺ｨ繧堤｢ｺ隱・
---

## [v2.9.5] - 2026-04-20 窶・Phase 2E-A 繧｢繧ｯ繧ｻ繧ｷ繝薙Μ繝・ぅ謾ｹ蝟・ｼ医そ繝槭Φ繝・ぅ繝・け蛹悶・alt謾ｹ蝟・・:focus-visible・・
### 笙ｿ a11y 謾ｹ蝟・窶・3鬆・岼・郁ｦ九◆逶ｮ螟牙喧縺ｪ縺暦ｼ・
#### 竭 繧ｻ繝槭Φ繝・ぅ繝・け main 繧ｿ繧ｰ蛹厄ｼ亥・10HTML・・- `<div id="content" role="main">` 竊・**`<main id="content">`** 縺ｸ鄂ｮ謠・  - 蟇ｾ雎｡: `index / company / service / access / contact / recruit / news / concept / privacy / pay`
  - 繧ｯ繝ｩ繧ｹ縺ｯ蜈ｨ縺ｦ菫晄戟・・.site-content .sp-content .page-xxx`・峨・CSS縺ｸ縺ｮ蠖ｱ髻ｿ縺ｪ縺・  - 蜀鈴聞縺ｪ `role="main"` 螻樊ｧ繧貞炎髯､・・<main>` 繧ｿ繧ｰ縺ｧ證鈴ｻ咏噪縺ｫ莉倅ｸ弱＆繧後ｋ・・
#### 竭｡ alt螻樊ｧ縺ｮ險倩ｿｰ逧・隼蝟・ｼ・HTML / 7邂・園・・- **concept.html**: `alt="CEO"` 竊・`alt="譬ｪ蠑丈ｼ夂､ｾ縺輔￥繧峨・縺｣縺ｨ 莉｣陦ｨ蜿也ｷ蠖ｹ"`
- **service.html**: 6邂・園縺ｮ髱櫁ｨ倩ｿｰ逧・alt 繧貞・菴灘喧
  - `images` 竊・`PC繧ｵ繝昴・繝医・繝医Λ繝悶Ν隗｣豎ｺ`
  - `service-s01` 竊・`SAKURA-NET Total Security 繧ｯ繝ｩ繧ｦ繝牙梛繝阪ャ繝医Ρ繝ｼ繧ｯ繧ｻ繧ｭ繝･繝ｪ繝・ぅ`
  - `service-s02` 竊・`蟷ｴ髢薙・譛磯俣PC菫晏ｮ亥･醍ｴ・し繝ｼ繝薙せ`
  - `images` 竊・`豕穂ｺｺ蜷代￠繧ｻ繧ｭ繝･繝ｪ繝・ぅSIM`
  - `download` 竊・`WatchGuard UTM繧ｻ繧ｭ繝･繝ｪ繝・ぅ`
  - `images1` 竊・`UniFi Security Gateway`

#### 竭｢ `:focus-visible` 繧ｭ繝ｼ繝懊・繝峨ヵ繧ｩ繝ｼ繧ｫ繧ｹ繧ｹ繧ｿ繧､繝ｫ霑ｽ蜉・・tyle.css・・- 繝槭え繧ｹ謫堺ｽ懈凾縺ｯ髱櫁｡ｨ遉ｺ繝ｻ繧ｭ繝ｼ繝懊・繝・Tab 謫堺ｽ懈凾縺ｮ縺ｿ繧ｵ繧ｯ繝ｩ繝斐Φ繧ｯ・・#c82054`・芽ｼｪ驛ｭ陦ｨ遉ｺ
- 蟇ｾ雎｡隕∫ｴ: `a / button / input / select / textarea / [tabindex]`
- 譌｢蟄・:focus 繝ｫ繝ｼ繝ｫ縺ｨ遶ｶ蜷医＠縺ｪ縺・ｨｭ險茨ｼ・:focus:not(:focus-visible)` 縺ｧ outline:none・・
### 笨・螳牙・諤ｧ讀懆ｨｼ
- 蜈ｨ10HTML 縺ｮ繧ｿ繧ｰ謨ｴ蜷域ｧ・・iv/main/header/footer 蜷・open=close・・*螳悟・荳閾ｴ**
- 譌ｧ繝代ち繝ｼ繝ｳ `<div id="content" role="main">` 谿句ｭ・**0莉ｶ**
- CSS 縺ｧ `div#content` / `div.site-content` 繧ｻ繝ｬ繧ｯ繧ｿ菴ｿ逕ｨ **0莉ｶ**・亥ｽｱ髻ｿ縺ｪ縺暦ｼ・- 隕冶ｦ夂噪螟牙喧: **縺ｪ縺・*・域э蜻ｳ繧ｿ繧ｰ縺ｮ鄂ｮ謠帙→alt譁・ｨ菫ｮ豁｣繝ｻ繝輔か繝ｼ繧ｫ繧ｹ譎ゅ・縺ｿ蜿ｯ隕厄ｼ・
### 逃 繝舌ャ繧ｯ繧｢繝・・
- `backups/v2.9.5_pre-a11y/`・・0HTML + style.css・・
### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡・・0繝輔ぃ繧､繝ｫ・・| 繝輔ぃ繧､繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
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

### 笞・陬懆ｶｳ
- Phase 2E-A 縺ｯ縲瑚ｦ九◆逶ｮ螟牙喧縺ｪ縺励阪せ繧ｳ繝ｼ繝励・縺ｿ螳滓命
- 竭｣ 濶ｲ繧ｳ繝ｳ繝医Λ繧ｹ繝域､懆ｨｼ・句ｾｮ隱ｿ謨ｴ縺ｯ譛ｪ螳滓命・郁ｦゞI謇ｿ隱阪・Phase 2E-B 蛟呵｣懶ｼ・- `custom.html / custom1.html / status.html / remote.html` 縺ｯ繧ｹ繧ｳ繝ｼ繝怜､厄ｼ井ｻ雁屓譛ｪ謾ｹ菫ｮ・・- 菴懈･ｭ逕ｨ Python 繧ｹ繧ｯ繝ｪ繝励ヨ `_a11y_semantic.py` 縺ｯ `trash/phase2e_2026-04-20/` 縺ｸ騾驕ｿ

---

## [v2.9.4] - 2026-04-19 窶・Phase 2C `/data/` 逶｣譟ｻ謨ｴ逅・ｼ域立HTML繝ｻ譌ｧCSS繝ｻ譛ｪ蜿ら・逕ｻ蜒上・譌ｧ繧ｹ繧ｯ繝ｪ繝励ヨ・・
### 卵 trash 遘ｻ蜍包ｼ亥粋險・19繝輔ぃ繧､繝ｫ / 邏・1.13MB 蜑頑ｸ幢ｼ・- **譌ｧHTML 12譛ｬ**: `access/company/concept/contact/custom/custom1/index/news/pay/privacy/recruit/service.html`
  - 繝ｫ繝ｼ繝育峩荳九↓迴ｾ蠖ｹ迚医≠繧翫・`/data/` 迚医・譛ｪ蜿ら・縺ｮ譌ｧ繝舌・繧ｸ繝ｧ繝ｳ
- **繧ｹ繝翫ャ繝励す繝ｧ繝・ヨ**: `index_snapshot_20260223.html`・・譛ｬ・・- **譌ｧCSS**: `style.css`・医Ν繝ｼ繝育峩荳・`style.css` 縺ｮ譌ｧ繧ｳ繝斐・ / 131KB・・- **譛ｪ蜿ら・逕ｻ蜒・*: `IMG_6578.PNG`・・73KB・・ `img_brand.png`・・6KB・・ `udmoromax.png`・・6KB・・  - `pay.html` 縺悟盾辣ｧ縺吶ｋ `img_brand.png` 縺ｯ `/img/` 蛛ｴ繝ｻ`/data/` 縺ｮ蜷悟錐縺ｯ蛻･迚ｩ
- **譌ｧ繧ｹ繧ｯ繝ｪ繝励ヨ**: `apply_responsive.ps1` / `apply_sakura.py`

### 笨・谿狗ｽｮ蠢・茨ｼ・譛ｬ・・- **`/data/status_data.json`** 窶・`status_api.php` 縺御ｽｿ逕ｨ荳ｭ・育ｨｼ蜒阪ョ繝ｼ繧ｿ・・
### 笨・螳牙・諤ｧ讀懆ｨｼ
- 蜈ｨHTML/CSS/JS/PHP 縺ｧ `data/` 蜿ら・繧・grep 竊・**谿九ｋ蜿ら・縺ｯ `status_data.json` 縺ｮ縺ｿ**
- 譛ｬ逡ｪ繝輔ぃ繧､繝ｫ縺ｮ螢翫ｌ縺溷盾辣ｧ **0莉ｶ**
- trash 遘ｻ蜍募・: `trash/phase2c_2026-04-19/`・域怙邨ょ炎髯､縺ｯ繝ｦ繝ｼ繧ｶ繝ｼ縺・`rm -rf trash/` 縺ｧ螳滓命・・
### 投 `/data/` 謨ｴ逅・ｾ・- 謨ｴ逅・燕: 20繝輔ぃ繧､繝ｫ / 邏・1.2MB
- 謨ｴ逅・ｾ・ 1繝輔ぃ繧､繝ｫ / 3繝舌う繝茨ｼ・status_data.json` 縺ｮ縺ｿ・・
### 笞・陬懆ｶｳ
- 菴懈･ｭ逕ｨ Python 繧ｹ繧ｯ繝ｪ繝励ヨ縺ｯ trash 縺ｸ蜷梧｢ｱ騾驕ｿ
- 繧ｵ繝ｼ繝舌・蛛ｴ縺ｸ縺ｮ螟画峩縺ｪ縺暦ｼ井ｻ雁屓縺ｯ**繝ｭ繝ｼ繧ｫ繝ｫ縺ｮ縺ｿ**縺ｮ謨ｴ逅・ｼ・- 谺｡繧ｿ繧ｹ繧ｯ: Phase 2B-WebP・域ｮ狗判蜒乗怙驕ｩ蛹悶・蜉ｹ譫懷ｰ上〒隕矩√ｊ蛟呵｣懶ｼ・or Phase 2D/2E・・I謇ｿ隱榊ｿ・ｦ・ｼ・
### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡
**縺ｪ縺・*・医Ο繝ｼ繧ｫ繝ｫ謨ｴ逅・・縺ｿ繝ｻ繧ｵ繝ｼ繝舌・縺ｯ迴ｾ迥ｶ邯ｭ謖・ｼ・
---

## [v2.9.3] - 2026-04-19 窶・Phase 2B img/ 螟ｧ謗・勁・域悴蜿ら・逕ｻ蜒上・PDF繝ｻZIP繝ｻDOCX謨ｴ逅・ｼ・
### 卵 trash 遘ｻ蜍包ｼ亥粋險・322繝輔ぃ繧､繝ｫ / 邏・136.6MB 蜑頑ｸ幢ｼ・- **驥崎､・・繝・ム繝ｼ逕ｻ蜒・*: `header-main2.png` 縲・`header-main55.png`・・4譛ｬ / 33.3MB・・  - 蜿ら・荳ｭ縺ｮ `header-main.png` 縺ｯ谿狗ｽｮ・・tyle.css縺ｧ菴ｿ逕ｨ・・- **譛ｪ蜿ら・ file*.pdf**: `file3`, `file5`, `file7`, `file8`, `file11`, `file13`縲彖file20`, `file23`縲彖file200`.pdf・・80譛ｬ / 91.9MB・・  - posts/ 縺悟盾辣ｧ縺吶ｋ `file1, 2, 4, 6, 9, 10, 12, 21, 22`.pdf 縺ｯ谿狗ｽｮ
- **逕ｻ蜒上ヵ繧ｩ繝ｫ繝蜀・・ZIP**: `img/*.zip`・・6譛ｬ / 10.4MB・・- **譛ｪ蜿ら・DOCX**: `file2.docx` 縲・`file23.docx`・・2譛ｬ / 0.95MB・・  - posts/ 縺悟盾辣ｧ縺吶ｋ `file1.docx` 縺ｯ谿狗ｽｮ

### 笨・螳牙・諤ｧ讀懆ｨｼ
- 蜈ｨHTML/CSS/JS 縺ｧ逕ｻ蜒丞盾辣ｧ繧・grep 竊・**譛ｬ逡ｪ繝輔ぃ繧､繝ｫ縺ｮ螢翫ｌ縺溷盾辣ｧ 0莉ｶ**
- 蠢・医ヵ繧｡繧､繝ｫ蟄伜惠遒ｺ隱・ `header-main.png` / `logo.png` / `ogp.jpg` / `favicon.ico` / file1-22.pdf / file1.docx 縺吶∋縺ｦ OK
- trash 遘ｻ蜍募・: `trash/phase2b_2026-04-19/`・域怙邨ょ炎髯､縺ｯ繝ｦ繝ｼ繧ｶ繝ｼ縺・`rm -rf trash/` 縺ｧ螳滓命・・
### 投 img/ 謨ｴ逅・ｾ・- 謨ｴ逅・燕: 1,803繝輔ぃ繧､繝ｫ / 163.5MB
- 謨ｴ逅・ｾ・ 1,481繝輔ぃ繧､繝ｫ / 邏・7MB
- **蜑頑ｸ・ 邏・136.6MB・・3%貂幢ｼ・*

### 笞・陬懆ｶｳ
- 隱､蜑企勁髦ｲ豁｢縺ｮ縺溘ａ Tier 1/2 縺ｮ縺ｿ螳滓命・域・繧峨°縺ｪ驥崎､・・譛ｪ蜿ら・縺ｮ縺ｿ・・- Tier 3・医◎縺ｮ莉悶・譛ｪ蜿ら・PNG/JPG ~50MB・峨・蟆・擂縺ｮ蛟句挨讀懆ｨｼ譎ゅ↓蛻､譁ｭ
- 繧ｵ繝ｼ繝舌・蛛ｴ縺ｸ縺ｮ螟画峩縺ｪ縺暦ｼ井ｻ雁屓縺ｯ**繝ｭ繝ｼ繧ｫ繝ｫ縺ｮ縺ｿ**縺ｮ謨ｴ逅・ｼ・- 谺｡繧ｿ繧ｹ繧ｯ: Phase 2C・・/data/` 逶｣譟ｻ・・
### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡
**縺ｪ縺・*・医Ο繝ｼ繧ｫ繝ｫ謨ｴ逅・・縺ｿ繝ｻ繧ｵ繝ｼ繝舌・縺ｯ迴ｾ迥ｶ邯ｭ謖・ｼ・
---

## [v2.9.2] - 2026-04-19 窶・Phase 2A 荳崎ｦ√ヵ繧｡繧､繝ｫ謨ｴ逅・ｼ医ざ繝溽ｮｱ繝代ち繝ｼ繝ｳ・・
### 卵 trash 遘ｻ蜍包ｼ亥粋險・67繝輔ぃ繧､繝ｫ / 邏・88.9MB 蜑頑ｸ幢ｼ・- **蜿､縺・navigation 繧ｹ繧ｯ繝ｪ繝励ヨ**: `navigation1.js` 縲・`navigation52.js`・・2譛ｬ・・  - 迴ｾ蠖ｹ `navigation.js` / `navigation53.js` 縺ｯ谿狗ｽｮ・・ndex.html 縺ｧ菴ｿ逕ｨ荳ｭ・・- **譌ｧSP迚・IP**: `sp21_20200612.zip` / `sp21_20210616.zip` / `sp21_20220706.zip` / `sp21_20250220.zip`・郁ｨ・邏・89MB・・- **譌ｧ繧ｹ繧ｯ繝ｪ繝励ヨ**: `apply_new_fixes.py` / `apply_new_fixes2.py` / `apply_responsive_all.ps1` / `apply_responsive_all.py` / `apply_safe.ps1` / `apply_safe2.ps1` / `apply_sakura.py` / `run_safe2.ps1` / `update_privacy_sakura.ps1` / `update_privacy_sakura.py`・・0譛ｬ・・- **繧ｹ繝翫ャ繝励す繝ｧ繝・ヨ**: `index_snapshot_20260223.html`・医Ν繝ｼ繝育峩荳九・1譛ｬ・・
### 笨・螳牙・諤ｧ讀懆ｨｼ
- 蜈ｨ9HTML + posts/ + style.css 縺ｧ `navigation[0-9]+\.js` / `sp21_` / `apply_` / `run_safe` / `update_privacy` 繧・grep 竊・**蜿ら・0莉ｶ遒ｺ隱肴ｸ医∩**
- trash 遘ｻ蜍募・: `trash/phase2a_2026-04-19/`・域怙邨ょ炎髯､縺ｯ繝ｦ繝ｼ繧ｶ繝ｼ縺・`rm -rf trash/` 縺ｧ螳滓命・・
### 笞・陬懆ｶｳ
- 繝輔ぃ繧､繝ｫ蜑企勁縺ｧ縺ｯ縺ｪ縺・**繧ｴ繝溽ｮｱ繝代ち繝ｼ繝ｳ**・・mkdir -p trash && mv`・峨〒螳滓命
- 繧ｵ繝ｼ繝舌・蛛ｴ縺ｸ縺ｮ螟画峩縺ｪ縺暦ｼ井ｻ雁屓縺ｯ**繝ｭ繝ｼ繧ｫ繝ｫ縺ｮ縺ｿ**縺ｮ謨ｴ逅・ｼ・- 谺｡繧ｿ繧ｹ繧ｯ: Phase 2B・育判蜒集ebP蛹悶・lazy loading・・
### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡
**縺ｪ縺・*・医Ο繝ｼ繧ｫ繝ｫ謨ｴ逅・・縺ｿ繝ｻ繧ｵ繝ｼ繝舌・縺ｯ迴ｾ迥ｶ邯ｭ謖・ｼ・
---

## [v2.9.1] - 2026-04-19 窶・繝ｭ繧ｴ繝ｻ繝輔ぃ繝薙さ繝ｳ繝ｻ繧｢繝励Μ繧｢繧､繧ｳ繝ｳ驟咲ｽｮ

### 耳 繝悶Λ繝ｳ繝臥判蜒上い繧ｻ繝・ヨ驟咲ｽｮ・・emini菴懈・・・- **`img/logo.png`** 窶・512ﾃ・12px / RGBA / 246KB・・chema.org Organization logo 逕ｨ繝ｻ閭梧勹騾城℃・・- **`img/apple-touch-icon.png`** 窶・180ﾃ・80px / RGB / 18KB・・OS 繝帙・繝霑ｽ蜉繧｢繧､繧ｳ繝ｳ繝ｻ閭梧勹 `#fff0f5`・・- **`favicon.ico`** 窶・16ﾃ・6 + 32ﾃ・2 + 48ﾃ・8 繝槭Ν繝√Ξ繧､繝､繝ｼ / 6.0KB・医Ν繝ｼ繝育峩荳矩・鄂ｮ・・
### 笨・讀懆ｨｼ邨先棡
- 蜈ｨ9HTML繝壹・繧ｸ縺ｮ `<link rel="icon">` / `<link rel="apple-touch-icon">` 繝代せ謨ｴ蜷域ｧOK
- PIL 縺ｫ繧医ｋ ICO 繝ｬ繧､繝､繝ｼ讀懆ｨｼ: `[(16,16),(32,32),(48,48)]` 遒ｺ隱肴ｸ医∩
- JSON-LD Organization 縺ｮ `logo` 繝励Ο繝代ユ繧｣縺悟盾辣ｧ縺吶ｋ `/img/logo.png` 螳滉ｽ馴・鄂ｮ螳御ｺ・  ・・2.9.0譎らせ縺ｧ譛ｪ驟咲ｽｮ縺縺｣縺溽判蜒上い繧ｻ繝・ヨ縺梧純縺・√Μ繝・メ繝ｪ繧ｶ繝ｫ繝医・螳悟・驕ｩ逕ｨ譚｡莉ｶ繧呈ｺ縺溘＠縺滂ｼ・
### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡
| 繝輔ぃ繧､繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| img/logo.png | `https://sakuranet-co.jp/img/logo.png` |
| img/apple-touch-icon.png | `https://sakuranet-co.jp/img/apple-touch-icon.png` |
| favicon.ico | `https://sakuranet-co.jp/favicon.ico` |

### 笞・陬懆ｶｳ
- 蛻晏屓邏榊刀縺輔ｌ縺・`favicon.ico` 縺ｯ 16ﾃ・6 蜊倅ｸ繝ｬ繧､繝､繝ｼ縺縺｣縺溘◆繧・Gemini 縺ｫ蟾ｮ縺玲綾縺怜・菴懈・竊呈ｭ｣蠑上・繝ｫ繝√Ξ繧､繝､繝ｼ縺ｧ蜿鈴・- 繝舌ャ繧ｯ繧｢繝・・: `backups/v2.9.1_logo-assets/`・・繝輔ぃ繧､繝ｫ・・- 萓晞ｼ譖ｸ: `GEMINI_LOGO_REQUEST.md` / `GEMINI_FAVICON_REDO.md`

---

## [v2.9.0] - 2026-04-19 窶・Phase 1 SEO蝓ｺ遉取紛蛯・
### 識 SEO繝ｻ讒矩蛹悶ョ繝ｼ繧ｿ・亥・9繝壹・繧ｸ荳諡ｬ蟇ｾ蠢懶ｼ・- **GA4遘ｻ陦・*: 譌ｧ `UA-123359498-1` 竊・`G-8DZV2NE7C1` 縺ｸ蜈ｨ繝壹・繧ｸ鄂ｮ謠・- **canonical**: 蜈ｨ繝壹・繧ｸ縺ｫ邨ｶ蟇ｾURL縺ｮ豁｣隕丞喧URL繧定ｿｽ蜉
- **OGP蠑ｷ蛹・*: `og:image`・・/img/ogp.jpg` 1200ﾃ・30px繝ｻGemini菴懈・・・ `og:image:width/height` + `og:locale` 繧貞・繝壹・繧ｸ縺ｫ霑ｽ蜉
- **Twitter Card**: `summary_large_image` 蠖｢蠑上・繧ｫ繝ｼ繝画ュ蝣ｱ繧貞・繝壹・繧ｸ縺ｫ霑ｽ蜉
- **robots meta**: `index,follow,max-image-preview:large` 繧貞・繝壹・繧ｸ縺ｫ霑ｽ蜉
- **favicon / apple-touch-icon**: 蜈ｨ繝壹・繧ｸ縺ｫ `<link rel="icon">` / `<link rel="apple-touch-icon">` 繧定ｿｽ蜉・遺ｻ逕ｻ蜒上ヵ繧｡繧､繝ｫ閾ｪ菴薙・蠕梧律驟咲ｽｮ莠亥ｮ夲ｼ・- **title / meta description 繝ｪ繝ｩ繧､繝・*: 蜈ｨ9繝壹・繧ｸ縺ｧSEO諢丞峙縺ｫ豐ｿ縺｣縺溷ｽ｢縺ｫ譖ｸ縺肴鋤縺茨ｼ・0蟄・120蟄励ぎ繧､繝峨Λ繧､繝ｳ貅匁侠・・
### 召 JSON-LD 讒矩蛹悶ョ繝ｼ繧ｿ螳溯｣・- **index.html**: `@graph` 蠖｢蠑上〒 Organization + LocalBusiness + WebSite 繧貞ｮ溯｣・ｼ井ｽ乗園繝ｻTEL繝ｻ蝟ｶ讌ｭ譎る俣繝ｻ蝨ｰ蝓溷ｯｾ蠢懈ュ蝣ｱ・・- **service.html**: Service 繧ｹ繧ｭ繝ｼ繝・遞ｮ・・niFi蟆主・ / SAKURA-NET蜈・/ 髦ｲ迥ｯ繧ｫ繝｡繝ｩ繝ｻ蜈･騾螳､邂｡逅・/ UTM繝ｻIP-PBX繝ｻVPN・峨ｒ螳溯｣・
### 翌 SEO繧､繝ｳ繝輔Λ譁ｰ隕上ヵ繧｡繧､繝ｫ
- `sitemap.xml` 窶・蜈ｨ9URL繧・image-sitemap蜷ｫ繧√※險倩ｼ・- `robots.txt` 窶・`/system/` `/data/` `/backup/` `/backups/` `/trash/` 繧帝勁螟悶・sitemap蜿ら・霑ｽ蜉
- `manifest.json` 窶・PWA蟇ｾ蠢懊・繝九ヵ繧ｧ繧ｹ繝茨ｼ医ヶ繝ｩ繝ｳ繝峨き繝ｩ繝ｼ `#c82054` / `#fff0f5`・・
### 笞・陬懆ｶｳ
- `img/ogp.jpg`・・09KB縲・200ﾃ・30px・烏emini菴懈・繝ｻ繝悶Λ繝ｳ繝峨き繝ｩ繝ｼ/繧ｭ繝｣繝・メ繧ｳ繝斐・貅匁侠
- 繝舌ャ繧ｯ繧｢繝・・: `backups/v2.8.2_pre-seo/`・・0繝輔ぃ繧､繝ｫ・・- **譛ｪ驟咲ｽｮ**: `favicon.ico` / `apple-touch-icon.png` / `img/logo.png`・・emini霑ｽ蜉萓晞ｼ莠亥ｮ夲ｼ・- **谺｡繝輔ぉ繝ｼ繧ｺ**: Phase 2・・ebP蛹悶・lazy loading繝ｻ荳崎ｦ√ヵ繧｡繧､繝ｫ謨ｴ逅・ｼ・
### 豆 繧ｵ繝ｼ繝舌・繧｢繝・・繝ｭ繝ｼ繝牙ｯｾ雎｡
| 繝輔ぃ繧､繝ｫ | 繧ｵ繝ｼ繝舌・繝代せ |
|---|---|
| index.html / company.html / service.html / access.html / contact.html / recruit.html / news.html / concept.html / privacy.html | `https://sakuranet-co.jp/` 蜷・Ν繝ｼ繝・|
| sitemap.xml | `https://sakuranet-co.jp/sitemap.xml` |
| robots.txt | `https://sakuranet-co.jp/robots.txt` |
| manifest.json | `https://sakuranet-co.jp/manifest.json` |
| img/ogp.jpg | `https://sakuranet-co.jp/img/ogp.jpg` |

---

## [v2.8.2] - 2026-04-15
### 笨ｨ 譁ｰ隕剰ｳ・肇蜉蟾･ (Asset Processing)
- **髮ｻ蟄占ｧ貞魂縺ｮ閭梧勹騾城℃蛹・*: `髮ｻ蟄占ｧ貞魂.bmp` 繧貞・縺ｫ縲∬レ譎ｯ縺ｮ逋ｽ譫繧貞ｮ悟・縺ｫ騾城℃蜃ｦ逅・＠縺・`髮ｻ蟄占ｧ貞魂_transparent.png` 繧堤函謌舌・- **驟咲ｽｮ蝣ｴ謇**: `C:\Users\MYPC\Desktop\髮ｻ蟄舌せ繧ｿ繝ｳ繝妖eta\髮ｻ蟄舌せ繧ｿ繝ｳ繝妖eta\`
- **繝舌ャ繧ｯ繧｢繝・・**: 蜈・ヵ繧｡繧､繝ｫ縺ｯ `髮ｻ蟄占ｧ貞魂_orig.bmp` 縺ｨ縺励※蜷後ョ繧｣繝ｬ繧ｯ繝医Μ縺ｫ菫晏ｭ倥・

## [v2.8.1] - 2026-04-01
### 屏 菫ｮ豁｣蜀・ｮｹ (Critical Bug Fix)
- **FAQ險倅ｺ九ョ繧ｶ繧､繝ｳ蜷梧悄縺ｮ荳榊ｙ隗｣豸・*: `faq1`, `faq2`, `faq3` 縺・018蟷ｴ蠖捺凾縺ｮ蜿､縺・ョ繧ｶ繧､繝ｳ縺ｧ縺ゅ▲縺溷撫鬘後ｒ縲∵怙譁ｰ繝励Ξ繝溘い繝UI繝・Φ繝励Ξ繝ｼ繝医∈荳諡ｬ螟画鋤縲・- **迚ｩ逅・ヵ繧｡繧､繝ｫ縺ｮ驟咲ｽｮ驕ｩ豁｣蛹・*: 繧｢繝・・繝ｭ繝ｼ繝臥畑繝・ぅ繝ｬ繧ｯ繝医Μ `CTO\posts\` 逶ｴ荳九↓縲∵怙譁ｰ蛹悶＠縺溷・HTML・・aq1-4, news12-13・峨ｒ驟咲ｽｮ縲ゅ％繧後↓繧医ｊ縲仝inSCP縺ｧ縺ｮ繧｢繝・・繝ｭ繝ｼ繝峨′蜊ｳ蠎ｧ縺ｫ蜿肴丐縺輔ｌ繧狗憾諷九↓謾ｹ蝟・・- **繝・ャ繝峨Μ繝ｳ繧ｯ/蟷ｽ髴翫ヵ繧｡繧､繝ｫ縺ｮ謨ｴ逅・*: 邂｡逅・判髱｢螟悶〒蟄伜惠縺励※縺・◆ `faq4.html`, `news13.html` 遲峨・繝・せ繝郁ｨ倅ｺ九ｂ荳蠕九・繝ｬ繝溘い繝繝・じ繧､繝ｳ縺ｸ邨ｱ荳縲・
## [v2.8.0] - 2026-04-01
### 笨ｨ 譁ｰ隕丞ｮ溯｣・- **繝励Ξ繝溘い繝繝ｻ繝槭ぎ繧ｸ繝ｳ鬚ｨUI縺ｮ蟆主・**: 繧ｰ繝ｩ繧ｹ繝｢繝ｼ繝輔ぅ繧ｺ繝隱ｿ繧ｫ繝ｼ繝峨√し繧ｯ繝ｩ繧｢繧ｯ繧ｻ繝ｳ繝医Λ繧､繝ｳ縲√Ξ繧ｹ繝昴Φ繧ｷ繝門ｯｾ蠢懊ｒ邨ｱ蜷医＠縺滓怙譁ｰUI繝槭せ繧ｿ繝ｼ繝・Φ繝励Ξ繝ｼ繝医・遲門ｮ壹・- **荳ｻ隕√・繝ｼ繧ｸ縺ｮ蛻ｷ譁ｰ**: `index.html`, `news.html`, `posts/news10.html` 繧偵・繝ｬ繝溘い繝UI縺ｸ譖ｴ譁ｰ縲・





## [v2.9.53] - 2026-04-25 - 固定ページ全体のタイトル帯を再設計
### 変更内容
- `index.html` / `service.html` / `company.html` / `contact.html` / `concept.html` / `news.html` / `recruit.html` / `access.html` / `custom.html` / `pay.html` / `custom1.html` のページタイトル帯を共通トーンへ再設計
- H1 を英字ベースの短いタイトルへ整理し、日本語は補足行へ分離
- 見出し色を黒・グレー系から深いさくら色 `#a85c70` へ変更
- タイトル帯の背景を `#fffdfd` から `#f8f4f4` へつながるグラデーションにし、角丸と余白も統一
- 見出しの文字組みを serif ベースに変更し、字間を広げて上品な印象へ調整

### バックアップ
- `backups/v2.9.53/` に更新前バックアップを保存

**変更ファイル:** index.html / service.html / company.html / contact.html / concept.html / news.html / recruit.html / access.html / custom.html / pay.html / custom1.html / RELEASE_NOTES.md

---
## [v2.9.54] - 2026-04-25 - status.html を現行トーンへ再構成
### 変更内容
- `status.html` を旧デザインと文字化け状態から再構成し、ヘッダー・ナビ・ページタイトル帯・フッターを現行トーンへ統一
- 固定文言、パンくず、メニュー、凡例、ローディング・空状態メッセージを日本語として読める形へ修正
- ステータスカードと凡例の配色・線・余白を `さくらねっと` の現行デザインに合わせて調整
- `status_api.php` からの動的読込は維持しつつ、ラベル・日付表示・エラーメッセージを再定義

### バックアップ
- `backups/v2.9.54/` に更新前バックアップを保存

**変更ファイル:** status.html / RELEASE_NOTES.md

---
## [v2.9.55] - 2026-04-26 - FAQ/導入事例ページの外観を現行トーンへ調整
### 変更内容
- `posts/faq_archive.html` の一覧カード、CTA、導入事例導線、ヘッダー/フッターを現行トーンへ調整
- `posts/faq1.html` / `faq2.html` / `faq3.html` の記事見出し、ヘッダー、ナビ、フッターの旧カラーを整理
- `posts/faq5.html` / `faq6.html` / `faq7.html` / `faq8.html` / `faq9.html` の記事見出し、CTA、戻るボタン、フッターを現行トーンへ統一
- `posts/jirei1.html` の導入事例ページも記事見出し、CTA、結果ブロック、フッターを現行トーンへ調整

### バックアップ
- `backups/v2.9.55/` に更新前バックアップを保存

**変更ファイル:** posts/faq_archive.html / posts/faq1.html / posts/faq2.html / posts/faq3.html / posts/faq5.html / posts/faq6.html / posts/faq7.html / posts/faq8.html / posts/faq9.html / posts/jirei1.html / RELEASE_NOTES.md

---

## v2.9.58 - 2026-04-26
- posts配下の news / faq / 導入事例ページへ現行トーンの上書きスタイルを追加
- 旧ピンクUIのヘッダー・ナビ・見出し・CTA・フッターを本文非破壊で調整
- news8.html を含む個別記事と news_archive / faq_archive に適用

---

## [v3.0.7] - 2026-04-27 - トップページ詳細導線の新規ページ作成
### 変更内容
- トップページの「詳しく見る」リンク先を既存 `posts/service*.html` から専用詳細ページへ変更
- `sakura-net-hikari.html` を新規作成し、SAKURA-NET光の法人向け光回線・通信導入支援ページを追加
- `unifi-network.html` を新規作成し、UniFi Cloud Gateway / UniFi Network を意識したネットワーク構築ページを追加
- `unifi-security.html` を新規作成し、UniFi Protect / UniFi Access を意識した防犯カメラ・入退室管理ページを追加
- トップページ「提供サービス」内の SAKURA-NET光 / UniFiネットワーク / UniFi Protect / UniFi Access の詳細リンクも新規ページへ統一

### 参照情報
- Ubiquiti公式サイトおよびHelp Center: Cloud Gateway / UniFi Network / UniFi Protect / UniFi Access
- MIMEYOI公式サイトおよびデザイン考察記事を参考に、余白・小さめの導線・静かな高級感をBtoB向けに調整

### バックアップ
- `backups/v3.0.7_pre-detail-pages/` に更新前バックアップを保存

**変更ファイル:** index.html / sakura-net-hikari.html / unifi-network.html / unifi-security.html / RELEASE_NOTES.md

---

## [v3.0.8] - 2026-04-27 - 詳細ページへGemini生成画像を追加
### 変更内容
- Gemini生成画像をWebP化し、詳細ページ用画像として `img/` に追加
- `sakura-net-hikari.html` のヒーローにSAKURA-NET光イメージを追加
- `unifi-network.html` のヒーローと補足パネルにUniFiネットワーク・機器イメージを追加
- `unifi-security.html` のヒーローとProtect / Accessカードにカメラ・入退室管理イメージを追加
- MIMEYOI参考の余白感を維持し、画像は主張しすぎないカード型で配置

### 追加画像
- `img/detail_sakura_net_hikari.webp`
- `img/detail_unifi_network.webp`
- `img/detail_unifi_equipment.webp`
- `img/detail_unifi_protect.webp`
- `img/detail_unifi_access.webp`

### バックアップ
- `backups/v3.0.8_pre-detail-images/` に更新前バックアップを保存

**変更ファイル:** sakura-net-hikari.html / unifi-network.html / unifi-security.html / img/detail_*.webp / RELEASE_NOTES.md

---

## [v3.0.9] - 2026-04-27 - トップページ提供サービス画像を詳細ページ画像へ差し替え
### 変更内容
- トップページ「提供サービス」の SAKURA-NET光 / UniFiネットワーク / UniFi Protect / UniFi Access のカード画像を `detail_*.webp` に差し替え
- 既存のカード構造・リンク先は維持し、絵文字アイコン表示から写真ベースのカードへ変更
- 詳細ページとトップページのビジュアルトーンを統一

### バックアップ
- `backups/v3.0.9_pre-home-service-images/` に更新前バックアップを保存

**変更ファイル:** index.html / RELEASE_NOTES.md

---

## [v3.0.13] - 2026-04-27 - SUPPORTページを新規作成
### 変更内容
- `custom1.html` のサポート概要を元に、公開導線用の `support.html` を新規作成
- `support.html` の title / description / canonical / OGP / 構造化データを新規URL向けに整理
- トップページ下部フッターナビゲーションに `SUPPORT` を追加
- `support.html` 側のフッターは `SYSTEM` リンクと `© 2026 SAKURA-NET Inc. All Rights Reserved.` 表記に統一

### バックアップ
- `backups/v3.0.13_pre-support-page/` に更新前バックアップを保存

**変更ファイル:** index.html / support.html / RELEASE_NOTES.md

---

## [v3.0.14] - 2026-04-27 - 主要ページのフッターを統一
### 変更内容
- 主要公開ページのフッターナビゲーションに `SUPPORT` を追加
- 主要公開ページのフッターナビゲーションに `SYSTEM` を追加し、`https://sakuranet-co.jp/system/index.php` へリンク
- 主要公開ページのコピーライトを `© 2026 SAKURA-NET Inc. All Rights Reserved.` へ統一
- `remote.html` は現行フッター構造が異なるため今回は未変更

### バックアップ
- `backups/v3.0.14_pre-footer-unify-all-pages/` に更新前バックアップを保存

**変更ファイル:** access.html / company.html / concept.html / contact.html / custom.html / custom1.html / news.html / pay.html / privacy.html / recruit.html / service.html / status.html / sakura-net-hikari.html / unifi-network.html / unifi-security.html / RELEASE_NOTES.md

---

## [v3.0.17] - 2026-04-27 - SAKURA-NET Mobile / SIM 詳細ページを新規作成
### 変更内容
- `sakura-net-mobile.html` を新規作成
- SAKURA-NET Mobile / SIM を mineo BiZ 法人向けサービスをベースにしたOEMとして説明
- 法人スマートフォン、モバイルルーター、M2M・IoT、VPN-SIM、5Gオプション、端末調達相談の対応範囲を整理
- mineo法人公式の約款・規約に準拠する旨を明記
- `Service` / `BreadcrumbList` の構造化データを追加
- `service.html` の Mobile / SIM カード説明を DoRACOON中心に見えない表現へ修正

### 参照元
- mineo法人 約款・規約一覧: `https://support.mineo.jp/business/agreelist.html`

### バックアップ
- `backups/v3.0.17_pre-mobile-page/` に更新前バックアップを保存

**変更ファイル:** sakura-net-mobile.html / service.html / RELEASE_NOTES.md

---

## [v3.0.12] - 2026-04-27 - トップページコピーライト表記を更新
### 変更内容
- `index.html` のコピーライト表記を `© 2026 SAKURA-NET Inc. All Rights Reserved.` へ変更

### バックアップ
- `backups/v3.0.12_pre-copyright-2026/` に更新前バックアップを保存

**変更ファイル:** index.html / RELEASE_NOTES.md

---

## [v3.0.10] - 2026-04-27 - 詳細ページ内の写真表示を削除
### 変更内容
- `sakura-net-hikari.html` / `unifi-network.html` / `unifi-security.html` から詳細ページ内の写真表示を削除
- トップページの提供サービス写真カードは維持
- 詳細ページはテキスト・余白・カード構成中心の静かなレイアウトへ戻し、読みやすさを優先
- `img/detail_*.webp` はトップページで利用中のため削除せず保持

### バックアップ
- `backups/v3.0.10_pre-remove-detail-images/` に更新前バックアップを保存

**変更ファイル:** sakura-net-hikari.html / unifi-network.html / unifi-security.html / RELEASE_NOTES.md

---

## [v3.0.11] - 2026-04-27 - トップページフッターにSYSTEMリンクを追加
### 変更内容
- `index.html` のフッターナビゲーションに `SYSTEM` を追加
- リンク先を `https://sakuranet-co.jp/system/index.php` に設定

### バックアップ
- `backups/v3.0.11_pre-footer-system-link/` に更新前バックアップを保存

**変更ファイル:** index.html / RELEASE_NOTES.md

