<div class="glass-morphism p-4 rounded-5 border-white-10 h-100 d-flex flex-column analytics-card">
    <h2 class="text-white h5 fw-black mb-4">Estado de Solicitudes</h2>
    <div id="resourceChartContainer" class="flex-grow-1 position-relative" style="height: 220px;">
        <div class="skeleton-loader position-absolute w-100 h-100 d-flex align-items-end justify-content-around pb-4">
            <div class="skeleton" style="width: 20%; height: 60%; border-radius: 4px;"></div>
            <div class="skeleton" style="width: 20%; height: 80%; border-radius: 4px;"></div>
            <div class="skeleton" style="width: 20%; height: 40%; border-radius: 4px;"></div>
        </div>
        <canvas id="resourceChart"></canvas>
    </div>
    <div class="mt-4 pt-4 border-top border-white-5 no-print">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-white-50 x-small">Salud del Sistema</span>
            <span class="text-success x-small fw-bold">Óptima</span>
        </div>
        <div class="progress bg-white-5" style="height: 4px;">
            <div class="progress-bar bg-success shadow-gold" style="width: 100%"></div>
        </div>
    </div>
</div>