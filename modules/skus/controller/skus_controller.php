<?php
session_start();
$session_user_id = $_SESSION["id"];
$user_role = $_SESSION["role"];
require "../model/skus_queries.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_request = filter_input(INPUT_POST, 'user_request');
} else {
    $user_request = filter_input(INPUT_GET, 'user_request');
}

include '../../../utilities/db_conn.php';
$db = new PDO($dsn, $username, $password);

switch ($user_request) {
    case 'fetch_skus':
        try {
            // productos activos (ajusta tu PDO $db)
            $skus = fetch_products($db);


            ob_start();
            include '../skus.php';
            $content = ob_get_clean();

            echo json_encode(['status' => 'success', 'message' => 'SKUs fetched successfully', 'view' => $content]);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
}
