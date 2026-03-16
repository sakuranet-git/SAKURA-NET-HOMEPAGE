/**
 * SAKURA MUSIC - GAS Master Script (v3.0.0 NO-SEARCH)
 * 「無効な引数: q」の真の原因は getFoldersByName/searchFiles 等の
 * 内部クエリ生成にあるため、これらをすべて廃止。
 * getFolderById / getFileById のみ使用する「完全ID直接取得方式」。
 */

const SAKURA_FOLDER_ID = '1ftI36zOUQ_snJEFTBqPbRbbxH18cLckS';

function doGet(e) { return handleRequest(e); }
function doPost(e) { return handleRequest(e); }

function handleRequest(e) {
    try {
        // パラメータ取得
        let p;
        if (e.postData && e.postData.contents) {
            try { p = JSON.parse(e.postData.contents); } catch (ex) { p = e.parameter; }
        } else {
            p = e.parameter || {};
        }

        const action = p.action || 'listFiles';

        // フォルダIDのクレンジング（URLが貼られても対応）
        function extractId(raw) {
            if (!raw) return null;
            const m = String(raw).match(/[-\w]{25,}/);
            return m ? m[0] : null;
        }

        // getFoldersByName は q エラーの原因なので使わない
        // getFolderById のみ使用
        function getFolder(rawId) {
            const id = extractId(rawId) || SAKURA_FOLDER_ID;
            return DriveApp.getFolderById(id);
        }

        // 楽曲ファイル一覧（再帰スキャン）
        if (action === 'listFiles') {
            const folder = getFolder(p.folderId || p.driveFolderId);
            const files = [];

            function scan(fld, path) {
                // getFiles() はクエリを使わない安全なイテレータ
                const fileIt = fld.getFiles();
                while (fileIt.hasNext()) {
                    const f = fileIt.next();
                    if (/\.(mp3|m4a|wav|flac|ogg|aac)$/i.test(f.getName())) {
                        files.push({
                            id: f.getId(),
                            name: f.getName(),
                            path: path,
                            mimeType: f.getMimeType()
                        });
                    }
                }
                // getFolders() もクエリを使わない安全なイテレータ
                const folderIt = fld.getFolders();
                while (folderIt.hasNext()) {
                    const sub = folderIt.next();
                    scan(sub, path ? path + '/' + sub.getName() : sub.getName());
                }
            }

            scan(folder, '');
            return ok({ files: files, count: files.length, folderName: folder.getName() });
        }

        // 音声データ取得（Base64）
        if (action === 'getStream') {
            const file = DriveApp.getFileById(p.fileId);
            return ok({
                data: Utilities.base64Encode(file.getBlob().getBytes()),
                mimeType: file.getMimeType()
            });
        }

        // アップロード
        if (action === 'uploadFile') {
            const folder = getFolder(p.folderId || p.driveFolderId);
            const raw = (p.fileData || '').indexOf(',') > -1
                ? p.fileData.split(',')[1]
                : p.fileData;
            const bytes = Utilities.base64Decode(raw);
            const blob = Utilities.newBlob(bytes, p.mimeType || 'application/octet-stream', p.fileName);
            const newFile = folder.createFile(blob);
            return ok({ fileId: newFile.getId() });
        }

        // 削除
        if (action === 'deleteFile') {
            DriveApp.getFileById(p.fileId).setTrashed(true);
            return ok({});
        }

        return ng('Unknown action: ' + action);

    } catch (err) {
        return ng(err.toString());
    }
}

function ok(data) {
    return ContentService
        .createTextOutput(JSON.stringify(Object.assign({ success: true }, data)))
        .setMimeType(ContentService.MimeType.JSON);
}

function ng(msg) {
    return ContentService
        .createTextOutput(JSON.stringify({ success: false, message: msg }))
        .setMimeType(ContentService.MimeType.JSON);
}
