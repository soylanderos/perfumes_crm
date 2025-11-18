// URL de controller (ajusta la ruta)
const orders_controller = 'modules/purchase_orders/controller/purchase_orders_controller.php';

// Cargar módulo de pedidos (similar a fetch_clients)
function loadOrdersModule() {
    $.ajax({
        url: orders_controller,
        method: 'POST',
        dataType: 'json',
        data: { user_request: 'fetch_orders' },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#app_content').html(resp.view);

                // reset filtros visuales/estado
                ordersFilterStatus = 'all';
                ordersFilterPeriod = 'this_month';
                ordersFilterSearch = '';

                $('#ordersFilterStatusDropdown').text('Estado: Todos');
                $('.orders-period-filter').removeClass('active');
                $('.orders-period-filter[data-period="this_month"]').addClass('active');
                $('#orders_search_input').val('');

                applyOrdersFilters();
            } else {
                console.error(resp.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
}


// Al hacer click en "Pedidos" en tu bottom nav, llamas a loadOrdersModule()
$(document).on('click', '#fetch_orders', function (e) { e.preventDefault(); loadOrdersModule(); });
/* ==========================
   Lógica dentro del módulo
   ========================== */

// Seleccionar un pedido de la lista
$(document).on('click', '.orders-list-item', function () {
    const $btn = $(this);
    const orderId = $btn.data('order-id');
    if (!orderId) return;

    // Marcar activo en la lista
    $('.orders-list-item').removeClass('active');
    $btn.addClass('active');

    // ¿Pantalla chica? (Bootstrap: < 992px)
    const isSmallScreen = window.matchMedia('(max-width: 991.98px)').matches;

    $.ajax({
        url: orders_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_order_detail',
            order_id: orderId
        },
        success: function (resp) {
            if (resp.status !== 'success') {
                console.error(resp.message);
                return;
            }

            if (isSmallScreen) {
                // 🔹 MODO MOBILE/TABLET → usar MODAL

                // Título + meta
                $('#orders_detail_modal_title').text(resp.order_title);
                $('#orders_detail_modal_meta').text(resp.meta_text);

                // Badge de estado
                $('#orders_detail_modal_status_badge')
                    .text(resp.status_label)
                    .attr(
                        'class',
                        'badge rounded-pill orders-status-badge ' + resp.status_class
                    );

                // Items
                $('#orders_detail_modal_items_list').html(resp.items_html);

                // Mostrar modal con Bootstrap 5
                const modalEl = document.getElementById('orders_detail_modal');
                const detailModal = new bootstrap.Modal(modalEl);
                detailModal.show();

                $('#orders_detail_modal').attr('data-order-id', resp.order_id);

            } else {
                // 🔹 MODO DESKTOP → panel derecho como ya estaba
                $('#orders_detail_empty').addClass('d-none');
                $('#orders_detail_content').removeClass('d-none');

                $('#orders_detail_title').text(resp.order_title);
                $('#orders_detail_meta').text(resp.meta_text);
                $('#orders_detail_status_badge')
                    .text(resp.status_label)
                    .attr(
                        'class',
                        'badge rounded-pill orders-status-badge ' + resp.status_class
                    );

                $('#orders_items_list').html(resp.items_html);
                $('#orders_detail_card').attr('data-order-id', resp.order_id);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Helper: genera una fila de item nueva
function renderNewOrderItemRow() {
    return `
    <div class="new-order-item-row row g-2 align-items-end mb-2">
        <div class="col-6">
            <label class="form-label small mb-1">Producto</label>
            <input type="text" class="form-control form-control-sm new-order-item-name"
                   placeholder="Nombre del producto">
        </div>
        <div class="col-3">
            <label class="form-label small mb-1">Cantidad</label>
            <input type="number" min="1" class="form-control form-control-sm new-order-item-qty"
                   value="1">
        </div>
        <div class="col-3">
            <label class="form-label small mb-1">Costo aprox</label>
            <input type="number" step="0.01" min="0"
                   class="form-control form-control-sm new-order-item-cost"
                   placeholder="0.00">
        </div>
    </div>`;
}

// Abrir modal de nueva lista (FAB y botón del empty state)
$(document).on('click', '#btn_orders_new_list, #btn_orders_new_list_empty', function (e) {
    e.preventDefault();

    // reset form
    const $form = $('#new_purchase_order_form');
    if ($form.length) {
        $form[0].reset();
    }

    const $itemsContainer = $('#new_order_items_container');
    $itemsContainer.empty().append(renderNewOrderItemRow());

    const modalEl = document.getElementById('orders_new_list_modal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
});

// Agregar otra fila de producto
$(document).on('click', '#btn_add_order_item', function (e) {
    e.preventDefault();
    $('#new_order_items_container').prepend(renderNewOrderItemRow());
});


// Guardar nueva lista de pedidos
// Enviar producto nuevo para agregarse a la lista
$(document).on('submit', '#add_order_item_form', function (e) {
    e.preventDefault();

    const orderId = $('#add_item_order_id').val(); // ya viene del helper
    const name = $('#add_item_name').val().trim();
    const qty = parseInt($('#add_item_qty').val(), 10) || 0;
    const costRaw = $('#add_item_cost').val().trim();

    if (!orderId) return;
    if (!name || qty <= 0) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Nombre y cantidad son obligatorios.'});
        return;
    }

    const items = [{
        product_name: name,
        quantity: qty,
        unit_cost: costRaw !== '' ? parseFloat(costRaw) : null
    }];

    $.ajax({
        url: orders_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'add_order_items',
            order_id: orderId,
            items: JSON.stringify(items)
        },
        success: function (resp) {
            if (resp.status === 'success') {
                const modalEl = document.getElementById('orders_add_item_modal');
                if (modalEl) {
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) instance.hide();
                }

                reloadCurrentOrderDetail(orderId);
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo agregar el producto.'});
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al agregar el producto.'});
        }
    });
});

$(document).on('submit', '#new_purchase_order_form', function (e) {
    e.preventDefault();
    let title = $('#new_order_title').val().trim();
    let order_date = $('#new_order_date').val().trim();
    if (!title) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'El nombre de la lista es obligatorio.'});
        return;
    }
    const notes = $('#new_order_notes').val().trim();

    // Recolectar items
    const items = [];
    $('.new-order-item-row').each(function () {
        const $row = $(this);
        const name = $row.find('.new-order-item-name').val().trim();
        const qty = parseInt($row.find('.new-order-item-qty').val(), 10) || 0;
        const costRaw = $row.find('.new-order-item-cost').val().trim();
        if (name && qty > 0) {
            items.push({
                product_name: name,
                quantity: qty,
                unit_cost: costRaw !== '' ? parseFloat(costRaw) : null
            });
        }

    });

    if (items.length === 0) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Agrega al menos un producto con nombre y cantidad.'});
        return;
    }

    $.ajax({
        url: orders_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'create_purchase_order',
            title: title,
            order_date: order_date,
            notes: notes,
            items: JSON.stringify(items)
        },
        success: function (resp) {
            if (resp.status === 'success') {
                const modalEl = document.getElementById('orders_new_list_modal');
                if (modalEl) {
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) instance.hide();
                }
                loadOrdersModule();
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo crear la lista de pedidos.'});
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al crear la lista de pedidos.'});
        }
    });
});


// Marcar / desmarcar item conseguido
$(document).on('click', '.orders-item-check', function () {
    const $check = $(this);
    const $row = $check.closest('.orders-item-row');
    const itemId = $row.data('order-item-id');

    const orderId = getCurrentOrderId();
    if (!itemId || !orderId) return;

    const isCurrentlyChecked = $check.hasClass('checked');
    const newState = isCurrentlyChecked ? 0 : 1;

    $.ajax({
        url: orders_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'toggle_order_item_acquired',
            order_item_id: itemId,
            order_id: orderId,
            is_acquired: newState
        },
        success: function (resp) {
            if (resp.status === 'success') {
                if (newState === 1) {
                    $check.addClass('checked').html('<i class="bi bi-check-lg"></i>');
                } else {
                    $check.removeClass('checked').html('');
                }

                // actualizar badge de estado (desktop y modal)
                $('#orders_detail_status_badge')
                    .text(resp.status_label)
                    .attr('class', 'badge rounded-pill orders-status-badge ' + resp.status_class);

                $('#orders_detail_modal_status_badge')
                    .text(resp.status_label)
                    .attr('class', 'badge rounded-pill orders-status-badge ' + resp.status_class);

            } else {
                console.error(resp.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Abrir modal para agregar producto a la lista actual
$(document).on('click', '#btn_orders_add_items_detail', function (e) {
    e.preventDefault();

    const orderId = getCurrentOrderId();
    if (!orderId) {
        console.warn('No hay order_id activo.');
        return;
    }

    $('#add_item_order_id').val(orderId);

    const $form = $('#add_order_item_form');
    if ($form.length) {
        $form[0].reset();
        $('#add_item_qty').val(1);
    }

    const modalEl = document.getElementById('orders_add_item_modal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
});

function reloadCurrentOrderDetail(orderId) {
    if (!orderId) return;

    const isSmallScreen = window.matchMedia('(max-width: 991.98px)').matches;

    $.ajax({
        url: orders_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_order_detail',
            order_id: orderId
        },
        success: function (resp) {
            if (resp.status !== 'success') return;

            if (isSmallScreen) {
                $('#orders_detail_modal').attr('data-order-id', resp.order_id);
                $('#orders_detail_modal_title').text(resp.order_title);
                $('#orders_detail_modal_meta').text(resp.meta_text);
                $('#orders_detail_modal_status_badge')
                    .text(resp.status_label)
                    .attr('class', 'badge rounded-pill orders-status-badge ' + resp.status_class);
                $('#orders_detail_modal_items_list').html(resp.items_html);
                $('#orders_detail_card').attr('data-order-id', resp.order_id);
                $('#orders_detail_modal').attr('data-order-id', resp.order_id);

            } else {
                $('#orders_detail_card').attr('data-order-id', resp.order_id);
                $('#orders_detail_empty').addClass('d-none');
                $('#orders_detail_content').removeClass('d-none');
                $('#orders_detail_title').text(resp.order_title);
                $('#orders_detail_meta').text(resp.meta_text);
                $('#orders_detail_status_badge')
                    .text(resp.status_label)
                    .attr('class', 'badge rounded-pill orders-status-badge ' + resp.status_class);
                $('#orders_items_list').html(resp.items_html);
                $('#orders_detail_card').attr('data-order-id', resp.order_id);
                $('#orders_detail_modal').attr('data-order-id', resp.order_id);

            }
        }
    });
}

function getCurrentOrderId() {
    // 1) Prioridad: la lista marcada como activa
    const $active = $('.orders-list-item.active').first();
    if ($active.length) {
        return $active.data('order-id');
    }

    // 2) Si por lo que sea no hay activa, probamos modal y card
    const modalId = $('#orders_detail_modal').data('order-id');
    if (modalId) return modalId;

    const cardId = $('#orders_detail_card').data('order-id');
    if (cardId) return cardId;

    return null;
}

// =======================
// Estado global de filtros
// =======================
let ordersFilterStatus = 'all';        // all | open | partial | closed
let ordersFilterPeriod = 'this_month'; // this_month | last_3_months | all
let ordersFilterSearch = '';           // string de búsqueda

// Helper: aplicar TODOS los filtros a las listas
function applyOrdersFilters() {
    const term = ordersFilterSearch.trim().toLowerCase();

    const now = new Date();
    const currentMonth = now.getMonth();      // 0-11
    const currentYear  = now.getFullYear();

    // para "últimos 3 meses"
    const threeMonthsAgo = new Date(now);
    threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);

    let visibleCount = 0;

    $('.orders-list-item').each(function () {
        const $item = $(this);

        const status   = ($item.data('status') || '').toString();
        const dateStr  = ($item.data('order-date') || '').toString(); // "YYYY-MM-DD" o ""
        const notes    = ($item.data('notes') || '').toString().toLowerCase();

        const title    = $item.find('.orders-list-title').text().toLowerCase();

        let show = true;

        // 1) Filtro por estado
        if (ordersFilterStatus !== 'all' && status !== ordersFilterStatus) {
            show = false;
        }

        // 2) Filtro por periodo (solo si aún sigue "show")
        if (show && ordersFilterPeriod !== 'all' && dateStr) {
            const date = new Date(dateStr); // YYYY-MM-DD -> Date

            if (ordersFilterPeriod === 'this_month') {
                const sameMonth = (date.getMonth() === currentMonth &&
                                   date.getFullYear() === currentYear);
                if (!sameMonth) show = false;
            } else if (ordersFilterPeriod === 'last_3_months') {
                if (date < threeMonthsAgo) show = false;
            }
        }

        // 3) Filtro por search
        if (show && term.length > 0) {
            const hayCoincidencia =
                title.includes(term) ||
                notes.includes(term);
            if (!hayCoincidencia) {
                show = false;
            }
        }

        if (show) {
            $item.removeClass('d-none');
            visibleCount++;
        } else {
            $item.addClass('d-none');
        }
    });

    // Actualizar badge de conteo
    const $badge = $('#orders_count_badge');
    if ($badge.length) {
        if (visibleCount === 0) {
            $badge.text('0 listas');
        } else if (visibleCount === 1) {
            $badge.text('1 lista');
        } else {
            $badge.text(visibleCount + ' listas');
        }
    }
}

// =======================
// Handlers de filtros
// =======================

// Filtro por estado (dropdown)
$(document).on('click', '.orders-status-filter', function (e) {
    e.preventDefault();

    const status = $(this).data('status') || 'all';
    ordersFilterStatus = status;

    // Actualizar texto del botón dropdown
    const labelMap = {
        all: 'Estado: Todos',
        open: 'Estado: Abiertos',
        partial: 'Estado: Parciales',
        closed: 'Estado: Cerrados'
    };
    $('#ordersFilterStatusDropdown').text(labelMap[status] || 'Estado: Todos');

    applyOrdersFilters();
});

// Filtro por periodo (botones)
$(document).on('click', '.orders-period-filter', function () {
    $('.orders-period-filter').removeClass('active');
    $(this).addClass('active');

    const period = $(this).data('period') || 'this_month';
    ordersFilterPeriod = period;

    applyOrdersFilters();
});

// Búsqueda por texto
$(document).on('input', '#orders_search_input', function () {
    ordersFilterSearch = $(this).val() || '';
    applyOrdersFilters();
});


