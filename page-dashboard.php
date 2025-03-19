<?php
require_once 'header.php';
requireLogin();

// Ensure proper cache control
setNoCacheHeaders();

// Regenerate session ID for security
if (!isset($_SESSION['last_regeneration']) || 
    (time() - $_SESSION['last_regeneration']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Verify session is still valid
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit();
}

$total_income = getTotalIncome();
$total_expense = getTotalExpense();
$balance = getBalance();
$recent_expenses = getExpenses(5);
$recent_incomes = getIncomes(5);
?>

<div class="min-h-screen">
    <h1 class="text-3xl font-bold mb-8">Financial Dashboard</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Income -->
        <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-600">Total Income</h3>
                <i class="fas fa-money-bill-wave text-green-500 text-2xl"></i>
            </div>
            <p id="totalIncome" class="text-3xl font-bold text-green-500">$<?php echo formatCurrency($total_income); ?></p>
            <p class="text-sm text-gray-500 mt-2">All time earnings</p>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-600">Total Expenses</h3>
                <i class="fas fa-receipt text-red-500 text-2xl"></i>
            </div>
            <p id="totalExpense" class="text-3xl font-bold text-red-500">$<?php echo formatCurrency($total_expense); ?></p>
            <p class="text-sm text-gray-500 mt-2">All time spending</p>
        </div>

        <!-- Current Balance -->
        <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-600">Current Balance</h3>
                <i class="fas fa-wallet text-blue-500 text-2xl"></i>
            </div>
            <p id="balance" class="text-3xl font-bold <?php echo $balance >= 0 ? 'text-blue-500' : 'text-red-500'; ?>">
                $<?php echo formatCurrency(abs($balance)); ?>
                <?php if ($balance < 0) echo ' (Deficit)'; ?>
            </p>
            <p class="text-sm text-gray-500 mt-2">Available funds</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Monthly Income vs Expenses Chart -->
        <div id="monthlyChart-container" class="chart-container bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="chart-title">Monthly Income vs Expenses</h3>
                <div class="flex space-x-4">
                    <select id="monthlyChartRange" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="6">Last 6 Months</option>
                        <option value="12" selected>Last 12 Months</option>
                        <option value="24">Last 24 Months</option>
                    </select>
                    <select id="monthlyChartType" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="bar" selected>Bar Chart</option>
                        <option value="line">Line Chart</option>
                    </select>
                </div>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="monthlyChart" class="chart-animate"></canvas>
            </div>
        </div>

        <!-- Expense by Category Chart -->
        <div id="categoryChart-container" class="chart-container bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="chart-title">Expenses by Category</h3>
                <div class="flex space-x-4">
                    <select id="categoryChartPeriod" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="month">This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year" selected>This Year</option>
                        <option value="all">All Time</option>
                    </select>
                    <select id="categoryChartType" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="doughnut" selected>Doughnut</option>
                        <option value="pie">Pie Chart</option>
                        <option value="bar">Bar Chart</option>
                    </select>
                </div>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="categoryChart" class="chart-animate"></canvas>
            </div>
        </div>
    </div>

    <!-- Metrics Filters -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex flex-wrap gap-4 items-center justify-between">
            <div class="flex items-center gap-4">
                <h3 class="text-lg font-semibold">Metrics Filters</h3>
                <button id="exportBtn" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition flex items-center">
                    <i class="fas fa-download mr-2"></i> Export Data
                </button>
            </div>
            <div class="flex flex-wrap gap-4">
                <button id="userFilterBtn" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition flex items-center">
                    <i class="fas fa-users mr-2"></i> Filter by User
                </button>
                <select id="dateRange" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month" selected>This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
                <div id="customDateRange" class="flex gap-2 hidden">
                    <input type="date" id="startDate" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <input type="date" id="endDate" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <select id="categoryFilter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all" selected>All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                    <?php endforeach; ?>
                </select>
                <button id="applyFilters" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                    Apply Filters
                </button>
                <button id="resetFilters" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Recent Expenses -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Recent Expenses</h3>
                <a href="archive-expense.php" class="text-blue-500 hover:text-blue-700">View All</a>
            </div>
            <?php if (empty($recent_expenses)): ?>
                <p class="text-gray-500 text-center py-4">No recent expenses</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recent_expenses as $expense): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <div>
                                <h4 class="font-semibold"><?php echo htmlspecialchars($expense['title']); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($expense['category']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-red-500 font-semibold">$<?php echo formatCurrency($expense['amount']); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('M d, Y', strtotime($expense['date_added'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Income -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Recent Income</h3>
                <a href="archive-income.php" class="text-blue-500 hover:text-blue-700">View All</a>
            </div>
            <?php if (empty($recent_incomes)): ?>
                <p class="text-gray-500 text-center py-4">No recent income</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recent_incomes as $income): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <div>
                                <h4 class="font-semibold"><?php echo htmlspecialchars($income['title']); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($income['source']); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-green-500 font-semibold">$<?php echo formatCurrency($income['amount']); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('M d, Y', strtotime($income['date_added'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js and Initialization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="chart-init.js"></script>
<script src="dashboard-filters.js"></script>

<!-- User Filter Modal -->
<div id="userFilterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Filter by User</h3>
            <div class="mt-2 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">User Type</label>
                    <select id="userTypeFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">All Users</option>
                        <option value="individual">Individual User</option>
                        <option value="department">Department</option>
                    </select>
                </div>
                <div id="userIdSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700">User ID</label>
                    <input type="text" id="userIdFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter User ID">
                </div>
                <div id="departmentSection" class="hidden">
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <select id="departmentFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Department</option>
                        <option value="finance">Finance</option>
                        <option value="sales">Sales</option>
                        <option value="marketing">Marketing</option>
                        <option value="operations">Operations</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button id="closeUserFilter" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button id="applyUserFilter" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// User Filter Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('userFilterModal');
    const userTypeFilter = document.getElementById('userTypeFilter');
    const userIdSection = document.getElementById('userIdSection');
    const departmentSection = document.getElementById('departmentSection');
    const closeUserFilter = document.getElementById('closeUserFilter');
    const applyUserFilter = document.getElementById('applyUserFilter');

    // Show/hide sections based on user type selection
    userTypeFilter.addEventListener('change', function() {
        userIdSection.classList.toggle('hidden', this.value !== 'individual');
        departmentSection.classList.toggle('hidden', this.value !== 'department');
    });

    // Open modal button in metrics filters
    document.getElementById('userFilterBtn').addEventListener('click', function() {
        modal.classList.remove('hidden');
    });

    // Close modal
    closeUserFilter.addEventListener('click', function() {
        modal.classList.add('hidden');
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });

    // Apply user filter
    applyUserFilter.addEventListener('click', function() {
        const filterType = userTypeFilter.value;
        const userId = document.getElementById('userIdFilter').value;
        const department = document.getElementById('departmentFilter').value;

        // Add these values to the chart update parameters
        const params = new URLSearchParams(window.location.search);
        params.set('user_type', filterType);
        if (filterType === 'individual') params.set('user_id', userId);
        if (filterType === 'department') params.set('department', department);

        // Update charts with new filters
        updateCharts();
        modal.classList.add('hidden');
    });
});
</script>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Export Data</h3>
            <div class="mt-2 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Export Format</label>
                    <select id="exportFormat" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="csv">CSV</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data Type</label>
                    <select id="exportType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">All Transactions</option>
                        <option value="income">Income Only</option>
                        <option value="expenses">Expenses Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date Range</label>
                    <select id="exportDateRange" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div id="exportCustomDateRange" class="hidden space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" id="exportStartDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" id="exportEndDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button id="closeExport" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button id="downloadExport" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                        Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Export Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const exportModal = document.getElementById('exportModal');
    const exportBtn = document.getElementById('exportBtn');
    const closeExport = document.getElementById('closeExport');
    const downloadExport = document.getElementById('downloadExport');
    const exportDateRange = document.getElementById('exportDateRange');
    const exportCustomDateRange = document.getElementById('exportCustomDateRange');

    // Show/hide custom date range
    exportDateRange.addEventListener('change', function() {
        exportCustomDateRange.classList.toggle('hidden', this.value !== 'custom');
    });

    // Open modal
    exportBtn.addEventListener('click', function() {
        exportModal.classList.remove('hidden');
    });

    // Close modal
    closeExport.addEventListener('click', function() {
        exportModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === exportModal) {
            exportModal.classList.add('hidden');
        }
    });

    // Handle export
    downloadExport.addEventListener('click', function() {
        const format = document.getElementById('exportFormat').value;
        const type = document.getElementById('exportType').value;
        const dateRange = exportDateRange.value;
        const startDate = document.getElementById('exportStartDate').value;
        const endDate = document.getElementById('exportEndDate').value;

        // Build export URL
        const params = new URLSearchParams({
            format: format,
            type: type,
            date_range: dateRange,
            start_date: startDate,
            end_date: endDate
        });

        // Trigger download
        window.location.href = `export-data.php?${params.toString()}`;
        
        // Close modal
        exportModal.classList.add('hidden');
    });
});
</script>

<?php require_once 'footer.php'; ?>
