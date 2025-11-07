<div class="row g-3">
    <div class="col-12 col-lg-7">
        <?php if (!empty($orders)): ?>
            <div class="vstack gap-3 container-responsive-350">
                <?php foreach ($orders as $o):
                    $orderId = (int)$o['id'];
                    $total   = (float)$o['total_amount'];
                    $paid    = (float)$o['paid_amount'];
                    $balance = max(0, $total - $paid);
                    $due     = $o['due_date'] ?? null;
                    $badge   = ($balance <= 0) ? 'success' : (($due && $due < date('Y-m-d')) ? 'danger' : ($paid > 0 ? 'warning' : 'secondary'));
                ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div>
                                    <div class="fw-semibold">Orden #<?= $orderId ?></div>
                                    <div class="small text-secondary">
                                        Creada: <?= htmlspecialchars(date('d/m/Y', strtotime($o['created_at'] ?? 'now'))) ?>
                                        <?php if ($due): ?> · Vence: <?= htmlspecialchars(date('d/m/Y', strtotime($due))) ?><?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div>Total: <span class="fw-semibold">$<?= number_format($total, 2) ?></span></div>
                                    <div>Pagado: $<?= number_format($paid, 2) ?></div>
                                    <div>Saldo: <span class="fw-semibold">$<?= number_format($balance, 2) ?></span></div>
                                </div>
                                <div>
                                    <span class="badge text-bg-<?= $badge ?>">
                                        <?= $balance <= 0 ? 'Pagada' : (($due && $due < date('Y-m-d')) ? 'Vencida' : ($paid > 0 ? 'Parcial' : 'Activa')) ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($o['items'])): ?>
                                <div class="mt-3">
                                    <div class="small text-secondary mb-1">Productos</div>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($o['items'] as $it): ?>
                                            <li class="list-group-item px-0 d-flex justify-content-between">
                                                <span><?= htmlspecialchars($it['name'] ?? '') ?> × <?= (int)($it['qty'] ?? 1) ?></span>
                                                <span>$<?= number_format((float)($it['subtotal'] ?? 0), 2) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Pago rápido -->
                            <?php if ($balance > 0): ?>
                                <form class="row g-2 align-items-end mt-3 js-pay-order-form" data-order-id="<?= $orderId ?>" enctype="multipart/form-data">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    <input type="hidden" name="customer_id" value="<?= (int)($customer_id ?? 0) ?>">

                                    <div class="col-6 col-md-3">
                                        <label class="form-label small">Monto</label>
                                        <input type="number" step="0.01" min="0.01" max="<?= number_format($balance, 2, '.', '') ?>"
                                            name="amount" class="form-control" placeholder="<?= number_format($balance, 2) ?>" required>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <label class="form-label small">Método</label>
                                        <select name="method" class="form-select">
                                            <option value="efectivo">Efectivo</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="whatsapp_link">Link</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>

                                    <!-- NUEVO: comprobante -->
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small">Comprobante (PDF/imagen)</label>
                                        <input type="file" name="receipt" class="form-control" accept=".pdf,image/*">
                                    </div>

                                    <div class="col-12 col-md-8 order-2 order-md-1">
                                        <label class="form-label small">Nota</label>
                                        <input type="text" name="note" class="form-control" placeholder="Opcional">
                                    </div>

                                    <div class="col-12 col-md-2 d-grid order-1 order-md-2">
                                        <button type="submit" class="btn btn-primary">
                                            Registrar pago
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-secondary">Sin órdenes registradas.</div>
        <?php endif; ?>
    </div>

    <!-- NUEVA ORDEN (lista de productos) -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Nueva orden</h6>

                <form id="form_order_create">
                    <input type="hidden" name="customer_id" value="<?= (int)($customer_id ?? 0) ?>">

                    <!-- LISTA DE PRODUCTOS -->
                    <ul class="list-group mb-2" id="order_items_list">

                        <!-- TEMPLATE (oculto) -->
                        <li class="list-group-item d-none" data-template="true" aria-hidden="true">
                            <div class="row g-2 align-items-end">
                                <div class="col-12">
                                    <label class="form-label small mb-1">Producto</label>
                                    <select class="form-select js-product"
                                        data-name="items[__i__][product_id]" disabled required>
                                        <option value="">Selecciona un producto…</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['id'] ?>" data-price="<?= number_format((float)$p['price'], 2, '.', '') ?>">
                                                <?= htmlspecialchars($p['name']) ?> — $<?= number_format((float)$p['price'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small mb-1">Cant.</label>
                                    <input type="number" class="form-control js-qty"
                                        data-name="items[__i__][qty]" min="1" step="1" value="1" disabled required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small mb-1">Precio</label>
                                    <input type="number" class="form-control js-price"
                                        data-name="items[__i__][unit_price]" min="0" step="0.01" placeholder="0.00" disabled required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small mb-1">Subtotal</label>
                                    <input type="text" class="form-control js-subtotal" value="$0.00" readonly disabled>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger js-remove" disabled>Quitar</button>
                                </div>
                            </div>
                        </li>

                        <!-- /TEMPLATE -->

                        <!-- PRIMER ITEM real -->
                        <li class="list-group-item" data-index="0">
                            <div class="row g-2 align-items-end">
                                <div class="col-12">
                                    <label class="form-label small mb-1">Producto</label>
                                    <select class="form-select js-product" name="items[0][product_id]" required>
                                        <option value="">Selecciona un producto…</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['id'] ?>"
                                                data-price="<?= number_format((float)$p['price'], 2, '.', '') ?>">
                                                <?= htmlspecialchars($p['name']) ?> — $<?= number_format((float)$p['price'], 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small mb-1">Cant.</label>
                                    <input type="number" class="form-control js-qty" name="items[0][qty]"
                                        min="1" step="1" value="1" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small mb-1">Precio</label>
                                    <input type="number" class="form-control js-price" name="items[0][unit_price]"
                                        min="0" step="0.01" placeholder="0.00" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small mb-1">Subtotal</label>
                                    <input type="text" class="form-control js-subtotal" value="$0.00" readonly>
                                </div>
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger js-remove" disabled>Quitar</button>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn_add_item">+ Agregar producto</button>
                        <div class="text-end">
                            <div class="small text-secondary">Total</div>
                            <div class="fs-5 fw-semibold" id="order_total">$0.00</div>
                            <input type="hidden" name="total_amount" id="order_total_input" value="0.00">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small">Fecha de vencimiento</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Notas</label>
                        <input type="text" name="notes" class="form-control" placeholder="Opcional">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Crear orden</button>
                    </div>
                </form>

                <small class="text-secondary d-block mt-2">
                    * El precio se **autocompleta** desde el catálogo pero es **editable**.
                </small>
            </div>
        </div>
    </div>

</div>