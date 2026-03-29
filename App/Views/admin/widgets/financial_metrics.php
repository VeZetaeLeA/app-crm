<?php
// App/Views/admin/widgets/financial_metrics.php
$fin = $financial ?? ['mrr' => 0, 'arr' => 0, 'churn_rate' => 0, 'history' => []];
?>
<div class="glass-morphism p-4 rounded-5 border-white-10 h-100 shadow-2xl overflow-hidden position-relative">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="text-white fw-black mb-0 uppercase small tracking-widest">Métricas Financieras <span class="text-primary italic">Live</span></h5>
            <p class="text-white-50 x-small mb-0">MRR, ARR y Retención de Clientes</p>
        </div>
        <div class="bg-primary bg-opacity-10 p-2 rounded-3 border border-primary border-opacity-20 no-print">
            <span class="material-symbols-outlined text-primary fs-4">payments</span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="bg-white-5 p-3 rounded-4 border border-white-10 text-center">
                <span class="text-white-50 x-small uppercase fw-bold d-block mb-1">MRR</span>
                <span class="text-white h4 fw-black d-block mb-0">$<?php echo number_format($fin['mrr'], 2); ?></span>
                <span class="text-success x-small fw-bold">+0% <span class="material-symbols-outlined fs-6 align-middle">trending_up</span></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white-5 p-3 rounded-4 border border-white-10 text-center">
                <span class="text-white-50 x-small uppercase fw-bold d-block mb-1">ARR</span>
                <span class="text-primary h4 fw-black d-block mb-0">$<?php echo number_format($fin['arr'], 2); ?></span>
                <span class="text-white-50 x-small">Proyección Anual</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white-5 p-3 rounded-4 border border-white-10 text-center">
                <span class="text-white-50 x-small uppercase fw-bold d-block mb-1">Churn Rate</span>
                <span class="text-danger h4 fw-black d-block mb-0"><?php echo $fin['churn_rate']; ?>%</span>
                <span class="text-white-50 x-small">Últimos 30 días</span>
            </div>
        </div>
    </div>

    <div class="revenue-chart-container" style="height: 200px;">
        <canvas id="revenueHistoryChart"></canvas>
    </div>

    <div class="mt-4 pt-4 border-top border-white-10 no-print">
        <div class="d-flex align-items-center justify-content-between">
            <span class="text-white-50 small">Eficiencia Financiera</span>
            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-1 rounded-pill x-small fw-black">OPTIMAL</span>
        </div>
    </div>
</div>
