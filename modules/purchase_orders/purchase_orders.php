<!-- purchase_orders.php -->
<div class="orders-page ">

    <!-- Toolbar / header -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-semibold">Pedidos de resurtido</h1>
            <p class="text-muted mb-0">
                Crea listas de productos para resurtir y da seguimiento a qué se vendió y a qué clientes se fue.
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Filtro por estado -->
            <div class="dropdown">
                <button class="btn btn-light btn-sm rounded-pill shadow-sm px-3 dropdown-toggle"
                    type="button" id="ordersFilterStatusDropdown" data-bs-toggle="dropdown">
                    Estado: Todos
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item orders-status-filter" href="#" data-status="all">Todos</a></li>
                    <li><a class="dropdown-item orders-status-filter" href="#" data-status="open">Abiertos</a></li>
                    <li><a class="dropdown-item orders-status-filter" href="#" data-status="partial">Parciales</a></li>
                    <li><a class="dropdown-item orders-status-filter" href="#" data-status="closed">Cerrados</a></li>
                </ul>
            </div>

            <!-- Filtro de periodo -->
            <div class="btn-group btn-group-sm rounded-pill shadow-sm overflow-hidden orders-period-group" role="group">
                <button type="button" class="btn btn-light orders-period-filter active" data-period="this_month">Este mes</button>
                <button type="button" class="btn btn-light orders-period-filter" data-period="last_3_months">Últimos 3 meses</button>
                <button type="button" class="btn btn-light orders-period-filter" data-period="all">Todo</button>
            </div>

            <!-- Buscador -->
            <div class="orders-search-wrapper">
                <span class="orders-search-icon">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                    class="form-control form-control-sm orders-search-input"
                    placeholder="Buscar pedido por nombre o nota"
                    id="orders_search_input">
            </div>
        </div>
    </div>

    <!-- Layout principal -->
    <div class="row g-4 orders-layout">
        <!-- Columna izquierda: listas de pedidos -->
        <div class="col-12 col-lg-5 col-xl-4">
            <div class="orders-lists-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h6 mb-0 fw-semibold">Listas de pedidos</h2>
                    <span class="small text-muted" id="orders_count_badge">
                        <?= !empty($purchase_orders) ? count($purchase_orders) . ' listas' : '0 listas' ?>
                    </span>
                </div>

                <?php if (!empty($purchase_orders)): ?>
                    <div class="orders-lists container-responsive-350" id="orders_lists">
                        <?php
                        foreach ($purchase_orders as $order):
                            include '../components/card/order_card.php';
                        endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="orders-empty-state text-center p-4">
                        <div class="orders-empty-icon mb-3">
                            <i class="bi bi-list-ul"></i>
                        </div>
                        <h2 class="h6 mb-1">Aún no tienes listas de pedidos</h2>
                        <p class="small text-muted mb-3">
                            Crea tu primera lista para planear qué perfumes vas a resurtir en tu próxima compra.
                        </p>
                        <button class="btn btn-primary rounded-pill px-4" id="btn_orders_new_list_empty">
                            <i class="bi bi-plus-lg me-1"></i> Crear primera lista
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna derecha: detalle de la lista seleccionada -->
        <div class="col-12 col-lg-7 col-xl-8 d-none d-sm-none d-md-none d-lg-block">
            <div class="orders-detail-card" id="orders_detail_card">
                <!-- Estado inicial: nada seleccionado -->
                <div class="orders-detail-empty text-center p-5" id="orders_detail_empty">
                    <div class="orders-empty-icon mb-3">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <h2 class="h6 mb-1">Selecciona una lista de pedidos</h2>
                    <p class="small text-muted mb-3">
                        Aquí verás todos los productos de la lista, sus cantidades y cuándo se asignan a clientes.
                    </p>
                    <p class="small text-muted mb-0">
                        También podrás marcar qué productos ya se vendieron para mantener la trazabilidad.
                    </p>
                </div>

                <!-- Contenido dinámico del detalle (se llena por AJAX) -->
                <div class="orders-detail-content d-none" id="orders_detail_content">
                    <!-- Header del pedido -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div>
                            <h2 class="h5 mb-1 fw-semibold" id="orders_detail_title">Nombre del pedido</h2>
                            <p class="small text-muted mb-0" id="orders_detail_meta">
                                Creado el — · 0 productos
                            </p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge rounded-pill orders-status-badge" id="orders_detail_status_badge">
                                Estado
                            </span>
                            <button class="btn btn-outline-secondary btn-sm rounded-pill" id="btn_orders_mark_closed">
                                <i class="bi bi-check2-circle me-1"></i> Marcar como cerrado
                            </button>
                        </div>
                    </div>

                    <!-- Lista de productos -->
                    <div class="orders-items-list" id="orders_items_list">
                        <!-- Se llena por AJAX: cada item será una row estilo lista de compras -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante para nueva lista (mobile / general) -->
    <button class="btn btn-primary shadow-lg orders-fab" id="btn_orders_new_list">
        <i class="bi bi-plus-lg"></i>
    </button>
</div>

<!-- Modal detalle de pedido (para mobile/tablet) -->
<div class="modal fade orders-detail-modal" id="orders_detail_modal"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0 px-3 pt-3">
                <button type="button"
                    class="btn btn-light btn-sm rounded-pill"
                    data-bs-dismiss="modal">
                    <i class="bi bi-chevron-left me-1"></i> Volver
                </button>
            </div>

            <div class="modal-body pt-2 pb-3 px-3">
                <div class="mb-2">
                    <h2 class="h5 mb-1 fw-semibold" id="orders_detail_modal_title">Título pedido</h2>
                    <p class="small text-muted mb-1" id="orders_detail_modal_meta">
                        Meta del pedido
                    </p>
                    <span class="badge rounded-pill orders-status-badge"
                        id="orders_detail_modal_status_badge">
                        Estado
                    </span>
                </div>

                <!-- en purchase_orders.php, en el header del detalle -->
                <button class="btn btn-outline-primary btn-sm rounded-pill" id="btn_orders_add_items_detail">
                    <i class="bi bi-plus-lg me-1"></i> Agregar productos
                </button>

                <div class="orders-items-list mt-3" id="orders_detail_modal_items_list">
                    <!-- items_html se inyecta aquí en mobile -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nueva lista de pedidos -->
<div class="modal fade orders-new-list-modal" id="orders_new_list_modal"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4">

            <form id="new_purchase_order_form" autocomplete="off">
                <div class="modal-header border-0 pb-0 px-3 px-md-4 pt-3 pt-md-4 d-flex align-items-center gap-2 justify-content-between w-100">
                    <h5 class="mb-0 fw-semibold">
                        Nueva lista de pedidos
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
                    <!-- Info básica de la lista -->
                    <div class="mb-3">
                        <div class="row g-3">
                            <div class="col-12 col-md-7">
                                <label class="form-label small mb-1">Nombre de la lista</label>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="new_order_title"
                                    placeholder="Ej. Resurtido noviembre importados"
                                    required>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small mb-1">Fecha del pedido</label>
                                <input type="date"
                                    class="form-control form-control-sm"
                                    id="new_order_date">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label small mb-1">Notas</label>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="new_order_notes"
                                    placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- Productos de la lista -->
                    <div class="orders-new-list-items-card mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold small text-muted text-uppercase">
                                Productos del pedido
                            </h6>
                            <button class="btn btn-outline-primary btn-sm rounded-pill"
                                type="button"
                                id="btn_add_order_item">
                                <i class="bi bi-plus-lg me-1"></i> Agregar producto
                            </button>
                        </div>

                        <div id="new_order_items_container" class="container-responsive-520">
                            <!-- Aquí se inyectan las filas con JS (renderNewOrderItemRow) -->
                        </div>

                        <p class="small text-muted mt-2 mb-0">
                            Tip: registra solo el nombre y la cantidad. El costo aprox. te ayuda después para KPIs de utilidad,
                            pero es opcional.
                        </p>
                    </div>
                </div>

                <div class="modal-footer border-0 px-3 px-md-4 pb-3 pb-md-4 pt-0">
                    <button type="button"
                        class="btn btn-light rounded-pill"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="btn btn-primary rounded-pill">
                        Guardar lista
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Modal: Agregar producto a esta lista -->
<div class="modal fade orders-add-item-modal" id="orders_add_item_modal"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm modal-fullscreen-sm-down">
        <div class="modal-content border-0 rounded-4">

            <form id="add_order_item_form" autocomplete="off">
                <input type="hidden" id="add_item_order_id">

                <div class="modal-header border-0 pb-0 px-3 pt-3">
                    <h5 class="mb-0 fw-semibold">
                        Agregar producto
                    </h5>
                    <button type="button"
                        class="btn btn-light btn-sm rounded-pill"
                        data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="modal-body px-3 pb-3 pt-2">
                    <div class="mb-3">
                        <label class="form-label small mb-1">Nombre del producto</label>
                        <input type="text"
                            class="form-control form-control-sm"
                            id="add_item_name"
                            placeholder="Ej. Dior Sauvage EDT 100ml"
                            required>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Cantidad</label>
                            <input type="number"
                                class="form-control form-control-sm"
                                id="add_item_qty"
                                min="1"
                                value="1"
                                required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Costo aprox (opcional)</label>
                            <input type="number"
                                class="form-control form-control-sm"
                                id="add_item_cost"
                                min="0"
                                step="0.01"
                                placeholder="0.00">
                        </div>
                    </div>

                    <p class="small text-muted mt-2 mb-0">
                        Este producto se agregará a la lista de pedidos que tienes abierta.
                    </p>
                </div>

                <div class="modal-footer border-0 px-3 pb-3 pt-0">
                    <button type="button"
                        class="btn btn-light rounded-pill"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="btn btn-primary rounded-pill">
                        Guardar producto
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>