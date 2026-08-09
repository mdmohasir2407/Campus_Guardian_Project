/**
 * CampusGuardian - Chart.js Initializers
 */

function initAttendanceDoughnutChart(canvasId, presentCount, lateCount, leaveCount, absentCount) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Late Entry', 'Leave / Perm', 'Not Informed / Absent'],
            datasets: [{
                data: [presentCount, lateCount, leaveCount, absentCount],
                backgroundColor: [
                    '#10b981', // Present (Success)
                    '#06b6d4', // Late (Info)
                    '#f59e0b', // Leave (Warning)
                    '#ef4444'  // Absent (Danger)
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            },
            cutout: '70%'
        }
    });
}

function initLateTrendLineChart(canvasId, labelsArray, dataArray) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labelsArray,
            datasets: [{
                label: 'Late Arrivals Count',
                data: dataArray,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#6366f1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}
