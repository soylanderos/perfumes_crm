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

$(document).on('click', '#btn_add_new_client', function (e) {
    e.preventDefault();
    let user_request = 'fetch_add_client_form';

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: { user_request: user_request },
        beforeSend: function () {
            show_loader();
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#modal_container').html(response.view);
                $('#client_modal').modal('show');
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function (xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});

$(document).on('submit', '#form_customer', function (e) {
    e.preventDefault();
    let form = $(this)[0];
    let formData = new FormData(form);

    //Validation form
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function () {
            show_loader();
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#client_modal').modal('hide');
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function (xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});

$(document).on('hidden.bs.modal', '#client_modal', function () {
    $('#modal_container').empty();
    $('.modal-backdrop').remove();
});

$(document).on('click', '.client-card', function (e) {
    e.preventDefault();
    let customer_id = $(this).data('customer-id');
    let user_request = 'fetch_client_details';

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: { user_request: user_request, customer_id: customer_id },
        beforeSend: function () {
            show_loader();
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#modal_container').html(response.view);
                $('#client_details_modal').modal('show');
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function (xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});

$(document).on('hidden.bs.modal', '#client_details_modal', function () {
    $('#modal_container').empty();
    $('.modal-backdrop').remove();
});

$(document).on('click', '#btn_edit_client', function (e) {
    e.preventDefault();

    //Hide Modal Details
    $('#client_details_modal').modal('hide');

    let customer_id = $(this).data('customer-id');
    let user_request = 'fetch_client_form';

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: { user_request: user_request, customer_id: customer_id },
        beforeSend: function () {
            show_loader();
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#modal_container').html(response.view);
                $('#client_modal').modal('show');
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function (xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});

$(document).on('hidden.bs.modal', '#client_modal', function () {
    $('#modal_container').empty();
    $('.modal-backdrop').remove();
});

$(document).on('click', '#tab-orders', function (e) {
    e.preventDefault();
    let customer_id = $(this).data('customer-id');
    let user_request = 'fetch_client_orders';

    $.ajax({
        url: clients_controller,
        type: 'POST',
        data: { user_request: user_request, customer_id: customer_id },
        beforeSend: function () {
            show_loader();
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#pane-orders').html(response.view);
                initOrderForm();
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function (xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});

function initOrderForm() {
    const $list = $('#order_items_list');
    const $addBtn = $('#btn_add_item');
    const $totalEl = $('#order_total');
    const $totalInput = $('#order_total_input');
    const $form = $('#form_order_create');

    function money(n) { n = Number(n || 0); return '$' + n.toFixed(2); }

    function recalc() {
        let total = 0;
        $list.find('li.list-group-item[data-index]').each(function () {
            const $li = $(this);
            const qty = parseFloat($li.find('.js-qty').val()) || 0;
            const price = parseFloat($li.find('.js-price').val()) || 0;
            const sub = qty * price;
            $li.find('.js-subtotal').val(money(sub));
            total += sub;
        });
        $totalEl.text(money(total));
        $totalInput.val(total.toFixed(2));
    }

    function bindItem($li) {
        const $sel = $li.find('.js-product');
        const $qty = $li.find('.js-qty');
        const $price = $li.find('.js-price');
        const $rmBtn = $li.find('.js-remove');

        $sel.on('change', function () {
            const $opt = $(this).find('option:selected');
            const p = parseFloat($opt.data('price'));
            if (!isNaN(p)) $price.val(p.toFixed(2));
            if (!$qty.val() || Number($qty.val()) <= 0) $qty.val(1);
            recalc();
        });

        $qty.on('input', recalc);
        $price.on('input', recalc);

        $rmBtn.on('click', function () {
            if ($(this).is(':disabled')) return;
            $li.remove();
            recalc();
            const $items = $list.find('li.list-group-item[data-index]');
            if ($items.length === 1) {
                $items.eq(0).find('.js-remove').prop('disabled', true);
            }
        });
    }

    // Inicial: primera línea
    bindItem($list.find('li.list-group-item[data-index="0"]'));
    recalc();

    // Agregar nueva línea
    $addBtn.on('click', function () {
        const $tmpl = $list.find('li[data-template="true"]').first();
        const $clone = $tmpl.clone(true, true);
        const nextIndex = $list.find('li.list-group-item[data-index]').length;

        $clone.removeClass('d-none')
            .removeAttr('data-template')
            .removeAttr('aria-hidden')
            .attr('data-index', String(nextIndex));

        // data-name -> name, reemplazando __i__ por nextIndex, y habilitar
        $clone.find('[data-name]').each(function () {
            const $el = $(this);
            const n = $el.attr('data-name').replace('__i__', nextIndex);
            $el.attr('name', n).prop('disabled', false);
        });
        // Habilitar botón quitar
        $clone.find('.js-remove').prop('disabled', false);

        $list.append($clone);
        bindItem($clone);
    });

    // Submit crear orden (envía FormData por AJAX jQuery)
    $form.on('submit', function (e) {
        e.preventDefault();

        const $items = $list.find('li.list-group-item[data-index]');
        if ($items.length === 0) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Agrega al menos un producto.' }); return; }

        // Validaciones básicas
        let ok = true;
        $items.each(function () {
            const $li = $(this);
            const selVal = $li.find('.js-product').val();
            const qty = parseFloat($li.find('.js-qty').val());
            const price = parseFloat($li.find('.js-price').val());
            if (!selVal) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Selecciona un producto en cada línea.' }); ok = false; return false; }
            if (!(qty > 0)) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Cantidad inválida.' }); ok = false; return false; }
            if (!(price >= 0)) { Swal.fire({ icon: 'warning', title: 'Atención', text: 'Precio inválido.' }); ok = false; return false; }
        });
        if (!ok) return;

        const formEl = this; // DOM nativo
        const fd = new FormData(formEl);

        //append user_request
        fd.append('user_request', 'create_order');

        $.ajax({
            url: clients_controller,
            method: 'POST',
            data: fd,
            processData: false, // importante para FormData
            contentType: false, // importante para FormData
            success: function () {
                // Reset del form y dejar solo la primera línea limpia
                formEl.reset();
                $items.each(function (i) { if (i > 0) $(this).remove(); });

                const $first = $list.find('li.list-group-item[data-index="0"]');
                $first.find('.js-product').prop('selectedIndex', 0);
                $first.find('.js-qty').val(1);
                $first.find('.js-price').val('');
                $first.find('.js-subtotal').val('$0.00');

                recalc();
                Swal.fire({
                    icon: 'success',
                    title: 'Orden creada',
                    text: 'La orden se ha creado correctamente.',
                });
                // Aquí puedes refrescar el listado de órdenes si lo necesitas.
                $('#tab-orders').trigger('click'); // Volver a órdenes para ver saldo actualizado
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo crear la orden' + (xhr.responseText ? (': ' + xhr.responseText) : '.'),
                });
            }
        });
    });
}

$(document).on('click', '#tab-payments', function (e) {
    e.preventDefault();
    const customerId = $(this).data('customer-id');
    if (!customerId) return;

    // Cargar pagos del cliente
    $.ajax({
        url: clients_controller,
        method: 'POST',
        data: {
            user_request: 'fetch_client_payments',
            customer_id: customerId
        },
        beforeSend: function () {
            show_loader();
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response.status === 'success') {
                $('#pane-payments').html(response.view);
                //initPaymentForm();
            } else {
                errorMessage(response.message);
            }
            hide_loader();
        },
        error: function (xhr, status, error) {
            hide_loader();
            console.error("Error:", error);
        }
    });
});

$(document).on('submit', '#form_payment_general', function (e) {
    e.preventDefault();

    const form = this;
    const fd = new FormData(form); // incluye el file receipt si se eligió

    //append user_request
    fd.append('user_request', 'create_payment');

    $.ajax({
        url: clients_controller,   // tu endpoint
        method: 'POST',
        data: fd,
        processData: false,            // necesario para FormData
        contentType: false,            // necesario para FormData
        success: function (resp) {
            // Limpia el form
            form.reset();
            Swal.fire({
                icon: 'success',
                title: 'Pago registrado',
                text: 'El pago se ha registrado correctamente.',
            });
            // Aquí puedes refrescar “Pagos recientes” y las cifras de saldo/orden
            // p.ej. disparar un evento: document.dispatchEvent(new CustomEvent('payments:changed'));

            $('#tab-payments').trigger('click'); // Volver a órdenes para ver saldo actualizado
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar el pago' + (xhr.responseText ? (': ' + xhr.responseText) : '.'),
            });
        }
    });
});

$(document).on('submit', '.js-pay-order-form', function (e) {
    e.preventDefault();
    const $form = $(this);
    const fd = new FormData(this); // incluye receipt si se cargó

    const $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true).text('Guardando…');

    //append user_request
    fd.append('user_request', 'create_payment');

    $.ajax({
        url: clients_controller,   // mismo endpoint que el general
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false
    })
        .done(function (resp) {
            // Limpia solo campos editables (no ocultos)
            $form.find('input[name="amount"]').val('');
            $form.find('input[name="note"]').val('');
            $form.find('input[type="file"][name="receipt"]').val('');
            // Feedback mínimo
            $btn.removeClass('btn-primary').addClass('btn-success').text('Registrado');
            setTimeout(function () {
                $btn.addClass('btn-primary').removeClass('btn-success').text('Registrar pago').prop('disabled', false);
            }, 1200);

            // Opcional: refrescar saldos/listas
            // document.dispatchEvent(new CustomEvent('payments:changed', { detail: resp }));

            $('#tab-orders').trigger('click'); // Volver a órdenes para ver saldo actualizado
        })
        .fail(function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo registrar el pago' + (xhr.responseText ? (': ' + xhr.responseText) : '.'),
            });
            $btn.prop('disabled', false).text('Registrar pago');
        });
});


