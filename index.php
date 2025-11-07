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

    <title>CRM Pay</title>
</head>

<body data-page="index" data-user-role="<?= $user_role ?>">
    <?php include 'shared/components/sidebar.php' ?>

    <div class="main-container ms-md-5 ps-md-5 mt-5 pt-3 mt-md-0 pt-md-0" id="app-content">

    </div>

    <div id="modal_container"></div>
    <script src="shared/script-loader.js"></script>

    <div id="modal-container"></div>
</body>

</html>