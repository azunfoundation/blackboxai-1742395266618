<?php
if (!defined('ABSPATH')) {
    exit;
}

$expense_tracker = new Expense_Tracker();
$user_id = get_current_user_id();

$total_income = $expense_tracker->get_total_income($user_id);
$total_expense = $expense_tracker->get_total_expenses($user_id);
$balance = $total_income - $total_expense;
$recent_expenses = $expense_tracker->get_recent_transactions('expenses', 5, $user_id);
$recent_incomes = $expense_tracker->get_recent_transactions('income', 5, $user_id);
?>

<div class="expense-tracker-dashboard min-h-screen p-6">
    <h1 class="text-3xl font-bold mb-8">Financial Dashboard</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Income -->
        <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-600">Total Income</h3>
                <i class="fas fa-money-bill-wave text-green-500 text-2xl"></i>
            </div>
            <p id="totalIncome" class="text-3xl font-bold text-green-500">$<?php echo number_format($total_income, 2); ?></p>
            <p class="text-sm text-gray-500 mt-2">All time earnings</p>
        </div>

        <!-- Total Expenses -->
        <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-600">Total Expenses</h3>
                <i class="fas fa-receipt text-red-500 text-2xl"></i>
            </div>
            <p id="totalExpense" class="text-3xl font-bold text-red-500">$<?php echo number_format($total_expense, 2); ?></p>
            <p class="text-sm text-gray-500 mt-2">All time spending</p>
        </div>

        <!-- Current Balance -->
        <div class="bg-white rounded-lg shadow-lg p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-600">Current Balance</h3>
                <i class="fas fa-wallet text-blue-500 text-2xl"></i>
            </div>
            <p id="balance" class="text-3xl font-bold <?php echo $balance >= 0 ? 'text-blue-500' : 'text-red-500'; ?>">
                $<?php echo number_format(abs($balance), 2); ?>
                <?php if ($balance < 0) echo ' (Deficit)'; ?>
            </p>
            <p class="text-sm text-gray-500 mt-2">Available funds</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex gap-4 mb-8">
        <button id="addExpenseBtn" class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition flex items-center">
            <i class="fas fa-minus-circle mr-2"></i> Add Expense
        </button>
        <button id="addIncomeBtn" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition flex items-center">
            <i class="fas fa-plus-circle mr-2"></i> Add Income
        </button>
        <button id="exportBtn" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition flex items-center">
            <i class="fas fa-download mr-2"></i> Export Data
        </button>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Monthly Income vs Expenses Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Monthly Overview</h3>
            <canvas id="monthlyChart" height="300"></canvas>
        </div>

        <!-- Expense Categories Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Expense Categories</h3>
            <canvas id="categoryChart" height="300"></canvas>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Recent Expenses -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Recent Expenses</h3>
                <a href="#" class="text-blue-500 hover:text-blue-700">View All</a>
            </div>
            <div id="recentExpenses" class="space-y-4">
                <?php foreach ($recent_expenses as $expense): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="font-semibold"><?php echo esc_html($expense->title); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo esc_html($expense->category); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-red-500 font-semibold">$<?php echo number_format($expense->amount, 2); ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($expense->date_added)); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Income -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Recent Income</h3>
                <a href="#" class="text-blue-500 hover:text-blue-700">View All</a>
            </div>
            <div id="recentIncome" class="space-y-4">
                <?php foreach ($recent_incomes as $income): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="font-semibold"><?php echo esc_html($income->title); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo esc_html($income->source); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-green-500 font-semibold">$<?php echo number_format($income->amount, 2); ?></p>
                            <p class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($income->date_added)); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'modals/add-expense-modal.php'; ?>
<?php include 'modals/add-income-modal.php'; ?>
<?php include 'modals/export-modal.php'; ?>