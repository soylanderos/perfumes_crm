<?php
session_start();
$session_user_id = $_SESSION["id"];
$user_role = $_SESSION["role"];

include '../../../utilities/db_conn.php';
$db = new PDO($dsn, $username, $password);
require "../model/clients_queries.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_request = filter_input(INPUT_POST, 'user_request');
} else {
    $user_request = filter_input(INPUT_GET, 'user_request');
}

switch ($user_request) {
    case 'fetch_clients':
        try {
            $clients = fetch_all_clients($db);

            ob_start();
            include '../clients.php';
            $content = ob_get_clean();

            echo json_encode(['status' => 'success', 'message' => 'Songs fetched successfully', 'view' => $content]);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;

    case 'fetch_add_client_form':
        try {
            $request = 'create_client';
            ob_start();
            include '../components/modal/client_form.php';
            $content = ob_get_clean();

            echo json_encode(['status' => 'success', 'message' => 'Form fetched successfully', 'view' => $content]);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;

    case 'create_client':
        try {
            $db->beginTransaction();

            $name = filter_input(INPUT_POST, 'name');
            $preferred_day = filter_input(INPUT_POST, 'preferred_day', FILTER_SANITIZE_NUMBER_INT);
            $phone = filter_input(INPUT_POST, 'phone');
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $address = filter_input(INPUT_POST, 'address');
            $notes = filter_input(INPUT_POST, 'notes');

            create_client($db, $name, $preferred_day, $phone, $email, $address, $notes);

            $db->commit();

            echo json_encode(['status' => 'success', 'message' => 'Cliente creado correctamente.']);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;

    case 'fetch_client_details':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id');

            $client_details = fetch_client_details($db, $customer_id);

            $name = $client_details['name'];
            $preferred_day = $client_details['preferred_day'];
            $phone = $client_details['phone'];
            $email = $client_details['email'];
            $address = $client_details['address'];
            $notes = $client_details['notes'];


            $request = 'update_client';

            ob_start();
            include '../components/modal/client_form.php';
            $content = ob_get_clean();

            echo json_encode(['status' => 'success', 'message' => 'Client details fetched successfully', 'view' => $content]);

        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;

        case 'update_client':
            try {
                $db->beginTransaction();

                $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_SANITIZE_NUMBER_INT);
                $name = filter_input(INPUT_POST, 'name');
                $preferred_day = filter_input(INPUT_POST, 'preferred_day', FILTER_SANITIZE_NUMBER_INT);
                $phone = filter_input(INPUT_POST, 'phone');
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                $address = filter_input(INPUT_POST, 'address');
                $notes = filter_input(INPUT_POST, 'notes');

                // Aquí iría la función para actualizar el cliente (a implementar)
                update_client($db, $customer_id, $name, $preferred_day, $phone, $email, $address, $notes);

                $db->commit();

                echo json_encode(['status' => 'success', 'message' => 'Cliente actualizado correctamente.']);
            } catch (PDOException | ErrorException | Exception $e) {
                error_log('Database Error: ' . $e->getMessage());
                $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
                echo json_encode(['status' => 'error', 'message' => $message]);
            }
            break;
}
