<?php
session_start();
$session_user_id = $_SESSION["id"];
$church_id = $_SESSION["church_id"];
$user_role = $_SESSION["role"];
require '../../admin/model/admin_queries.php';
require "../model/dashboard_queries.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_request = filter_input(INPUT_POST, 'user_request');
} else {
    $user_request = filter_input(INPUT_GET, 'user_request');
}

switch ($user_request) {
    case 'fetch_dashboard':
        try {
            include '../../../utilities/db_conn.php';
            $db = new PDO($dsn, $username, $password);

            ob_start();
            include '../dashboard.php';
            $content = ob_get_clean();  
        
            echo json_encode(['status' => 'success', 'message' => 'Songs fetched successfully', 'view' => $content]);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
}