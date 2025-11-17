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

            // 1) Traer todos los clientes con saldo y última fecha de pago
            $customers = fetch_all_clients($db);

            // 2) Renderizar vista
            $content = '';
            ob_start();
            include '../clients.php';   // aquí se usa $customers
            $content = ob_get_clean();

            echo json_encode([
                'status'  => 'success',
                'message' => 'Clients fetched successfully',
                'view'    => $content
            ]);
        } catch (Throwable $e) { // PDOException | ErrorException | Exception
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode
                ? 'Database error occurred: ' . $e->getMessage()
                : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message
            ]);
        }
        break;

    case 'fetch_client_profile':
        try {

            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            if (!$customer_id) {
                throw new Exception('ID de cliente inválido');
            }

            $customer = fetch_customer_by_id($db, $customer_id);
            if (!$customer) {
                throw new Exception('Cliente no encontrado');
            }

            $sales_stats     = fetch_customer_sales_stats($db, $customer_id);
            $payments_stats  = fetch_customer_payments_stats($db, $customer_id);
            $recent_sales    = fetch_customer_recent_sales($db, $customer_id, 5);
            $recent_payments = fetch_customer_recent_payments($db, $customer_id, 5);
            $movements       = fetch_customer_recent_movements($db, $customer_id, 10);

            ob_start();
            include '../components/modal/client_profile_modal.php'; // vista del modal
            $content = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'view'   => $content,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_client_profile: ' . $e->getMessage());
            $message = $development_mode
                ? 'Error: ' . $e->getMessage()
                : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

    case 'fetch_new_sale_modal':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            if (!$customer_id) {
                throw new Exception('Cliente inválido.');
            }

            $available_items = fetch_available_purchase_order_items($db);

            ob_start();
            include '../components/modal/client_new_sale_modal.php';
            $html = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'view'   => $html,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_new_sale_modal: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

    case 'create_client_sale':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            $sale_date   = filter_input(INPUT_POST, 'sale_date');
            $notes       = filter_input(INPUT_POST, 'notes');

            $items_json = filter_input(INPUT_POST, 'items');
            $items = json_decode($items_json, true);

            if (!$customer_id) {
                throw new Exception('Cliente inválido.');
            }
            if (!is_array($items) || empty($items)) {
                throw new Exception('Debes seleccionar al menos un producto.');
            }

            $sale_id = create_client_sale_with_items($db, $customer_id, $sale_date, $notes, $items);

            echo json_encode([
                'status'  => 'success',
                'message' => 'Compra registrada correctamente.',
                'sale_id' => $sale_id,
            ]);
        } catch (Throwable $e) {
            error_log('Error create_client_sale: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

    case 'fetch_new_payment_modal':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            if (!$customer_id) {
                throw new Exception('Cliente inválido.');
            }

            // Reusar la función que ya tienes
            $customer = fetch_customer_by_id($db, $customer_id);
            if (!$customer) {
                throw new Exception('Cliente no encontrado.');
            }

            ob_start();
            include '../components/modal/client_new_payment_modal.php';
            $html = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'view'   => $html,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_new_payment_modal: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

    case 'create_client_payment':
        try {
            $customer_id  = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            $payment_date = filter_input(INPUT_POST, 'payment_date');
            $amount       = (float)filter_input(INPUT_POST, 'amount');
            $method       = filter_input(INPUT_POST, 'method');
            $notes        = filter_input(INPUT_POST, 'notes');

            if (!$customer_id) {
                throw new Exception('Cliente inválido.');
            }
            if ($amount <= 0) {
                throw new Exception('El monto del pago debe ser mayor a cero.');
            }

            // =============================
            // Manejo de comprobante (archivo)
            // =============================
            $receipt_path = null;

            if (!empty($_FILES['payment_receipt']['name'])) {
                $file      = $_FILES['payment_receipt'];
                $error     = $file['error'] ?? UPLOAD_ERR_NO_FILE;

                if ($error === UPLOAD_ERR_OK) {
                    $tmpName = $file['tmp_name'];
                    $origName = $file['name'];
                    $size = (int)$file['size'];

                    // Validar tamaño (ej. máx 5MB)
                    if ($size > 5 * 1024 * 1024) {
                        throw new Exception('El comprobante es demasiado pesado (máximo 5MB).');
                    }

                    // Validar extensión / tipo básico
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                    if (!in_array($ext, $allowed_ext, true)) {
                        throw new Exception('Formato de comprobante no permitido. Usa imagen o PDF.');
                    }

                    // Carpeta de destino (ajusta ruta si hace falta)
                    $upload_dir_fs  = __DIR__ . '/../../../uploads/payment_receipts/';
                    $upload_dir_web = 'uploads/payment_receipts/';

                    if (!is_dir($upload_dir_fs)) {
                        mkdir($upload_dir_fs, 0775, true);
                    }

                    $uniqueName = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $target_fs  = $upload_dir_fs . $uniqueName;
                    $target_web = $upload_dir_web . $uniqueName;

                    if (!move_uploaded_file($tmpName, $target_fs)) {
                        throw new Exception('No se pudo guardar el comprobante en el servidor.');
                    }

                    $receipt_path = $target_web;
                } elseif ($error !== UPLOAD_ERR_NO_FILE) {
                    throw new Exception('Error al subir el comprobante de pago.');
                }
            }

            $payment_id = create_client_payment(
                $db,
                $customer_id,
                $payment_date,
                $amount,
                $method,
                $notes,
                $receipt_path
            );

            echo json_encode([
                'status'      => 'success',
                'message'     => 'Pago registrado correctamente.',
                'payment_id'  => $payment_id,
            ]);
        } catch (Throwable $e) {
            error_log('Error create_client_payment: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

    case 'fetch_payment_detail':
        try {
            $payment_id = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
            if (!$payment_id) {
                throw new Exception('Pago inválido.');
            }

            $payment = fetch_payment_by_id($db, $payment_id);
            if (!$payment) {
                throw new Exception('Pago no encontrado.');
            }

            ob_start();
            include '../components/modal/client_payment_detail_modal.php';
            $html = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'view'   => $html,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_payment_detail: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;
    case 'fetch_new_client_modal':
        try {
            $mode = 'create';
            ob_start();
            include '../components/modal/client_form_modal.php';
            $html = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'view'   => $html,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_new_client_modal: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
    case 'fetch_edit_client_modal':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            if (!$customer_id) {
                throw new Exception('Cliente inválido.');
            }

            $customer = fetch_customer_by_id($db, $customer_id);
            if (!$customer) {
                throw new Exception('Cliente no encontrado.');
            }

            $mode = 'edit';

            ob_start();
            include '../components/modal/client_form_modal.php';
            $html = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'view'   => $html,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_edit_client_modal: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
    case 'create_client':
        try {
            $name   = trim((string)filter_input(INPUT_POST, 'name'));
            $phone  = trim((string)filter_input(INPUT_POST, 'phone'));
            $email  = trim((string)filter_input(INPUT_POST, 'email'));
            $status = filter_input(INPUT_POST, 'status') ?: 'active';

            if ($name === '') {
                throw new Exception('El nombre del cliente es obligatorio.');
            }

            $customer_id = create_customer($db, $name, $phone, $email, $status);

            echo json_encode([
                'status'       => 'success',
                'message'      => 'Cliente creado correctamente.',
                'customer_id'  => $customer_id,
            ]);
        } catch (Throwable $e) {
            error_log('Error create_client: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
    case 'update_client':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            $name        = trim((string)filter_input(INPUT_POST, 'name'));
            $phone       = trim((string)filter_input(INPUT_POST, 'phone'));
            $email       = trim((string)filter_input(INPUT_POST, 'email'));
            $status      = filter_input(INPUT_POST, 'status') ?: 'active';

            if (!$customer_id) {
                throw new Exception('Cliente inválido.');
            }
            if ($name === '') {
                throw new Exception('El nombre del cliente es obligatorio.');
            }

            update_customer($db, $customer_id, $name, $phone, $email, $status);

            echo json_encode([
                'status'      => 'success',
                'message'     => 'Cliente actualizado correctamente.',
                'customer_id' => $customer_id,
            ]);
        } catch (Throwable $e) {
            error_log('Error update_client: ' . $e->getMessage());
            $message = $development_mode ? $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;




    default:
        echo json_encode([
            'status'  => 'error',
            'message' => 'Solicitud no válida.'
        ]);
        break;
}
