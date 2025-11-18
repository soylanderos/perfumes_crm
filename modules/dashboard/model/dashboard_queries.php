<?php
// modules/dashboard/model/dashboard_queries.php

function get_dashboard_summary(PDO $db): array
{
    // Total clientes, con deuda, sin deuda, saldo total y nuevos este mes
    $sql = "
        SELECT 
            COUNT(*) AS total_clients,
            SUM(CASE WHEN balance > 0  THEN 1 ELSE 0 END) AS clients_with_debt,
            SUM(CASE WHEN balance <= 0 THEN 1 ELSE 0 END) AS clients_clear,
            SUM(GREATEST(balance, 0)) AS total_receivable,
            SUM(CASE WHEN created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 ELSE 0 END) AS new_clients_this_month
        FROM customers
    ";
    $stmt = $db->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Compras y pagos del mes
    $sqlSales = "
        SELECT COALESCE(SUM(total_amount), 0) AS sales_this_month
        FROM customer_sales
        WHERE sale_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ";
    $sales = $db->query($sqlSales)->fetch(PDO::FETCH_ASSOC);

    $sqlPayments = "
        SELECT COALESCE(SUM(amount), 0) AS payments_this_month
        FROM payments
        WHERE payment_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ";
    $payments = $db->query($sqlPayments)->fetch(PDO::FETCH_ASSOC);

    $row['sales_this_month']    = (float)$sales['sales_this_month'];
    $row['payments_this_month'] = (float)$payments['payments_this_month'];
    $row['net_change']          = $row['sales_this_month'] - $row['payments_this_month'];

    return $row;
}

/**
 * Compras vs pagos por mes (últimos $months meses)
 */
function get_monthly_sales_vs_payments(PDO $db, int $months = 6): array
{
    // Normalizamos a AAAA-MM
    $sqlSales = "
        SELECT DATE_FORMAT(sale_date, '%Y-%m') AS ym,
               SUM(total_amount) AS total
        FROM customer_sales
        WHERE sale_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL :months MONTH), '%Y-%m-01')
        GROUP BY ym
    ";
    $stmt = $db->prepare($sqlSales);
    $stmt->bindValue(':months', $months, PDO::PARAM_INT);
    $stmt->execute();
    $salesRows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ym => total

    $sqlPayments = "
        SELECT DATE_FORMAT(payment_date, '%Y-%m') AS ym,
               SUM(amount) AS total
        FROM payments
        WHERE payment_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL :months MONTH), '%Y-%m-01')
        GROUP BY ym
    ";
    $stmt = $db->prepare($sqlPayments);
    $stmt->bindValue(':months', $months, PDO::PARAM_INT);
    $stmt->execute();
    $payRows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Armamos línea de tiempo ordenada
    $labels   = [];
    $sales    = [];
    $payments = [];

    $current = new DateTime(date('Y-m-01', strtotime('-' . ($months - 1) . ' month')));
    $end     = new DateTime(date('Y-m-01'));
    while ($current <= $end) {
        $ym = $current->format('Y-m');
        $labels[]   = $current->format('M Y');
        $sales[]    = isset($salesRows[$ym]) ? (float)$salesRows[$ym] : 0;
        $payments[] = isset($payRows[$ym]) ? (float)$payRows[$ym] : 0;
        $current->modify('+1 month');
    }

    return [
        'labels'   => $labels,
        'sales'    => $sales,
        'payments' => $payments,
    ];
}

/**
 * Top N clientes con más deuda
 */
function get_top_debtors(PDO $db, int $limit = 5): array
{
    $sql = "
        SELECT id, name, balance
        FROM customers
        WHERE balance > 0
        ORDER BY balance DESC
        LIMIT :limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Actividad reciente (cargos / pagos) basada en customer_movements
 */
function get_recent_activity(PDO $db, int $limit = 5): array
{
    $sql = "
        SELECT 
            m.movement_date,
            m.type,
            m.amount,
            m.notes,
            c.name AS customer_name
        FROM customer_movements m
        JOIN customers c ON c.id = m.customer_id
        ORDER BY m.movement_date DESC, m.id DESC
        LIMIT :limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
