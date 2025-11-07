// modules/sku/js/sku_admin.js
const SKU_CONTROLLER = 'modules/sku/controller/sku_controller.php';

$(document).on('input', '#catalog_search', function () {
    const q = $(this).val().toLowerCase().trim();
    $('#catalog_grid > [data-name]').each(function () {
        const name = $(this).data('name');
        $(this).toggle(!q || String(name).includes(q));
    });
});