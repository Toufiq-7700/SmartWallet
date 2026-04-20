<?php
session_start();
require_once 'db.php';

// If the user is NOT logged in, redirect them to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// Get the user's total budget (income)
$stmt_budget = mysqli_prepare($conn, "SELECT budget FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt_budget, "i", $user_id);
mysqli_stmt_execute($stmt_budget);
$result_budget = mysqli_stmt_get_result($stmt_budget);
$user_data = mysqli_fetch_assoc($result_budget);
$total_budget = $user_data['budget'] ? $user_data['budget'] : 0.00;
mysqli_stmt_close($stmt_budget);

// Get year filter (default to current year)
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Get all available years from expenses
$stmt_years = mysqli_prepare($conn, "SELECT DISTINCT YEAR(expense_date) as yr FROM expenses WHERE user_id = ? ORDER BY yr DESC");
mysqli_stmt_bind_param($stmt_years, "i", $user_id);
mysqli_stmt_execute($stmt_years);
$years_result = mysqli_stmt_get_result($stmt_years);
$available_years = [];
while ($yr = mysqli_fetch_assoc($years_result)) {
    $available_years[] = $yr['yr'];
}
mysqli_stmt_close($stmt_years);

// Add current year if not in the list
if (!in_array((int)date('Y'), $available_years)) {
    array_unshift($available_years, (int)date('Y'));
}
// Add selected year if not in the list
if (!in_array($selected_year, $available_years)) {
    $available_years[] = $selected_year;
    sort($available_years);
    $available_years = array_reverse($available_years);
}

// Get monthly expenses for the selected year
$stmt_monthly = mysqli_prepare($conn, 
    "SELECT MONTH(expense_date) as month_num, 
            MONTHNAME(expense_date) as month_name, 
            SUM(amount) as total_spent, 
            COUNT(*) as transaction_count 
     FROM expenses 
     WHERE user_id = ? AND YEAR(expense_date) = ? 
     GROUP BY MONTH(expense_date), MONTHNAME(expense_date) 
     ORDER BY MONTH(expense_date) ASC"
);
mysqli_stmt_bind_param($stmt_monthly, "ii", $user_id, $selected_year);
mysqli_stmt_execute($stmt_monthly);
$monthly_result = mysqli_stmt_get_result($stmt_monthly);

$monthly_data = [];
$chart_months = [];
$chart_spent = [];
$grand_total_spent = 0;

while ($row = mysqli_fetch_assoc($monthly_result)) {
    $monthly_data[] = $row;
    $chart_months[] = substr($row['month_name'], 0, 3); // Short month name
    $chart_spent[] = (float)$row['total_spent'];
    $grand_total_spent += (float)$row['total_spent'];
}
mysqli_stmt_close($stmt_monthly);

// Get top category per month for the selected year
$stmt_top_cats = mysqli_prepare($conn, 
    "SELECT m.month_num, e.category, e.cat_total
     FROM (
         SELECT MONTH(expense_date) as month_num 
         FROM expenses WHERE user_id = ? AND YEAR(expense_date) = ?
         GROUP BY MONTH(expense_date)
     ) m
     JOIN (
         SELECT MONTH(expense_date) as month_num, category, SUM(amount) as cat_total
         FROM expenses WHERE user_id = ? AND YEAR(expense_date) = ?
         GROUP BY MONTH(expense_date), category
     ) e ON m.month_num = e.month_num
     WHERE e.cat_total = (
         SELECT MAX(sub.cat_total) FROM (
             SELECT MONTH(expense_date) as month_num, SUM(amount) as cat_total
             FROM expenses WHERE user_id = ? AND YEAR(expense_date) = ?
             GROUP BY MONTH(expense_date), category
         ) sub WHERE sub.month_num = m.month_num
     )
     ORDER BY m.month_num ASC"
);
mysqli_stmt_bind_param($stmt_top_cats, "iiiiii", $user_id, $selected_year, $user_id, $selected_year, $user_id, $selected_year);
mysqli_stmt_execute($stmt_top_cats);
$top_cats_result = mysqli_stmt_get_result($stmt_top_cats);
$top_categories = [];
while ($row = mysqli_fetch_assoc($top_cats_result)) {
    $top_categories[$row['month_num']] = $row['category'];
}
mysqli_stmt_close($stmt_top_cats);

// Calculate yearly stats
$remaining_year = $total_budget - $grand_total_spent;
$avg_monthly = count($monthly_data) > 0 ? $grand_total_spent / count($monthly_data) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Tracking - SmartWallet</title>
    <meta name="description" content="Track your monthly expenses and budget with SmartWallet's detailed monthly breakdown view.">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
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
                <li><a href="dashboard.php"><i class="fa-solid fa-border-all"></i> <span>Dashboard</span></a></li>
                <li><a href="expenses.php"><i class="fa-solid fa-receipt"></i> <span>Transactions</span></a></li>
                <li><a href="budget.php"><i class="fa-solid fa-piggy-bank"></i> <span>Budget</span></a></li>
                <li><a href="monthly.php" class="active"><i class="fa-solid fa-calendar-days"></i> <span>Monthly</span></a></li>
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
                <div class="page-title">Monthly Tracking</div>
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

                <!-- Year Selector -->
                <div class="monthly-header">
                    <div class="monthly-title-group">
                        <h2 class="monthly-heading"><i class="fa-solid fa-calendar-check"></i> <?php echo $selected_year; ?> Overview</h2>
                        <p class="monthly-subtitle">Track your spending patterns month by month</p>
                    </div>
                    <form method="GET" action="monthly.php" class="year-selector" id="yearForm">
                        <button type="button" class="year-nav-btn" onclick="changeYear(-1)" title="Previous Year">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <select name="year" id="yearSelect" onchange="document.getElementById('yearForm').submit();">
                            <?php foreach ($available_years as $yr): ?>
                                <option value="<?php echo $yr; ?>" <?php echo ($yr == $selected_year) ? 'selected' : ''; ?>>
                                    <?php echo $yr; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="year-nav-btn" onclick="changeYear(1)" title="Next Year">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </form>
                </div>

                <!-- Yearly Summary Cards -->
                <div class="cards-grid monthly-summary-cards">
                    <div class="glass-card">
                        <div class="card-header">
                            <span class="card-title">Total Budget</span>
                            <div class="card-icon income"><i class="fa-solid fa-vault"></i></div>
                        </div>
                        <div class="card-amount">$<?php echo number_format($total_budget, 2); ?></div>
                        <div class="card-label">All-time income</div>
                    </div>

                    <div class="glass-card">
                        <div class="card-header">
                            <span class="card-title">Spent in <?php echo $selected_year; ?></span>
                            <div class="card-icon expenses"><i class="fa-solid fa-fire-flame-curved"></i></div>
                        </div>
                        <div class="card-amount">$<?php echo number_format($grand_total_spent, 2); ?></div>
                        <div class="card-label"><?php echo count($monthly_data); ?> active month<?php echo count($monthly_data) != 1 ? 's' : ''; ?></div>
                    </div>

                    <div class="glass-card">
                        <div class="card-header">
                            <span class="card-title">Avg / Month</span>
                            <div class="card-icon balance"><i class="fa-solid fa-calculator"></i></div>
                        </div>
                        <div class="card-amount">$<?php echo number_format($avg_monthly, 2); ?></div>
                        <div class="card-label">Based on active months</div>
                    </div>
                </div>

                <!-- Monthly Bar Chart -->
                <div class="glass-card monthly-chart-card">
                    <div class="chart-header">
                        <span><i class="fa-solid fa-chart-column" style="margin-right: 8px; color: var(--primary-accent);"></i> Monthly Spending — <?php echo $selected_year; ?></span>
                    </div>
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="monthlyBarChart"></canvas>
                    </div>
                </div>

                <!-- Monthly Breakdown Table -->
                <div class="transactions-section monthly-table-section">
                    <div class="table-header">
                        <span class="table-title"><i class="fa-solid fa-table-list" style="margin-right: 8px; color: var(--secondary-accent);"></i> Month-by-Month Breakdown</span>
                        <span class="monthly-year-badge"><?php echo $selected_year; ?></span>
                    </div>
                    <div class="table-container" style="overflow-x: auto;">
                        <table id="monthlyTable">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Transactions</th>
                                    <th>Top Category</th>
                                    <th>Total Spent</th>
                                    <th>% of Year</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($monthly_data) > 0): ?>
                                    <?php 
                                    $prev_spent = 0;
                                    foreach ($monthly_data as $index => $row): 
                                        $pct_of_year = $grand_total_spent > 0 ? ($row['total_spent'] / $grand_total_spent) * 100 : 0;
                                        $trend = '';
                                        $trend_class = '';
                                        if ($index > 0) {
                                            $diff = $row['total_spent'] - $prev_spent;
                                            if ($diff > 0) {
                                                $trend = '+$' . number_format(abs($diff), 2);
                                                $trend_class = 'trend-up';
                                            } elseif ($diff < 0) {
                                                $trend = '-$' . number_format(abs($diff), 2);
                                                $trend_class = 'trend-down';
                                            } else {
                                                $trend = '$0.00';
                                                $trend_class = 'trend-neutral';
                                            }
                                        } else {
                                            $trend = '—';
                                            $trend_class = 'trend-neutral';
                                        }
                                        $prev_spent = $row['total_spent'];
                                        $top_cat = isset($top_categories[$row['month_num']]) ? $top_categories[$row['month_num']] : '—';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="month-cell">
                                                    <span class="month-icon"><?php 
                                                        $month_icons = ['❄️','💝','🌸','🌧️','🌺','☀️','🏖️','🌻','🍂','🎃','🍁','🎄'];
                                                        echo $month_icons[$row['month_num'] - 1]; 
                                                    ?></span>
                                                    <span><?php echo htmlspecialchars($row['month_name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="transaction-count"><?php echo $row['transaction_count']; ?></span>
                                            </td>
                                            <td><span class="cat-badge"><?php echo htmlspecialchars($top_cat); ?></span></td>
                                            <td class="amount negative">-$<?php echo number_format($row['total_spent'], 2); ?></td>
                                            <td>
                                                <div class="pct-bar-container">
                                                    <div class="pct-bar" style="width: <?php echo min($pct_of_year, 100); ?>%;"></div>
                                                    <span class="pct-text"><?php echo number_format($pct_of_year, 1); ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="trend-badge <?php echo $trend_class; ?>">
                                                    <?php if ($trend_class === 'trend-up'): ?>
                                                        <i class="fa-solid fa-arrow-up"></i>
                                                    <?php elseif ($trend_class === 'trend-down'): ?>
                                                        <i class="fa-solid fa-arrow-down"></i>
                                                    <?php endif; ?>
                                                    <?php echo $trend; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <!-- Totals Row -->
                                    <tr class="totals-row">
                                        <td><strong>Total</strong></td>
                                        <td>—</td>
                                        <td>—</td>
                                        <td class="amount negative"><strong>-$<?php echo number_format($grand_total_spent, 2); ?></strong></td>
                                        <td><strong>100%</strong></td>
                                        <td>—</td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 3rem;">
                                            <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 1rem; display: block; opacity: 0.3;"></i>
                                            No expenses recorded for <?php echo $selected_year; ?>.
                                        </td>
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
        const monthlyLabels = <?php echo json_encode($chart_months); ?>;
        const monthlySpent = <?php echo json_encode($chart_spent); ?>;
        const totalBudget = <?php echo json_encode((float)$total_budget); ?>;
        const selectedYear = <?php echo json_encode($selected_year); ?>;
        const availableYears = <?php echo json_encode($available_years); ?>;
    </script>
    <script src="monthly.js"></script>

</body>
</html>
