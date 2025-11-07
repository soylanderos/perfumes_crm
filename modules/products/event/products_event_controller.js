$(document).on('input', '#catalog_search', function () {
    const q = $(this).val().toLowerCase().trim();
    $('#catalog_grid > [data-name]').each(function () {
        const name = $(this).data('name');
        $(this).toggle(!q || String(name).includes(q));
    });
});