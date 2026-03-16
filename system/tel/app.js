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
    const exportXmlBtn = document.getElementById('export-xml');

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

    // --- Persistence Logic ---
    function saveToLocalStorage() {
        const data = {
            files: Array.from(filesMap.entries()),
            deleted: Array.from(deletedKeys),
            names: Array.from(phoneNumberToName.entries())
        };
        localStorage.setItem('sakura_call_data', JSON.stringify(data));
    }

    function loadFromLocalStorage() {
        const saved = localStorage.getItem('sakura_call_data');
        if (saved) {
            try {
                const data = JSON.parse(saved);
                filesMap = new Map(data.files);
                deletedKeys = new Set(data.deleted);
                phoneNumberToName = new Map(data.names || []);
                updateAppDisplay();
            } catch (e) { console.error('Load error:', e); }
        }
    }

    if (clearDataBtn) {
        clearDataBtn.addEventListener('click', () => {
            if (confirm('すべての読み込み済みデータと編集内容を完全に消去しますか？')) {
                localStorage.removeItem('sakura_call_data');
                filesMap.clear();
                deletedKeys.clear();
                phoneNumberToName.clear();
                selectedMonth = "all";
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
        let processedFileCount = 0;

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
                    processedFileCount++;
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
            reader.onload = (e) => resolve(e.target.result);
            reader.readAsText(file, 'Shift-JIS');
        });
    }

    function parseCSV(csvText) {
        const cleanText = csvText.replace(/^\uFEFF/, '');
        const lines = cleanText.split(/\r?\n/).filter(l => l.trim() !== '');
        if (lines.length === 0) return [];

        const logs = [];
        const isExported = lines[0].includes('通話日') && lines[0].includes('発信元番号');

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
            li.querySelector('.btn-danger').onclick = () => {
                filesMap.delete(filename);
                saveToLocalStorage();
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
                    saveToLocalStorage();
                    document.querySelectorAll(`.editable[data-num="${num}"]`).forEach(c => { c.innerText = newName; });
                });
            });

            tr.querySelector('.from-search').onclick = () => {
                window.open(`https://www.google.com/search?q=${formatPhoneNumberForSearch(log.from)}+%E9%9B%BB%E8%A9%B1%E7%95%AA%E5%8F%B7`, '_blank');
            };
            tr.querySelector('.to-search').onclick = () => {
                window.open(`https://www.google.com/search?q=${formatPhoneNumberForSearch(log.to)}+%E9%9B%BB%E8%A9%B1%E7%95%AA%E5%8F%B7`, '_blank');
            };
            tr.querySelector('.btn-delete-row').onclick = () => {
                deletedKeys.add(log.key);
                saveToLocalStorage();
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

    if (exportXmlBtn) {
        exportXmlBtn.addEventListener('click', () => {
            if (currentDisplayData.length === 0) return;
            let xmlContent = '<?xml version="1.0" encoding="UTF-8"?>\n<CallDetails>\n';
            currentDisplayData.forEach(log => {
                xmlContent += `  <Log>\n    <Date>${log.date}</Date>\n    <Time>${log.time}</Time>\n    <FromName>${log.fromName}</FromName>\n    <FromNumber>${log.from}</FromNumber>\n    <ToName>${log.toName}</ToName>\n    <ToNumber>${log.to}</ToNumber>\n    <Duration>${log.duration}</Duration>\n    <Cost>${log.cost}</Cost>\n    <CAF>${log.caf}</CAF>\n  </Log>\n`;
            });
            xmlContent += '</CallDetails>';
            downloadFile(xmlContent, `通話明細_${new Date().toISOString().slice(0, 10)}.xml`, "text/xml;charset=utf-8;");
        });
    }

    searchInput.addEventListener('input', (e) => {
        searchTerm = e.target.value.toLowerCase();
        processAndDisplayData();
    });

    updateSortUI();
    loadFromLocalStorage();
});
