<?php
// SmartWallet Landing Page
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartWallet | Master Your Wealth</title>
    <!-- Add Google Fonts and FontAwesome from style.css imports or directly here -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="landing.css">
</head>
<body>
    <!-- Animated Interactive Background -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    
    <div class="floating-elements">
        <i class="fas fa-coins float-icon"></i>
        <i class="fas fa-wallet float-icon"></i>
        <i class="fas fa-chart-line float-icon"></i>
        <i class="fas fa-piggy-bank float-icon"></i>
        <i class="fas fa-credit-card float-icon"></i>
        <i class="fas fa-rupee-sign float-icon"></i>
        <i class="fas fa-dollar-sign float-icon"></i>
        <i class="fas fa-chart-pie float-icon"></i>
    </div>
    
    <div class="cursor-glow" id="cursorGlow"></div>

    <!-- Navigation Header -->
    <nav class="landing-nav">
        <a href="index.php" class="nav-logo">
            <i class="fas fa-wallet"></i> SmartWallet
        </a>
        <div class="nav-links">
            <a href="login.php" class="nav-link">Login</a>
            <a href="register.php" class="btn-primary" style="padding: 8px 16px; margin: 0;">Get Started</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="landing-main">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content glass-card">
                <div class="hero-badge"><i class="fas fa-star" style="color: var(--primary-accent); margin-right: 5px;"></i> NEXT-GEN FINANCE</div>
                <h1 class="hero-title">Master Your Wealth.<br><span class="text-gradient">Elevate Your Future.</span></h1>
                <p class="hero-subtitle">Experience a breathtakingly beautiful way to track expenses, handle budgets, and gain deep financial insights—all in one secure place with stunning visualizations.</p>
                <div class="hero-cta">
                    <a href="register.php" class="btn-primary hero-btn">Create Account <i class="fas fa-arrow-right"></i></a>
                    <a href="login.php" class="btn-outline hero-btn">Login to Dashboard</a>
                </div>
            </div>
        </section>

        <!-- Overview Section -->
        <section class="overview-section">
            <div class="section-title-wrap">
                <h2 class="section-title">What is SmartWallet?</h2>
            </div>
            <div class="overview-grid glass-card" style="padding: 3rem; transform: none;">
                <div class="overview-text">
                    <p>SmartWallet is your ultimate personal finance companion. Built with cutting-edge design and security in mind, we bring absolute clarity to your financial life. Minimalist interfaces meet powerful analytics, allowing you to take charge of your money like never before.</p>
                </div>
            </div>
        </section>

        <!-- Features/Services Section -->
        <section class="features-section">
            <div class="section-title-wrap">
                <h2 class="section-title">Everything You Need</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card glass-card">
                    <div class="card-icon balance"><i class="fas fa-wallet"></i></div>
                    <h3>Expense Tracking</h3>
                    <p>Log your daily spending seamlessly. Categorize and manage expenses to stop leaks in your pocket.</p>
                </div>
                <div class="feature-card glass-card">
                    <div class="card-icon income"><i class="fas fa-chart-pie"></i></div>
                    <h3>Smart Budgeting</h3>
                    <p>Set custom limits for various categories. Receive real-time insights so you never overspend.</p>
                </div>
                <div class="feature-card glass-card">
                    <div class="card-icon" style="background: rgba(192, 132, 252, 0.15); color: var(--secondary-accent); box-shadow: 0 0 15px rgba(192, 132, 252, 0.2);"><i class="fas fa-chart-line"></i></div>
                    <h3>Deep Analytics</h3>
                    <p>Gain insights with beautiful, dynamic charts. Visualize where your money goes every month.</p>
                </div>
                <div class="feature-card glass-card">
                    <div class="card-icon expenses"><i class="fas fa-shield-alt"></i></div>
                    <h3>Secure Data</h3>
                    <p>Your financial data is yours alone. We employ industry-standard practices to keep it consistently safe.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <h3><i class="fas fa-wallet"></i> SmartWallet</h3>
                <p>The premium personal finance tracker.</p>
            </div>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Security</a>
                <a href="#">Contact Us</a>
            </div>
            <div class="footer-social">
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 SmartWallet. Designed for clarity.</p>
        </div>
    </footer>
    
    <!-- Cursor Glow Script -->
    <script>
        document.addEventListener('mousemove', (e) => {
            const glow = document.getElementById('cursorGlow');
            if (glow) {
                glow.style.left = e.clientX + 'px';
                glow.style.top = e.clientY + 'px';
                glow.style.opacity = '1';
            }
        });
        document.addEventListener('mouseleave', () => {
            const glow = document.getElementById('cursorGlow');
            if (glow) {
                glow.style.opacity = '0';
            }
        });
    </script>
</body>
</html>
