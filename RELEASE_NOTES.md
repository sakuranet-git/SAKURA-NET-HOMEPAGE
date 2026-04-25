# SAKURA-NET プレミアムUI 改修履歴 (RELEASE_NOTES)

## [v2.9.45] - 2026-04-25 — ニュース欄フォールバック修正・news12 をアーカイブに追加

### 🐛 バグ修正
- `index.html`: ローカル file:// 表示時に Chrome が fetch をブロックしニュースが非表示になる問題を修正
  - インライン JSON データ（`<script id="news-inline-data" type="application/json">`）を追加
  - API・archive fetch 両方が失敗した場合のフォールバックとして最新5件を確実に表示
- `posts/news_archive.html`: news12（さくらインターネットメンテナンスのお知らせ / 2026-04-01）を追記

### 🗑️ ゴミファイル処理
- .tmp.* ファイル 25本を trash/ に移動

### 📰 インライン静的ニュースデータ（最新5件）
1. さくらインターネットメンテナンスのお知らせ - 2026-04-01
2. SAKURA-NETサービス契約約款（最新版）- 2026-02-22
3. 臨時休業のお知らせ - 2025-11-01
4. SAKURA-IPPBX利用開始のお知らせ - 2025-10-01
5. SAKURA-NET光開始のお知らせ - 2024-11-01

### 🗂 バックアップ
- `backups/v2.9.45_pre-news-fix/index.html`
- `backups/v2.9.45_pre-news-fix/posts/news_archive.html`

**変更ファイル:** index.html / posts/news_archive.html / RELEASE_NOTES.md

---

## [v2.9.44] - 2026-04-25 — index.html MIMEYOI風「静かな高級感」局所改善

### 🎨 デザイン改善（10項目）
- CSS変数 `--sakura / --sakura-deep / --sakura-border / --bg-base / --bg-light / --text-main / --text-sub` 追加
- `body { -webkit-font-smoothing: antialiased; }` によるフォントレンダリング改善
- `@keyframes fadeUp` 追加・ヒーローセクション（`#hero-flex`）に 0.7s アニメーション適用
- ヘッダー・contenthead 背景: `#fff0f5` → `#fffdfd`（クリーンホワイト）
- キャッチフレーズ: `color #c82054` → `#c97b8d`、`text-shadow` 除去
- 情報ボックス: `border 2px #ffc1d3` → `1px #ead8dd`、`box-shadow` 除去
- ページタイトル背景: `#fff4f7` → `#f8f4f4`、border → `#ead8dd`
- H1: `color #c82054` → `#2f2a2a`（ダークテキスト）
- 課題ボックス: `bg #fff4f7` → `#f8f4f4`、border → `#ead8dd`、h3/CTAテキスト → `#c97b8d`
- ソリューション見出し: `border-bottom 2px #e03164` → `1px #ead8dd`、`color` → `#c97b8d`
- ソリューションカード5枚: `border #e0e0e0` → `#ead8dd`、`box-shadow` 除去
- カード h4 色: `#c82054` → `#c97b8d`（全5枚）
- ナビ pill ボタン: `border 2px #ffc1d3` → `1px #ead8dd`、色 → `#c97b8d`、hover → `#a85c70`、shadow 除去
- ニュース欄 CSS: border/shadow/gradient を `#c97b8d/#a85c70/#ead8dd` トーンに統一
- ステータスアラートバナー: bg `#fff0f5` → `#f8f4f4`、border `2px #ffc1d3` → `1px #ead8dd`、left accent `5px #c82054` → `4px #c97b8d`
- CTA サブテキスト: `#a08090` → `#6f6262`
- フッター: bg `#fff4f7` → `#f8f4f4`、`border-top 3px` → `1px #ead8dd`
- フッター copyright・プライバシーリンク: `#c82054` → `#6f6262`

### 🗂 バックアップ
- `backups/v2.9.44_pre-mimeyoi/index/index.html` に更新前を保存済み

**変更ファイル:** index.html / RELEASE_NOTES.md

---

## [v2.9.43] - 2026-04-25 — faq_archive.html に jirei1.html への内部リンク追加

### 🔗 内部導線追加
- `posts/faq_archive.html` のFAQ一覧下部に「導入事例」ブロックを追加
- jirei1.html へのリンクボタンを contact CTA の上に配置
- contact CTA はそのまま維持

### 🗂 バックアップ
- `backups/v2.9.43_pre-jirei-link/posts/faq_archive.html` に更新前を保存

**変更ファイル:** posts/faq_archive.html / RELEASE_NOTES.md

---

## [v2.9.42] - 2026-04-25 — faq_archive.html をプレミアムスタイルに全面更新

### 🎨 スタイル改善
- `posts/faq_archive.html` を旧テンプレートスタイルから個別FAQページ同等のさくらピンクスタイルに全面書き直し
- ヘッダー・ナビ・フッターを faq5〜faq9 と統一
- FAQ一覧を `.faq-item` カード形式（ホバーアニメーション付き）に刷新
- meta description を追加
- ページ下部に contact.html へのCTAを追加

### 🗂 バックアップ
- `backups/v2.9.42_pre-faq-archive-restyle/posts/faq_archive.html` に更新前を保存

**変更ファイル:** posts/faq_archive.html / RELEASE_NOTES.md

---

## [v2.9.41] - 2026-04-25 — FAQ 5本・導入事例 1本の新規追加

### 📝 変更内容
- `posts/faq5.html` 新規作成：Q. UniFiの導入費用はどのくらいかかりますか？
- `posts/faq6.html` 新規作成：Q. SAKURA-NET光の月額料金と回線速度を教えてください
- `posts/faq7.html` 新規作成：Q. 法人ITサポートはどこまで対応してもらえますか？
- `posts/faq8.html` 新規作成：Q. リモートで対応してもらえますか？出張費はかかりますか？
- `posts/faq9.html` 新規作成：Q. 防犯カメラや入退室管理の工事も対応していますか？
- `posts/jirei1.html` 新規作成：【導入事例】大阪市内の小売業様へUniFiネットワーク＋防犯カメラを一括導入
- `posts/faq_archive.html` 更新：faq5〜faq9 の5件を一覧に追加
- `posts/faq4.html` （テストファイル）を trash/ に移動

### 🗂 バックアップ
- `backups/v2.9.41_pre-faq-jirei/posts/faq_archive.html` に更新前を保存

**変更ファイル:** posts/faq5-faq9.html / posts/jirei1.html / posts/faq_archive.html / RELEASE_NOTES.md

---

## [v2.9.40] - 2026-04-25 — FAQ・導入事例のSEO設計書を追加

### 📝 変更内容
- `SEO_CONTENT_PLAN_FAQ_CASES.md` を新規作成
- FAQ案、導入事例案、狙う検索テーマ、実装方針、Claude Code向け依頼文を整理
- 次フェーズのSEO強化の土台として利用できる内容を文書化

### 🗂 バックアップ
- `backups/v2.9.40/` に `RELEASE_NOTES.md` を保存

**変更ファイル:** SEO_CONTENT_PLAN_FAQ_CASES.md / RELEASE_NOTES.md

---

## [v2.9.39] - 2026-04-25 — ソリューションカード画像WebP軽量化

### ⚡ パフォーマンス改善
- card_*.png (計2.35MB) → card_*.webp quality82 (計164KB) に変換（約93%削減）
  - card_sakura_hikari: 581KB → 42KB
  - card_unifi_network: 639KB → 59KB
  - card_unifi_protect: 553KB → 33KB
  - card_unifi_access: 577KB → 30KB
- index.html の img 参照を .png → .webp に4箇所変更
- 元の .png ファイルはバックアップとして img/ に保持

### 🗂 バックアップ
- `backups/v2.9.39_pre-webp-optimize/` に `index.html` を保存

**変更ファイル:** index.html / RELEASE_NOTES.md / img/card_*.webp（新規4点）

---

## [v2.9.38] - 2026-04-25 — index.html ソリューションカード画像統一

### ✨ 改善
- 「ソリューション・トピックス」4カードを画像付きで統一
  - SAKURA-NET光 → `card_sakura_hikari.png`
  - UniFiネットワーク → `card_unifi_network.png`
  - UniFi Protect → `card_unifi_protect.png`（下部旧画像を除去し上部に統一）
  - UniFi Access → `card_unifi_access.png`
- 全カード `height:160px; object-fit:cover` で高さ・比率を完全統一
- 負マージン手法でカード上部フラッシュ配置・既存レイアウト崩れなし

### 🖼 新規画像（img/に配置）
- `img/card_sakura_hikari.png`
- `img/card_unifi_network.png`
- `img/card_unifi_protect.png`
- `img/card_unifi_access.png`

### 🗂 バックアップ
- `backups/v2.9.38_pre-solution-cards/` に `index.html` を保存

**変更ファイル:** index.html / RELEASE_NOTES.md

---

## [v2.9.37] - 2026-04-25 — index.html ヒーロー画像差し替え・OGP更新・機器ビジュアル追加

### ✨ 改善
- ヒーロー画像を `modern_network.webp` → `fv_main_visual.png`（法人オフィス・さくら色テーマ）に差し替え
- ヒーロー本文下に地域密着テキスト1行追加（大阪淀川区拠点 ／ 法人専門 ／ 2002年創業 ／ 全国リモート対応）
- UniFi Protectカードに `unifi_equipment_visual.png`（AP+防犯カメラ）を追加
- OGP・Twitter Card 画像を `ogp.jpg` → `ogp_2026.png` に更新

### 🖼 新規画像（img/に配置）
- `img/fv_main_visual.png` — ヒーロー用
- `img/unifi_equipment_visual.png` — UniFi機器ビジュアル用
- `img/ogp_2026.png` — OGP用

### 🗂 バックアップ
- `backups/v2.9.37_pre-index-image-update/` に `index.html` を保存

**変更ファイル:** index.html / RELEASE_NOTES.md

---

## [v2.9.36] - 2026-04-25 — service.html 営業時間バグ修正・内部導線強化

### 🐛 バグ修正
- CTAボタンの営業時間 `10:00-17:00` → `10:00-19:00` に修正

### ✨ 改善
- `href="#"` だった主力サービスカードのリンクを `contact.html` に変更（UniFi・SAKURA-NET光・Mobile・DoRACOON）
- UniFiネットワーク・セキュリティ機器カードに「ご相談・お見積りはこちら」ボタン追加
- SAKURA-NET Total IT SUPPORTカードに「ご相談・お見積りはこちら」ボタン追加
- クラウドUTMカードに「ご相談・お見積りはこちら」ボタン追加

### 🗂 バックアップ
- `backups/v2.9.36_pre-service-fix/` に `service.html` を保存

**変更ファイル:** service.html / RELEASE_NOTES.md

---

## [v2.9.35] - 2026-04-25 — company.html 営業時間バグ修正・信頼材料追加

### 🐛 バグ修正
- CTAボタンの営業時間 `10:00-17:00` → `10:00-19:00` に修正

### ✨ 改善
- 冒頭ボックスに信頼ポイント1行追加（2002年設立 ／ 大阪市淀川区本社 ／ 資本金 ／ 従業員数 ／ 全国リモート対応）
- 電気通信事業届出番号に「（総務省届出済み事業者）」補足追記
- インボイス番号に「（適格請求書発行事業者登録済み）」補足追記

### 🗂 バックアップ
- `backups/v2.9.35_pre-company-fix/` に `company.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** company.html / RELEASE_NOTES.md

---

## [v2.9.34] - 2026-04-25 — contact.html 営業時間バグ修正・相談例追加

### 🐛 バグ修正
- TEL欄の営業時間 `10：00～17:00` → `10：00～19：00` に修正
- CTAボタンの営業時間 `10:00-17:00` → `10:00-19:00` に修正

### ✨ 改善
- 「新規ご相談・ご連絡先」の上に「こんなご相談が多いです」`<ul>` 5件を追加（CVR改善）

### 🗂 バックアップ
- `backups/v2.9.34_pre-contact-fix/` に `contact.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** contact.html / RELEASE_NOTES.md

---

## [v2.9.33] - 2026-04-25 — index.html CTA営業時間バグ修正・安心文追加

### 🐛 バグ修正
- CTAボタンの営業時間が `10:00-17:00` と誤っていたのを `10:00-19:00` に修正（ヘッダーと不一致だった）

### ✨ 改善
- 統一CTAのlead文末尾に「全国リモート対応も承ります。」を追記
- CTA内にサブテキスト（法人専門 ／ 完全予約制で丁寧対応 ／ 導入から運用サポートまで一貫対応）を追加

### 🗂 バックアップ
- `backups/v2.9.33_pre-cta-fix/` に `index.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** index.html / RELEASE_NOTES.md

---

## [v2.9.32] - 2026-04-25 — Claude Code向け次フェーズ指示書を追加

### 📝 変更内容
- `CLAUDE_CODE_NEXT_PHASE_BRIEF.md` を新規作成
- 現在の改善状況、崩れやすいポイント、次フェーズの優先順位、Claude Code向け依頼文を整理
- 「大規模リデザインではなく局所改善」を次フェーズの基本方針として明文化

### 🗂 バックアップ
- `backups/v2.9.32/` に `RELEASE_NOTES.md` を保存

**変更ファイル:** CLAUDE_CODE_NEXT_PHASE_BRIEF.md / RELEASE_NOTES.md

---

## [v2.9.31] - 2026-04-24 — title / meta description / OGP文言を最終調整

### ✨ 変更内容
- `index.html` の title / description / OGP / Twitter文言を、UniFi導入・ネットワーク構築・法人ITサポート軸へ調整
- `contact.html` の meta 文言を `お問い合わせ・ご相談` 軸に整理
- `company.html` の meta 文言を `企業概要・会社情報` と事業内容が伝わる形へ調整
- `service.html` の meta 文言を主力サービスが先に伝わる表現へ整理

### 🗂 バックアップ
- `backups/v2.9.31/` に `index.html` `contact.html` `company.html` `service.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** index.html / contact.html / company.html / service.html / RELEASE_NOTES.md

---

## [v2.9.30] - 2026-04-24 — サービスページ冒頭の主力訴求を整理

### ✨ 変更内容
- `service.html` 冒頭の案内文を調整し、主力サービスを `UniFi導入・SAKURA-NET光・防犯カメラ・入退室管理・法人ITサポート` と明確化
- 対応サービス一覧の順序を整理し、主力サービスが先に伝わるよう調整
- 既存カード一覧や詳細コンテンツの構造は維持

### 🗂 バックアップ
- `backups/v2.9.30/` に `service.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** service.html / RELEASE_NOTES.md

---

## [v2.9.29] - 2026-04-24 — 会社概要ページの冒頭要約を追加

### ✨ 変更内容
- `company.html` の企業概要一覧の前に、事業内容と強みを伝える要約ボックスを追加
- 大阪拠点、法人向けネットワーク構築、UniFi導入、防犯、入退室管理、ITサポートを明確化
- 既存の会社情報・沿革・詳細情報の構造は維持

### 🗂 バックアップ
- `backups/v2.9.29/` に `company.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** company.html / RELEASE_NOTES.md

---

## [v2.9.28] - 2026-04-24 — お問い合わせページの新規相談導線を整理

### ✨ 変更内容
- `contact.html` 冒頭に新規相談向けの案内ボックスを追加
- `電話・FAX / E-mail` 見出しを `新規ご相談・ご連絡先` へ変更
- 既存のお客様向け運用案内に見出しを追加し、新規相談向け情報と区別しやすく整理
- レイアウト構造は維持し、文言中心で改善

### 🗂 バックアップ
- `backups/v2.9.28/` に `contact.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** contact.html / RELEASE_NOTES.md

---

## [v2.9.27] - 2026-04-24 — ローカル表示用ニュース欄フォールバック追加

### 🔧 修正内容
- `index.html` のニュース取得処理を改善
- 公開環境では従来通り `/system/news_api.php` を使用
- `file://` 表示や API 失敗時は `posts/news_archive.html` から最新ニュースを抽出して表示
- ローカル確認時でもトップのニュース・お知らせ欄が表示されるよう対応

### 🗂 バックアップ
- `backups/v2.9.27/` に `index.html` `RELEASE_NOTES.md` を保存

**変更ファイル:** index.html / RELEASE_NOTES.md

---

## [v2.9.26] - 2026-04-24 — 緊急復旧（トップの大規模レイアウト変更を取り消し）

### ⏪ 復旧内容
- `index.html` `contact.html` `style.css` を `backups/v2.9.25/` ベースへ復旧
- トップページの大規模レイアウト変更を取り消し、既存の安定レイアウトへ戻し
- 復旧後、崩れない範囲でトップのH1・ヒーロー文言・CTA文言のみ再調整
- ニュース欄の初期文言を `ニュース一覧ページ` への案内付きに変更

### 🗂 バックアップ
- `backups/v2.9.26/` に復旧前の `index.html` `contact.html` `style.css` `RELEASE_NOTES.md` を保存

**変更ファイル:** index.html / contact.html / style.css / RELEASE_NOTES.md

---

## [v2.9.25] - 2026-04-24 — トップ訴求・問い合わせ導線・OEM表現改善

### ✨ 変更内容
- `index.html` を再構成し、トップのH1を `大阪のオフィスIT、すべてをひとつの窓口で。` に変更
- トップページに `よくある課題` `主要サービス` `選ばれる理由` `導入の流れ` `会社情報` セクションを追加
- トップのファーストビューに無料相談CTAと会社情報サマリーを追加
- ニュース欄の初期表示を `読み込み中...` から、ニュース一覧への案内つきプレースホルダへ変更
- `service.html` のOEMセクションから `○○○` プレースホルダ表現を除去し、公開向け文言へ修正
- `contact.html` に新規相談向け導線ブロックを追加し、既存顧客向け運用案内と分離
- `style.css` に暖色ニュートラル + Notionライクな共通クラス群を追加

### 🗂 バックアップ
- `backups/v2.9.25/` に `index.html` `service.html` `contact.html` `style.css` `RELEASE_NOTES.md` を保存

**変更ファイル:** index.html / service.html / contact.html / style.css / RELEASE_NOTES.md

---

## [v2.9.24] - 2026-04-24 — ロールバック（v2.9.22状態に復元）

### ⏪ ロールバック内容
- **v2.9.23 (Notion Design System適用)** を取り消し
- index.html / style.css を v2.9.22 状態へ復元
- 理由：Notion Blue適用によりさくらねっとのピンクブランドが失われた為

### 現在の有効フェーズ
- Phase 3-A 料金サマリー（v2.9.17）
- Phase 3-B タイポグラフィ（Inter / Noto Sans JP）
- Phase 3-C スマホ強化（mobile-cta-bar）
- Phase 3-D スクロールアニメ（scroll-animate.js）

**変更ファイル:** index.html / style.css

---

## [v2.9.23] - 2026-04-24 — ~~Phase 3-G: Notion Design System 適用~~ ※v2.9.24でロールバック済み

### 🎨 変更内容
- **カラーテーマ刷新**: ピンク系 (#c82054) → Notion Blue (#0075de) + 暖色ニュートラル
- **背景色**: `#fff0f5` / `#fff4f7` → `#ffffff` / `#f6f5f4` (Notion warm white)
- **ボーダー**: ピンクボーダー → ウィスパーボーダー `1px solid rgba(0,0,0,0.1)`
- **カード・シャドウ**: Notion 4層シャドウスタック適用
- **テキスト色**: `rgba(0,0,0,0.95)` warm near-black に統一
- **CTAボタン**: Notion Blue (#0075de) / hover: #005bab
- **フォント**: Inter + Noto Sans JP (レンダリング強化・letter-spacing追加)
- **ハンバーガーメニュー**: ピンク → Notion Blue
- **モバイルCTAバー**: ピンク → Notion Blue
- **ニュースカード・ステータスバナー**: Notion Blue アクセント
- **料金早見表・Unified CTA**: Notion スタイル統一
- CSS変数 (`--n-blue`, `--n-warm-white`, `--n-shadow` 等) 導入

### 現在の有効フェーズ
- Phase 3-A 料金サマリー（v2.9.17）
- Phase 3-B タイポグラフィ（Inter / Noto Sans JP）
- Phase 3-C スマホ強化（mobile-cta-bar）
- Phase 3-D スクロールアニメ（scroll-animate.js）
- **Phase 3-G Notion Design System（v2.9.23）** ← NEW

**変更ファイル:** index.html / style.css

---

## [v2.9.22] - 2026-04-20 — ロールバック（v2.9.19状態に復元）

### ⏪ ロールバック内容
- **v2.9.20 (ヒーローリニューアル)** と **v2.9.21 (フォント差戻し)** を取り消し
- index.html / style.css を v2.9.19 状態へ復元
- 理由：ヒーロー改修 × 旧フォント差戻しで全体デザインが劣化した為

### 現在の有効フェーズ
- Phase 3-A 料金サマリー（v2.9.17）
- Phase 3-B タイポグラフィ（Inter / Noto Sans JP）
- Phase 3-C スマホ強化（mobile-cta-bar）
- Phase 3-D スクロールアニメ（scroll-animate.js）

**変更ファイル:** index.html / style.css

---

## [v2.9.21] - 2026-04-20 — フォント・メールリンク修正（※v2.9.22でロールバック済）

### 🔧 修正内容（取り消し）
- フォントを元のヒラギノ/メイリオ系に戻す（Inter/Noto Sans JP 削除、Google Fonts読み込み除去）
- `info@sakuranet-co.jp` のmailtoリンクをプレーンテキストに変更

**変更ファイル:** index.html / style.css

---

## [v2.9.20] - 2026-04-20 — ヒーローリニューアル（Phase 3-E）（※v2.9.22でロールバック済）

### 🌸 ヒーローセクション刷新（取り消し）

- グラデーション背景（#fff0f5→#fce4ed）＋サクラ花びらアニメ（CSS keyframes）
- 1次CTAボタン「無料相談はこちら」（ピンクグラデ）＋電話CTA追加
- キャッチコピーを `hero-lead`（1.3em, bold, #c82054）に強化
- `prefers-reduced-motion` 対応（花びらアニメ無効化）
- スマホ対応（CTA幅100%・パディング調整）

**変更ファイル:** index.html / style.css

---

## [v2.9.19] - 2026-04-20 — スクロールアニメ（Phase 3-D）

### ✨ IntersectionObserver fade-in アニメーション

- 新規: `scroll-animate.js` — ページ要素が画面内に入ると fade-in + slide-up
- 対象要素: `.sp-page-title` / `.unified-cta` / `.pricing-summary` / `#hero-flex`
- `prefers-reduced-motion` 対応（アニメ無効化）
- `style.css` に `.sa-hidden` / `.sa-visible` クラス追加

**変更ファイル:** scroll-animate.js（新規） / style.css / 全10ページ（script タグ追加）

---

## [v2.9.18] - 2026-04-20 — スマホ版強化（Phase 3-C）

### 📱 スマホ固定CTAバー・タップターゲット・ハンバーガー改善

- **C-1**: 全10ページにスマホ下部固定CTAバー追加（電話・フォームボタン）
- **C-2**: モバイルメニュー展開時のタップターゲット最小44pxに拡大
- **C-3**: ハンバーガーボタンをブランドカラー（#c82054→#e03164グラデ）にリデザイン
- `body { padding-bottom: 56px }` をスマホのみ適用し、固定バーへの隠れを防止

**変更ファイル:** style.css / index.html / company.html / service.html / access.html / contact.html / recruit.html / news.html / concept.html / privacy.html / pay.html

---

## [v2.9.17] - 2026-04-20 — 統一CTAブロック 全ページ展開（Phase 3-B）

### 📞 CTAブロック pay.html へ追加

- pay.html にのみ統一CTAブロック（電話・フォーム）を追加（他6ページはv2.9.9済み）

**変更ファイル:** pay.html

---

## [v2.9.16] - 2026-04-20 — タイポグラフィ刷新 Google Fonts 導入（Phase 3-A）

### 🔤 Inter + Noto Sans JP 追加

- 全10ページに Google Fonts（Inter / Noto Sans JP）を追加
- style.css body font-family の先頭に `'Inter', 'Noto Sans JP'` を追加
- 既存のシステムフォント（ヒラギノ・メイリオ等）はフォールバックとして維持

**変更ファイル:** index.html / company.html / service.html / access.html / contact.html / recruit.html / news.html / concept.html / privacy.html / pay.html / style.css

---

## [v2.9.15] - 2026-04-20 — 画像WebP変換（Phase 2）

### 🖼️ imgをWebP化で大幅軽量化

ffmpegで10枚をWebP変換（quality 85）。元ファイルはimg/に保持。

| 画像 | 変換前 | 変換後 | 削減率 |
|---|---|---|---|
| udm-pro-max.png | 76KB | 5KB | -93% |
| page-0001.jpg | 21KB | 4KB | -79% |
| service-s01.jpg | 33KB | 8KB | -76% |
| img_brand.png | 95KB | 42KB | -56% |
| Airpayqr.JPG | 41KB | 20KB | -50% |
| rakutenpay.JPG | 36KB | 20KB | -46% |
| modern_network.png | 34KB | 21KB | -40% |
| service-s02.jpg | 13KB | 4KB | -66% |
| images.png | — | 変換済み | — |
| download.jpg | 12KB | 9KB | -29% |

※`images.jpg`(5KB)・`images1.jpg`(13%のみ)・`download2.jpg`(増加)は変換対象外

**変更ファイル:** index.html / service.html / concept.html / pay.html（src属性変更）

---

## [v2.9.14] - 2026-04-20 — 全imgタグに lazy loading 追加（Phase 2）

### ⚡ パフォーマンス改善 — `loading="lazy" decoding="async"` 追加

| ファイル | 追加数 |
|---|---|
| index.html | 1枚 |
| service.html | 7枚 |
| concept.html | 1枚 |
| pay.html | 4枚 |

合計13枚。ヒーロー以外の全コンテンツ画像がスクロール時のみ読み込まれるため初期LCPが改善。

---

## [v2.9.13] - 2026-04-20 — access.html メタタグ住所誤記修正

### 🔧 access.html の meta description / OGP / Twitter Card 住所を修正

`木川本町4-3-4 5F` → `木川東4-3-34 5F`（3箇所）

**変更箇所:** access.html 行10・行19・行27

---

## [v2.9.12] - 2026-04-20 — JSON-LD住所誤記修正

### 🔧 index.html の構造化データ住所を修正

JSON-LD（Organization・LocalBusiness）の `streetAddress` が「木川本町4-3-4 5F」と誤記されていたため、正しい住所「木川東4-3-34 5F」に修正。本文HTMLは既に正しかったため影響なし。

**変更箇所:** index.html 行50・行69（JSON-LD 2箇所）

---

## [v2.9.11] - 2026-04-20 — 料金早見表セクション追加（Phase 3-#4B）

### 💰 service.html に「主要サービス料金早見表」を追加

既存の公開価格のみを使用した6カード構成の料金早見表を service.html 下部（統一CTAブロック直前）に新設。ユーザーが価格を即座に把握でき、問い合わせ前の意思決定を後押しするCV改善施策。

### 📋 掲載サービス（既存公開価格のみ・捏造なし）

| # | サービス | 価格表示 |
|---|---|---|
| 1 | Total IT SUPPORT | 月額 4,000円〜 |
| 2 | クラウドUTM | 月額 2,500円〜 |
| 3 | VPSサーバー | 月額 550円〜 |
| 4 | PCサポート（法人） | 出張費 + 作業代 |
| 5 | PCサポート（個人） | 出張費 + 作業代 |
| 6 | IT・パソコン教室 | 段階制料金 |

**注釈**: 「※価格は税抜／詳細・最新プランは営業担当までお問い合わせください。」

### 🎨 デザイン仕様

- ピンクブランド維持（`#c82054`基調・`#ffc1d3`アクセント・`#ffd4e0`ボーダー）
- カード: 白背景 / 角丸12px / 薄ピンクボーダー / ホバーで浮き上がり
- 価格: `#c82054` で強調表示（1.6em 太字）
- レスポンシブ: PC 3列 / タブレット 2列（960px） / スマホ 1列（640px）
- タップ領域: 最小48px（アクセシビリティ確保）
- 見出し: `h2 #pricing-summary-heading` + `aria-labelledby`

### 📂 変更ファイル

| ファイル | 変更内容 |
|---|---|
| style.css | 末尾に `.pricing-summary*` CSS を約110行追加（v2.9.11ブロック） |
| service.html | 統一CTAブロック直前に `<section class="pricing-summary">` を挿入 |

### ✅ 検証

- 既存のサービス記事一覧（15カード）・商品リスト（3カード）・統一CTAに影響なし
- CSS追加のみ（既存セレクタ変更なし）
- ピンクブランド・既存フッター・ヘッダーは無変更

### 🛡️ セーフティ

- バックアップ: `backups/v2.9.11_pre-pricing/` に service.html / style.css を保存
- 既存機能への副作用なし（新規セクション・新規CSSクラスのみ）

### 📤 アップロード対象

| ローカル | サーバーパス |
|---|---|
| service.html | `/service.html` |
| style.css | `/style.css` |

---

## [v2.9.10] - 2026-04-20 — 遠隔サポートリンク一括修正

### 🎯 全13ページの「遠隔サポートはこちら」リンクを自社ページに差し替え

従来の Microsoft Store（Quick Assist）への外部リンクを、自社の遠隔サポート案内ページ `remote.html` に変更。導線を自社内に統一し、利用者が迷わず自社サポート情報に到達できるようにした。

### 🔗 変更内容

| 項目 | 変更前 | 変更後 |
|---|---|---|
| URL | `https://apps.microsoft.com/detail/9p7bp5vnwkx5?hl=ja-JP&gl=JP` | `https://sakuranet-co.jp/remote.html` |
| リンク文言 | 遠隔サポートはこちら | （変更なし） |
| `target="_blank"` | 維持 | 維持 |

### 📂 変更ファイル（13ページ・各1箇所）

| ファイル | 変更箇所 |
|---|---|
| index.html / company.html / concept.html / service.html / access.html / contact.html / recruit.html / news.html / privacy.html / custom.html / custom1.html / pay.html / status.html | ヘッダー会社情報ボックス内の「遠隔サポートはこちら」リンク href のみ |

### ✅ 検証

- 旧URL全件削除確認: `apps.microsoft.com/detail/9p7bp5vnwkx5` → 0件
- 新URL全件反映確認: `sakuranet-co.jp/remote.html` → 13件（全ページ各1件）
- `remote.html` ファイル存在確認: ✓
- 既存レイアウト・デザインに影響なし（href属性のみ置換）

### 🛡️ セーフティ

- バックアップ: `backups/v2.9.10_link-fix/` に全13 HTMLを保存
- 変更範囲: `href` 属性のみ・他の属性・テキスト・構造は一切変更なし

### 📤 アップロード対象

| ローカルファイル | サーバーパス |
|---|---|
| index.html / company.html / concept.html / service.html / access.html / contact.html / recruit.html / news.html / privacy.html / custom.html / custom1.html / pay.html / status.html | `/`（サイトルート） |

---

## [v2.9.9] - 2026-04-20 — Phase 3-#5 統一CTAブロック追加

### 🎯 全ページにCTA（Call To Action）ブロックを追加

問い合わせCVの増加を目的に、主要9ページのフッター直前に統一デザインのCTAブロックを追加。電話とお問い合わせフォームへの導線を明確化。

### 🧩 追加仕様

| 項目 | 内容 |
|---|---|
| 配置 | 全9ページ フッター直前（colophonの直前） |
| 構成 | 2ボタン: 📞 電話 `06-7777-2720` / ✉ お問い合わせフォーム |
| デザイン | ピンクブランド維持（`#c82054` 主ボタン / `#fdf2f6→#fff4f7` 背景グラデ） |
| レスポンシブ | SP (≤640px) は縦並び・タップ領域 48px以上 |
| アクセシビリティ | `aria-labelledby` / `aria-label` / セマンティック `<section>` |
| ホバー効果 | `translateY(-2px)` + shadow強化（0.2s transition） |

### 📂 変更ファイル

| ファイル | 変更 |
|---|---|
| style.css | `.unified-cta` 系 110行 追記（既存スタイル変更なし） |
| index.html | CTAブロック挿入 |
| company.html | CTAブロック挿入 |
| service.html | CTAブロック挿入 |
| access.html | CTAブロック挿入 |
| contact.html | CTAブロック挿入 |
| recruit.html | CTAブロック挿入 |
| news.html | CTAブロック挿入 |
| concept.html | CTAブロック挿入 |
| privacy.html | CTAブロック挿入 |

### ✅ 検証

- ローカル PC（1280×800）: index/contact 崩れなし ✓
- ローカル SP（390×844）: 縦並び・タップ領域OK ✓
- ピンクブランド維持・既存UI非破壊 ✓

### 🛡️ セーフティ

- バックアップ: `backups/v2.9.9_pre-cta/`（9HTML + style.css）
- 作業スクリプト: `trash/phase3-5_2026-04-20/` へ退避

### 📤 アップロード対象

| ローカル | サーバー |
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

## [v2.9.8] - 2026-04-20 — Phase 2E-B 本文テキストのAA完全化

### ♿ `#e03164` 本文色をAA基準 (4.5:1) 達成色へ

v2.9.6 で据え置かれていた `#e03164`（白背景で 4.39:1 ≒ AA 4.5 未達）のうち、**本文・段落・リンクなど小テキスト用途のみ** を `#c82054` (5.47:1 ✓) に置換。見出し・アイコン・グラデーション・背景は **意図的に据え置き**。

| 指標 | v2.9.7 | v2.9.8 |
|---|---|---|
| 本文 `color: #e03164` | 混在 | **0件** |
| `#e03164` 残存（非テキスト） | 178 | 109（icon/bg/gradient/heading） |
| `#e03164` のAA対応テキスト | 未達 | **全達成** |

### 🎯 置換ロジック（自動判定 + 人手レビュー）

| 判定 | 件数 | 対象 |
|---|---|---|
| **CHANGE** (#e03164 → #c82054) | **69** | `<p>` / `<a>` / 段落内 `<span>` / CSSルール内の小テキスト |
| KEEP（アイコン） | ~52 | `<span style="color:#e03164; margin-right:5px">` 絵文字装飾 |
| KEEP（背景・枠・影） | ~40 | `background` / `border` / `linear-gradient` / `box-shadow` |
| KEEP（見出し） | ~17 | `<h1>`〜`<h6>` は Large Text 3:1 基準で既に達成 |

### 📂 変更ファイル（13ファイル・69置換）

| ファイル | 置換数 |
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

### 🛡️ 安全策
- プロパティ名でフィルタ（`background*` / `border*` / `gradient` は対象外）
- 親タグ追跡でマルチライン `<h*>` 開始タグを検出しスキップ
- アイコンパターン `margin-right: 5px` の絵文字spanはスキップ
- ローカルで PC/SP + privacy.html を目視確認（崩れなし）

### 📦 バックアップ
- `backups/v2.9.8_pre-contrast/` に 13HTMLファイルを退避

### 📤 サーバーアップロード対象（13ファイル）
| ローカル | サーバーパス |
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

## [v2.9.7] - 2026-04-20 — Phase 2D CSS未使用セレクタ削減

### 🧹 style.css の不要セレクタ一括削除

HPB（IBMホームページビルダー）および WordPress テンプレート由来の未使用セレクタを撤去し、スタイルシートをスリム化。

| 指標 | v2.9.6 | v2.9.7 | 削減 |
|---|---|---|---|
| 行数 | 4,695 | **3,467** | **-1,228行（-26.2%）** |
| サイズ | 127KB | **85KB** | **-41.5KB（-32.7%）** |
| ルール中括弧 | 380 | 277 | -103ブロック |

### 🗑️ 削除した未使用クラス（35種類）
- **HPB残骸**: `.hpb-more-entry` / `.hpb-viewtype-content` / `.hpb-viewtype-simple` / `.hpb-viewtype-thumbnail`
- **WordPress残骸**: `.by-author` / `.cat-links` / `.comments-link` / `.tags-links` / `.sep` / `.nav-next` / `.nav-previous` / `.navigation-post` / `.current-menu-item` / `.current-menu-ancestor` / `.current_page_ancestor` / `.menu-item-has-children`
- **HPB sp系**: `.sp-button` / `.sp-column` / `.sp-form` / `.sp-list` / `.sp-table` / `.sp-google-map` / `.sp-yahoo-map` / `.sp-item-gallery`
- **その他**: `.accordion` / `.masonry` / `.grid` / `.searchform` / `.col-title` / `.column-body` / `.column-label` / `.row-title` / `.toggled-on` / `.vertical` / `.item-gallery-thumbnail`

### 🗑️ 削除した未使用ID（7種類）
`#sp-image-1/3/4`, `#sp-list-1/2/3/4`

### 🛡️ 安全策
- カンマ連結セレクタは **使用中のセレクタを残し、未使用のみを削除**
- @media ブロック内部も再帰的に処理
- 編集前後で brace balance 検証（277/277 一致）
- ローカル (PC/SP) で表示確認済み

### 📦 バックアップ
- `backups/v2.9.7_pre-css-cleanup/style.css`

### 📤 サーバーアップロード対象（1ファイル）
| ファイル | サーバーパス |
|---|---|
| style.css | `https://sakuranet-co.jp/style.css` |

---

## [v2.9.6] - 2026-04-20 — Phase 2E-B アクセシビリティ（色コントラスト改善）

### ♿ WCAG AA 準拠 — style.css 色コントラスト修正（29箇所）

| # | 旧色 | 新色 | 比（白背景/該当背景） | 置換数 |
|---|---|---|---|---|
| ① | `#f0a7a7` | `#c82054` | 1.95:1 → **5.51:1** ✓ | 4 |
| ② | `#c76b6b` | `#8c2b47` | 2.78:1 (on #ffd9d9) → **約5.5:1** ✓ | 17 |
| ③ | `#929292` | `#767676` | 3.12:1 → **4.54:1** ✓ | 3 |
| ④ | `#eb6a6a` | `#c82054` | 3.07:1 → **5.51:1** ✓ | 5 |

### 🎨 視覚的効果
- 淡いサーモン/コーラル系を廃止し、メインピンク `#c82054` に統合 → ブランド統一感が向上
- SPナビ・サブメニューの小豆色 `#c76b6b` をやや濃い `#8c2b47` に変更（読みやすさ向上）
- サブ文字グレーをわずかに濃く（`#929292` → `#767676`）

### 📋 方針
- **ブランド色 `#c82054` / `#e03164` は維持**
- `#e03164` on #ffffff（4.38:1）は大テキスト基準（3:1）合格のため**据え置き**

### 📦 バックアップ
- `backups/v2.9.6_pre-a11y-color/style.css`

### 📤 サーバーアップロード対象（1ファイル）
| ファイル | サーバーパス |
|---|---|
| style.css | `https://sakuranet-co.jp/style.css` |

### 🔍 検証方法
- Web・SP両方で表示確認（デザイン崩れなし・文字視認性向上）
- PageSpeed Insights / axe DevTools でコントラスト違反の減少を確認

---

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
