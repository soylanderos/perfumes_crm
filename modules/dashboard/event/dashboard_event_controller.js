const dashboard_controller = 'modules/dashboard/controller/dashboard_controller.php';

$(function () {
    $.ajax({
        url: dashboard_controller,
        type: 'POST',
        data: { user_request: 'fetch_dashboard' },
        dataType: 'json'
    })
        .done(function (resp) {
            if (resp.status === 'success') {
                $('#app-content').html(resp.view);

                // UI
                $('.sidebar').addClass('collapsed');
                $('.menu-toggle-btn .material-symbols-rounded').text('menu');

                // Pintar charts
                if (typeof window.initDashboardCharts === 'function') {
                    window.initDashboardCharts('#app-content');
                } else {
                    console.error('initDashboardCharts no está definido. ¿Cargaste dashboard.charts.js?');
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo cargar el dashboard.' });
            }
        })
        .fail(function (xhr, status, error) {
            console.error('Error en la solicitud AJAX:', status, error, xhr.responseText);
        });
});

// Requiere Chart.js incluido en el layout
function money(v) { return '$' + Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function getPayload(root) {
    const el = root.querySelector('#dashboard-data');
    if (!el) return null;
    try { return JSON.parse(el.textContent || '{}'); } catch (e) { console.error('JSON payload inválido', e); return null; }
}
function bar(id, labels, data, opts = {}) {
    const ctx = document.getElementById(id); if (!ctx) return;
    return new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ data, label: opts.label || '' }] },
        options: Object.assign({
            indexAxis: opts.indexAxis || 'x',
            plugins: { legend: { display: !!opts.label } },
            scales: opts.money ? { y: { ticks: { callback: v => money(v) } } } : {}
        }, opts.options || {})
    });
}
function line(id, labels, datasets, opts = {}) {
    const ctx = document.getElementById(id); if (!ctx) return;
    return new Chart(ctx, {
        type: 'line',
        data: { labels, datasets: datasets.map(d => Object.assign({ tension: .3 }, d)) },
        options: Object.assign({ scales: { y: { ticks: { callback: v => money(v) } } } }, opts)
    });
}
function doughnut(id, labels, data) {
    const ctx = document.getElementById(id); if (!ctx) return;
    return new Chart(ctx, { type: 'doughnut', data: { labels, datasets: [{ data }] } });
}

function initDashboardCharts(rootOrSelector) {
    const root = (typeof rootOrSelector === 'string') ? document.querySelector(rootOrSelector) : rootOrSelector;
    if (!root) return;
    const P = getPayload(root); if (!P) return;

    // Ventas vs Cobros
    line('chart_sales_paid', P.labels, [
        { label: 'Ventas', data: P.sales },
        { label: 'Cobros', data: P.paid }
    ]);

    // Estatus
    const sL = (P.by_status || []).map(r => r.status);
    const sD = (P.by_status || []).map(r => Number(r.cnt || 0));
    doughnut('chart_status', sL, sD);

    // Aging
    const a = P.aging || {};
    const agingLabels = ['Al corriente', '1–7', '8–30', '31–60', '>60'];
    const agingData = [a.current_cnt || 0, a.d1_7_cnt || 0, a.d8_30_cnt || 0, a.d31_60_cnt || 0, a.d60p_cnt || 0].map(Number);
    bar('chart_aging', agingLabels, agingData, { label: 'Órdenes', options: { plugins: { legend: { display: false } } } });

    // Top clientes
    bar('chart_top_customers',
        (P.top_customers || []).map(r => r.name),
        (P.top_customers || []).map(r => Number(r.total || 0)),
        { label: 'Ventas', indexAxis: 'y', money: true });

    // Top deudores
    bar('chart_top_debtors',
        (P.top_debtors || []).map(r => r.name),
        (P.top_debtors || []).map(r => Number(r.due || 0)),
        { label: 'Saldo', indexAxis: 'y', money: true });

    // Top productos
    const tpL = (P.top_products || []).map(r => r.name);
    bar('chart_top_products_total', tpL, (P.top_products || []).map(r => Number(r.total || 0)), { label: 'Importe', indexAxis: 'y', money: true });
    bar('chart_top_products_qty', tpL, (P.top_products || []).map(r => Number(r.qty || 0)), { indexAxis: 'y', options: { plugins: { legend: { display: false } } } });
}
