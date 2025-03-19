</div> <!-- Close container from header -->
    
    <footer class="bg-gray-800 text-white mt-auto py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">Expense Tracker</h3>
                    <p class="text-gray-400">
                        Track your expenses and income efficiently with our easy-to-use platform.
                    </p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="page-dashboard.php" class="text-gray-400 hover:text-white transition">Dashboard</a></li>
                        <li><a href="archive-income.php" class="text-gray-400 hover:text-white transition">Income</a></li>
                        <li><a href="archive-expense.php" class="text-gray-400 hover:text-white transition">Expenses</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i> support@expensetracker.com</li>
                        <li><i class="fas fa-phone mr-2"></i> +1 (555) 123-4567</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Expense Tracker. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for notifications -->
    <script>
        // Auto-hide notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const notification = document.querySelector('[role="alert"]');
                if (notification) {
                    notification.style.display = 'none';
                }
            }, 5000);
        });
    </script>
</body>
</html>