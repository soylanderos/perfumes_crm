<!-- BLOQUE VISTA (texto) -->
<div id="client_view_block">
    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-primary" data-customer-id="<?= (int)($customer['id'] ?? 0) ?>" id="btn_edit_client">Editar</button>
    </div>
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="small text-secondary">Nombre completo</div>
            <div class="fw-semibold"><?= htmlspecialchars($customer['name'] ?? '—') ?></div>
        </div>
        <div class="col-12 col-md-3">
            <div class="small text-secondary">Día de pago preferente</div>
            <div class="fw-semibold"><?= !empty($customer['preferred_day']) ? (int)$customer['preferred_day'] : '—' ?></div>
        </div>
        <div class="col-12 col-md-3">
            <div class="small text-secondary">Teléfono</div>
            <div class="fw-semibold"><?= htmlspecialchars($customer['phone'] ?? '—') ?></div>
        </div>

        <div class="col-12 col-md-6">
            <div class="small text-secondary">Email</div>
            <div class="fw-semibold"><?= htmlspecialchars($customer['email'] ?? '—') ?></div>
        </div>
        <div class="col-12">
            <div class="small text-secondary">Dirección</div>
            <div class="fw-semibold"><?= nl2br(htmlspecialchars($customer['address'] ?? '—')) ?></div>
        </div>
        <div class="col-12">
            <div class="small text-secondary">Notas</div>
            <div class="fw-semibold"><?= nl2br(htmlspecialchars($customer['notes'] ?? '—')) ?></div>
        </div>
    </div>
</div>

<!-- BLOQUE EDICIÓN (inputs) -->
<form id="form_customer_edit" class="needs-validation d-none" novalidate>
    <input type="hidden" name="user_request" value="update_client">
    <input type="hidden" name="customer_id" value="<?= (int)($customer['id'] ?? 0) ?>">

    <div class="row g-3">
        <div class="col-12 col-md-8">
            <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required
                value="<?= htmlspecialchars($customer['name'] ?? '') ?>">
            <div class="invalid-feedback">Ingresa el nombre.</div>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label">Día de pago preferente</label>
            <select name="preferred_day" class="form-select">
                <option value="">—</option>
                <?php $pd = (int)($customer['preferred_day'] ?? 0);
                for ($i = 1; $i <= 31; $i++): ?>
                    <option value="<?= $i ?>" <?= $i === $pd ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="tel" name="phone" class="form-control" inputmode="tel"
                value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
            <div class="invalid-feedback">Correo inválido.</div>
        </div>

        <div class="col-12">
            <label class="form-label">Dirección</label>
            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Notas</label>
            <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($customer['notes'] ?? '') ?></textarea>
        </div>
    </div>
</form>