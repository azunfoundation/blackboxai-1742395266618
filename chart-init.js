// Chart initialization and configuration
document.addEventListener('DOMContentLoaded', function() {
    // Show loading state
    const showLoading = (chartId) => {
        const container = document.querySelector(`#${chartId}-container`);
        if (container) {
            const loader = document.createElement('div');
            loader.className = 'chart-loading';
            loader.innerHTML = '<div class="loading-spinner"></div>';
            container.appendChild(loader);
        }
    };

    // Hide loading state
    const hideLoading = (chartId) => {
        const loader = document.querySelector(`#${chartId}-container .chart-loading`);
        if (loader) {
            loader.remove();
        }
    };

    // Format currency
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(value);
    };

    // Initialize charts
    const initializeCharts = async () => {
        try {
            showLoading('monthlyChart');
            showLoading('categoryChart');

            const response = await fetch('dashboard-charts.php');
            const chartData = await response.json();

            // Monthly Income vs Expenses Chart
            const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: {
                    labels: chartData.monthly.labels,
                    datasets: [{
                        label: 'Income',
                        data: chartData.monthly.income,
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                        hoverBackgroundColor: 'rgba(34, 197, 94, 0.3)'
                    }, {
                        label: 'Expenses',
                        data: chartData.monthly.expenses,
                        backgroundColor: 'rgba(239, 68, 68, 0.2)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8,
                        hoverBackgroundColor: 'rgba(239, 68, 68, 0.3)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                callback: (value) => formatCurrency(value),
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1f2937',
                            titleFont: {
                                size: 13,
                                weight: 'bold'
                            },
                            bodyColor: '#4b5563',
                            bodyFont: {
                                size: 12
                            },
                            padding: 12,
                            borderColor: 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => {
                                    return `${context.dataset.label}: ${formatCurrency(context.parsed.y)}`;
                                }
                            }
                        }
                    }
                }
            });

            // Category Expenses Chart
            const categoryChart = new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: chartData.category.labels,
                    datasets: [{
                        data: chartData.category.data,
                        backgroundColor: chartData.category.colors,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 15
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1f2937',
                            titleFont: {
                                size: 13,
                                weight: 'bold'
                            },
                            bodyColor: '#4b5563',
                            bodyFont: {
                                size: 12
                            },
                            padding: 12,
                            borderColor: 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value * 100) / total).toFixed(1);
                                    return `${context.label}: ${formatCurrency(value)} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            hideLoading('monthlyChart');
            hideLoading('categoryChart');

            // Add chart update functionality
            const updateCharts = async () => {
                try {
                    const response = await fetch('dashboard-charts.php');
                    const newData = await response.json();

                    // Update monthly chart
                    monthlyChart.data.labels = newData.monthly.labels;
                    monthlyChart.data.datasets[0].data = newData.monthly.income;
                    monthlyChart.data.datasets[1].data = newData.monthly.expenses;
                    monthlyChart.update();

                    // Update category chart
                    categoryChart.data.labels = newData.category.labels;
                    categoryChart.data.datasets[0].data = newData.category.data;
                    categoryChart.update();
                } catch (error) {
                    console.error('Error updating charts:', error);
                }
            };

            // Update charts every 5 minutes
            setInterval(updateCharts, 300000);

        } catch (error) {
            console.error('Error initializing charts:', error);
            hideLoading('monthlyChart');
            hideLoading('categoryChart');

            // Show error message
            const chartContainers = document.querySelectorAll('.chart-container');
            chartContainers.forEach(container => {
                container.innerHTML = `
                    <div class="flex items-center justify-center h-full">
                        <div class="text-red-500 text-center">
                            <i class="fas fa-exclamation-circle text-3xl mb-2"></i>
                            <p>Error loading chart data</p>
                        </div>
                    </div>
                `;
            });
        }
    };

    // Initialize charts when page loads
    initializeCharts();
});