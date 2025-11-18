<?php
// Variables esperadas:
// $customer, $sales_stats, $payments_stats, $recent_sales, $recent_payments, $movements

$customer_name  = $customer['name'] ?? '';
$customer_phone = $customer['phone'] ?? '';
$customer_email = $customer['email'] ?? '';
$customer_status = $customer['status'] ?? 'active';
$balance        = (float)($customer['balance'] ?? 0);

$total_sales        = (int)($sales_stats['total_sales'] ?? 0);
$total_sales_amount = (float)($sales_stats['total_sales_amount'] ?? 0);
$total_payments     = (int)($payments_stats['total_payments'] ?? 0);
$total_paid         = (float)($payments_stats['total_paid'] ?? 0);
$last_sale_date     = $sales_stats['last_sale_date'] ?? null;
$last_payment_date  = $payments_stats['last_payment_date'] ?? null;

$created_at = isset($customer['created_at']) ? date('d/m/Y', strtotime($customer['created_at'])) : null;
$last_sale_pretty    = $last_sale_date ? date('d/m/Y', strtotime($last_sale_date)) : null;
$last_payment_pretty = $last_payment_date ? date('d/m/Y', strtotime($last_payment_date)) : null;

// Tomamos la última compra y el último pago de los arrays que ya tienes
$last_sale_row    = !empty($recent_sales)    ? $recent_sales[0]    : null;
$last_payment_row = !empty($recent_payments) ? $recent_payments[0] : null;

$last_sale_amount    = $last_sale_row['total_amount']    ?? null;
$last_sale_items     = $last_sale_row['items_count']     ?? null;
$last_sale_date_full = $last_sale_row['sale_date']       ?? null;

$last_payment_amount = $last_payment_row['amount']       ?? null;
$last_payment_method = $last_payment_row['method']       ?? null;
$last_payment_date_full = $last_payment_row['payment_date'] ?? null;

$balance_class = $balance > 0 ? 'text-danger' : 'text-success';
?>
<div class="modal fade client-profile-modal" id="client_profile_modal"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
        <div class="modal-content client-profile-modal-content">
            <div class="modal-body p-0">

                <!-- Header / Hero -->
                <header class="client-profile-hero">
                    <div class="client-profile-hero-top">
                        <button type="button"
                            class="btn btn-light btn-sm rounded-pill client-profile-close"
                            data-bs-dismiss="modal">
                            <i class="bi bi-chevron-left me-1"></i> Volver
                        </button>

                        <div class="client-profile-hero-actions">
                            <button class="btn btn-outline-light btn-sm rounded-pill client-profile-edit"
                                type="button"
                                data-customer-id="<?= (int)$customer['id'] ?>">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </button>
                        </div>
                    </div>

                    <div class="client-profile-hero-main">
                        <div class="client-profile-avatar">
                            <span class="client-profile-avatar-initial">
                                <?= strtoupper(substr($customer_name, 0, 1)) ?>
                            </span>
                            <?php if ($customer_status === 'active'): ?>
                                <span class="client-profile-status-dot status-online"></span>
                            <?php else: ?>
                                <span class="client-profile-status-dot status-offline"></span>
                            <?php endif; ?>
                        </div>

                        <div class="client-profile-hero-info">
                            <h2 class="h4 mb-1 fw-semibold text-white"><?= htmlspecialchars($customer_name) ?></h2>
                            <div class="client-profile-contact small text-white-50 mb-1">
                                <?php if ($customer_phone): ?>
                                    <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($customer_phone) ?>
                                <?php endif; ?>
                                <?php if ($customer_phone && $customer_email): ?>
                                    &nbsp;&bull;&nbsp;
                                <?php endif; ?>
                                <?php if ($customer_email): ?>
                                    <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($customer_email) ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($created_at): ?>
                                <div class="small text-white-50">
                                    Cliente desde <span class="fw-semibold"><?= $created_at ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="client-profile-hero-meta">
                            <span class="badge rounded-pill bg-light text-dark mb-2">
                                <?= $customer_status === 'active' ? 'Activo' : 'Inactivo' ?>
                            </span>

                            <div class="client-profile-balance-box <?= $balance > 0 ? 'negative' : 'positive' ?>">
                                <div class="small mb-1">Saldo actual</div>
                                <div class="h5 mb-0 fw-semibold client-profile-balance-amount">
                                    <?= $balance > 0 ? '$' . number_format($balance, 2) : 'En regla' ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Tabs simples -->
                    <div class="client-profile-tabs">
                        <button class="client-profile-tab active" data-tab="overview">
                            <i class="bi bi-speedometer2 me-1"></i> Resumen
                        </button>
                        <button class="client-profile-tab" data-tab="sales">
                            <i class="bi bi-receipt-cutoff me-1"></i> Compras
                        </button>
                        <button class="client-profile-tab" data-tab="payments">
                            <i class="bi bi-cash-coin me-1"></i> Pagos
                        </button>
                        <button class="client-profile-tab" data-tab="movements">
                            <i class="bi bi-activity me-1"></i> Movimientos
                        </button>
                    </div>
                </header>


                <!-- Body -->
                <div class="client-profile-body">
                    <!-- TAB: RESUMEN -->
                    <section class="client-profile-tab-pane active" data-tab-content="overview">
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="client-profile-kpi">
                                    <div class="small text-muted mb-1">Total compras</div>
                                    <div class="fw-semibold h5 mb-0"><?= $total_sales ?></div>
                                    <div class="small text-muted">
                                        Monto: $<?= number_format($total_sales_amount, 2) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="client-profile-kpi">
                                    <div class="small text-muted mb-1">Pagos recibidos</div>
                                    <div class="fw-semibold h5 mb-0"><?= $total_payments ?></div>
                                    <div class="small text-muted">
                                        Total: $<?= number_format($total_paid, 2) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="client-profile-kpi">
                                    <div class="small text-muted mb-1">Última compra</div>
                                    <div class="fw-semibold mb-0">
                                        <?= $last_sale_pretty ?: '—' ?>
                                    </div>
                                    <div class="small text-muted">Fecha</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="client-profile-kpi">
                                    <div class="small text-muted mb-1">Último abono</div>
                                    <div class="fw-semibold mb-0">
                                        <?= $last_payment_pretty ?: '—' ?>
                                    </div>
                                    <div class="small text-muted">Fecha</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Última compra -->
                            <div class="col-12 col-lg-6">
                                <div class="client-overview-highlight client-overview-sale <?= $last_sale_row ? '' : 'client-overview-empty' ?>">

                                    <div class="coh-header">
                                        <span class="coh-label">Última compra</span>
                                        <?php if ($last_sale_row): ?>
                                            <span class="coh-pill">
                                                <?= (int)$total_sales ?> compras totales
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($last_sale_row): ?>
                                        <div class="coh-main">
                                            <div class="coh-amount">
                                                $<?= number_format($last_sale_amount, 2) ?>
                                            </div>
                                            <div class="coh-meta text-end">
                                                <div class="coh-meta-title">Realizada el</div>
                                                <div class="coh-meta-value">
                                                    <?= date('d/m/Y', strtotime($last_sale_date_full)) ?>
                                                </div>
                                                <div class="coh-meta-sub">
                                                    <?= (int)$last_sale_items ?> productos
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="coh-empty">
                                            <p class="mb-2">Este cliente aún no tiene compras registradas.</p>
                                            <button type="button"
                                                class="btn btn-light btn-sm rounded-pill clients-btn-add-sale"
                                                data-customer-id="<?= (int)$customer['id'] ?>">
                                                Registrar primera compra
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Último abono -->
                            <div class="col-12 col-lg-6">
                                <div class="client-overview-highlight client-overview-payment <?= $last_payment_row ? '' : 'client-overview-empty' ?>">

                                    <div class="coh-header">
                                        <span class="coh-label">Último abono</span>
                                        <?php if ($last_payment_row): ?>
                                            <span class="coh-pill">
                                                Total abonado: $<?= number_format($total_paid, 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($last_payment_row): ?>
                                        <?php
                                        switch ($last_payment_method) {
                                            case 'cash':
                                                $method_label = 'Efectivo';
                                                break;
                                            case 'transfer':
                                                $method_label = 'Transferencia';
                                                break;
                                            case 'card':
                                                $method_label = 'Tarjeta';
                                                break;
                                            case 'other':
                                                $method_label = 'Otro';
                                                break;
                                            default:
                                                $method_label = htmlspecialchars($last_payment_method);
                                        }
                                        ?>
                                        <div class="coh-main">
                                            <div class="coh-amount text-light">
                                                +$<?= number_format($last_payment_amount, 2) ?>
                                            </div>
                                            <div class="coh-meta text-end">
                                                <div class="coh-meta-title">Pagado el</div>
                                                <div class="coh-meta-value">
                                                    <?= date('d/m/Y', strtotime($last_payment_date_full)) ?>
                                                </div>
                                                <div class="coh-meta-sub">
                                                    Método: <?= $method_label ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="coh-empty">
                                            <p class="mb-2">Aún no hay pagos registrados para este cliente.</p>
                                            <button type="button"
                                                class="btn btn-light btn-sm rounded-pill clients-btn-add-payment"
                                                data-customer-id="<?= (int)$customer['id'] ?>">
                                                Registrar primer pago
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </section>

                    <!-- TAB: COMPRAS -->
                    <section class="client-profile-tab-pane" data-tab-content="sales">
                        <div class="client-profile-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h6 mb-0">Compras del cliente</h3>

                                <button type="button"
                                    class="btn btn-primary btn-xs rounded-pill client-profile-add-sale"
                                    data-customer-id="<?= (int)$customer['id'] ?>">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    Nueva compra
                                </button>
                            </div>

                            <?php if (!empty($recent_sales)): ?>
                                <ul class="list-unstyled mb-0 client-profile-list container-responsive-350">
                                    <?php foreach ($recent_sales as $sale): ?>
                                        <li class="client-profile-list-item">
                                            <div>
                                                <div class="fw-semibold small">
                                                    Compra #<?= (int)$sale['id'] ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?= date('d/m/Y', strtotime($sale['sale_date'])) ?>
                                                    &nbsp;&bull;&nbsp;
                                                    <?= (int)$sale['items_count'] ?> productos
                                                </div>
                                            </div>
                                            <div class="fw-semibold small">
                                                $<?= number_format($sale['total_amount'], 2) ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="small text-muted mb-2">
                                    No hay compras para mostrar todavía.
                                </p>
                                <button type="button"
                                    class="btn btn-outline-primary btn-xs rounded-pill client-profile-add-sale"
                                    data-customer-id="<?= (int)$customer['id'] ?>">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    Registrar primera compra
                                </button>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- TAB: PAGOS -->
                    <section class="client-profile-tab-pane" data-tab-content="payments">
                        <div class="client-profile-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h6 mb-0">Pagos del cliente</h3>
                                <button type="button"
                                    class="btn btn-success btn-xs rounded-pill client-profile-add-payment"
                                    data-customer-id="<?= (int)$customer['id'] ?>">
                                    <i class="bi bi-cash-coin me-1"></i>
                                    Registrar pago
                                </button>
                            </div>

                            <?php if (!empty($recent_payments)): ?>
                                <ul class="list-unstyled mb-0 client-profile-list container-responsive-350">
                                    <?php foreach ($recent_payments as $pay): ?>
                                        <li class="client-profile-list-item">
                                            <div>
                                                <div class="fw-semibold small">
                                                    <?= date('d/m/Y', strtotime($pay['payment_date'])) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?php
                                                    switch ($pay['method']) {
                                                        case 'cash':
                                                            $method_label = 'Efectivo';
                                                            break;
                                                        case 'transfer':
                                                            $method_label = 'Transferencia';
                                                            break;
                                                        case 'card':
                                                            $method_label = 'Tarjeta';
                                                            break;
                                                        case 'other':
                                                            $method_label = 'Otro';
                                                            break;
                                                        default:
                                                            $method_label = htmlspecialchars($pay['method']);
                                                    }
                                                    ?>
                                                    Método: <?= htmlspecialchars($method_label) ?>
                                                    <?php if (!empty($pay['notes'])): ?>
                                                        &nbsp;&bull;&nbsp; <?= htmlspecialchars($pay['notes']) ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($pay['receipt_path'])): ?>
                                                        &nbsp;&bull;&nbsp;
                                                        <span class="text-primary">
                                                            <i class="bi bi-paperclip me-1"></i>Comprobante adjunto
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column align-items-end gap-1">
                                                <div class="fw-semibold small text-success">
                                                    +$<?= number_format($pay['amount'], 2) ?>
                                                </div>
                                                <?php if (!empty($pay['receipt_path'])): ?>
                                                    <button type="button"
                                                        class="btn btn-light btn-xs rounded-pill client-payment-view"
                                                        data-payment-id="<?= (int)$pay['id'] ?>">
                                                        Ver detalle
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="small text-muted mb-0">
                                    No hay pagos registrados aún para este cliente.
                                </p>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- TAB: MOVIMIENTOS -->
                    <section class="client-profile-tab-pane" data-tab-content="movements">
                        <div class="client-profile-card">
                            <h3 class="h6 mb-3">Historial de movimientos</h3>
                            <?php if (!empty($movements)): ?>
                                <ul class="list-unstyled mb-0 client-profile-list container-responsive-350">
                                    <?php foreach ($movements as $m):
                                        $is_charge  = $m['type'] === 'charge';
                                        $is_payment = $m['type'] === 'payment';
                                        $sign       = $is_payment ? '+' : ($is_charge ? '-' : '');
                                        $amount     = number_format($m['amount'], 2);
                                        $date       = date('d/m/Y H:i', strtotime($m['movement_date']));
                                        $color      = $is_payment ? 'text-success' : 'text-danger';

                                        // Origen del movimiento
                                        $origin = '';
                                        if ($is_charge && !empty($m['related_sale_id'])) {
                                            $origin = 'Compra #' . (int)$m['related_sale_id'];
                                            if (!empty($m['sale_date'])) {
                                                $origin .= ' · ' . date('d/m/Y', strtotime($m['sale_date']));
                                            }
                                        } elseif ($is_payment && !empty($m['related_payment_id'])) {
                                            $origin = 'Pago #' . (int)$m['related_payment_id'];
                                            if (!empty($m['payment_method'])) {
                                                switch ($pay['method']) {
                                                        case 'cash':
                                                            $method_label = 'Efectivo';
                                                            break;
                                                        case 'transfer':
                                                            $method_label = 'Transferencia';
                                                            break;
                                                        case 'card':
                                                            $method_label = 'Tarjeta';
                                                            break;
                                                        case 'other':
                                                            $method_label = 'Otro';
                                                            break;
                                                        default:
                                                            $method_label = htmlspecialchars($pay['method']);
                                                    }
                                                $origin .= ' · ' . htmlspecialchars($method_label);
                                            }
                                        }
                                    ?>
                                        <li class="client-profile-list-item">
                                            <div>
                                                <div class="fw-semibold small">
                                                    <?= $is_payment ? 'Pago' : ($is_charge ? 'Cargo' : 'Ajuste') ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?= $date ?>
                                                    <?php if ($origin): ?>
                                                        &nbsp;&bull;&nbsp; <?= $origin ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($m['notes'])): ?>
                                                        &nbsp;&bull;&nbsp; <?= htmlspecialchars($m['notes']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="fw-semibold small <?= $color ?>">
                                                <?= $sign ?>$<?= $amount ?>
                                                <?php if (!is_null($m['balance_after'])): ?>
                                                    <div class="small text-muted text-end">
                                                        Saldo: $<?= number_format($m['balance_after'], 2) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="small text-muted mb-0">
                                    Aún no hay movimientos para este cliente.
                                </p>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>