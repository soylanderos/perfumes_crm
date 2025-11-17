<?php
session_start();
ini_set('display_errors', 0);

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$session_user_id = $_SESSION["id"];
$user_role = $_SESSION["role"];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="icon" type="image/x-icon" href="assets/img/crm_icon.png">

    <!-- Estilos base -->
    <link href="utilities/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link href="utilities/styles/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Carga dinámica extra -->
    <script src="shared/style-loader.js"></script>

    <!-- PWA -->
    <meta name="theme-color" content="#78ffd6">
    <meta name="MobileOptimized" content="width">
    <meta name="HandheldFriendly" content="true">
    <link rel="manifest" href="./manifest.json" />

    <title>CRM Pay</title>
</head>

<body data-page="index" data-user-role="<?= htmlspecialchars($user_role) ?>">

    <!-- Shell principal -->
    <main class="app-shell">
        <div class="main-container" id="app_content">
            <!-- Aquí se inyectan dashboard, clientes, etc. vía AJAX -->
        </div>
    </main>

    <!-- Barra de navegación flotante (antes estaba tu sidebar.php) -->
    <?php include 'shared/components/floating-nav.php'; ?>

    <!-- Modales -->
    <div id="modal_container"></div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js" integrity="sha512-2rNj2KJ+D8s1ceNasTIex6z4HWyOnEYLVC3FigGOmyQCZc2eBXKgOxQmo3oKLHyfcj53uz4QMsRCWNbLd32Q1g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="shared/script-loader.js"></script>
</body>
</html>
