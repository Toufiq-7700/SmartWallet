<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get current budget
$stmt = mysqli_prepare($conn, "SELECT budget FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$current_budget = $row['budget'] ? $row['budget'] : 0.00;
mysqli_stmt_close($stmt);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_budget'])) {
    $new_budget = trim($_POST['budget']);

    if (!is_numeric($new_budget) || $new_budget < 0) {
        $error = "Please enter a valid positive number for your budget.";
    } else {
        // Update user's budget in database
        $stmt_update = mysqli_prepare($conn, "UPDATE users SET budget = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "di", $new_budget, $user_id); // 'd' for double, 'i' for integer
        
        if (mysqli_stmt_execute($stmt_update)) {
            // Update session variable if we wanted to use it, but since dashboard checks DB, we don't strictly have to.
            $_SESSION['budget'] = $new_budget;
            $success = "Budget updated successfully!";
            $current_budget = $new_budget; // Update the display value right away
        } else {
            $error = "Failed to update budget. Please try again.";
        }
        mysqli_stmt_close($stmt_update);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Monthly Budget - Expense Tracker</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-gray-800 flex flex-col min-h-screen">

    <nav class="bg-white border-b border-gray-100 shadow-sm mb-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Back Link -->
                <a href="dashboard.php" class="flex items-center text-gray-500 hover:text-indigo-600 transition-colors font-medium text-sm gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Dashboard
                </a>
                <span class="font-bold text-lg tracking-tight text-gray-900">Manage Budget</span>
            </div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 mb-20">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-50 rounded-full mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900">Monthly Budget</h1>
                <p class="text-sm text-gray-500 mt-1">Set a budget to track your remaining balance effectively.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm font-medium border border-red-100">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-50 text-green-600 p-3 rounded-lg mb-4 text-sm font-medium border border-green-100">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="budget.php" method="POST" class="space-y-6">
                <div>
                    <label for="budget" class="block text-sm font-medium text-gray-700 mb-1.5">New Budget Amount ($)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" step="0.01" min="0" id="budget" name="budget" 
                            value="<?php echo number_format($current_budget, 2, '.', ''); ?>" required 
                            class="w-full pl-8 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all duration-200" 
                            placeholder="0.00">
                    </div>
                </div>

                <button type="submit" name="update_budget" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-600/20 transition-all duration-200 hover:-translate-y-0.5 mt-2">
                    Update Budget
                </button>
            </form>
        </div>
    </main>

</body>
</html>
