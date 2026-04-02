<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- DELETE LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    
    // Use prepared statements to safely delete, AND ensure the expense belongs to the current user
    $stmt_del = mysqli_prepare($conn, "DELETE FROM expenses WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt_del, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);
    
    // Refresh the page
    header("Location: expenses.php");
    exit();
}

// Fetch expenses for the logged-in user, sorted by date (latest first)
$query = "SELECT id, amount, category, note, expense_date FROM expenses WHERE user_id = ? ORDER BY expense_date DESC, created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Expenses - Expense Tracker</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-gray-800">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-10 shadow-sm mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Back to Dashboard -->
                <a href="dashboard.php" class="flex items-center text-gray-500 hover:text-indigo-600 transition-colors font-medium text-sm gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Dashboard
                </a>
                
                <div class="flex items-center gap-4">
                    <a href="add_expense.php" class="text-sm font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-4 py-2 rounded-lg transition-colors">
                        + Add New
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Your Expenses</h1>
                <p class="text-sm text-gray-500 mt-1">A complete list of your transaction history.</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Date</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Category</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600">Note</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-right">Amount</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                // Format the date nicely
                                $formatted_date = date("M j, Y", strtotime($row['expense_date']));
                            ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                        <?php echo $formatted_date; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo htmlspecialchars($row['category']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        <?php echo htmlspecialchars($row['note'] ? $row['note'] : '-'); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right whitespace-nowrap">
                                        $<?php echo number_format($row['amount'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <!-- Form to submit the delete request -->
                                        <form action="expenses.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <p class="text-sm font-medium text-gray-900">No expenses found</p>
                                    <p class="text-xs text-gray-500 mt-1">Get started by adding a new expense.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</body>
</html>
<?php mysqli_stmt_close($stmt); ?>
