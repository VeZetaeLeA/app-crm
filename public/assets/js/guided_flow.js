/**
 * guided_flow.js — v2.1
 * Handles the step-by-step commercial funnel: Category → Service → Plan → Form
 * 
 * Depends on window.APP_CONFIG injected by PHP layout (layouts/public.php)
 * Endpoints: /service/getByCategory/{id}  /service/getPlans/{id}
 */

const GuidedFlow = {

    // Resolved from PHP-injected global — never empty
    get baseUrl() {
        return (window.APP_CONFIG && window.APP_CONFIG.baseUrl)
            ? window.APP_CONFIG.baseUrl.replace(/\/$/, '')
            : window.location.origin;
    },

    // ─────────────────────────────────────────────
    // STEP 1: Category (Pilar) selected
    // ─────────────────────────────────────────────
    async selectPillar(element) {
        const id   = element.dataset.id;
        const name = element.dataset.name;

        // Visual: highlight selected pillar
        document.querySelectorAll('[onclick^="GuidedFlow.selectPillar"]').forEach(el => {
            el.classList.remove('border-primary', 'shadow-gold');
        });
        element.classList.add('border-primary', 'shadow-gold');

        // Update summary badge
        const summaryPillar = document.getElementById('summary-pillar');
        const flowSummary   = document.getElementById('flow-summary');
        if (summaryPillar) summaryPillar.textContent = name;
        if (flowSummary)   flowSummary.classList.remove('d-none');

        // Reset downstream steps
        this.hideSteps(['service-step', 'plan-step', 'form-step']);
        this.clearContainer('service-selection');
        this.clearContainer('plan-selection');

        // Show loading state
        this.showLoading('service-step', 'service-selection', 'Cargando servicios...');

        const url = `${this.baseUrl}/service/getByCategory/${id}`;
        console.debug('[GuidedFlow] Fetching services:', url);

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status} — ${res.statusText}`);
            }

            const services = await res.json();
            this.renderServices(services, name);

        } catch (err) {
            console.error('[GuidedFlow] selectPillar error:', err);
            this.showError('service-step', 'service-selection', 'No se pudieron cargar los servicios. Inténtalo de nuevo.');
        }
    },

    // ─────────────────────────────────────────────
    // STEP 2: Service selected
    // ─────────────────────────────────────────────
    async selectService(element, id, name) {
        document.querySelectorAll('.service-card').forEach(el => {
            el.classList.remove('border-primary', 'shadow-gold');
        });
        element.classList.add('border-primary', 'shadow-gold');

        const summaryService = document.getElementById('summary-service');
        const summaryArrow   = document.getElementById('summary-arrow-1');
        if (summaryService) { summaryService.textContent = name; summaryService.classList.remove('d-none'); }
        if (summaryArrow)   summaryArrow.classList.remove('d-none');

        this.hideSteps(['plan-step', 'form-step']);
        this.clearContainer('plan-selection');
        this.showLoading('plan-step', 'plan-selection', 'Cargando planes...');

        const url = `${this.baseUrl}/service/getPlans/${id}`;
        console.debug('[GuidedFlow] Fetching plans:', url);

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status} — ${res.statusText}`);
            }

            const plans = await res.json();
            this.renderPlans(plans, name);

        } catch (err) {
            console.error('[GuidedFlow] selectService error:', err);
            this.showError('plan-step', 'plan-selection', 'No se pudieron cargar los planes. Inténtalo de nuevo.');
        }
    },

    // ─────────────────────────────────────────────
    // STEP 3: Plan selected → show form
    // ─────────────────────────────────────────────
    selectPlan(element, id, title) {
        document.querySelectorAll('.plan-card').forEach(el => {
            el.classList.remove('border-primary', 'shadow-gold');
        });
        element.classList.add('border-primary', 'shadow-gold');

        const planInput   = document.getElementById('selected-plan-id');
        const subjectInput = document.getElementById('form-subject');
        const formStep    = document.getElementById('form-step');

        if (planInput)    planInput.value    = id;
        if (subjectInput) subjectInput.value = `Solicitud: ${title}`;
        if (formStep) {
            formStep.classList.remove('d-none');
            this.scrollTo('form-step');
        }
    },

    // ─────────────────────────────────────────────
    // Render helpers
    // ─────────────────────────────────────────────
    renderServices(services, pillarName) {
        const container = document.getElementById('service-selection');
        const step      = document.getElementById('service-step');
        if (!container || !step) return;

        if (!services || services.length === 0) {
            this.showError('service-step', 'service-selection', 'No hay servicios disponibles para este pilar.');
            return;
        }

        container.innerHTML = services.map(s => `
            <div class="col-md-4">
                <div class="service-card glass-morphism rounded-5 p-4 transition-all cursor-pointer h-100 hover-lift border border-white-5"
                    onclick="GuidedFlow.selectService(this, ${s.id}, '${this.escapeAttr(s.name)}')">
                    <h6 class="text-white fw-bold x-small mb-2 uppercase">${this.escapeHtml(s.name)}</h6>
                    ${s.short_description ? `<p class="text-white-50 x-small mb-0">${this.escapeHtml(s.short_description)}</p>` : ''}
                </div>
            </div>`
        ).join('');

        step.classList.remove('d-none');
        this.scrollTo('service-step');
    },

    renderPlans(plans, serviceName) {
        const container = document.getElementById('plan-selection');
        const step      = document.getElementById('plan-step');
        if (!container || !step) return;

        if (!plans || plans.length === 0) {
            this.showError('plan-step', 'plan-selection', 'No hay planes disponibles para este servicio.');
            return;
        }

        container.innerHTML = plans.map(p => `
            <div class="col-md-4">
                <div class="plan-card glass-morphism rounded-5 p-4 transition-all cursor-pointer h-100 hover-lift border border-white-5"
                    onclick="GuidedFlow.selectPlan(this, ${p.id}, '${this.escapeAttr(serviceName)} - ${this.escapeAttr(p.name)}')">
                    <h6 class="text-primary x-small fw-bold uppercase mb-3">${this.escapeHtml(p.name)}</h6>
                    <h4 class="text-white fw-bold mb-3">$${parseFloat(p.price || 0).toLocaleString('es-AR')}</h4>
                    ${p.features && Array.isArray(p.features) && p.features.length > 0
                        ? `<ul class="list-unstyled text-white-50 x-small mb-3">
                            ${p.features.slice(0, 3).map(f => `<li class="mb-1">✓ ${this.escapeHtml(f)}</li>`).join('')}
                          </ul>`
                        : ''
                    }
                    <button class="btn btn-outline-white btn-sm w-100 uppercase">Seleccionar</button>
                </div>
            </div>`
        ).join('');

        step.classList.remove('d-none');
        this.scrollTo('plan-step');
    },

    // ─────────────────────────────────────────────
    // UI Utility methods
    // ─────────────────────────────────────────────
    hideSteps(stepIds) {
        stepIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
    },

    clearContainer(containerId) {
        const el = document.getElementById(containerId);
        if (el) el.innerHTML = '';
    },

    showLoading(stepId, containerId, message = 'Cargando...') {
        const container = document.getElementById(containerId);
        const step      = document.getElementById(stepId);
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Cargando...</span></div>
                    <p class="text-white-50 x-small">${message}</p>
                </div>`;
        }
        if (step) step.classList.remove('d-none');
    },

    showError(stepId, containerId, message) {
        const container = document.getElementById(containerId);
        const step      = document.getElementById(stepId);
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <span class="material-symbols-outlined text-warning fs-1 d-block mb-3">warning</span>
                    <p class="text-white-50 small">${message}</p>
                </div>`;
        }
        if (step) step.classList.remove('d-none');
    },

    scrollTo(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    },

    escapeAttr(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '\\"');
    }
};
