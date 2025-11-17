<?php
// Espera: $customer proveniente del controller
$customer_id    = (int)$customer['id'];
$customer_name  = $customer['name'] ?? '';
$current_balance = isset($customer['balance']) ? (float)$customer['balance'] : 0.0;
?>
<div class="modal fade" id="client_new_payment_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4 client-new-payment-modal-content">
            <form id="client_new_payment_form" autocomplete="off">
                <input type="hidden" id="payment_customer_id" value="<?= $customer_id ?>">

                <div class="modal-header border-0 pb-0 px-3 px-md-4 pt-3 pt-md-4">
                    <div class="d-flex align-items-center gap-2 justify-content-between w-100">
                        <h5 class="mb-0 fw-semibold">
                            Registrar pago
                        </h5>
                        <button type="button"
                            class="btn btn-light btn-sm rounded-pill"
                            data-bs-dismiss="modal">
                            Cerrar
                        </button>
                    </div>

                </div>

                <div class="modal-body px-3 px-md-4 pb-3 pb-md-4 pt-2">
                    <!-- Resumen de saldo -->
                    <div class="client-new-payment-summary mb-3">
                        <div class="small text-muted mb-1">Cliente</div>
                        <div class="fw-semibold mb-1"><?= htmlspecialchars($customer_name) ?></div>
                        <div class="small text-muted mb-1">Saldo actual</div>
                        <div class="h5 mb-0 <?= $current_balance > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= $current_balance > 0
                                ? '$' . number_format($current_balance, 2)
                                : 'En regla' ?>
                        </div>
                    </div>

                    <!-- Datos del pago -->
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">Fecha del pago</label>
                            <input type="date"
                                class="form-control form-control-sm"
                                id="payment_date"
                                value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Monto</label>
                            <input type="number"
                                class="form-control form-control-sm text-end"
                                id="payment_amount"
                                min="0"
                                step="0.01"
                                placeholder="0.00">
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Método</label>
                            <select class="form-select form-select-sm" id="payment_method">
                                <option value="cash">Efectivo</option>
                                <option value="transfer">Transferencia</option>
                                <option value="card">Tarjeta</option>
                                <option value="other">Otro</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Notas</label>
                            <input type="text"
                                class="form-control form-control-sm"
                                id="payment_notes"
                                placeholder="Ej. abono semanal, pago completo, etc.">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label small mb-1">Comprobante de pago (opcional)</label>
                        <input type="file"
                            class="form-control form-control-sm"
                            id="payment_receipt"
                            accept="image/*,application/pdf">
                        <p class="small text-muted mb-0 mt-1">
                            Puedes subir una foto del voucher o un PDF.
                        </p>
                    </div>

                    <p class="small text-muted mt-3 mb-0">
                        Este pago se restará del saldo del cliente y quedará registrado en el historial de movimientos.
                    </p>
                </div>

                <div class="modal-footer border-0 px-3 px-md-4 pb-3 pb-md-4 pt-0">
                    <button type="button"
                        class="btn btn-light rounded-pill"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="btn btn-success rounded-pill">
                        Guardar pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>