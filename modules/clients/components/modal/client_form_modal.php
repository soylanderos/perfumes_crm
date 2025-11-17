<?php
// Variables esperadas: $mode = 'create' | 'edit', y opcional $customer
$mode = $mode ?? 'create';

$customer_id   = $mode === 'edit' ? (int)$customer['id'] : null;
$name          = $mode === 'edit' ? ($customer['name'] ?? '') : '';
$phone         = $mode === 'edit' ? ($customer['phone'] ?? '') : '';
$email         = $mode === 'edit' ? ($customer['email'] ?? '') : '';
$status        = $mode === 'edit' ? ($customer['status'] ?? 'active') : 'active';

$title = $mode === 'edit' ? 'Editar cliente' : 'Nuevo cliente';
$primary_btn = $mode === 'edit' ? 'Guardar cambios' : 'Crear cliente';
?>
<div class="modal fade" id="client_form_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4 client-form-modal-content">
            <form id="client_form" autocomplete="off">
                <?php if ($mode === 'edit'): ?>
                    <input type="hidden" id="client_id" value="<?= $customer_id ?>">
                <?php else: ?>
                    <input type="hidden" id="client_id" value="">
                <?php endif; ?>

                <div class="modal-header border-0 px-3 px-md-4 pt-3 pt-md-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold"><?= htmlspecialchars($title) ?></h5>

                    <button type="button"
                        class="btn btn-light btn-sm rounded-pill"
                        data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>

                <div class="modal-body px-3 px-md-4 pb-3 pb-md-4 pt-2">
                    <div class="mb-3">
                        <label class="form-label small mb-1">Nombre del cliente</label>
                        <input type="text"
                            class="form-control form-control-sm"
                            id="client_name"
                            value="<?= htmlspecialchars($name) ?>"
                            placeholder="Ej. Juan Pérez"
                            required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">Teléfono</label>
                            <input type="text"
                                class="form-control form-control-sm"
                                id="client_phone"
                                value="<?= htmlspecialchars($phone) ?>"
                                placeholder="Opcional">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Correo electrónico</label>
                            <input type="email"
                                class="form-control form-control-sm"
                                id="client_email"
                                value="<?= htmlspecialchars($email) ?>"
                                placeholder="Opcional">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small mb-1">Estado</label>
                        <select class="form-select form-select-sm" id="client_status">
                            <option value="active" <?= $status === 'active'   ? 'selected' : '' ?>>Activo</option>
                            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

                    <p class="small text-muted mb-0">
                        Podrás registrar compras y pagos para este cliente después de guardarlo.
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
                        <?= htmlspecialchars($primary_btn) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>