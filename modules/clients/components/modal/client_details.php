<!-- Modal: Cliente con Tabs (vista/edición + órdenes + pagos) -->
<div class="modal fade" id="client_details_modal" tabindex="-1" aria-labelledby="client_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <!-- HEADER -->
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="client_modalLabel">
                        <?= htmlspecialchars($customer['name'] ?? 'Cliente') ?>
                    </h5>
                    <small class="text-secondary">
                        ID #<?= (int)($customer['id'] ?? 0) ?> ·
                        <?= htmlspecialchars($customer['email'] ?? '—') ?> ·
                        <?= htmlspecialchars($customer['phone'] ?? '—') ?>
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- NAV TABS -->
            <div class="px-3 pt-2">
                <ul class="nav nav-tabs" id="clientTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-info" data-bs-toggle="tab" data-bs-target="#pane-info" type="button" role="tab">
                            <i class="bi bi-person me-1"></i> Cliente
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-orders" data-customer-id="<?= (int)($customer['id'] ?? 0) ?>" data-bs-toggle="tab" data-bs-target="#pane-orders" type="button" role="tab">
                            <i class="bi bi-receipt me-1"></i> Órdenes
                            <span class="badge bg-secondary ms-1"><?= isset($total_orders) ? $total_orders : 0 ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-payments" data-customer-id="<?= (int)($customer['id'] ?? 0) ?>" data-bs-toggle="tab" data-bs-target="#pane-payments" type="button" role="tab">
                            <i class="bi bi-cash-coin me-1"></i> Pagos
                        </button>
                    </li>
                </ul>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <div class="tab-content" id="clientTabsContent">

                    <!-- TAB 1: INFO CLIENTE (LECTURA -> EDITAR) -->
                    <div class="tab-pane fade show active" id="pane-info" role="tabpanel" tabindex="0">
                       <?php include '../components/containers/client_info_containers.php'; ?>
                    </div>

                    <!-- TAB 2: ÓRDENES (listar + crear nueva + pago rápido) -->
                    <div class="tab-pane fade" id="pane-orders" role="tabpanel" tabindex="0">

                    </div>

                    <!-- TAB 3: PAGOS (general por orden + historial) -->
                    <div class="tab-pane fade" id="pane-payments" role="tabpanel" tabindex="0">

                    </div>

                </div> <!-- /tab-content -->
            </div>

        </div>
    </div>
</div>