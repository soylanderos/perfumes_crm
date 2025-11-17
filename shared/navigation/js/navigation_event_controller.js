$(function () {
    $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal-backdrop').remove();
    });

});



// Cambiar estado activo y disparar acciones
$(document).on('click', '.floating-nav-btn', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const controller = $btn.data('controller');

    // Marcar activo
    $('.floating-nav-btn').removeClass('active');
    $btn.addClass('active');

    // Si es logout, no disparamos controller
    if ($btn.attr('id') === 'btn_log_out') {
        // aquí pones tu lógica de logout
        // ej: $('#btn_log_out').trigger('yourLogoutHandler');
        return;
    }

    // Disparar la acción correspondiente
    switch (controller) {
        case 'dashboard':
            $('#fetch_dashboard').trigger('custom:nav');
            break;
        case 'clients':
            $('#fetch_clients').trigger('custom:nav');
            break;
        case 'skus':
            $('#fetch_skus').trigger('custom:nav');
            break;
        case 'products':
            $('#fetch_products').trigger('custom:nav');
            break;
    }
});
