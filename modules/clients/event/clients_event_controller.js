const clients_controller = "modules/clients/controller/clients_controller.php";

function show_loader() {
    $("#loading-spinner").removeClass("d-none");
}

function hide_loader() {
    $("#loading-spinner").addClass("d-none");
}

function get_current_time() {
    var now = new Date();
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var seconds = String(now.getSeconds()).padStart(2, '0');
    var new_time = hours + ':' + minutes + ':' + seconds;

    return new_time;
}

function get_current_date() {
    var now = new Date();
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var new_date = year + '-' + month + '-' + day;

    return new_date;
}

function errorMessage(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
    });
    console.error("Error:", message);
}

function loadClients() {
    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: { user_request: 'fetch_clients' },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#app_content').html(resp.view);
                initClientsModule();
            } else {
                console.error(resp.message);
                // aquí puedes mostrar toast / alerta
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
}


$(document).on('custom:nav', '#fetch_clients', function () {
    loadClients();
});


// Abrir perfil de cliente
$(document).on('click', '.clients-btn-view', function (e) {
    e.preventDefault();
    const customerId = $(this).data('customer-id');
    if (!customerId) return;

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_client_profile',
            customer_id: customerId
        },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#modal_container').html(resp.view);
                $('#client_profile_modal').modal('show');
            } else {
                console.error(resp.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Cerrar modal perfil cliente
$(document).on('click', '.client-profile-close, .client-profile-backdrop', function () {
    $('#modal_container').empty();
});

$(document).on('click', '.client-profile-tab', function () {
    const tab = $(this).data('tab');

    $('.client-profile-tab').removeClass('active');
    $(this).addClass('active');

    $('.client-profile-tab-pane').removeClass('active');
    $(`.client-profile-tab-pane[data-tab-content="${tab}"]`).addClass('active');
});

// Abrir modal de nueva compra
$(document).on('click', '.clients-btn-add-sale, .client-profile-add-sale', function (e) {
    e.preventDefault();
    const customerId = $(this).data('customer-id');
    if (!customerId) return;

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_new_sale_modal',
            customer_id: customerId
        },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#modal_container').html(resp.view);
                const modalEl = document.getElementById('client_new_sale_modal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } else {
                console.error(resp.message);
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Registrar compra
$(document).on('submit', '#client_new_sale_form', function (e) {
    e.preventDefault();

    const customerId = parseInt($('#sale_customer_id').val(), 10);
    const saleDate = $('#sale_date').val();
    const notes = $('#sale_notes').val().trim();

    if (!customerId) return;

    const items = [];
    let invalid = false;

    $('#client_new_sale_form .sale-item-row').each(function () {
        const $row = $(this);
        const checked = $row.find('.sale-item-select').is(':checked');
        if (!checked) return;

        const poiId = $row.data('order-item-id');
        const max = parseInt($row.data('max-qty'), 10) || 0;
        let qty = parseInt($row.find('.sale-item-qty').val(), 10) || 0;
        const price = parseFloat($row.find('.sale-item-price').val()) || 0;

        if (!poiId || qty <= 0 || price <= 0) {
            invalid = true;
            return;
        }

        if (max > 0 && qty > max) {
            qty = max; // clamp por si acaso
            $row.find('.sale-item-qty').val(qty);
        }

        items.push({
            purchase_order_item_id: poiId,
            quantity: qty,
            unit_price: price
        });
    });

    if (invalid) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Revisa cantidad y precio de los productos seleccionados.' });
        return;
    }

    if (items.length === 0) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Selecciona al menos un producto y asigna precio.' });
        return;
    }

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'create_client_sale',
            customer_id: customerId,
            sale_date: saleDate,
            notes: notes,
            items: JSON.stringify(items)
        },
        success: function (resp) {
            if (resp.status === 'success') {
                // Cerrar modal
                const modalEl = document.getElementById('client_new_sale_modal');
                if (modalEl) {
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) instance.hide();
                }

                // Recargar el perfil del cliente para ver saldo, compras, movimientos actualizados
                reloadClientProfile(customerId);
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo registrar la compra.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al registrar la compra.' });
        }
    });
});

function reloadClientProfile(customerId) {
    if (!customerId) return;

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_client_profile',
            customer_id: customerId
        },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#modal_container').html(resp.view);
                const modalEl = document.getElementById('client_profile_modal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
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

// Toggle visual de selección de producto
$(document).on('change', '.sale-item-select', function () {
    const $card = $(this).closest('.client-sale-item-card');
    if (!$card.length) return;

    if (this.checked) {
        $card.addClass('selected');
    } else {
        $card.removeClass('selected');
    }
});

// Click en card → toggle check (sin pelearse con inputs)
$(document).on('click', '.client-sale-item-card', function (e) {
    // Evitar conflicto si hicieron click directo en un input
    if ($(e.target).is('input, label, .form-control')) return;

    const $checkbox = $(this).find('.sale-item-select').first();
    if (!$checkbox.length) return;

    $checkbox.prop('checked', !$checkbox.prop('checked')).trigger('change');
});

// Evitar que cantidad se pase del máximo o se vaya a 0
$(document).on('input change', '.sale-item-qty', function () {
    const $input = $(this);
    const $row = $input.closest('.sale-item-row');
    const max = parseInt($row.data('max-qty'), 10) || 0;

    let val = parseInt($input.val(), 10);

    if (isNaN(val) || val < 1) {
        val = 1;
    }
    if (max > 0 && val > max) {
        val = max;
    }

    $input.val(val);
});

// Abrir modal de pago (desde card o desde perfil)
$(document).on('click', '.clients-btn-add-payment, .client-profile-add-payment', function (e) {
    e.preventDefault();

    let customerId = $(this).data('customer-id');

    // fallback por si algún día lo tomamos del modal
    if (!customerId) {
        const $modal = $('#client_profile_modal');
        if ($modal.length) {
            customerId = $modal.data('customer-id');
        }
    }

    if (!customerId) {
        console.warn('No se pudo determinar el customer_id para nuevo pago.');
        return;
    }

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_new_payment_modal',
            customer_id: customerId
        },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#modal_container').html(resp.view);
                const modalEl = document.getElementById('client_new_payment_modal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo abrir el modal de pago.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Guardar pago con comprobante (FormData)
$(document).on('submit', '#client_new_payment_form', function (e) {
    e.preventDefault();

    const customerId = parseInt($('#payment_customer_id').val(), 10);
    const paymentDate = $('#payment_date').val();
    const amountRaw = $('#payment_amount').val();
    const method = $('#payment_method').val();
    const notes = $('#payment_notes').val().trim();
    const fileInput = $('#payment_receipt')[0];

    const amount = parseFloat(amountRaw);

    if (!customerId) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Cliente inválido.' });
        return;
    }
    if (isNaN(amount) || amount <= 0) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Ingresa un monto de pago válido.' });
        return;
    }

    const formData = new FormData();
    formData.append('user_request', 'create_client_payment');
    formData.append('customer_id', customerId);
    formData.append('payment_date', paymentDate);
    formData.append('amount', amount);
    formData.append('method', method);
    formData.append('notes', notes);

    if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('payment_receipt', fileInput.files[0]);
    }

    $.ajax({
        url: clients_controller,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (resp) {
            if (resp.status === 'success') {
                const modalEl = document.getElementById('client_new_payment_modal');
                if (modalEl) {
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) instance.hide();
                }

                // recargar perfil para ver saldo, pagos y movimientos actualizados
                reloadClientProfile(customerId);
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo registrar el pago.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al registrar el pago.' });
        }
    });
});

// Ver detalle de pago
$(document).on('click', '.client-payment-view', function (e) {
    e.preventDefault();
    const paymentId = $(this).data('payment-id');
    if (!paymentId) return;

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_payment_detail',
            payment_id: paymentId
        },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#modal_container').html(resp.view);
                const modalEl = document.getElementById('client_payment_detail_modal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo cargar el detalle del pago.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Estado actual de filtros
const clientsFilters = {
    status: 'all',      // all | active | inactive | debtor
    period: 'this_week',// this_week | last_week | all
    search: ''          // texto del input
};

function getWeekBoundaries() {
    const now = new Date();
    const current = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const day = current.getDay(); // 0 dom, 1 lun, ... 6 sáb

    // Queremos lunes como inicio
    const diffToMonday = (day === 0 ? -6 : 1 - day);

    const mondayThisWeek = new Date(current);
    mondayThisWeek.setDate(current.getDate() + diffToMonday);
    mondayThisWeek.setHours(0, 0, 0, 0);

    const mondayLastWeek = new Date(mondayThisWeek);
    mondayLastWeek.setDate(mondayThisWeek.getDate() - 7);

    const sundayLastWeek = new Date(mondayThisWeek);
    sundayLastWeek.setDate(mondayThisWeek.getDate() - 1);
    sundayLastWeek.setHours(23, 59, 59, 999);

    return {
        mondayThisWeek,
        mondayLastWeek,
        sundayLastWeek
    };
}

function applyClientFilters() {
    const { status, period, search } = clientsFilters;
    const searchTerm = (search || '').toLowerCase().trim();

    const { mondayThisWeek, mondayLastWeek, sundayLastWeek } = getWeekBoundaries();

    let visibleCount = 0;
    let visibleDebtors = 0;
    let visibleClear = 0;
    let visibleTotalBalance = 0;

    $('#clients_grid .clients-card').each(function () {
        const $card = $(this);

        const cardStatus  = ($card.data('status') || '').toString();
        const cardBalance = parseFloat($card.data('balance')) || 0;
        const lastPayRaw  = ($card.data('last-payment-date') || '').toString();

        let show = true;

        // 1) Filtro por estado
        if (status !== 'all') {
            if (status === 'debtor') {
                // "Con deuda" = balance > 0
                if (!(cardBalance > 0)) {
                    show = false;
                }
            } else {
                if (cardStatus !== status) {
                    show = false;
                }
            }
        }

        // 2) Filtro por periodo (según última fecha de pago)
        if (show && period !== 'all') {
            if (!lastPayRaw) {
                // Sin pago -> no entra ni en esta semana ni en la pasada
                show = false;
            } else {
                const parts = lastPayRaw.split('-'); // 'YYYY-MM-DD'
                const d = new Date(
                    parseInt(parts[0], 10),
                    parseInt(parts[1], 10) - 1,
                    parseInt(parts[2], 10)
                );
                d.setHours(12, 0, 0, 0); // evitar temas de zona extraños

                if (period === 'this_week') {
                    if (!(d >= mondayThisWeek)) {
                        show = false;
                    }
                } else if (period === 'last_week') {
                    if (!(d >= mondayLastWeek && d <= sundayLastWeek)) {
                        show = false;
                    }
                }
            }
        }

        // 3) Buscador (nombre / teléfono / email dentro del texto de la card)
        if (show && searchTerm) {
            const cardText = $card.text().toLowerCase();
            if (!cardText.includes(searchTerm)) {
                show = false;
            }
        }

        // Mostrar / ocultar
        if (show) {
            $card.removeClass('d-none');
            visibleCount++;
            if (cardBalance > 0) {
                visibleDebtors++;
                visibleTotalBalance += cardBalance;
            } else {
                visibleClear++;
            }
        } else {
            $card.addClass('d-none');
        }
    });

    // Actualizar KPIs (si tienes esos IDs en la vista)
    $('#kpi_total_clients').text(visibleCount);
    $('#kpi_debtor_clients').text(visibleDebtors);
    $('#kpi_clear_clients').text(visibleClear);
    $('#kpi_total_balance').text(
        '$' + visibleTotalBalance.toFixed(2)
    );
}

// Click en las opciones del dropdown de estado
$(document).on('click', '.dropdown-item[data-status]', function (e) {
    e.preventDefault();
    const status = $(this).data('status');

    clientsFilters.status = status;

    // Actualizar texto del botón
    let label = 'Todos';
    if (status === 'active') label = 'Activos';
    else if (status === 'inactive') label = 'Inactivos';
    else if (status === 'debtor') label = 'Con deuda';

    $('#filterStatusDropdown').text('Estado: ' + label);

    applyClientFilters();
});

// Click en "Esta semana / Semana pasada / Todo"
$(document).on('click', '.period-filter', function () {
    const $btn = $(this);
    const period = $btn.data('period');

    clientsFilters.period = period;

    // Marcar activo visualmente
    $('.period-filter').removeClass('active');
    $btn.addClass('active');

    applyClientFilters();
});

// Filtrar mientras escribe
$(document).on('input', '#clients_search_input', function () {
    clientsFilters.search = $(this).val();
    applyClientFilters();
});

function initClientsModule() {
    // Reset filtros al abrir módulo
    clientsFilters.status = 'all';
    clientsFilters.period = 'this_week';
    clientsFilters.search = '';

    $('#filterStatusDropdown').text('Estado: Todos');
    $('.period-filter').removeClass('active');
    $('.period-filter[data-period="this_week"]').addClass('active');
    $('#clients_search_input').val('');

    applyClientFilters();
}



// Reusar las acciones existentes
$(document).on('click', '.client-action-view', function (e) {
    e.preventDefault();
    const id = $(this).data('customer-id');
    if (!id) return;
    // Llamas tu flujo existente de abrir perfil
    $('.clients-btn-view[data-customer-id="'+id+'"]').trigger('click');
});

$(document).on('click', '.client-action-add-sale', function (e) {
    e.preventDefault();
    const id = $(this).data('customer-id');
    if (!id) return;
    $('.clients-btn-add-sale[data-customer-id="'+id+'"]').trigger('click');
});

$(document).on('click', '.client-action-add-payment', function (e) {
    e.preventDefault();
    const id = $(this).data('customer-id');
    if (!id) return;
    $('.clients-btn-add-payment[data-customer-id="'+id+'"]').trigger('click');
});

// Nuevo cliente (toolbar, FAB, empty state)
$(document).on('click', '#btn_new_client, #clients_fab_new_client, #btn_empty_new_client', function (e) {
    e.preventDefault();

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: { user_request: 'fetch_new_client_modal' },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#modal_container').html(resp.view);
                const modalEl = document.getElementById('client_form_modal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo abrir el formulario de cliente.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Editar cliente (desde card o desde perfil)
$(document).on('click', '.client-action-edit, .client-profile-edit', function (e) {
    e.preventDefault();
    const customerId = $(this).data('customer-id');
    if (!customerId) return;

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: {
            user_request: 'fetch_edit_client_modal',
            customer_id: customerId
        },
        success: function (resp) {
            if (resp.status === 'success') {
                $('#modal_container').html(resp.view);
                const modalEl = document.getElementById('client_form_modal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo abrir el editor de cliente.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
        }
    });
});

// Crear / actualizar cliente
$(document).on('submit', '#client_form', function (e) {
    e.preventDefault();

    const id     = $('#client_id').val();
    const name   = $('#client_name').val().trim();
    const phone  = $('#client_phone').val().trim();
    const email  = $('#client_email').val().trim();
    const status = $('#client_status').val();

    if (!name) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'El nombre del cliente es obligatorio.' });
        return;
    }

    const isEdit = id !== '';

    const payload = {
        user_request: isEdit ? 'update_client' : 'create_client',
        name,
        phone,
        email,
        status
    };

    if (isEdit) {
        payload.customer_id = parseInt(id, 10);
    }

    $.ajax({
        url: clients_controller,
        method: 'POST',
        dataType: 'json',
        data: payload,
        success: function (resp) {
            if (resp.status === 'success') {
                const modalEl = document.getElementById('client_form_modal');
                if (modalEl) {
                    const instance = bootstrap.Modal.getInstance(modalEl);
                    if (instance) instance.hide();
                }

                // Recargar módulo de clientes (para refrescar grid y KPIs)
                loadClientsModule && loadClientsModule();

                // Si estabas en el perfil del cliente editado, recárgalo
                if (isEdit && typeof reloadClientProfile === 'function') {
                    const cid = parseInt(id, 10);
                    if (!isNaN(cid)) {
                        reloadClientProfile(cid);
                    }
                }
            } else {
                console.error(resp.message);
                Swal.fire({ icon: 'error', title: 'Error', text: resp.message || 'No se pudo guardar el cliente.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX error:', error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al guardar el cliente.' });
        }
    });
});

function loadClientsModule() {
    loadClients();
}

$(document).on('hidden.bs.modal', '#client_form_modal, #client_new_payment_modal, #client_new_sale_modal, #client_payment_detail_modal', function () {
    // Limpiar contenido del modal al cerrarlo
    $('#modal_container').empty();
    $('.modal-backdrop').remove();
});

// Evitar que el click en los 3 puntos dispare el "ver perfil"
$(document).on('click', '.clients-card-menu, .clients-card-menu *', function (e) {
    e.stopPropagation(); // no sube al .clients-btn-view
});

$(document).on('click', '.dropdown-menu', function (e) {
    e.stopPropagation();
});
