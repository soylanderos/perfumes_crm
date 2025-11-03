<!-- Modal: Nuevo Cliente -->
<div class="modal fade" id="client_modal" tabindex="-1" aria-labelledby="client_modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- modal-lg para desktop -->
        <div class="modal-content">
            <form id="form_customer" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="client_modalLabel">Registrar cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <input type="hidden" name="user_request" value="<?= $request ?? '' ?>">
                <?php if (isset($customer_id)): ?>
                    <input type="hidden" name="customer_id" value="<?= htmlspecialchars($customer_id) ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-12 col-md-8">
                            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Ej. Juan Pérez" required value="<?= $name ?? '' ?>">
                            <div class="invalid-feedback">Ingresa el nombre del cliente.</div>
                        </div>

                        <!-- Día preferente -->
                        <div class="col-12 col-md-4">
                            <label class="form-label">Día de pago preferente</label>
                            <select name="preferred_day" class="form-select" required>
                                <option value="">—</option>
                                <!-- 1..31 -->
                                <?php for ($i = 1; $i <= 31; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($i == ($preferred_day ?? 0)) ? 'selected' : '' ?>><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Teléfono</label>
                            <div class="input-group">
                                <input type="tel" name="phone" class="form-control" placeholder="55 1234 5678" inputmode="tel" required value="<?= $phone ?? '' ?>">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="cliente@correo.com" value="<?= $email ?? '' ?>">
                            <div class="invalid-feedback">Ingresa un correo válido.</div>
                        </div>

                        <!-- Dirección -->
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Calle, número, colonia, ciudad"><?= $address ?? '' ?></textarea>
                        </div>

                        <!-- Notas -->
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Preferencias, recordatorios, etc."><?= $notes ?? '' ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="me-auto small text-secondary">Los campos marcados con * son obligatorios.</div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        Guardar cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
