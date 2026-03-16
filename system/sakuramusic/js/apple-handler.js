/* 
 * SAKURA MUSIC - Apple Music Handler
 * MusicKit JS Integration
 */

const AppleMusicHandler = {
    musicKit: null,

    /**
     * MusicKit JS の初期化
     * @param {string} developerToken - Apple Developer Portal で作成した JWT
     */
    async init(developerToken) {
        try {
            if (!developerToken) {
                console.warn('Apple Music Developer Token is missing.');
                return false;
            }

            await MusicKit.configure({
                developerToken: developerToken,
                app: {
                    name: 'SAKURA MUSIC',
                    build: '1.0.0'
                }
            });

            this.musicKit = MusicKit.getInstance();
            console.log('MusicKit JS Configured');
            return true;
        } catch (err) {
            console.error('MusicKit Init Error:', err);
            return false;
        }
    },

    /**
     * ログイン処理
     */
    async login() {
        if (!this.musicKit) return;
        try {
            await this.musicKit.authorize();
            console.log('Apple Music Authorized');
            return true;
        } catch (err) {
            console.error('Apple Music Login Error:', err);
            return false;
        }
    },

    /**
     * ログアウト処理
     */
    async logout() {
        if (!this.musicKit) return;
        await this.musicKit.unauthorize();
        console.log('Apple Music Unauthorized');
    },

    /**
     * 再生状態の取得
     */
    get isAuthorized() {
        return this.musicKit ? this.musicKit.isAuthorized : false;
    }
};
