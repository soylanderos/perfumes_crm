<?php
// Espera: $customer_id y $available_items del controller
?>
<div class="modal fade" id="client_new_sale_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4 client-new-sale-modal-content">
            <form id="client_new_sale_form" autocomplete="off">
                <input type="hidden" id="sale_customer_id" value="<?= htmlspecialchars($customer_id) ?>">

                <div class="modal-header border-0 pb-0 px-3 px-md-4 pt-3 pt-md-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                        Registrar compra
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button"
                            class="btn btn-light btn-sm rounded-pill"
                            data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>
                </div>

                <div class="modal-body px-3 px-md-4 pb-3 pb-md-4 pt-2">
                    <!-- Datos de la compra -->
                    <div class="mb-3">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label small mb-1">Fecha de la compra</label>
                                <input type="date"
                                    class="form-control form-control-sm"
                                    id="sale_date"
                                    value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-6 col-md-9">
                                <label class="form-label small mb-1">Notas</label>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="sale_notes"
                                    placeholder="Ej. Venta a crédito, pedido especial, etc.">
                            </div>
                        </div>
                    </div>

                    <!-- Lista de productos disponibles (de pedidos) -->
                    <div class="client-new-sale-items-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold small text-muted text-uppercase">
                                Productos disponibles de listas de pedidos
                            </h6>
                            <span class="small text-muted">
                                Solo se muestran productos con unidades disponibles.
                            </span>
                        </div>

                        <?php if (!empty($available_items)): ?>
                            <div class="client-new-sale-list-wrapper">
                                <?php foreach ($available_items as $row):
                                    $poi_id      = (int)$row['id'];
                                    $order_title = $row['order_title'] ?? ('Pedido #' . $row['purchase_order_id']);
                                    $order_date  = $row['order_date'] ? date('d/m/Y', strtotime($row['order_date'])) : 'Sin fecha';
                                    $product     = $row['product_name'];
                                    $available   = (int)$row['quantity_available'];
                                    $cost        = $row['unit_cost'];
                                ?>
                                    <div class="client-sale-item-card sale-item-row"
                                        data-order-item-id="<?= htmlspecialchars($poi_id) ?>"
                                        data-max-qty="<?= htmlspecialchars($available) ?>">

                                        <!-- Header tipo pricing card -->
                                        <div class="client-sale-item-header">
                                            <div class="client-sale-item-title-wrap">
                                                <input type="checkbox"
                                                    class="form-check-input sale-item-select client-sale-item-check">
                                                <div>
                                                    <div class="client-sale-item-title">
                                                        <?= htmlspecialchars($product) ?>
                                                    </div>
                                                    <div class="client-sale-item-sub small text-muted">
                                                        ID item: #<?= $poi_id ?>
                                                        <?php if (!is_null($cost)): ?>
                                                            · Costo base: $<?= number_format((float)$cost, 2) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="client-sale-item-pill">
                                                <?= $available ?> u disp.
                                            </div>
                                        </div>

                                        <!-- Meta de lista -->
                                        <div class="client-sale-item-meta-row small">
                                            <span class="text-muted">Lista:</span>
                                            <span class="client-sale-item-meta-text">
                                                <?= htmlspecialchars($order_title) ?>
                                            </span>
                                            <span class="client-sale-item-dot">&bull;</span>
                                            <span class="text-muted"><?= $order_date ?></span>
                                        </div>

                                        <!-- Controles de cantidad / precio -->
                                        <div class="client-sale-item-controls">
                                            <div class="client-sale-item-control">
                                                <span class="small text-muted d-block">Cantidad</span>
                                                <input type="number"
                                                    class="form-control form-control-sm text-center sale-item-qty"
                                                    min="1"
                                                    max="<?= $available ?>"
                                                    value="<?= min(1, $available) ?>">
                                            </div>

                                            <div class="client-sale-item-control">
                                                <span class="small text-muted d-block">Precio unit.</span>
                                                <input type="number"
                                                    class="form-control form-control-sm text-end sale-item-price"
                                                    min="0"
                                                    step="0.01"
                                                    placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted mb-0">
                                No hay productos disponibles en las listas de pedidos.
                                Crea o actualiza una lista de pedidos primero.
                            </p>
                        <?php endif; ?>

                    </div>

                    <p class="small text-muted mt-2 mb-0">
                        Solo se registrarán los productos marcados con el check. El saldo del cliente se actualizará
                        automáticamente con el total de esta compra.
                    </p>
                </div>

                <div class="modal-footer border-0 px-3 px-md-4 pb-3 pb-md-4 pt-0">
                    <button type="button"
                        class="btn btn-light rounded-pill"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="btn btn-primary rounded-pill">
                        Guardar compra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>