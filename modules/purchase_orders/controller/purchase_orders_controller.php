<?php
session_start();
$session_user_id = $_SESSION["id"];
$user_role = $_SESSION["role"];

include '../../../utilities/db_conn.php';
$db = new PDO($dsn, $username, $password);
require "../model/purchase_orders_queries.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_request = filter_input(INPUT_POST, 'user_request');
} else {
    $user_request = filter_input(INPUT_GET, 'user_request');
}



switch ($user_request) {

    /**
     * Cargar módulo de pedidos completo (lista izquierda + panel derecho vacío)
     */
    case 'fetch_orders':
        try {
            $purchase_orders = fetch_purchase_orders_summary($db);

            ob_start();
            include '../purchase_orders.php';      // la vista que ya hicimos
            $content = ob_get_clean();

            echo json_encode([
                'status' => 'success',
                'view'   => $content,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_orders: ' . $e->getMessage());
            $message = $development_mode
                ? 'Error: ' . $e->getMessage()
                : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

    /**
     * Cargar detalle de un pedido específico (header + items)
     */
    case 'fetch_order_detail':
        try {
            $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
            if (!$order_id) {
                throw new Exception('ID de pedido inválido');
            }

            $order  = fetch_purchase_order_header($db, $order_id);
            if (!$order) {
                throw new Exception('Pedido no encontrado');
            }

            $items  = fetch_purchase_order_items($db, $order_id);

            // ========= Armar info para header =========
            $title       = $order['title'] ?: ('Pedido #' . $order['id']);
            $order_date  = $order['order_date'] ? date('d/m/Y', strtotime($order['order_date'])) : 'Sin fecha';
            $total_items = (int)$order['total_items'];
            $total_units = (int)$order['total_units'];
            $used_units  = (int)$order['used_units'];
            $remaining   = max($total_units - $used_units, 0);

            $status      = $order['status'] ?? 'open';

            $status_label = [
                'open'    => 'Abierto',
                'partial' => 'En uso',
                'closed'  => 'Cerrado',
            ][$status] ?? 'Abierto';

            $status_class = [
                'open'    => 'orders-badge-open',
                'partial' => 'orders-badge-partial',
                'closed'  => 'orders-badge-closed',
            ][$status] ?? 'orders-badge-open';

            $meta_text = sprintf(
                'Creado el %s · %d productos · %d unidades ( %d en uso, %d disponibles )',
                $order_date,
                $total_items,
                $total_units,
                $used_units,
                $remaining
            );

            // ========= Armar HTML de los items =========
            ob_start();

            if (!empty($items)) {
                foreach ($items as $item) {
                    $item_id      = (int)$item['id'];
                    $product_name = $item['product_name'];
                    $qty          = (int)$item['quantity'];
                    $qty_used     = (int)$item['quantity_used'];
                    $qty_avail    = max((int)$item['quantity_available'], 0);
                    $unit_cost    = $item['unit_cost'];
                    $is_acquired  = (int)($item['is_acquired'] ?? 0) === 1;
?>
                    <div class="orders-item-row"
                        data-order-item-id="<?= htmlspecialchars($item_id) ?>"
                        data-qty="<?= htmlspecialchars($qty) ?>"
                        data-qty-used="<?= htmlspecialchars($qty_used) ?>"
                        data-qty-available="<?= htmlspecialchars($qty_avail) ?>">

                        <div class="orders-item-main">
                            <button type="button"
                                class="orders-item-check <?= $is_acquired ? 'checked' : '' ?>">
                                <?php if ($is_acquired): ?>
                                    <i class="bi bi-check-lg"></i>
                                <?php endif; ?>
                            </button>

                            <div class="orders-item-info">
                                <div class="orders-item-title text-truncate">
                                    <?= htmlspecialchars($product_name) ?>
                                </div>
                                <div class="orders-item-meta">
                                    Pedido: <?= $qty ?> u
                                    <?php if ($qty_used > 0): ?>
                                        · Usado: <?= $qty_used ?> u
                                    <?php endif; ?>
                                    <?php if (!is_null($unit_cost)): ?>
                                        · Costo aprox: $<?= number_format((float)$unit_cost, 2) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="orders-item-qty">
                            <?= $qty_avail ?> disp.
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <p class="small text-muted mb-0">
                    Este pedido aún no tiene productos agregados.
                </p>
<?php
            }

            $items_html = ob_get_clean();

            echo json_encode([
                'status'        => 'success',
                'order_id'      => (int)$order['id'],   // 👈 NUEVO
                'order_title'   => $title,
                'meta_text'     => $meta_text,
                'status_label'  => $status_label,
                'status_class'  => $status_class,
                'items_html'    => $items_html,
            ]);
        } catch (Throwable $e) {
            error_log('Error fetch_order_detail: ' . $e->getMessage());
            $message = $development_mode
                ? 'Error: ' . $e->getMessage()
                : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

    /**
     * Crear nueva lista de pedidos con sus items
     */
    case 'create_purchase_order':
        try {
            $title      = filter_input(INPUT_POST, 'title');
            $order_date = filter_input(INPUT_POST, 'order_date'); // puede venir vacío
            $notes      = filter_input(INPUT_POST, 'notes');

            $items_json = filter_input(INPUT_POST, 'items');
            $items      = json_decode($items_json, true);

            if (!is_array($items)) {
                throw new Exception('Formato de items inválido.');
            }

            $order_data = [
                'title'      => $title,
                'order_date' => $order_date,
                'notes'      => $notes,
            ];

            $order_id = create_purchase_order_with_items($db, $order_data, $items);

            echo json_encode([
                'status'   => 'success',
                'message'  => 'Lista de pedidos creada correctamente.',
                'order_id' => $order_id,
            ]);
        } catch (Throwable $e) {
            error_log('Error create_purchase_order: ' . $e->getMessage());
            $message = $development_mode
                ? 'Error: ' . $e->getMessage()
                : $user_message;

            echo json_encode([
                'status'  => 'error',
                'message' => $message,
            ]);
        }
        break;

        case 'toggle_order_item_acquired':
    try {
        $item_id     = filter_input(INPUT_POST, 'order_item_id', FILTER_VALIDATE_INT);
        $is_acquired = filter_input(INPUT_POST, 'is_acquired', FILTER_VALIDATE_INT);
        $order_id    = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

        if (!$item_id || $is_acquired === null || !$order_id) {
            throw new Exception('Datos inválidos para actualizar el item.');
        }

        $bool_acquired = $is_acquired == 1;

        update_order_item_acquired($db, $item_id, $bool_acquired);
        $new_status = recalc_purchase_order_status($db, $order_id);

        // map de classes para actualizar badge
        $status_label = [
            'open'    => 'Abierto',
            'partial' => 'En uso',
            'closed'  => 'Cerrado',
        ][$new_status] ?? 'Abierto';

        $status_class = [
            'open'    => 'orders-badge-open',
            'partial' => 'orders-badge-partial',
            'closed'  => 'orders-badge-closed',
        ][$new_status] ?? 'orders-badge-open';

        echo json_encode([
            'status'        => 'success',
            'order_status'  => $new_status,
            'status_label'  => $status_label,
            'status_class'  => $status_class,
        ]);
    } catch (Throwable $e) {
        error_log('Error toggle_order_item_acquired: ' . $e->getMessage());
        $message = $development_mode ? $e->getMessage() : $user_message;

        echo json_encode([
            'status'  => 'error',
            'message' => $message,
        ]);
    }
    break;

    case 'add_order_items':
    try {
        $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $items_json = filter_input(INPUT_POST, 'items');
        $items = json_decode($items_json, true);

        if (!$order_id) {
            throw new Exception('ID de pedido inválido.');
        }
        if (!is_array($items)) {
            throw new Exception('Formato de productos inválido.');
        }

        add_items_to_purchase_order($db, $order_id, $items);
        // recalcula status por si venían marcados como adquiridos luego (por ahora no)
        $new_status = recalc_purchase_order_status($db, $order_id);

        echo json_encode([
            'status'       => 'success',
            'message'      => 'Productos agregados correctamente.',
            'order_status' => $new_status,
        ]);

    } catch (Throwable $e) {
        error_log('Error add_order_items: ' . $e->getMessage());
        $message = $development_mode ? $e->getMessage() : $user_message;

        echo json_encode([
            'status'  => 'error',
            'message' => $message,
        ]);
    }
    break;



    default:
        echo json_encode([
            'status'  => 'error',
            'message' => 'Acción no soportada en orders_controller.',
        ]);
        break;
}
