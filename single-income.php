<?php
require_once 'header.php';
requireLogin();

$income = null;
$is_edit = false;

// Check if we're editing an existing income entry
if (isset($_GET['id'])) {
    $income_id = (int)$_GET['id'];
    $income = getIncome($income_id);
    $is_edit = true;

    if (!$income) {
        $_SESSION['message'] = "Income entry not found.";
        $_SESSION['message_type'] = "error";
        header("Location: archive-income.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $amount = (float)$_POST['amount'];
    $source = trim($_POST['source']);
    $description = trim($_POST['description']);

    // Validation
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if ($amount <= 0) {
        $errors[] = "Amount must be greater than 0";
    }
    if (empty($source)) {
        $errors[] = "Source is required";
    }

    if (empty($errors)) {
        if ($is_edit) {
            // Update existing income
            if (updateIncome($income['id'], $title, $amount, $source, $description)) {
                $_SESSION['message'] = "Income updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: archive-income.php");
                exit();
            } else {
                $_SESSION['message'] = "Error updating income entry.";
                $_SESSION['message_type'] = "error";
            }
        } else {
            // Add new income
            if (addIncome($title, $amount, $source, $description)) {
                $_SESSION['message'] = "Income added successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: archive-income.php");
                exit();
            } else {
                $_SESSION['message'] = "Error adding income entry.";
                $_SESSION['message_type'] = "error";
            }
        }
    }
}

// Predefined income sources
$sources = [
    'Salary',
    'Freelance',
    'Business',
    'Investments',
    'Rental',
    'Dividends',
    'Commission',
    'Bonus',
    'Gift',
    'Other'
];
?>

<div class="min-h-screen max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold"><?php echo $is_edit ? 'Edit Income' : 'Add New Income'; ?></h1>
        <a href="archive-income.php" class="text-blue-500 hover:text-blue-700">
            <i class="fas fa-arrow-left mr-2"></i> Back to Income
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
                   value="<?php echo $income ? htmlspecialchars($income['title']) : ''; ?>"
                   placeholder="Enter income title">
        </div>

        <!-- Amount -->
        <div class="mb-6">
            <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">Amount ($) *</label>
            <input type="number" id="amount" name="amount" required step="0.01" min="0.01"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   value="<?php echo $income ? htmlspecialchars($income['amount']) : ''; ?>"
                   placeholder="Enter amount">
        </div>

        <!-- Source -->
        <div class="mb-6">
            <label for="source" class="block text-gray-700 text-sm font-bold mb-2">Source *</label>
            <select id="source" name="source" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Select a source</option>
                <?php foreach ($sources as $src): ?>
                    <option value="<?php echo htmlspecialchars($src); ?>"
                            <?php echo ($income && $income['source'] === $src) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($src); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
            <textarea id="description" name="description" rows="4"
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                      placeholder="Enter description (optional)"><?php echo $income ? htmlspecialchars($income['description']) : ''; ?></textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?php echo $is_edit ? 'Update Income' : 'Add Income'; ?>
            </button>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>