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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $amount = trim($_POST['amount']);
    $category = trim($_POST['category']);
    $note = trim($_POST['note']);
    $expense_date = trim($_POST['expense_date']);

    // Basic validation
    if (empty($amount) || empty($category) || empty($expense_date)) {
        $error = "Amount, Category, and Date are required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Please enter a valid positive amount.";
    } else {
        // Prepare and execute the insert statement
        // 'idsss' means: Integer, Double(decimal), String, String, String
        $stmt = mysqli_prepare($conn, "INSERT INTO expenses (user_id, amount, category, note, expense_date) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "idsss", $user_id, $amount, $category, $note, $expense_date);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to dashboard on success
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense - Expense Tracker</title>
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
                <a href="dashboard.php" class="flex items-center text-gray-500 hover:text-indigo-600 transition-colors font-medium text-sm gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Dashboard
                </a>
                <span class="font-bold text-lg tracking-tight text-gray-900">Add New Expense</span>
            </div>
        </div>
    </nav>

    <main class="max-w-xl mx-auto px-4 sm:px-6">
        
        <div class="bg-white p-8 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100">
            
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900">Expense Details</h2>
                <p class="text-sm text-gray-500 mt-1">Provide the details of your new spending.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm font-medium border border-red-100">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="add_expense.php" method="POST" class="space-y-5">
                
                <!-- Amount Field -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1.5">Amount ($)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" step="0.01" min="0.01" id="amount" name="amount" required 
                            class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all duration-200" 
                            placeholder="0.00">
                    </div>
                </div>

                <!-- Category Dropdown -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                    <select id="category" name="category" required 
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all duration-200 text-gray-700">
                        <option value="" disabled selected>Select a category</option>
                        <option value="Food & Dining">🍔 Food & Dining</option>
                        <option value="Transportation">🚗 Transportation</option>
                        <option value="Housing & Utilities">🏠 Housing & Utilities</option>
                        <option value="Entertainment">🎬 Entertainment</option>
                        <option value="Shopping">🛍️ Shopping</option>
                        <option value="Health & Personal Care">💊 Health & Personal Care</option>
                        <option value="Education">📚 Education</option>
                        <option value="Miscellaneous">✨ Miscellaneous</option>
                    </select>
                </div>

                <!-- Date Field -->
                <div>
                    <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-1.5">Date</label>
                    <input type="date" id="expense_date" name="expense_date" required 
                        value="<?php echo date('Y-m-d'); ?>" 
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all duration-200">
                </div>

                <!-- Note Field -->
                <div>
                    <label for="note" class="block text-sm font-medium text-gray-700 mb-1.5">Note (Optional)</label>
                    <textarea id="note" name="note" rows="3" 
                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all duration-200 resize-none" 
                        placeholder="What was this expense for?"></textarea>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <button type="submit" name="add_expense" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-xl shadow-lg shadow-indigo-600/20 transition-all duration-200 hover:-translate-y-0.5 mt-2 flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Save Expense
                    </button>
                </div>

            </form>

        </div>
    </main>

</body>
</html>
