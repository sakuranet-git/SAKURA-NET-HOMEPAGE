/* 
 * SAKURA MUSIC - YouTube Handler
 * YouTube IFrame Player API & Google API Integration
 */

const YouTubeHandler = {
    player: null,
    isReady: false,

    /**
     * YouTube IFrame API の初期化
     */
    init() {
        // IFrame API スクリプトを非同期で読み込み
        if (!window.YT) {
            const tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            const firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        }

        window.onYouTubeIframeAPIReady = () => {
            console.log('YouTube IFrame API Ready');
            this.createPlayer();
        };
    },

    /**
     * プレイヤーの作成 (隠し IFrame)
     */
    createPlayer() {
        // プレイヤーを表示するためのコンテナが HTML に必要
        const container = document.createElement('div');
        container.id = 'yt-player-container';
        // v1.4.13: display: none だとモバイルブラウザで再生がブロックされることがあるため、
        // 物理的に配置しつつ見えないように設定
        container.style.position = 'fixed';
        container.style.top = '-1000px';
        container.style.left = '-1000px';
        container.style.width = '1px';
        container.style.height = '1px';
        container.style.opacity = '0';
        container.style.pointerEvents = 'none';
        document.body.appendChild(container);

        this.player = new YT.Player('yt-player-container', {
            height: '0',
            width: '0',
            events: {
                'onReady': () => {
                    this.isReady = true;
                    console.log('YouTube Player Ready');
                },
                'onStateChange': (event) => {
                    // 再生状態の変化を検知
                    console.log('YouTube Player State Changed:', event.data);
                    window.dispatchEvent(new CustomEvent('ytPlayerStateChange', { detail: event.data }));

                    // 0 = 終了, 1 = 再生中, 2 = 一時停止, 3 = バッファリング中, 5 = 頭出し済み
                    if (event.data === 0) {
                        console.log('YouTube playback ended, triggering next track...');
                        // 再生終了時に次の曲へ（グローバル関数を呼び出す）
                        if (typeof window.playNextTrack === 'function') {
                            window.playNextTrack();
                        }
                    }
                }
            }
        });
    },

    /**
     * Google ログイン (YouTube Data API 用)
     * @param {string} clientId - Google Cloud Console で作成した Client ID
     */
    async login(clientId) {
        if (window.location.protocol === 'file:') {
            alert('【重要】Google ログイン（OAuth）はセキュリティ上の理由により、ファイルを直接開いた状態（file://）では動作しません。\n\n解決するには：\n1. VSCode の Live Server などのローカルサーバーを使用する\n2. または、サーバー（https://sakuranet-co.jp 等）にアップロードして確認する\n\n※検索機能（ゲストモード）は今のままでも利用可能です。');
            return;
        }

        if (typeof google === 'undefined') {
            console.error('Google SDK not loaded');
            alert('Google SDK が読み込まれていません。通信状況を確認してください。');
            return;
        }

        try {
            console.log('Initiating Google OAuth with Client ID:', clientId);
            console.log('Current Origin:', window.location.origin);

            const client = google.accounts.oauth2.initTokenClient({
                client_id: clientId,
                scope: 'https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtube',
                prompt: 'select_account',
                callback: (response) => {
                    if (response.error) {
                        console.error('OAuth Error Response:', response);
                        alert(`Google ログインエラー: ${response.error}\n詳細: ${response.error_description || 'なし'}`);
                        return;
                    }
                    if (response.access_token) {
                        console.log('Google Access Token acquired');
                        localStorage.setItem('yt_access_token', response.access_token);
                        alert('ログインに成功しました！プレミアムアカウントのライブラリにアクセス可能です。');
                        // サーバーの誤ったリダイレクトによる404を防ぐため、安全にリロードする
                        window.location.reload();
                    }
                },
            });
            client.requestAccessToken();
        } catch (err) {
            console.error('Core Login Exception:', err);
            alert('ログイン処理中に例外が発生しました。ブラウザのコンソール(F12)で詳細を確認してください。');
        }
    },

    /**
     * トークンの有効性を確認し、必要に応じてサイレントログイン（prompt: 'none'）を試行する
     * v1.4.35 追加
     */
    async checkLoginStatus(clientId) {
        return new Promise((resolve) => {
            if (typeof google === 'undefined') {
                resolve(false);
                return;
            }

            try {
                const client = google.accounts.oauth2.initTokenClient({
                    client_id: clientId,
                    scope: 'https://www.googleapis.com/auth/youtube.readonly https://www.googleapis.com/auth/youtube',
                    prompt: 'none', // ユーザーに許可を求めずバックグラウンドで試行
                    callback: (response) => {
                        if (response.error) {
                            console.warn('Silent Login Failed:', response.error);
                            resolve(false);
                            return;
                        }
                        if (response.access_token) {
                            console.log('Silent Login Successfully: New Access Token acquired');
                            localStorage.setItem('yt_access_token', response.access_token);
                            resolve(true);
                        }
                    },
                });
                client.requestAccessToken();
            } catch (err) {
                console.error('CheckLoginStatus Exception:', err);
                resolve(false);
            }
        });
    },

    // YouTube データの取得 (Guest Mode / Data API)
    async search(query, apiKey) {
        if (!apiKey) {
            console.warn('YouTube API Key is missing for search.');
            return [];
        }
        try {
            const url = `https://www.googleapis.com/youtube/v3/search?part=snippet&q=${encodeURIComponent(query)}&type=video&videoCategoryId=10&maxResults=10&key=${apiKey}`;
            const response = await fetch(url);
            const data = await response.json();
            return data.items || [];
        } catch (err) {
            console.error('YouTube Search Error:', err);
            return [];
        }
    },

    /**
     * ユーザーのプレイリスト（ライブラリ）を取得
     */
    async getLibrary() {
        const token = localStorage.getItem('yt_access_token');
        if (!token) return [];

        try {
            console.log('Fetching Library...');
            let results = [];

            // 1. まずは「高く評価した動画 (LL)」を直接取得してみる
            // チャンネルを作成していなくても楽曲を好評価していれば存在します
            try {
                const likedUrl = `https://www.googleapis.com/youtube/v3/playlists?part=snippet,contentDetails&id=LL`;
                const likedRes = await fetch(likedUrl, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const likedData = await likedRes.json();
                console.log('Direct LL Check:', likedData);
                if (likedData.items && likedData.items.length > 0) {
                    results.push({
                        ...likedData.items[0],
                        isSystem: true
                    });
                }
            } catch (e) {
                console.warn('System playlist fetch failed:', e);
            }

            // 2. チャンネル情報を取得（あれば「アップロード動画」などを追加）
            try {
                const chanUrl = `https://www.googleapis.com/youtube/v3/channels?part=contentDetails,snippet&mine=true`;
                const chanRes = await fetch(chanUrl, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                const chanData = await chanRes.json();
                if (chanData.items && chanData.items.length > 0) {
                    const related = chanData.items[0].contentDetails.relatedPlaylists;
                    if (related.uploads && !results.find(r => r.id === related.uploads)) {
                        results.push({
                            id: related.uploads,
                            isSystem: true,
                            snippet: {
                                title: 'アップロード動画',
                                thumbnails: chanData.items[0].snippet.thumbnails
                            }
                        });
                    }
                }
            } catch (e) {
                console.warn('Channel info fetch failed:', e);
            }

            // 3. ユーザーが作成したプレイリスト一覧を取得
            const playlistUrl = `https://www.googleapis.com/youtube/v3/playlists?part=snippet,contentDetails&mine=true&maxResults=50`;
            const plResponse = await fetch(playlistUrl, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const plData = await plResponse.json();
            console.log('User Playlists Response:', plData);

            // デバッグ用にグローバル変数に保存（app.jsで拾う用）
            window.__yt_last_playlist_response = plData;

            if (plData.items) {
                // 重複除去
                plData.items.forEach(item => {
                    if (!results.find(r => r.id === item.id)) {
                        results.push(item);
                    }
                });
            }

            return results;
        } catch (err) {
            console.error('YouTube Library Fetch Error:', err);
            window.__yt_last_playlist_error = err.toString();
            return [];
        }
    },

    /**
     * 指定したプレイリスト内のアイテムを取得
     */
    async getPlaylistItems(playlistId) {
        const token = localStorage.getItem('yt_access_token');
        if (!token) return [];

        try {
            const url = `https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=${playlistId}&maxResults=50`;
            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const data = await response.json();
            return data.items || [];
        } catch (err) {
            console.error('YouTube PlaylistItems Error:', err);
            return [];
        }
    },

    /**
     * 楽曲の再生
     */
    play(videoId) {
        if (!this.player || !this.isReady) return;
        try {
            this.player.loadVideoById(videoId);
            this.player.playVideo();
            const volSlider = document.getElementById('volume-slider');
            if (volSlider) this.player.setVolume(volSlider.value);
        } catch (e) {
            console.error('YouTube Play Error:', e);
        }
    },

    /**
     * モバイルブラウザの再生制限を解除するための「空再生」
     * YouTube プレイヤーのみに作用し、Drive 側の Audio コンテキストを奪わないように設定
     */
    unlock() {
        if (!this.player || !this.isReady) return;
        try {
            // 他の再生 (Driveなど) が動いている可能性があるため、
            // 自身の状態のみを安全に変更する。
            const state = this.player.getPlayerState();
            if (state !== 1 && state !== 3) { // 1: playing, 3: buffering 以外なら
                // v1.4.35: ミュート状態を極小化
                this.player.mute();
                this.player.playVideo();
                setTimeout(() => {
                    if (this.player && typeof this.player.pauseVideo === 'function') {
                        this.player.pauseVideo();
                        this.player.unMute();
                    }
                }, 200);
            }
        } catch (e) {
            console.warn('YouTube Unlock Failed:', e);
        }
    },

    pause() {
        if (this.player && this.isReady) {
            this.player.pauseVideo();
        }
    },

    resume() {
        if (this.player && this.isReady) {
            this.player.playVideo();
        }
    },

    setVolume(volume) {
        if (this.player && this.isReady) {
            this.player.setVolume(volume);
        }
    },

    getVolume() {
        if (this.player && this.isReady) {
            return this.player.getVolume();
        }
        return 100;
    },

    getCurrentTime() {
        if (this.player && this.isReady && this.player.getCurrentTime) {
            const time = this.player.getCurrentTime();
            // console.log('[YouTubeHandler] getCurrentTime:', time);
            return time;
        }
        return 0;
    },

    getDuration() {
        if (this.player && this.isReady && this.player.getDuration) {
            const duration = this.player.getDuration();
            // console.log('[YouTubeHandler] getDuration:', duration);
            return duration;
        }
        return 0;
    },

    seekTo(seconds) {
        if (this.player && this.isReady) {
            this.player.seekTo(seconds, true);
        }
    }
};

