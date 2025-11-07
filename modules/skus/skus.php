<!-- PRODUCTS/SKUs – Admin View -->
<div class="container py-3" id="products_admin_root">

    <!-- Toolbar -->
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <h1 class="h5 mb-0 me-auto">Productos / SKUs</h1>

        <div class="input-group" style="max-width:320px;">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input id="prod_search" type="search" class="form-control" placeholder="Buscar por nombre o SKU…">
        </div>

        <select id="prod_filter_active" class="form-select" style="max-width:140px;">
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
        </select>

        <select id="prod_filter_stock" class="form-select" style="max-width:160px;">
            <option value="">Stock: Todos</option>
            <option value="low">Bajo (≤5)</option>
            <option value="zero">Agotado (=0)</option>
        </select>

        <select id="prod_order_by" class="form-select" style="max-width:180px;">
            <option value="created_at DESC">Orden: Recientes</option>
            <option value="name ASC">Nombre (A–Z)</option>
            <option value="price DESC">Precio (alto→bajo)</option>
            <option value="stock ASC">Stock (bajo→alto)</option>
        </select>

        <div class="btn-group">
            <button class="btn btn-outline-secondary" id="btn_export"><i class="bi bi-download me-1"></i>Exportar</button>
            <button class="btn btn-outline-secondary" id="btn_import"><i class="bi bi-upload me-1"></i>Importar CSV</button>
            <button class="btn btn-primary" id="btn_new_product"><i class="bi bi-plus-lg me-1"></i>Nuevo</button>
        </div>
    </div>

    <!-- Bulk actions -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <div class="small text-secondary" id="bulk_count">0 seleccionados</div>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" id="bulk_activate"><i class="bi bi-play"></i> Activar</button>
            <button class="btn btn-outline-secondary" id="bulk_deactivate"><i class="bi bi-pause"></i> Desactivar</button>
            <button class="btn btn-outline-danger" id="bulk_delete"><i class="bi bi-trash"></i> Eliminar</button>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                    <tr>
                        <th style="width:36px;">
                            <input class="form-check-input" type="checkbox" id="chk_all">
                        </th>
                        <th style="width:64px;">Imagen</th>
                        <th>SKU</th>
                        <th>Nombre</th>
                        <th class="text-end" style="width:120px;">Precio</th>
                        <th class="text-end" style="width:110px;">Stock</th>
                        <th style="width:110px;">Estado</th>
                        <th style="width:140px;">Creado</th>
                        <th class="text-end" style="width:160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="prod_tbody">
                    <!-- Se rellena por JS. Ejemplo de fila: -->
                    <?php foreach ($skus as $pr):
                        $id = (int)$pr['id'];
                        $sku = $pr['sku'];
                        $name = $pr['name'];
                        $desc = $pr['description'] ?? '';
                        $price = (float)$pr['price'];
                        $stock = (int)$pr['stock'];
                        $active = (int)$pr['active'];
                        $img = "/product_images/{$id}.jpg";
                        $badge = $active ? 'success' : 'secondary';
                        $status = $active ? 'Activo' : 'Inactivo';
                        $stockBadge = $stock <= 0 ? 'danger' : ($stock <= 5 ? 'warning' : 'secondary');
                    ?>
                        <tr data-id="<?= $id ?>">
                            <td><input class="form-check-input row-chk" type="checkbox"></td>
                            <td>
                                <div class="ratio ratio-1x1" style="width:48px;">
                                    <img src="<?= htmlspecialchars($img) ?>" class="rounded border"
                                        onerror="this.src='https://picsum.photos/96/96?blur=1'" style="object-fit:cover;">
                                </div>
                            </td>
                            <td class="text-monospace"><?= htmlspecialchars($sku) ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($name) ?></div>
                                <div class="small text-secondary text-truncate" style="max-width:360px;">
                                    <?= htmlspecialchars(mb_strimwidth($desc, 0, 120, '…')) ?>
                                </div>
                            </td>
                            <td class="text-end fw-semibold">$<?= number_format($price, 2) ?></td>
                            <td class="text-end">
                                <span class="badge text-bg-<?= $stockBadge ?>"><?= $stock ?></span>
                            </td>
                            <td><span class="badge text-bg-<?= $badge ?>"><?= $status ?></span></td>
                            <td class="small text-secondary"><?= !empty($pr['created_at']) ? date('Y-m-d', strtotime($pr['created_at'])) : '—' ?></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-edit"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-outline-secondary btn-toggle" data-active="<?= $active ? 0 : 1 ?>">
                                        <?= $active ? '<i class="bi bi-pause"></i>' : '<i class="bi bi-play"></i>' ?>
                                    </button>
                                    <button class="btn btn-outline-danger btn-delete"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer: resultados y paginación -->
        <div class="d-flex flex-wrap align-items-center justify-content-between p-2">
            <div class="small text-secondary" id="result_info">
                Mostrando <span id="res_from">1</span>–<span id="res_to"><?= count($skus) ?></span> de <span id="res_total">—</span>
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="pager">
                    <li class="page-item disabled"><a class="page-link" href="#" data-page="prev">Anterior</a></li>
                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                    <li class="page-item"><a class="page-link" href="#" data-page="next">Siguiente</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>