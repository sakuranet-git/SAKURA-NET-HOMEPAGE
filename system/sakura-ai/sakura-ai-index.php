<?php
// セッションの有効期限を index.php と同じ10分に合わせる
ini_set('session.gc_maxlifetime', 600);
session_set_cookie_params(['lifetime' => 600]);
session_start();

// 未認証ならログインページへ強制リダイレクト
if (empty($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /system/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SAKURA AI Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet" />
    <!-- Release Notes Modal -->
    <div class="modal-overlay" id="rel-notes-modal">
        <div class="modal" style="width: 450px;">
            <div class="m-title">リリースノート</div>
            <div id="rel-notes-body">読み込み中...</div>
            <button class="m-close"
                onclick="document.getElementById('rel-notes-modal').classList.remove('open')">閉じる</button>
        </div>
    </div>
    <style>
        /* ========== リセット & 共通 ========== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            font-family: 'Noto Sans JP', sans-serif;
            background: #1a0010;
            color: #fff;
            position: relative;
        }

        #wallpaper-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background-size: cover;
            background-position: center;
            transition: opacity 2s ease-in-out;
            opacity: 0;
        }

        #wallpaper-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: rgba(0, 0, 0, 0.5);
            display: none;
        }

        body.has-wallpaper #wallpaper-overlay {
            display: block;
        }

        /* 壁紙有効時の透過設定 */
        body.has-wallpaper {
            background-color: transparent !important;
        }

        /* 壁紙有効時の透過設定 */
        body.has-wallpaper #sidebar {
            background: linear-gradient(180deg, rgba(61, 0, 31, 0.8) 0%, rgba(26, 0, 16, 0.9) 100%) !important;
        }

        body.has-wallpaper #chat-area,
        body.has-wallpaper #messages,
        body.has-wallpaper #chat-header {
            background: transparent !important;
        }

        body.has-wallpaper #input-wrapper {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        /* 壁紙有効時のメッセージ視認性向上 (v0.22.1 調整) */
        body.has-wallpaper .msg-bubble {
            backdrop-filter: blur(6px);
            /* 10px -> 6px */
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
        }

        body.has-wallpaper .msg-row.ai .msg-bubble {
            background: rgba(30, 20, 40, 0.45) !important;
            /* 0.7 -> 0.45 */
        }

        body.has-wallpaper .msg-row.user .msg-bubble {
            background: linear-gradient(135deg, rgba(230, 0, 126, 0.35), rgba(255, 105, 180, 0.25)) !important;
            /* 0.5/0.4 -> 0.35/0.25 */
        }

        /* ========== レイアウト ========== */
        #app {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ========== 左サイドバー ========== */
        #sidebar {
            width: 260px;
            min-width: 260px;
            background: linear-gradient(180deg, #3d001f 0%, #1a0010 100%);
            border-right: 1px solid rgba(255, 105, 180, 0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        #sidebar-header {
            padding: 20px 16px 14px;
            border-bottom: 1px solid rgba(255, 105, 180, 0.2);
        }

        #sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        #sidebar-logo:hover {
            opacity: 0.8;
        }

        #sidebar-logo .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #ff69b4, #e6007e);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        #sidebar-logo .logo-text {
            font-size: 16px;
            font-weight: 700;
            color: #ffb6d9;
        }

        #new-chat-btn {
            width: 100%;
            padding: 9px 14px;
            background: rgba(255, 105, 180, 0.15);
            border: 1px solid rgba(255, 105, 180, 0.4);
            border-radius: 8px;
            color: #ffb6d9;
            font-size: 13px;
            cursor: pointer;
            text-align: left;
            transition: background 0.2s;
        }

        #new-chat-btn:hover {
            background: rgba(255, 105, 180, 0.3);
        }

        #sidebar-title {
            padding: 12px 16px 6px;
            font-size: 11px;
            color: rgba(255, 182, 217, 0.5);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        #conv-list {
            flex: 1;
            overflow-y: auto;
            padding: 4px 8px 12px;
        }

        #conv-list::-webkit-scrollbar {
            width: 4px;
        }

        #conv-list::-webkit-scrollbar-thumb {
            background: rgba(255, 105, 180, 0.3);
            border-radius: 4px;
        }

        .conv-item {
            padding: 9px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            color: rgba(255, 182, 217, 0.8);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: background 0.15s;
            margin-bottom: 2px;
        }

        .conv-item:hover {
            background: rgba(255, 105, 180, 0.15);
            color: #fff;
        }

        .conv-item.active {
            background: rgba(255, 105, 180, 0.25);
            color: #fff;
        }

        .conv-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .conv-title-text {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .conv-delete-btn {
            opacity: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: 0.2s;
            color: rgba(255, 182, 217, 0.4);
            flex-shrink: 0;
            margin-left: 4px;
        }

        .conv-item:hover .conv-delete-btn {
            opacity: 1;
        }

        .conv-delete-btn:hover {
            background: rgba(255, 69, 117, 0.3);
            color: #ffb6d9;
        }

        /* ========== 右チャットエリア ========== */
        #chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: #110008;
        }

        /* ヘッダー */
        #chat-header {
            padding: 14px 24px;
            background: rgba(255, 105, 180, 0.05);
            border-bottom: 1px solid rgba(255, 105, 180, 0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        #chat-header .model-badge {
            background: rgba(255, 105, 180, 0.2);
            border: 1px solid rgba(255, 105, 180, 0.4);
            color: #ffb6d9;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        #chat-header .header-title {
            font-size: 15px;
            font-weight: 600;
            color: #ffb6d9;
        }

        /* メッセージ表示エリア */
        #messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        #messages::-webkit-scrollbar {
            width: 6px;
        }

        #messages::-webkit-scrollbar-thumb {
            background: rgba(255, 105, 180, 0.3);
            border-radius: 4px;
        }

        /* ウェルカム画面 */
        #welcome {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
            color: rgba(255, 182, 217, 0.6);
        }

        #welcome .welcome-icon {
            font-size: 48px;
        }

        #welcome h2 {
            font-size: 22px;
            font-weight: 700;
            color: #ffb6d9;
        }

        #welcome p {
            font-size: 14px;
            line-height: 1.6;
            text-align: center;
            max-width: 360px;
        }

        /* メッセージバブル */
        .msg-row {
            display: flex;
            gap: 12px;
            animation: fadeIn 0.2s ease;
        }

        .msg-row.user {
            flex-direction: row-reverse;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .msg-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .msg-row.user .msg-avatar {
            background: linear-gradient(135deg, #e6007e, #ff69b4);
        }

        .msg-row.ai .msg-avatar {
            background: linear-gradient(135deg, #6600cc, #9933ff);
        }

        .msg-bubble {
            max-width: 72%;
            padding: 12px 16px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.7;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .msg-row.user .msg-bubble {
            background: linear-gradient(135deg, rgba(230, 0, 126, 0.3), rgba(255, 105, 180, 0.2));
            border: 1px solid rgba(255, 105, 180, 0.3);
            color: #ffe6f4;
            border-bottom-right-radius: 4px;
        }

        .msg-row.ai .msg-bubble {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #f0e6ff;
            border-bottom-left-radius: 4px;
        }

        .msg-row.ai.thinking .msg-bubble {
            color: rgba(255, 182, 217, 0.5);
            font-style: italic;
        }

        /* ========== 入力エリア ========== */
        #input-area {
            flex-shrink: 0;
            padding: 16px 24px 20px;
            background: rgba(255, 105, 180, 0.03);
            border-top: 1px solid rgba(255, 105, 180, 0.15);
        }

        #input-wrapper {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 105, 180, 0.3);
            border-radius: 14px;
            padding: 10px 14px;
            transition: border-color 0.2s;
        }

        #input-wrapper:focus-within {
            border-color: rgba(255, 105, 180, 0.7);
        }

        #input-wrapper.dragover {
            border-color: #ff69b4;
            background: rgba(255, 105, 180, 0.15);
            box-shadow: 0 0 15px rgba(255, 105, 180, 0.3);
        }

        #msg-input {
            flex: 1;
            background: none;
            border: none;
            outline: none;
            color: #fff;
            font-size: 14px;
            font-family: inherit;
            resize: none;
            min-height: 24px;
            max-height: 140px;
            line-height: 1.5;
        }

        #msg-input::placeholder {
            color: rgba(255, 182, 217, 0.4);
        }

        #send-btn {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #e6007e, #ff69b4);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: opacity 0.2s, transform 0.1s;
        }

        #send-btn:hover {
            opacity: 0.85;
        }

        #send-btn:active {
            transform: scale(0.93);
        }

        #send-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .input-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .mic-btn {
            background: none;
            border: 1px solid rgba(255, 105, 180, 0.3);
            color: rgba(255, 182, 217, 0.8);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .mic-btn:hover {
            border-color: #ffb6d9;
            color: #fff;
        }

        .mic-btn.active {
            background: rgba(255, 69, 117, 0.3);
            border-color: #ff4575;
            color: #fff;
            animation: pulse-red 1.5s infinite;
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 69, 117, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 69, 117, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 69, 117, 0);
            }
        }

        /* Saku-dot Loader */
        .saku-dots {
            display: inline-flex;
            gap: 4px;
            align-items: center;
            vertical-align: middle;
        }

        .saku-dot {
            width: 6px;
            height: 6px;
            background-color: #ffb6d9;
            border-radius: 50%;
            animation: saku-bounce 1.4s infinite ease-in-out both;
        }

        .saku-dot:nth-child(1) {
            animation-delay: -0.32s;
        }

        .saku-dot:nth-child(2) {
            animation-delay: -0.16s;
        }

        @keyframes saku-bounce {

            0%,
            80%,
            100% {
                transform: scale(0);
            }

            40% {
                transform: scale(1.0);
            }
        }

        .bubble-actions {
            display: flex;
            gap: 12px;
            padding: 4px 12px;
            margin-top: 4px;
        }

        .b-act {
            background: none;
            border: none;
            color: rgba(255, 182, 217, 0.5);
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .b-act:hover {
            color: #ffb6d9;
        }

        #input-hint {
            font-size: 11px;
            color: rgba(255, 182, 217, 0.35);
            margin-top: 6px;
            text-align: center;
        }

        /* File Preview Area */
        #file-preview-area {
            display: none;
            padding: 8px 12px;
            background: rgba(255, 105, 180, 0.1);
            border-top: 1px solid rgba(255, 105, 180, 0.2);
            flex-wrap: wrap;
            gap: 8px;
        }

        .file-token {
            background: rgba(255, 105, 180, 0.3);
            border: 1px solid rgba(255, 105, 180, 0.5);
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .file-remove {
            cursor: pointer;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            font-size: 10px;
            transition: 0.2s;
        }

        .file-remove:hover {
            background: #ff4575;
            color: #fff;
        }

        .file-clear-btn {
            background: rgba(255, 69, 117, 0.2);
            border: 1px solid rgba(255, 69, 117, 0.4);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            color: #ffb6d9;
            cursor: pointer;
            transition: 0.2s;
            margin-left: auto;
        }

        .file-clear-btn:hover {
            background: rgba(255, 69, 117, 0.4);
        }

        /* Search Bar */
        #sidebar-search {
            padding: 10px 15px;
            border-bottom: 1px solid rgba(255, 105, 180, 0.1);
        }

        #search-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 105, 180, 0.2);
            border-radius: 20px;
            padding: 6px 12px;
            color: #fff;
            font-size: 13px;
            outline: none;
            transition: 0.2s;
        }

        #search-input:focus {
            border-color: rgba(255, 105, 180, 0.5);
            background: rgba(255, 255, 255, 0.08);
        }

        /* UI Components */
        .h-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 105, 180, 0.3);
            color: #ffb6d9;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }

        .h-select option {
            background: #1a0010;
            color: #fff;
        }

        .h-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 105, 180, 0.3);
            color: rgba(255, 182, 217, 0.8);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s;
        }

        .h-btn:hover {
            border-color: #ffb6d9;
            color: #fff;
        }

        .h-btn.active {
            background: rgba(255, 105, 180, 0.2);
            border-color: #ffb6d9;
            color: #fff;
        }

        .header-actions {
            margin-left: auto;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        /* モバイル対応用ユーティリティとメニューボタン */
        .mobile-only {
            display: none;
        }

        .menu-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 105, 180, 0.3);
            color: #ffb6d9;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .mobile-only {
                display: flex;
            }

            #sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                width: 280px;
                height: 100%;
                z-index: 1000;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.5);
                transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            #sidebar.open {
                transform: translateX(280px);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                backdrop-filter: blur(2px);
            }

            .sidebar-overlay.open {
                display: block;
            }

            .msg-bubble {
                max-width: 85%;
            }

            #chat-header {
                padding: 10px 16px;
            }

            #messages {
                padding: 16px 12px;
            }

            #input-area {
                padding: 10px 16px 16px;
            }
        }

        /* ----- Modals ----- */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal {
            background: #120c18;
            border: 1px solid rgba(255, 105, 180, 0.3);
            border-radius: 16px;
            padding: 24px;
            width: 320px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .m-title {
            font-size: 16px;
            font-weight: 700;
            color: #ffb6d9;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        .m-group {
            margin-bottom: 20px;
        }

        .m-label {
            font-size: 12px;
            color: rgba(255, 182, 217, 0.6);
            margin-bottom: 8px;
        }

        .m-btns {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .m-btn {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 105, 180, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
        }

        .m-btn:hover {
            border-color: #ffb6d9;
        }

        .m-btn.active {
            background: rgba(255, 105, 180, 0.2);
            border-color: #ffb6d9;
            color: #ffb6d9;
        }

        .m-close {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: none;
            background: #ff69b4;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        /* Sidebar Footer UI */
        .sb-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 105, 180, 0.2);
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .sbf-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 105, 180, 0.2);
            color: #ffb6d9;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: 0.2s;
        }

        .sbf-btn:hover {
            background: rgba(255, 105, 180, 0.2);
            border-color: #ffb6d9;
        }

        /* Release Notes Modal */
        #rel-notes-body {
            max-height: 400px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            color: #f0e6ff;
            margin-bottom: 15px;
        }

        #rel-notes-body::-webkit-scrollbar {
            width: 4px;
        }

        #rel-notes-body::-webkit-scrollbar-thumb {
            background: rgba(255, 105, 180, 0.3);
            border-radius: 4px;
        }

        /* Themes */
        [data-theme="light"] {
            background: #fff5f8;
            color: #331a26;
        }

        [data-theme="light"] #messages {
            background: #fff5f8;
        }

        [data-theme="light"] .msg-bubble {
            color: #331a26;
        }

        [data-theme="light"] .msg-row.ai .msg-bubble {
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        [data-theme="light"] #input-wrapper {
            background: #fff;
        }

        [data-theme="light"] #msg-input {
            color: #331a26;
        }

        [data-theme="purple"] {
            background: #0a0514;
        }

        [data-theme="purple"] #sidebar {
            background: linear-gradient(180deg, #1f003d 0%, #0a0514 100%);
            border-right-color: rgba(157, 80, 187, 0.2);
        }
    </style>
</head>

<body data-theme="dark" style="font-size:14px;">
    <div id="wallpaper-bg"></div>
    <div id="wallpaper-overlay"></div>
    <div id="app">
        <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>
        <div id="sidebar">
            <div id="sidebar-header">
                <div id="sidebar-logo" onclick="startNewChat()">
                    <div class="logo-icon">🌸</div>
                    <div class="logo-text">SAKURA AI <small id="app-version-display"
                            style="font-size: 10px; opacity: 0.6; font-weight: normal; vertical-align: middle;">v0.3</small>
                    </div>
                </div>
                <button id="new-chat-btn" onclick="startNewChat()">＋ 新しい会話</button>
            </div>
            <div id="sidebar-search">
                <input type="text" id="search-input" placeholder="履歴を検索..." oninput="filterConversations()">
            </div>
            <div id="sidebar-title">会話履歴</div>
            <div id="conv-list"></div>
            <div class="sb-footer">
                <button class="sbf-btn" onclick="window.open('dashboard.html','_blank')" title="統計">📊</button>
                <div style="position:relative;">
                    <button class="sbf-btn" onclick="toggleExportMenu()" title="出力">📥</button>
                    <div id="export-menu"
                        style="display:none; position:absolute; bottom:45px; left:0; background:#120c18; border:1px solid rgba(255,105,180,0.3); border-radius:8px; padding:8px; width:140px; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
                        <div class="m-btn" style="margin-bottom:4px;text-align:center" onclick="exportChat('json')">JSON
                        </div>
                        <div class="m-btn" style="margin-bottom:4px;text-align:center" onclick="exportChat('md')">
                            Markdown</div>
                        <div class="m-btn" style="margin-bottom:4px;text-align:center" onclick="exportChat('txt')">Text
                        </div>
                        <div class="m-btn" style="text-align:center" onclick="exportChat('html')">🔗 Share HTML</div>
                    </div>
                </div>
                <button class="sbf-btn" id="wp-skip-btn" onclick="skipWallpaper()" title="次の壁紙へ"
                    style="display:none;">🖼</button>
                <button class="sbf-btn" onclick="syncCloud()" title="クラウド同期 (手動マージ)">🔄</button>
                <button class="sbf-btn" onclick="showReleaseNotes()" title="リリースノート">📝</button>
                <button class="sbf-btn" onclick="openSettings()" title="設定">⚙</button>
                <button class="sbf-btn" onclick="clearAll()" title="リセット">🗑</button>
            </div>
        </div>
        <div id="chat-area">
            <div id="chat-header">
                <button id="menu-btn" class="menu-btn mobile-only" onclick="toggleSidebar()">☰</button>
                <span class="header-title mobile-only" style="margin-right:auto;">🌸</span>
                <span class="header-title" style="display:none;" id="desktop-title">🌸 SAKURA AI Chat</span>

                <div class="header-actions">
                    <button class="h-btn" id="privacy-btn" onclick="togglePrivacy()" title="外部通信遮断">🔒 プラ</button>
                    <select class="h-select" id="model-select" onchange="setModel(this.value)">
                        <option value="qwen3:8b">🤖 qwen3:8b</option>
                        <option value="phi3">🤖 phi3</option>
                    </select>
                    <select class="h-select" id="mode-select" onchange="setMode(this.value)">
                        <option value="standard">🌸 標準</option>
                        <option value="sales">💼 営業</option>
                        <option value="code">💻 コード</option>
                        <option value="creative">✨ 創造</option>
                    </select>
                    <button class="h-btn" id="weather-btn" onclick="toggleWeather()">☁ 天気</button>
                </div>
            </div>
            <div id="messages">
                <div id="welcome">
                    <div class="welcome-icon">🌸</div>
                    <h2>SAKURA AI へようこそ</h2>
                    <p>完全ローカルで動作するAIアシスタントです。<br>何でもお気軽にご質問ください。</p>
                </div>
            </div>
            <div id="file-preview-area"></div>
            <div id="input-area">
                <div id="input-wrapper">
                    <input type="file" id="file-input" style="display:none" multiple
                        accept=".html,.css,.js,.php,.txt,.csv,.xlsx,.xls,.json" />
                    <button class="mic-btn" onclick="document.getElementById('file-input').click()"
                        title="ファイルを添付">📎</button>
                    <textarea id="msg-input" rows="1" placeholder="メッセージを入力 (Shift+Enterで改行)"></textarea>
                    <div class="input-actions">
                        <button id="mic-btn" class="mic-btn" onclick="toggleMic()" title="音声入力">🎤</button>
                        <button id="send-btn" onclick="sendMessage()" title="送信">➤</button>
                    </div>
                </div>
                <div id="input-hint">Enter で送信 ／ Shift+Enter で改行</div>
            </div>
        </div>

        <!-- Settings Modal -->
        <div id="settings-modal" class="modal-overlay">
            <div class="modal">
                <div class="m-title">⚙ Settings</div>

                <div class="m-group">
                    <div class="m-label">テーマ</div>
                    <div class="m-btns">
                        <button class="m-btn thm-btn" data-v="dark" onclick="setThemeBtn('dark')">Dark (桜)</button>
                        <button class="m-btn thm-btn" data-v="light" onclick="setThemeBtn('light')">Light</button>
                        <button class="m-btn thm-btn" data-v="purple" onclick="setThemeBtn('purple')">Purple</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">フォントサイズ</div>
                    <div class="m-btns">
                        <button class="m-btn fs-btn" data-v="12" onclick="setFSBtn('12')">小</button>
                        <button class="m-btn fs-btn" data-v="14" onclick="setFSBtn('14')">中</button>
                        <button class="m-btn fs-btn" data-v="16" onclick="setFSBtn('16')">大</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">回答の長さ</div>
                    <div class="m-btns">
                        <button class="m-btn sl-btn" data-v="short" onclick="setSLBtn('short')">短文 (簡潔)</button>
                        <button class="m-btn sl-btn" data-v="normal" onclick="setSLBtn('normal')">通常</button>
                        <button class="m-btn sl-btn" data-v="long" onclick="setSLBtn('long')">長文 (詳細)</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">回答の口調</div>
                    <div class="m-btns">
                        <button class="m-btn st-btn" data-v="polite" onclick="setSTBtn('polite')">丁寧・敬語</button>
                        <button class="m-btn st-btn" data-v="frank" onclick="setSTBtn('frank')">フランク (タメ口)</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">セキュリティ</div>
                    <button class="m-btn" onclick="setPIN()" style="width:100%;">🔐 PINロック設定</button>
                </div>

                <div class="m-group" style="border-top:1px solid rgba(255,105,180,0.2); padding-top:15px;">
                    <div class="m-label" style="color:#ffb6d9; font-weight:bold;">🖼 壁紙スライドショー (v0.20)</div>
                    <label
                        style="display:flex; align-items:center; gap:10px; font-size:13px; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" id="wp-enabled-check" onchange="saveCfg()"> 有効にする
                    </label>
                    <div
                        style="display:flex; align-items:center; gap:10px; font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:10px;">
                        切り替え間隔: <input type="number" id="wp-interval-input"
                            style="width:50px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,105,180,0.3); color:#fff; border-radius:4px; padding:2px 5px;"
                            min="1" onchange="saveCfg()"> 分
                    </div>
                    <button class="m-btn" onclick="changeWallpaper()"
                        style="width:100%; font-size:11px; padding:6px; background:rgba(255,105,180,0.2);">📸 壁紙を今すぐ変える
                        (テスト用)</button>
                    <p style="font-size:10px; color:rgba(255,255,255,0.5); margin-top:8px;">※ wallpapers/
                        フォルダ内の画像が順に表示されます。</p>
                </div>

                <button class="m-close" onclick="closeSettings()">閉じる</button>
            </div>
        </div>

        <!-- PIN Modal -->
        <div id="pin-modal" class="modal-overlay" style="z-index:3000;">
            <div class="modal" style="text-align:center;">
                <div style="font-size:40px; margin-bottom:10px;">🔐</div>
                <h3 style="color:#fff; margin-bottom:8px;">Enter PIN</h3>
                <p style="font-size:12px; color:rgba(255,255,255,0.6); margin-bottom:16px;">プライバシー保護のためPINを入力してください</p>
                <input type="password" id="pin-in"
                    style="width:100%; padding:12px; border-radius:8px; border:1px solid rgba(255,105,180,0.5); background:rgba(0,0,0,0.5); color:#fff; text-align:center; letter-spacing:0.5em; margin-bottom:16px;"
                    maxlength="4" placeholder="••••">
                <button class="m-close" onclick="checkPIN()">Unlock</button>
            </div>
        </div>

        <!-- Release Notes Modal -->
        <div id="rel-notes-modal" class="modal-overlay">
            <div class="modal" style="max-width: 600px;">
                <div class="m-title">📝 Release Notes</div>
                <div id="rel-notes-body">読み込み中...</div>
                <button class="m-close"
                    onclick="document.getElementById('rel-notes-modal').classList.remove('open')">閉じる</button>
            </div>
        </div>

        <script>
            // 状態管理
            let conversations = JSON.parse(localStorage.getItem('sakura_convs') || '[]');
            let defaultCfg = { "theme": "dark", "fs": "14", "s_len": "normal", "s_tone": "polite", "wp_enabled": false, "wp_interval": 5 };
            let cfg = JSON.parse(localStorage.getItem('sakura_cfg') || JSON.stringify(defaultCfg));

            // 壁紙スライドショー管理用変数 (TDZ回避のため上部に移動)
            let wp_images = [];
            let wp_index = 0;
            let wp_timer = null;
            // 新規項目（壁紙等）が既存のcfgにない場合に備えて補完
            cfg = { ...defaultCfg, ...cfg };
            let currentConvId = null;
            let isLoading = false;
            let aiMode = 'standard';
            let useWeather = false;
            let aiModel = 'qwen3:8b';
            let usePrivacy = false;
            const APP_VERSION = '0.3'; // クライアント側のバージョン
            let attachedFiles = []; // { name, content }

            function applyCfg() {
                document.body.setAttribute('data-theme', cfg.theme);
                document.body.style.fontSize = cfg.fs + 'px';
                document.querySelectorAll('.thm-btn').forEach(b => b.classList.toggle('active', b.dataset.v === cfg.theme));
                document.querySelectorAll('.fs-btn').forEach(b => b.classList.toggle('active', b.dataset.v === cfg.fs));
                document.querySelectorAll('.sl-btn').forEach(b => b.classList.toggle('active', b.dataset.v === cfg.s_len));
                document.querySelectorAll('.st-btn').forEach(b => b.classList.toggle('active', b.dataset.v === cfg.s_tone));

                // 壁紙設定の反映
                const wpCheck = document.getElementById('wp-enabled-check');
                const wpInterval = document.getElementById('wp-interval-input');
                if (wpCheck) wpCheck.checked = !!cfg.wp_enabled;
                if (wpInterval) wpInterval.value = cfg.wp_interval || 5;

                if (typeof applyWallpaperCfg === 'function') applyWallpaperCfg();
            }
            applyCfg();

            function setThemeBtn(t) { cfg.theme = t; saveCfg(); }
            function setFSBtn(s) { cfg.fs = s; saveCfg(); }
            function setSLBtn(v) { cfg.s_len = v; saveCfg(); }
            function setSTBtn(v) { cfg.s_tone = v; saveCfg(); }

            function saveCfg() {
                // 壁紙設定も同期
                const wpCheck = document.getElementById('wp-enabled-check');
                const wpInterval = document.getElementById('wp-interval-input');
                if (wpCheck) cfg.wp_enabled = wpCheck.checked;
                if (wpInterval) cfg.wp_interval = wpInterval.value;

                localStorage.setItem('sakura_cfg', JSON.stringify(cfg));
                applyCfg();
            }

            function setMode(mode) { aiMode = mode; }
            function toggleWeather() { useWeather = !useWeather; document.getElementById('weather-btn').classList.toggle('active', useWeather); }
            function setModel(m) { aiModel = m; }
            function togglePrivacy() { usePrivacy = !usePrivacy; document.getElementById('privacy-btn').classList.toggle('active', usePrivacy); }

            function openSettings() { document.getElementById('settings-modal').classList.add('open'); }
            function closeSettings() { document.getElementById('settings-modal').classList.remove('open'); }
            function toggleExportMenu() {
                const m = document.getElementById('export-menu');
                m.style.display = m.style.display === 'none' ? 'block' : 'none';
            }

            // DOM
            const msgArea = document.getElementById('messages');
            const convList = document.getElementById('conv-list');
            const msgInput = document.getElementById('msg-input');
            const sendBtn = document.getElementById('send-btn');

            // 入力エリアの自動リサイズ
            function adjustInputHeight() {
                msgInput.style.height = 'auto';
                const newHeight = Math.min(msgInput.scrollHeight, 250); // 最大 250px まで
                msgInput.style.height = (newHeight > 60 ? newHeight : 60) + 'px'; // 最小 60px (約3行分)
            }
            msgInput.addEventListener('input', adjustInputHeight);

            // 初期高さ設定 (約3行分)
            msgInput.style.height = '60px';

            const fileInput = document.getElementById('file-input');
            const filePreviewArea = document.getElementById('file-preview-area');
            const inputWrapper = document.getElementById('input-wrapper');

            // ドラッグ＆ドロップイベント
            inputWrapper.addEventListener('dragover', (e) => {
                e.preventDefault();
                inputWrapper.classList.add('dragover');
            });
            inputWrapper.addEventListener('dragleave', () => {
                inputWrapper.classList.remove('dragover');
            });
            inputWrapper.addEventListener('drop', (e) => {
                e.preventDefault();
                inputWrapper.classList.remove('dragover');
                handleFileSelection(e.dataTransfer.files);
            });

            // ファイル選択時の処理
            fileInput.addEventListener('change', (e) => {
                handleFileSelection(e.target.files);
                fileInput.value = ''; // Reset for same file selection
            });

            async function handleFileSelection(filesList) {
                const files = Array.from(filesList);
                for (const file of files) {
                    if (attachedFiles.find(f => f.name === file.name)) continue;

                    try {
                        const content = await readFileContent(file);
                        attachedFiles.push({ name: file.name, content: content, type: file.type });
                        renderFilePreviews();
                    } catch (err) {
                        alert(`ファイルの読み込みに失敗しました: ${file.name}`);
                    }
                }
            }

            async function readFileContent(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    const ext = file.name.split('.').pop().toLowerCase();

                    if (ext === 'xlsx' || ext === 'xls') {
                        reader.onload = (e) => {
                            const data = new Uint8Array(e.target.result);
                            const workbook = XLSX.read(data, { type: 'array' });
                            let textContent = '';
                            workbook.SheetNames.forEach(sheetName => {
                                textContent += `--- Sheet: ${sheetName} ---\n`;
                                const csv = XLSX.utils.sheet_to_csv(workbook.Sheets[sheetName]);
                                textContent += csv + "\n\n";
                            });
                            resolve(textContent);
                        };
                        reader.readAsArrayBuffer(file);
                    } else {
                        reader.onload = (e) => resolve(e.target.result);
                        reader.readAsText(file);
                    }
                    reader.onerror = reject;
                });
            }

            function renderFilePreviews() {
                filePreviewArea.innerHTML = '';
                if (attachedFiles.length === 0) {
                    filePreviewArea.style.display = 'none';
                    return;
                }
                filePreviewArea.style.display = 'flex';
                attachedFiles.forEach((f, idx) => {
                    const token = document.createElement('div');
                    token.className = 'file-token';
                    token.innerHTML = `<span>📄 ${f.name}</span><span class="file-remove" onclick="removeFile(${idx})">✕</span>`;
                    filePreviewArea.appendChild(token);
                });

                const clearBtn = document.createElement('button');
                clearBtn.className = 'file-clear-btn';
                clearBtn.textContent = 'すべて解除';
                clearBtn.onclick = () => {
                    attachedFiles = [];
                    renderFilePreviews();
                };
                filePreviewArea.appendChild(clearBtn);
            }

            window.removeFile = (idx) => {
                attachedFiles.splice(idx, 1);
                renderFilePreviews();
            };

            const welcomeEl = document.getElementById('welcome');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            // メディアクエリ対応でデスクトップ用タイトル表示制御
            const mql = window.matchMedia('(min-width: 769px)');
            function handleMQ(e) {
                const dt = document.getElementById('desktop-title');
                const db = document.getElementById('desktop-badge');
                if (dt) dt.style.display = e.matches ? 'inline' : 'none';
                if (db) db.style.display = e.matches ? 'inline' : 'none';
            }
            mql.addEventListener('change', handleMQ);
            handleMQ(mql);

            function toggleSidebar() {
                sidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('open');
            }

            renderSidebar();

            // テキストエリア自動リサイズ
            msgInput.addEventListener('input', () => { msgInput.style.height = 'auto'; msgInput.style.height = Math.min(msgInput.scrollHeight, 140) + 'px'; });

            // Enter送信
            msgInput.addEventListener('keydown', (e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (!isLoading) sendMessage(); } });

            // サイドバー描画
            function renderSidebar(filter = '') {
                convList.innerHTML = '';
                const query = filter.toLowerCase();
                [...conversations].reverse().forEach(conv => {
                    const title = conv.title || conv.messages[0]?.text || '会話';
                    const contentText = conv.messages.map(m => m.text).join(' ').toLowerCase();

                    if (query && !title.toLowerCase().includes(query) && !contentText.includes(query)) {
                        return;
                    }

                    const item = document.createElement('div');
                    item.className = 'conv-item' + (conv.id === currentConvId ? ' active' : '');
                    item.dataset.id = conv.id;

                    const titleSpan = document.createElement('span');
                    titleSpan.className = 'conv-title-text';
                    titleSpan.textContent = title;
                    titleSpan.onclick = (e) => { e.stopPropagation(); loadConversation(conv.id); };

                    const delBtn = document.createElement('div');
                    delBtn.className = 'conv-delete-btn';
                    delBtn.innerHTML = '🗑';
                    delBtn.title = '削除';
                    delBtn.onclick = (e) => {
                        e.stopPropagation();
                        deleteConversation(conv.id);
                    };

                    item.appendChild(titleSpan);
                    item.appendChild(delBtn);
                    item.onclick = () => loadConversation(conv.id);
                    convList.appendChild(item);
                });
            }

            function filterConversations() {
                const query = document.getElementById('search-input').value;
                renderSidebar(query);
            }

            // 新しい会話
            function startNewChat() { currentConvId = null; msgArea.innerHTML = ''; msgArea.appendChild(welcomeEl); welcomeEl.style.display = 'flex'; renderSidebar(); }

            // 会話読み込み
            function loadConversation(id) { const conv = conversations.find(c => c.id === id); if (!conv) return; currentConvId = id; msgArea.innerHTML = ''; conv.messages.forEach(msg => { appendBubble(msg.text, msg.role); }); renderSidebar(); }

            // メッセージバブル追加
            function appendBubble(text, role, extraClass = '') {
                if (welcomeEl.parentNode) welcomeEl.style.display = 'none';
                const row = document.createElement('div');
                row.className = `msg-row ${role}${extraClass ? ' ' + extraClass : ''}`;
                const avatar = document.createElement('div');
                avatar.className = 'msg-avatar';
                avatar.textContent = role === 'user' ? '👤' : '🌸';

                const contentDiv = document.createElement('div');
                contentDiv.style.flex = '1';
                contentDiv.style.minWidth = '0';

                const bubble = document.createElement('div');
                bubble.className = 'msg-bubble';
                bubble.textContent = text;

                contentDiv.appendChild(bubble);

                if (role === 'ai') {
                    const acts = document.createElement('div');
                    acts.className = 'bubble-actions';
                    acts.innerHTML = `
                    <button class="b-act" onclick="retryChat()">↻ 再生成</button>
                    <button class="b-act" onclick="this.textContent='👍 済'; this.style.color='#ffb6d9'">👍</button>
                    <button class="b-act" onclick="this.textContent='👎 済'; this.style.color='#ffb6d9'">👎</button>
                    <button class="b-act" onclick="speakText(this.closest('div').parentElement.querySelector('.msg-bubble').textContent)">🔊 読上</button>
                `;
                    contentDiv.appendChild(acts);
                }

                row.appendChild(avatar);
                row.appendChild(contentDiv);
                msgArea.appendChild(row);
                msgArea.scrollTop = msgArea.scrollHeight;
                return bubble;
            }

            // 送信
            async function sendMessage(forceText = null) {
                const text = forceText || msgInput.value.trim();
                if (!text || isLoading) return;
                isLoading = true; sendBtn.disabled = true; document.getElementById('mic-btn').disabled = true;
                if (!forceText) { msgInput.value = ''; msgInput.style.height = 'auto'; }
                appendBubble(text, 'user');
                const thinkingBubble = appendBubble('', 'ai', 'thinking');
                thinkingBubble.innerHTML = '<div class="saku-dots"><div class="saku-dot"></div><div class="saku-dot"></div><div class="saku-dot"></div></div> <span style="font-size:12px; opacity:0.7;">さくらが考え中...</span>';
                const thinkingRow = thinkingBubble.closest('.msg-row');

                try {
                    const formData = new FormData();
                    let finalMsg = text;
                    if (attachedFiles.length > 0) {
                        let fileContext = "【添付ファイルの情報】\nユーザーから以下のファイルが提供されました。これらの内容を元に分析や回答を行ってください。\n\n";
                        attachedFiles.forEach(f => {
                            fileContext += `--- File: ${f.name} ---\n${f.content}\n\n`;
                        });
                        finalMsg = fileContext + "【ユーザーの質問】\n" + text;
                    }

                    formData.append('message', finalMsg);
                    formData.append('mode', aiMode);
                    formData.append('model', aiModel);
                    if (usePrivacy) formData.append('privacy_mode', '1');
                    if (useWeather) formData.append('use_weather', '1');

                    formData.append('style_len', cfg.s_len);
                    formData.append('style_tone', cfg.s_tone);

                    // 簡易学習：過去のユーザー発言から最近の関心を抽出しコンテキスト化
                    const userTopics = conversations.flatMap(c => c.messages.filter(m => m.role === 'user').map(m => m.text));
                    if (userTopics.length > 0) {
                        const recentTopics = [...new Set(userTopics)].slice(-5).join('、');
                        formData.append('system_context', 'ユーザーがよく話題にするキーワードや関心事: ' + recentTopics);
                    }

                    const res = await fetch('chat.php', { method: 'POST', body: formData });
                    if (!res.ok) throw new Error('サーバーエラー: ' + res.status);
                    const data = await res.json();
                    if (data.version && data.version !== APP_VERSION) {
                        console.warn(`Version mismatch: Client ${APP_VERSION} vs Server ${data.version}`);
                    }

                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const aiText = data.response || '(応答なし)';
                    thinkingRow.classList.remove('thinking');
                    thinkingBubble.textContent = aiText;
                    msgArea.scrollTop = msgArea.scrollHeight;
                    saveMessage(text, aiText);
                    attachedFiles = [];
                    renderFilePreviews();
                } catch (err) {
                    thinkingRow.classList.remove('thinking');
                    thinkingBubble.textContent = 'エラーが発生しました: ' + err.message;
                    alert('AIチャットエラー: ' + err.message);
                }
                isLoading = false; sendBtn.disabled = false; document.getElementById('mic-btn').disabled = false;
                msgInput.focus();
            }

            function retryChat() {
                if (conversations.length && currentConvId) {
                    const c = conversations.find(x => x.id === currentConvId);
                    if (c && c.messages.length >= 2) {
                        const lastUserMsg = c.messages[c.messages.length - 2].text;
                        msgInput.value = lastUserMsg;
                        sendMessage();
                    }
                }
            }

            let recognition = null;
            function toggleMic() {
                const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRec) {
                    return alert('現在お使いのブラウザは音声入力APIに対応していません。\n★iOS(iPhone/iPad)の場合、HTTPS(暗号化)通信でのアクセスが必須です。');
                }
                if (isLoading) return;
                const btn = document.getElementById('mic-btn');

                // 録音中なら停止
                if (recognition) {
                    recognition.stop();
                    recognition = null;
                    btn.classList.remove('active');
                    return;
                }

                recognition = new SpeechRec();
                recognition.lang = 'ja-JP';
                recognition.interimResults = true;   // iOS対策: trueに変更
                recognition.continuous = false;
                recognition.maxAlternatives = 1;

                recognition.onstart = () => {
                    btn.classList.add('active');
                };
                recognition.onend = () => {
                    btn.classList.remove('active');
                    recognition = null;
                };
                recognition.onerror = (event) => {
                    console.error('Speech recognition error', event.error);
                    let msg = '音声入力エラー: ' + event.error;
                    if (event.error === 'not-allowed') {
                        msg += '\n\nマイクの使用が許可されていません。\niOSの場合：設定 > Safari > マイク をオンにしてください。';
                    } else if (event.error === 'no-speech') {
                        msg += '\n\n音声が検出されませんでした。もう一度お試しください。';
                    } else if (event.error === 'network') {
                        msg += '\n\nネットワークエラーです。HTTPS接続で再試行してください。';
                    }
                    alert(msg);
                    btn.classList.remove('active');
                    recognition = null;
                };
                recognition.onresult = (e) => {
                    let finalTranscript = '';
                    // iOS対策: isFinalなものだけを集める
                    for (let i = e.resultIndex; i < e.results.length; i++) {
                        if (e.results[i].isFinal) {
                            finalTranscript += e.results[i][0].transcript;
                        }
                    }
                    if (finalTranscript) {
                        msgInput.value += finalTranscript;
                        adjustInputHeight();
                        msgInput.focus();
                    }
                };

                // try-catchをstart()のみに絞り、エラーを確実に捕捉
                try {
                    recognition.start();
                } catch (e) {
                    console.error('マイク起動失敗:', e);
                    alert('マイクの起動に失敗しました: ' + e.message + '\nHTTPS接続か確認してください。');
                    recognition = null;
                    btn.classList.remove('active');
                }
            }

            function speakText(text) {
                const u = new SpeechSynthesisUtterance(text);
                u.lang = 'ja-JP';
                u.rate = 1.05;
                speechSynthesis.speak(u);
            }

            // 保存
            function saveMessage(userText, aiText) {
                if (!currentConvId) { currentConvId = Date.now().toString(); conversations.push({ id: currentConvId, title: userText.slice(0, 30), messages: [] }); }
                const conv = conversations.find(c => c.id === currentConvId);
                if (!conv) return;
                conv.messages.push({ role: 'user', text: userText });
                conv.messages.push({ role: 'ai', text: aiText });

                localStorage.setItem('sakura_convs', JSON.stringify(conversations));
                renderSidebar();
            }

            function clearAll() {
                if (confirm('全データを削除しますか？(サーバー同期ファイルも初期化されます)')) {
                    conversations = [];
                    localStorage.clear();
                    // サーバー側も「上書き」で空にする
                    fetch('chat_sync.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'overwrite', data: [] })
                    }).then(() => location.reload());
                }
            }

            async function deleteConversation(id) {
                if (!confirm('この会話を削除しますか？')) return;

                const idx = conversations.findIndex(c => c.id === id);
                if (idx === -1) return;

                conversations.splice(idx, 1);
                localStorage.setItem('sakura_convs', JSON.stringify(conversations));

                if (currentConvId === id) {
                    startNewChat();
                } else {
                    renderSidebar();
                }

                // サーバー側にも明示的に「削除後」の状態を上書き同期
                try {
                    await fetch('chat_sync.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'overwrite', data: conversations })
                    });
                } catch (e) {
                    console.error('削除のサーバー同期に失敗しました', e);
                }
            }

            // 手動クラウド同期 (マージ)
            async function syncCloud() {
                try {
                    const btn = document.querySelector('button[title="クラウド同期 (手動マージ)"]');
                    btn.style.opacity = '0.5';
                    const res = await fetch('chat_sync.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(conversations)
                    });
                    const data = await res.json();
                    if (data.status === 'ok' && data.merged) {
                        conversations = data.merged;
                        localStorage.setItem('sakura_convs', JSON.stringify(conversations));
                        renderSidebar();
                        if (currentConvId) loadConversation(currentConvId);
                        alert(`クラウド同期が完了しました (全${conversations.length}件)`);
                    } else if (data.status === 'ok') {
                        alert('クラウド同期は完了しましたが、更新データはありませんでした');
                    } else {
                        alert('同期エラー: ' + (data.message || '不明なエラーが発生しました'));
                    }
                } catch (e) {
                    alert('同期に失敗しました: ' + e.message);
                } finally {
                    document.querySelector('button[title="クラウド同期 (手動マージ)"]').style.opacity = '1';
                }
            }

            // エクスポート
            function exportChat(type) {
                document.getElementById('export-menu').style.display = 'none';
                const c = conversations.find(x => x.id === currentConvId); if (!c) return alert('会話を選択してください');
                let content = '', mime = 'text/plain', ext = type;
                if (type === 'json') { content = JSON.stringify(c, null, 2); mime = 'application/json'; }
                else if (type === 'md') { content = `# ${c.title}\n\n` + c.messages.map(m => `### ${m.role === 'user' ? 'ユーザー' : 'AI'}\n${m.text}\n`).join('\n'); }
                else if (type === 'txt') { content = c.messages.map(m => `[${m.role.toUpperCase()}]\n${m.text}\n`).join('\n'); }
                else if (type === 'html') { return generateShareHTML(c); }

                const b = new Blob([content], { type: mime });
                const a = document.createElement('a'); a.href = URL.createObjectURL(b); a.download = `chat_${currentConvId}.${ext}`; a.click();
            }

            function generateShareHTML(c) {
                const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>${c.title}</title><style>body{font-family:sans-serif;background:#1a0010;color:#fff;padding:20px;line-height:1.6;}.m{margin-bottom:20px;padding:15px;border-radius:10px;background:rgba(255,255,255,0.05);}.u{border-left:4px solid #e6007e;}.a{border-left:4px solid #6600cc;}.r{font-weight:bold;margin-bottom:5px;color:#ffb6d9;}</style></head><body><h1>${c.title}</h1>${c.messages.map(m => `<div class="m ${m.role === 'user' ? 'u' : 'a'}"><div class="r">${m.role.toUpperCase()}</div>${m.text}</div>`).join('')}</body></html>`;
                const b = new Blob([html], { type: 'text/html' });
                const a = document.createElement('a'); a.href = URL.createObjectURL(b); a.download = `share_${currentConvId}.html`; a.click();
            }

            // PINロック
            function setPIN() {
                const p = prompt('新しい4桁のPINを入力してください(数字のみ)');
                if (p && p.length === 4) { localStorage.setItem('sakura_pin', p); alert('PINを設定しました'); closeSettings(); }
            }
            function checkPIN() {
                const target = localStorage.getItem('sakura_pin');
                if (document.getElementById('pin-in').value === target) { document.getElementById('pin-modal').classList.remove('open'); } else { alert('PINが違います'); document.getElementById('pin-in').value = ''; }
            }
            if (localStorage.getItem('sakura_pin')) { document.getElementById('pin-modal').classList.add('open'); }

            // ピンロック設定・チェック用などのロジック...

            function applyWallpaperCfg() {
                console.info('壁紙設定を適用中... 有効:', cfg.wp_enabled, '間隔:', cfg.wp_interval);
                const skipBtn = document.getElementById('wp-skip-btn');
                if (cfg.wp_enabled) {
                    document.body.classList.add('has-wallpaper');
                    if (skipBtn) skipBtn.style.display = 'inline-block';
                    fetchWallpapers();
                } else {
                    document.body.classList.remove('has-wallpaper');
                    if (skipBtn) skipBtn.style.display = 'none';
                    const bg = document.getElementById('wallpaper-bg');
                    if (bg) bg.style.opacity = 0;
                    if (wp_timer) clearInterval(wp_timer);
                }
            }

            async function fetchWallpapers() {
                console.log('get_wallpapers.php からリストを取得します...');
                try {
                    const res = await fetch('get_wallpapers.php');
                    const data = await res.json();
                    if (data.status === 'ok' && data.images && data.images.length > 0) {
                        wp_images = data.images;
                        console.info(`壁紙を${wp_images.length}枚認識しました。`, wp_images);
                        startWallpaperCycle();
                    } else {
                        console.error('壁紙リストが空、または取得に失敗しました。', data);
                    }
                } catch (e) {
                    console.error('壁紙の fetch エラー:', e);
                }
            }

            function startWallpaperCycle() {
                if (wp_timer) clearInterval(wp_timer);
                changeWallpaper();
                const interval = (parseInt(cfg.wp_interval) || 5) * 60000;
                wp_timer = setInterval(changeWallpaper, interval);
            }

            function skipWallpaper() {
                console.info('手動で次へ切り替えます');
                startWallpaperCycle(); // タイマーリセットを兼ねて再起動
            }

            function changeWallpaper() {
                if (wp_images.length === 0) return;
                const bg = document.getElementById('wallpaper-bg');
                if (!bg) return;

                const rawUrl = wp_images[wp_index];
                const encodedUrl = encodeURI(rawUrl);
                const cssUrl = encodedUrl.replace(/'/g, "\\'");

                console.info(`壁紙切り替え: [${wp_index}] ${rawUrl}`);

                const tempImg = new Image();
                tempImg.src = encodedUrl;
                tempImg.onload = () => {
                    console.log(`画像ロード成功: ${rawUrl}`);
                    bg.style.opacity = 0;
                    setTimeout(() => {
                        bg.style.backgroundImage = `url('${cssUrl}')`;
                        bg.style.opacity = 1;
                    }, 500);
                };
                tempImg.onerror = () => {
                    console.error(`画像ロード失敗: ${rawUrl}`);
                };

                // インデックスを次へ（失敗しても次は別の画像にする）
                wp_index = (wp_index + 1) % wp_images.length;
            }

            // リリースノート表示
            async function showReleaseNotes() {
                const modal = document.getElementById('rel-notes-modal');
                const body = document.getElementById('rel-notes-body');
                modal.classList.add('open');
                body.innerText = '読み込み中...';
                try {
                    const res = await fetch('RELEASE_NOTES.md?v=' + Date.now());
                    if (!res.ok) throw new Error('取得に失敗しました');
                    const text = await res.text();
                    body.innerText = text;
                } catch (e) {
                    body.innerText = 'リリースノートの読み込みに失敗しました。';
                }
            }
        </script>

        <!-- Settings Modal -->
        <div id="settings-modal" class="modal-overlay">
            <div class="modal">
                <div class="m-title">⚙ Settings</div>

                <div class="m-group">
                    <div class="m-label">テーマ</div>
                    <div class="m-btns">
                        <button class="m-btn thm-btn" data-v="dark" onclick="setThemeBtn('dark')">Dark (桜)</button>
                        <button class="m-btn thm-btn" data-v="light" onclick="setThemeBtn('light')">Light</button>
                        <button class="m-btn thm-btn" data-v="purple" onclick="setThemeBtn('purple')">Purple</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">フォントサイズ</div>
                    <div class="m-btns">
                        <button class="m-btn fs-btn" data-v="12" onclick="setFSBtn('12')">小</button>
                        <button class="m-btn fs-btn" data-v="14" onclick="setFSBtn('14')">中</button>
                        <button class="m-btn fs-btn" data-v="16" onclick="setFSBtn('16')">大</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">回答の長さ</div>
                    <div class="m-btns">
                        <button class="m-btn sl-btn" data-v="short" onclick="setSLBtn('short')">短文 (簡潔)</button>
                        <button class="m-btn sl-btn" data-v="normal" onclick="setSLBtn('normal')">通常</button>
                        <button class="m-btn sl-btn" data-v="long" onclick="setSLBtn('long')">長文 (詳細)</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">回答の口調</div>
                    <div class="m-btns">
                        <button class="m-btn st-btn" data-v="polite" onclick="setSTBtn('polite')">丁寧・敬語</button>
                        <button class="m-btn st-btn" data-v="frank" onclick="setSTBtn('frank')">フランク (タメ口)</button>
                    </div>
                </div>

                <div class="m-group">
                    <div class="m-label">セキュリティ</div>
                    <button class="m-btn" onclick="setPIN()" style="width:100%;">🔐 PINロック設定</button>
                </div>

                <div class="m-group" style="border-top:1px solid rgba(255,105,180,0.2); padding-top:15px;">
                    <div class="m-label" style="color:#ffb6d9; font-weight:bold;">🖼 壁紙スライドショー (v0.20)</div>
                    <label
                        style="display:flex; align-items:center; gap:10px; font-size:13px; cursor:pointer; margin-bottom:10px;">
                        <input type="checkbox" id="wp-enabled-check" onchange="saveCfg()"> 有効にする
                    </label>
                    <div
                        style="display:flex; align-items:center; gap:10px; font-size:12px; color:rgba(255,255,255,0.7); margin-bottom:10px;">
                        切り替え間隔: <input type="number" id="wp-interval-input"
                            style="width:50px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,105,180,0.3); color:#fff; border-radius:4px; padding:2px 5px;"
                            min="1" onchange="saveCfg()"> 分
                    </div>
                    <button class="m-btn" onclick="changeWallpaper()"
                        style="width:100%; font-size:11px; padding:6px; background:rgba(255,105,180,0.2); margin-bottom:10px;">📸
                        壁紙を今すぐ変える (テスト用)</button>
                    <p style="font-size:10px; color:rgba(255,255,255,0.5); margin-top:8px;">※ wallpapers/
                        フォルダ内の画像が順に表示されます。</p>
                </div>

                <button class="m-close" onclick="closeSettings()">閉じる</button>
            </div>
        </div>

        <!-- PIN Modal -->
        <div id="pin-modal" class="modal-overlay" style="z-index:3000;">
            <div class="modal" style="text-align:center;">
                <div style="font-size:40px; margin-bottom:10px;">🔐</div>
                <h3 style="color:#fff; margin-bottom:8px;">Enter PIN</h3>
                <p style="font-size:12px; color:rgba(255,255,255,0.6); margin-bottom:16px;">プライバシー保護のためPINを入力してください</p>
                <input type="password" id="pin-in"
                    style="width:100%; padding:12px; border-radius:8px; border:1px solid rgba(255,105,180,0.5); background:rgba(0,0,0,0.5); color:#fff; text-align:center; letter-spacing:0.5em; margin-bottom:16px;"
                    maxlength="4" placeholder="••••">
                <button class="m-close" onclick="checkPIN()">Unlock</button>
            </div>
        </div>
</body>

</html>