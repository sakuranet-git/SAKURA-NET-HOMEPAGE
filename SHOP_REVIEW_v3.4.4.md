# SHOP v3.4.4 実装・レビュー引継ぎ (2026-10-22)

## 完了範囲

- 注文履歴の欠損/破損時に既存履歴を空にせず停止。Stripeセッション作成前にも事前検査。
- 独立ロック、全長書込み確認、同期、renameによる保存切替。
- 商品更新はID単位マージ。未送信商品、画像/カテゴリ、未知フィールドを保持。明示削除のみ処理し全件削除を拒否。
- revision検証で古いフォームの更新を拒否。JSONフォーム転送でmax_input_varsを回避。
- バックアップ: `backups/v3.4.4-shop-integrity-20261022/` (Git除外、秘密キーを含む退避データは公開禁止)。
- 本番反映先: `/home/sakura-nets/www/shop/`。変更は storage_integrity.php / create_session.php / admin/save.php / admin/products.php のみ。
- Git実装コミット: f51886a (masterへpush済み)。

## 検証

- PHP lint成功。テスト `tests/shop-storage-integrity.test.php` と `tests/shop-save-endpoint.test.php` は実データではなくtrash配下の隔離fixtureを使用。
- 破損保護10ケース、エンドポイント7ケース。202商品維持、古いrevision拒否、欠落拒否、空画像保持、明示削除を検証。
- 実Chromeで隔離商品管理の保存が成功。スマホ390pxの管理画面はページ横はみ出しなし（既存テーブル内スクロール）。
- Claude Code独立静的レビューAPPROVE。ID型懸念は実カタログ全202件文字列・重複なしを確認。空画像の懸念は既存フォールバック＋自動テストで保持を確認。
- 本番読み戻し4件SHA-256一致。商品JSON/注文JSONは反映前後で不変。
- 本番GETのショップ/カート/購入/決済作成/管理画面は最終HTTP200、PHP致命的エラーなし。管理画面の未認証GETはログイン画面への遷移。
- 本番注文ページ390px幅390、scrollWidth390。証跡 `backups/v3.4.4-shop-integrity-20261022/verify/order-mobile.png`。
- 本番の実決済、商品データ更新はしていない。GET200を決済E2Eの証拠とは扱わない。

## 未完了・次の実装に必要な確認

- 顧客ログイン連携: ユーザーが方式を選択済み。HP作業ツリー、本番shopファイル一覧、別HPチェックアウト、PROJECT_MAP、SHOP資料を調査したが顧客専用ログインの認可モデルを確定できていない。既存自己申告制限は変更していない。社内SYSTEMログインを契約者資格とみなす変更はしない。
- Claude Codeの独立調査はSYSTEMのPasskey署名未検証、access_gate.phpの生ファイル配信等を指摘。Codexもローカル `SAKURA-PORTAL/passkey_handler.php:73` と `index.php:85`、`access_gate.php:26` を読み、問題の処理を確認。本番同一性・登録の有無・影響範囲は未確認。認証方式の無断無効化やSYSTEMファイル変更はしていない。
- PHPランタイム全vhostの更新状況と侵害有無は今回の保存保護で解決したとは扱わない。ローカルCLI PHP8.2.12での構文試験は本番PHPの証拠ではない。
- 最優先の次の確認はSYSTEM認証の本番コード同一性と回帰範囲。契約者認可を確認し、安全な認証連携を設計してから購入制限を反映する。

## 運用上の制限

- `.pending` は保存失敗時の復旧用に残す。勝手に削除しない。
- 保存後のファイル権限は0600。PHP/FTPの同一アカウント前提。別実行ユーザーへ移行時には確認が必要。
- 従来のconfig.php内保存関数は変更していない。現行管理save.phpは新ライブラリを使用。他の書込み経路を追加する際も同じロック方式を使用すること。
- 商品JSON、注文JSON、config.php、既存のprivacy.html変更はGit/アップロード対象外。
