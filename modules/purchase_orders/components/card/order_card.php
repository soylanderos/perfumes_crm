<?php
$order_id = $order['id'];
$title = $order['title'] ?? ('Pedido #' . $order_id);
$status = $order['status'] ?? 'open';
$order_date = $order['order_date'] ?? null;
$notes = $order['notes'] ?? '';
$total_items = (int)($order['total_items'] ?? 0);
$used_items = (int)($order['used_items'] ?? 0);
$remaining = max($total_items - $used_items, 0);

$badge_label = [
'open' => 'Abierto',
'partial' => 'En uso',
'closed' => 'Cerrado',
][$status] ?? 'Abierto';

$badge_class = [
'open' => 'orders-badge-open',
'partial' => 'orders-badge-partial',
'closed' => 'orders-badge-closed',
][$status] ?? 'orders-badge-open';

$date_pretty = $order_date ? date('d/m/Y', strtotime($order_date)) : 'Sin fecha';
?>
<button class="orders-list-item"
    data-order-id="<?= htmlspecialchars($order_id) ?>"
    data-status="<?= htmlspecialchars($status) ?>"
    data-order-date="<?= htmlspecialchars($order_date) ?>"
    data-notes="<?= htmlspecialchars($notes) ?>">
    <div class="orders-list-main">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <h3 class="orders-list-title mb-0 text-truncate">
                <?= htmlspecialchars($title) ?>
            </h3>
            <span class="badge rounded-pill orders-status-badge <?= $badge_class ?>">
                <?= $badge_label ?>
            </span>
        </div>
        <div class="small text-muted mb-1">
            Creado el <?= $date_pretty ?>
        </div>
        <div class="small">
            <span class="fw-semibold"><?= $total_items ?></span> productos ·
            <span class="text-success fw-semibold"><?= $used_items ?></span> usados ·
            <span class="text-muted"><?= $remaining ?> disponibles</span>
        </div>
    </div>
    <div class="orders-list-chevron">
        <i class="bi bi-chevron-right"></i>
    </div>
</button>