<?php

/**
 * Trae todas las listas de pedidos con resumen de items.
 */
function fetch_purchase_orders_summary(PDO $db): array
{
    $sql = "
        SELECT 
            po.id,
            po.title,
            po.order_date,
            po.status,
            po.notes,
            COUNT(poi.id) AS total_items,
            SUM(
                CASE 
                    WHEN COALESCE(poi.quantity_used, 0) > 0 THEN 1 
                    ELSE 0 
                END
            ) AS used_items
        FROM purchase_orders po
        LEFT JOIN purchase_order_items poi 
            ON poi.purchase_order_id = po.id
        GROUP BY po.id, po.title, po.order_date, po.status, po.notes
        ORDER BY po.order_date DESC, po.id DESC
    ";

    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Trae header + totales de un pedido específico.
 */
function fetch_purchase_order_header(PDO $db, int $order_id): ?array
{
    $sql = "
        SELECT 
            po.id,
            po.title,
            po.order_date,
            po.status,
            po.notes,
            COUNT(poi.id) AS total_items,
            COALESCE(SUM(poi.quantity), 0) AS total_units,
            COALESCE(SUM(poi.quantity_used), 0) AS used_units
        FROM purchase_orders po
        LEFT JOIN purchase_order_items poi 
            ON poi.purchase_order_id = po.id
        WHERE po.id = :order_id
        GROUP BY po.id, po.title, po.order_date, po.status, po.notes
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $order_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

/**
 * Trae todos los items de un pedido.
 */
function fetch_purchase_order_items(PDO $db, int $order_id): array
{
    $sql = "
        SELECT 
            poi.id,
            poi.sku_id,
            poi.product_name,
            poi.quantity,
            COALESCE(poi.quantity_used, 0) AS quantity_used,
            (poi.quantity - COALESCE(poi.quantity_used, 0)) AS quantity_available,
            poi.unit_cost,
            poi.is_acquired
        FROM purchase_order_items poi
        WHERE poi.purchase_order_id = :order_id
        ORDER BY poi.id ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}



function create_purchase_order_with_items(PDO $db, array $order_data, array $items): int
{
    if (empty($items)) {
        throw new Exception('No se puede crear una lista sin productos.');
    }

    $title      = trim($order_data['title'] ?? '');
    $order_date = $order_data['order_date'] ?? null;
    $notes      = $order_data['notes'] ?? null;

    if ($title === '') {
        throw new Exception('El título de la lista es obligatorio.');
    }

    // normalizar fecha: si viene vacío, se queda null
    if ($order_date === '') {
        $order_date = null;
    }

    try {
        $db->beginTransaction();

        // Insert en purchase_orders
        $sqlOrder = "
            INSERT INTO purchase_orders
                (title, order_date, status, notes, created_at, updated_at)
            VALUES
                (:title, :order_date, :status, :notes, NOW(), NOW())
        ";

        $stmtOrder = $db->prepare($sqlOrder);
        $status    = 'open';

        $stmtOrder->execute([
            ':title'      => $title,
            ':order_date' => $order_date,
            ':status'     => $status,
            ':notes'      => $notes,
        ]);

        $order_id = (int)$db->lastInsertId();

        // Insert items
        $sqlItem = "
            INSERT INTO purchase_order_items
                (purchase_order_id, sku_id, product_name, quantity, quantity_used, unit_cost, created_at, updated_at)
            VALUES
                (:order_id, :sku_id, :product_name, :quantity, 0, :unit_cost, NOW(), NOW())
        ";

        $stmtItem = $db->prepare($sqlItem);

        foreach ($items as $item) {
            $product_name = trim($item['product_name'] ?? '');
            $quantity     = (int)($item['quantity'] ?? 0);
            $unit_cost    = $item['unit_cost'] ?? null;

            if ($product_name === '' || $quantity <= 0) {
                // skip basura
                continue;
            }

            // si viene string vacío en unit_cost, lo dejamos null
            if ($unit_cost === '' || $unit_cost === null) {
                $unit_cost = null;
            } else {
                $unit_cost = (float)$unit_cost;
            }

            $stmtItem->execute([
                ':order_id'     => $order_id,
                ':sku_id'       => null, // luego lo ligamos a SKUs si quieres
                ':product_name' => $product_name,
                ':quantity'     => $quantity,
                ':unit_cost'    => $unit_cost,
            ]);
        }

        $db->commit();
        return $order_id;

    } catch (Throwable $e) {
        $db->rollBack();
        throw new Exception('Error al crear la lista de pedidos: ' . $e->getMessage());
    }
}

function update_order_item_acquired(PDO $db, int $item_id, bool $is_acquired): void
{
    $sql = "
        UPDATE purchase_order_items
        SET is_acquired = :is_acquired, updated_at = NOW()
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':is_acquired' => $is_acquired ? 1 : 0,
        ':id'          => $item_id,
    ]);
}

function recalc_purchase_order_status(PDO $db, int $order_id): string
{
    $sql = "
        SELECT 
            COUNT(*) AS total_items,
            SUM(CASE WHEN is_acquired = 1 THEN 1 ELSE 0 END) AS acquired_items
        FROM purchase_order_items
        WHERE purchase_order_id = :order_id
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':order_id' => $order_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $total     = (int)($row['total_items'] ?? 0);
    $acquired  = (int)($row['acquired_items'] ?? 0);

    if ($total === 0) {
        $status = 'open';
    } elseif ($acquired === 0) {
        $status = 'open';
    } elseif ($acquired < $total) {
        $status = 'partial';
    } else {
        $status = 'closed';
    }

    $sqlU = "UPDATE purchase_orders SET status = :status, updated_at = NOW() WHERE id = :id LIMIT 1";
    $stmtU = $db->prepare($sqlU);
    $stmtU->execute([
        ':status' => $status,
        ':id'     => $order_id,
    ]);

    return $status;
}

function add_items_to_purchase_order(PDO $db, int $order_id, array $items): void
{
    if (empty($items)) {
        return;
    }

    $sql = "
        INSERT INTO purchase_order_items
            (purchase_order_id, sku_id, product_name, quantity, quantity_used, is_acquired, unit_cost, created_at, updated_at)
        VALUES
            (:order_id, :sku_id, :product_name, :quantity, 0, 0, :unit_cost, NOW(), NOW())
    ";

    $stmt = $db->prepare($sql);

    foreach ($items as $item) {
        $name = trim($item['product_name'] ?? '');
        $qty  = (int)($item['quantity'] ?? 0);
        $cost = $item['unit_cost'] ?? null;

        if ($name === '' || $qty <= 0) {
            continue;
        }

        if ($cost === '' || $cost === null) {
            $cost = null;
        } else {
            $cost = (float)$cost;
        }

        $stmt->execute([
            ':order_id'     => $order_id,
            ':sku_id'       => null,
            ':product_name' => $name,
            ':quantity'     => $qty,
            ':unit_cost'    => $cost,
        ]);
    }
}
