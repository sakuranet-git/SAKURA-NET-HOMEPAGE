/* mascot.js */
class SakuraMascot {
    constructor() {
        this.container = null;
        this.imgWrap = null;
        this.bubble = null;
        this.messages = [
            "こんにちは！",
            "今日も一日頑張りましょう🌸",
            "SAKURA OSへようこそ！",
            "何かお手伝いしましょうか？",
            "お疲れ様です、一息入れませんか？",
            "綺麗な壁紙ですね！"
        ];
        this.canvas = null; // キャンバスの追加
        this.init();
    }

    init() {
        // コンテナ作成
        this.container = document.createElement('div');
        this.container.className = 'sakura-mascot-container';
        this.container.id = 'sakura-mascot';
        // 初期は画面外（位置計算完了まで非表示）
        this.container.style.left = '-200px';
        this.container.style.top  = '0px';

        // 吹き出し作成
        this.bubble = document.createElement('div');
        this.bubble.className = 'mascot-bubble';
        this.container.appendChild(this.bubble);

        // 画像ラップ作成
        this.imgWrap = document.createElement('div');
        this.imgWrap.className = 'mascot-image-wrap';

        // Canvas で白背景を完全透明化
        const canvas = document.createElement('canvas');
        canvas.className = 'mascot-image';
        canvas.width = 300;
        canvas.height = 300;
        canvas.style.width = '300px';
        canvas.style.height = '300px';
        const ctx = canvas.getContext('2d');
        const tempImg = new Image();
        tempImg.crossOrigin = 'anonymous';
        tempImg.onload = () => {
            // PNG側で既に透過済みのため、単純描画のみを行う（指示書G1準拠）
            ctx.clearRect(0, 0, 300, 300);
            ctx.drawImage(tempImg, 0, 0, 300, 300);
        };
        tempImg.src = '/system/SAKURA-NET-Mascot/sakura-mascot.png';

        this.imgWrap.appendChild(canvas);
        this.container.appendChild(this.imgWrap);

        // クリックイベント
        this.imgWrap.addEventListener('click', () => {
            if (!this._wasDragged) this.onMascotClick();
        });

        // ボディに追加
        document.body.appendChild(this.container);

        // 指示書(G3): 呼び出し順序は絶対
        this._initPosition();   // 1. 位置設定
        this._initDrag();       // 2. ドラッグ
        this.updateVisibility(); // 3. 表示状態の同期

        // 初回起動時の挨拶
        setTimeout(() => {
            if (localStorage.getItem('sakura_mascot_off') !== 'true') {
                this.say("おかえりなさい！お会いできて嬉しいです🌸");
            }
        }, 2000);

        // 定期的な独り言（頻度と確率を向上）
        setInterval(() => this.randomTalk(), 10000 + Math.random() * 20000);
    }

    _initPosition() {
        // 保存済み位置があれば復元して終了
        const saved = this._loadPosition();
        if (saved) {
            this.container.style.left = saved.x + 'px';
            this.container.style.top  = saved.y + 'px';
            return;
        }
        // dcal-wrapper のレイアウト完了を待って位置を決める
        const trySetPos = () => {
            const ref = document.getElementById('dcal-wrapper');
            if (ref) {
                const rect = ref.getBoundingClientRect();
                if (rect.height > 0) {
                    this.container.style.left = (rect.left + 10) + 'px';
                    this.container.style.top  = (rect.bottom + 16) + 'px';
                    return;
                }
            }
            // まだレイアウトされていなければリトライ
            setTimeout(trySetPos, 200);
        };
        // window.load 後に少し待ってから計算
        if (document.readyState === 'complete') {
            setTimeout(trySetPos, 300);
        } else {
            window.addEventListener('load', () => setTimeout(trySetPos, 300));
        }
    }

    _initDrag() {
        let startX, startY, startLeft, startTop;
        this._wasDragged = false;

        const onMouseMove = (e) => {
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            if (Math.abs(dx) > 3 || Math.abs(dy) > 3) this._wasDragged = true;
            let newLeft = Math.max(0, Math.min(window.innerWidth  - this.container.offsetWidth,  startLeft + dx));
            let newTop  = Math.max(0, Math.min(window.innerHeight - this.container.offsetHeight, startTop  + dy));
            this.container.style.left = newLeft + 'px';
            this.container.style.top  = newTop  + 'px';
        };

        const onMouseUp = () => {
            this.container.classList.remove('dragging');
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            this._savePosition();
            setTimeout(() => { this._wasDragged = false; }, 50);
        };

        this.container.addEventListener('mousedown', (e) => {
            if (e.target.classList.contains('mascot-bubble')) return;
            e.preventDefault();
            this._wasDragged = false;
            startX    = e.clientX;
            startY    = e.clientY;
            startLeft = this.container.offsetLeft;
            startTop  = this.container.offsetTop;
            this.container.classList.add('dragging');
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        // タッチ対応
        this.container.addEventListener('touchstart', (e) => {
            const t = e.touches[0];
            this._wasDragged = false;
            startX    = t.clientX;
            startY    = t.clientY;
            startLeft = this.container.offsetLeft;
            startTop  = this.container.offsetTop;
            this.container.classList.add('dragging');
        }, { passive: true });

        this.container.addEventListener('touchmove', (e) => {
            const t = e.touches[0];
            const dx = t.clientX - startX;
            const dy = t.clientY - startY;
            if (Math.abs(dx) > 3 || Math.abs(dy) > 3) this._wasDragged = true;
            let newLeft = Math.max(0, Math.min(window.innerWidth  - this.container.offsetWidth,  startLeft + dx));
            let newTop  = Math.max(0, Math.min(window.innerHeight - this.container.offsetHeight, startTop  + dy));
            this.container.style.left = newLeft + 'px';
            this.container.style.top  = newTop  + 'px';
        }, { passive: true });

        this.container.addEventListener('touchend', () => {
            this.container.classList.remove('dragging');
            this._savePosition();
            setTimeout(() => { this._wasDragged = false; }, 50);
        });
    }

    _savePosition() {
        localStorage.setItem('sakura_mascot_pos', JSON.stringify({
            x: this.container.offsetLeft,
            y: this.container.offsetTop
        }));
    }

    _loadPosition() {
        try {
            const s = localStorage.getItem('sakura_mascot_pos');
            return s ? JSON.parse(s) : null;
        } catch { return null; }
    }

    onMascotClick() {
        this.jump();
        this.say(this.getRandomMessage());
    }

    jump() {
        const img = this.imgWrap.querySelector('.mascot-image');
        img.classList.remove('mascot-jump');
        void img.offsetWidth;
        img.classList.add('mascot-jump');
    }

    say(text) {
        this.bubble.textContent = text;
        this.bubble.classList.add('show');
        if (this.bubbleTimeout) clearTimeout(this.bubbleTimeout);
        this.bubbleTimeout = setTimeout(() => {
            this.bubble.classList.remove('show');
        }, 4000);
    }

    getRandomMessage() {
        return this.messages[Math.floor(Math.random() * this.messages.length)];
    }

    randomTalk() {
        // 50%の確率で話しかける
        if (Math.random() > 0.5 && !this.bubble.classList.contains('show')) {
            this.say(this.getRandomMessage());
        }
    }

    updateVisibility() {
        const isOff = localStorage.getItem('sakura_mascot_off') === 'true';
        this.container.style.display = isOff ? 'none' : 'flex';
    }

    toggle(force) {
        const currentState = localStorage.getItem('sakura_mascot_off') === 'true';
        const newState = force !== undefined ? !force : !currentState;
        localStorage.setItem('sakura_mascot_off', String(newState));
        this.updateVisibility();
    }
}

window.sakuraMascot = new SakuraMascot();
