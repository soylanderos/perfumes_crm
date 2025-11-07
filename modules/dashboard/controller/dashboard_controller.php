<?php
session_start();
$session_user_id = $_SESSION["id"];
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

            // Rango 12m
            $to   = (new DateTime('today'))->format('Y-m-d 23:59:59');
            $from = (new DateTime('first day of -11 months'))->setTime(0, 0, 0)->format('Y-m-d H:i:s');

           

            // KPIs
            $kpi = fetchAllAssoc($db, "
  SELECT
    (SELECT COALESCE(SUM(o.total_amount),0) FROM orders o WHERE o.created_at BETWEEN :from AND :to) AS total_sales,
    (SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.payment_date BETWEEN :from AND :to) AS total_paid,
    (SELECT COALESCE(SUM(o.total_amount - o.paid_amount),0) FROM orders o) AS total_due,
    (SELECT COUNT(*) FROM orders o WHERE o.created_at BETWEEN :from AND :to) AS orders_count,
    (SELECT COALESCE(AVG(o.total_amount),0) FROM orders o WHERE o.created_at BETWEEN :from AND :to) AS avg_ticket
", [':from' => $from, ':to' => $to])[0];

            $series_sales   = fetchAllAssoc($db, "SELECT DATE_FORMAT(o.created_at, '%Y-%m') ym, SUM(o.total_amount) amt FROM orders o
                                      WHERE o.created_at BETWEEN :from AND :to GROUP BY ym ORDER BY ym", [':from' => $from, ':to' => $to]);
            $series_paid    = fetchAllAssoc($db, "SELECT DATE_FORMAT(p.payment_date, '%Y-%m') ym, SUM(p.amount) amt FROM payments p
                                      WHERE p.payment_date BETWEEN :from AND :to GROUP BY ym ORDER BY ym", [':from' => $from, ':to' => $to]);
            $by_status      = fetchAllAssoc($db, "SELECT status, COUNT(*) cnt FROM orders GROUP BY status");
            $aging          = fetchAllAssoc($db, "SELECT
    SUM(CASE WHEN o.due_date IS NULL OR o.due_date >= CURDATE() THEN 1 ELSE 0 END) current_cnt,
    SUM(CASE WHEN o.due_date < CURDATE() AND DATEDIFF(CURDATE(), o.due_date) BETWEEN 1  AND 7  THEN 1 END) d1_7_cnt,
    SUM(CASE WHEN o.due_date < CURDATE() AND DATEDIFF(CURDATE(), o.due_date) BETWEEN 8  AND 30 THEN 1 END) d8_30_cnt,
    SUM(CASE WHEN o.due_date < CURDATE() AND DATEDIFF(CURDATE(), o.due_date) BETWEEN 31 AND 60 THEN 1 END) d31_60_cnt,
    SUM(CASE WHEN o.due_date < CURDATE() AND DATEDIFF(CURDATE(), o.due_date) > 60 THEN 1 END) d60p_cnt
  FROM orders o WHERE (o.total_amount - o.paid_amount) > 0")[0];

            $top_customers  = fetchAllAssoc($db, "SELECT c.id, c.name, SUM(o.total_amount) total
                                      FROM orders o JOIN customers c ON c.id=o.customer_id
                                      WHERE o.created_at BETWEEN :from AND :to
                                      GROUP BY c.id, c.name ORDER BY total DESC LIMIT 8", [':from' => $from, ':to' => $to]);

            $top_debtors    = fetchAllAssoc($db, "SELECT c.id, c.name, SUM(o.total_amount - o.paid_amount) due
                                      FROM orders o JOIN customers c ON c.id=o.customer_id
                                      WHERE (o.total_amount - o.paid_amount) > 0
                                      GROUP BY c.id, c.name ORDER BY due DESC LIMIT 8");

            $top_products   = fetchAllAssoc($db, "SELECT oi.product_id, p.name, SUM(oi.qty) qty, SUM(oi.subtotal) total
                                      FROM order_items oi JOIN products p ON p.id=oi.product_id
                                      JOIN orders o ON o.id=oi.order_id
                                      WHERE o.created_at BETWEEN :from AND :to
                                      GROUP BY oi.product_id, p.name ORDER BY total DESC LIMIT 10", [':from' => $from, ':to' => $to]);

            $low_stock      = fetchAllAssoc(
                $db,
                "SELECT id, sku, name, stock FROM products WHERE active=1 AND stock <= :thr ORDER BY stock ASC LIMIT 20",
                [':thr' => 5]
            );

            $due_soon       = fetchAllAssoc($db, "SELECT id, customer_id, total_amount, (total_amount - paid_amount) balance, due_date
                                      FROM orders WHERE (total_amount - paid_amount) > 0
                                      AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                                      ORDER BY due_date ASC LIMIT 10");

            $overdue        = fetchAllAssoc($db, "SELECT id, customer_id, total_amount, (total_amount - paid_amount) balance, due_date
                                      FROM orders WHERE (total_amount - paid_amount) > 0
                                      AND due_date < CURDATE() ORDER BY due_date ASC LIMIT 10");

            // Helper: serie completa 12m (rellena con 0 donde falte)
            function fillMonths($rows, $from)
            {
                $map = [];
                foreach ($rows as $r) $map[$r['ym']] = (float)$r['amt'];
                $outLabels = [];
                $outData = [];
                $dt = new DateTime($from);
                for ($i = 0; $i < 12; $i++) {
                    $k = $dt->format('Y-m');
                    $outLabels[] = $k;
                    $outData[]   = $map[$k] ?? 0.0;
                    $dt->modify('+1 month');
                }
                return [$outLabels, $outData];
            }
            [$labels, $salesData] = fillMonths($series_sales, $from);
            [, $paidData]         = fillMonths($series_paid,  $from);


            ob_start();
            include '../dashboard.php';
            $content = ob_get_clean();

            echo json_encode(['status' => 'success', 'message' => 'Dashboard data fetched successfully', 'view' => $content]);
        } catch (PDOException | ErrorException | Exception $e) {
            error_log('Database Error: ' . $e->getMessage());
            $message = $development_mode ? 'Database error occurred: ' . $e->getMessage() : $user_message;
            echo json_encode(['status' => 'error', 'message' => $message]);
        }
        break;
}
