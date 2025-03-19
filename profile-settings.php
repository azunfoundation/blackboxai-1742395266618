<?php
require_once 'header.php';
requireLogin();

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, currency_preference, notification_preference FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Profile Update
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $currency = $_POST['currency'];
        $notifications = isset($_POST['notifications']) ? 1 : 0;
        
        if (empty($username)) {
            $errors[] = "Username is required";
        }
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        
        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, currency_preference = ?, notification_preference = ? WHERE id = ?");
            $stmt->bind_param("sssii", $username, $email, $currency, $notifications, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Profile updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: profile-settings.php");
                exit();
            } else {
                $errors[] = "Error updating profile";
            }
        }
    }
    
    // Password Update
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password)) {
            $errors[] = "Current password is required";
        }
        if (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors)) {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (password_verify($current_password, $result['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Password updated successfully!";
                    $_SESSION['message_type'] = "success";
                    header("Location: profile-settings.php");
                    exit();
                } else {
                    $errors[] = "Error updating password";
                }
            } else {
                $errors[] = "Current password is incorrect";
            }
        }
    }
}
?>

<div class="min-h-screen max-w-4xl mx-auto py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold mb-8">Profile Settings</h1>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Profile Settings -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">General Settings</h2>
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Preferred Currency</label>
                        <select name="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="USD" <?php echo $user['currency_preference'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                            <option value="EUR" <?php echo $user['currency_preference'] === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                            <option value="GBP" <?php echo $user['currency_preference'] === 'GBP' ? 'selected' : ''; ?>>GBP (£)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notifications</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="notifications" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       <?php echo $user['notification_preference'] ? 'checked' : ''; ?>>
                                <span class="ml-2">Enable email notifications</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="update_profile" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Change -->
        <div>
            <h2 class="text-xl font-semibold mb-4">Change Password</h2>
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input type="password" name="current_password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" name="new_password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" name="confirm_password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="update_password" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>