/**
 * Vezetaelea OS - Session Monitor & Heartbeat
 * Mantiene la sesión viva y muestra advertencias antes de que caduque.
 */
(function () {
    // Configuración desde el servidor (inyectada en el layout)
    const config = window.SESSION_CONFIG || { lifetime: 14400, heartbeat: 300, warning: 300 };

    const SESSION_LIFETIME = config.lifetime; // en segundos
    const HEARTBEAT_INTERVAL = config.heartbeat * 1000; // a milisegundos
    const WARNING_THRESHOLD = config.warning; // en segundos
    const APP_URL = window.APP_URL || '';

    let sessionStartTime = Date.now();
    let heartbeatTimer = null;
    let warningShown = false;

    /**
     * Inicia el proceso de monitoreo
     */
    function init() {
        console.log('Session Monitor: Iniciado');
        startHeartbeat();
        setupVisualMonitor();
    }

    /**
     * Envía una petición silenciosa al servidor para refrescar la sesión
     */
    async function startHeartbeat() {
        heartbeatTimer = setInterval(async () => {
            try {
                const response = await fetch(`${APP_URL}/session/heartbeat`);
                if (response.ok) {
                    const data = await response.json();
                    console.log('Session Monitor: Heartbeat enviado con éxito', data);
                    sessionStartTime = Date.now(); // Reset local timer
                    hideWarning();
                }
            } catch (error) {
                console.error('Session Monitor: Error en heartbeat', error);
            }
        }, HEARTBEAT_INTERVAL);
    }

    /**
     * Crea un pequeño indicador visual discreto
     */
    function setupVisualMonitor() {
        const monitorDiv = document.createElement('div');
        monitorDiv.id = 'session-timeout-warning';
        monitorDiv.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-weight: bold;
            display: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            animation: slideUp 0.5s ease-out;
        `;

        monitorDiv.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <span class="material-symbols-outlined">timer</span>
                <div>
                    <span class="d-block x-small uppercase opacity-75">Tu sesión expirará pronto</span>
                    <button id="extend-session" class="btn btn-link p-0 text-white fw-black text-decoration-none small">Haz clic aquí para extender</button>
                </div>
            </div>
        `;

        document.body.appendChild(monitorDiv);

        document.getElementById('extend-session').addEventListener('click', () => {
            manualRefresh();
        });

        // Loop de verificación de tiempo (cada 30 seg)
        setInterval(checkSessionStatus, 30000);
    }

    function checkSessionStatus() {
        const elapsedSeconds = (Date.now() - sessionStartTime) / 1000;
        const remainingSeconds = SESSION_LIFETIME - elapsedSeconds;

        if (remainingSeconds < WARNING_THRESHOLD && !warningShown) {
            showWarning();
        }
    }

    function showWarning() {
        const el = document.getElementById('session-timeout-warning');
        if (el) el.style.display = 'block';
        warningShown = true;
    }

    function hideWarning() {
        const el = document.getElementById('session-timeout-warning');
        if (el) el.style.display = 'none';
        warningShown = false;
    }

    async function manualRefresh() {
        console.log('Session Monitor: Refresco manual solicitado');
        try {
            const response = await fetch(`${APP_URL}/session/heartbeat`);
            if (response.ok) {
                sessionStartTime = Date.now();
                hideWarning();
            }
        } catch (e) {
            window.location.reload(); // Si falla, recargamos para re-autenticar
        }
    }

    // Estilo para la animación
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes slideUp {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);

    // Arrancar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
