<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="glass-morphism p-4 p-md-5 rounded-5 border-white-10 shadow-lg">
            <div class="d-flex align-items-center gap-4 mb-5">
                <div class="rounded-circle bg-midnight d-flex align-items-center justify-content-center text-accent fw-bold shadow-gold"
                    style="width: 80px; height: 80px; font-size: 2rem;">
                    <?php echo strtoupper(substr(\Core\Auth::user()['name'], 0, 1)); ?>
                </div>
                <div>
                    <h2 class="text-white fw-bold mb-1">
                        <?php echo \Core\Auth::user()['name']; ?>
                    </h2>
                    <p class="text-white-50 mb-0">
                        <?php echo \Core\Auth::user()['email']; ?> • <span
                            class="badge bg-white-10 text-accent uppercase x-small">
                            <?php echo \Core\Auth::role(); ?>
                        </span>
                    </p>
                </div>
            </div>

            <hr class="border-white-10 mb-5">

            <div class="section-security">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="material-symbols-outlined text-accent fs-1">shield</span>
                    <h3 class="text-white h4 mb-0 fw-bold">Seguridad de la Cuenta</h3>
                </div>

                <div class="card bg-midnight bg-opacity-50 border-white-10 rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                            <div class="flex-grow-1">
                                <h4 class="text-white h5 fw-bold mb-2">Autenticación de Dos Factores (2FA)</h4>
                                <p class="text-white-50 small mb-0 max-width-500">
                                    Añade una capa extra de seguridad a tu cuenta. Al iniciar sesión, deberás introducir
                                    un código generado en tu dispositivo móvil.
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <?php if ($user['two_factor_enabled']): ?>
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="material-symbols-outlined fs-6">check_circle</span>
                                            Activado
                                        </div>
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="material-symbols-outlined fs-6">cancel</span>
                                            Desactivado
                                        </div>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-top border-white-10">
                            <?php if ($user['two_factor_enabled']): ?>
                                <a href="<?php echo url('profile/disable2FA'); ?>"
                                    class="btn btn-outline-danger rounded-pill px-4 btn-sm"
                                    onclick="return confirm('¿Estás seguro de desactivar la seguridad 2FA?')">
                                    Desactivar 2FA
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-tech"
                                    id="btn-setup-2fa">
                                    Configurar 2FA
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Password Change Section -->
                <div class="card bg-midnight bg-opacity-50 border-white-10 rounded-4 overflow-hidden"
                    id="change-password">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <span class="material-symbols-outlined text-accent">lock_reset</span>
                            <h4 class="text-white h5 fw-bold mb-0">Cambiar Contraseña</h4>
                        </div>

                        <form action="<?php echo url('profile/updatePassword'); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-white-50 x-small mb-2 uppercase fw-bold tracking-widest">Nueva
                                        Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-deep-black border-white-10 text-white-50">
                                            <span class="material-symbols-outlined fs-6">password</span>
                                        </span>
                                        <input type="password" name="password"
                                            class="form-control bg-deep-black border-white-10 text-white"
                                            placeholder="Mínimo 6 caracteres" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label
                                        class="text-white-50 x-small mb-2 uppercase fw-bold tracking-widest">Confirmar
                                        Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-deep-black border-white-10 text-white-50">
                                            <span class="material-symbols-outlined fs-6">key</span>
                                        </span>
                                        <input type="password" name="confirm_password"
                                            class="form-control bg-deep-black border-white-10 text-white"
                                            placeholder="Repite la contraseña" required>
                                    </div>
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="submit"
                                        class="btn btn-primary rounded-pill px-5 fw-bold uppercase x-small tracking-widest shadow-gold">
                                        Actualizar Contraseña
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2FA -->
<div class="modal fade" id="modal2FA" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-morphism border-white-10 rounded-5">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title text-white fw-bold">Configurar Autenticación 2FA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div id="setup-step-1">
                    <p class="text-white-50 small mb-4">Escanea este código QR con Google Authenticator o Authy.</p>
                    <div class="bg-white p-3 rounded-4 d-inline-block mb-4 shadow-lg">
                        <img id="qr-code-img" src="" alt="QR Code" style="width: 180px; height: 180px;">
                    </div>
                    <div class="bg-deep-black bg-opacity-50 p-3 rounded-4 mb-4 border border-white-10">
                        <p class="text-white-50 x-small mb-1 uppercase tracking-widest">Código Secreto</p>
                        <code class="text-accent fw-bold fs-5" id="secret-key-text">----</code>
                    </div>
                    <button class="btn btn-primary w-100 py-3 rounded-4 fw-bold" id="btn-go-to-verify">
                        Ya lo escaneé, continuar
                    </button>
                </div>

                <div id="setup-step-2" class="d-none">
                    <p class="text-white-50 small mb-4">Introduce el código de 6 dígitos que aparece en tu aplicación
                        móvil para verificar la configuración.</p>
                    <div class="mb-4">
                        <input type="text" id="otp-code"
                            class="form-control form-control-lg bg-deep-black border-white-10 text-white text-center fs-2 tracking-widest py-3 rounded-4"
                            maxlength="6" placeholder="000000">
                        <div id="verify-error" class="text-danger small mt-2 d-none"></div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light w-100 py-3 rounded-4 fw-bold" id="btn-back-to-qr">
                            Atrás
                        </button>
                        <button class="btn btn-accent w-100 py-3 rounded-4 fw-bold" id="btn-verify-2fa">
                            Verificar y Activar
                        </button>
                    </div>
                </div>

                <div id="setup-success" class="d-none">
                    <div class="text-success mb-3">
                        <span class="material-symbols-outlined fs-huge">check_circle</span>
                    </div>
                    <h4 class="text-white fw-bold mb-2">¡2FA Activado!</h4>
                    <p class="text-white-50 small mb-4">Tu cuenta ahora está protegida con autenticación de dos
                        factores.</p>
                    <button class="btn btn-primary w-100 py-3 rounded-4 fw-bold" onclick="location.reload()">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .max-width-500 {
        max-width: 500px;
    }

    .fs-huge {
        font-size: 5rem;
    }

    .btn-accent {
        background: var(--elegant-gold);
        color: var(--deep-black);
        border: none;
    }

    .btn-accent:hover {
        background: #fff;
        transform: translateY(-2px);
    }

    .shadow-tech {
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnSetup = document.getElementById('btn-setup-2fa');
        const modal = new bootstrap.Modal(document.getElementById('modal2FA'));

        if (btnSetup) {
            btnSetup.addEventListener('click', function () {
                fetch(window.APP_URL + '/profile/enable2FAStep1')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('qr-code-img').src = data.qrUrl;
                            document.getElementById('secret-key-text').textContent = data.secret;
                            modal.show();
                        }
                    });
            });
        }

        document.getElementById('btn-go-to-verify').addEventListener('click', function () {
            document.getElementById('setup-step-1').classList.add('d-none');
            document.getElementById('setup-step-2').classList.remove('d-none');
        });

        document.getElementById('btn-back-to-qr').addEventListener('click', function () {
            document.getElementById('setup-step-2').classList.add('d-none');
            document.getElementById('setup-step-1').classList.remove('d-none');
        });

        document.getElementById('btn-verify-2fa').addEventListener('click', function () {
            const code = document.getElementById('otp-code').value;
            const errorDiv = document.getElementById('verify-error');

            fetch(window.APP_URL + '/profile/confirm2FA', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `code=${code}&_token=<?php echo \Core\Validator::generateCsrfToken(); ?>`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('setup-step-2').classList.add('d-none');
                        document.getElementById('setup-success').classList.remove('d-none');
                    } else {
                        errorDiv.textContent = data.message;
                        errorDiv.classList.remove('d-none');
                    }
                });
        });
    });
</script>