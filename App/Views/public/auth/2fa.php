<div class="login-container d-flex align-items-center justify-content-center min-vh-100">
    <div class="login-card glass-morphism p-4 p-md-5 rounded-5 border-white-10 shadow-lg text-center"
        style="max-width: 450px; width: 100%;">
        <div class="mb-4">
            <span class="material-symbols-outlined text-accent fs-huge mb-3">lock_person</span>
            <h2 class="text-white fw-bold h3 mb-2">Verificación de Seguridad</h2>
            <p class="text-white-50 small">Introduce el código de 6 dígitos de tu aplicación de autenticación para
                continuar.</p>
        </div>

        <?php if ($error = \Core\Session::flash('error')): ?>
            <div
                class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger rounded-4 mb-4 small">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo url('auth/verify2FA'); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="mb-4">
                <input type="text" name="code"
                    class="form-control form-control-lg bg-deep-black border-white-10 text-white text-center fs-1 tracking-widest py-3 rounded-4"
                    maxlength="6" placeholder="000000" autofocus required autocomplete="one-time-code">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-bold shadow-tech mb-3">
                Verificar Identidad
            </button>

            <a href="<?php echo url('auth/logout'); ?>"
                class="text-white-50 small hover-gold transition-all text-decoration-none">
                Cancelar e iniciar sesión con otra cuenta
            </a>
        </form>
    </div>
</div>

<style>
    .fs-huge {
        font-size: 4rem;
    }

    .shadow-tech {
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
    }
</style>