<div class="clients-card clients-btn-view"
    data-customer-id="<?= htmlspecialchars($customer_id) ?>"
    data-status="<?= htmlspecialchars($customer_status) ?>"
    data-balance="<?= htmlspecialchars($customer_balance) ?>"
    data-last-payment-date="<?= htmlspecialchars($last_payment_raw ?? '') ?>">

    <div class="d-flex align-items-start gap-3 mb-3">
        <div class="clients-avatar">
            <span class="clients-avatar-initial">
                <?= strtoupper(substr($customer_name, 0, 1)) ?>
            </span>
            <?php if ($customer_status === 'active'): ?>
                <span class="clients-status-dot status-online"></span>
            <?php else: ?>
                <span class="clients-status-dot status-offline"></span>
            <?php endif; ?>
        </div>

        <!-- 👇 le agregamos una clase al contenedor principal -->
        <div class="flex-grow-1 clients-card-main">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <h2 class="h6 mb-0 fw-semibold text-truncate">
                    <?= htmlspecialchars($customer_name) ?>
                </h2>

                <div class="dropdown">
                    <button class="btn btn-icon btn-link btn-sm text-muted p-0 clients-card-menu"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        data-customer-id="<?= htmlspecialchars($customer_id) ?>">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button class="dropdown-item client-action-view"
                                type="button"
                                data-customer-id="<?= htmlspecialchars($customer_id) ?>">
                                Ver perfil
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item client-action-edit"
                                type="button"
                                data-customer-id="<?= htmlspecialchars($customer_id) ?>">
                                Editar cliente
                            </button>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <button class="dropdown-item client-action-add-sale"
                                type="button"
                                data-customer-id="<?= htmlspecialchars($customer_id) ?>">
                                Registrar compra
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item client-action-add-payment"
                                type="button"
                                data-customer-id="<?= htmlspecialchars($customer_id) ?>">
                                Registrar pago
                            </button>
                        </li>
                    </ul>
                </div>
            </div>


            <?php if ($customer_phone || $customer_email): ?>
                <!-- 👇 mantenemos text-truncate pero ya va a respetar el ancho -->
                <div class="small text-muted text-truncate clients-contact-line">
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
            <?php else: ?>
                <div class="small text-muted fst-italic">
                    Sin datos de contacto aún
                </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- Saldos / info financiera -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex flex-column">
            <span class="small text-muted">Saldo actual</span>
            <span class="fw-semibold <?= $customer_balance > 0 ? 'text-danger' : 'text-success' ?>">
                <?= $customer_balance > 0 ? '$' . number_format($customer_balance, 2) : 'En regla' ?>
            </span>
        </div>
        <span class="badge rounded-pill clients-badge-light">
            <?= $customer_status === 'active' ? 'Activo' : 'Inactivo' ?>
        </span>
    </div>

    <!-- Barra de cumplimiento semanal -->
    <div class="mb-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small text-muted">Hábitos de pago (semana)</span>
            <span class="small fw-semibold"><?= (int)$weekly_score ?>%</span>
        </div>
        <div class="progress clients-progress">
            <div class="progress-bar" role="progressbar"
                style="width: <?= (int)$weekly_score ?>%;"
                aria-valuenow="<?= (int)$weekly_score ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>

    <!-- Último movimiento -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
            <?php if ($last_payment_date): ?>
                <?php
                // ✅ Formato más amigable (opcional)
                $last_payment_pretty = date('d/m/Y', strtotime($last_payment_date));
                ?>
                <span class="clients-last-payment">
                    Último abono: <span class="fw-semibold"><?= htmlspecialchars($last_payment_pretty) ?></span>
                </span>
            <?php else: ?>
                <span class="fw-semibold text-warning">Nunca ha abonado</span>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-2">
            <!-- <button class="btn btn-primary btn-xs rounded-pill px-3 clients-btn-add-sale"
                data-customer-id="">
                Nueva compra
            </button> -->
        </div>
    </div>

</div>