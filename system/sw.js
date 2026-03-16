/**
 * SAKURA Groupware - Service Worker
 * PWA対応とオフライン機能を提供
 */

const CACHE_NAME = 'sakura-gw-v1.1';
const urlsToCache = [
    'groupware.html',
    'manifest.json',
    'icon-192.png',
    'icon-512.png',
    'favicon.ico'
];

// インストール時
self.addEventListener('install', event => {
    console.log('[Service Worker] インストール中...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[Service Worker] キャッシュを開きました');
                return cache.addAll(urlsToCache);
            })
            .catch(err => {
                console.error('[Service Worker] キャッシュエラー:', err);
            })
    );
    // 新しいService Workerをすぐにアクティブ化
    self.skipWaiting();
});

// アクティベーション時
self.addEventListener('activate', event => {
    console.log('[Service Worker] アクティベート中...');
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] 古いキャッシュを削除:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    // 即座に制御を開始
    return self.clients.claim();
});

// フェッチ時（ネットワークファースト戦略）
self.addEventListener('fetch', event => {
    // GET以外はキャッシュしない
    if (event.request.method !== 'GET') {
        return;
    }

    // APIリクエストや外部ドメインリクエストはキャッシュしない
    const url = event.request.url;
    if (url.includes('gw_api.php') ||
        url.includes('gw_files.php') ||
        url.includes('gw_calendar_sync.php') ||
        url.includes('script.google.com')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // レスポンスが正常ならキャッシュに保存
                if (response && response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // ネットワークエラー時はキャッシュから返す
                return caches.match(event.request)
                    .then(response => {
                        if (response) {
                            console.log('[Service Worker] キャッシュから返します:', event.request.url);
                            return response;
                        }
                        // キャッシュにもない場合はオフラインページを返す
                        return new Response('オフラインです', {
                            status: 503,
                            statusText: 'Service Unavailable',
                            headers: new Headers({
                                'Content-Type': 'text/plain; charset=utf-8'
                            })
                        });
                    });
            })
    );
});

// プッシュ通知受信時
self.addEventListener('push', event => {
    console.log('[Service Worker] プッシュ通知を受信:', event);

    let notificationData = {
        title: 'SAKURA Groupware',
        body: '新しい通知があります',
        icon: '/icon-192.png',
        badge: '/icon-192.png',
        vibrate: [200, 100, 200],
        tag: 'notification',
        requireInteraction: false
    };

    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = {
                ...notificationData,
                ...data
            };
        } catch (e) {
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

// 通知クリック時
self.addEventListener('notificationclick', event => {
    console.log('[Service Worker] 通知がクリックされました');
    event.notification.close();

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                // すでにウィンドウが開いている場合はフォーカス
                for (let client of clientList) {
                    if (client.url.includes('groupware.html') && 'focus' in client) {
                        return client.focus();
                    }
                }
                // 開いていない場合は新規ウィンドウで開く
                if (clients.openWindow) {
                    return clients.openWindow('/groupware.html');
                }
            })
    );
});

// メッセージ受信時（アプリからの指示）
self.addEventListener('message', event => {
    console.log('[Service Worker] メッセージを受信:', event.data);

    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            caches.open(CACHE_NAME).then(cache => {
                return cache.addAll(event.data.urls);
            })
        );
    }
});
