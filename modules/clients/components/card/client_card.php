<?php
// prepara valores para data-*
$next_due   = $c['next_due_date'] ?? '';                 // "YYYY-MM-DD" o vacío
$balanceNum = (float)$c['balance'];                      // número
$email      = $c['email'] ?? '';
$status     = $c['status'] ?? 'pendiente';               // al_dia|pendiente|vencido
?>
<div
    class="card border-0 shadow-sm mb-3 client-card"
    data-customer-id="<?= (int)$c['customer_id'] ?>"
    data-name="<?= htmlspecialchars(mb_strtolower($c['name'])) ?>"
    data-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>"
    data-email="<?= htmlspecialchars(mb_strtolower($email)) ?>"
    data-status="<?= htmlspecialchars($status) ?>"
    data-balance="<?= number_format($balanceNum, 2, '.', '') ?>"
    data-next-due="<?= htmlspecialchars($next_due) ?>"
    data-index="<?= (int)$loop_index /* opcional para orden estable */ ?>">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <img src="https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=<?= urlencode($c['name']) ?>" class="rounded-circle" style="width:44px;height:44px;object-fit:cover;" alt="">
            <div class="me-auto">
                <div class="fw-semibold"><?= htmlspecialchars($c['name']) ?></div>
                <div class="text-secondary small"><?= htmlspecialchars($c['phone'] ?: '—') ?></div>
            </div>

            <div class="d-none d-xl-block text-secondary small">
                <?php if (!empty($c['last_payment_date'])): ?>
                    <i class="bi bi-cash-coin me-1"></i>
                    Último pago: <?= date('d/m/Y', strtotime($c['last_payment_date'])) ?> · $
                    <?= number_format((float)$c['last_payment_amount'], 0) ?>
                <?php else: ?>
                    <i class="bi bi-cash-coin me-1"></i>Sin pagos
                <?php endif; ?>
            </div>

            <div class="text-end">
                <div class="fw-semibold">$<?= number_format((float)$c['balance'], 2) ?></div>
                <?php $badge = ['al_dia' => 'success', 'pendiente' => 'warning', 'vencido' => 'danger'][$status] ?? 'secondary'; ?>
                <span class="badge text-bg-<?= $badge ?> d-none d-md-inline">
                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                </span>
            </div>
        </div>
    </div>
</div>