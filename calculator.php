<?php
require_once 'header.php';
requireLogin();
?>

<div class="min-h-screen max-w-4xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-8">Financial Calculator</h1>

        <!-- Calculator Tabs -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button class="tab-button active" data-tab="savings">
                        <i class="fas fa-piggy-bank mr-2"></i>Savings Calculator
                    </button>
                    <button class="tab-button" data-tab="loan">
                        <i class="fas fa-hand-holding-usd mr-2"></i>Loan Calculator
                    </button>
                    <button class="tab-button" data-tab="budget">
                        <i class="fas fa-chart-pie mr-2"></i>Budget Calculator
                    </button>
                </nav>
            </div>
        </div>

        <!-- Calculator Content -->
        <div class="calculator-content">
            <!-- Savings Calculator -->
            <div id="savings" class="tab-content active">
                <form id="savingsForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Initial Deposit ($)</label>
                            <input type="number" id="savingsInitial" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Monthly Contribution ($)</label>
                            <input type="number" id="savingsMonthly" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Annual Interest Rate (%)</label>
                            <input type="number" id="savingsRate" step="0.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time Period (Years)</label>
                            <input type="number" id="savingsYears" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">Calculate Savings</button>
                </form>
                <div id="savingsResult" class="mt-6 hidden">
                    <h3 class="text-lg font-semibold mb-4">Results</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Savings</p>
                            <p class="text-2xl font-bold text-blue-600" id="savingsTotalAmount"></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Interest Earned</p>
                            <p class="text-2xl font-bold text-green-600" id="savingsInterestEarned"></p>
                        </div>
                    </div>
                    <canvas id="savingsChart" class="mt-6" height="200"></canvas>
                </div>
            </div>

            <!-- Loan Calculator -->
            <div id="loan" class="tab-content hidden">
                <form id="loanForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loan Amount ($)</label>
                            <input type="number" id="loanAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Annual Interest Rate (%)</label>
                            <input type="number" id="loanRate" step="0.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loan Term (Years)</label>
                            <input type="number" id="loanYears" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">Calculate Loan</button>
                </form>
                <div id="loanResult" class="mt-6 hidden">
                    <h3 class="text-lg font-semibold mb-4">Results</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Monthly Payment</p>
                            <p class="text-2xl font-bold text-blue-600" id="loanMonthlyPayment"></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Payment</p>
                            <p class="text-2xl font-bold text-red-600" id="loanTotalPayment"></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Total Interest</p>
                            <p class="text-2xl font-bold text-green-600" id="loanTotalInterest"></p>
                        </div>
                    </div>
                    <canvas id="loanChart" class="mt-6" height="200"></canvas>
                </div>
            </div>

            <!-- Budget Calculator -->
            <div id="budget" class="tab-content hidden">
                <form id="budgetForm" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Monthly Income ($)</label>
                            <input type="number" id="budgetIncome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Savings Goal (%)</label>
                            <input type="number" id="budgetSavings" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">Calculate Budget</button>
                </form>
                <div id="budgetResult" class="mt-6 hidden">
                    <h3 class="text-lg font-semibold mb-4">Recommended Monthly Budget</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Savings (20%)</p>
                                <p class="text-2xl font-bold text-blue-600" id="budgetSavingsAmount"></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Housing (30%)</p>
                                <p class="text-2xl font-bold text-red-600" id="budgetHousingAmount"></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Transportation (15%)</p>
                                <p class="text-2xl font-bold text-green-600" id="budgetTransportAmount"></p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Utilities (10%)</p>
                                <p class="text-2xl font-bold text-purple-600" id="budgetUtilitiesAmount"></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Food (15%)</p>
                                <p class="text-2xl font-bold text-yellow-600" id="budgetFoodAmount"></p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">Other (10%)</p>
                                <p class="text-2xl font-bold text-gray-600" id="budgetOtherAmount"></p>
                            </div>
                        </div>
                    </div>
                    <canvas id="budgetChart" class="mt-6" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.tab-button');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Show target content
            contents.forEach(content => {
                content.classList.add('hidden');
                if (content.id === target) {
                    content.classList.remove('hidden');
                }
            });
        });
    });

    // Savings Calculator
    const savingsForm = document.getElementById('savingsForm');
    savingsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const initial = parseFloat(document.getElementById('savingsInitial').value) || 0;
        const monthly = parseFloat(document.getElementById('savingsMonthly').value) || 0;
        const rate = parseFloat(document.getElementById('savingsRate').value) || 0;
        const years = parseInt(document.getElementById('savingsYears').value) || 0;
        
        const monthlyRate = rate / 100 / 12;
        const months = years * 12;
        let total = initial;
        let totalInterest = 0;
        
        for (let i = 0; i < months; i++) {
            const interest = total * monthlyRate;
            totalInterest += interest;
            total += monthly + interest;
        }
        
        document.getElementById('savingsTotalAmount').textContent = `$${total.toFixed(2)}`;
        document.getElementById('savingsInterestEarned').textContent = `$${totalInterest.toFixed(2)}`;
        document.getElementById('savingsResult').classList.remove('hidden');
        
        // Create savings chart
        const ctx = document.getElementById('savingsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: years + 1}, (_, i) => `Year ${i}`),
                datasets: [{
                    label: 'Balance Over Time',
                    data: Array.from({length: years + 1}, (_, i) => {
                        const monthsElapsed = i * 12;
                        let balance = initial;
                        for (let j = 0; j < monthsElapsed; j++) {
                            balance += monthly + (balance * monthlyRate);
                        }
                        return balance;
                    }),
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });

    // Loan Calculator
    const loanForm = document.getElementById('loanForm');
    loanForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const amount = parseFloat(document.getElementById('loanAmount').value) || 0;
        const rate = parseFloat(document.getElementById('loanRate').value) || 0;
        const years = parseInt(document.getElementById('loanYears').value) || 0;
        
        const monthlyRate = rate / 100 / 12;
        const months = years * 12;
        const monthlyPayment = amount * monthlyRate * Math.pow(1 + monthlyRate, months) / (Math.pow(1 + monthlyRate, months) - 1);
        const totalPayment = monthlyPayment * months;
        const totalInterest = totalPayment - amount;
        
        document.getElementById('loanMonthlyPayment').textContent = `$${monthlyPayment.toFixed(2)}`;
        document.getElementById('loanTotalPayment').textContent = `$${totalPayment.toFixed(2)}`;
        document.getElementById('loanTotalInterest').textContent = `$${totalInterest.toFixed(2)}`;
        document.getElementById('loanResult').classList.remove('hidden');
        
        // Create loan chart
        const ctx = document.getElementById('loanChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Principal', 'Interest'],
                datasets: [{
                    data: [amount, totalInterest],
                    backgroundColor: ['rgb(59, 130, 246)', 'rgb(239, 68, 68)']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });

    // Budget Calculator
    const budgetForm = document.getElementById('budgetForm');
    budgetForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const income = parseFloat(document.getElementById('budgetIncome').value) || 0;
        const savingsPercent = parseFloat(document.getElementById('budgetSavings').value) || 20;
        
        const savings = income * (savingsPercent / 100);
        const housing = income * 0.3;
        const transport = income * 0.15;
        const utilities = income * 0.1;
        const food = income * 0.15;
        const other = income * 0.1;
        
        document.getElementById('budgetSavingsAmount').textContent = `$${savings.toFixed(2)}`;
        document.getElementById('budgetHousingAmount').textContent = `$${housing.toFixed(2)}`;
        document.getElementById('budgetTransportAmount').textContent = `$${transport.toFixed(2)}`;
        document.getElementById('budgetUtilitiesAmount').textContent = `$${utilities.toFixed(2)}`;
        document.getElementById('budgetFoodAmount').textContent = `$${food.toFixed(2)}`;
        document.getElementById('budgetOtherAmount').textContent = `$${other.toFixed(2)}`;
        document.getElementById('budgetResult').classList.remove('hidden');
        
        // Create budget chart
        const ctx = document.getElementById('budgetChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Savings', 'Housing', 'Transportation', 'Utilities', 'Food', 'Other'],
                datasets: [{
                    data: [savings, housing, transport, utilities, food, other],
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(239, 68, 68)',
                        'rgb(34, 197, 94)',
                        'rgb(168, 85, 247)',
                        'rgb(234, 179, 8)',
                        'rgb(107, 114, 128)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    });
});
</script>

<style>
.tab-button {
    @apply px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap pb-4 border-b-2 border-transparent;
}

.tab-button.active {
    @apply border-blue-500 text-blue-600;
}

.calculator-content {
    min-height: 400px;
}
</style>

<?php require_once 'footer.php'; ?>