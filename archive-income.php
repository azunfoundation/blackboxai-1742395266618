<?php
require_once 'header.php';
requireLogin();

// Handle deletion
if (isset($_POST['delete']) && isset($_POST['income_id'])) {
    $income_id = (int)$_POST['income_id'];
    if (deleteIncome($income_id)) {
        $_SESSION['message'] = "Income entry deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting income entry.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: archive-income.php");
    exit();
}

// Get all income entries
$incomes = getIncomes();
?>

<div class="min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Income Archive</h1>
        <a href="single-income.php" class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
            <i class="fas fa-plus mr-2"></i> Add New Income
        </a>
    </div>

    <?php if (empty($incomes)): ?>
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-money-bill-wave text-gray-400 text-5xl mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-600 mb-2">No Income Entries Found</h2>
            <p class="text-gray-500 mb-4">Start tracking your income by adding a new entry.</p>
            <a href="single-income.php" class="inline-block bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
                Add Your First Income Entry
            </a>
        </div>
    <?php else: ?>
        <!-- Income List -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($incomes as $income): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($income['date_added'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($income['title']); ?>
                                </div>
                                <?php if (!empty($income['description'])): ?>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($income['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <?php echo htmlspecialchars($income['source']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-500 font-semibold">
                                $<?php echo formatCurrency($income['amount']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="single-income.php?id=<?php echo $income['id']; ?>" 
                                       class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="archive-income.php" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this income entry?');">
                                        <input type="hidden" name="income_id" value="<?php echo $income['id']; ?>">
                                        <button type="submit" name="delete" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Income Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Total Income:</p>
                    <p class="text-2xl font-bold text-green-500">$<?php echo formatCurrency(getTotalIncome()); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Average Income:</p>
                    <p class="text-2xl font-bold text-blue-500">
                        $<?php echo formatCurrency(getTotalIncome() / count($incomes)); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>