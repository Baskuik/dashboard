<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acties per Maand</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="actionsChart"></canvas>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kosten per Maand (€)</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="costChart"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 mb-6">
    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kosten per Medewerker</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="costPerWorkerChart"></canvas>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acties naar Type</h3>
        <div style="position: relative; height: 300px;">
            <canvas id="actionTypeChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Inline data
    const chartData = @json($chartData ?? []);
    console.log('Chart data:', chartData);

    // Initialize charts after short delay to ensure Chart.js is loaded
    setTimeout(function() {
        // Acties per Maand - LINE CHART
        const actionsCtx = document.getElementById('actionsChart');
        if (actionsCtx && chartData.actionsPerMonth) {
            new Chart(actionsCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(chartData.actionsPerMonth),
                    datasets: [{
                        label: 'Acties',
                        data: Object.values(chartData.actionsPerMonth),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Kosten per Maand - LINE CHART
        const costCtx = document.getElementById('costChart');
        if (costCtx && chartData.costPerMonth) {
            new Chart(costCtx, {
                type: 'line',
                data: {
                    labels: Object.keys(chartData.costPerMonth),
                    datasets: [{
                        label: 'Kosten (€)',
                        data: Object.values(chartData.costPerMonth),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: 'white',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Kosten per Medewerker - BAR CHART
        const workerCtx = document.getElementById('costPerWorkerChart');
        if (workerCtx && chartData.costPerEmployee) {
            new Chart(workerCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(chartData.costPerEmployee),
                    datasets: [{
                        label: 'Kosten (€)',
                        data: Object.values(chartData.costPerEmployee),
                        backgroundColor: 'rgb(139, 92, 246)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Acties naar Type - DOUGHNUT CHART
        const typeCtx = document.getElementById('actionTypeChart');
        if (typeCtx && chartData.actionsByType) {
            new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(chartData.actionsByType),
                    datasets: [{
                        data: Object.values(chartData.actionsByType),
                        backgroundColor: ['rgb(59, 130, 246)', 'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)', 'rgb(239, 68, 68)', 'rgb(139, 92, 246)',
                            'rgb(236, 72, 153)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        }
    }, 100);
</script>
