/**
 * SAKURA MUSIC - App Main (v1.4.20)
 * - Safe Drive Playlist Reordering (LocalStorage)
 * - Web Audio API High-Quality Engine (v1.2.9)
 * - Screen Wake Lock API (Car-Nav style sleep prevention)
 * - Mobile User Gesture Unlock & Silent Keeper (v1.4.20)
 */

const APP_VERSION = "v2.0.0";

const AppConfig = {
    appleToken: localStorage.getItem('sakura_apple_token') || '',
    googleClientId: localStorage.getItem('sakura_google_id') || '1034545304058-8414nlkdpms40gjknm47r9v3efbbjdas.apps.googleusercontent.com',
    googleApiKey: localStorage.getItem('sakura_google_api_key') || 'AIzaSyD-IoqRej9KOCWDYy8W7InW3qpu6xxbW8Y',
    gasUrl: localStorage.getItem('sakura_gas_url') || 'https://script.google.com/macros/s/AKfycbyLc7Q25g8GOlwAhl2hkJSreQqfQllKsFQ7eJ1vL_ARUAG6Gz6EpnCpVygGSBiWUx1D/exec',
    driveFolderId: localStorage.getItem('sakura_drive_folder_id') || '1ftI36zOUQ_snJEFTBqPbRbbxH18cLckS',

    save(apple, google, googleApi, gasUrl, folderId) {
        localStorage.setItem('sakura_apple_token', apple || '');
        localStorage.setItem('sakura_google_id', google || '');
        localStorage.setItem('sakura_google_api_key', googleApi || '');
        localStorage.setItem('sakura_gas_url', gasUrl || '');
        localStorage.setItem('sakura_drive_folder_id', folderId || '');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    try {
        initNavigation();
        initSettings();
        initPlayerUI();
        initSearch();
        initMobileMenu();
        initMobileOverlay(); // モバイルオーバーレイの初期化を追加
        YouTubeHandler.init();
        // ページ起動時に Drive キャッシュをバックグラウンドで自動開始
        startBackgroundCache();
        // 音量の初期同期
        syncInitialVolume();

        // ページ内どこかを一回タップした時に、モバイルの音声制限を解除する
        const unlock = () => {
            console.log("[App] Unlocking mobile audio restrict...");

            // 1. Google Drive / 共通オーディオのアンロック
            if (!window.__localAudio) {
                window.__localAudio = new Audio();
                window.__localAudio.muted = false;
                syncInitialVolume();
                try { connectAudioEngine(window.__localAudio); } catch (e) { }
            }

            // モバイルブラウザでの再生制限を解除するために一度再生して即停止する (Kick処理)
            if (window.__localAudio) {
                window.__localAudio.play().then(() => {
                    window.__localAudio.pause();
                    console.log("[App] window.__localAudio unlocked.");
                }).catch(e => {
                    console.warn("[App] window.__localAudio initial play failed:", e);
                });
            }

            // AudioContext をユーザー操作内で開始 (まだなければ作成)
            if (!window.__audioContext) {
                try {
                    window.__audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    console.log("[App] AudioContext created in unlock.");
                } catch (e) { console.error("AudioContext creation failed", e); }
            }

            if (window.__audioContext && window.__audioContext.state === 'suspended') {
                window.__audioContext.resume().then(() => {
                    console.log(`[App] AudioContext state: ${window.__audioContext.state}`);
                });
            }

            // 2. YouTube のアンロック (完全に独立させる)
            try {
                if (YouTubeHandler && typeof YouTubeHandler.unlock === 'function') {
                    YouTubeHandler.unlock();
                }
            } catch (e) {
                console.warn("[App] YouTube unlock failed, but continuing for Drive...", e);
            }

            document.removeEventListener('touchstart', unlock);
            document.removeEventListener('click', unlock);
        };
        document.addEventListener('touchstart', unlock, { passive: true });
        document.addEventListener('click', unlock);

        // v1.4.12: バージョン表示を動的に更新
        const verEl = document.getElementById('app-version-display');
        if (verEl) verEl.innerText = APP_VERSION;

        // Auto-login YouTube (v1.4.35: Improved Check)
        if (localStorage.getItem('yt_access_token')) {
            console.log("Auto-login: YouTube token found, verifying status...");
            // トークンの有効性を確認（サイレントログイン試行）
            YouTubeHandler.checkLoginStatus(AppConfig.googleClientId).then(ok => {
                if (ok) {
                    showYouTubeLibrary();
                } else {
                    console.log("Silent login failed or token expired.");
                }
            });
        }
    } catch (e) {
        console.error('Initial sequence failed:', e);
    }
});

// 再生状態管理用
// 状態管理
let _currentQueue = [];
let _currentIndex = -1;
let _progressTimer = null;
let _cacheJobRunning = false;
let _isDragging = false; // ドラッグ中フラグ
let _wakeLock = null; // v1.4.20: Wake Lock 用
window.__activePlayer = null; // 'youtube' or 'drive'
window.__audioSourceNode = null;
/**
 * v1.2.9: 高音質オーディオエンジン (Web Audio API) の初期化とルーティング
 * @param {HTMLAudioElement} audioElement 
 */
function connectAudioEngine(audioElement) {
    if (!window.__audioContext) {
        window.__audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }

    // すでにコンテキストが Suspended（ブラウザの自動再生ブロック等）の場合は再開
    if (window.__audioContext.state === 'suspended') {
        window.__audioContext.resume();
    }

    // 既存のソースノードがあれば切断（多重接続防止）
    if (window.__audioSourceNode) {
        window.__audioSourceNode.disconnect();
    }

    // 新たにソースノードを作成し、各種エフェクトへ接続
    window.__audioSourceNode = window.__audioContext.createMediaElementSource(audioElement);

    const ctx = window.__audioContext;

    // 1. Low Shelf: 100Hz 以下の低域をブースト (+4dB)
    const lowEQ = ctx.createBiquadFilter();
    lowEQ.type = 'lowshelf';
    lowEQ.frequency.value = 100;
    lowEQ.gain.value = 4.0;

    // 2. Peaking: 2000Hz 半ばの中広域をブースト (+2.5dB) - ボーカルの抜け感向上
    const midEQ = ctx.createBiquadFilter();
    midEQ.type = 'peaking';
    midEQ.frequency.value = 2000;
    midEQ.Q.value = 1.0;
    midEQ.gain.value = 2.5;

    // 3. High Shelf: 10000Hz 以上の高域をブースト (+3dB) - 透明感・空気感
    const highEQ = ctx.createBiquadFilter();
    highEQ.type = 'highshelf';
    highEQ.frequency.value = 10000;
    highEQ.gain.value = 3.0;

    // 4. Dynamics Compressor: 全体の音圧を整え、EQによるクリッピングを防止
    const compressor = ctx.createDynamicsCompressor();
    compressor.threshold.value = -24; // 圧縮開始の閾値 (dB)
    compressor.knee.value = 30;
    compressor.ratio.value = 12; // 圧縮比
    compressor.attack.value = 0.003;
    compressor.release.value = 0.25;

    // ルーティング: Source -> Low -> Mid -> High -> Compressor -> Destination (スピーカー)
    window.__audioSourceNode
        .connect(lowEQ)
        .connect(midEQ)
        .connect(highEQ)
        .connect(compressor)
        .connect(ctx.destination);

    console.log('🎧 Web Audio API Engine Connected (Hi-Res Mode).');
}

/**
 * プログレスバー（タイムライン）の定期更新
 */
function startProgressTimer() {
    if (_progressTimer) clearInterval(_progressTimer);

    _progressTimer = setInterval(() => {
        const slider = document.getElementById('progress-slider');
        const currentTimeEl = document.getElementById('time-current');
        const durationEl = document.getElementById('time-total');

        const mSlider = document.getElementById('overlay-progress-slider');
        const mCurrentTimeEl = document.getElementById('overlay-time-current');
        const mDurationEl = document.getElementById('overlay-time-total');
        const mProgressBar = document.getElementById('overlay-progress-bar-bg');

        let current = 0;
        let total = 0;

        try {
            if (window.__activePlayer === 'drive' && window.__localAudio) {
                current = window.__localAudio.currentTime || 0;
                total = window.__localAudio.duration || 0;
            } else if (window.__activePlayer === 'youtube') {
                current = YouTubeHandler.getCurrentTime() || 0;
                total = YouTubeHandler.getDuration() || 0;
            }

            // 歌詞の同期ハイライト更新
            if (window.__syncedLyrics && window.__syncedLyrics.length > 0) {
                let activeIdx = -1;
                for (let i = 0; i < window.__syncedLyrics.length; i++) {
                    if (current >= window.__syncedLyrics[i].time - 0.3) { // わずかに早めに反応
                        activeIdx = i;
                    } else {
                        break;
                    }
                }

                if (activeIdx !== -1 && window.__lastActiveLyricIdx !== activeIdx) {
                    window.__lastActiveLyricIdx = activeIdx;

                    // すべてのlyric-lineのアクティブ状態を解除
                    document.querySelectorAll('.lyric-line').forEach(l => l.classList.remove('active'));

                    // PC版のハイライトとスクロール
                    const pcContainer = document.getElementById('immersion-lyrics');
                    if (pcContainer) {
                        const pcActiveEl = pcContainer.querySelector(`.lyric-line[data-idx="${activeIdx}"]`);
                        if (pcActiveEl) {
                            pcActiveEl.classList.add('active');
                            const offset = pcActiveEl.offsetTop - pcContainer.offsetTop - (pcContainer.clientHeight / 2) + (pcActiveEl.clientHeight / 2);
                            pcContainer.scrollTo({ top: Math.max(0, offset), behavior: 'smooth' });
                        }
                    }

                    // スマホ版のハイライトとスクロール
                    const mobileContainer = document.getElementById('mobile-immersion-lyrics');
                    if (mobileContainer) {
                        const mActiveEl = mobileContainer.querySelector(`.lyric-line[data-idx="${activeIdx}"]`);
                        if (mActiveEl) {
                            mActiveEl.classList.add('active');
                            const offset = mActiveEl.offsetTop - mobileContainer.offsetTop - (mobileContainer.clientHeight / 2) + (mActiveEl.clientHeight / 2);
                            mobileContainer.scrollTo({ top: Math.max(0, offset), behavior: 'smooth' });
                        }
                    }
                }
            }

            // 現在時刻の表示更新
            const formattedCurrent = formatTime(current);
            if (currentTimeEl) currentTimeEl.innerText = formattedCurrent;
            if (mCurrentTimeEl) mCurrentTimeEl.innerText = formattedCurrent;

            if (total > 0 && !isNaN(total)) {
                const formattedTotal = formatTime(total);
                if (durationEl) durationEl.innerText = formattedTotal;
                if (mDurationEl) mDurationEl.innerText = formattedTotal;

                const percent = (current / total) * 100;

                // デスクトップ用シークバー更新
                if (slider && !_isDragging) {
                    slider.value = percent;
                    const progressBar = document.getElementById('progress-bar-bg');
                    if (progressBar) progressBar.style.width = `${percent}%`;
                }

                // モバイル用（オーバーレイ）シークバー更新
                if (mSlider && !_isDragging) {
                    mSlider.value = percent;
                    if (mProgressBar) mProgressBar.style.width = `${percent}%`;
                }
            } else {
                const zeroTime = "0:00";
                if (durationEl) durationEl.innerText = zeroTime;
                if (mDurationEl) mDurationEl.innerText = zeroTime;

                if (slider && !_isDragging) {
                    slider.value = 0;
                    const progressBar = document.getElementById('progress-bar-bg');
                    if (progressBar) progressBar.style.width = '0%';
                }
            }

            // v2.0.0 スマホ版用: 自動次曲フォールバック機構
            // バックグラウンド等で event がブロックされる場合への対策
            if (total > 0 && current > 0) {
                // 残り0.5秒を切ったら「ほぼ終了」とみなし次へ
                if (total - current <= 0.5) {
                    if (!window.__isTransitioningNext) {
                        window.__isTransitioningNext = true;
                        console.log('[App] Fallback next track triggered by timer.');
                        releaseWakeLock();
                        if (typeof window.playNextTrack === 'function') {
                            window.playNextTrack();
                        }
                        // 連続発火防止のため少し待つ
                        setTimeout(() => { window.__isTransitioningNext = false; }, 2000);
                    }
                }
            }

        } catch (e) {
            console.warn('Progress update error:', e);
        }
    }, 500);
}

function formatTime(seconds) {
    if (isNaN(seconds) || seconds === Infinity) return "0:00";
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
}

/**
 * 全プレイヤーの音量を同期 (初期化用)
 */
function syncInitialVolume() {
    try {
        const saved = localStorage.getItem('sakura_volume') || 70;
        const volSlider = document.getElementById('volume-slider');
        if (volSlider) {
            volSlider.value = saved;
        }
        YouTubeHandler.setVolume(saved);
        if (window.__localAudio) {
            window.__localAudio.volume = saved / 100;
        }
    } catch (e) {
        console.warn('Volume sync failed:', e);
    }
}


/**
 * アプリ起動時に自動的にキャッシュを開始する。
 * Drive タブを開かなくてもバックグラウンドで ⚡ マークが点灯する。
 */
async function startBackgroundCache() {
    if (_cacheJobRunning) return;
    _cacheJobRunning = true;
    try {
        const files = await DriveHandler.listFiles();
        if (!files || files.length === 0) return;

        const targets = files.slice(0, 10);
        // 未キャッシュのものだけ抽出
        const needCache = [];
        for (const f of targets) {
            if (!(await DriveHandler.isCached(f.id))) needCache.push(f);
        }

        // 2件ずつ並列でキャッシュ
        for (let i = 0; i < needCache.length; i += 2) {
            const batch = needCache.slice(i, i + 2);
            await Promise.all(batch.map(async (f) => {
                const ok = await DriveHandler.cacheAudio(f.id, f.mimeType);
                if (ok) {
                    console.log('⚡ バックグラウンドキャッシュ完了:', f.name);
                    // Drive ビューが表示中なら即座に ⚡ を点灯
                    const badge = document.querySelector(`[data-file-id="${f.id}"] .cache-badge`);
                    if (badge) badge.style.opacity = '1';
                }
            }));
        }
    } catch (err) {
        console.warn('バックグラウンドキャッシュエラー:', err);
    } finally {
        _cacheJobRunning = false;
    }
}


function initNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            const t = link.innerText.trim();
            if (t.includes('ホーム')) showHome();
            else if (t.includes('YouTube Music')) showYouTubeLibrary();
            else if (t.includes('Google Drive')) showDriveLibrary();
        });
    });
}

function initSettings() {
    document.getElementById('settings-btn')?.addEventListener('click', () => {
        document.getElementById('apple-token-input').value = AppConfig.appleToken;
        document.getElementById('google-id-input').value = AppConfig.googleClientId;
        document.getElementById('google-api-key-input').value = AppConfig.googleApiKey;
        document.getElementById('gas-url-input').value = AppConfig.gasUrl;
        document.getElementById('drive-folder-id-input').value = AppConfig.driveFolderId;
        document.getElementById('settings-modal').classList.add('open');
    });
    document.getElementById('save-settings')?.addEventListener('click', () => {
        AppConfig.save(
            document.getElementById('apple-token-input').value,
            document.getElementById('google-id-input').value,
            document.getElementById('google-api-key-input').value,
            document.getElementById('gas-url-input').value,
            document.getElementById('drive-folder-id-input').value
        );
        document.getElementById('settings-modal').classList.remove('open');
        location.reload();
    });
}

function initSearch() {
    const inputs = [document.getElementById('global-search'), document.getElementById('center-search')];
    inputs.forEach(input => {
        input?.addEventListener('keypress', async (e) => {
            if (e.key === 'Enter') {
                const query = input.value.trim();
                if (!query) return;
                hideAllViews();
                document.getElementById('search-view').style.display = 'block';
                const grid = document.getElementById('search-results');
                grid.innerHTML = '<p style="grid-column:1/-1; text-align:center; padding:50px;">検索中...</p>';
                try {
                    const results = await YouTubeHandler.search(query, AppConfig.googleApiKey);
                    renderSearchResults(results);
                } catch (err) {
                    grid.innerHTML = `<p style="grid-column:1/-1; text-align:center;">エラー: ${err.message}</p>`;
                }
            }
        });
    });
}

function hideAllViews() {
    ['welcome-view', 'library-view', 'playlist-detail-view', 'drive-view', 'search-view'].forEach(id => {
        const el = document.getElementById(id); if (el) el.style.display = 'none';
    });
    // v1.4.29: Drive等へ戻った際に確実にプレイヤーバーを再表示させる
    document.body.classList.remove('yt-mobile-active');
}

function showHome() { hideAllViews(); document.getElementById('welcome-view').style.display = 'block'; }

async function showYouTubeLibrary() {
    hideAllViews();
    document.getElementById('library-view').style.display = 'block';
    const grid = document.getElementById('library-content');
    grid.innerHTML = '<p style="text-align:center; opacity:0.5;">読込中...</p>';
    const playlists = await YouTubeHandler.getLibrary();
    renderLibrary(playlists);
}

function renderLibrary(playlists) {
    const grid = document.getElementById('library-content');
    grid.innerHTML = '';
    playlists.forEach(pl => {
        const card = document.createElement('div');
        card.className = 'result-card';
        card.innerHTML = `<img src="${pl.snippet.thumbnails.medium.url}" class="result-thumb"><div class="result-title">${pl.snippet.title}</div><div style="font-size:11px; opacity:0.5;">${pl.contentDetails?.itemCount || 0} tracks</div>`;
        card.addEventListener('click', () => showPlaylistDetails(pl.id, pl.snippet.title));
        grid.appendChild(card);
    });
}

async function showPlaylistDetails(id, title) {
    hideAllViews();
    document.getElementById('playlist-detail-view').style.display = 'block';
    document.getElementById('playlist-title').innerText = title;
    const grid = document.getElementById('playlist-items');
    grid.innerHTML = '読み込み中...';
    const items = await YouTubeHandler.getPlaylistItems(id);
    grid.innerHTML = '';
    items.forEach(item => {
        const { videoId } = item.snippet.resourceId;
        const t = item.snippet.title;
        let artist = item.snippet.videoOwnerChannelTitle || 'YouTube Music';
        // v1.2.4: アーティスト名のクレンジング
        artist = artist.replace(/ - Topic$/i, '').replace(/ OFFICIAL$| Official Channel$/i, '').trim();
        const thumb = item.snippet.thumbnails?.default?.url || '';
        const row = document.createElement('div');
        row.className = 'playlist-row';
        row.style = 'display:flex; align-items:center; padding:12px; background:rgba(255,255,255,0.03); margin-bottom:8px; cursor:pointer; border-radius:12px;';
        row.innerHTML = `
            <img src="${thumb}" style="width:40px; height:40px; border-radius:6px; margin-right:15px; object-fit:cover;">
            <div style="flex:1;"><div style="font-weight:600;">${t}</div><div style="font-size:11px; opacity:0.4;">${artist}</div></div>
        `;
        row.addEventListener('click', () => {
            _currentQueue = items.map(it => ({
                id: it.snippet.resourceId.videoId,
                title: it.snippet.title,
                artist: it.snippet.videoOwnerChannelTitle || 'YouTube Music',
                thumb: it.snippet.thumbnails?.default?.url || '',
                isDrive: false
            }));
            _currentIndex = items.indexOf(item);
            playTrack(videoId, t, artist, thumb, false);
        });
        grid.appendChild(row);
    });
}

function renderSearchResults(items) {
    const grid = document.getElementById('search-results');
    grid.innerHTML = '';
    items.forEach(item => {
        const videoId = item.id.videoId;
        const { title, thumbnails, channelTitle } = item.snippet;
        const artist = channelTitle.replace(/ - Topic$/i, '').trim();
        const card = document.createElement('div');
        card.className = 'result-card';
        card.innerHTML = `<img src="${thumbnails.medium.url}" class="result-thumb"><div class="result-title">${title}</div><div class="result-artist">${artist}</div>`;
        card.addEventListener('click', () => {
            _currentQueue = items.map(it => ({
                id: it.id.videoId,
                title: it.snippet.title,
                artist: it.snippet.channelTitle,
                thumb: it.snippet.thumbnails.medium.url,
                isDrive: false
            }));
            _currentIndex = items.indexOf(item);
            // プレイヤーバーの基本情報を更新
            updatePlayerBarUI(title, artist, thumbnails.medium.url);

            // モバイルでの自動遷移（iOS YouTubeアプリ風）
            if (window.innerWidth <= 768) {
                const overlay = document.getElementById('mobile-player-overlay');
                if (overlay) {
                    overlay.classList.add('open');
                    document.body.classList.add('yt-mobile-active');
                    updateMobileOverlayUI(title, artist, thumbnails.medium.url);
                }
            }
            playTrack(videoId, title, artist, thumbnails.medium.url, false);
        });
        grid.appendChild(card);
    });
}

async function showDriveLibrary() {
    hideAllViews();
    document.getElementById('drive-view').style.display = 'block';
    const grid = document.getElementById('drive-content');
    grid.innerHTML = '<p style="text-align:center; padding:40px; opacity:0.5;">✨ ドライブを同期中...</p>';
    const lamp = document.getElementById('drive-status-lamp');
    if (lamp) { lamp.className = ''; lamp.style.background = '#666'; }

    const files = await DriveHandler.listFiles();

    if (lamp) {
        if (files === null) { lamp.classList.add('lamp-error'); lamp.title = '接続エラー'; }
        else { lamp.classList.add('lamp-ok'); lamp.title = `接続完了 (${files.length}曲)`; }
    }

    // キャッシュチェックなしで即座に描画
    renderDriveFiles(files || []);
    setupDriveActions();

    if (!files || files.length === 0) return;

    // ⚡マーカーを並列チェックして更新（描画をブロックしない）
    Promise.all(files.map(async (f) => {
        const cached = await DriveHandler.isCached(f.id);
        if (cached) {
            const badge = document.querySelector(`[data-file-id="${f.id}"] .cache-badge`);
            if (badge) badge.style.opacity = '1';
        }
    }));

    // バックグラウンドでキャッシュ（最初の10曲、並列2件ずつ）
    const uncached = [];
    for (const f of files.slice(0, 10)) {
        if (!(await DriveHandler.isCached(f.id))) uncached.push(f);
    }
    // 2件ずつ並列でキャッシュ（サーバー負荷を抑えつつ高速化）
    for (let i = 0; i < uncached.length; i += 2) {
        const batch = uncached.slice(i, i + 2);
        await Promise.all(batch.map(async (f) => {
            const ok = await DriveHandler.cacheAudio(f.id, f.mimeType);
            if (ok) {
                const badge = document.querySelector(`[data-file-id="${f.id}"] .cache-badge`);
                if (badge) {
                    badge.style.opacity = '1';
                    console.log('⚡ キャッシュ完了:', f.name);
                }
            }
        }));
    }
}

// v1.2.8: 安全な曲順保存ロジック (LocalStorage)
function getOrderedDriveFiles(files) {
    if (!files || files.length === 0) return files;

    // LocalStorageから順序配列を取得
    let savedOrder = [];
    try {
        const stored = localStorage.getItem('sakura_drive_order');
        if (stored) savedOrder = JSON.parse(stored);
    } catch (e) { console.error('Error reading drive order', e); }

    const fileMap = new Map();
    files.forEach(f => fileMap.set(f.id, f));

    const orderedFiles = [];
    // 存在するファイルだけを保存された順序で追加
    for (const id of savedOrder) {
        if (fileMap.has(id)) {
            orderedFiles.push(fileMap.get(id));
            fileMap.delete(id);
        }
    }
    // 保存されていなかった新規ファイルは末尾に追加
    for (const [id, f] of fileMap.entries()) {
        orderedFiles.push(f);
    }

    // 最新の順序を再保存して自己修復 (描画ブロックを防ぐため非同期で)
    setTimeout(() => {
        try {
            localStorage.setItem('sakura_drive_order', JSON.stringify(orderedFiles.map(f => f.id)));
        } catch (e) { console.error('Error saving drive order', e); }
    }, 0);

    return orderedFiles;
}

// v1.2.8: 曲順移動アクション
window.moveDriveFile = function (fileId, direction) {
    if (!DriveHandler._cache) return;
    const files = getOrderedDriveFiles(DriveHandler._cache);
    const index = files.findIndex(f => f.id === fileId);

    if (index === -1) return;
    if (direction === 'up' && index > 0) {
        [files[index], files[index - 1]] = [files[index - 1], files[index]];
    } else if (direction === 'down' && index < files.length - 1) {
        [files[index], files[index + 1]] = [files[index + 1], files[index]];
    } else {
        return;
    }

    // 並び替えた順序を保存
    try {
        localStorage.setItem('sakura_drive_order', JSON.stringify(files.map(f => f.id)));
    } catch (e) { console.error('Error saving new drive order', e); }

    // 画面を再描画（瞬時に反映）
    renderDriveFiles(DriveHandler._cache);
};

function renderDriveFiles(rawFiles) {
    const content = document.getElementById('drive-content');
    if (!rawFiles || rawFiles.length === 0) {
        content.innerHTML = `<div style="text-align:center; padding:40px; opacity:0.6;"><p>楽曲が見つかりませんでした。</p></div>`;
        return;
    }

    // 取得したファイルをユーザーのカスタム順序に並び替え
    const files = getOrderedDriveFiles(rawFiles);
    content.innerHTML = '';

    for (let i = 0; i < files.length; i++) {
        const f = files[i];
        const isFirst = (i === 0);
        const isLast = (i === files.length - 1);

        const item = document.createElement('div');
        item.className = 'drive-list-item';
        item.dataset.fileId = f.id;
        item.innerHTML = `
            <div style="display:flex; align-items:center; flex:1;">
                <div style="width:32px; height:32px; background:rgba(230,0,126,0.1); border-radius:6px; display:flex; align-items:center; justify-content:center; margin-right:12px;">🎵</div>
                <div style="min-width:0; flex:1;">
                    <div style="font-weight:bold; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${f.name}</div>
                    <div style="font-size:10px; opacity:0.4;">${f.path || 'Root'}</div>
                </div>
            </div>
            
            <div class="move-controls" style="display:flex; flex-direction:column; margin-right:8px; align-items:center; justify-content:center;">
                <button class="move-up-btn" style="background:none; border:none; padding:2px; font-size:16px; cursor:${isFirst ? 'default' : 'pointer'}; opacity:${isFirst ? 0.1 : 0.6}; transition:opacity 0.2s; color:#333;">▲</button>
                <button class="move-down-btn" style="background:none; border:none; padding:2px; font-size:16px; cursor:${isLast ? 'default' : 'pointer'}; opacity:${isLast ? 0.1 : 0.6}; transition:opacity 0.2s; color:#333;">▼</button>
            </div>

            <span class="cache-badge" title="キャッシュ済みで高速再生" style="font-size:14px; margin-right:8px; opacity:0.15; transition:opacity 0.5s;">⚡</span>
            <button class="delete-btn" style="background:none; border:none; color:#ff4d4d; cursor:pointer; padding:8px;">✕</button>
        `;

        // トラッククリック再生イベント（ボタン以外）
        item.addEventListener('click', (e) => {
            if (!e.target.closest('button')) {
                _currentQueue = files.map(driveFile => ({
                    id: driveFile.id,
                    title: driveFile.name,
                    artist: 'Google Drive',
                    thumb: '',
                    isDrive: true,
                    mimeType: driveFile.mimeType
                }));
                _currentIndex = i;
                // プレイヤーバーの基本情報を更新
                updatePlayerBarUI(f.name, 'Google Drive', '');

                playTrack(f.id, f.name, 'Google Drive', '', true);
            }
        });

        // 移動ボタンイベント
        const upBtn = item.querySelector('.move-up-btn');
        const downBtn = item.querySelector('.move-down-btn');

        if (!isFirst) {
            upBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                moveDriveFile(f.id, 'up');
            });
            upBtn.addEventListener('mouseenter', () => upBtn.style.opacity = '1');
            upBtn.addEventListener('mouseleave', () => upBtn.style.opacity = '0.6');
        }

        if (!isLast) {
            downBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                moveDriveFile(f.id, 'down');
            });
            downBtn.addEventListener('mouseenter', () => downBtn.style.opacity = '1');
            downBtn.addEventListener('mouseleave', () => downBtn.style.opacity = '0.6');
        }

        // 削除ボタンイベント
        item.querySelector('.delete-btn').addEventListener('click', async (e) => {
            e.stopPropagation();
            if (confirm(`「${f.name}」を削除しますか？`)) { await DriveHandler.deleteFile(f.id); showDriveLibrary(); }
        });

        content.appendChild(item);
    }
}

function setupDriveActions() {
    const trigger = document.getElementById('drive-upload-trigger');
    const input = document.getElementById('drive-upload-input');
    if (!trigger || trigger.dataset.init) return;
    trigger.dataset.init = "true";
    trigger.addEventListener('click', () => input.click());
    input.addEventListener('change', async (e) => {
        const files = Array.from(e.target.files);
        const container = document.getElementById('upload-progress-container');
        if (container) container.style.display = 'block';
        for (const file of files) {
            document.getElementById('upload-filename').innerText = file.name;
            await DriveHandler.uploadFile(file);
        }
        if (container) container.style.display = 'none';
        showDriveLibrary();
    });
}

// v1.4.22: プレイヤーバーのUIを更新する共通関数
function updatePlayerBarUI(title, artist, thumb) {
    const titleEl = document.querySelector('.track-details h3');
    const artistEl = document.querySelector('.track-details p');
    const artEl = document.querySelector('.album-art');
    const playBtn = document.querySelector('.play-btn');

    if (titleEl) titleEl.innerText = title;
    if (artistEl) artistEl.innerText = artist;
    if (artEl && thumb) artEl.style.backgroundImage = `url(${thumb})`;
    if (playBtn) playBtn.innerText = '⏳'; // 再生開始中は一時的にローディング表示
}

function updateMobileOverlayUI(title, artist, thumb) {
    const oTitle = document.getElementById('overlay-title');
    const oArtist = document.getElementById('overlay-artist');
    const oArt = document.getElementById('overlay-album-art');
    const oBg = document.getElementById('mobile-overlay-bg');

    if (oTitle) oTitle.innerText = title;
    if (oArtist) oArtist.innerText = artist;

    if (oArt) {
        if (thumb && thumb.includes('ytimg.com')) {
            let maxRes = thumb;
            let hqRes = thumb;

            if (!thumb.includes('maxresdefault.jpg')) {
                maxRes = thumb.replace(/\/(default|mqdefault|hqdefault|sddefault)\.jpg/, '/maxresdefault.jpg');
                hqRes = thumb.replace(/\/(default|mqdefault|sddefault|maxresdefault)\.jpg/, '/hqdefault.jpg');
            }

            const img = new Image();
            img.onload = () => {
                const finalSrc = (img.width > 120) ? maxRes : hqRes;
                oArt.style.backgroundImage = `url(${finalSrc})`;
                if (oBg) oBg.style.backgroundImage = `url(${finalSrc})`;
            };
            img.onerror = () => {
                oArt.style.backgroundImage = `url(${hqRes})`;
                if (oBg) oBg.style.backgroundImage = `url(${hqRes})`;
            };
            img.src = maxRes;
        } else if (thumb) {
            oArt.style.backgroundImage = `url(${thumb})`;
            if (oBg) oBg.style.backgroundImage = `url(${thumb})`;
        } else {
            oArt.style.backgroundImage = 'linear-gradient(135deg, #1a0a0f, #3a1a2f)';
            oArt.innerHTML = '<div style="font-size:80px; text-shadow: 0 10px 20px rgba(0,0,0,0.5);">🎵</div>';
            oArt.style.display = 'flex';
            oArt.style.alignItems = 'center';
            oArt.style.justifyContent = 'center';
            if (oBg) oBg.style.backgroundImage = 'none';
        }
    }

    const artView = document.getElementById('mobile-art-view');
    const lyricsView = document.getElementById('mobile-lyrics-view');
    const lyricsBtn = document.getElementById('overlay-lyrics-btn');
    if (artView && lyricsView) {
        artView.style.display = 'flex';
        lyricsView.style.display = 'none';
        if (lyricsBtn) {
            lyricsBtn.innerText = 'Lyrics';
            lyricsBtn.style.background = 'rgba(255,255,255,0.1)';
        }
    }
}
async function playTrack(id, title, artist, thumb, isDrive = false) {
    // 1. UI要素の取得 (updatePlayerBarUIで大部分を処理)
    const playBtn = document.querySelector('.play-btn');

    // 2. 音声エンジンの確保とアンロック (v1.4.35: ユーザー操作の直後、同期的に実行)
    if (!window.__localAudio) {
        window.__localAudio = new Audio();
        syncInitialVolume();
        window.setupAudioListeners?.();
    }
    const audio = window.__localAudio;

    // エンジンが未接続なら接続
    if (!window.__audioContext || !window.__audioSourceNode) {
        try { connectAudioEngine(audio); } catch (e) { console.warn("Engine connection failed", e); }
    }

    // [VITAL] 非同期処理の前に無音を再生し続け、ブラウザの音声制限を完全に「開いたまま」にする。
    // YouTube再生時同様のロジックを、DriveのBlob取得待機中にも適用。
    if (audio) {
        audio.muted = false;
        audio.loop = true;
        // 1ピクセルの無音WAV
        audio.src = "data:audio/wav;base64,UklGRigAAABXQVZFRm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQQAAAAAAA==";
        audio.play().catch(e => console.warn("Initial kick failed:", e));
    }
    if (window.__audioContext && window.__audioContext.state === 'suspended') {
        window.__audioContext.resume();
    }

    YouTubeHandler.pause();
    audio.onended = null;

    // 3. UI基本更新
    updatePlayerBarUI(title, artist, thumb);
    if (playBtn) playBtn.innerText = '⏳';


    // 4. MediaSession 設定 (通知センター / ロック画面対応)
    if ('mediaSession' in navigator) {
        navigator.mediaSession.metadata = new MediaMetadata({
            title: title,
            artist: artist,
            album: isDrive ? 'Google Drive' : 'YouTube Music',
            artwork: thumb ? [{ src: thumb, sizes: '512x512', type: 'image/png' }] : []
        });
        navigator.mediaSession.setActionHandler('play', () => {
            if (window.__activePlayer === 'drive') { audio.play(); if (playBtn) playBtn.innerText = '⏸️'; }
            else { YouTubeHandler.resume(); if (playBtn) playBtn.innerText = '⏸️'; }
        });
        navigator.mediaSession.setActionHandler('pause', () => {
            if (window.__activePlayer === 'drive') { audio.pause(); if (playBtn) playBtn.innerText = '▶️'; }
            else { YouTubeHandler.pause(); if (playBtn) playBtn.innerText = '▶️'; }
        });
        navigator.mediaSession.setActionHandler('nexttrack', () => playNextTrack());
        navigator.mediaSession.setActionHandler('previoustrack', () => playPreviousTrack());
    }

    // 5. YouTube 再生 (同期命令を最優先)
    if (!isDrive) {
        window.__activePlayer = 'youtube';
        YouTubeHandler.play(id);
        if (playBtn) playBtn.innerText = '⏸️';

        // v1.4.20: スリープ防止
        requestWakeLock();
        audio.src = "data:audio/wav;base64,UklGRigAAABXQVZFRm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQQAAAAAAA==";
        audio.loop = true;
        audio.play().catch(e => console.warn("Silent keeper failed:", e));
    } else {
        // 6. Google Drive 再生 (既存のロジックを最適化)
        window.__activePlayer = 'drive';
        requestWakeLock();

        try {
            console.log(`[App] Starting Drive Playback process: ${title} (${id})`);

            let blobUrl = await DriveHandler.getCachedBlobUrl(id);
            if (!blobUrl) {
                console.log('[App] No cache found, fetching from GAS proxy...');
                blobUrl = await DriveHandler.fetchAudioStream(id);
            }

            if (blobUrl) {
                console.log('[App] Blob URL ready, swapping source...');
                // プレースホルダ(無音)を停止して差し替え
                audio.pause();
                audio.src = blobUrl;
                audio.loop = false;
                audio.load();

                const playPromise = audio.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        if (playBtn) playBtn.innerText = '⏸️';
                        console.log('[App] Playback started successfully!');
                    }).catch(e => {
                        console.error('[App] Playback Error:', e);
                        if (playBtn) playBtn.innerText = '▶️';
                    });
                }

                // v2.0.0 よりモバイルSafari/Chromeでの安定化のため、timer側でのフォールバックも併用
                audio.onended = () => {
                    if (window.__isTransitioningNext) return; // 二重発火防止
                    window.__isTransitioningNext = true;
                    console.log('[App] Track ended (onended event), playing next...');
                    releaseWakeLock();
                    if (typeof window.playNextTrack === 'function') {
                        window.playNextTrack();
                    }
                    setTimeout(() => { window.__isTransitioningNext = false; }, 2000);
                };
            } else {
                console.error('[App] Failed to acquire blob URL.');
                if (playBtn) playBtn.innerText = '▶️';
                alert('楽曲データの取得に失敗しました。GASプロキシが応答していないか、ファイルサイズが制限を超えている可能性があります。');
            }
        } catch (err) {
            console.error('[App] Fatal error in Drive playback:', err);
            alert(`致命的なシステムエラー: ${err.message}`);
            if (playBtn) playBtn.innerText = '▶️';
        }

        // 先読みは再生開始を優先するため、少し遅らせる
        setTimeout(() => prefetchNextDriveTracks(_currentIndex), 2000);
    }

    // 7. UI表示と共通処理 (PCはImmersion View, スマホはモバイルオーバーレイ)
    if (window.innerWidth <= 768) {
        const overlay = document.getElementById('mobile-player-overlay');
        if (overlay) {
            overlay.style.display = 'flex';
            overlay.offsetHeight;
            overlay.classList.add('open');
            document.body.classList.add('yt-mobile-active');
            updateMobileOverlayUI(title, artist, thumb);
        }
    } else {
        openImmersionView(title, artist, thumb);
    }

    fetchLyrics(title, artist);
    startProgressTimer();
}

/**
 * v1.4.20: Screen Wake Lock API
 * カーナビアプリのように、再生中に画面スリープを抑制する
 */
async function requestWakeLock() {
    if ('wakeLock' in navigator) {
        try {
            if (!_wakeLock) {
                _wakeLock = await navigator.wakeLock.request('screen');
                console.log("Wake Lock is active.");
                _wakeLock.addEventListener('release', () => {
                    console.log("Wake Lock was released.");
                    _wakeLock = null;
                });
            }
        } catch (err) {
            console.warn(`${err.name}, ${err.message}`);
        }
    }
}

function releaseWakeLock() {
    if (_wakeLock) {
        _wakeLock.release();
        _wakeLock = null;
    }
}


/**
 * Google Drive 次の10曲を先読み
 * v1.4.21: 最後の⚡マークが再生されたら、さらに次の10曲をキャッシュ
 */
async function prefetchNextDriveTracks(startIndex) {
    if (!_currentQueue || _currentQueue.length === 0 || startIndex === -1) return;

    // 現在の曲の後から10曲分を取得
    const nextTracks = _currentQueue.slice(startIndex + 1, startIndex + 11);

    console.log(`Prefetching next ${nextTracks.length} tracks from index ${startIndex + 1}...`);

    // 2件ずつ並列でキャッシュ（サーバー負荷を抑えつつ高速化）
    for (let i = 0; i < nextTracks.length; i += 2) {
        const batch = nextTracks.slice(i, i + 2);
        await Promise.all(batch.map(async (track) => {
            if (track.isDrive && !(await DriveHandler.isCached(track.id))) {
                const ok = await DriveHandler.cacheAudio(track.id, track.mimeType);
                if (ok) {
                    const badge = document.querySelector(`[data-file-id="${track.id}"] .cache-badge`);
                    if (badge) {
                        badge.style.opacity = '1';
                        console.log('⚡ Cached:', track.title);
                    }

                    // 最後の⚡マーク（10曲目）がキャッシュされたら、さらに次の10曲をキャッシュ
                    if (i + batch.indexOf(track) === 9 && startIndex + 11 < _currentQueue.length) {
                        console.log('Last lightning bolt reached! Caching next 10 tracks...');
                        setTimeout(() => prefetchNextDriveTracks(startIndex + 10), 1000);
                    }
                }
            }
        }));
    }
}

/**
 * 次の曲へ
 */
function playNextTrack() {
    if (!_currentQueue || _currentQueue.length === 0 || _currentIndex === -1) {
        console.log('playNextTrack: No queue available');
        return;
    }
    _currentIndex = (_currentIndex + 1) % _currentQueue.length;
    const track = _currentQueue[_currentIndex];
    console.log('Playing next track:', track.title);
    playTrack(track.id, track.title, track.artist, track.thumb, track.isDrive);
}

/**
 * 前の曲へ
 */
function playPreviousTrack() {
    if (!_currentQueue || _currentQueue.length === 0 || _currentIndex === -1) {
        console.log('playPreviousTrack: No queue available');
        return;
    }
    _currentIndex = (_currentIndex - 1 + _currentQueue.length) % _currentQueue.length;
    const track = _currentQueue[_currentIndex];
    console.log('Playing previous track:', track.title);
    playTrack(track.id, track.title, track.artist, track.thumb, track.isDrive);
}

// グローバルに公開
window.playNextTrack = playNextTrack;
window.playPreviousTrack = playPreviousTrack;

function parseLRC(lrc) {
    if (!lrc) return null;
    const lines = lrc.split('\n');
    const parsed = [];
    const regex = /\[(\d{2}):(\d{2}(?:\.\d+)?)\](.*)/;
    for (const line of lines) {
        const match = line.match(regex);
        if (match) {
            const min = parseInt(match[1], 10);
            const sec = parseFloat(match[2]);
            const text = match[3].trim();
            if (text) {
                parsed.push({ time: min * 60 + sec, text: text });
            }
        }
    }
    return parsed.length > 0 ? parsed : null;
}


async function fetchLyrics(title, artist) {
    const pane = document.getElementById('lyrics-content');
    document.getElementById('lyrics-track-title').innerText = title;
    document.getElementById('lyrics-track-artist').innerText = artist;
    pane.innerHTML = '<p style="opacity:0.3;">歌詞を捜索中...</p>';

    // 高度なクレンジング処理 (v1.2.2)
    let cT = title;
    let cA = artist;

    // 共通の不要語削除 (v1.2.6 ライブ音源・カバー曲対応版)
    const cleanStr = (s) => s.replace(/\.(mp3|m4a|wav|flac)$/i, '')
        .replace(/^\d+[\s._-]+/, '') // トラック番号 01. 01 - 等
        .replace(/いつもの|～.*?～|〈.*?〉|\[.*?\]|\(.*?\)/g, (m) => {
            // ライブ関連キーワードや不要語が含まれていれば削除、そうでなければ残す
            return /official|video|audio|lyric|full|mv|topic|only|live|tour|day\.\d+|lovelive|いつもの/i.test(m) ? '' : m;
        })
        .replace(/Official Video|Music Video|Lyric Video|Full Audio|MV|Audio Only|Official Audio|Audio/gi, '')
        .replace(/ - Topic$| OFFICIAL$| Official Channel$/i, '')
        .replace(/Live ver\.?|Live Version/gi, '')
        .replace(/\s+/g, ' ')
        .trim();

    const isGenericArtist = (a) => {
        const lower = a.toLowerCase();
        return lower.includes('google drive') || lower === 'youtube music' || lower === 'youtube' || lower === 'unknown' || lower === 'music' || lower === '' || lower === 'various artists';
    };

    if (isGenericArtist(cA)) {
        // タイトル自体にアーティスト情報が含まれている場合が多い
        if (title.includes(' - ')) {
            const parts = title.split(' - ');
            cA = parts[0];
            cT = parts[1];
        } else if (title.includes('「') && title.includes('」')) {
            const match = title.match(/(.*?)「(.*?)」/);
            if (match) { cA = match[1]; cT = match[2]; }
        } else if (title.includes('/')) {
            const parts = title.split('/');
            cA = parts[0];
            cT = parts[1];
        } else {
            cA = '';
        }
    }

    cT = cleanStr(cT);
    cA = (cA && !isGenericArtist(cA)) ? cleanStr(cA) : '';

    const tryDisplay = (data, force = false) => {
        if (!data) return false;
        // v1.2.6: アーティスト名の検証 (カバー曲・ライブ盤での不一致を許容しつつ、全く無関係な曲を弾く)
        if (cA && !force) {
            const dataArtist = data.artistName.toLowerCase();
            const currentArtist = cA.toLowerCase();
            const dataTrack = data.trackName.toLowerCase();
            const currentTrack = cT.toLowerCase();

            // アーティストが一致するか、またはタイトルが非常に強く一致している場合は許可
            const isArtistMatch = dataArtist.includes(currentArtist) || currentArtist.includes(dataArtist);
            const isTrackMatch = dataTrack === currentTrack || dataTrack.includes(currentTrack) || currentTrack.includes(dataTrack);

            if (!isArtistMatch && !isTrackMatch) {
                console.warn(`Mismatch: Found "${data.artistName} - ${data.trackName}" for "${cA} - ${cT}"`);
                return false;
            }
        }
        document.getElementById('lyrics-track-title').innerText = data.trackName;
        document.getElementById('lyrics-track-artist').innerText = data.artistName;
        document.querySelector('.track-details h3').innerText = data.trackName;
        document.querySelector('.track-details p').innerText = data.artistName;

        const plainText = data.plainLyrics || (data.syncedLyrics ? data.syncedLyrics.replace(/\[\d+:\d+(?:\.\d+)?\]/g, '') : '') || '歌詞がありません';
        pane.innerText = plainText;

        const pcLyrics = document.getElementById('immersion-lyrics');
        const mobileLyrics = document.getElementById('mobile-immersion-lyrics');

        // 歌詞要素を生成するヘルパー関数
        const createLyricEls = (container, isSynced) => {
            if (!container) return;
            container.innerHTML = '';

            if (isSynced && window.__syncedLyrics) {
                window.__syncedLyrics.forEach((lineData, idx) => {
                    const el = document.createElement('div');
                    el.className = 'lyric-line';
                    el.setAttribute('data-idx', idx);
                    el.innerText = lineData.text;
                    el.addEventListener('click', () => {
                        if (window.__activePlayer === 'drive' && window.__localAudio) {
                            window.__localAudio.currentTime = lineData.time;
                        } else if (window.__activePlayer === 'youtube') {
                            YouTubeHandler.seekTo(lineData.time);
                        }
                    });
                    container.appendChild(el);
                });
            } else {
                container.innerHTML = plainText.split('\n').map(l => `<div class="lyric-line" style="cursor:default;">${l}</div>`).join('');
            }
        };

        if (pcLyrics || mobileLyrics) {
            window.__syncedLyrics = parseLRC(data.syncedLyrics);
            window.__lastActiveLyricIdx = -1; // reset state

            createLyricEls(pcLyrics, !!window.__syncedLyrics);
            createLyricEls(mobileLyrics, !!window.__syncedLyrics);
        }

        return true;
    };

    try {
        // 1. 普通に GET 試行
        const res = await fetch(`https://lrclib.net/api/get?artist_name=${encodeURIComponent(cA)}&track_name=${encodeURIComponent(cT)}`);
        if (res.ok) {
            const data = await res.json();
            if (tryDisplay(data)) return;
        }

        // 2. 検索 API でリトライ
        const sRes = await fetch(`https://lrclib.net/api/search?q=${encodeURIComponent(cT + " " + cA)}`);
        const sData = await sRes.json();
        if (sData && sData.length > 0) {
            if (tryDisplay(sData[0])) return;
        }

        // 3. (v1.2.5) 英数字キーワード抽出リトライ
        const alphaKeywords = cT.replace(/[^\x20-\x7E]/g, ' ').split(/\s+/).filter(w => w.length > 2).join(' ');
        if (alphaKeywords) {
            const sRes2 = await fetch(`https://lrclib.net/api/search?q=${encodeURIComponent(cA + " " + alphaKeywords)}`);
            const sData2 = await sRes2.json();
            if (sData2 && sData2.length > 0) {
                if (tryDisplay(sData2[0])) return;
            }
        }

        // 4. (v1.2.6 最終手段) アーティストを無視してタイトルのみで検索 (カバー曲対応)
        const sRes3 = await fetch(`https://lrclib.net/api/search?q=${encodeURIComponent(cT)}`);
        const sData3 = await sRes3.json();
        if (sData3 && sData3.length > 0) {
            // タイトルの完全一致を優先的に探す
            const bestMatch = sData3.find(d => d.trackName.toLowerCase() === cT.toLowerCase());
            if (tryDisplay(bestMatch || sData3[0])) return;
        }

        // 5. (v1.2.7 追加バックアップ) lyrics.ovh API を用いたフォールバック検索
        if (cA) {
            try {
                console.log("lrclib.net failed. Trying lyrics.ovh as fallback...");
                const ovhRes = await fetch(`https://api.lyrics.ovh/v1/${encodeURIComponent(cA)}/${encodeURIComponent(cT)}`);
                if (ovhRes.ok) {
                    const ovhData = await ovhRes.json();
                    if (ovhData && ovhData.lyrics) {
                        document.getElementById('lyrics-track-title').innerText = cT;
                        document.getElementById('lyrics-track-artist').innerText = cA;
                        document.querySelector('.track-details h3').innerText = cT;
                        document.querySelector('.track-details p').innerText = cA;
                        // lyrics.ovhはプレーンテキストのみ提供
                        pane.innerText = ovhData.lyrics;
                        const pcLyrics = document.getElementById('immersion-lyrics');
                        const mobileLyrics = document.getElementById('mobile-immersion-lyrics');
                        window.__syncedLyrics = null;

                        const renderPlain = (container) => {
                            if (container) {
                                container.innerHTML = ovhData.lyrics.split('\n').map(l => `<div class="lyric-line" style="cursor:default;">${l}</div>`).join('');
                            }
                        };

                        renderPlain(pcLyrics);
                        renderPlain(mobileLyrics);
                        return; // 成功したら終了
                    }
                }
            } catch (ovhError) {
                console.warn('lyrics.ovh Fetch Error:', ovhError);
            }
        }

        pane.innerHTML = '<p style="opacity:0.5;">歌詞が見つかりませんでした。</p>';
    } catch (e) {
        console.error('Lyrics Fetch Error:', e);
        pane.innerHTML = '<p style="opacity:0.5;">歌詞の取得に失敗しました。</p>';
    }
}

function initPlayerUI() {
    const btn = document.getElementById('play-btn');

    // 再生ボタン
    btn?.addEventListener('click', () => {
        // モバイルアンロックの再試行 (ユーザー操作を起点にする)
        if (YouTubeHandler.unlock) YouTubeHandler.unlock();

        if (window.__activePlayer === 'drive' && window.__localAudio) {
            if (window.__localAudio.paused) {
                window.__localAudio.play();
                if (btn) btn.innerText = '⏸️';
                startProgressTimer();
                requestWakeLock();
            } else {
                window.__localAudio.pause();
                if (btn) btn.innerText = '▶️';
                releaseWakeLock();
            }
        } else if (window.__activePlayer === 'youtube') {
            const state = YouTubeHandler.player?.getPlayerState();
            if (state === 1) { // 1: playing
                YouTubeHandler.pause();
                if (btn) btn.innerText = '▶️';
                releaseWakeLock();
            } else {
                YouTubeHandler.resume();
                if (btn) btn.innerText = '⏸️';
                startProgressTimer();
                requestWakeLock();
            }
        }
    });

    // YouTube 側の状態変化を監視してUIに即反映
    window.addEventListener('ytPlayerStateChange', (e) => {
        if (window.__activePlayer === 'youtube' && btn) {
            btn.innerText = (e.detail === 1) ? '⏸️' : '▶️';
        }
        // YouTube 再生終了 (0) 時に次へ
        if (e.detail === 0) {
            if (window.__isTransitioningNext) return; // 二重発火防止
            window.__isTransitioningNext = true;
            if (typeof window.playNextTrack === 'function') {
                window.playNextTrack();
            }
            setTimeout(() => { window.__isTransitioningNext = false; }, 2000);
        }
    });

    // 次へ・前へボタンをグローバル関数化
    window.playNextTrack = () => {
        if (_currentQueue && _currentQueue.length > 0) {
            let nextIdx = _currentIndex + 1;
            if (nextIdx >= _currentQueue.length) nextIdx = 0; // ループ
            _currentIndex = nextIdx;
            const t = _currentQueue[nextIdx];

            // UI上のアクティブ状態を更新 (v1.3.2)
            document.querySelectorAll('.app-list-item').forEach(el => el.classList.remove('active'));
            if (t.id) {
                const activeEl = document.querySelector(`.app-list-item[data-video-id="${t.id}"]`);
                if (activeEl) activeEl.classList.add('active');
            }
            if (t.fileId) {
                const activeEl = document.querySelector(`.drive-list-item[data-file-id="${t.fileId}"]`);
                if (activeEl) activeEl.classList.add('active');
            }

            playTrack(t.id || t.fileId, t.title || t.name, t.artist || "Google Drive", t.thumb || "", !!t.fileId);
        }
    };

    window.playPreviousTrack = () => {
        if (_currentQueue && _currentQueue.length > 0) {
            let prevIdx = _currentIndex - 1;
            if (prevIdx < 0) prevIdx = _currentQueue.length - 1; // ループ
            _currentIndex = prevIdx;
            const t = _currentQueue[prevIdx];

            // UI上のアクティブ状態を更新
            document.querySelectorAll('.app-list-item').forEach(el => el.classList.remove('active'));
            if (t.id) {
                const activeEl = document.querySelector(`.app-list-item[data-video-id="${t.id}"]`);
                if (activeEl) activeEl.classList.add('active');
            }
            if (t.fileId) {
                const activeEl = document.querySelector(`.drive-list-item[data-file-id="${t.fileId}"]`);
                if (activeEl) activeEl.classList.add('active');
            }

            playTrack(t.id || t.fileId, t.title || t.name, t.artist || "Google Drive", t.thumb || "", !!t.fileId);
        }
    };

    document.getElementById('next-btn')?.addEventListener('click', window.playNextTrack);
    document.getElementById('prev-btn')?.addEventListener('click', window.playPreviousTrack);

    // シークバー (スライダー形式)
    const progSlider = document.getElementById('progress-slider');

    // ドラッグ開始
    const startDrag = () => { _isDragging = true; };
    // ドラッグ終了（スライダー外でのリリースも確実にシーク実行）
    const endDrag = () => {
        if (!_isDragging) return; // 二重発火防止
        _isDragging = false;
        const percent = progSlider.value;

        // シーク実行
        if (window.__activePlayer === 'drive' && window.__localAudio) {
            window.__localAudio.currentTime = (percent / 100) * window.__localAudio.duration;
        } else if (window.__activePlayer === 'youtube') {
            const duration = YouTubeHandler.getDuration();
            YouTubeHandler.seekTo((percent / 100) * duration);
        }

        // プログレスバー確定
        const progressBar = document.getElementById('progress-bar-bg');
        if (progressBar) progressBar.style.width = `${percent}%`;
    };

    progSlider?.addEventListener('mousedown', startDrag);
    progSlider?.addEventListener('touchstart', startDrag, { passive: true });

    // document で確実にキャッチ（スライダー外でリリースしてもシーク実行）
    document.addEventListener('mouseup', endDrag);
    document.addEventListener('touchend', endDrag);

    // ドラッグ中のリアルタイム表示更新 (input イベント)
    progSlider?.addEventListener('input', (e) => {
        const percent = e.target.value;
        const currentTimeEl = document.getElementById('time-current');

        // プログレスバーをリアルタイム更新
        const progressBar = document.getElementById('progress-bar-bg');
        if (progressBar) progressBar.style.width = `${percent}%`;

        let total = 0;
        if (window.__activePlayer === 'drive' && window.__localAudio) {
            total = window.__localAudio.duration;
        } else if (window.__activePlayer === 'youtube') {
            total = YouTubeHandler.getDuration();
        }
        if (total > 0 && currentTimeEl) {
            currentTimeEl.innerText = formatTime((percent / 100) * total);
        }
    });

    const volSlider = document.getElementById('volume-slider');
    volSlider?.addEventListener('input', (e) => {
        const val = e.target.value;
        YouTubeHandler.setVolume(val);
        if (window.__localAudio) window.__localAudio.volume = val / 100;
        localStorage.setItem('sakura_volume', val);
    });

    document.getElementById('lyrics-btn')?.addEventListener('click', () => document.getElementById('lyrics-panel').classList.toggle('open'));
}

function initMobileMenu() {
    const toggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar-nav');
    const overlay = document.getElementById('sidebar-overlay');
    const navLinks = document.querySelectorAll('.nav-link');

    if (!toggle || !sidebar || !overlay) return;

    const closeMenu = () => {
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
        document.body.style.position = ''; // スクロールロック解除
    };

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = sidebar.classList.contains('open');
        if (isOpen) {
            closeMenu();
        } else {
            sidebar.classList.add('open');
            overlay.style.display = 'block';
            document.body.style.position = 'fixed'; // 背面のスクロールを防止
        }
    });

    overlay.addEventListener('click', closeMenu);

    navLinks.forEach(link => {
        link.addEventListener('click', closeMenu);
    });
}

window.loginYouTube = () => YouTubeHandler.login(AppConfig.googleClientId);
window.loginApple = () => AppleMusicHandler.login();

/**
 * --- iOS風再生オーバーレイ関連ロジック (v1.5.0) ---
 */
function initMobileOverlay() {
    const overlay = document.getElementById('mobile-player-overlay');
    const closeBtn = document.getElementById('overlay-close-btn');
    const playBtn = document.getElementById('overlay-play-btn');
    const prevBtn = document.getElementById('overlay-prev-btn');
    const nextBtn = document.getElementById('overlay-next-btn');
    const slider = document.getElementById('overlay-progress-slider');
    const lyricsBtn = document.getElementById('overlay-lyrics-btn');

    if (!overlay) return;

    // 閉じる
    closeBtn?.addEventListener('click', () => {
        overlay.classList.remove('open');
        document.body.classList.remove('yt-mobile-active');
        // 少し遅らせて display:none に（アニメーション待ち）
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 400);
    });

    // 再生・一時停止
    playBtn?.addEventListener('click', async () => {
        console.log('[MobileOverlay] Play button clicked, activePlayer:', window.__activePlayer);

        // 1. AudioContext の再開 (スマホで音が止まらないようにする最重要処理)
        if (window.__audioContext && window.__audioContext.state === 'suspended') {
            console.log('[MobileOverlay] Resuming suspended AudioContext...');
            await window.__audioContext.resume();
        }

        // 2. YouTube アンロック (YouTubeHandler 側で独立して制御)
        if (YouTubeHandler.unlock) YouTubeHandler.unlock();

        if (window.__activePlayer === 'drive' && window.__localAudio) {
            if (window.__localAudio.paused) {
                console.log('[MobileOverlay] Resuming Drive playback');
                window.__localAudio.muted = false;
                window.__localAudio.play().catch(e => console.error('Drive resume failed:', e));
                startProgressTimer();
                requestWakeLock();
            } else {
                console.log('[MobileOverlay] Pausing Drive playback');
                window.__localAudio.pause();
                releaseWakeLock();
            }
        } else if (window.__activePlayer === 'youtube') {
            const state = YouTubeHandler.player?.getPlayerState();
            if (state === 1) { // 1: playing
                YouTubeHandler.pause();
                releaseWakeLock();
            } else {
                YouTubeHandler.resume();
                startProgressTimer();
                requestWakeLock();
            }
        }
    });

    // 状態同期 (Drive/Local Audio 用) - window.__localAudio が生成されたら呼び出す
    window.setupAudioListeners = () => {
        if (!window.__localAudio || window.__localAudio._init) return;
        window.__localAudio._init = true;
        window.__localAudio.addEventListener('play', () => {
            const icon = '⏸️';
            if (playBtn) playBtn.innerText = icon;
            const mainPlayBtn = document.querySelector('.player-bar .play-btn');
            if (mainPlayBtn) mainPlayBtn.innerText = icon;
        });
        window.__localAudio.addEventListener('pause', () => {
            const icon = '▶️';
            if (playBtn) playBtn.innerText = icon;
            const mainPlayBtn = document.querySelector('.player-bar .play-btn');
            if (mainPlayBtn) mainPlayBtn.innerText = icon;
        });
    };

    // 次へ・前へ
    prevBtn?.addEventListener('click', playPreviousTrack);
    nextBtn?.addEventListener('click', playNextTrack);

    // シーク
    slider?.addEventListener('input', (e) => {
        _isDragging = true;
        const percent = e.target.value;
        const total = (window.__activePlayer === 'drive' && window.__localAudio) ? window.__localAudio.duration : YouTubeHandler.getDuration();
        const currentEl = document.getElementById('overlay-time-current');
        const bg = document.getElementById('overlay-progress-bar-bg');

        if (bg) bg.style.width = `${percent}%`;
        if (total > 0 && currentEl) {
            currentEl.innerText = formatTime((percent / 100) * total);
        }
    });

    slider?.addEventListener('change', (e) => {
        _isDragging = false;
        const percent = e.target.value;
        const total = (window.__activePlayer === 'drive' && window.__localAudio) ? window.__localAudio.duration : YouTubeHandler.getDuration();

        if (total > 0) {
            if (window.__activePlayer === 'drive' && window.__localAudio) {
                window.__localAudio.currentTime = (percent / 100) * total;
            } else if (window.__activePlayer === 'youtube') {
                YouTubeHandler.seekTo((percent / 100) * total);
            }
        }
    });

    // ドラッグ中にタイマーで戻されないようにイベント追加
    slider?.addEventListener('mousedown', () => { _isDragging = true; });
    slider?.addEventListener('touchstart', () => { _isDragging = true; });
    slider?.addEventListener('mouseup', () => { _isDragging = false; });
    slider?.addEventListener('touchend', () => { _isDragging = false; });

    // 歌詞表示切替 (Immersion風)
    lyricsBtn?.addEventListener('click', () => {
        const artView = document.getElementById('mobile-art-view');
        const lyricsView = document.getElementById('mobile-lyrics-view');

        if (lyricsView && lyricsView.style.display === 'none') {
            if (artView) artView.style.display = 'none';
            lyricsView.style.display = 'flex';
            lyricsBtn.innerText = 'Artwork';
            lyricsBtn.style.background = 'rgba(230,0,126,0.5)';
            // Scroll to active lyric block if any
            const activeLine = lyricsView.querySelector('.lyric-line.active');
            if (activeLine) {
                const container = document.getElementById('mobile-immersion-lyrics');
                const offset = activeLine.offsetTop - container.offsetTop - (container.clientHeight / 2) + (activeLine.clientHeight / 2);
                container.scrollTo({ top: Math.max(0, offset), behavior: 'smooth' });
            }
        } else if (lyricsView) {
            lyricsView.style.display = 'none';
            if (artView) artView.style.display = 'flex';
            lyricsBtn.innerText = 'Lyrics';
            lyricsBtn.style.background = 'rgba(255,255,255,0.1)';
        }
    });

    // YouTube 状態変化の同期 (アイコン一括更新)
    window.addEventListener('ytPlayerStateChange', (e) => {
        if (window.__activePlayer === 'youtube') {
            const icon = (e.detail === 1) ? '⏸️' : '▶️';
            if (playBtn) playBtn.innerText = icon; // オーバーレイ側
            const mainPlayBtn = document.querySelector('.player-bar .play-btn');
            if (mainPlayBtn) mainPlayBtn.innerText = icon; // メイン側

            // 再生開始時にオーバーレイが開いていなければ開く（モバイルのみ）
            if (e.detail === 1 && window.innerWidth <= 768 && !overlay.classList.contains('open')) {
                overlay.style.display = 'flex';
                setTimeout(() => { overlay.classList.add('open'); document.body.classList.add('yt-mobile-active'); }, 10);
            }
        }
    });
}

/* --- PC Immersion View --- */
function openImmersionView(title, artist, thumb) {
    const immersionView = document.getElementById('pc-immersion-view');
    if (!immersionView) return;

    // 背景とアートワーク更新
    updateImmersionBackground(thumb);

    // 歌詞のリセット
    const lyricsContainer = document.getElementById('immersion-lyrics');
    if (lyricsContainer) {
        lyricsContainer.innerHTML = '<p style="opacity:0.5; font-size:24px;">歌詞を読み込み中...</p>';
    }

    immersionView.style.display = 'flex';
    immersionView.offsetHeight; // force reflow
    immersionView.classList.add('open');
    document.body.classList.add('immersion-active');
}

window.closeImmersionView = function () {
    const immersionView = document.getElementById('pc-immersion-view');
    if (!immersionView) return;

    immersionView.classList.remove('open');
    document.body.classList.remove('immersion-active');
    setTimeout(() => {
        immersionView.style.display = 'none';
        // reset background to avoid artifacts when opened again
        const bg = document.getElementById('immersion-bg');
        if (bg) bg.style.backgroundImage = 'none';
    }, 600);
};

function updateImmersionBackground(thumb) {
    const bg = document.getElementById('immersion-bg');
    const art = document.getElementById('immersion-art');
    const fallback = document.getElementById('immersion-art-fallback');

    // YTMのサムネイルの場合は高解像度版を取得
    if (thumb && thumb.includes('ytimg.com')) {
        let maxRes = thumb;
        let hqRes = thumb;
        if (!thumb.includes('maxresdefault.jpg')) {
            maxRes = thumb.replace(/\/(default|mqdefault|hqdefault|sddefault)\.jpg/, '/maxresdefault.jpg');
            hqRes = thumb.replace(/\/(default|mqdefault|sddefault|maxresdefault)\.jpg/, '/hqdefault.jpg');
        }

        const img = new Image();
        img.onload = () => {
            const finalSrc = (img.width > 120) ? maxRes : hqRes;
            if (art) { art.src = finalSrc; art.style.display = 'block'; }
            if (bg) bg.style.backgroundImage = `url(${finalSrc})`;
            if (fallback) fallback.style.display = 'none';
        };
        img.onerror = () => {
            if (art) { art.src = hqRes; art.style.display = 'block'; }
            if (bg) bg.style.backgroundImage = `url(${hqRes})`;
            if (fallback) fallback.style.display = 'none';
        };
        img.src = maxRes;
    } else if (thumb) {
        if (art) { art.src = thumb; art.style.display = 'block'; }
        if (bg) bg.style.backgroundImage = `url(${thumb})`;
        if (fallback) fallback.style.display = 'none';
    } else {
        if (art) art.style.display = 'none';
        if (bg) bg.style.backgroundImage = 'none';
        if (fallback) fallback.style.display = 'flex';
    }
}

// playTrack関数をグローバルに公開
window.playTrack = playTrack;
