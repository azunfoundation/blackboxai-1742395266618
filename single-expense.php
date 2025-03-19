<?php
require_once 'header.php';
requireLogin();

$expense = null;
$is_edit = false;

// Check if we're editing an existing expense
if (isset($_GET['id'])) {
    $expense_id = (int)$_GET['id'];
    $expense = getExpense($expense_id);
    $is_edit = true;

    if (!$expense) {
        $_SESSION['message'] = "Expense not found.";
        $_SESSION['message_type'] = "error";
        header("Location: archive-expense.php");
        exit();
    }

    // Check if expense is frozen
    if (isExpenseFrozen($expense_id)) {
        $_SESSION['message'] = "Cannot edit frozen expense. Unfreeze it first.";
        $_SESSION['message_type'] = "error";
        header("Location: archive-expense.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $amount = (float)$_POST['amount'];
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);

    // Validation
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if ($amount <= 0) {
        $errors[] = "Amount must be greater than 0";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }

    if (empty($errors)) {
        if ($is_edit) {
            // Update existing expense
            if (updateExpense($expense['id'], $title, $amount, $category, $description)) {
                $_SESSION['message'] = "Expense updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: archive-expense.php");
                exit();
            } else {
                $_SESSION['message'] = "Error updating expense.";
                $_SESSION['message_type'] = "error";
            }
        } else {
            // Add new expense
            if (addExpense($title, $amount, $category, $description)) {
                $_SESSION['message'] = "Expense added successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: archive-expense.php");
                exit();
            } else {
                $_SESSION['message'] = "Error adding expense.";
                $_SESSION['message_type'] = "error";
            }
        }
    }
}

// Predefined expense categories
$categories = [
    'Food & Dining',
    'Transportation',
    'Housing',
    'Utilities',
    'Healthcare',
    'Entertainment',
    'Shopping',
    'Education',
    'Travel',
    'Other'
];
?>

<div class="min-h-screen max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold"><?php echo $is_edit ? 'Edit Expense' : 'Add New Expense'; ?></h1>
        <a href="archive-expense.php" class="text-blue-500 hover:text-blue-700">
            <i class="fas fa-arrow-left mr-2"></i> Back to Expenses
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-lg shadow-lg p-6">
        <!-- Title -->
        <div class="mb-6">
            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Title *</label>
            <input type="text" id="title" name="title" required
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   value="<?php echo $expense ? htmlspecialchars($expense['title']) : ''; ?>"
                   placeholder="Enter expense title">
        </div>

        <!-- Amount -->
        <div class="mb-6">
            <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">Amount ($) *</label>
            <input type="number" id="amount" name="amount" required step="0.01" min="0.01"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   value="<?php echo $expense ? htmlspecialchars($expense['amount']) : ''; ?>"
                   placeholder="Enter amount">
        </div>

        <!-- Category -->
        <div class="mb-6">
            <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Category *</label>
            <select id="category" name="category" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Select a category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"
                            <?php echo ($expense && $expense['category'] === $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      placeholder="Enter description (optional)"><?php echo $expense ? htmlspecialchars($expense['description']) : ''; ?></textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Update Expense' : 'Add Expense'; ?>
            </button>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>