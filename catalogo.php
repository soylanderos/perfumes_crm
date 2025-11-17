<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="assets/img/crm_icon.png">
    <!-- ✅ Carga crítica para evitar que se vea desordenado -->
    <link href="utilities/styles/styles.css" rel="stylesheet">

    <link href="utilities/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <!-- ⚙️ El resto lo carga dinámicamente -->
    <script src="shared/style-loader.js"></script>

    <!-- PWA -->
    <meta name="theme-color" content="#78ffd6">
    <meta name="MobileOptimized" content="width">
    <meta name="HandheldFriendly" content="true">
    <meta name="theme-color" content="#78ffd6">
    <link rel="manifest" href="./manifest.json" />

    <title>Catalogo</title>
</head>

<body data-page="index">
    <div class="main-container ms-lg-5 ps-lg-5 mt-5 pt-3 mt-md-0 pt-md-0" id="app-content">

    </div>

    <div id="modal_container"></div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="modules/products/event/products_event_controller.js"></script>

    <div id="modal-container"></div>
</body>

<script>
    $(document).ready(function() {
        // Cargar contenido inicial
       $.post('modules/products/controller/products_controller.php', { user_request: 'fetch_products' }, function(response) {
            if (response.status === 'success') {
                $('#app-content').html(response.view);
            } else {
                $('#app-content').html('<div class="alert alert-danger">Error al cargar el catálogo de productos.</div>');
            }
        }, 'json');
    });
</script>

</html>