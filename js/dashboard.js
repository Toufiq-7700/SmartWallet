// js/dashboard.js

// Setup Chart Colors and Styles for Glassmorphism Look
Chart.defaults.color = "rgba(255, 255, 255, 0.7)";
Chart.defaults.font.family = "'Roboto', sans-serif";

// 1. Line Chart (Spending Overview)
const lineCtx = document.getElementById('lineChart').getContext('2d');

let lineGradient = lineCtx.createLinearGradient(0, 0, 0, 400);
lineGradient.addColorStop(0, 'rgba(0, 242, 254, 0.5)'); // primary-accent
lineGradient.addColorStop(1, 'rgba(0, 242, 254, 0.0)');

// Format dates nicely
const formattedDates = chartDates.map(dateStr => {
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
});

new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: formattedDates.length > 0 ? formattedDates : ['No Data'],
        datasets: [{
            label: 'Amount Spent',
            data: chartAmounts.length > 0 ? chartAmounts : [0],
            borderColor: '#00f2fe', // primary-accent
            backgroundColor: lineGradient,
            borderWidth: 3,
            fill: true,
            tension: 0.4, // Smooth curve
            pointBackgroundColor: '#fff',
            pointBorderColor: '#00f2fe',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 10,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return '$' + context.parsed.y.toFixed(2);
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(255,255,255,0.05)',
                    drawBorder: false
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
                        return '$' + value;
                    }
                }
            }
        }
    }
});

// 2. Pie Chart (Expenses by Category)
const pieCtx = document.getElementById('pieChart').getContext('2d');

// Beautiful Palette for Pie Chart
const palette = [
    '#00f2fe', // cyan/teal
    '#9b51e0', // purple
    '#ff4757', // rose/danger
    '#2ed573', // emerald/success
    '#facc15', // yellow
    '#f472b6'  // pink
];

new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: chartCategories.length > 0 ? chartCategories : ['No Data'],
        datasets: [{
            data: chartCatAmounts.length > 0 ? chartCatAmounts : [1],
            backgroundColor: chartCategories.length > 0 ? palette : ['rgba(255,255,255,0.1)'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%', // high cutout for thin ring
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                callbacks: {
                    label: function(context) {
                        return ' $' + context.parsed.toFixed(2);
                    }
                }
            }
        }
    }
});

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

        // Use requestAnimationFrame for smoother performance
        requestAnimationFrame(() => {
            glow.style.left = `${e.clientX}px`;
            glow.style.top = `${e.clientY}px`;
        });
    });

    document.addEventListener('mouseleave', () => {
        glow.style.opacity = '0';
        isMouseMoving = false;
    });
});
