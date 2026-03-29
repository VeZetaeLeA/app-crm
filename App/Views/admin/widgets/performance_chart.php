<div class="glass-morphism p-4 rounded-5 border-white-10 h-100 analytics-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white h5 fw-black mb-0">Rendimiento Operativo</h2>
        <select id="periodSelect"
            class="form-select form-select-sm bg-white-5 border-white-10 text-white x-small w-auto no-print">
            <option value="7d" selected>Últimos 7 días</option>
            <option value="30d">Últimos 30 días</option>
            <option value="3m">Últimos 3 meses</option>
            <option value="all">Todo</option>
        </select>
    </div>
    <div id="performanceChartContainer" style="height: 300px;" class="position-relative">
        <div class="skeleton-loader position-absolute w-100 h-100 d-flex flex-column gap-3 p-2">
            <div class="skeleton h-100"></div>
        </div>
        <canvas id="performanceChart"></canvas>
    </div>
</div>