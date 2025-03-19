document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const dateRange = document.getElementById('dateRange');
    const customDateRange = document.getElementById('customDateRange');

    // Show/hide custom date range based on selection
    dateRange.addEventListener('change', function() {
        customDateRange.classList.toggle('hidden', this.value !== 'custom');
    });

    // Chart type selectors
    const monthlyChartType = document.getElementById('monthlyChartType');
    const categoryChartType = document.getElementById('categoryChartType');
    const monthlyChartRange = document.getElementById('monthlyChartRange');
    const categoryChartPeriod = document.getElementById('categoryChartPeriod');

    // Filter buttons
    const applyFilters = document.getElementById('applyFilters');
    const resetFilters = document.getElementById('resetFilters');

    // Function to update charts
    async function updateCharts() {
        const params = new URLSearchParams({
            date_range: dateRange.value,
            start_date: startDate.value,
            end_date: endDate.value,
            category: document.getElementById('categoryFilter').value,
            chart_range: monthlyChartRange.value,
            chart_type: monthlyChartType.value,
            category_chart_type: categoryChartType.value,
            category_period: categoryChartPeriod.value
        });

        try {
            const response = await fetch(`dashboard-charts.php?${params.toString()}`);
            const data = await response.json();

            // Update Monthly Chart
            if (monthlyChart) {
                monthlyChart.data.labels = data.monthly.labels;
                monthlyChart.data.datasets[0].data = data.monthly.income;
                monthlyChart.data.datasets[1].data = data.monthly.expenses;
                monthlyChart.config.type = data.monthly.type;
                monthlyChart.update();
            }

            // Update Category Chart
            if (categoryChart) {
                categoryChart.data.labels = data.category.labels;
                categoryChart.data.datasets[0].data = data.category.data;
                categoryChart.config.type = data.category.type;
                categoryChart.update();
            }

            // Update metrics
            updateMetrics(data.metrics);
        } catch (error) {
            console.error('Error updating charts:', error);
            showError('Failed to update charts. Please try again.');
        }
    }

    // Function to update metrics display
    function updateMetrics(metrics) {
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        });

        document.getElementById('totalIncome').textContent = formatter.format(metrics.total_income);
        document.getElementById('totalExpenses').textContent = formatter.format(metrics.total_expenses);
        document.getElementById('netSavings').textContent = formatter.format(metrics.net_savings);
        document.getElementById('savingsRate').textContent = `${metrics.savings_rate.toFixed(1)}%`;
    }

    // Function to show error message
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
        errorDiv.innerHTML = `
            <span class="block sm:inline">${message}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </span>
        `;
        document.querySelector('.min-h-screen').insertBefore(errorDiv, document.querySelector('.grid'));

        // Remove error after 5 seconds
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // Event listeners for filter changes
    monthlyChartType.addEventListener('change', updateCharts);
    categoryChartType.addEventListener('change', updateCharts);
    monthlyChartRange.addEventListener('change', updateCharts);
    categoryChartPeriod.addEventListener('change', updateCharts);
    applyFilters.addEventListener('click', updateCharts);

    // Reset filters
    resetFilters.addEventListener('click', function() {
        dateRange.value = 'month';
        startDate.value = '';
        endDate.value = '';
        document.getElementById('categoryFilter').value = 'all';
        monthlyChartRange.value = '12';
        monthlyChartType.value = 'bar';
        categoryChartType.value = 'doughnut';
        categoryChartPeriod.value = 'year';
        customDateRange.classList.add('hidden');
        updateCharts();
    });

    // Initialize charts on page load
    updateCharts();
});