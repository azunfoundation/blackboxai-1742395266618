document.addEventListener('DOMContentLoaded', function() {
    // Chart color schemes
    const colors = {
        income: {
            background: 'rgba(34, 197, 94, 0.2)',
            border: 'rgb(34, 197, 94)',
            hover: 'rgba(34, 197, 94, 0.4)'
        },
        expense: {
            background: 'rgba(239, 68, 68, 0.2)',
            border: 'rgb(239, 68, 68)',
            hover: 'rgba(239, 68, 68, 0.4)'
        },
        category: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
        ]
    };

    // Initialize Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        window.monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: [], // Will be populated with months
                datasets: [{
                    label: 'Income',
                    data: [], // Will be populated with income data
                    backgroundColor: colors.income.background,
                    borderColor: colors.income.border,
                    borderWidth: 1,
                    hoverBackgroundColor: colors.income.hover
                },
                {
                    label: 'Expenses',
                    data: [], // Will be populated with expense data
                    backgroundColor: colors.expense.background,
                    borderColor: colors.expense.border,
                    borderWidth: 1,
                    hoverBackgroundColor: colors.expense.hover
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('en-US', {
                                    style: 'currency',
                                    currency: 'USD',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize Category Chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        window.categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [], // Will be populated with category names
                datasets: [{
                    data: [], // Will be populated with category amounts
                    backgroundColor: colors.category,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${new Intl.NumberFormat('en-US', {
                                    style: 'currency',
                                    currency: 'USD'
                                }).format(value)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Handle chart type changes
    const monthlyChartType = document.getElementById('monthlyChartType');
    if (monthlyChartType) {
        monthlyChartType.addEventListener('change', function() {
            if (window.monthlyChart) {
                window.monthlyChart.config.type = this.value;
                window.monthlyChart.update('none');
            }
        });
    }

    const categoryChartType = document.getElementById('categoryChartType');
    if (categoryChartType) {
        categoryChartType.addEventListener('change', function() {
            if (window.categoryChart) {
                window.categoryChart.config.type = this.value;
                window.categoryChart.update('none');
            }
        });
    }

    // Handle data range changes
    const monthlyChartRange = document.getElementById('monthlyChartRange');
    if (monthlyChartRange) {
        monthlyChartRange.addEventListener('change', function() {
            updateChartData(this.value);
        });
    }

    // Function to update chart data
    function updateChartData(months = 12) {
        fetch(`metric-updates.php?months=${months}`)
            .then(response => response.json())
            .then(data => {
                if (window.monthlyChart) {
                    window.monthlyChart.data.labels = data.months;
                    window.monthlyChart.data.datasets[0].data = data.income;
                    window.monthlyChart.data.datasets[1].data = data.expenses;
                    window.monthlyChart.update('active');
                }

                if (window.categoryChart && data.categories) {
                    window.categoryChart.data.labels = data.categories.labels;
                    window.categoryChart.data.datasets[0].data = data.categories.data;
                    window.categoryChart.update('active');
                }
            })
            .catch(error => console.error('Error updating chart data:', error));
    }

    // Initial data load
    updateChartData();
});