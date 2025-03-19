<?php if (!defined('ABSPATH')) exit; ?>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Export Financial Data</h3>
            <form id="exportForm" class="space-y-4">
                <?php wp_nonce_field('expense_tracker_form', 'expense_tracker_nonce'); ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Export Format</label>
                    <select name="format" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="csv">CSV</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Data Type</label>
                    <select name="type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">All Transactions</option>
                        <option value="income">Income Only</option>
                        <option value="expenses">Expenses Only</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <select name="date_range" id="exportDateRange" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <div id="customDateRange" class="space-y-4 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" id="closeExportModal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition flex items-center">
                        <i class="fas fa-download mr-2"></i>
                        Export Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Export Progress Modal -->
<div id="exportProgressModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Preparing Your Export</h3>
            <p class="text-sm text-gray-500 mt-2">Please wait while we generate your file...</p>
        </div>
    </div>
</div>

<!-- Success Notification -->
<div id="exportSuccessNotification" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-full opacity-0 transition-all duration-300">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>Export completed successfully!</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportDateRange = document.getElementById('exportDateRange');
    const customDateRange = document.getElementById('customDateRange');
    
    exportDateRange.addEventListener('change', function() {
        customDateRange.classList.toggle('hidden', this.value !== 'custom');
        
        if (this.value === 'custom') {
            document.querySelector('input[name="start_date"]').required = true;
            document.querySelector('input[name="end_date"]').required = true;
        } else {
            document.querySelector('input[name="start_date"]').required = false;
            document.querySelector('input[name="end_date"]').required = false;
        }
    });
});
</script>