<?php
session_start();
$session_user_id = $_SESSION["id"];

include '../../../utilities/db_conn.php';
$db = new PDO($dsn, $username, $password);
require "../model/dashboard_queries.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_request = filter_input(INPUT_POST, 'user_request');
} else {
    $user_request = filter_input(INPUT_GET, 'user_request');
}


switch ($user_request) {
    case 'fetch_dashboard':
        // KPIs principales
        $summary        = get_dashboard_summary($db);
        $chart_data     = get_monthly_sales_vs_payments($db, 6);
        $top_debtors    = get_top_debtors($db, 5);
        $recent_activity = get_recent_activity($db, 5);

        ob_start();
        include '../dashboard.php';
        $content = ob_get_clean();

        echo json_encode([
            'status'     => 'success',
            'view'       => $content,
            'chart_data' => $chart_data,
        ]);
        break;

    default:
        throw new Exception('Petición no reconocida para dashboard');
}
