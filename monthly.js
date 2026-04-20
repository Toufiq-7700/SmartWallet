// monthly.js

// Setup Chart Colors and Styles for Glassmorphism Look
Chart.defaults.color = "rgba(255, 255, 255, 0.7)";
Chart.defaults.font.family = "'Roboto', sans-serif";

// 1. Monthly Bar Chart
const barCtx = document.getElementById('monthlyBarChart').getContext('2d');

// Gradient for bars
let barGradient = barCtx.createLinearGradient(0, 0, 0, 400);
barGradient.addColorStop(0, 'rgba(155, 81, 224, 0.9)');   // secondary-accent
barGradient.addColorStop(1, 'rgba(0, 242, 254, 0.4)');     // primary-accent

// Hover gradient
let barHoverGradient = barCtx.createLinearGradient(0, 0, 0, 400);
barHoverGradient.addColorStop(0, 'rgba(155, 81, 224, 1)');
barHoverGradient.addColorStop(1, 'rgba(0, 242, 254, 0.7)');

// All 12 months for full year view
const allMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Map data to all 12 months (fill missing months with 0)
const fullYearData = allMonths.map((month) => {
    const idx = monthlyLabels.indexOf(month);
    return idx !== -1 ? monthlySpent[idx] : 0;
});

// Generate gradient colors per bar
const barColors = fullYearData.map((val) => {
    return val > 0 ? barGradient : 'rgba(255, 255, 255, 0.03)';
});

const barHoverColors = fullYearData.map((val) => {
    return val > 0 ? barHoverGradient : 'rgba(255, 255, 255, 0.06)';
});

// Calculate average line
const activeMonths = fullYearData.filter(v => v > 0);
const avgSpending = activeMonths.length > 0 ? activeMonths.reduce((a, b) => a + b, 0) / activeMonths.length : 0;

new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: allMonths,
        datasets: [
            {
                label: 'Monthly Spending',
                data: fullYearData,
                backgroundColor: barColors,
                hoverBackgroundColor: barHoverColors,
                borderRadius: 8,
                borderSkipped: false,
                barPercentage: 0.6,
                categoryPercentage: 0.7,
            },
            {
                label: 'Avg Spending',
                data: Array(12).fill(avgSpending),
                type: 'line',
                borderColor: 'rgba(250, 204, 21, 0.6)',
                borderWidth: 2,
                borderDash: [8, 4],
                pointRadius: 0,
                pointHoverRadius: 0,
                fill: false,
                tension: 0,
                order: 0,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                align: 'end',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'rectRounded',
                    font: { size: 11 }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 14,
                cornerRadius: 12,
                displayColors: true,
                callbacks: {
                    title: function(context) {
                        const monthIndex = context[0].dataIndex;
                        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                                           'July', 'August', 'September', 'October', 'November', 'December'];
                        return monthNames[monthIndex] + ' ' + selectedYear;
                    },
                    label: function(context) {
                        if (context.datasetIndex === 0) {
                            return ' Spent: $' + context.parsed.y.toFixed(2);
                        } else {
                            return ' Average: $' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(255,255,255,0.03)',
                    drawBorder: false
                },
                ticks: {
                    font: { size: 12, weight: '500' }
                }
            },
            y: {
                grid: {
                    color: 'rgba(255,255,255,0.05)',
                    drawBorder: false
                },
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    },
                    font: { size: 11 }
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        }
    }
});

// 2. Year Navigation Helper
function changeYear(direction) {
    const select = document.getElementById('yearSelect');
    const currentIndex = select.selectedIndex;
    const newIndex = currentIndex - direction; // reversed because years are DESC

    if (newIndex >= 0 && newIndex < select.options.length) {
        select.selectedIndex = newIndex;
        document.getElementById('yearForm').submit();
    }
}

// 3. Interactive Cursor Glow Effect
document.addEventListener('DOMContentLoaded', () => {
    const glow = document.querySelector('.cursor-glow');
    if (!glow) return;

    let isMouseMoving = false;

    document.addEventListener('mousemove', (e) => {
        if (!isMouseMoving) {
            glow.style.opacity = '1';
            isMouseMoving = true;
        }

        requestAnimationFrame(() => {
            glow.style.left = `${e.clientX}px`;
            glow.style.top = `${e.clientY}px`;
        });
    });

    document.addEventListener('mouseleave', () => {
        glow.style.opacity = '0';
        isMouseMoving = false;
    });

    // Add entrance animation to table rows
    const rows = document.querySelectorAll('#monthlyTable tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        row.style.transition = `all 0.4s ease ${index * 0.06}s`;
        
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, 100);
    });
});
