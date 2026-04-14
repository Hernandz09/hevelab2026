/**
 * face_auth.js — Módulo de reconocimiento facial para HEVELAB VIISION ERP
 * Requiere: face-api.js (CDN) cargado ANTES de este script
 *
 * API pública:
 *   FaceAuth.openLoginModal()           → abre modal y autentica con cara
 *   FaceAuth.openRegisterCapture(cb)    → captura descriptor para registro
 */
(function (window) {
    'use strict';

    // ── Configuración ─────────────────────────────────────────
    const MODELS_URL   = window.FACE_MODELS_URL || '../../assets/models';
    const API_LOGIN    = window.FACE_API_LOGIN   || '../../api/face_login.php';
    const THRESHOLD    = 0.52;   // distancia máxima para reconocer (más bajo = más estricto)
    const STABLE_HITS  = 4;      // detecciones consecutivas antes de capturar

    let modelsLoaded = false;
    let activeStream  = null;
    let loopRunning   = false;

    // ── Cargar modelos (solo la primera vez) ──────────────────
    async function loadModels(onProgress) {
        if (modelsLoaded) return;
        onProgress?.('Cargando detector...');
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODELS_URL);
        onProgress?.('Cargando puntos de referencia...');
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODELS_URL);
        onProgress?.('Cargando reconocedor...');
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODELS_URL);
        modelsLoaded = true;
    }

    // ── Cámara ────────────────────────────────────────────────
    async function startCamera(videoEl) {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 480 }, height: { ideal: 480 } }
        });
        videoEl.srcObject = stream;
        activeStream = stream;
        await new Promise(res => { videoEl.onloadedmetadata = res; });
        videoEl.play();
    }

    function stopCamera() {
        activeStream?.getTracks().forEach(t => t.stop());
        activeStream = null;
    }

    // ── Bucle de detección ────────────────────────────────────
    async function detectionLoop(videoEl, opts) {
        const {
            onStatus,    // fn(text, cls)
            onProgress,  // fn(pct 0-100)
            onCapture,   // fn(Float32Array descriptor)
            ovalGuide,
            scanBar,
        } = opts;

        let hits = 0;
        loopRunning = true;

        const detect = async () => {
            if (!loopRunning) return;

            const result = await faceapi
                .detectSingleFace(videoEl, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.75 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (result) {
                hits++;
                const pct = Math.round((hits / STABLE_HITS) * 100);
                onProgress?.(Math.min(pct, 100));
                onStatus?.('Rostro detectado — mantén la posición', '');
                ovalGuide?.classList.add('detected');
                scanBar?.classList.add('scanning');

                if (hits >= STABLE_HITS) {
                    loopRunning = false;
                    onStatus?.('Procesando...', '');
                    onCapture?.(Array.from(result.descriptor));
                    return;
                }
            } else {
                hits = 0;
                onProgress?.(0);
                onStatus?.('Acerca tu rostro al óvalo — buena iluminación', '');
                ovalGuide?.classList.remove('detected');
                scanBar?.classList.remove('scanning');
            }

            setTimeout(detect, 250);
        };

        detect();
    }

    // ── Construir modal ───────────────────────────────────────
    function buildModal() {
        if (document.getElementById('face-modal-overlay')) return;

        document.body.insertAdjacentHTML('beforeend', `
        <div class="face-overlay" id="face-modal-overlay" aria-hidden="true">
            <div class="face-modal" role="dialog" aria-modal="true">

                <div class="face-modal-header">
                    <div>
                        <span>HEVELAB BIOMETRICS</span>
                        <h3>Reconocimiento Facial</h3>
                    </div>
                    <button class="face-modal-close" id="face-modal-close-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                             stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <div class="face-camera-wrap">
                    <video id="face-video" autoplay muted playsinline></video>
                    <canvas id="face-canvas"></canvas>
                    <div class="face-oval-guide" id="face-oval"></div>
                    <div class="face-scan-bar" id="face-scan-bar"></div>

                    <div class="face-loading-overlay" id="face-loading-overlay">
                        <div class="face-spinner"></div>
                        <span class="face-loading-text" id="face-loading-text">Inicializando...</span>
                    </div>
                </div>

                <div class="face-progress-wrap">
                    <div class="face-progress-bar" id="face-progress-bar"></div>
                </div>

                <p class="face-status" id="face-status">Cargando modelos...</p>
            </div>
        </div>
        `);

        document.getElementById('face-modal-close-btn').addEventListener('click', closeModal);
        document.getElementById('face-modal-overlay').addEventListener('click', e => {
            if (e.target.id === 'face-modal-overlay') closeModal();
        });
    }

    function openModal() {
        const overlay = document.getElementById('face-modal-overlay');
        overlay.setAttribute('aria-hidden', 'false');
        overlay.classList.add('active');
    }

    function closeModal() {
        loopRunning = false;
        stopCamera();
        const overlay = document.getElementById('face-modal-overlay');
        overlay.classList.remove('active');
        overlay.setAttribute('aria-hidden', 'true');
        // Reset UI
        setTimeout(() => {
            document.getElementById('face-progress-bar').style.width = '0%';
            document.getElementById('face-status').textContent = 'Cargando modelos...';
            document.getElementById('face-status').className = 'face-status';
            document.getElementById('face-oval')?.classList.remove('detected');
            document.getElementById('face-scan-bar')?.classList.remove('scanning');
        }, 320);
    }

    function setStatus(msg, cls = '') {
        const el = document.getElementById('face-status');
        if (!el) return;
        el.textContent = msg;
        el.className = 'face-status' + (cls ? ' ' + cls : '');
    }

    function setProgress(pct) {
        const bar = document.getElementById('face-progress-bar');
        if (bar) bar.style.width = pct + '%';
    }

    function setLoading(visible, text = '') {
        const overlay = document.getElementById('face-loading-overlay');
        const textEl  = document.getElementById('face-loading-text');
        if (!overlay) return;
        overlay.classList.toggle('hidden', !visible);
        if (text && textEl) textEl.textContent = text;
    }

    // ── LOGIN con rostro ──────────────────────────────────────
    async function openLoginModal() {
        buildModal();
        openModal();
        setLoading(true, 'Inicializando modelos...');
        setStatus('Cargando...');

        try {
            await loadModels(text => setLoading(true, text));
        } catch (e) {
            setLoading(false);
            setStatus('Error al cargar modelos. Recarga la página.', 'fail');
            return;
        }

        const video = document.getElementById('face-video');
        try {
            await startCamera(video);
        } catch (e) {
            setLoading(false);
            setStatus('No se pudo acceder a la cámara. Verifica permisos.', 'fail');
            return;
        }

        setLoading(false);

        await detectionLoop(video, {
            onStatus:   setStatus,
            onProgress: setProgress,
            ovalGuide:  document.getElementById('face-oval'),
            scanBar:    document.getElementById('face-scan-bar'),
            onCapture:  async (descriptor) => {
                // Detener cámara y enviar al servidor
                stopCamera();
                setStatus('Verificando identidad...', '');
                setProgress(100);

                try {
                    const res = await fetch(API_LOGIN, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ descriptor })
                    });
                    const data = await res.json();

                    if (data.success) {
                        setStatus('¡Rostro verificado!', 'ok');
                        window.showToast?.('Enviando código de seguridad...', 'success', 2500);
                        setTimeout(() => {
                            closeModal();
                            window.location.href = data.redirect;
                        }, 900);
                    } else {
                        setStatus(data.message || 'Rostro no reconocido. Intenta de nuevo.', 'fail');
                        window.showToast?.(data.message || 'Rostro no reconocido', 'error');
                        setProgress(0);
                        // Reintentar
                        setTimeout(async () => {
                            if (!document.getElementById('face-modal-overlay')?.classList.contains('active')) return;
                            await startCamera(video);
                            setStatus('Acerca tu rostro al óvalo', '');
                            detectionLoop(video, arguments[0]); // restart
                        }, 2000);
                    }
                } catch (err) {
                    setStatus('Error de red. Comprueba la conexión.', 'fail');
                    window.showToast?.('Error de conexión', 'error');
                }
            }
        });
    }

    // ── CAPTURA para registro (sin modal, inline) ─────────────
    async function openRegisterCapture(onDescriptor) {
        buildModal();
        openModal();
        setLoading(true, 'Inicializando...');

        try {
            await loadModels(text => setLoading(true, text));
        } catch (e) {
            setLoading(false);
            setStatus('Error al cargar modelos.', 'fail');
            return;
        }

        const video = document.getElementById('face-video');
        try {
            await startCamera(video);
        } catch (e) {
            setLoading(false);
            setStatus('Sin acceso a la cámara.', 'fail');
            return;
        }

        setLoading(false);
        setStatus('Coloca tu rostro en el óvalo y mantén la posición', '');

        await detectionLoop(video, {
            onStatus:   setStatus,
            onProgress: setProgress,
            ovalGuide:  document.getElementById('face-oval'),
            scanBar:    document.getElementById('face-scan-bar'),
            onCapture: (descriptor) => {
                stopCamera();
                setStatus('¡Rostro registrado correctamente!', 'ok');
                setProgress(100);
                window.showToast?.('Rostro capturado correctamente', 'success');
                setTimeout(() => {
                    closeModal();
                    onDescriptor?.(descriptor);
                }, 1200);
            }
        });
    }

    // ── Exportar API pública ──────────────────────────────────
    window.FaceAuth = { openLoginModal, openRegisterCapture };

})(window);
