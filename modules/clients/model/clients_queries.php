<?php
function fetch_all_clients(PDO $db): array
{
    $sql = "
        SELECT 
            c.id,
            c.name,
            c.phone,
            c.email,
            c.status,
            COALESCE(b.balance, 0) AS balance,
            b.last_payment_date
        FROM customers c
        LEFT JOIN (
            SELECT 
                customer_id,
                SUM(
                    CASE 
                        WHEN type = 'charge'    THEN amount
                        WHEN type = 'payment'   THEN -amount
                        WHEN type = 'adjustment' THEN amount
                        ELSE 0
                    END
                ) AS balance,
                MAX(
                    CASE 
                        WHEN type = 'payment' THEN DATE(movement_date)
                        ELSE NULL
                    END
                ) AS last_payment_date
            FROM customer_movements
            GROUP BY customer_id
        ) b ON b.customer_id = c.id
        ORDER BY c.name ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetch_customer_by_id(PDO $db, int $customer_id): ?array
{
    $sql = "
        SELECT 
            c.id,
            c.name,
            c.phone,
            c.email,
            c.status,
            c.created_at,
            COALESCE(b.balance, 0) AS balance,
            b.last_payment_date
        FROM customers c
        LEFT JOIN (
            SELECT 
                customer_id,
                SUM(
                    CASE 
                        WHEN type = 'charge'    THEN amount
                        WHEN type = 'payment'   THEN -amount
                        WHEN type = 'adjustment'then amount
                        ELSE 0
                    END
                ) AS balance,
                MAX(
                    CASE WHEN type = 'payment' THEN DATE(movement_date) END
                ) AS last_payment_date
            FROM customer_movements
            GROUP BY customer_id
        ) b ON b.customer_id = c.id
        WHERE c.id = :customer_id
        LIMIT 1
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':customer_id' => $customer_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function fetch_customer_sales_stats(PDO $db, int $customer_id): array
{
    $sql = "
        SELECT 
            COUNT(*) AS total_sales,
            COALESCE(SUM(total_amount), 0) AS total_sales_amount,
            MAX(sale_date) AS last_sale_date
        FROM customer_sales
        WHERE customer_id = :customer_id
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':customer_id' => $customer_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'total_sales'        => 0,
        'total_sales_amount' => 0,
        'last_sale_date'     => null,
    ];
}

function fetch_customer_payments_stats(PDO $db, int $customer_id): array
{
    $sql = "
        SELECT 
            COUNT(*) AS total_payments,
            COALESCE(SUM(amount), 0) AS total_paid,
            MAX(payment_date) AS last_payment_date
        FROM payments
        WHERE customer_id = :customer_id
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':customer_id' => $customer_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
        'total_payments' => 0,
        'total_paid'     => 0,
        'last_payment_date' => null,
    ];
}

function fetch_customer_recent_sales(PDO $db, int $customer_id, int $limit = 5): array
{
    $sql = "
        SELECT 
            cs.id,
            cs.sale_date,
            cs.total_amount,
            COUNT(csi.id) AS items_count
        FROM customer_sales cs
        LEFT JOIN customer_sale_items csi ON csi.sale_id = cs.id
        WHERE cs.customer_id = :customer_id
        GROUP BY cs.id, cs.sale_date, cs.total_amount
        ORDER BY cs.sale_date DESC
        LIMIT :limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetch_customer_recent_payments(PDO $db, int $customer_id, int $limit = 5): array
{
    $sql = "
        SELECT 
            id,
            payment_date,
            amount,
            method,
            notes,
            receipt_path
        FROM payments
        WHERE customer_id = :customer_id
        ORDER BY payment_date DESC, id DESC
        LIMIT :limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetch_customer_recent_movements(PDO $db, int $customer_id, int $limit = 10): array
{
    $sql = "
        SELECT 
            m.type,
            m.movement_date,
            m.amount,
            m.balance_after,
            m.notes,
            m.related_sale_id,
            m.related_payment_id,
            cs.sale_date      AS sale_date,
            cs.total_amount   AS sale_total_amount,
            p.payment_date    AS payment_date,
            p.method          AS payment_method
        FROM customer_movements m
        LEFT JOIN customer_sales cs
            ON cs.id = m.related_sale_id
        LEFT JOIN payments p
            ON p.id = m.related_payment_id
        WHERE m.customer_id = :customer_id
        ORDER BY m.movement_date DESC, m.id DESC
        LIMIT :limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':customer_id', $customer_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



/**
 * Trae todos los items de purchase_orders que aún tienen unidades disponibles
 * para usarse en compras de clientes.
 */
function fetch_available_purchase_order_items(PDO $db): array
{
    $sql = "
        SELECT
            poi.id,
            poi.purchase_order_id,
            poi.product_name,
            poi.quantity,
            COALESCE(poi.quantity_used, 0) AS quantity_used,
            (poi.quantity - COALESCE(poi.quantity_used, 0)) AS quantity_available,
            poi.unit_cost,
            po.title AS order_title,
            po.order_date
        FROM purchase_order_items poi
        INNER JOIN purchase_orders po
            ON po.id = poi.purchase_order_id
        WHERE (poi.quantity - COALESCE(poi.quantity_used, 0)) > 0
        ORDER BY po.order_date DESC, po.id DESC, poi.id ASC
    ";

    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Crea una venta para un cliente, ligando items a purchase_order_items,
 * actualiza quantity_used, el saldo del cliente y registra un movimiento.
 *
 * $items: [
 *   ['purchase_order_item_id' => int, 'quantity' => int, 'unit_price' => float],
 *   ...
 * ]
 */
function create_client_sale_with_items(PDO $db, int $customer_id, ?string $sale_date, ?string $notes, array $items): int
{
    if (empty($items)) {
        throw new Exception('No se puede crear una compra sin productos.');
    }

    if (!$sale_date) {
        $sale_date = date('Y-m-d');
    }

    try {
        $db->beginTransaction();

        // Validar disponibilidad y calcular total
        $total_amount = 0.0;

        $sqlItemCheck = "
            SELECT 
                id,
                quantity,
                COALESCE(quantity_used, 0) AS quantity_used
            FROM purchase_order_items
            WHERE id = :id
            FOR UPDATE
        ";
        $stmtCheck = $db->prepare($sqlItemCheck);

        foreach ($items as $item) {
            $poi_id = (int)($item['purchase_order_item_id'] ?? 0);
            $qty    = (int)($item['quantity'] ?? 0);
            $price  = (float)($item['unit_price'] ?? 0);

            if ($poi_id <= 0 || $qty <= 0 || $price <= 0) {
                throw new Exception('Producto, cantidad o precio inválido.');
            }

            $stmtCheck->execute([':id' => $poi_id]);
            $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new Exception("Item de pedido no encontrado (ID {$poi_id}).");
            }

            $available = (int)$row['quantity'] - (int)$row['quantity_used'];
            if ($qty > $available) {
                throw new Exception("No hay suficientes unidades disponibles para el item {$poi_id}.");
            }

            $total_amount += $qty * $price;
        }

        // Insertar venta
        $sqlSale = "
            INSERT INTO customer_sales
                (customer_id, sale_date, total_amount, notes, created_at, updated_at)
            VALUES
                (:customer_id, :sale_date, :total_amount, :notes, NOW(), NOW())
        ";
        $stmtSale = $db->prepare($sqlSale);
        $stmtSale->execute([
            ':customer_id'  => $customer_id,
            ':sale_date'    => $sale_date,
            ':total_amount' => $total_amount,
            ':notes'        => $notes,
        ]);
        $sale_id = (int)$db->lastInsertId();

        // Insertar items y actualizar quantity_used
        $sqlSaleItem = "
            INSERT INTO customer_sale_items
                (sale_id, purchase_order_item_id, quantity, unit_price, created_at, updated_at)
            VALUES
                (:sale_id, :poi_id, :qty, :price, NOW(), NOW())
        ";
        $stmtSaleItem = $db->prepare($sqlSaleItem);

        $sqlUpdatePoi = "
            UPDATE purchase_order_items
            SET quantity_used = quantity_used + :qty, updated_at = NOW()
            WHERE id = :poi_id
        ";
        $stmtUpdatePoi = $db->prepare($sqlUpdatePoi);

        foreach ($items as $item) {
            $poi_id = (int)$item['purchase_order_item_id'];
            $qty    = (int)$item['quantity'];
            $price  = (float)$item['unit_price'];

            $stmtSaleItem->execute([
                ':sale_id' => $sale_id,
                ':poi_id'  => $poi_id,
                ':qty'     => $qty,
                ':price'   => $price,
            ]);

            $stmtUpdatePoi->execute([
                ':poi_id' => $poi_id,
                ':qty'    => $qty,
            ]);
        }

        // Actualizar saldo del cliente y registrar movimiento
        $sqlCustomer = "SELECT balance FROM customers WHERE id = :cid FOR UPDATE";
        $stmtCustomer = $db->prepare($sqlCustomer);
        $stmtCustomer->execute([':cid' => $customer_id]);
        $customerRow = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

        $old_balance = (float)($customerRow['balance'] ?? 0);
        $new_balance = $old_balance + $total_amount;

        $sqlUpdateCustomer = "
            UPDATE customers
            SET balance = :new_balance, updated_at = NOW()
            WHERE id = :cid
        ";
        $stmtUpdateCustomer = $db->prepare($sqlUpdateCustomer);
        $stmtUpdateCustomer->execute([
            ':new_balance' => $new_balance,
            ':cid'         => $customer_id,
        ]);

        $sqlMovement = "
            INSERT INTO customer_movements
                (customer_id, type, amount, movement_date, notes, balance_after, related_sale_id, created_at)
            VALUES
                (:customer_id, 'charge', :amount, NOW(), :notes, :balance_after, :sale_id, NOW())
        ";
        $stmtMovement = $db->prepare($sqlMovement);
        $stmtMovement->execute([
            ':customer_id'  => $customer_id,
            ':amount'       => $total_amount,
            ':notes'        => $notes,
            ':balance_after' => $new_balance,
            ':sale_id'      => $sale_id,
        ]);

        $db->commit();
        return $sale_id;
    } catch (Throwable $e) {
        $db->rollBack();
        throw new Exception('Error al registrar la compra: ' . $e->getMessage());
    }
}

/**
 * Registra un pago de un cliente:
 * - Inserta en payments
 * - Actualiza balance en customers
 * - Inserta un movimiento type = 'payment'
 *
 * Retorna el id del pago.
 */
function create_client_payment(
    PDO $db,
    int $customer_id,
    ?string $payment_date,
    float $amount,
    ?string $method,
    ?string $notes,
    ?string $receipt_path = null
): int {
    if ($amount <= 0) {
        throw new Exception('El monto del pago debe ser mayor a cero.');
    }

    if (!$payment_date) {
        $payment_date = date('Y-m-d');
    }

    $method = $method ?: 'Efectivo';

    try {
        $db->beginTransaction();

        // 1) Insertar pago
        $sqlPayment = "
            INSERT INTO payments
                (customer_id, payment_date, amount, method, notes, receipt_path)
            VALUES
                (:customer_id, :payment_date, :amount, :method, :notes, :receipt_path)
        ";
        $stmtPay = $db->prepare($sqlPayment);
        $stmtPay->execute([
            ':customer_id'   => $customer_id,
            ':payment_date'  => $payment_date,
            ':amount'        => $amount,
            ':method'        => $method,
            ':notes'         => $notes,
            ':receipt_path'  => $receipt_path,
        ]);
        $payment_id = (int)$db->lastInsertId();

        // 2) Saldo del cliente
        $sqlCustomer = "SELECT balance FROM customers WHERE id = :cid FOR UPDATE";
        $stmtCustomer = $db->prepare($sqlCustomer);
        $stmtCustomer->execute([':cid' => $customer_id]);
        $customerRow = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

        $old_balance = (float)($customerRow['balance'] ?? 0);
        $new_balance = $old_balance - $amount;

        $sqlUpdateCustomer = "
            UPDATE customers
            SET balance = :new_balance, updated_at = NOW()
            WHERE id = :cid
        ";
        $stmtUpdateCustomer = $db->prepare($sqlUpdateCustomer);
        $stmtUpdateCustomer->execute([
            ':new_balance' => $new_balance,
            ':cid'         => $customer_id,
        ]);

        // 3) Movimiento
        $sqlMovement = "
            INSERT INTO customer_movements
                (customer_id, type, amount, movement_date, notes, balance_after, related_sale_id, related_payment_id, created_at)
            VALUES
                (:customer_id, 'payment', :amount, NOW(), :notes, :balance_after, NULL, :payment_id, NOW())
        ";
        $stmtMovement = $db->prepare($sqlMovement);
        $stmtMovement->execute([
            ':customer_id'   => $customer_id,
            ':amount'        => $amount,
            ':notes'         => $notes,
            ':balance_after' => $new_balance,
            ':payment_id'    => $payment_id,
        ]);

        $db->commit();
        return $payment_id;
    } catch (Throwable $e) {
        $db->rollBack();
        throw new Exception('Error al registrar el pago: ' . $e->getMessage());
    }
}

function fetch_payment_by_id(PDO $db, int $payment_id): ?array
{
    $sql = "
        SELECT 
            id,
            customer_id,
            payment_date,
            amount,
            method,
            notes,
            receipt_path,
            created_at
        FROM payments
        WHERE id = :id
        LIMIT 1
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $payment_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function create_customer(PDO $db, string $name, ?string $phone, ?string $email, string $status = 'active'): int
{
    $sql = "
        INSERT INTO customers (name, phone, email, status, balance, created_at, updated_at)
        VALUES (:name, :phone, :email, :status, 0, NOW(), NOW())
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':name'   => $name,
        ':phone'  => $phone ?: null,
        ':email'  => $email ?: null,
        ':status' => $status,
    ]);

    return (int)$db->lastInsertId();
}

function update_customer(PDO $db, int $customer_id, string $name, ?string $phone, ?string $email, string $status = 'active'): void
{
    $sql = "
        UPDATE customers
        SET name = :name,
            phone = :phone,
            email = :email,
            status = :status,
            updated_at = NOW()
        WHERE id = :id
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':name'  => $name,
        ':phone' => $phone ?: null,
        ':email' => $email ?: null,
        ':status'=> $status,
        ':id'    => $customer_id,
    ]);
}
