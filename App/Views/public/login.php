<section class="min-vh-100 d-flex align-items-center justify-content-center brand-bg pt-5">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="glass-morphism p-4 rounded-5 border-white-10 shadow-2xl position-relative">
                    <a href="<?php echo url(); ?>"
                        class="position-absolute top-0 end-0 m-4 text-white-50 text-decoration-none hover-gold transition-colors"
                        title="Volver a la web">
                        <span class="material-symbols-outlined">close</span>
                    </a>

                    <?php if (\Core\Session::has('flash_error')): ?>
                        <div
                            class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small mb-4">
                            <?php echo \Core\Session::flash('error'); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo url('auth/doLogin'); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-4">
                            <label class="text-white-50 x-small mb-2 uppercase fw-bold tracking-widest">Correo
                                Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-midnight border-white-10 text-white-50 icon-container">
                                    <span class="material-symbols-outlined fs-6">mail</span>
                                </span>
                                <input type="email" name="email"
                                    class="form-control bg-steel border-white-10 text-white p-2 small"
                                    placeholder="tu.email@ejemplo.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label
                                class="text-white-50 x-small mb-2 uppercase fw-bold tracking-widest">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-midnight border-white-10 text-white-50 icon-container">
                                    <span class="material-symbols-outlined fs-6">lock</span>
                                </span>
                                <input type="password" name="password"
                                    class="form-control bg-steel border-white-10 text-white p-2 small"
                                    placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label text-white-50 small" for="remember">Recordarme</label>
                            </div>
                            <a href="#" class="text-primary x-small text-decoration-none fw-bold uppercase">¿Olvidaste
                                tu contraseña?</a>
                        </div>

                        <button type="submit"
                            class="btn vzl-btn-glow-magenta px-5 py-3 fw-bold uppercase tracking-widest w-100 mb-4">
                            Entrar al Sistema
                        </button>

                        <p class="text-center text-white-50 small mb-0">
                            ¿No tienes una cuenta? <a href="<?php echo url('ticket/request'); ?>"
                                class="text-primary text-decoration-none fw-bold">Inicia un Proyecto</a>
                        </p>
                    </form>
                </div>


            </div>
        </div>
    </div>
</section>

<style>
    .input-group-text {
        border-right: none;
    }

    .form-control {
        border-left: none;
    }

    .form-control:focus {
        border-left: none;
    }

    <script>document.querySelector('form').addEventListener('submit', function (e) {
            const btn=this.querySelector('button[type="submit"]');
            btn.classList.add('is-loading');
            btn.innerHTML='Validando Credenciales...';
        });
    </script>