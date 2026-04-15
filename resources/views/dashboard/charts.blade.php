<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">

    {{-- Acties per maand --}}
    <div id="actions-chart-container"
        class="border border-white/8 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-300">Acties per maand</h3>
            <span
                class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 rounded">Trend</span>
        </div>
        <canvas id="actionsChart" height="120"></canvas>
    </div>

    {{-- Kosten per maand --}}
    <div id="costs-month-chart-container"
        class="border border-white/8 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-300">Kosten per maand (€)</h3>
            <span
                class="text-xs px-2 py-1 bg-cyan-100 dark:bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 rounded">Trend</span>
        </div>
        <canvas id="kostenMaandChart" height="120"></canvas>
    </div>

    {{-- Kosten per medewerker --}}
    <div id="costs-employee-chart-container"
        class="border border-white/8 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-300">Kosten per medewerker (€)</h3>
            <span
                class="text-xs px-2 py-1 bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300 rounded">Vergelijking</span>
        </div>
        <canvas id="costChart" height="120"></canvas>
    </div>

    {{-- Actietypes verdeling --}}
    <div id="actions-type-chart-container"
        class="border border-white/8 dark:border-white/8 bg-white dark:bg-[#131928] rounded-xl p-5 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-300">Verdeling actietypes</h3>
            <span
                class="text-xs px-2 py-1 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 rounded">Verdeling</span>
        </div>
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

    // Enhanced color palette for better contrast in both light and dark mode
    const colorPalette = {
        blue: {
            bg: 'rgba(59,130,246,0.8)',
            border: '#3b82f6',
            light: '#dbeafe'
        },
        cyan: {
            bg: 'rgba(6,182,212,0.8)',
            border: '#06b6d4',
            light: '#cffafe'
        },
        green: {
            bg: 'rgba(34,197,94,0.8)',
            border: '#22c55e',
            light: '#dcfce7'
        },
        emerald: {
            bg: 'rgba(159,225,203,0.8)',
            border: '#9FE1CB',
            light: '#d1fae5'
        },
        amber: {
            bg: 'rgba(251,191,36,0.8)',
            border: '#fbbf24',
            light: '#fef3c7'
        },
        red: {
            bg: 'rgba(248,113,113,0.8)',
            border: '#f87171',
            light: '#fee2e2'
        },
        violet: {
            bg: 'rgba(167,139,250,0.8)',
            border: '#a78bfa',
            light: '#ede9fe'
        },
    };

    const tickColor = 'rgb(107, 114, 128)';
    const gridColor = 'rgba(255,255,255,0.08)';
    const lightTickColor = 'rgb(75, 85, 99)';
    const lightGridColor = 'rgba(0,0,0,0.06)';

    // Detect dark mode
    const isDarkMode = () => document.documentElement.classList.contains('dark');

    const getChartColors = () => ({
        tickColor: isDarkMode() ? tickColor : lightTickColor,
        gridColor: isDarkMode() ? gridColor : lightGridColor,
    });

    const baseScales = {
        x: {
            ticks: {
                color: getChartColors().tickColor,
                font: {
                    size: 11
                }
            },
            grid: {
                color: getChartColors().gridColor,
                drawBorder: false
            }
        },
        y: {
            beginAtZero: true,
            ticks: {
                color: getChartColors().tickColor,
                font: {
                    size: 11
                }
            },
            grid: {
                color: getChartColors().gridColor,
                drawBorder: false
            }
        }
    };

    const baseLegend = {
        labels: {
            color: isDarkMode() ? '#9ca3af' : '#6b7280',
            font: {
                size: 11,
                weight: '500'
            },
            boxWidth: 12,
            padding: 15,
            usePointStyle: true,
            pointStyle: 'circle'
        }
    };

    const baseTooltip = {
        backgroundColor: isDarkMode() ? 'rgba(15,21,33,0.95)' : 'rgba(255,255,255,0.95)',
        titleColor: isDarkMode() ? '#f3f4f6' : '#1f2937',
        bodyColor: isDarkMode() ? '#e5e7eb' : '#374151',
        borderColor: isDarkMode() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
        borderWidth: 1,
        padding: 12,
        displayColors: true,
        callbacks: {
            label: function(context) {
                let label = context.dataset.label || '';
                if (label) label += ': ';
                if (context.parsed.y !== null) {
                    label += Number.isInteger(context.parsed.y) ?
                        context.parsed.y.toLocaleString() :
                        context.parsed.y.toFixed(2);
                }
                return label;
            }
        }
    };

    // Acties per maand Chart
    window.actionsPerMonthChart = new Chart(document.getElementById('actionsChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(chartData.actionsPerMonth),
            datasets: [{
                label: 'Acties',
                data: Object.values(chartData.actionsPerMonth),
                backgroundColor: colorPalette.blue.bg,
                borderColor: colorPalette.blue.border,
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(59,130,246,1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: baseTooltip
            },
            scales: baseScales,
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });
    if (typeof window.storeChartInstance !== 'undefined') {
        window.storeChartInstance('actionsPerMonth', window.actionsPerMonthChart);
    }

    // Kosten per maand Chart
    window.kostenPerMaandChart = new Chart(document.getElementById('kostenMaandChart'), {
        type: 'line',
        data: {
            labels: Object.keys(kostenPerMaand),
            datasets: [{
                label: 'Kosten (€)',
                data: Object.values(kostenPerMaand),
                backgroundColor: 'rgba(6,182,212,0.12)',
                borderColor: colorPalette.cyan.border,
                borderWidth: 2.5,
                tension: 0.45,
                fill: true,
                pointBackgroundColor: colorPalette.cyan.border,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBorderColor: 'rgba(6,182,212,0.3)',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: baseTooltip
            },
            scales: baseScales,
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });
    if (typeof window.storeChartInstance !== 'undefined') {
        window.storeChartInstance('kostenPerMaand', window.kostenPerMaandChart);
    }

    // Kosten per medewerker Chart
    window.costPerEmployeeChart = new Chart(document.getElementById('costChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(chartData.costPerEmployee),
            datasets: [{
                label: 'Kosten (€)',
                data: Object.values(chartData.costPerEmployee),
                backgroundColor: colorPalette.green.bg,
                borderColor: colorPalette.green.border,
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(34,197,94,1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: Object.keys(chartData.costPerEmployee).length > 8 ? 'y' : 'x',
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: baseTooltip
            },
            scales: baseScales,
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            }
        }
    });
    if (typeof window.storeChartInstance !== 'undefined') {
        window.storeChartInstance('costPerEmployee', window.costPerEmployeeChart);
    }

    // Actietypes Doughnut Chart
    window.actionsByTypeChart = new Chart(document.getElementById('actionsTypeChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(chartData.actionsByType),
            datasets: [{
                data: Object.values(chartData.actionsByType),
                backgroundColor: [
                    colorPalette.blue.bg,
                    colorPalette.cyan.bg,
                    colorPalette.emerald.bg,
                    colorPalette.amber.bg,
                    colorPalette.red.bg,
                    colorPalette.violet.bg,
                ],
                borderColor: isDarkMode() ? '#131928' : '#ffffff',
                borderWidth: 2,
                hoverOffset: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    ...baseLegend,
                    position: 'bottom'
                },
                tooltip: {
                    ...baseTooltip,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
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
