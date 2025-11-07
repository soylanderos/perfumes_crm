<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Registrar pago</h6>

                <!-- IMPORTANTE: enctype -->
                <form id="form_payment_general" enctype="multipart/form-data">
                    <input type="hidden" name="customer_id" value="<?= (int)($customer_id ?? 0) ?>">

                    <div class="mb-2">
                        <label class="form-label small">Orden</label>
                        <select name="order_id" class="form-select" required>
                            <option value="">Selecciona una orden…</option>
                            <?php if (!empty($orders)): foreach ($orders as $o):
                                    $orderId = (int)$o['id'];
                                    $bal = (float)$o['total_amount'] - (float)$o['paid_amount']; ?>
                                    <option value="<?= $orderId ?>">#<?= $orderId ?> — Saldo $<?= number_format(max(0, $bal), 2) ?></option>
                            <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small">Monto</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Método</label>
                            <select name="method" class="form-select">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>

                        <!-- NUEVO: Comprobante -->
                        <div class="col-12">
                            <label class="form-label small">Comprobante (PDF/imagen)</label>
                            <input type="file" name="receipt" class="form-control"
                                accept=".pdf,image/*">
                            <div class="form-text">Máx. 10 MB. Se guarda como evidencia del pago.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small">Nota</label>
                            <input type="text" name="note" class="form-control" placeholder="Opcional">
                        </div>

                        <div class="col-12 d-grid">
                            <button class="btn btn-primary" type="submit">Guardar pago</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- derecha: pagos recientes (sin cambios aquí) -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Pagos recientes</h6>
                <?php if (!empty($recent_payments)): ?>
                    <ul class="list-group list-group-flush container-responsive-350">
                        <?php foreach ($recent_payments as $p): ?>
                            <?php
                            $method = $p['method'] ?? 'otro';
                            $methodBadge = [
                                'efectivo'       => 'success',
                                'transferencia'  => 'primary',
                                'tarjeta'        => 'warning',
                                'whatsapp_link'  => 'info',
                                'otro'           => 'secondary'
                            ][$method] ?? 'secondary';
                            ?>

                            <li class="list-group-item p-3">

                                <!-- FILA SUPERIOR: monto + meta + método + acciones -->
                                <div class="row g-3 align-items-center">
                                    <!-- Monto + fecha + orden -->
                                    <div class="col-12 col-md-6 d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width:44px;height:44px;">
                                            <i class="bi bi-cash-coin fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold fs-4 lh-sm mb-1">
                                                $<?= number_format((float)$p['amount'], 2) ?>
                                            </div>
                                            <div class="small text-secondary">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['payment_date']))) ?>
                                                <span class="mx-2">•</span>
                                                <i class="bi bi-hash me-1"></i>
                                                Orden #<?= (int)$p['order_id'] ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Método -->
                                    <div class="col-6 col-md-3">
                                        <span class="badge text-bg-<?= $methodBadge ?>">
                                            <i class="bi bi-wallet2 me-1"></i><?= ucfirst(str_replace('_', ' ', $method)) ?>
                                        </span>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="col-6 col-md-3 text-end d-grid d-md-flex gap-2 justify-content-md-end">
                                        <?php if (!empty($p['receipt_path'])): ?>
                                            <a href="/<?= htmlspecialchars($p['receipt_path']) ?>" target="_blank"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-paperclip me-1"></i>Comprobante
                                            </a>
                                        <?php else: ?>
                                            <span class="badge text-bg-light align-self-center">
                                                <i class="bi bi-file-earmark me-1"></i>Sin comprobante
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- FILA INFERIOR: Nota (full width) -->
                                <div class="row g-2 mt-3">
                                    <div class="col-12">
                                        <div class="small text-secondary mb-1">
                                            <i class="bi bi-sticky me-1"></i>Nota
                                        </div>
                                        <?php if (!empty($p['note'])): ?>
                                            <div class="border rounded p-2">
                                                <?= nl2br(htmlspecialchars($p['note'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-secondary small fst-italic">Sin nota</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </li>

                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-secondary">Sin pagos registrados.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>