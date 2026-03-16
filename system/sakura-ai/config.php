<?php
/**
 * SAKURA AI - 基本共通設定ファイル
 * 接続先URLやAPIキーをここで一元管理します。
 * 本番サーバー（さくらインターネット等）へデプロイする際は、ここを書き換えてください。
 */
// --- 1. アプリケーション設定 ---
define('APP_VERSION', '0.45');
// --- 2. AI (Ollama) 設定 ---
// ローカル実行時 (デフォルト): 'http://localhost:11434/api/generate'
// さくらインターネットから自宅PCへ繋ぐ際: 'https://your-ngrok-url.ngrok-free.app/api/generate' 等
define('OLLAMA_URL', 'https://chiquita-heterostyled-leslee.ngrok-free.dev/api/generate');
// デフォルトのモデル名 (14Bだとニュース取得時に重くなるため、7B/8Bクラスを推奨)
define('DEFAULT_MODEL', 'qwen2.5-coder:7b-instruct-q4_K_M');
// 推奨: 'qwen2.5-coder:7b-instruct-q4_K_M' または 'llama3.1:8b'
// --- 2. 秘密設定の読み込み ---
if (file_exists(__DIR__ . '/config-secret.php')) {
    require_once __DIR__ . '/config-secret.php';
}

// --- 3. Web 検索API (デフォルト値) ---
if (!defined('TAVILY_API_KEY')) define('TAVILY_API_KEY', '');
if (!defined('GOOGLE_API_KEY')) define('GOOGLE_API_KEY', '');
if (!defined('GOOGLE_CX')) define('GOOGLE_CX', '');
// --- 3. 天気API設定 (OpenWeatherMap) ---
define('OPENWEATHER_API_KEY', ''); // 必要に応じて入力