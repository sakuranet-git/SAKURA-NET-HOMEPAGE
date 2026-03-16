<?php
/**
 * SAKURA Groupware API v1.0
 * JSONファイルベースのバックエンドAPI
 * 
 * エンドポイント:
 *   POST ?module=board    - 掲示板 CRUD
 *   POST ?module=todo     - ToDo CRUD
 *   POST ?module=schedule - スケジュール CRUD
 *   POST ?module=chat     - チャット送受信
 *   POST ?module=user     - ユーザー情報
 */

session_start();

// CORS & Content-Type
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

// 認証チェック (SAKURA_AUTH クッキーの確認)
if (!isset($_COOKIE['SAKURA_AUTH']) || $_COOKIE['SAKURA_AUTH'] !== 'verified') {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        http_response_code(401);
        echo json_encode(['error' => '認証が必要です。ポータルからログインしてください。']);
        exit;
    }
}

// データディレクトリ
$DATA_DIR = __DIR__ . '/gw_data';
if (!is_dir($DATA_DIR)) {
    mkdir($DATA_DIR, 0755, true);
}

// ユーザーID取得
$currentUser = $_SESSION['user_id'] ?? 'anonymous';
$currentUserName = $_SESSION['user_name'] ?? $currentUser;

// リクエスト解析
$module = $_GET['module'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? 'list';

// ==================================================================
// ヘルパー関数
// ==================================================================
function loadData($file) {
    global $DATA_DIR;
    $path = $DATA_DIR . '/' . $file;
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveData($file, $data) {
    global $DATA_DIR;
    $path = $DATA_DIR . '/' . $file;
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function generateId() {
    return uniqid('', true);
}

function now() {
    return date('Y-m-d H:i:s');
}

function ok($data = []) {
    echo json_encode(array_merge(['status' => 'ok'], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

function err($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

// ==================================================================
// モジュール別処理
// ==================================================================
switch ($module) {

    // ──────────────────────────────────────────────
    // 掲示板 (Board)
    // ──────────────────────────────────────────────
    case 'board':
        $posts = loadData('board.json');

        switch ($action) {
            case 'list':
                // カテゴリフィルタ
                $category = $input['category'] ?? null;
                $filtered = $posts;
                if ($category && $category !== 'all') {
                    $filtered = array_values(array_filter($posts, fn($p) => ($p['category'] ?? '') === $category));
                }
                // 新着順
                usort($filtered, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
                ok(['posts' => $filtered]);

            case 'create':
                $title = trim($input['title'] ?? '');
                $body = trim($input['body'] ?? '');
                $category = $input['category'] ?? 'general';
                $important = $input['important'] ?? false;
                if (!$title) err('タイトルは必須です');

                $post = [
                    'id' => generateId(),
                    'title' => $title,
                    'body' => $body,
                    'category' => $category,
                    'important' => $important,
                    'author' => $currentUser,
                    'author_name' => $currentUserName,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'comments' => [],
                    'read_by' => [$currentUser]
                ];
                $posts[] = $post;
                saveData('board.json', $posts);
                ok(['post' => $post]);

            case 'update':
                $id = $input['id'] ?? '';
                foreach ($posts as &$p) {
                    if ($p['id'] === $id) {
                        if (isset($input['title'])) $p['title'] = $input['title'];
                        if (isset($input['body'])) $p['body'] = $input['body'];
                        if (isset($input['category'])) $p['category'] = $input['category'];
                        if (isset($input['important'])) $p['important'] = $input['important'];
                        $p['updated_at'] = now();
                        saveData('board.json', $posts);
                        ok(['post' => $p]);
                    }
                }
                err('投稿が見つかりません', 404);

            case 'delete':
                $id = $input['id'] ?? '';
                $posts = array_values(array_filter($posts, fn($p) => $p['id'] !== $id));
                saveData('board.json', $posts);
                ok();

            case 'comment':
                $id = $input['id'] ?? '';
                $text = trim($input['text'] ?? '');
                if (!$text) err('コメントは空にできません');
                foreach ($posts as &$p) {
                    if ($p['id'] === $id) {
                        $p['comments'][] = [
                            'id' => generateId(),
                            'text' => $text,
                            'author' => $currentUser,
                            'author_name' => $currentUserName,
                            'created_at' => now()
                        ];
                        saveData('board.json', $posts);

                        // @メンション検出と通知送信
                        preg_match_all('/@(\w+)/', $text, $mentions);
                        if (!empty($mentions[1])) {
                            $users = loadData('users.json');
                            $notifications = loadData('notifications.json');
                            foreach ($mentions[1] as $mentionedName) {
                                foreach ($users as $user) {
                                    if (strtolower($user['name']) === strtolower($mentionedName) && $user['id'] !== $currentUser) {
                                        $notifications[] = [
                                            'id' => generateId(),
                                            'user_id' => $user['id'],
                                            'type' => 'mention',
                                            'title' => '掲示板でメンションされました',
                                            'message' => $currentUserName . ' が「' . $p['title'] . '」のコメントであなたをメンションしました',
                                            'link' => '#board',
                                            'read' => false,
                                            'created_at' => now()
                                        ];
                                    }
                                }
                            }
                            saveData('notifications.json', $notifications);
                        }

                        ok(['post' => $p]);
                    }
                }
                err('投稿が見つかりません', 404);

            case 'mark_read':
                $id = $input['id'] ?? '';
                foreach ($posts as &$p) {
                    if ($p['id'] === $id) {
                        if (!in_array($currentUser, $p['read_by'] ?? [])) {
                            $p['read_by'][] = $currentUser;
                        }
                        saveData('board.json', $posts);
                        ok();
                    }
                }
                err('投稿が見つかりません', 404);
        }
        break;

    // ──────────────────────────────────────────────
    // ToDo / タスク管理
    // ──────────────────────────────────────────────
    case 'todo':
        $todos = loadData('todos.json');

        switch ($action) {
            case 'list':
                $filter = $input['filter'] ?? 'all'; // all, mine, assigned
                $filtered = $todos;
                if ($filter === 'mine') {
                    $filtered = array_values(array_filter($todos, fn($t) => $t['creator'] === $currentUser));
                } elseif ($filter === 'assigned') {
                    $filtered = array_values(array_filter($todos, fn($t) => in_array($currentUser, $t['assignees'] ?? [])));
                }
                usort($filtered, function($a, $b) {
                    $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
                    $pa = $priorityOrder[$a['priority'] ?? 'medium'] ?? 1;
                    $pb = $priorityOrder[$b['priority'] ?? 'medium'] ?? 1;
                    if ($pa !== $pb) return $pa - $pb;
                    return strtotime($a['due_date'] ?? '2099-12-31') - strtotime($b['due_date'] ?? '2099-12-31');
                });
                ok(['todos' => $filtered]);

            case 'create':
                $title = trim($input['title'] ?? '');
                if (!$title) err('タイトルは必須です');
                $todo = [
                    'id' => generateId(),
                    'title' => $title,
                    'description' => $input['description'] ?? '',
                    'status' => 'open',
                    'priority' => $input['priority'] ?? 'medium',
                    'due_date' => $input['due_date'] ?? null,
                    'assignees' => $input['assignees'] ?? [$currentUser],
                    'creator' => $currentUser,
                    'creator_name' => $currentUserName,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'comments' => []
                ];
                $todos[] = $todo;
                saveData('todos.json', $todos);
                ok(['todo' => $todo]);

            case 'update':
                $id = $input['id'] ?? '';
                foreach ($todos as &$t) {
                    if ($t['id'] === $id) {
                        if (isset($input['title'])) $t['title'] = $input['title'];
                        if (isset($input['description'])) $t['description'] = $input['description'];
                        if (isset($input['status'])) $t['status'] = $input['status'];
                        if (isset($input['priority'])) $t['priority'] = $input['priority'];
                        if (isset($input['due_date'])) $t['due_date'] = $input['due_date'];
                        if (isset($input['assignees'])) $t['assignees'] = $input['assignees'];
                        $t['updated_at'] = now();
                        saveData('todos.json', $todos);
                        ok(['todo' => $t]);
                    }
                }
                err('タスクが見つかりません', 404);

            case 'delete':
                $id = $input['id'] ?? '';
                $todos = array_values(array_filter($todos, fn($t) => $t['id'] !== $id));
                saveData('todos.json', $todos);
                ok();

            case 'comment':
                $id = $input['id'] ?? '';
                $text = trim($input['text'] ?? '');
                if (!$text) err('コメントは空にできません');
                foreach ($todos as &$t) {
                    if ($t['id'] === $id) {
                        $t['comments'][] = [
                            'id' => generateId(),
                            'text' => $text,
                            'author' => $currentUser,
                            'author_name' => $currentUserName,
                            'created_at' => now()
                        ];
                        saveData('todos.json', $todos);
                        ok(['todo' => $t]);
                    }
                }
                err('タスクが見つかりません', 404);
        }
        break;

    // ──────────────────────────────────────────────
    // スケジュール
    // ──────────────────────────────────────────────
    case 'schedule':
        $events = loadData('schedule.json');

        switch ($action) {
            case 'list':
                $start = $input['start'] ?? date('Y-m-01');
                $end = $input['end'] ?? date('Y-m-t');
                $filtered = array_values(array_filter($events, function($e) use ($start, $end) {
                    return $e['start_date'] <= $end && ($e['end_date'] ?? $e['start_date']) >= $start;
                }));
                usort($filtered, fn($a, $b) => strcmp($a['start_date'] . ' ' . ($a['start_time'] ?? '00:00'), $b['start_date'] . ' ' . ($b['start_time'] ?? '00:00')));
                ok(['events' => $filtered]);

            case 'create':
                $title = trim($input['title'] ?? '');
                if (!$title) err('タイトルは必須です');
                $event = [
                    'id' => generateId(),
                    'title' => $title,
                    'description' => $input['description'] ?? '',
                    'start_date' => $input['start_date'] ?? date('Y-m-d'),
                    'end_date' => $input['end_date'] ?? $input['start_date'] ?? date('Y-m-d'),
                    'start_time' => $input['start_time'] ?? null,
                    'end_time' => $input['end_time'] ?? null,
                    'all_day' => $input['all_day'] ?? false,
                    'color' => $input['color'] ?? '#e91e63',
                    'participants' => $input['participants'] ?? [$currentUser],
                    'creator' => $currentUser,
                    'creator_name' => $currentUserName,
                    'created_at' => now(),
                    'google_event_id' => null,
                    'location' => $input['location'] ?? ''
                ];
                $events[] = $event;
                saveData('schedule.json', $events);
                ok(['event' => $event]);

            case 'update':
                $id = $input['id'] ?? '';
                foreach ($events as &$e) {
                    if ($e['id'] === $id) {
                        foreach (['title','description','start_date','end_date','start_time','end_time','all_day','color','participants','location'] as $key) {
                            if (isset($input[$key])) $e[$key] = $input[$key];
                        }
                        saveData('schedule.json', $events);
                        ok(['event' => $e]);
                    }
                }
                err('予定が見つかりません', 404);

            case 'delete':
                $id = $input['id'] ?? '';
                $events = array_values(array_filter($events, fn($e) => $e['id'] !== $id));
                saveData('schedule.json', $events);
                ok();
        }
        break;

    // ──────────────────────────────────────────────
    // チャット
    // ──────────────────────────────────────────────
    case 'chat':
        $channels = loadData('chat_channels.json');

        switch ($action) {
            case 'channels':
                if (empty($channels)) {
                    // デフォルトチャンネル作成
                    $channels = [
                        ['id' => 'general', 'name' => '全体', 'icon' => '💬', 'created_at' => now()],
                        ['id' => 'random', 'name' => '雑談', 'icon' => '🎲', 'created_at' => now()],
                        ['id' => 'work', 'name' => '業務連絡', 'icon' => '💼', 'created_at' => now()],
                    ];
                    saveData('chat_channels.json', $channels);
                }
                ok(['channels' => $channels]);

            case 'create_channel':
                $name = trim($input['name'] ?? '');
                $icon = $input['icon'] ?? '💬';
                if (!$name) err('チャンネル名は必須です');
                $ch = ['id' => generateId(), 'name' => $name, 'icon' => $icon, 'created_at' => now()];
                $channels[] = $ch;
                saveData('chat_channels.json', $channels);
                ok(['channel' => $ch]);

            case 'messages':
                $channelId = $input['channel_id'] ?? 'general';
                $messages = loadData("chat_{$channelId}.json");
                // 最新100件のみ返す
                $messages = array_slice($messages, -100);
                ok(['messages' => $messages]);

            case 'send':
                $channelId = $input['channel_id'] ?? 'general';
                $text = trim($input['text'] ?? '');
                $parentId = $input['parent_id'] ?? null; // スレッド返信用
                if (!$text) err('メッセージは空にできません');
                $messages = loadData("chat_{$channelId}.json");
                $msg = [
                    'id' => generateId(),
                    'text' => $text,
                    'author' => $currentUser,
                    'author_name' => $currentUserName,
                    'created_at' => now(),
                    'reactions' => [],
                    'parent_id' => $parentId,
                    'replies' => []
                ];
                $messages[] = $msg;
                saveData("chat_{$channelId}.json", $messages);

                // @メンション検出と通知送信
                preg_match_all('/@(\w+)/', $text, $mentions);
                if (!empty($mentions[1])) {
                    $users = loadData('users.json');
                    $notifications = loadData('notifications.json');
                    foreach ($mentions[1] as $mentionedName) {
                        // ユーザー名からユーザーIDを検索
                        foreach ($users as $user) {
                            if (strtolower($user['name']) === strtolower($mentionedName) && $user['id'] !== $currentUser) {
                                $notifications[] = [
                                    'id' => generateId(),
                                    'user_id' => $user['id'],
                                    'type' => 'mention',
                                    'title' => 'チャットでメンションされました',
                                    'message' => $currentUserName . ' があなたをメンションしました: ' . mb_substr($text, 0, 50),
                                    'link' => '#chat',
                                    'read' => false,
                                    'created_at' => now()
                                ];
                            }
                        }
                    }
                    saveData('notifications.json', $notifications);
                }

                ok(['message' => $msg]);

            case 'react':
                $channelId = $input['channel_id'] ?? 'general';
                $msgId = $input['message_id'] ?? '';
                $emoji = $input['emoji'] ?? '👍';
                $messages = loadData("chat_{$channelId}.json");
                foreach ($messages as &$m) {
                    if ($m['id'] === $msgId) {
                        if (!isset($m['reactions'])) $m['reactions'] = [];
                        $key = $currentUser . ':' . $emoji;
                        $exists = false;
                        foreach ($m['reactions'] as $i => $r) {
                            if ($r['user'] === $currentUser && $r['emoji'] === $emoji) {
                                array_splice($m['reactions'], $i, 1);
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $m['reactions'][] = ['user' => $currentUser, 'emoji' => $emoji];
                        }
                        saveData("chat_{$channelId}.json", $messages);
                        ok(['message' => $m]);
                    }
                }
                err('メッセージが見つかりません', 404);
        }
        break;

    // ──────────────────────────────────────────────
    // ユーザー管理
    // ──────────────────────────────────────────────
    case 'user':
        $users = loadData('users.json');

        switch ($action) {
            case 'list':
                ok(['users' => $users]);

            case 'me':
                ok(['user' => ['id' => $currentUser, 'name' => $currentUserName]]);

            case 'update_profile':
                $name = trim($input['name'] ?? '');
                if ($name) {
                    $_SESSION['user_name'] = $name;
                    $found = false;
                    foreach ($users as &$u) {
                        if ($u['id'] === $currentUser) {
                            $u['name'] = $name;
                            $u['updated_at'] = now();
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $users[] = ['id' => $currentUser, 'name' => $name, 'created_at' => now(), 'updated_at' => now()];
                    }
                    saveData('users.json', $users);
                    ok(['user' => ['id' => $currentUser, 'name' => $name]]);
                }
                err('名前は必須です');

            case 'register':
                $name = trim($input['name'] ?? '');
                if (!$name) err('名前は必須です');
                $exists = false;
                foreach ($users as $u) {
                    if ($u['id'] === $currentUser) { $exists = true; break; }
                }
                if (!$exists) {
                    // 最初のユーザーは自動的に管理者に
                    $role = count($users) === 0 ? 'admin' : 'user';
                    $users[] = [
                        'id' => $currentUser,
                        'name' => $name,
                        'role' => $role,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = $role;
                    saveData('users.json', $users);
                }
                ok();

            case 'set_role':
                // 管理者のみ実行可能
                $userRole = $_SESSION['user_role'] ?? 'user';
                if ($userRole !== 'admin') {
                    err('管理者権限が必要です', 403);
                }

                $targetUserId = $input['user_id'] ?? '';
                $newRole = $input['role'] ?? 'user'; // admin or user

                if (!in_array($newRole, ['admin', 'user'])) {
                    err('無効なロールです');
                }

                foreach ($users as &$u) {
                    if ($u['id'] === $targetUserId) {
                        $u['role'] = $newRole;
                        $u['updated_at'] = now();
                        saveData('users.json', $users);
                        ok(['user' => $u]);
                    }
                }
                err('ユーザーが見つかりません', 404);
        }
        break;

    // ──────────────────────────────────────────────
    // 通知システム
    // ──────────────────────────────────────────────
    case 'notifications':
        $notifications = loadData('notifications.json');

        switch ($action) {
            case 'list':
                // ユーザーの通知を取得（最新50件）
                $userNotifs = array_values(array_filter($notifications, fn($n) => $n['user_id'] === $currentUser));
                usort($userNotifs, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
                $userNotifs = array_slice($userNotifs, 0, 50);
                ok(['notifications' => $userNotifs]);

            case 'create':
                $targetUser = $input['user_id'] ?? '';
                $type = $input['type'] ?? 'info'; // info, success, warning, mention
                $title = $input['title'] ?? '';
                $message = $input['message'] ?? '';
                $link = $input['link'] ?? null;

                if (!$targetUser || !$message) err('必要な情報が不足しています');

                $notif = [
                    'id' => generateId(),
                    'user_id' => $targetUser,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'link' => $link,
                    'read' => false,
                    'created_at' => now()
                ];

                $notifications[] = $notif;
                saveData('notifications.json', $notifications);
                ok(['notification' => $notif]);

            case 'mark_read':
                $id = $input['id'] ?? '';
                foreach ($notifications as &$n) {
                    if ($n['id'] === $id && $n['user_id'] === $currentUser) {
                        $n['read'] = true;
                        saveData('notifications.json', $notifications);
                        ok();
                    }
                }
                err('通知が見つかりません', 404);

            case 'mark_all_read':
                $updated = 0;
                foreach ($notifications as &$n) {
                    if ($n['user_id'] === $currentUser && !$n['read']) {
                        $n['read'] = true;
                        $updated++;
                    }
                }
                saveData('notifications.json', $notifications);
                ok(['updated' => $updated]);

            case 'delete':
                $id = $input['id'] ?? '';
                $notifications = array_values(array_filter($notifications, fn($n) => !($n['id'] === $id && $n['user_id'] === $currentUser)));
                saveData('notifications.json', $notifications);
                ok();

            case 'count_unread':
                $unread = count(array_filter($notifications, fn($n) => $n['user_id'] === $currentUser && !$n['read']));
                ok(['count' => $unread]);
        }
        break;

    // ──────────────────────────────────────────────
    // 検索
    // ──────────────────────────────────────────────
    case 'search':
        $query = trim($input['query'] ?? '');
        $category = $input['category'] ?? 'all'; // all, board, todo, schedule, chat, files

        if (!$query) {
            ok(['results' => []]);
        }

        $results = [];

        // 掲示板を検索
        if ($category === 'all' || $category === 'board') {
            $posts = loadData('board.json');
            foreach ($posts as $post) {
                if (stripos($post['title'], $query) !== false || stripos($post['body'], $query) !== false) {
                    $results[] = [
                        'type' => 'board',
                        'id' => $post['id'],
                        'title' => $post['title'],
                        'content' => mb_substr($post['body'], 0, 100) . '...',
                        'created_at' => $post['created_at'],
                        'icon' => '📝'
                    ];
                }
            }
        }

        // タスクを検索
        if ($category === 'all' || $category === 'todo') {
            $todos = loadData('todos.json');
            foreach ($todos as $todo) {
                if (stripos($todo['title'], $query) !== false || stripos($todo['description'] ?? '', $query) !== false) {
                    $results[] = [
                        'type' => 'todo',
                        'id' => $todo['id'],
                        'title' => $todo['title'],
                        'content' => $todo['description'] ?? '',
                        'created_at' => $todo['created_at'],
                        'icon' => '✅'
                    ];
                }
            }
        }

        // スケジュールを検索
        if ($category === 'all' || $category === 'schedule') {
            $events = loadData('schedule.json');
            foreach ($events as $event) {
                if (stripos($event['title'], $query) !== false || stripos($event['description'] ?? '', $query) !== false) {
                    $results[] = [
                        'type' => 'schedule',
                        'id' => $event['id'],
                        'title' => $event['title'],
                        'content' => $event['description'] ?? '',
                        'created_at' => $event['created_at'],
                        'date' => $event['start_date'],
                        'icon' => '📅'
                    ];
                }
            }
        }

        // チャットを検索
        if ($category === 'all' || $category === 'chat') {
            $channels = loadData('chat_channels.json');
            foreach ($channels as $ch) {
                $messages = loadData("chat_{$ch['id']}.json");
                foreach ($messages as $msg) {
                    if (stripos($msg['text'], $query) !== false) {
                        $results[] = [
                            'type' => 'chat',
                            'id' => $msg['id'],
                            'title' => '💬 ' . $ch['name'] . ' - ' . $msg['author_name'],
                            'content' => $msg['text'],
                            'created_at' => $msg['created_at'],
                            'icon' => '💬'
                        ];
                    }
                }
            }
        }

        // ファイルを検索
        if ($category === 'all' || $category === 'files') {
            $filesIndexPath = $DATA_DIR . '/files/index.json';
            if (file_exists($filesIndexPath)) {
                $files = json_decode(file_get_contents($filesIndexPath), true);
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (stripos($file['name'], $query) !== false) {
                            $results[] = [
                                'type' => 'file',
                                'id' => $file['id'],
                                'title' => $file['name'],
                                'content' => $file['size_formatted'] . ' - ' . $file['extension'],
                                'created_at' => $file['uploaded_at'],
                                'icon' => $file['icon']
                            ];
                        }
                    }
                }
            }
        }

        // 日付順にソート
        usort($results, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

        ok(['results' => $results, 'count' => count($results)]);

    default:
        err('不明なモジュールです: ' . $module);
}
