<?php
require_once 'header.php';
requireLogin();

// Handle freeze/unfreeze
if (isset($_POST['freeze']) && isset($_POST['expense_id'])) {
    $expense_id = (int)$_POST['expense_id'];
    if (freezeExpense($expense_id)) {
        $_SESSION['message'] = "Expense frozen successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error freezing expense.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: archive-expense.php");
    exit();
}

if (isset($_POST['unfreeze']) && isset($_POST['expense_id'])) {
    $expense_id = (int)$_POST['expense_id'];
    if (unfreezeExpense($expense_id)) {
        $_SESSION['message'] = "Expense unfrozen successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error unfreezing expense.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: archive-expense.php");
    exit();
}

// Handle deletion
if (isset($_POST['delete']) && isset($_POST['expense_id'])) {
    $expense_id = (int)$_POST['expense_id'];
    if (!isExpenseFrozen($expense_id)) {
        if (deleteExpense($expense_id)) {
            $_SESSION['message'] = "Expense deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting expense.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Cannot delete frozen expense. Unfreeze it first.";
        $_SESSION['message_type'] = "error";
    }
    header("Location: archive-expense.php");
    exit();
}

// Get all expenses
$expenses = getExpenses();
?>

<div class="min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Expenses Archive</h1>
        <a href="single-expense.php" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">
            <i class="fas fa-plus mr-2"></i> Add New Expense
        </a>
    </div>

    <?php if (empty($expenses)): ?>
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <i class="fas fa-receipt text-gray-400 text-5xl mb-4"></i>
            <h2 class="text-2xl font-semibold text-gray-600 mb-2">No Expenses Found</h2>
            <p class="text-gray-500 mb-4">Start tracking your expenses by adding a new expense.</p>
            <a href="single-expense.php" class="inline-block bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">
                Add Your First Expense
            </a>
        </div>
    <?php else: ?>
        <!-- Expense List -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($expenses as $expense): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($expense['date_added'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($expense['title']); ?>
                                </div>
                                <?php if (!empty($expense['description'])): ?>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($expense['description']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($expense['category']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-500 font-semibold">
                                $<?php echo formatCurrency($expense['amount']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <?php if (!isExpenseFrozen($expense['id'])): ?>
                                        <a href="single-expense.php?id=<?php echo $expense['id']; ?>" 
                                           class="text-blue-500 hover:text-blue-700">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="archive-expense.php" method="POST" class="inline">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                            <button type="submit" name="freeze" class="text-blue-500 hover:text-blue-700" 
                                                    title="Freeze expense">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        </form>
                                        <form action="archive-expense.php" method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                            <button type="submit" name="delete" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="archive-expense.php" method="POST" class="inline">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['id']; ?>">
                                            <button type="submit" name="unfreeze" class="text-gray-500 hover:text-gray-700" 
                                                    title="Unfreeze expense">
                                                <i class="fas fa-lock-open"></i>
                                            </button>
                                        </form>
                                        <span class="text-gray-400" title="Editing is disabled while expense is frozen">
                                            <i class="fas fa-edit"></i>
                                        </span>
                                        <span class="text-gray-400" title="Deletion is disabled while expense is frozen">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Expenses Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Total Expenses:</p>
                    <p class="text-2xl font-bold text-red-500">$<?php echo formatCurrency(getTotalExpense()); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Average Expense:</p>
                    <p class="text-2xl font-bold text-blue-500">
                        $<?php echo formatCurrency(getTotalExpense() / count($expenses)); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>