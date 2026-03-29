/**
 * guided_flow.js
 * Handles the step-by-step selection of Category -> Service -> Plan -> Form
 */

const GuidedFlow = {
    config: {
        baseUrl: window.APP_URL || '',
        endpoints: {
            services: 'service/getByCategory',
            plans: 'service/getPlans'
        }
    },

    async selectPillar(element) {
        const id = element.dataset.id;
        const name = element.dataset.name;
        
        const summaryPillar = document.getElementById('summary-pillar');
        const flowSummary = document.getElementById('flow-summary');
        
        if (summaryPillar) summaryPillar.textContent = name;
        if (flowSummary) flowSummary.classList.remove('d-none');
        
        this.hideSteps(['service-step', 'plan-step', 'form-step']);

        try {
            const res = await fetch(`${this.config.baseUrl}/${this.config.endpoints.services}/${id}`);
            const services = await res.json();
            const container = document.getElementById('service-selection');
            
            if (container) {
                container.innerHTML = '';
                services.forEach(s => {
                    container.innerHTML += `
                        <div class="col-md-4">
                            <div class="service-card glass-morphism rounded-5 p-4 transition-all cursor-pointer h-100 hover-lift border border-white-5" 
                                onclick="GuidedFlow.selectService(this, ${s.id}, '${s.name}')">
                                <h6 class="text-white fw-bold x-small mb-0 uppercase">${s.name}</h6>
                            </div>
                        </div>`;
                });
                document.getElementById('service-step').classList.remove('d-none');
                this.scrollTo('service-step');
            }
        } catch (e) { 
            console.error('Error fetching services:', e); 
        }
    },

    async selectService(element, id, name) {
        const summaryService = document.getElementById('summary-service');
        const summaryArrow = document.getElementById('summary-arrow-1');
        
        if (summaryService) {
            summaryService.textContent = name;
            summaryService.classList.remove('d-none');
        }
        if (summaryArrow) summaryArrow.classList.remove('d-none');
        
        this.hideSteps(['plan-step', 'form-step']);

        try {
            const res = await fetch(`${this.config.baseUrl}/${this.config.endpoints.plans}/${id}`);
            const plans = await res.json();
            const container = document.getElementById('plan-selection');
            
            if (container) {
                container.innerHTML = '';
                plans.forEach(p => {
                    container.innerHTML += `
                        <div class="col-md-4">
                            <div class="plan-card glass-morphism rounded-5 p-4 transition-all cursor-pointer h-100 hover-lift border border-white-5" 
                                onclick="GuidedFlow.selectPlan(this, ${p.id}, '${name} - ${p.name}')">
                                <h6 class="text-primary x-small fw-bold uppercase mb-3">${p.name}</h6>
                                <h4 class="text-white fw-bold mb-3">$${parseFloat(p.price).toLocaleString()}</h4>
                                <button class="btn btn-outline-white btn-sm w-100 uppercase">Seleccionar</button>
                            </div>
                        </div>`;
                });
                document.getElementById('plan-step').classList.remove('d-none');
                this.scrollTo('plan-step');
            }
        } catch (e) { 
            console.error('Error fetching plans:', e); 
        }
    },

    selectPlan(element, id, title) {
        const planInput = document.getElementById('selected-plan-id');
        const subjectInput = document.getElementById('form-subject');
        const formStep = document.getElementById('form-step');
        
        if (planInput) planInput.value = id;
        if (subjectInput) subjectInput.value = `Solicitud: ${title}`;
        if (formStep) {
            formStep.classList.remove('d-none');
            this.scrollTo('form-step');
        }
    },

    hideSteps(stepIds) {
        stepIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        });
    },

    scrollTo(id) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
};
