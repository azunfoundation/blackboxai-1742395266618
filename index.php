<?php 
require_once 'header.php';
?>

<div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-blue-600 text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Take Control of Your Finances
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                Track expenses, monitor income, and achieve your financial goals with our easy-to-use platform.
            </p>
            <?php if (!isLoggedIn()): ?>
                <div class="flex justify-center space-x-4">
                    <a href="register.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-50 transition">
                        Get Started
                    </a>
                    <a href="login.php" class="border-2 border-white text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-700 transition">
                        Login
                    </a>
                </div>
            <?php else: ?>
                <a href="page-dashboard.php" class="bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-blue-50 transition">
                    Go to Dashboard
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Why Choose Our Platform?</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Expense Tracking</h3>
                    <p class="text-gray-600">
                        Keep track of every penny with our intuitive expense tracking system.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Income Management</h3>
                    <p class="text-gray-600">
                        Monitor your income sources and track your earnings effectively.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4">Financial Analytics</h3>
                    <p class="text-gray-600">
                        Get insights into your spending patterns with detailed analytics.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-gray-50 py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">How It Works</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">
                        1
                    </div>
                    <h3 class="font-semibold mb-2">Create Account</h3>
                    <p class="text-gray-600">Sign up for free and set up your profile</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">
                        2
                    </div>
                    <h3 class="font-semibold mb-2">Add Transactions</h3>
                    <p class="text-gray-600">Record your income and expenses</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">
                        3
                    </div>
                    <h3 class="font-semibold mb-2">Track Progress</h3>
                    <p class="text-gray-600">Monitor your financial progress</p>
                </div>

                <!-- Step 4 -->
                <div class="text-center">
                    <div class="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4 text-xl font-bold">
                        4
                    </div>
                    <h3 class="font-semibold mb-2">Gain Insights</h3>
                    <p class="text-gray-600">Get detailed reports and analytics</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'footer.php';
?>