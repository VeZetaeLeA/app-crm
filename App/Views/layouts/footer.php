<?php
// Determinar si estamos en el panel de administración
$is_admin = $is_admin ?? (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false || strpos($_SERVER['REQUEST_URI'], 'tickets') !== false || strpos($_SERVER['REQUEST_URI'], 'quotes') !== false || strpos($_SERVER['REQUEST_URI'], 'finance') !== false || strpos($_SERVER['REQUEST_URI'], 'leads') !== false || strpos($_SERVER['REQUEST_URI'], 'users') !== false);
?>

<?php if (!$is_admin): ?>
    <!-- Full Footer Pro (Solo para Landing/Web Principal) -->
    <footer class="footer-pro">
        <div class="footer-grid">
            <!-- Section 1: Contact & Address -->
            <div class="footer-brand mobile-accordion-section">
                <div class="accordion-trigger show-mobile">
                    <h4>Contacto y Ubicación</h4>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 6 15 12 9 18"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="accordion-content">
                    <div style="margin-bottom: 25px;">
                        <h5
                            style="color: var(--vzl-primary); font-size: var(--vzl-fs-sm); text-transform: uppercase; letter-spacing: var(--vzl-tracking-wide); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <span>📞</span> Contacto
                        </h5>
                        <p style="font-size: var(--vzl-fs-sm); color: var(--vzl-text-muted); margin-bottom: 5px;">
                            <a href="mailto:<?php echo \Core\Config::get('business.company_mail'); ?>"
                                style="color: var(--vzl-text-muted);"><?php echo \Core\Config::get('business.company_mail'); ?></a>
                        </p>
                        <p style="font-size: var(--vzl-fs-sm); color: var(--vzl-text-muted);"><?php echo \Core\Config::get('business.company_phone'); ?></p>
                    </div>

                    <div>
                        <h5
                            style="color: var(--vzl-primary); font-size: var(--vzl-fs-sm); text-transform: uppercase; letter-spacing: var(--vzl-tracking-wide); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <span>📍</span> Dirección
                        </h5>
                        <p style="font-size: var(--vzl-fs-sm); color: var(--vzl-text-muted); line-height: var(--vzl-lh-body);">
                            <?php echo \Core\Config::get('business.company_address'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Services -->
            <div class="footer-links mobile-accordion-section" style="text-align: center;">
                <div class="accordion-trigger show-mobile">
                    <h4>Servicios</h4>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 6 15 12 9 18"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="accordion-content">
                    <h4 class="hide-mobile">Servicios</h4>
                    <ul>
                        <li><a href="<?php echo url('home'); ?>#pillar-data">Datos &amp; BI</a></li>
                        <li><a href="<?php echo url('home'); ?>#pillar-web">Web &amp; Apps</a></li>
                        <li><a href="<?php echo url('home'); ?>#pillar-proc">Procesos</a></li>
                    </ul>
                </div>
            </div>

            <!-- Section 3: Company -->
            <div class="footer-links mobile-accordion-section" style="text-align: center;">
                <div class="accordion-trigger show-mobile">
                    <h4>Compañía</h4>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 6 15 12 9 18"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="accordion-content">
                    <h4 class="hide-mobile">Compañía</h4>
                    <ul>
                        <li><a href="#">Sobre Nosotros</a></li>
                        <li><a href="#">Casos de Éxito</a></li>
                        <li><a href="<?php echo url('login'); ?>">Portal de Clientes</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>

<!-- Bottom Footer Bar (Siempre visible) -->
<div style="background: var(--bg-main); border-top: 1px solid rgba(255,255,255,0.05); padding: 15px 20px;">
    <div
        style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <p style="color:var(--vzl-text-muted); font-size: var(--vzl-fs-xs);">Diseño y desarrollo Full Stack por <?php echo \Core\Config::get('business.company_name'); ?> <br>©
            since <?php echo \Core\Config::get('business.company_est_year'); ?>. All Rights Reserved</p>
        <div style="display: flex; gap: 20px; align-items: center;">
            <!-- Instagram -->
            <a href="<?php echo \Core\Config::get('social.instagram'); ?>" target="_blank"
                style="color:var(--text-muted); transition: color 0.2s;" title="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
            </a>
            <!-- LinkedIn -->
            <a href="<?php echo \Core\Config::get('social.linkedin'); ?>" target="_blank"
                style="color:var(--text-muted); transition: color 0.2s;" title="LinkedIn">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                    <rect x="2" y="9" width="4" height="12"></rect>
                    <circle cx="4" cy="4" r="2"></circle>
                </svg>
            </a>
            <!-- Facebook -->
            <a href="<?php echo \Core\Config::get('social.facebook'); ?>" target="_blank" style="color:var(--text-muted); transition: color 0.2s;" title="Facebook">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                </svg>
            </a>
            <!-- Twitter -->
            <a href="<?php echo \Core\Config::get('social.twitter'); ?>" target="_blank"
                style="color:var(--text-muted); transition: color 0.2s;" title="Twitter">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z">
                    </path>
                </svg>
            </a>
            <!-- GitHub -->
            <a href="<?php echo \Core\Config::get('social.github'); ?>" target="_blank"
                style="color:var(--text-muted); transition: color 0.2s;" title="GitHub">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22">
                    </path>
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
    // Lógica de acordeón móvil para el footer
    (function () {
        function initFooterAccordion() {
            const triggers = document.querySelectorAll('.accordion-trigger');

            triggers.forEach(trigger => {
                // Eliminar cualquier listener previo para evitar duplicados
                const newTrigger = trigger.cloneNode(true);
                trigger.parentNode.replaceChild(newTrigger, trigger);

                newTrigger.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const section = this.closest('.mobile-accordion-section');
                    if (!section) return;

                    const isActive = section.classList.contains('active');

                    // Cerrar todas las secciones
                    document.querySelectorAll('.mobile-accordion-section').forEach(s => {
                        s.classList.remove('active');
                    });

                    // Si la sección clickeada NO estaba activa, la abrimos
                    if (!isActive) {
                        section.classList.add('active');
                    }
                });
            });
        }

        // Ejecutar al cargar el DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initFooterAccordion);
        } else {
            initFooterAccordion();
        }
    })();
</script>