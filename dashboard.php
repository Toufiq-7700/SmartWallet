<?php
session_start();
require_once 'db.php'; // Include database connection

// If the user is NOT logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// 1. Get the latest budget (Income) for this user
$stmt_budget = mysqli_prepare($conn, "SELECT budget FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_budget, "i", $user_id);
mysqli_stmt_execute($stmt_budget);
$result_budget = mysqli_stmt_get_result($stmt_budget);
$user_data = mysqli_fetch_assoc($result_budget);
$total_budget = $user_data['budget'] ? $user_data['budget'] : 0.00;
mysqli_stmt_close($stmt_budget);

// 2. Get the total spent (SUM of amounts in expenses table)
$stmt_spent = mysqli_prepare($conn, "SELECT SUM(amount) AS total_spent FROM expenses WHERE user_id = ?");
mysqli_stmt_bind_param($stmt_spent, "i", $user_id);
mysqli_stmt_execute($stmt_spent);
$result_spent = mysqli_stmt_get_result($stmt_spent);
$expense_data = mysqli_fetch_assoc($result_spent);
$total_spent = $expense_data['total_spent'] ? $expense_data['total_spent'] : 0.00;
mysqli_stmt_close($stmt_spent);

// 3. Calculate remaining balance
$remaining_balance = $total_budget - $total_spent;

// 4. Get recent transactions (limit 5)
$stmt_recent = mysqli_prepare($conn, "SELECT id, amount, category, expense_date, note FROM expenses WHERE user_id = ? ORDER BY expense_date DESC, id DESC LIMIT 5");
mysqli_stmt_bind_param($stmt_recent, "i", $user_id);
mysqli_stmt_execute($stmt_recent);
$recent_expenses = mysqli_stmt_get_result($stmt_recent);

// 5. Get data for line chart (last 7 days)
$stmt_days = mysqli_prepare($conn, "SELECT expense_date, SUM(amount) as total FROM expenses WHERE user_id = ? AND expense_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY expense_date ORDER BY expense_date ASC");
mysqli_stmt_bind_param($stmt_days, "i", $user_id);
mysqli_stmt_execute($stmt_days);
$days_result = mysqli_stmt_get_result($stmt_days);
$chart_dates = [];
$chart_amounts = [];
while ($row = mysqli_fetch_assoc($days_result)) {
    $chart_dates[] = $row['expense_date'];
    $chart_amounts[] = $row['total'];
}

// 6. Get data for pie chart (expenses by category)
$stmt_cats = mysqli_prepare($conn, "SELECT category, SUM(amount) as total FROM expenses WHERE user_id = ? GROUP BY category");
mysqli_stmt_bind_param($stmt_cats, "i", $user_id);
mysqli_stmt_execute($stmt_cats);
$cats_result = mysqli_stmt_get_result($stmt_cats);
$chart_categories = [];
$chart_cat_amounts = [];
while ($row = mysqli_fetch_assoc($cats_result)) {
    $chart_categories[] = $row['category'];
    $chart_cat_amounts[] = $row['total'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartWallet Dashboard</title>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Cursor Glow Effect -->
    <div class="cursor-glow"></div>

    <!-- Animated Background Shapes -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>

    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="float-icon">🪙</div>
        <div class="float-icon">💎</div>
        <div class="float-icon">💸</div>
        <div class="float-icon">📈</div>
        <div class="float-icon">💰</div>
        <div class="float-icon">💳</div>
        <div class="float-icon">✨</div>
        <div class="float-icon">🏦</div>
    </div>

    <div class="app-container">
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-wallet"></i>
                <span>SmartWallet</span>
            </div>
            <ul class="sidebar-nav">
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-border-all"></i> <span>Dashboard</span></a></li>
                <li><a href="expenses.php"><i class="fa-solid fa-receipt"></i> <span>Transactions</span></a></li>
                <li><a href="budget.php"><i class="fa-solid fa-piggy-bank"></i> <span>Budget</span></a></li>
                <li><a href="#"><i class="fa-solid fa-chart-pie"></i> <span>Reports</span></a></li>
                <li><a href="#"><i class="fa-solid fa-gear"></i> <span>Settings</span></a></li>
            </ul>
            <div class="sidebar-logout">
                <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Logout</span></a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            
            <!-- Top Navbar -->
            <nav class="top-navbar">
                <div class="page-title">Overview</div>
                <div class="nav-actions">
                    <div class="notification-btn">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge"></span>
                    </div>
                    <div class="user-profile">
                        <div class="avatar">
                            <?php echo strtoupper(substr(htmlspecialchars($user_name), 0, 1)); ?>
                        </div>
                        <span>Hello, <?php echo htmlspecialchars($user_name); ?></span>
                    </div>
                </div>
            </nav>

            <!-- Dashboard Body -->
            <div class="dashboard-container">
                
                <div class="header-actions" style="display: flex; gap: 15px; justify-content: flex-end;">
                    <a href="add_income.php" class="btn-success">
                        <i class="fa-solid fa-plus"></i> Add Money
                    </a>
                    <a href="add_expense.php" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Add Expense
                    </a>
                </div>

                <!-- Summary Cards -->
                <div class="cards-grid">
                    
                    <div class="glass-card">
                        <div class="card-header">
                            <span class="card-title">Balance</span>
                            <div class="card-icon balance"><i class="fa-solid fa-wallet"></i></div>
                        </div>
                        <div class="card-amount" style="<?php echo ($remaining_balance < 0) ? 'color: var(--danger-color);' : ''; ?>">
                            $<?php echo number_format($remaining_balance, 2); ?>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-header">
                            <span class="card-title">Budget (Income)</span>
                            <div class="card-icon income"><i class="fa-solid fa-arrow-trend-up"></i></div>
                        </div>
                        <div class="card-amount">
                            $<?php echo number_format($total_budget, 2); ?>
                        </div>
                    </div>

                    <div class="glass-card">
                        <div class="card-header">
                            <span class="card-title">Expenses</span>
                            <div class="card-icon expenses"><i class="fa-solid fa-arrow-trend-down"></i></div>
                        </div>
                        <div class="card-amount">
                            $<?php echo number_format($total_spent, 2); ?>
                        </div>
                    </div>

                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <div class="glass-card">
                        <div class="chart-header">
                            <span>Spending Overview (Last 7 Days)</span>
                            <i class="fa-solid fa-chart-line text-secondary"></i>
                        </div>
                        <div class="chart-container">
                            <canvas id="lineChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="glass-card">
                        <div class="chart-header">
                            <span>Expenses by Category</span>
                            <i class="fa-solid fa-chart-pie text-secondary"></i>
                        </div>
                        <div class="chart-container">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="transactions-section">
                    <div class="table-header">
                        <span class="table-title">Recent Transactions</span>
                        <a href="expenses.php" class="view-all">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Note</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_expenses) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($recent_expenses)): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($row['expense_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['note'] ? $row['note'] : '-'); ?></td>
                                            <td><span class="cat-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                                            <td class="amount negative">-$<?php echo number_format($row['amount'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: var(--text-secondary);">No recent transactions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Pass PHP data to JS -->
    <script>
        const chartDates = <?php echo json_encode($chart_dates); ?>;
        const chartAmounts = <?php echo json_encode($chart_amounts); ?>;
        const chartCategories = <?php echo json_encode($chart_categories); ?>;
        const chartCatAmounts = <?php echo json_encode($chart_cat_amounts); ?>;
    </script>
    <!-- Custom JS -->
    <script src="js/dashboard.js"></script>
</body>
</html>