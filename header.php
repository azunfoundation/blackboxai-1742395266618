<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">
    <?php require_once 'functions.php'; ?>
    
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="index.php" class="text-2xl font-bold flex items-center">
                    <i class="fas fa-wallet mr-2"></i>
                    Expense Tracker
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <div class="flex items-center space-x-6">
                        <a href="page-dashboard.php" class="hover:text-blue-200 transition">
                            <i class="fas fa-chart-line mr-1"></i> Dashboard
                        </a>
                        <a href="archive-income.php" class="hover:text-blue-200 transition">
                            <i class="fas fa-money-bill-wave mr-1"></i> Income
                        </a>
                        <a href="archive-expense.php" class="hover:text-blue-200 transition">
                            <i class="fas fa-receipt mr-1"></i> Expenses
                        </a>
                        <a href="calculator.php" class="hover:text-blue-200 transition">
                            <i class="fas fa-calculator mr-1"></i> Calculator
                        </a>
                        <div class="relative group">
                            <button class="hover:text-blue-200 transition">
                                <i class="fas fa-user-circle mr-1"></i> Account
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                                <a href="profile-settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-1"></i> Settings
                                </a>
                                <form action="logout.php" method="POST">
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                            <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-4">
                        <a href="login.php" class="hover:text-blue-200 transition">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="register.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo $_SESSION['message_type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>