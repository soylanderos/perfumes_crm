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
            $title = 'Registrar cliente';
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

            $customer = fetch_client_details($db, $customer_id);
            $total_orders = count_client_orders($db, $customer_id);

            $name = $customer['name'];
            $preferred_day = $customer['preferred_day'];
            $phone = $customer['phone'];
            $email = $customer['email'];
            $address = $customer['address'];
            $notes = $customer['notes'];

            $title = 'Editar cliente';
            $request = 'update_client';

            ob_start();
            include '../components/modal/client_details.php';
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
    case 'fetch_client_form':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id');

            $customer = fetch_client_details($db, $customer_id);

            $name = $customer['name'];
            $preferred_day = $customer['preferred_day'];
            $phone = $customer['phone'];
            $email = $customer['email'];
            $address = $customer['address'];
            $notes = $customer['notes'];

            $title = 'Editar cliente';
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
    case 'fetch_client_orders':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id');

            $orders = fetch_client_orders($db, $customer_id);
            $products = fetch_products($db);


            ob_start();
            include '../components/containers/orders_containers.php';
            $content = ob_get_clean();

            echo json_encode(['status' => 'success', 'message' => 'Client orders fetched successfully', 'view' => $content]);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
    case 'create_order':
        try {
            // --- 1) Entrada segura ---
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            $due_date_raw = filter_input(INPUT_POST, 'due_date'); // puede venir vacío
            $notes = filter_input(INPUT_POST, 'notes');

            if (!$customer_id || $customer_id <= 0) {
                throw new Exception('customer_id inválido.');
            }

            if (!isset($_POST['items']) || !is_array($_POST['items']) || count($_POST['items']) === 0) {
                throw new Exception('Debes agregar al menos un producto.');
            }

            // Normaliza due_date (YYYY-MM-DD o null)
            $due_date = null;
            if (!empty($due_date_raw)) {
                $dt = DateTime::createFromFormat('Y-m-d', $due_date_raw);
                if ($dt && $dt->format('Y-m-d') === $due_date_raw) {
                    $due_date = $due_date_raw;
                } else {
                    throw new Exception('Fecha de vencimiento inválida.');
                }
            }

            // (Opcional) user_id desde sesión (si lo manejas)
            $user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

            // --- 2) Recalcular total en servidor y validar items ---
            $items = $_POST['items'];  // items[n][product_id], qty, unit_price
            $order_total = 0.0;

            // Prepara una consulta para validar productos (opcional, pero útil)
            $stCheckProd = $db->prepare("SELECT id, price FROM products WHERE id = :pid AND active = 1");

            // Normaliza cada item
            foreach ($items as $idx => $it) {
                $pid  = isset($it['product_id']) ? (int)$it['product_id'] : 0;
                $qty  = isset($it['qty']) ? (int)$it['qty'] : 0;
                $unit = isset($it['unit_price']) ? (float)$it['unit_price'] : null;

                if ($pid <= 0) throw new Exception("Producto inválido en línea #" . ($idx + 1));
                if ($qty <= 0) throw new Exception("Cantidad inválida en línea #" . ($idx + 1));
                if ($unit === null || $unit < 0) throw new Exception("Precio inválido en línea #" . ($idx + 1));

                // (Opcional) verificar que el producto exista/activo
                $stCheckProd->execute([':pid' => $pid]);
                $prod = $stCheckProd->fetch(PDO::FETCH_ASSOC);
                if (!$prod) throw new Exception("Producto no encontrado/activo en línea #" . ($idx + 1));

                // Suma total (usa el precio editable que viene del form)
                $order_total += $qty * round($unit, 2);

                // Sobrescribe los valores normalizados
                $items[$idx]['product_id'] = $pid;
                $items[$idx]['qty'] = $qty;
                $items[$idx]['unit_price'] = round($unit, 2);
            }

            // (Opcional) compara contra total_amount enviado (tolerancia de 1 centavo)
            $posted_total = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : null;
            if ($posted_total !== null && abs($posted_total - $order_total) > 0.01) {
                // No abortamos; solo registramos por si quieres depurar
                error_log("Aviso: total enviado ($posted_total) difiere del calculado ($order_total). Se usará el calculado.");
            }

            // --- 3) Transacción: insertar orden + items ---
            $db->beginTransaction();

            // Insertar orden
            $sqlOrder = "INSERT INTO orders
            (customer_id, user_id, total_amount, paid_amount, status, created_at, due_date, notes)
            VALUES (:c, :u, :t, 0.00, 'active', NOW(), :d, :n)";
            $stOrder = $db->prepare($sqlOrder);
            $stOrder->execute([
                ':c' => $customer_id,
                ':u' => $user_id,    // puede ser null si no manejas sesión
                ':t' => $order_total,
                ':d' => $due_date,
                ':n' => $notes,
            ]);
            $order_id = (int)$db->lastInsertId();

            // Insertar items
            $sqlItem = "INSERT INTO order_items (order_id, product_id, qty, unit_price, subtotal)
                    VALUES (:o, :p, :q, :u, :s)";
            $stItem = $db->prepare($sqlItem);

            foreach ($items as $it) {
                $subtotal = $it['qty'] * $it['unit_price'];
                $stItem->execute([
                    ':o' => $order_id,
                    ':p' => $it['product_id'],
                    ':q' => $it['qty'],
                    ':u' => $it['unit_price'],
                    ':s' => $subtotal,
                ]);
            }

            $db->commit();

            echo json_encode([
                'status'  => 'success',
                'message' => 'Orden creada correctamente.',
                'order'   => [
                    'id'           => $order_id,
                    'customer_id'  => $customer_id,
                    'total_amount' => number_format($order_total, 2, '.', ''),
                    'paid_amount'  => '0.00',
                    'status'       => 'active',
                    'due_date'     => $due_date,
                    'notes'        => $notes,
                ]
            ]);
        } catch (PDOException | ErrorException | Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? ('Database error occurred: ' . $e->getMessage())
                : 'No se pudo crear la orden.';
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;

    case 'fetch_client_payments':
        try {
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);


            $orders = fetch_client_active_orders($db, $customer_id);
            $recent_payments = fetch_recent_payments($db, $customer_id);

            ob_start();
            include '../components/containers/payments_containers.php';
            $content = ob_get_clean();

            echo json_encode(['status' => 'success', 'message' => 'Recent payments fetched successfully', 'view' => $content]);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
    case 'create_payment':
        try {
            // -------- entrada --------
            $customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
            $order_id    = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            $amount      = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
            $method      = filter_input(INPUT_POST, 'method');
            $note        = filter_input(INPUT_POST, 'note');

            if (!$customer_id || !$order_id || !$amount || $amount <= 0) {
                throw new Exception('Datos de pago inválidos.');
            }

            // (opcional) user actual
            $user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

            // -------- transacción --------
            $db->beginTransaction();

            // 1) insertar pago (sin receipt_path)
            $sql = "INSERT INTO payments (order_id, customer_id, user_id, amount, method, payment_date, note, receipt_path)
                VALUES (:o, :c, :u, :a, :m, NOW(), :n, NULL)";
            $st = $db->prepare($sql);
            $st->execute([
                ':o' => $order_id,
                ':c' => $customer_id,
                ':u' => $user_id,
                ':a' => round((float)$amount, 2),
                ':m' => $method ?: 'otro',
                ':n' => $note
            ]);
            $payment_id = (int)$db->lastInsertId();

            // 2) si viene archivo, validar y mover
            $receiptPath = null;
            if (!empty($_FILES['receipt']) && $_FILES['receipt']['error'] !== UPLOAD_ERR_NO_FILE) {

                if ($_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Error al subir comprobante (código ' . $_FILES['receipt']['error'] . ').');
                }

                $allowedMime = [
                    'application/pdf',
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                    'image/gif',
                    'image/bmp'
                ];
                $type = mime_content_type($_FILES['receipt']['tmp_name']);
                if (!in_array($type, $allowedMime, true)) {
                    throw new Exception('Tipo de archivo no permitido. Solo PDF o imágenes.');
                }

                // Máx 10 MB
                if ($_FILES['receipt']['size'] > 10 * 1024 * 1024) {
                    throw new Exception('El archivo excede 10 MB.');
                }

                // Ruta destino (ajusta a tu estructura)
                $baseDir = "../../../payment_receipts";
                $dir = $baseDir . "/order_$order_id/payment_$payment_id";
                if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
                    throw new Exception('No se pudo crear la carpeta de comprobantes.');
                }

                // Nombre de archivo seguro
                $orig  = $_FILES['receipt']['name'];
                $ext   = pathinfo($orig, PATHINFO_EXTENSION);
                $fname = 'receipt_' . date('Ymd_His') . '.' . strtolower($ext);
                $dest  = $dir . '/' . $fname;

                if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $dest)) {
                    throw new Exception('No se pudo guardar el comprobante en disco.');
                }

                // Ruta pública/relativa para servirlo (ajusta según tu hosting)
                $receiptPath = "payment_receipts/order_$order_id/payment_$payment_id/$fname";

                // 3) actualizar payments con la ruta
                $up = $db->prepare("UPDATE payments SET receipt_path = :p WHERE id = :id");
                $up->execute([':p' => $receiptPath, ':id' => $payment_id]);
            }

            $db->commit();

            echo json_encode([
                'status'  => 'success',
                'message' => 'Pago registrado correctamente.',
                'payment' => [
                    'id'           => $payment_id,
                    'order_id'     => $order_id,
                    'customer_id'  => $customer_id,
                    'amount'       => number_format($amount, 2, '.', ''),
                    'method'       => $method,
                    'note'         => $note,
                    'receipt_path' => $receiptPath
                ]
            ]);
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('create_payment: ' . $e->getMessage());
            $msg = $development_mode ? $e->getMessage() : 'No se pudo registrar el pago.';
            echo json_encode(['status' => 'error', 'message' => $msg]);
        }
        break;
}
