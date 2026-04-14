<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">

    {{-- Acties per maand --}}
    <div id="actions-chart-container" class="border border-white/8 bg-[#131928] rounded-xl p-5">
        <h3 class="text-sm font-medium text-gray-300 mb-4">Acties per maand</h3>
        <canvas id="actionsChart" height="120"></canvas>
    </div>

    {{-- Kosten per maand --}}
    <div id="costs-month-chart-container" class="border border-white/8 bg-[#131928] rounded-xl p-5">
        <h3 class="text-sm font-medium text-gray-300 mb-4">Kosten per maand (€)</h3>
        <canvas id="kostenMaandChart" height="120"></canvas>
    </div>

    {{-- Kosten per medewerker --}}
    <div id="costs-employee-chart-container" class="border border-white/8 bg-[#131928] rounded-xl p-5">
        <h3 class="text-sm font-medium text-gray-300 mb-4">Kosten per medewerker (€)</h3>
        <canvas id="costChart" height="120"></canvas>
    </div>

    {{-- Actietypes verdeling --}}
    <div id="actions-type-chart-container" class="border border-white/8 bg-[#131928] rounded-xl p-5">
        <h3 class="text-sm font-medium text-gray-300 mb-4">Verdeling actietypes</h3>
        <canvas id="actionsTypeChart" height="120"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    const chartData = @json($chartData ?? ['actionsPerMonth' => [], 'costPerEmployee' => [], 'actionsByType' => []]);
    const kostenPerMaand = @json($kostenPerMaand ?? []);
    const selectedWidgets = @json($selectedWidgets ?? []);

    // Store ORIGINAL EUR data - CRITICAL: Never modify these, always convert FROM these
    window.originalChartData = JSON.parse(JSON.stringify(chartData));
    window.originalKostenPerMaandData = JSON.parse(JSON.stringify(kostenPerMaand));
    console.log('✓ Original EUR data stored:', {
        originalChartData: window.originalChartData,
        originalKostenPerMaandData: window.originalKostenPerMaandData
    });

    const tickColor = '#6b7280';
    const gridColor = 'rgba(255,255,255,0.04)';

    const baseScales = {
        x: {
            ticks: {
                color: tickColor,
                font: {
                    size: 11
                }
            },
            grid: {
                color: gridColor
            }
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: tickColor,
                font: {
                    size: 11
                }
            },
            grid: {
                color: gridColor
            }
        }
    };
    const baseLegend = {
        labels: {
            color: '#9ca3af',
            font: {
                size: 11
            },
            boxWidth: 10
        }
    };

    // Acties per maand
    window.actionsPerMonthChart = new Chart(document.getElementById('actionsChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(chartData.actionsPerMonth),
            datasets: [{
                label: 'Acties',
                data: Object.values(chartData.actionsPerMonth),
                backgroundColor: 'rgba(59,130,246,0.7)',
                borderColor: '#3b82f6',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: baseScales
        }
    });
    if (typeof window.storeChartInstance !== 'undefined') {
        window.storeChartInstance('actionsPerMonth', window.actionsPerMonthChart);
    }

    // Kosten per maand
    window.kostenPerMaandChart = new Chart(document.getElementById('kostenMaandChart'), {
        type: 'line',
        data: {
            labels: Object.keys(kostenPerMaand),
            datasets: [{
                label: 'Kosten (€)',
                data: Object.values(kostenPerMaand),
                backgroundColor: 'rgba(6,182,212,0.15)',
                borderColor: '#06b6d4',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#06b6d4',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: baseScales
        }
    });
    if (typeof window.storeChartInstance !== 'undefined') {
        window.storeChartInstance('kostenPerMaand', window.kostenPerMaandChart);
    }

    // Kosten per medewerker
    window.costPerEmployeeChart = new Chart(document.getElementById('costChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(chartData.costPerEmployee),
            datasets: [{
                label: 'Kosten (€)',
                data: Object.values(chartData.costPerEmployee),
                backgroundColor: 'rgba(159,225,203,0.7)',
                borderColor: '#9FE1CB',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: baseScales
        }
    });
    if (typeof window.storeChartInstance !== 'undefined') {
        window.storeChartInstance('costPerEmployee', window.costPerEmployeeChart);
    }

    // Actietypes donut
    window.actionsByTypeChart = new Chart(document.getElementById('actionsTypeChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(chartData.actionsByType),
            datasets: [{
                data: Object.values(chartData.actionsByType),
                backgroundColor: ['#3b82f6', '#06b6d4', '#9FE1CB', '#fbbf24', '#f87171', '#a78bfa'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    ...baseLegend,
                    position: 'bottom'
                }
            }
        }
    });
    if (typeof window.storeChartInstance !== 'undefined') {
        window.storeChartInstance('actionsByType', window.actionsByTypeChart);
    }

    // Hide chart containers that aren't selected
    window.hideUnselectedCharts = function() {
        if (!selectedWidgets.includes('actions_per_month')) {
            const container = document.getElementById('actions-chart-container');
            if (container) container.style.display = 'none';
        }
        if (!selectedWidgets.includes('costs_per_month')) {
            const container = document.getElementById('costs-month-chart-container');
            if (container) container.style.display = 'none';
        }
        if (!selectedWidgets.includes('costs_per_employee')) {
            const container = document.getElementById('costs-employee-chart-container');
            if (container) container.style.display = 'none';
        }
        if (!selectedWidgets.includes('actions_by_type')) {
            const container = document.getElementById('actions-type-chart-container');
            if (container) container.style.display = 'none';
        }
    };

    // Call immediately to hide unselected charts
    window.hideUnselectedCharts();
</script>
