<div class="card border-0 shadow-sm mb-3 client-card" data-customer-id="<?= (int)$c['customer_id'] ?>">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <img src="https://i.pravatar.cc/80?u=<?= urlencode($c['customer_id']) ?>" class="rounded-circle" style="width:44px;height:44px;object-fit:cover;" alt="">
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
                <?php
                $badge = ['al_dia' => 'success', 'pendiente' => 'warning', 'vencido' => 'danger'][$c['status']] ?? 'secondary';
                ?>
                <span class="badge text-bg-<?= $badge ?> d-none d-md-inline">
                    <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                </span>
            </div>

            <div class="ms-auto ms-md-0">
                <div class="btn-group">
                    <a href="payments_new.php?customer=<?= (int)$c['customer_id'] ?>" class="btn btn-sm btn-primary">Registrar pago</a>
                </div>
            </div>
        </div>
    </div>
</div>