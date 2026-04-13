<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Records per Maand') }}</h3>
        <canvas id="actionsChart"></canvas>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Kosten per Medewerker') }}</h3>
        <canvas id="costChart"></canvas>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Acties naar Type') }}</h3>
    <canvas id="actionsTypeChart" style="height: 300px;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    const chartData = @json($chartData);

    // Records per Month Chart
    const actionsCtx = document.getElementById('actionsChart').getContext('2d');
    new Chart(actionsCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(chartData.actionsPerMonth),
            datasets: [{
                label: 'Records',
                data: Object.values(chartData.actionsPerMonth),
                backgroundColor: '#185FA5',
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Cost per Employee Chart
    const costCtx = document.getElementById('costChart').getContext('2d');
    new Chart(costCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(chartData.costPerEmployee),
            datasets: [{
                label: 'Kosten (€)',
                data: Object.values(chartData.costPerEmployee),
                backgroundColor: '#9FE1CB',
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Actions by Type Donut Chart
    const actionsTypeCtx = document.getElementById('actionsTypeChart').getContext('2d');
    new Chart(actionsTypeCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(chartData.actionsByType),
            datasets: [{
                data: Object.values(chartData.actionsByType),
                backgroundColor: ['#185FA5', '#9FE1CB', '#B5D4F4', '#E6F1FB', '#5B8FDC', '#7BA3D6'],
            }]
        },
        options: {
            responsive: true,
        }
    });
</script>
