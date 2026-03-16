document.addEventListener('DOMContentLoaded', () => {
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
    const contractCountEl = document.getElementById('contract-count');
    const totalCountEl = document.getElementById('total-count');
    const fileDateEl = document.getElementById('file-date');

    // 管理データ
    let filesMap = new Map();
    let deletedKeys = new Set(); // 個別削除されたレコードのキー
    let cafToName = new Map(); // CAF番号と氏名の対応表
    let searchTerm = "";
    let selectedMonth = "all"; // 現在選択中の月 (例: "2026/02")
    let sortConfig = { key: 'billingMonth', order: 'desc' };
    let currentDisplayData = [];

    // --- Persistence Logic ---
    async function saveToLocalStorage() {
        const data = {
            files: Array.from(filesMap.entries()),
            deleted: Array.from(deletedKeys),
            names: Array.from(cafToName.entries())
        };
        localStorage.setItem('sakura_usage_data', JSON.stringify(data));
        
        // サーバーにも同期
        await saveToServer(data);
    }

    async function loadFromLocalStorage() {
        // 1. まずローカルから読み込む (高速表示用)
        const saved = localStorage.getItem('sakura_usage_data');
        if (saved) {
            try {
                const data = JSON.parse(saved);
                filesMap = new Map(data.files);
                deletedKeys = new Set(data.deleted);
                cafToName = new Map(data.names || []);
                updateAppDisplay();
            } catch (e) { console.error('Local load error:', e); }
        }

        // 2. サーバーから最新情報を取得して統合
        await loadFromServer();
    }

    async function saveToServer(data) {
        try {
            const response = await fetch('save_usagelog.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error('サーバーへの保存に失敗しました');
        } catch (err) {
            console.error('Server sync error:', err);
        }
    }

    async function loadFromServer() {
        try {
            const response = await fetch('save_usagelog.php');
            if (!response.ok) return;
            const serverData = await response.json();
            
            if (serverData && serverData.files) {
                let changed = false;

                // サーバーデータをマージ (新しいデータがあれば更新)
                const serverFiles = new Map(serverData.files);
                serverFiles.forEach((logs, filename) => {
                    if (!filesMap.has(filename)) {
                        filesMap.set(filename, logs);
                        changed = true;
                    }
                });

                if (serverData.deleted) {
                    serverData.deleted.forEach(key => {
                        if (!deletedKeys.has(key)) {
                            deletedKeys.add(key);
                            changed = true;
                        }
                    });
                }

                if (serverData.names) {
                    serverData.names.forEach(([caf, name]) => {
                        if (!cafToName.has(caf)) {
                            cafToName.set(caf, name);
                            changed = true;
                        }
                    });
                }

                if (changed) {
                    updateAppDisplay();
                    // 最新状態をローカルに上書き保存
                    const data = {
                        files: Array.from(filesMap.entries()),
                        deleted: Array.from(deletedKeys),
                        names: Array.from(cafToName.entries())
                    };
                    localStorage.setItem('sakura_usage_data', JSON.stringify(data));
                }
            }
        } catch (err) {
            console.error('Server fetch error:', err);
        }
    }

    if (clearDataBtn) {
        clearDataBtn.addEventListener('click', async () => {
            if (confirm('すべての読み込み済みデータと氏名を完全に消去しますか？ (サーバー上のデータも消去されます)')) {
                localStorage.removeItem('sakura_usage_data');
                filesMap.clear();
                deletedKeys.clear();
                cafToName.clear();
                selectedMonth = "all";
                
                // サーバーからも消去
                await saveToServer({ files: [], deleted: [], names: [] });
                
                updateAppDisplay();
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

        // 既存の全レコードのキーを取得（重複チェック用）
        const existingKeys = new Set();
        filesMap.forEach(logs => {
            logs.forEach(log => existingKeys.add(log.key));
        });

        for (const file of csvFiles) {
            try {
                const text = await readFileAsText(file);
                const logs = parseCSV(text);

                // 既存のレコードと重複しないものだけを抽出
                const newLogs = logs.filter(log => !existingKeys.has(log.key));

                if (newLogs.length > 0) {
                    // 同名ファイルが既にマップにある場合は、既存のリストに新しいログを追加する
                    if (filesMap.has(file.name)) {
                        const currentLogs = filesMap.get(file.name);
                        filesMap.set(file.name, [...currentLogs, ...newLogs]);
                    } else {
                        filesMap.set(file.name, newLogs);
                    }

                    totalNewRecords += newLogs.length;
                    // 新しく追加されたキーをセットに加える（同時アップロード内の重複も防ぐ）
                    newLogs.forEach(log => existingKeys.add(log.key));
                } else if (logs.length > 0) {
                    duplicateFileNames.push(file.name);
                }
            } catch (err) { console.error(`File processing error (${file.name}):`, err); }
        }

        if (totalNewRecords > 0) {
            let msg = `${totalNewRecords}件の新しい明細を追加しました。`;
            if (duplicateFileNames.length > 0) {
                msg += `\n以下のファイルは既にインポート済みか、新しい明細が含まれていなかったためスキップされました：\n${duplicateFileNames.join('\n')}`;
            }
            alert(msg);
        } else if (duplicateFileNames.length > 0) {
            alert(`選択されたファイル（${duplicateFileNames.length}個）のデータはすべて既にインポート済みです。`);
        }

        saveToLocalStorage();
        updateAppDisplay();
    }

    function readFileAsText(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const buffer = e.target.result;
                const view = new Uint8Array(buffer);
                
                // UTF-8 BOM チェック (EF BB BF)
                let encoding = 'Shift-JIS';
                if (view.length >= 3 && view[0] === 0xEF && view[1] === 0xBB && view[2] === 0xBF) {
                    encoding = 'UTF-8';
                } else {
                    // 簡易的なUTF-8チェック (もしBOMがなくてもUTF-8として妥当なら)
                    try {
                        const decoder = new TextDecoder('utf-8', { fatal: true });
                        decoder.decode(view);
                        encoding = 'UTF-8';
                    } catch (e) {
                        encoding = 'Shift-JIS';
                    }
                }
                
                const decoder = new TextDecoder(encoding);
                resolve(decoder.decode(view));
            };
            reader.onerror = reject;
            reader.readAsArrayBuffer(file);
        });
    }

    function parseCSV(csvText) {
        const cleanText = csvText.replace(/^\uFEFF/, '');
        const allRows = splitCSVLines(cleanText);
        if (allRows.length === 0) return [];

        const logs = [];
        const clean = (val) => val ? val.trim().replace(/^['"]|['"]$/g, '').replace(/""/g, '"') : '';
        const parseNum = (val) => {
            if (val === undefined || val === null) return 0;
            // カンマ、円記号、スペース、引用符を除去して数値化
            const cleaned = val.toString().replace(/[^0-9.-]/g, '');
            return parseInt(cleaned, 10) || 0;
        };

        // --- 形式判定とヘッダー探索 ---
        let headerIdx = -1;
        let isExported = false;

        // 最初の10行からヘッダーを探す
        for (let i = 0; i < Math.min(allRows.length, 10); i++) {
            const rowStr = allRows[i].join(',');
            if (rowStr.includes('CAF番号') && (rowStr.includes('利用料') || rowStr.includes('サービス名'))) {
                headerIdx = i;
                isExported = true;
                break;
            }
        }

        if (isExported && headerIdx >= 0) {
            // --- エクスポート形式 (動的列マッピング) ---
            const headers = allRows[headerIdx].map(h => clean(h));
            const getCol = (name) => headers.findIndex(h => h.includes(name));

            const idx = {
                caf: getCol('CAF番号'),
                name: getCol('氏名'),
                provider: getCol('プロバイダ'),
                sCode: getCol('サービスコード'),
                sName: getCol('サービス名'),
                cost: getCol('利用料'),
                days: getCol('日数'),
                month: getCol('請求月'),
                start: getCol('開始日'),
                end: getCol('終了日')
            };

            for (let i = headerIdx + 1; i < allRows.length; i++) {
                const cols = allRows[i];
                if (cols.length < 5) continue;

                const caf = idx.caf >= 0 ? clean(cols[idx.caf]) : '';
                const name = idx.name >= 0 ? clean(cols[idx.name]) : '';
                
                // 氏名があればマッピングを更新
                if (caf && name) {
                    cafToName.set(caf, name);
                }

                const sCode = idx.sCode >= 0 ? clean(cols[idx.sCode]) : '';
                const sName = idx.sName >= 0 ? clean(cols[idx.sName]) : '';
                const cost = idx.cost >= 0 ? parseNum(cols[idx.cost]) : 0;
                const month = idx.month >= 0 ? clean(cols[idx.month]) : '';
                const start = idx.start >= 0 ? clean(cols[idx.start]) : '';
                const end = idx.end >= 0 ? clean(cols[idx.end]) : '';

                // 重複排除のための極めて一意なキーを生成 (全項目・金額を含める)
                const key = `${caf}_${sCode}_${month}_${start}_${end}_${cost}_${sName}`;

                logs.push({
                    caf,
                    providerCode: idx.provider >= 0 ? clean(cols[idx.provider]) : '',
                    serviceCode: sCode,
                    serviceName: sName,
                    cost: cost,
                    days: idx.days >= 0 ? parseNum(cols[idx.days]) : 0,
                    billingMonth: month,
                    startDate: start,
                    endDate: end,
                    key: key
                });
            }
        } else {
            // --- 原本 (K1BIZ0R7) 形式 ---
            allRows.forEach(cols => {
                if (cols.length < 11) return;
                const type = clean(cols[0]);
                if (type === '1') {
                    const caf = clean(cols[1]);
                    const sCode = clean(cols[3]);
                    const sName = clean(cols[4]);
                    const cost = parseNum(cols[5]);
                    const bMonth = formatBillingMonth(clean(cols[8]));
                    const sDate = formatDate(clean(cols[9]));
                    const eDate = formatDate(clean(cols[10]));
                    
                    // 重複排除のために金額も含めたキーを生成
                    const key = `${caf}_${sCode}_${bMonth}_${sDate}_${eDate}_${cost}_${sName}`;

                    logs.push({
                        caf,
                        providerCode: clean(cols[2]),
                        serviceCode: sCode,
                        serviceName: sName,
                        cost: cost,
                        days: parseNum(cols[6]),
                        billingMonth: bMonth,
                        startDate: sDate,
                        endDate: eDate,
                        key: key
                    });
                }
            });
        }
        return logs;
    }

    /** 引用符・カンマ・エスケープに対応したCSV分割ロジック */
    function splitCSVLines(csvText) {
        const rows = [];
        const lines = csvText.split(/\r?\n/).filter(l => l.trim() !== '');
        
        lines.forEach(line => {
            const cols = [];
            let curr = '';
            let inQuotes = false;
            for (let i = 0; i < line.length; i++) {
                const char = line[i];
                if (char === '"') {
                    if (inQuotes && line[i+1] === '"') {
                        curr += '"';
                        i++;
                    } else {
                        inQuotes = !inQuotes;
                    }
                } else if (char === ',' && !inQuotes) {
                    cols.push(curr);
                    curr = '';
                } else {
                    curr += char;
                }
            }
            cols.push(curr);
            rows.push(cols);
        });
        return rows;
    }

    function formatBillingMonth(month) {
        if (!month || month.length < 6) return month;
        return `${month.substring(0, 4)}/${month.substring(4, 6)}`;
    }

    function formatDate(raw) {
        if (!raw || raw.length < 8) return raw;
        return `${raw.substring(0, 4)}/${raw.substring(4, 6)}/${raw.substring(6, 8)}`;
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
            h.removeAttribute('data-order');
        });
        const active = Array.from(sortableHeaders).find(h => h.dataset.sort === sortConfig.key);
        if (active) {
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
                if (log.billingMonth) {
                    months.add(log.billingMonth);
                }
            });
        });

        const sortedMonths = Array.from(months).sort().reverse();

        // 現在の選択を保持
        const currentSelection = monthSelectEl.value;

        monthSelectEl.innerHTML = '<option value="all">すべて表示 (合算)</option>';
        sortedMonths.forEach(month => {
            const opt = document.createElement('option');
            opt.value = month;
            opt.textContent = `${month}請求分`;
            monthSelectEl.appendChild(opt);
        });

        // 選択を復元
        if (sortedMonths.includes(currentSelection)) {
            monthSelectEl.value = currentSelection;
            selectedMonth = currentSelection;
        } else {
            monthSelectEl.value = "all";
            selectedMonth = "all";
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
            li.classList.add('file-item');
            li.innerHTML = `
                <span class="file-info">📄 ${filename} (${logs.length}件)</span>
                <button class="btn-danger btn-small" data-file="${filename}">削除</button>
            `;
            fileListUl.appendChild(li);
        });

        // ファイル削除ボタンのイベント
        document.querySelectorAll('.btn-danger[data-file]').forEach(btn => {
            btn.addEventListener('click', () => {
                const filename = btn.dataset.file;
                if (confirm(`ファイル「${filename}」を削除しますか？`)) {
                    filesMap.delete(filename);
                    saveToLocalStorage();
                    updateAppDisplay();
                }
            });
        });
    }

    function processAndDisplayData() {
        let allData = [];
        filesMap.forEach(logs => {
            allData = allData.concat(logs);
        });

        // 削除済みレコードを除外
        allData = allData.filter(log => !deletedKeys.has(log.key));

        // 月でフィルタ
        if (selectedMonth !== "all") {
            allData = allData.filter(log => log.billingMonth === selectedMonth);
        }

        // 検索でフィルタ
        if (searchTerm) {
            const term = searchTerm.toLowerCase();
            allData = allData.filter(log => {
                const name = cafToName.get(log.caf) || '';
                return (
                    (log.caf && log.caf.toLowerCase().includes(term)) ||
                    (name && name.toLowerCase().includes(term)) ||
                    (log.serviceName && log.serviceName.toLowerCase().includes(term)) ||
                    (log.serviceCode && log.serviceCode.toLowerCase().includes(term))
                );
            });
        }

        // ソート
        allData.sort((a, b) => {
            const key = sortConfig.key;
            let valA, valB;

            // 氏名でソートする場合
            if (key === 'name') {
                valA = cafToName.get(a.caf) || '';
                valB = cafToName.get(b.caf) || '';
            } else {
                valA = a[key];
                valB = b[key];
            }

            // 数値比較
            if (key === 'cost' || key === 'days') {
                valA = valA || 0;
                valB = valB || 0;
                return sortConfig.order === 'asc' ? valA - valB : valB - valA;
            }

            // 文字列比較
            valA = (valA || '').toString();
            valB = (valB || '').toString();
            if (sortConfig.order === 'asc') {
                return valA.localeCompare(valB);
            } else {
                return valB.localeCompare(valA);
            }
        });

        currentDisplayData = allData;
        renderTable(allData);
        updateStats(allData);
    }

    function renderTable(data) {
        tableBody.innerHTML = '';

        if (data.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td colspan="11" style="text-align:center; padding:2rem; color: #999;">データがありません</td>';
            tableBody.appendChild(tr);
            return;
        }

        data.forEach(log => {
            const tr = document.createElement('tr');
            const cafNumber = log.caf || '';
            const name = cafToName.get(cafNumber) || '';

            tr.innerHTML = `
                <td><span class="copyable" data-caf="${cafNumber}" title="クリックしてCAF番号をコピー">${cafNumber || '-'}</span></td>
                <td class="name-cell">
                    <div class="editable" contenteditable="true" data-caf="${cafNumber}">${name}</div>
                </td>
                <td>${log.providerCode || '-'}</td>
                <td>${log.serviceCode || '-'}</td>
                <td>${log.serviceName || '-'}</td>
                <td style="text-align: right; font-weight: 600;">¥${log.cost.toLocaleString()}</td>
                <td style="text-align: center;">${log.days || '-'}</td>
                <td>${log.billingMonth || '-'}</td>
                <td>${log.startDate || '-'}</td>
                <td>${log.endDate || '-'}</td>
                <td style="text-align: center;">
                    <button class="btn-delete-row" data-key="${log.key}">🗑️</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        // CAF番号コピーのイベント
        document.querySelectorAll('.copyable').forEach(el => {
            el.addEventListener('click', () => {
                const caf = el.dataset.caf;
                if (!caf) return;
                navigator.clipboard.writeText(caf).then(() => {
                    showCopyToast(`CAF番号: ${caf} をコピーしました`);
                });
            });
        });

        // 氏名編集のイベント
        document.querySelectorAll('.name-cell .editable').forEach(el => {
            el.addEventListener('blur', () => {
                const cafNumber = el.dataset.caf;
                const newName = el.textContent.trim();
                if (newName) {
                    cafToName.set(cafNumber, newName);
                } else {
                    cafToName.delete(cafNumber);
                }
                saveToLocalStorage();
                // 同じCAF番号の他の行も更新
                document.querySelectorAll(`.editable[data-caf="${cafNumber}"]`).forEach(elem => {
                    if (elem !== el) {
                        elem.textContent = newName;
                    }
                });
            });

            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    el.blur();
                }
            });
        });

        // 行削除ボタンのイベント
        document.querySelectorAll('.btn-delete-row').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.key;
                deletedKeys.add(key);
                saveToLocalStorage();
                processAndDisplayData();
            });
        });
    }

    function updateStats(data) {
        const totalCost = data.reduce((sum, log) => sum + log.cost, 0);
        const uniqueContracts = new Set(data.map(log => log.caf)).size;

        totalCostEl.textContent = `¥${totalCost.toLocaleString()}`;
        contractCountEl.textContent = `${uniqueContracts}件`;
        totalCountEl.textContent = `${data.length}件`;

        // 期間表示
        if (selectedMonth !== "all") {
            fileDateEl.textContent = `${selectedMonth}`;
        } else if (data.length > 0) {
            const months = [...new Set(data.map(log => log.billingMonth))].sort();
            if (months.length > 0) {
                fileDateEl.textContent = `${months[0]} 〜 ${months[months.length - 1]}`;
            } else {
                fileDateEl.textContent = '-';
            }
        } else {
            fileDateEl.textContent = '-';
        }

        // 検索サマリー
        if (searchTerm) {
            searchSummaryEl.textContent = `「${searchTerm}」の検索結果: ${data.length}件`;
            searchSummaryEl.classList.remove('hidden');
        } else {
            searchSummaryEl.classList.add('hidden');
        }
    }

    // --- Search ---
    searchInput.addEventListener('input', (e) => {
        searchTerm = e.target.value.trim();
        processAndDisplayData();
    });

    // --- Export CSV ---
    exportCsvBtn.addEventListener('click', () => {
        if (currentDisplayData.length === 0) {
            alert('エクスポートするデータがありません。');
            return;
        }

        const headers = ['CAF番号', '氏名', 'プロバイダコード', 'サービスコード', 'サービス名', '利用料', '日数', '請求月', '開始日', '終了日'];
        const rows = currentDisplayData.map(log => [
            log.caf || '',
            cafToName.get(log.caf) || '',
            log.providerCode || '',
            log.serviceCode || '',
            log.serviceName || '',
            log.cost || 0,
            log.days || 0,
            log.billingMonth || '',
            log.startDate || '',
            log.endDate || ''
        ]);

        const csvContent = [
            headers.join(','),
            ...rows.map(r => r.map(val => `"${val}"`).join(','))
        ].join('\n');

        const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `sakura_usage_${selectedMonth.replace(/\//g, '')}_${Date.now()}.csv`;
        link.click();
    });

    // --- Export XLSX (SheetJS) ---
    exportXlsxBtn.addEventListener('click', () => {
        if (currentDisplayData.length === 0) {
            alert('エクスポートするデータがありません。');
            return;
        }

        const headers = ['CAF番号', '氏名', 'プロバイダコード', 'サービスコード', 'サービス名', '利用料', '日数', '請求月', '開始日', '終了日'];
        const data = currentDisplayData.map(log => [
            log.caf || '',
            cafToName.get(log.caf) || '',
            log.providerCode || '',
            log.serviceCode || '',
            log.serviceName || '',
            log.cost || 0,
            log.days || 0,
            log.billingMonth || '',
            log.startDate || '',
            log.endDate || ''
        ]);

        const worksheet = XLSX.utils.aoa_to_sheet([headers, ...data]);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "利用明細");

        // 列幅の自動調整
        const wscols = headers.map(() => ({ wch: 15 }));
        wscols[4] = { wch: 30 }; // サービス名は長めに
        worksheet['!cols'] = wscols;

        XLSX.writeFile(workbook, `sakura_usage_${selectedMonth.replace(/\//g, '')}_${Date.now()}.xlsx`);
    });

    // --- Toast Notification ---
    function showCopyToast(message) {
        let toast = document.getElementById('copy-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'copy-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    // --- Initialize ---
    loadFromLocalStorage();
    updateSortUI();
});
