<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_income'])) {
    $amount = trim($_POST['amount']);

    if (empty($amount)) {
        $error = "Amount is required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Please enter a valid positive amount.";
    } else {
        // Fetch current budget
        $stmt_budget = mysqli_prepare($conn, "SELECT budget FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt_budget, "i", $user_id);
        mysqli_stmt_execute($stmt_budget);
        $result_budget = mysqli_stmt_get_result($stmt_budget);
        $user_data = mysqli_fetch_assoc($result_budget);
        $current_budget = $user_data['budget'] ? $user_data['budget'] : 0.00;
        mysqli_stmt_close($stmt_budget);

        $new_budget = $current_budget + (float)$amount;

        // Update budget
        $stmt_update = mysqli_prepare($conn, "UPDATE users SET budget = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "di", $new_budget, $user_id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            // Redirect to dashboard on success
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
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
    <title>Add Income - Expense Tracker</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-gray-800 pb-10">

    <!-- Simple Navigation -->
    <nav class="bg-white border-b border-gray-100 shadow-sm mb-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Back Link -->
                <a href="dashboard.php" class="flex items-center text-gray-500 hover:text-emerald-600 transition-colors font-medium text-sm gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Dashboard
                </a>
                <span class="font-bold text-lg tracking-tight text-gray-900">Add Income / Money</span>
            </div>
        </div>
    </nav>

    <main class="max-w-xl mx-auto px-4 sm:px-6">
        
        <div class="bg-white p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
            
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900">Income Details</h2>
                <p class="text-sm text-gray-500 mt-1">Add money to your account balance/budget.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm font-medium border border-red-100">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="add_income.php" method="POST" class="space-y-5">
                
                <!-- Amount Field -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1.5">Amount</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">+</span>
                        </div>
                        <input type="number" step="0.01" min="0.01" id="amount" name="amount" required 
                            class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all duration-200" 
                            placeholder="0.00">
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <button type="submit" name="add_income" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 rounded-xl shadow-lg shadow-emerald-500/20 transition-all duration-200 hover:-translate-y-0.5 mt-2 flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Money
                    </button>
                </div>

            </form>

        </div>
    </main>

</body>
</html>
