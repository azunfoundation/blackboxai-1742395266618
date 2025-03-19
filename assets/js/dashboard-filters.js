document.addEventListener('DOMContentLoaded', function() {
    // Modal Elements
    const modals = {
        expense: document.getElementById('addExpenseModal'),
        income: document.getElementById('addIncomeModal'),
        export: document.getElementById('exportModal')
    };

    // Button Elements
    const buttons = {
        addExpense: document.getElementById('addExpenseBtn'),
        addIncome: document.getElementById('addIncomeBtn'),
        export: document.getElementById('exportBtn'),
        applyFilters: document.getElementById('applyFilters'),
        resetFilters: document.getElementById('resetFilters')
    };

    // Filter Elements
    const filters = {
        dateRange: document.getElementById('dateRange'),
        customDateRange: document.getElementById('customDateRange'),
        startDate: document.getElementById('startDate'),
        endDate: document.getElementById('endDate'),
        category: document.getElementById('categoryFilter')
    };

    // Modal Controls
    function setupModal(modalId, openBtnId, closeBtnId) {
        const modal = document.getElementById(modalId);
        const openBtn = document.getElementById(openBtnId);
        const closeBtn = document.getElementById(closeBtnId);

        if (modal && openBtn && closeBtn) {
            openBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
            });

            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            // Close on outside click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }
    }

    // Setup all modals
    setupModal('addExpenseModal', 'addExpenseBtn', 'closeExpenseModal');
    setupModal('addIncomeModal', 'addIncomeBtn', 'closeIncomeModal');
    setupModal('exportModal', 'exportBtn', 'closeExportModal');

    // Form Submissions
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        expenseForm.addEventListener('submit', handleFormSubmit);
    }

    const incomeForm = document.getElementById('incomeForm');
    if (incomeForm) {
        incomeForm.addEventListener('submit', handleFormSubmit);
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

        fetch('metric-updates.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal
                form.closest('.modal').classList.add('hidden');
                
                // Show success notification
                showNotification('Entry saved successfully!', 'success');
                
                // Reset form
                form.reset();
                
                // Refresh data
                updateDashboard();
            } else {
                showNotification(data.message || 'Error saving entry', 'error');
            }
        })
        .catch(error => {
            showNotification('Error saving entry', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = form.id === 'expenseForm' ? 
                '<i class="fas fa-minus-circle mr-2"></i>Add Expense' : 
                '<i class="fas fa-plus-circle mr-2"></i>Add Income';
        });
    }

    // Date Range Filter
    if (filters.dateRange) {
        filters.dateRange.addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            filters.customDateRange.classList.toggle('hidden', !isCustom);
            
            if (isCustom) {
                filters.startDate.required = true;
                filters.endDate.required = true;
            } else {
                filters.startDate.required = false;
                filters.endDate.required = false;
                
                // Auto-apply non-custom filters
                applyFilters();
            }
        });
    }

    // Apply Filters
    if (buttons.applyFilters) {
        buttons.applyFilters.addEventListener('click', applyFilters);
    }

    function applyFilters() {
        const filterData = {
            date_range: filters.dateRange.value,
            start_date: filters.startDate.value,
            end_date: filters.endDate.value,
            category: filters.category.value
        };

        // Show loading state
        showLoadingState(true);

        fetch('metric-updates.php?' + new URLSearchParams(filterData))
            .then(response => response.json())
            .then(data => {
                updateDashboardWithData(data);
                showNotification('Filters applied successfully', 'success');
            })
            .catch(error => {
                console.error('Error applying filters:', error);
                showNotification('Error applying filters', 'error');
            })
            .finally(() => {
                showLoadingState(false);
            });
    }

    // Reset Filters
    if (buttons.resetFilters) {
        buttons.resetFilters.addEventListener('click', resetFilters);
    }

    function resetFilters() {
        // Reset filter values
        filters.dateRange.value = 'month';
        filters.category.value = 'all';
        filters.customDateRange.classList.add('hidden');
        filters.startDate.value = '';
        filters.endDate.value = '';

        // Apply reset
        applyFilters();
    }

    // Helper Functions
    function showLoadingState(isLoading) {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.toggle('hidden', !isLoading);
        }
    }

    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg transform translate-y-full transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.transform = 'translateY(0)';
        });

        // Remove after delay
        setTimeout(() => {
            notification.style.transform = 'translateY(full)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    function updateDashboard() {
        // Refresh all dashboard components
        updateChartData();
        updateMetrics();
        updateTransactions();
    }

    // Initial load
    updateDashboard();
});