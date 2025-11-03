<?php
session_start();
require "../model/login_queries.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_request = filter_input(INPUT_POST, 'user_request');
} else {
    $user_request = filter_input(INPUT_GET, 'user_request');
}

switch ($user_request) {
    case 'verify_login':
        try {
            include '../../../utilities/db_conn.php';
            $db = new PDO($dsn, $username, $password);

            $email = filter_input(INPUT_POST, 'email');
            $password = filter_input(INPUT_POST, 'password');

            if (!verify_login($db, $email, $password)) {
                echo json_encode(['status' => 'error', 'message' => 'Email or Password Incorrect']);
                break;  // Exit early if login fails
            } else {
                $user_data = fetch_users($db, $email);

                $_SESSION["id"] = $user_data['id'];
                $_SESSION["username"] = $user_data['username'];
                $_SESSION["role"] = $user_data['role'];
                $_SESSION["full_name"] = $user_data['full_name'];
                $_SESSION["email"] = $user_data['email'];

                ob_start();
                include '../components/redirect_user_script.php';
                $content = ob_get_clean();
                echo json_encode(['status' => 'success', 'message' => 'Login Successfully', 'view' => $content]);
            }
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'logout':
        try {
            session_unset();
            session_destroy();
            echo json_encode(['status' => 'success', 'message' => 'Logout Successfully']);
        } catch (Exception $e) {
            error_log('Logout Error: ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Logout failed']);
        }
        break;
}
