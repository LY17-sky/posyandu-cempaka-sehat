// Chart.js initialization for growth charts
document.addEventListener('DOMContentLoaded', function () {
    // This file is now handled inline in grafik.php for better control
    // Keeping this file for any additional chart utilities
    
    // Utility function to format chart data
    window.formatChartData = function(data, parameter) {
        const formatted = [];
        data.forEach(item => {
            const value = item[parameter];
            if (value !== null && value !== undefined) {
                formatted.push({
                    x: new Date(item.tgl_timbang),
                    y: parseFloat(value),
                    status: item.status || 'Normal'
                });
            }
        });
        return formatted;
    };
    
    // Utility function to get chart colors
    window.getChartColors = function() {
        return {
            bb: { border: '#10B981', background: 'rgba(16, 185, 129, 0.1)' },
            tb: { border: '#3B82F6', background: 'rgba(59, 130, 246, 0.1)' },
            lk: { border: '#F59E0B', background: 'rgba(245, 158, 11, 0.1)' },
            lila: { border: '#EF4444', background: 'rgba(239, 68, 68, 0.1)' }
        };
    };
    
    // Utility function to create chart options
    window.createChartOptions = function(title = 'Grafik Pertumbuhan') {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: title,
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                },
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        afterLabel: function(context) {
                            const data = context.dataset.data[context.dataIndex];
                            if (data && data.status) {
                                return 'Status: ' + data.status;
                            }
                            return '';
                        }
                    }
                }
            },
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'month',
                        displayFormats: {
                            month: 'MMM YYYY'
                        }
                    },
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Nilai'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                },
                line: {
                    tension: 0.4
                }
            }
        };
    };
    
    // Utility function to filter data by period
    window.filterDataByPeriod = function(data, period) {
        const now = new Date();
        let startDate;
        
        switch (period) {
            case '3m':
                startDate = new Date(now.getFullYear(), now.getMonth() - 3, 1);
                break;
            case '6m':
                startDate = new Date(now.getFullYear(), now.getMonth() - 6, 1);
                break;
            case '1year':
                startDate = new Date(now.getFullYear() - 1, now.getMonth() + 1, 1);
                break;
            default:
                return data; // 'all'
        }
        
        return data.filter(item => new Date(item.tgl_timbang) >= startDate);
    };
    
    // Utility function to show loading state
    window.showChartLoading = function(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#6B7280';
        ctx.font = '16px Inter';
        ctx.textAlign = 'center';
        ctx.fillText('Memuat grafik...', centerX, centerY);
    };
    
    // Utility function to hide loading state
    window.hideChartLoading = function(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    };
});