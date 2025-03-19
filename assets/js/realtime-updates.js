document.addEventListener('DOMContentLoaded', function() {
    // Initialize EventSource for real-time updates
    const eventSource = new EventSource('metric-updates.php');
    let updateTimeout;

    // Handle incoming updates
    eventSource.onmessage = function(event) {
        const updates = JSON.parse(event.data);
        updateMetrics(updates);
    };

    // Handle connection error
    eventSource.onerror = function(error) {
        console.error('EventSource failed:', error);
        eventSource.close();
        
        // Fallback to polling if SSE fails
        startPolling();
    };

    // Polling fallback
    function startPolling() {
        setInterval(pollUpdates, 30000); // Poll every 30 seconds
    }

    function pollUpdates() {
        fetch('metric-updates.php')
            .then(response => response.json())
            .then(data => updateMetrics(data))
            .catch(error => console.error('Polling failed:', error));
    }

    // Update all metrics on the page
    function updateMetrics(data) {
        if (data.summary) {
            updateSummaryCards(data.summary);
        }

        if (data.charts && window.monthlyChart) {
            updateCharts(data.charts);
        }

        if (data.transactions) {
            updateTransactions(data.transactions);
        }

        showUpdateNotification();
    }

    // Update summary cards with animations
    function updateSummaryCards(summary) {
        const elements = {
            totalIncome: document.getElementById('totalIncome'),
            totalExpense: document.getElementById('totalExpense'),
            balance: document.getElementById('balance')
        };

        if (elements.totalIncome) {
            animateValue(elements.totalIncome, summary.total_income);
            highlightElement(elements.totalIncome);
        }

        if (elements.totalExpense) {
            animateValue(elements.totalExpense, summary.total_expense);
            highlightElement(elements.totalExpense);
        }

        if (elements.balance) {
            animateValue(elements.balance, summary.balance);
            highlightElement(elements.balance);
            elements.balance.classList.toggle('text-red-500', summary.balance < 0);
            elements.balance.classList.toggle('text-blue-500', summary.balance >= 0);
        }
    }

    // Animate value changes
    function animateValue(element, newValue) {
        const start = parseFloat(element.textContent.replace(/[^0-9.-]+/g, ''));
        const duration = 1000;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            const current = start + (newValue - start) * progress;
            element.textContent = formatCurrency(current);

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    }

    // Update charts with animations
    function updateCharts(chartData) {
        if (window.monthlyChart) {
            window.monthlyChart.data.datasets[0].data = chartData.monthly.income;
            window.monthlyChart.data.datasets[1].data = chartData.monthly.expenses;
            window.monthlyChart.update('active');
        }

        if (window.categoryChart) {
            window.categoryChart.data.labels = chartData.category.labels;
            window.categoryChart.data.datasets[0].data = chartData.category.data;
            window.categoryChart.update('active');
        }
    }

    // Update transaction lists with animations
    function updateTransactions(transactions) {
        updateTransactionList('recentExpenses', transactions.expenses, 'expense');
        updateTransactionList('recentIncome', transactions.income, 'income');
    }

    function updateTransactionList(containerId, transactions, type) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const colorClass = type === 'expense' ? 'text-red-500' : 'text-green-500';
        
        transactions.forEach(transaction => {
            const newItem = document.createElement('div');
            newItem.className = 'flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200 opacity-0';
            newItem.innerHTML = `
                <div>
                    <h4 class="font-semibold">${transaction.title}</h4>
                    <p class="text-sm text-gray-500">${transaction.category || transaction.source}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold ${colorClass}">$${formatCurrency(transaction.amount)}</p>
                    <p class="text-xs text-gray-500">${formatDate(transaction.date_added)}</p>
                </div>
            `;

            // Animate new transaction entry
            container.insertBefore(newItem, container.firstChild);
            requestAnimationFrame(() => {
                newItem.style.transition = 'opacity 0.5s ease-in';
                newItem.style.opacity = '1';
            });

            // Remove oldest transaction if list is too long
            if (container.children.length > 5) {
                const lastChild = container.lastElementChild;
                lastChild.style.opacity = '0';
                setTimeout(() => lastChild.remove(), 500);
            }
        });
    }

    // Highlight updated elements
    function highlightElement(element) {
        element.classList.add('highlight');
        setTimeout(() => element.classList.remove('highlight'), 2000);
    }

    // Show update notification
    function showUpdateNotification() {
        clearTimeout(updateTimeout);
        
        const notification = document.createElement('div');
        notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full';
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>Data updated</span>
            </div>
        `;

        document.body.appendChild(notification);
        requestAnimationFrame(() => {
            notification.style.transition = 'transform 0.3s ease-out';
            notification.style.transform = 'translateY(0)';
        });

        updateTimeout = setTimeout(() => {
            notification.style.transform = 'translateY(full)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Helper function to format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        }).format(amount);
    }

    // Helper function to format date
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
});