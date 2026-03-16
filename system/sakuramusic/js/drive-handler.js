/**
 * SAKURA MUSIC - Google Drive Handler 
 * v1.0.66 - IndexedDB Audio Cache + Green Lamp Fix
 * - Drive 接続: ai.sakuranet@gmail.com の GAS URL を使用
 * - 楽曲キャッシュ: IndexedDB に Base64 音声を保存し高速再生
 */

const DriveHandler = {
    get gasUrl() {
        return localStorage.getItem('sakura_gas_url') || 'https://script.google.com/macros/s/AKfycbyLc7Q25g8GOlwAhl2hkJSreQqfQllKsFQ7eJ1vL_ARUAG6Gz6EpnCpVygGSBiWUx1D/exec';
    },

    get rootFolderId() {
        return localStorage.getItem('sakura_drive_folder_id') || '1ftI36zOUQ_snJEFTBqPbRbbxH18cLckS';
    },

    // メモリキャッシュ（ファイルリスト用）
    _cache: null,

    // IndexedDB の設定
    _IDB_NAME: 'sakura_music_cache',
    _IDB_STORE: 'audio_blobs',
    _IDB_VERSION: 1,

    /**
     * IndexedDB を開く
     */
    _openDB() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(this._IDB_NAME, this._IDB_VERSION);
            req.onupgradeneeded = (e) => {
                const db = e.target.result;
                if (!db.objectStoreNames.contains(this._IDB_STORE)) {
                    db.createObjectStore(this._IDB_STORE, { keyPath: 'fileId' });
                }
            };
            req.onsuccess = (e) => resolve(e.target.result);
            req.onerror = () => reject(req.error);
        });
    },

    /**
     * キャッシュ済みかどうかを確認
     */
    async isCached(fileId) {
        try {
            const db = await this._openDB();
            return new Promise((resolve) => {
                const tx = db.transaction(this._IDB_STORE, 'readonly');
                const req = tx.objectStore(this._IDB_STORE).count(fileId);
                req.onsuccess = () => resolve(req.result > 0);
                req.onerror = () => resolve(false);
            });
        } catch { return false; }
    },

    /**
     * キャッシュから BlobURL を取得
     */
    async getCachedBlobUrl(fileId) {
        try {
            const db = await this._openDB();
            return new Promise((resolve) => {
                const tx = db.transaction(this._IDB_STORE, 'readonly');
                const req = tx.objectStore(this._IDB_STORE).get(fileId);
                req.onsuccess = () => {
                    if (!req.result) return resolve(null);
                    const blob = new Blob([req.result.data], { type: req.result.mimeType });
                    resolve(URL.createObjectURL(blob));
                };
                req.onerror = () => resolve(null);
            });
        } catch { return null; }
    },

    /**
     * GAS から音声を取得して IndexedDB に保存
     */
    async cacheAudio(fileId, mimeType, existingDataBase64 = null) {
        try {
            console.log(`[DriveHandler] cacheAudio start: ${fileId}`);
            let base64Data = existingDataBase64;
            let finalMimeType = mimeType;

            if (!base64Data) {
                const url = new URL(this.gasUrl);
                url.searchParams.set('action', 'getStream');
                url.searchParams.set('fileId', fileId);

                const res = await fetch(url.toString(), { redirect: 'follow' });
                const result = await res.json();
                if (!result.success || !result.data) {
                    console.error('[DriveHandler] cacheAudio failed (GAS):', result);
                    return false;
                }
                base64Data = result.data;
                finalMimeType = result.mimeType || 'audio/mpeg';
            }

            // v1.0.68: メモリ効率の高いデコード
            const bytesScroll = await this._decodeToArrayBuffer(base64Data, finalMimeType);
            if (!bytesScroll) return false;

            const db = await this._openDB();
            return new Promise((resolve) => {
                const tx = db.transaction(this._IDB_STORE, 'readwrite');
                tx.objectStore(this._IDB_STORE).put({
                    fileId,
                    data: bytesScroll,
                    mimeType: finalMimeType || 'audio/mpeg',
                    cachedAt: Date.now()
                });
                tx.oncomplete = () => {
                    console.log(`[DriveHandler] cached successfully: ${fileId}`);
                    resolve(true);
                };
                tx.onerror = () => {
                    console.error('[DriveHandler] IndexedDB Error:', tx.error);
                    resolve(false);
                };
            });
        } catch (err) {
            console.error('[DriveHandler] cacheAudio failed:', err);
            return false;
        }
    },

    /**
     * Base64 を ArrayBuffer に高速デコード (native fetch 方式)
     */
    async _decodeToArrayBuffer(base64, mimeType) {
        try {
            const dataUri = `data:${mimeType || 'audio/mpeg'};base64,${base64}`;
            const res = await fetch(dataUri);
            return await res.arrayBuffer();
        } catch (e) {
            console.error('[DriveHandler] _decodeToArrayBuffer failed:', e);
            return null;
        }
    },

    /**
     * ファイル一覧取得（メモリキャッシュ付き）
     */
    async listFiles(forceRefresh = false) {
        if (!forceRefresh && this._cache) return this._cache;
        try {
            console.log('--- Drive List (GET Sync) ---');
            const url = new URL(this.gasUrl);
            url.searchParams.set('action', 'listFiles');
            url.searchParams.set('folderId', this.rootFolderId);
            url.searchParams.set('_t', Date.now());

            const res = await fetch(url.toString(), { redirect: 'follow' });
            const result = await res.json();

            if (result.success) {
                this._cache = result.files || [];
                console.log('Files Sync OK:', this._cache.length);
                return this._cache;
            } else {
                console.error('GAS Error:', result.message);
                return null;
            }
        } catch (err) {
            console.error('Connection failed:', err);
            return null;
        }
    },

    /**
     * アップロード（POST）
     */
    async uploadFile(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = async (e) => {
                const base64 = e.target.result.split(',')[1];
                const payload = JSON.stringify({
                    action: 'uploadFile',
                    folderId: this.rootFolderId,
                    fileName: file.name,
                    mimeType: file.type || 'application/octet-stream',
                    fileData: base64
                });
                try {
                    const res = await fetch(this.gasUrl, { method: 'POST', body: payload, redirect: 'follow' });
                    const result = await res.json();
                    this._cache = null;
                    resolve({ success: result.success });
                } catch { resolve({ success: false }); }
            };
            reader.readAsDataURL(file);
        });
    },

    /**
     * 削除（POST）
     */
    async deleteFile(fileId) {
        try {
            const payload = JSON.stringify({ action: 'deleteFile', fileId });
            const res = await fetch(this.gasUrl, { method: 'POST', body: payload, redirect: 'follow' });
            const result = await res.json();
            this._cache = null;
            return { success: result.success };
        } catch { return { success: false }; }
    },

    async fetchAudioStream(fileId) {
        try {
            console.log(`[DriveHandler] fetchAudioStream start: ${fileId}`);
            const url = new URL(this.gasUrl);
            url.searchParams.set('action', 'getStream');
            url.searchParams.set('fileId', fileId);

            const res = await fetch(url.toString(), { redirect: 'follow' });
            const result = await res.json();
            if (!result.success || !result.data) {
                console.error('[DriveHandler] fetchAudioStream error (GAS):', result);
                return null;
            }

            console.log(`[DriveHandler] Data acquired (${Math.round(result.data.length / 1024)} KB), decoding...`);

            // 同期的にキャッシュにも保存
            this.cacheAudio(fileId, result.mimeType, result.data);

            const buffer = await this._decodeToArrayBuffer(result.data, result.mimeType);
            if (!buffer) return null;

            const blob = new Blob([buffer], { type: result.mimeType || 'audio/mpeg' });
            return URL.createObjectURL(blob);
        } catch (err) {
            console.error('[DriveHandler] fetchAudioStream fatal error:', err);
            return null;
        }
    }
};
