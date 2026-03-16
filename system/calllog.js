document.addEventListener('DOMContentLoaded', () => {
    // --- Firebase Initialization ---
    let db;
    async function initFirebase() {
        try {
            if (typeof firebase !== 'undefined') {
                const firebaseConfig = {
                    apiKey: "AIzaSyD-IoqRej9KOCWDYy8W7InW3qpu6xxbW8Y",
                    authDomain: "sakura-net-db.firebaseapp.com",
                    projectId: "sakura-net-db",
                    storageBucket: "sakura-net-db.firebasestorage.app",
                    messagingSenderId: "764550859520",
                    appId: "1:764550859520:web:3a009815df3c95aa3041e5"
                };
                if (!firebase.apps.length) {
                    firebase.initializeApp(firebaseConfig);
                }
                db = firebase.firestore();
                console.log("🔥 Firestore initialized successfully");
                return true;
            } else {
                console.error("❌ Firebase SDK not found. Sync will not work.");
                setSyncStatus('error', 'SDK Missing');
                return false;
            }
        } catch (e) {
            console.error("❌ Firebase initialization error:", e);
            setSyncStatus('error', e.message);
            return false;
        }
    }

    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('csv-file');
    const dashboard = document.getElementById('dashboard');
    const fileListPanel = document.getElementById('file-list-panel');
    const fileListUl = document.getElementById('file-list');
    const tableBody = document.querySelector('#detail-table tbody');
    const themeToggle = document.getElementById('theme-toggle');
    const searchInput = document.getElementById('search-input');
    const searchSummaryEl = document.getElementById('search-summary');
    const monthSelectEl = document.getElementById('month-select');
    const sortableHeaders = document.querySelectorAll('th.sortable');
    const clearDataBtn = document.getElementById('clear-data');
    const exportCsvBtn = document.getElementById('export-csv');
    const exportXlsxBtn = document.getElementById('export-xlsx');

    const totalCostEl = document.getElementById('total-cost');
    const totalDurationEl = document.getElementById('total-duration');
    const totalCountEl = document.getElementById('total-count');
    const fileDateEl = document.getElementById('file-date');

    // 管理データ
    let filesMap = new Map();
    let deletedKeys = new Set(); // 個別削除されたレコードのキー
    let phoneNumberToName = new Map(); // 電話番号と氏名の対応表 { phoneNumber: name }
    let searchTerm = "";
    let selectedMonth = "all"; // 現在選択中の月 (例: "2026/01")
    let sortConfig = { key: 'date', order: 'desc' };
    let currentDisplayData = [];

    // --- Firestore Sync Logic ---
    let firestoreInitialLoad = false;

    /** 同期ステータスの表示更新 */
    function setSyncStatus(status, message = '') {
        const syncDot = document.getElementById('syncDot');
        const syncLabel = document.getElementById('syncLabel');
        if (!syncDot || !syncLabel) return;
        
        switch (status) {
            case 'synced':
                syncLabel.innerHTML = '同期済み';
                syncDot.style.backgroundColor = '#4caf50'; // Green
                break;
            case 'saving':
                syncLabel.innerHTML = '保存中...';
                syncDot.style.backgroundColor = '#ff9800'; // Orange
                break;
            case 'error':
                syncLabel.innerHTML = '同期エラー';
                syncDot.style.backgroundColor = '#f44336'; // Red
                if (message) console.error('Sync Error:', message);
                break;
            default:
                syncLabel.innerHTML = '接続中...';
                syncDot.style.backgroundColor = '#bbb'; // Grey
        }
    }

    /** データを Firestore へ保存する（デバウンス付き） */
    let saveTimeout = null;
    async function syncData() {
        setSyncStatus('saving');
        
        // 1. ローカルにはフルデータを保存（高速表示のため）
        const dataForLocal = {
            files: Array.from(filesMap.entries()),
            deleted: Array.from(deletedKeys),
            names: Array.from(phoneNumberToName.entries())
        };
        localStorage.setItem('sakura_call_data', JSON.stringify(dataForLocal));

        // Firestore 保存（デバウンス1秒）
        if (saveTimeout) clearTimeout(saveTimeout);
        return new Promise((resolve) => {
            saveTimeout = setTimeout(async () => {
                try {
                    if (!db) {
                        console.warn('Database (db) is not initialized yet.');
                        return resolve();
                    }

                    // --- [新アーキテクチャ] Firestore ドキュメント分割保存 ---
                    // ファイルごとに個別のドキュメントとして保存することで 1MB 制限を回避。
                    
                    const batch = db.batch();
                    const fileCatalog = [];

                    // a) 各ファイルを個別に保存 (`calllog_files` コレクション)
                    for (const [filename, logs] of filesMap.entries()) {
                        const fileDocRef = db.collection('calllog_files').doc(filename.replace(/\//g, '_'));
                        // ファイル内容（ログのキーなどの軽量データ、または将来的にフルデータ）
                        // ここでは「軽量化」を維持しつつ分割する
                        const compressedLogs = logs.map(l => ({
                            key: l.key,
                            d: l.date || '',
                            t: l.time || '',
                            fn: l.fromName || '',
                            fnm: l.fromNum || l.from || '',
                            tn: l.toName || '',
                            tnm: l.toNum || l.to || '',
                            dur: l.duration || 0,
                            p: l.price || l.cost || 0,
                            c: l.caf || ''
                        }));
                        
                        batch.set(fileDocRef, { logs: compressedLogs, updatedAt: firebase.firestore.FieldValue.serverTimestamp() });
                        fileCatalog.push({ name: filename, count: logs.length });
                    }

                    // b) メタ情報（目次）を保存 (`calllog_data/main`)
                    const mainDocRef = db.collection('calllog_data').doc('main');
                    const mainData = {
                        catalog: fileCatalog,
                        deleted: Array.from(deletedKeys),
                        // Firestore は [[k,v], ...] という入れ子の配列をサポートしていないため、
                        // [{ num, name }, ...] というオブジェクトの配列に変換する
                        names: Array.from(phoneNumberToName.entries()).map(([num, name]) => ({ num, name })),
                        updatedAt: firebase.firestore.FieldValue.serverTimestamp(),
                        version: 'v2'
                    };

                    batch.set(mainDocRef, mainData);

                    // バッチ実行（最大500件まで。今はファイル数がそこまで多くない前提）
                    await batch.commit();
                    
                    console.log('✅ Synchronized with split Firestore documents.');
                    setSyncStatus('synced');
                    resolve();
                } catch (e) {
                    console.error('❌ Sync failed:', e);
                    setSyncStatus('error', e.message);
                    resolve();
                }
            }, 1000);
        });
    }

    function setupFirestoreSync() {
        setSyncStatus('connecting');

        // まずメタデータ（目次）を監視
        const mainDocRef = db.collection('calllog_data').doc('main');
        
        mainDocRef.onSnapshot(async (doc) => {
            if (doc.exists) {
                const data = doc.data();
                console.log('📦 Firestore Metadata received:', data);
                
                if (data.deleted) deletedKeys = new Set(data.deleted);
                if (data.names) {
                    // 新形式 (v2: オブジェクト配列) と旧形式 (v1: 入れ子配列) の両方に対応
                    if (data.names.length > 0 && !Array.isArray(data.names[0])) {
                        phoneNumberToName = new Map(data.names.map(entry => [entry.num, entry.name]));
                    } else {
                        phoneNumberToName = new Map(data.names);
                    }
                }

                // v2 (分割保存) 形式の場合
                if (data.version === 'v2' && data.catalog) {
                    const newFilesMap = new Map();
                    
                    for (const item of data.catalog) {
                        const filename = item.name;
                        // 最適化: メモリにあり、件数が一致すればスキップ
                        if (filesMap.has(filename) && filesMap.get(filename).length === item.count) {
                            newFilesMap.set(filename, filesMap.get(filename));
                            continue;
                        }

                        // 個別ドキュメントを取得
                        try {
                            const fileDoc = await db.collection('calllog_files').doc(filename.replace(/\//g, '_')).get();
                            if (fileDoc.exists) {
                                const fileData = fileDoc.data();
                                const restoredLogs = fileData.logs.map(l => ({
                                    key: l.key, 
                                    date: l.d || '', 
                                    time: l.t || '', 
                                    fromName: l.fn || '', 
                                    fromNum: l.fnm || '',
                                    from: l.fnm || '', // from フィールドも補完
                                    toName: l.tn || '', 
                                    toNum: l.tnm || '',
                                    to: l.tnm || '',   // to フィールドも補完
                                    duration: l.dur || 0, 
                                    price: l.p || 0, 
                                    cost: l.p || 0,    // cost フィールドも補完
                                    caf: l.c || ''
                                }));
                                newFilesMap.set(filename, restoredLogs);
                            }
                        } catch (err) {
                            console.warn(`Failed to fetch file: ${filename}`, err);
                        }
                    }
                    filesMap = newFilesMap;
                } else if (data.files) {
                    // 旧形式 (v1)
                    filesMap = new Map(data.files);
                }
                
                localStorage.setItem('calllog_fs_migration_done', 'true');
                updateAppDisplay();
                setSyncStatus('synced');
                firestoreInitialLoad = true;
            } else {
                // Firestore が空の場合、移行が必要か確認
                const migrationDone = localStorage.getItem('calllog_fs_migration_done');
                if (!migrationDone) {
                    const saved = localStorage.getItem('sakura_call_data');
                    if (saved) {
                        try {
                            const localData = JSON.parse(saved);
                            if (localData.files && localData.files.length > 0) {
                                console.log('Starting migration (split) to Firestore...');
                                setTimeout(() => syncData(), 500); 
                            }
                        } catch (e) { console.error('Migration error:', e); }
                    }
                    localStorage.setItem('calllog_fs_migration_done', 'true');
                }
                firestoreInitialLoad = true;
                setSyncStatus('synced');
            }
        }, (error) => {
            console.error('Firestore Snapshot Error:', error);
            setSyncStatus('error', error.message);
            if (error.code === 'permission-denied') {
                setTimeout(() => setupFirestoreSync(), 10000);
            }
        });
    }

    // 起動時の初期化：Firestore 同期を開始
    const syncIndicator = document.getElementById('syncIndicator');
    if (syncIndicator) {
        syncIndicator.addEventListener('click', () => {
            setupFirestoreSync(); // 手動再接続
        });
    }

    if (clearDataBtn) {
        clearDataBtn.addEventListener('click', async () => {
            if (confirm('すべての読み込み済みデータと編集内容を【全デバイスから】完全に消去しますか？')) {
                setSyncStatus('saving');
                try {
                    await db.collection('calllog_data').doc('main').delete();
                    localStorage.removeItem('sakura_call_data');
                    localStorage.removeItem('calllog_fs_migration_done');
                    filesMap.clear();
                    deletedKeys.clear();
                    phoneNumberToName.clear();
                    selectedMonth = "all";
                    updateAppDisplay();
                    setSyncStatus('synced');
                } catch (e) {
                    setSyncStatus('error', e.message);
                    alert('消去中にエラーが発生しました。');
                }
            }
        });
    }

    // --- Theme Logic ---
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    themeToggle.addEventListener('click', () => {
        const theme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    });

    // --- File Handling ---
    const preventDefaults = (e) => { e.preventDefault(); e.stopPropagation(); };
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(n => dropZone.addEventListener(n, preventDefaults));
    dropZone.addEventListener('dragover', () => dropZone.classList.add('dragover'));
    ['dragleave', 'drop'].forEach(n => dropZone.addEventListener(n, () => dropZone.classList.remove('dragover')));

    dropZone.addEventListener('drop', (e) => {
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });

    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFiles(files);
        fileInput.value = '';
    });

    async function handleFiles(files) {
        const csvFiles = files.filter(f => f.name.endsWith('.csv'));
        let totalNewRecords = 0;
        let duplicateFileNames = [];
        let processedFileCount = 0;

        // 既存の全レコードのキーを取得（重複チェック用）
        const existingKeys = new Set();
        filesMap.forEach(logs => {
            logs.forEach(log => existingKeys.add(log.key));
        });

        for (const file of csvFiles) {
            try {
                console.log(`--- Starting import for file: ${file.name} ---`);
                const text = await readFileAsText(file);
                if (!text || text.trim().length === 0) {
                    console.warn(`File ${file.name} is empty.`);
                    continue;
                }
                
                const logs = parseCSV(text);
                console.log(`File: ${file.name}, total records parsed: ${logs.length}`);

                if (logs.length === 0) {
                    console.warn(`No valid records found in ${file.name}. Check CSV format.`);
                    continue;
                }

                // 既存のレコードと重複しないものだけを抽出
                const newLogs = logs.filter(log => !existingKeys.has(log.key));
                console.log(`New unique records: ${newLogs.length} (total in file: ${logs.length})`);

                if (newLogs.length > 0) {
                    if (filesMap.has(file.name)) {
                        const currentLogs = filesMap.get(file.name);
                        filesMap.set(file.name, [...currentLogs, ...newLogs]);
                    } else {
                        filesMap.set(file.name, newLogs);
                    }

                    totalNewRecords += newLogs.length;
                    newLogs.forEach(log => existingKeys.add(log.key));
                    processedFileCount++;
                } else {
                    duplicateFileNames.push(file.name);
                }
            } catch (err) { 
                console.error(`❌ Critical error processing file (${file.name}):`, err);
                alert(`ファイル ${file.name} の処理中にエラーが発生しました: ${err.message}`);
            }
        }

        if (totalNewRecords > 0) {
            let msg = `${totalNewRecords}件の新しい明細を追加しました。`;
            if (duplicateFileNames.length > 0) {
                msg += `\n以下のファイルは既にインポート済みか、新しい明細が含まれていなかったためスキップされました：\n${duplicateFileNames.join('\n')}`;
            }
            alert(msg);
        } else if (duplicateFileNames.length > 0) {
            alert(`選択されたファイル（${duplicateFileNames.length}個）のデータはすべて既にインポート済みです。\n\nもし一覧に何も表示されない場合は、画面右上の「データを全消去」を一度行い、再度ドロップしてみてください。`);
        } else {
            alert('有効なCSVデータが見つかりませんでした。通話明細(c040R7)の形式か確認してください。');
        }

        console.log('Starting final synchronization...');
        await syncData();
        console.log('Updating display...');
        updateAppDisplay();
    }

    function readFileAsText(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.readAsText(file, 'Shift-JIS');
        });
    }

    function parseCSV(csvText) {
        const cleanText = csvText.replace(/^\uFEFF/, '');
        const lines = cleanText.split(/\r?\n/).filter(l => l.trim() !== '');
        if (lines.length === 0) return [];

        const logs = [];
        const headerLine = lines[0];
        // 判定条件を緩和しつつ、確実に検知する
        const isExported = headerLine.includes('通話日') || (headerLine.includes('発信元') && headerLine.includes('着信先'));
        
        console.log(`CSV Format detected: ${isExported ? 'Exported/Standard' : 'Original/Raw'}`);
        if (isExported) {
            // エクスポート形式のパース
            for (let i = 1; i < lines.length; i++) {
                const line = lines[i];
                // CSV対応の分割ロジック（引用符内のカンマを保護）
                const cols = [];
                let current = '';
                let inQuotes = false;
                for (let j = 0; j < line.length; j++) {
                    const char = line[j];
                    if (char === '"') inQuotes = !inQuotes;
                    else if (char === ',' && !inQuotes) {
                        cols.push(current);
                        current = '';
                    } else {
                        current += char;
                    }
                }
                cols.push(current);

                if (cols.length < 8) continue;

                // 値のクリーンアップ
                const clean = (val) => val.trim().replace(/^['"]|['"]$/g, '');

                const date = clean(cols[0]);
                const time = clean(cols[1]);
                const fromName = clean(cols[2]);
                const fromNum = clean(cols[3]);
                const toName = clean(cols[4]);
                const toNum = clean(cols[5]);
                const duration = parseFloat(cols[6]);
                const cost = parseInt(cols[7], 10);
                const caf = cols[8] ? clean(cols[8]) : '';

                // オリジナルのキーを再現
                const dateRaw = date.replace(/\//g, '');
                const timeRaw = time.trim();
                const key = `${dateRaw}_${timeRaw}_${fromNum}_${toNum}`;

                logs.push({ caf, date, time, from: fromNum, to: toNum, duration, cost, key });

                // 電話帳を更新
                if (fromName) phoneNumberToName.set(fromNum, fromName);
                if (toName) phoneNumberToName.set(toNum, toName);
            }
        } else {
            // オリジナル形式
            const clean = (val) => val ? val.trim().replace(/^['"]|['"]$/g, '') : '';
            lines.forEach(line => {
                const cols = line.split(',');
                if (cols[0] === '1') {
                    const caf = cols[2] ? clean(cols[2]) : '';
                    const rawDate = clean(cols[5]);
                    const time = clean(cols[6]);
                    const fromNum = clean(cols[7]);
                    const toNum = clean(cols[8]);
                    const duration = parseFloat(cols[12]) / 10;
                    const cost = parseInt(cols[13], 10);
                    const key = `${rawDate}_${time}_${fromNum}_${toNum}`;

                    logs.push({
                        caf,
                        date: formatRawDate(rawDate),
                        time,
                        from: fromNum,
                        to: toNum,
                        duration,
                        cost,
                        key
                    });
                }
            });
        }
        return logs;
    }

    function formatRawDate(raw) {
        if (!raw || raw.length < 8) return raw;
        return `${raw.substring(0, 4)}/${raw.substring(4, 6)}/${raw.substring(6, 8)}`;
    }

    function formatPhoneNumberForSearch(num) {
        if (!num) return num;
        const clean = num.replace(/[^\d]/g, '');
        if (clean.startsWith('06') && clean.length === 10) return `${clean.substring(0, 2)}-${clean.substring(2, 6)}-${clean.substring(6)}`;
        if (clean.startsWith('0') && clean.length === 10) return `${clean.substring(0, 3)}-${clean.substring(3, 6)}-${clean.substring(6)}`;
        if (clean.length === 11) return `${clean.substring(0, 3)}-${clean.substring(3, 7)}-${clean.substring(7)}`;
        return num;
    }

    // --- Sorting Logic ---
    sortableHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const key = header.dataset.sort;
            if (sortConfig.key === key) {
                sortConfig.order = sortConfig.order === 'asc' ? 'desc' : 'asc';
            } else {
                sortConfig.key = key;
                sortConfig.order = 'asc';
            }
            updateSortUI();
            processAndDisplayData();
        });
    });

    function updateSortUI() {
        sortableHeaders.forEach(h => {
            h.classList.remove('active-sort');
            h.removeAttribute('data-order');
        });
        const active = Array.from(sortableHeaders).find(h => h.dataset.sort === sortConfig.key);
        if (active) {
            active.classList.add('active-sort');
            active.setAttribute('data-order', sortConfig.order);
        }
    }

    // --- Core Data Processing ---
    function updateAppDisplay() {
        const hasData = filesMap.size > 0;
        fileListPanel.classList.toggle('hidden', !hasData);
        dashboard.classList.toggle('hidden', !hasData);

        if (hasData) {
            updateMonthSelector();
            renderFileList();
            processAndDisplayData();
        }
    }

    function updateMonthSelector() {
        const months = new Set();
        filesMap.forEach(logs => {
            logs.forEach(log => {
                if (log.date) {
                    const monthKey = log.date.substring(0, 7); // "YYYY/MM"
                    months.add(monthKey);
                }
            });
        });

        const sortedMonths = Array.from(months).sort().reverse();
        const currentValue = monthSelectEl.value;

        monthSelectEl.innerHTML = '<option value="all">すべて表示 (合算)</option>';
        sortedMonths.forEach(m => {
            const option = document.createElement('option');
            option.value = m;
            option.textContent = m.replace('/', '年') + '月分';
            monthSelectEl.appendChild(option);
        });

        if (Array.from(monthSelectEl.options).some(o => o.value === currentValue)) {
            monthSelectEl.value = currentValue;
            selectedMonth = currentValue;
        } else {
            selectedMonth = monthSelectEl.value;
        }
    }

    monthSelectEl.addEventListener('change', (e) => {
        selectedMonth = e.target.value;
        processAndDisplayData();
    });

    function renderFileList() {
        fileListUl.innerHTML = '';
        filesMap.forEach((logs, filename) => {
            const li = document.createElement('li');
            li.className = 'file-item';
            li.innerHTML = `
                <div class="file-info">📄 <span>${filename}</span> <small>(${logs.length}件)</small></div>
                <button class="btn-danger">×</button>
            `;
            li.querySelector('.btn-danger').onclick = async () => {
                filesMap.delete(filename);
                await syncData();
                updateAppDisplay();
            };
            fileListUl.appendChild(li);
        });
    }

    function processAndDisplayData() {
        const unifiedMap = new Map();
        filesMap.forEach(logs => {
            logs.forEach(log => {
                if (!unifiedMap.has(log.key) && !deletedKeys.has(log.key)) {
                    const logMonth = log.date.substring(0, 7);
                    if (selectedMonth === 'all' || logMonth === selectedMonth) {
                        const fromName = phoneNumberToName.get(log.from) || "";
                        const toName = phoneNumberToName.get(log.to) || "";
                        unifiedMap.set(log.key, { ...log, fromName, toName });
                    }
                }
            });
        });

        let data = Array.from(unifiedMap.values());

        if (searchTerm) {
            data = data.filter(log =>
                log.from.includes(searchTerm) ||
                log.to.includes(searchTerm) ||
                log.fromName.includes(searchTerm) ||
                log.toName.includes(searchTerm) ||
                log.date.includes(searchTerm)
            );
            const searchCost = data.reduce((sum, log) => sum + log.cost, 0);
            searchSummaryEl.textContent = `検索結果: ${data.length}件 / 合計 ¥${searchCost.toLocaleString()}`;
            searchSummaryEl.classList.remove('hidden');
        } else {
            searchSummaryEl.classList.add('hidden');
        }

        data.sort((a, b) => {
            let valA = a[sortConfig.key];
            let valB = b[sortConfig.key];
            if (sortConfig.key === 'date') {
                valA = valA + a.time;
                valB = valB + b.time;
            }
            if (valA < valB) return sortConfig.order === 'asc' ? -1 : 1;
            if (valA > valB) return sortConfig.order === 'asc' ? 1 : -1;
            return 0;
        });

        currentDisplayData = data;
        renderTable(data);
        updateStats(data);
    }

    function renderTable(logs) {
        tableBody.innerHTML = '';
        logs.forEach(log => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${log.date}</td>
                <td class="name-cell">
                    <div class="editable" contenteditable="true" data-num="${log.from}">${log.fromName}</div>
                    <button class="btn-search-mini from-search" title="発信元をWeb検索">🔍</button>
                </td>
                <td title="CAF: ${log.caf}">${log.from}</td>
                <td class="name-cell">
                    <div class="editable" contenteditable="true" data-num="${log.to}">${log.toName}</div>
                    <button class="btn-search-mini to-search" title="相手先をWeb検索">🔍</button>
                </td>
                <td>${log.to}</td>
                <td>${log.duration.toFixed(1)}s</td>
                <td>¥${log.cost.toLocaleString()}</td>
                <td><button class="btn-delete-row" title="この明細を削除">🗑️</button></td>
            `;

            tr.querySelectorAll('.editable').forEach(cell => {
                cell.addEventListener('blur', (e) => {
                    const newName = e.target.innerText.trim();
                    const num = e.target.dataset.num;
                    if (newName) phoneNumberToName.set(num, newName);
                    else phoneNumberToName.delete(num);
                    syncData();
                    document.querySelectorAll(`.editable[data-num="${num}"]`).forEach(c => { c.innerText = newName; });
                });
            });

            tr.querySelector('.from-search').onclick = () => {
                window.open(`https://www.google.com/search?q=${formatPhoneNumberForSearch(log.from)}+%E9%9B%BB%E8%A9%B1%E7%95%AA%E5%8F%B7`, '_blank');
            };
            tr.querySelector('.to-search').onclick = () => {
                window.open(`https://www.google.com/search?q=${formatPhoneNumberForSearch(log.to)}+%E9%9B%BB%E8%A9%B1%E7%95%AA%E5%8F%B7`, '_blank');
            };
            tr.querySelector('.btn-delete-row').onclick = async () => {
                deletedKeys.add(log.key);
                await syncData();
                processAndDisplayData();
            };
            tableBody.appendChild(tr);
        });
    }

    function updateStats(logs) {
        const totalCost = logs.reduce((sum, log) => sum + log.cost, 0);
        const totalDuration = logs.reduce((sum, log) => sum + log.duration, 0);
        totalCostEl.textContent = `¥${totalCost.toLocaleString()}`;
        totalDurationEl.textContent = `${totalDuration.toFixed(1)} 秒`;
        totalCountEl.textContent = `${logs.length} 件`;
        if (logs.length > 0) {
            const sortedByDate = [...logs].sort((a, b) => a.date.localeCompare(b.date));
            fileDateEl.textContent = `${sortedByDate[0].date} 〜 ${sortedByDate[logs.length - 1].date}`;
        } else {
            fileDateEl.textContent = '-';
        }
    }

    function downloadFile(content, fileName, contentType) {
        const a = document.createElement("a");
        const file = new Blob([content], { type: contentType });
        a.href = URL.createObjectURL(file);
        a.download = fileName;
        a.click();
        URL.revokeObjectURL(a.href);
    }

    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', () => {
            if (currentDisplayData.length === 0) return;
            const headers = ["通話日", "通話時刻", "契約者(発信)", "発信元番号", "相手先名称", "着信先番号", "通話時間(秒)", "通話料金(円)", "CAF番号"];
            const rows = currentDisplayData.map(log => [
                log.date,
                log.time,
                `"${log.fromName}"`,
                `'${log.from}`,
                `"${log.toName}"`,
                `'${log.to}`,
                log.duration,
                log.cost,
                log.caf
            ]);
            const csvContent = "\uFEFF" + [headers, ...rows].map(e => e.join(",")).join("\n");
            downloadFile(csvContent, `通話明細_${new Date().toISOString().slice(0, 10)}.csv`, "text/csv;charset=utf-8;");
        });
    }

    if (exportXlsxBtn) {
        exportXlsxBtn.addEventListener('click', () => {
            try {
                if (currentDisplayData.length === 0) {
                    alert("出力する明細データがありません。");
                    return;
                }
                if (typeof XLSX === 'undefined') {
                    alert("Excel生成ライブラリ (SheetJS) が読み込めていません。\nインターネット接続を確認するか、ページを一度更新(F5)してください。");
                    return;
                }
                const headers = ["通話日", "通話時刻", "契約者(発信)", "発信元番号", "相手先名称", "着信先番号", "通話時間(秒)", "通話料金(円)", "CAF番号"];
                const rows = currentDisplayData.map(log => [
                    log.date || "",
                    log.time || "",
                    log.fromName || "",
                    log.from || "",
                    log.toName || "",
                    log.to || "",
                    log.duration || 0,
                    log.cost || 0,
                    log.caf || ""
                ]);

                const wsData = [headers, ...rows];
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet(wsData);

                // 列幅の自動調整 (簡易版)
                const colWidths = [
                    { wch: 12 }, { wch: 10 }, { wch: 20 }, { wch: 15 },
                    { wch: 20 }, { wch: 15 }, { wch: 12 }, { wch: 12 }, { wch: 15 }
                ];
                ws['!cols'] = colWidths;

                XLSX.utils.book_append_sheet(wb, ws, "通話明細");
                XLSX.writeFile(wb, `通話明細_${new Date().toISOString().slice(0, 10)}.xlsx`);
            } catch (error) {
                console.error("Excel出力エラー:", error);
                alert("Excelファイルの生成中にエラーが発生しました。\n" + error.message);
            }
        });
    }

    searchInput.addEventListener('input', (e) => {
        searchTerm = e.target.value.toLowerCase();
        processAndDisplayData();
    });

    updateSortUI();

    // 起動時の初期化：Firestore 同期を開始
    async function initializeApp() {
        // 1. Firebase の初期化を待つ
        const initialized = await initFirebase();
        if (!initialized) return;

        // 2. 表示を速くするため、まずキャッシュから読み込んでおく
        const saved = localStorage.getItem('sakura_call_data');
        if (saved) {
            try {
                const data = JSON.parse(saved);
                filesMap = new Map(data.files || []);
                deletedKeys = new Set(data.deleted || []);
                phoneNumberToName = new Map(data.names || []);
                updateAppDisplay();
            } catch (e) { console.error('Cache load error:', e); }
        }
        
        // 3. Firestore リアルタイム同期開始
        setupFirestoreSync();
    }

    initializeApp();

    // デバッグ用ヘルパー
    window.debugAppState = () => {
        console.log('--- App State Debug ---');
        console.log('filesMap size:', filesMap.size);
        console.log('filesMap filenames:', Array.from(filesMap.keys()));
        console.log('deletedKeys size:', deletedKeys.size);
        console.log('selectedMonth:', selectedMonth);
        console.log('searchTerm:', searchTerm);
        console.log('localStorage (migration_done):', localStorage.getItem('calllog_fs_migration_done'));
        console.log('localStorage (sakura_call_data) size:', localStorage.getItem('sakura_call_data')?.length || 0);
        console.log('--- End Debug ---');
    };
});
