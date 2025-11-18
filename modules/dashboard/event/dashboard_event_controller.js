// URL del controller del dashboard
const dashboard_controller = 'modules/dashboard/controller/dashboard_controller.php';

// Cargar dashboard
function loadDashboardModule() {
    $.ajax({
        url: dashboard_controller,
        method: 'POST',
        dataType: 'json',
        data: { user_request: 'fetch_dashboard' },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#app_content').html(resp.view);

                // Inicializar el chart si los datos vienen en el payload
                if (resp.chart_data) {
                    initDashboardChart(resp.chart_data);
                }
            } else {
                console.error(resp.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
}

// Navegación: botón Dashboard en tu bottom nav / barra
$(document).on('click', '#fetch_dashboard', function (e) {
    e.preventDefault();
    loadDashboardModule();
});

// Necesitas tener cargado Chart.js (CDN) en tu index (te lo pongo más abajo)
function initDashboardChart(chartData) {
    const ctx = document.getElementById('dashboard_kpi_chart');
    if (!ctx) return;

    const labels   = chartData.labels || [];
    const sales    = chartData.sales  || [];
    const payments = chartData.payments || [];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Compras',
                    data: sales,
                    borderRadius: 8,
                },
                {
                    label: 'Pagos',
                    data: payments,
                    borderRadius: 8,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });
}

$(document).ready(function () {
    // Cargar el dashboard al iniciar la app
    loadDashboardModule();
});