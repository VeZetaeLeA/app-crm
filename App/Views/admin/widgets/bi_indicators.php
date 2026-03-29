<div class="glass-morphism-premium p-4 rounded-5 h-100 bi-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white h5 fw-black mb-0">Indicadores BI</h2>
        <span class="badge bg-primary text-deep-black x-small fw-bold px-3 py-2 rounded-pill shadow-sm">Real-Time</span>
    </div>

    <div class="row g-4">
        <!-- Conversiones -->
        <div class="col-12 col-md-6">
            <div class="bg-white-5 p-3 rounded-4 border-white-10">
                <p class="text-white-50 x-small fw-bold uppercase mb-2">Conversión Lead -> Ticket</p>
                <div class="d-flex align-items-end justify-content-between">
                    <h4 class="text-white fw-black mb-0">
                        <?php echo $stats['conversions']['leads_to_tickets']; ?>%
                    </h4>
                    <div class="progress w-50" style="height: 6px; background: rgba(255,255,255,0.05);">
                        <div class="progress-bar bg-primary"
                            style="width: <?php echo $stats['conversions']['leads_to_tickets']; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="bg-white-5 p-3 rounded-4 border-white-10">
                <p class="text-white-50 x-small fw-bold uppercase mb-2">Conversión Ticket -> Presupuesto</p>
                <div class="d-flex align-items-end justify-content-between">
                    <h4 class="text-white fw-black mb-0">
                        <?php echo $stats['conversions']['tickets_to_budgets']; ?>%
                    </h4>
                    <div class="progress w-50" style="height: 6px; background: rgba(255,255,255,0.05);">
                        <div class="progress-bar bg-warning"
                            style="width: <?php echo $stats['conversions']['tickets_to_budgets']; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financieros -->
        <div class="col-12">
            <div class="bg-white-5 p-3 rounded-4 border-white-10 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Ingresos Proyectados (Mes)</p>
                    <h3 class="text-white fw-black mb-0">$
                        <?php echo number_format($stats['financial']['monthly_revenue'], 2); ?>
                    </h3>
                </div>
                <div class="text-end">
                    <p class="text-white-50 x-small fw-bold uppercase mb-1">Valor Promedio</p>
                    <p class="text-primary fw-bold mb-0">$
                        <?php echo number_format($stats['financial']['avg_customer_value'], 2); ?>
                    </p>
                </div>
            </div>
        </div>
        <!-- Chart Funnel -->
        <div class="col-12 mt-2" style="height: 120px;">
            <canvas id="conversionFunnelChart"></canvas>
        </div>
    </div>
</div>