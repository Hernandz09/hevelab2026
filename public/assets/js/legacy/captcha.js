
class SliderCaptcha {
    constructor(options) {
        this.container  = options.container;
        this.onSuccess  = options.onSuccess || (() => {});
        this.onFail     = options.onFail    || (() => {});
        this.W          = 280;
        this.H          = 155;
        this.PS         = 46;
        this.tolerance  = 9;
        this.verified   = false;
        this.dragging   = false;
        this.startX     = 0;
        this.currentX   = 0;
        this.targetX    = 0;
        this.targetY    = 0;
        this.fails      = 0;          
        this.maxFails   = 5;          
        this.lockSecs   = 30;         
        this.locked     = false;
        this._lockTimer = null;
        this._moveH     = this._onMove.bind(this);
        this._endH      = this._onEnd.bind(this);
        this._render();
        this.reset();
    }

    
    _render() {
        this.container.innerHTML = `
            <div class="sc-wrap">
                <p class="sc-title">Deslice para completar el rompecabezas</p>
                <div class="sc-canvas-wrap">
                    <canvas class="sc-bg"    width="${this.W}" height="${this.H}"></canvas>
                    <canvas class="sc-piece" width="${this.PS}" height="${this.H}"></canvas>
                </div>
                <div class="sc-slider-wrap">
                    <div class="sc-track">
                        <div class="sc-fill"></div>
                        <div class="sc-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2.5"
                                 stroke-linecap="round" stroke-linejoin="round">
                                 <polyline points="13 17 18 12 13 7"/>
                                 <polyline points="6 17 11 12 6 7"/>
                            </svg>
                        </div>
                    </div>
                    <p class="sc-hint">← Desliza el bloque</p>
                </div>
                <p class="sc-result"></p>
            </div>`;

        this.bgCanvas    = this.container.querySelector('.sc-bg');
        this.pieceCanvas = this.container.querySelector('.sc-piece');
        this.btn         = this.container.querySelector('.sc-btn');
        this.fill        = this.container.querySelector('.sc-fill');
        this.hint        = this.container.querySelector('.sc-hint');
        this.result      = this.container.querySelector('.sc-result');
        this.bgCtx       = this.bgCanvas.getContext('2d');
        this.pieceCtx    = this.pieceCanvas.getContext('2d');

        this.btn.addEventListener('mousedown',  e => this._onStart(e));
        this.btn.addEventListener('touchstart', e => this._onStart(e), { passive: true });
        document.addEventListener('mousemove',  this._moveH);
        document.addEventListener('touchmove',  this._moveH, { passive: true });
        document.addEventListener('mouseup',    this._endH);
        document.addEventListener('touchend',   this._endH);
    }

    
    reset() {
        if (this.locked) return;      
        this.verified = false;
        this.currentX = 0;
        this.btn.style.left         = '0';
        this.fill.style.width       = '42px';
        this.fill.style.background  = '';
        this.btn.style.background   = '';
        this.btn.style.color        = '#6abf00';
        this.pieceCanvas.style.left = '0';
        this.hint.style.opacity     = '1';
        this.result.textContent     = '';
        this.result.className       = 'sc-result';
        this._generate();
    }

    _generate() {
        const pad = this.PS + 16;
        this.targetX = Math.floor(Math.random() * (this.W - pad - this.PS)) + pad;
        this.targetY = Math.floor(Math.random() * (this.H - this.PS - 20)) + 10;
        this._drawBg();
        this._drawPiece();
    }

    
    _drawBg() {
        const ctx = this.bgCtx;
        const { W, H, PS, targetX: tx, targetY: ty } = this;

        
        const g = ctx.createLinearGradient(0, 0, W, H);
        g.addColorStop(0, '#1a56db');
        g.addColorStop(1, '#1e3a8a');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, W, H);

        
        ctx.save();
        for (let i = 1; i <= 5; i++) {
            ctx.beginPath();
            ctx.ellipse(W / 2, H / 2, W * 0.36, H * 0.36 * (i / 5), 0, 0, Math.PI * 2);
            ctx.strokeStyle = `rgba(255,255,255,${0.07 + i * 0.015})`;
            ctx.lineWidth = 1;
            ctx.stroke();
        }
        
        ctx.beginPath();
        ctx.arc(W / 2, H / 2, H * 0.36, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(255,255,255,0.18)';
        ctx.lineWidth = 1.5;
        ctx.stroke();
        ctx.restore();

        
        [[14, H / 2 - 25], [W - 14, H / 2 - 30], [14, H / 2 + 28], [W - 14, H / 2 + 24]].forEach(([x, y]) => {
            this._star(ctx, x, y, 6, 3);
        });

        
        ctx.save();
        this._piecePath(ctx, tx, ty);
        ctx.fillStyle = 'rgba(0,0,0,0.52)';
        ctx.fill();
        ctx.strokeStyle = 'rgba(255,255,255,0.65)';
        ctx.lineWidth   = 1.5;
        ctx.stroke();
        ctx.restore();
    }

    
    _drawPiece() {
        const ctx = this.pieceCtx;
        const { W, H, PS, targetX: tx, targetY: ty } = this;

        ctx.clearRect(0, 0, PS, H);

        
        ctx.save();
        this._piecePath(ctx, 0, ty);
        ctx.clip();
        ctx.drawImage(this.bgCanvas, tx, 0, PS, H, 0, 0, PS, H);
        
        ctx.fillStyle = 'rgba(106,191,0,0.28)';
        ctx.fillRect(0, ty, PS, PS);
        ctx.restore();

        
        ctx.save();
        this._piecePath(ctx, 0, ty);
        ctx.strokeStyle = 'rgba(255,255,255,0.9)';
        ctx.lineWidth   = 1.5;
        ctx.stroke();
        ctx.restore();

        
        this.pieceCanvas.style.filter = 'drop-shadow(2px 2px 5px rgba(0,0,0,0.45))';
    }

    
    _piecePath(ctx, x, y) {
        const s = this.PS;
        const r = s * 0.17; 
        ctx.beginPath();
        
        ctx.moveTo(x, y);
        ctx.lineTo(x + s / 2 - r, y);
        ctx.arc(x + s / 2, y, r, Math.PI, 0, true);   
        ctx.lineTo(x + s, y);
        
        ctx.lineTo(x + s, y + s / 2 - r);
        ctx.arc(x + s, y + s / 2, r, Math.PI * 1.5, Math.PI * 0.5, false);
        ctx.lineTo(x + s, y + s);
        
        ctx.lineTo(x + s / 2 + r, y + s);
        ctx.arc(x + s / 2, y + s, r, 0, Math.PI, true);
        ctx.lineTo(x, y + s);
        
        ctx.lineTo(x, y);
        ctx.closePath();
    }

    
    _star(ctx, cx, cy, r1, r2) {
        ctx.save();
        ctx.fillStyle  = '#facc15';
        ctx.globalAlpha = 0.85;
        ctx.beginPath();
        for (let i = 0; i < 8; i++) {
            const r   = i % 2 === 0 ? r1 : r2;
            const ang = (i * Math.PI) / 4 - Math.PI / 2;
            ctx[i === 0 ? 'moveTo' : 'lineTo'](cx + r * Math.cos(ang), cy + r * Math.sin(ang));
        }
        ctx.closePath();
        ctx.fill();
        ctx.restore();
    }

    
    _onStart(e) {
        if (this.verified || this.locked) return;
        this.dragging = true;
        this.startX   = e.clientX || e.touches[0].clientX;
        this.hint.style.opacity = '0';
    }

    _onMove(e) {
        if (!this.dragging) return;
        const cx   = e.clientX || e.touches[0].clientX;
        const maxDx = this.W - this.PS;
        const dx    = Math.max(0, Math.min(cx - this.startX, maxDx));
        this.currentX               = dx;
        this.btn.style.left         = dx + 'px';
        this.fill.style.width       = (dx + 42) + 'px';
        this.pieceCanvas.style.left = dx + 'px';
    }

    _onEnd() {
        if (!this.dragging) return;
        this.dragging = false;
        Math.abs(this.currentX - this.targetX) <= this.tolerance
            ? this._success()
            : this._fail();
    }

    _success() {
        this.verified          = true;
        this.btn.style.background  = '#6abf00';
        this.btn.style.color       = '#fff';
        this.fill.style.background = 'linear-gradient(90deg,#6abf00,#58a000)';
        this.result.textContent    = '✓ Verificación completada';
        this.result.className      = 'sc-result ok';
        setTimeout(() => this.onSuccess(), 500);
    }

    _fail() {
        this.fails++;
        this.result.textContent = `✗ Inténtalo de nuevo (${this.fails}/${this.maxFails})`;
        this.result.className   = 'sc-result fail';
        this.btn.classList.add('sc-shake');

        if (this.fails >= this.maxFails) {
            
            setTimeout(() => this._lockout(), 600);
        } else {
            setTimeout(() => {
                this.btn.classList.remove('sc-shake');
                this.reset();
            }, 800);
        }
    }

    _lockout() {
        this.locked   = true;
        this.dragging = false;
        this.btn.classList.remove('sc-shake');

        
        let lockEl = this.container.querySelector('.sc-lock');
        if (!lockEl) {
            lockEl = document.createElement('div');
            lockEl.className = 'sc-lock';
            this.container.querySelector('.sc-canvas-wrap').appendChild(lockEl);
        }

        let remaining = this.lockSecs;
        lockEl.innerHTML = this._lockHTML(remaining);
        lockEl.style.display = 'flex';

        this._lockTimer = setInterval(() => {
            remaining--;
            const countEl = lockEl.querySelector('.sc-lock-count');
            if (countEl) countEl.textContent = remaining;
            if (remaining <= 0) {
                clearInterval(this._lockTimer);
                lockEl.style.display = 'none';
                this.locked = false;
                this.fails  = 0;
                this.reset();
                this.result.textContent = '';
                this.result.className   = 'sc-result';
            }
        }, 1000);

        this.result.textContent = '🔒 Demasiados intentos fallidos';
        this.result.className   = 'sc-result fail';
    }

    _lockHTML(secs) {
        return `
            <div class="sc-lock-icon">🔒</div>
            <p class="sc-lock-msg">Demasiados intentos fallidos</p>
            <p class="sc-lock-sub">Espera <span class="sc-lock-count">${secs}</span>s para continuar</p>
        `;
    }

    destroy() {
        document.removeEventListener('mousemove', this._moveH);
        document.removeEventListener('touchmove', this._moveH);
        document.removeEventListener('mouseup',   this._endH);
        document.removeEventListener('touchend',  this._endH);
    }
}
