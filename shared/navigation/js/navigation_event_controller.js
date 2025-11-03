$(function () {

    $(document).on('click', '[data-section]', function() {
        if($('.modal').length){
            $('.modal').modal('hide').remove();
            $('.modal-backdrop').remove();
        }
    });

    $(document).on('hidden.bs.modal', '.modal', function() {
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
        const controllerUrl = 'modules/'  + controller + '/controller/' + controller +  '_controller.php';

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




