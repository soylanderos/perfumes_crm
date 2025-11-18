<?php
$payment_id   = (int)$payment['id'];
$payment_date = $payment['payment_date'] ? date('d/m/Y', strtotime($payment['payment_date'])) : '';
$amount       = number_format((float)$payment['amount'], 2);
$method       = $payment['method'] ?? '';
$notes        = $payment['notes'] ?? '';
$receipt      = $payment['receipt_path'] ?? null;
$ext          = $receipt ? strtolower(pathinfo($receipt, PATHINFO_EXTENSION)) : null;
?>
<div class="modal fade" id="client_payment_detail_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4 client-payment-detail-modal-content">
            <div class="modal-header border-0 px-3 px-md-4 pt-3 pt-md-4 d-flex align-items-center gap-2 justify-content-between w-100">
                <h5 class="mb-0 fw-semibold">
                    Detalle del pago #<?= $payment_id ?>
                </h5>
                <button type="button"
                    class="btn btn-light btn-sm rounded-pill"
                    data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="modal-body px-3 px-md-4 pb-3 pb-md-4 pt-2">
                <div class="mb-3">
                    <div class="small text-muted mb-1">Fecha</div>
                    <div class="fw-semibold"><?= $payment_date ?></div>
                </div>

                <div class="mb-3">
                    <div class="small text-muted mb-1">Monto</div>
                    <div class="fw-semibold text-success h5 mb-0">
                        $<?= $amount ?>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="small text-muted mb-1">Método</div>
                        <div class="fw-semibold"><?= htmlspecialchars($method) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Notas</div>
                        <div class="fw-normal small">
                            <?= $notes ? htmlspecialchars($notes) : 'Sin notas' ?>
                        </div>
                    </div>
                </div>

                <?php if ($receipt): ?>
                    <div class="mt-3">
                        <div class="small text-muted mb-1">Comprobante de pago</div>
                        

                        <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)): ?>
                            <div class="client-payment-receipt-preview mt-2">
                                <img src="<?= htmlspecialchars($receipt) ?>"
                                    alt="Comprobante de pago"
                                    class="img-fluid rounded-3">
                            </div>
                        <?php elseif ($ext === 'pdf'): ?>
                            <div class="client-payment-receipt-preview mt-2">
                                <embed src="<?= htmlspecialchars($receipt) ?>"
                                    type="application/pdf"
                                    style="width: 100%; height: 320px; border-radius: 12px; border: 1px solid #e5e7eb;">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="small text-muted mb-0">
                        Este pago no tiene comprobante adjunto.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>