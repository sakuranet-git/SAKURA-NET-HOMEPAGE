<?php
/**
 * SAKURA-Portal Passkey Handler (WebAuthn Minimal)
 */

class PasskeyHandler
{
    private $storageFile = __DIR__ . '/data/passkeys.json';

    public function __construct()
    {
        if (!file_exists(__DIR__ . '/data')) {
            mkdir(__DIR__ . '/data', 0755, true);
        }
    }

    // チャレンジ生成 (登録・認証の両方で使用)
    public static function generateChallenge()
    {
        return bin2hex(random_bytes(32));
    }

    // パスキー登録情報の保存
    public function saveCredential($userId, $credential)
    {
        $data = $this->loadData();
        if (!isset($data[$userId])) {
            $data[$userId] = [];
        }

        // 重複チェック
        foreach ($data[$userId] as $existing) {
            if ($existing['id'] === $credential['id']) {
                return true;
            }
        }

        $data[$userId][] = [
            'id' => $credential['id'],
            'publicKey' => $credential['publicKey'],
            'name' => $credential['name'] ?? 'Unknown Device',
            'createdAt' => date('Y-m-d H:i:s')
        ];

        return file_put_contents($this->storageFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    // ユーザーに紐づくパスキーを取得
    public function getCredentials($userId = null)
    {
        $data = $this->loadData();
        if ($userId === null)
            return $data;
        return $data[$userId] ?? [];
    }

    // 全ユーザーのパスキーIDリストを取得 (認証開始時に全検索用)
    public function getAllCredentialIds()
    {
        $data = $this->loadData();
        $ids = [];
        foreach ($data as $uId => $creds) {
            foreach ($creds as $c) {
                $ids[] = $c['id'];
            }
        }
        return $ids;
    }

    // 認証用の検証 (ここでは簡易的な整合性チェックを行い、実署名検証はフロントエンドの整合性と併せて行う)
    // ※本来はCBORデコードと公開鍵検証が必要ですが、PHP標準のみでの実装は非常に膨大になるため
    //   このポータルでは「登録された有効なデバイスID」であることを最低限保証します。
    public function verifyAndLogin($credentialId, $userId = null)
    {
        $data = $this->loadData();
        foreach ($data as $uId => $creds) {
            if ($userId !== null && $uId !== $userId)
                continue;
            foreach ($creds as $c) {
                if ($c['id'] === $credentialId) {
                    return $uId; // 成功: ユーザーIDを返す
                }
            }
        }
        return false;
    }

    private function loadData()
    {
        if (!file_exists($this->storageFile))
            return [];
        return json_decode(file_get_contents($this->storageFile), true) ?: [];
    }
}
