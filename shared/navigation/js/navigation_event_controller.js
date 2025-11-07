$(function () {

    $(document).on('click', '[data-section]', function () {
        if ($('.modal').length) {
            $('.modal').modal('hide').remove();
            $('.modal-backdrop').remove();
        }
    });

    $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal-backdrop').remove();
    });

    // Evento genérico para todos los links del sidebar
    $(document).on('click', '.sidebar-item', function (e) {
        e.preventDefault();

        // Guardamos el link clickeado
        const $link = $(this);
        const user_request = $link.attr('id'); // ID del link usado como request

        if (!user_request) return; // Si no tiene ID, no hace nada
        // definir el controlador en base al ID del link
        const controller = $link.data('controller');
        //armar controller URL
        const controllerUrl = 'modules/' + controller + '/controller/' + controller + '_controller.php';

        console.log('Controller URL:', controllerUrl);

        $.ajax({
            url: controllerUrl,
            type: 'POST',
            data: { user_request: user_request },
            success: function (data) {
                var response = JSON.parse(data);
                if (response.status === 'success') {
                    $('#app-content').html(response.view);

                    // ✅ Cerrar el sidebar
                    $('.sidebar').addClass('collapsed');
                    // ✅ Cambiar icono del botón menú si aplica
                    $('.menu-toggle-btn .material-symbols-rounded').text('menu');
                    if (controller === 'dashboard') {
                        window.initDashboardCharts('#app-content');
                    }

                    if (controller === 'clients') {
                      initClientsEventController()
                    }

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while fetching content.'
                });
            }
        });
    });

    // Evento para hover sobre sidebar items agregand la clase collapsed
    $(document).on('mouseenter', '.nav-item .sidebar-item', function () {
        //$('.sidebar').removeClass('collapsed');
    });

    // Evento para salir del hover sobre sidebar items removiendo la clase collapsed
    $(document).on('mouseleave', '.nav-item .sidebar-item', function () {
        //$('.sidebar').addClass('collapsed');
    });

    // Evento para el botón de logout
    let login_controller = 'modules/login/controller/login_controller.php';
    $(document).on('click', '#btn_log_out', function () {
        let user_request = 'logout';
        $.ajax({
            url: login_controller,
            type: 'POST',
            data: { user_request: user_request },
            success: function (data) {
                let response = JSON.parse(data);
                if (response.status === 'success') {
                    window.location.href = 'login.php';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to log out.'
                    });
                }
            }
        });
    });

    // Función para abrir/cerrar dropdown
    function toggleDropdown($dropdown, isOpen) {
        const $menu = $dropdown.find(".dropdown-menu");
        $dropdown.toggleClass("open", isOpen);
        $menu.css("height", isOpen ? $menu.prop("scrollHeight") + "px" : 0);
    }

    // Cerrar todos los dropdowns abiertos
    function closeAllDropdowns() {
        $(".dropdown-container.open").each(function () {
            toggleDropdown($(this), false);
        });
    }

    // Click en toggles de dropdown
    $(".dropdown-toggle").on("click", function (e) {
        e.preventDefault();
        const $dropdown = $(this).closest(".dropdown-container");
        const isOpen = $dropdown.hasClass("open");
        closeAllDropdowns();
        toggleDropdown($dropdown, !isOpen);
    });

    // Toggle del sidebar (botones)
    $(".sidebar-toggler, .sidebar-menu-button, .menu-toggle-btn").on("click", function () {
        closeAllDropdowns();
        $(".sidebar").toggleClass("collapsed");

        // Cambiar ícono dinámicamente
        const $icon = $(this).find(".material-symbols-rounded");
        if ($(".sidebar").hasClass("collapsed")) {
            $icon.text("menu");
        } else {
            $icon.text("close");
        }
    });

    // Colapsar por defecto en pantallas pequeñas
    if ($(window).width() <= 1024) {
        $(".sidebar").addClass("collapsed");
    }


});



function initClientsEventController() {
    const $container = $('.container-responsive-350');      // contenedor de cards
    const $cards = $container.find('.client-card');      // colección inicial

    /* ---------- Utils ---------- */
    const deb = (fn, ms = 250) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn.apply(null, a), ms); }; };
    const norm = s => (s || '').toString().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
    const parseDate = s => { if (!s) return null; const d = new Date(s + 'T00:00:00'); return isNaN(d) ? null : d; };

    /* ---------- Estado de filtros ---------- */
    const state = { q: '', status: '', sort: 'nombre' };

    /* ---------- Filtro de búsqueda ---------- */
    function onSearch() {
        state.q = norm($('#client_search').val());
        applyFilters();
    }
    $('#client_search').on('input', deb(onSearch, 250));

    /* ---------- Filtro por estado ---------- */
    function onStatusChange() {
        state.status = $('#client_status').val() || '';
        applyFilters();
    }
    $('#client_status').on('change', onStatusChange);

    /* ---------- Orden ---------- */
    function onSortChange() {
        state.sort = $('#client_sort').val() || 'nombre';
        applyFilters();
    }
    $('#client_sort').on('change', onSortChange);

    /* ---------- Aplicar filtros + ordenar ---------- */
    function applyFilters() {
        // 1) Mostrar/ocultar según búsqueda + estado
        const q = state.q;
        const st = state.status;

        $cards.each(function () {
            const $c = $(this);
            const name = norm($c.data('name'));
            const phone = norm($c.data('phone'));
            const email = norm($c.data('email'));
            const stat = ($c.data('status') || '').toString();

            const passSearch = !q || name.includes(q) || phone.includes(q) || email.includes(q);
            const passStatus = !st || stat === st;

            $c.toggle(passSearch && passStatus);
        });

        // 2) Ordenar los visibles
        sortVisible();
    }

    function sortVisible() {
        const cards = $cards.filter(':visible').get();

        cards.sort((a, b) => {
            const $a = $(a), $b = $(b);
            switch (state.sort) {
                case 'saldo_desc': {
                    const ba = parseFloat($a.data('balance')) || 0;
                    const bb = parseFloat($b.data('balance')) || 0;
                    if (bb !== ba) return bb - ba;
                    break;
                }
                case 'prox_venc': {
                    const da = parseDate($a.data('next-due'));
                    const db = parseDate($b.data('next-due'));
                    // nulos al final
                    if (da && !db) return -1;
                    if (!da && db) return 1;
                    if (da && db && da.getTime() !== db.getTime()) return da - db;
                    break;
                }
                default: { // nombre
                    const na = norm($a.data('name'));
                    const nb = norm($b.data('name'));
                    if (na !== nb) return na < nb ? -1 : 1;
                }
            }
            // desempate por data-index o por DOM original
            const ia = parseInt($a.data('index')) || 0;
            const ib = parseInt($b.data('index')) || 0;
            return ia - ib;
        });

        // reinyectar en orden
        $container.append(cards);
    }
}