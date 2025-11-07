<div class="container py-4">

    <!-- Toolbar (solo búsqueda) -->
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <h1 class="h4 mb-0 me-auto">Catálogo de Productos</h1>
        <div class="input-group" style="max-width:360px;">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input id="catalog_search" type="search" class="form-control" placeholder="Buscar por nombre o SKU…">
        </div>
    </div>

    <!-- Grid -->
    <div id="catalog_grid" class="row g-4">
        <?php foreach ($products as $pr):
            $id    = (int)$pr['id'];
            $sku   = $pr['sku'] ?? '';
            $name  = $pr['name'] ?? 'Perfume';
            $desc  = $pr['description'] ?? '';
            $price = (float)$pr['price'];
            $stock = (int)$pr['stock'];

            // Heurística de stock para badge
            $stockLabel = $stock <= 0 ? ['Agotado', 'danger'] : ($stock <= 5 ? ['Bajo stock', 'warning'] : ['Disponible', 'success']);

            // Convención de imágenes: /product_images/{id}.jpg (con fallback a placeholder)
            $img = "/product_images/{$id}.jpg";
            $ph  = "https://picsum.photos/800/600?grayscale&blur=1"; // placeholder
        ?>
            <div class="col-12 col-md-6 col-lg-4"
                data-name="<?= htmlspecialchars(mb_strtolower($name . ' ' . $sku)) ?>">
                <div class="card h-100 border-0 shadow-sm position-relative overflow-hidden">

                    <!-- Imagen (usa onerror para fallback) -->
                    <div class="ratio ratio-4x3">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>"
                            class="w-100 h-100" style="object-fit:cover;"
                            onerror="this.onerror=null;this.src='<?= htmlspecialchars($ph) ?>';">
                    </div>

                    <!-- Precio en círculo -->
                    <div class="position-absolute top-0 start-0 m-3 rounded-circle bg-dark text-white d-flex align-items-center justify-content-center"
                        style="width:56px;height:56px;">
                        <span class="fw-semibold">$<?= number_format($price, 2) ?></span>
                    </div>

                    <!-- Cuerpo -->
                    <div class="card-body text-center">
                        <!-- SKU + Stock -->
                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <?php if ($sku): ?>
                                <span class="badge text-bg-light">SKU <?= htmlspecialchars($sku) ?></span>
                            <?php endif; ?>
                            <span class="badge text-bg-<?= $stockLabel[1] ?>"><?= $stockLabel[0] ?></span>
                        </div>

                        <!-- Nombre -->
                        <h3 class="fs-2 fw-semibold mb-1" style="letter-spacing:.06em;">
                            <?= htmlspecialchars($name) ?>
                        </h3>

                        <!-- Separador fino -->
                        <div class="d-flex justify-content-center my-2">
                            <div class="border-top" style="width:48px;"></div>
                        </div>

                        <!-- Descripción corta -->
                        <p class="text-secondary small mb-3" style="min-height:3.5em;">
                            <?= htmlspecialchars(mb_strimwidth($desc, 0, 160, '…')) ?>
                        </p>

                        <!-- CTA -->
                        <div class="d-grid">
                            <div class="text-center fs-3">
                                <?php if ($stock > 0): ?>
                                <span class="badge text-bg-light">Stock: <?= $stock ?> unidades</span>
                                <?php else: ?>
                                    <span class="badge text-bg-danger">Sin stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
