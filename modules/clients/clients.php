<!-- clients.php -->
<div class="clients-page">

    <!-- Encabezado / toolbar -->
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-semibold">Clientes</h1>
            <p class="text-muted mb-0">Administra tus clientes, saldos y actividad semanal.</p>
        </div>

        <div class=" d-flex flex-wrap align-items-center gap-2">
            <!-- Filtro por estado -->
            <div class="dropdown">
                <button class="btn btn-light btn-sm rounded-pill shadow-sm px-3 dropdown-toggle"
                    type="button" id="filterStatusDropdown" data-bs-toggle="dropdown">
                    Estado: Todos
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" data-status="all">Todos</a></li>
                    <li><a class="dropdown-item" href="#" data-status="active">Activos</a></li>
                    <li><a class="dropdown-item" href="#" data-status="inactive">Inactivos</a></li>
                    <li><a class="dropdown-item" href="#" data-status="debtor">Con deuda</a></li>
                </ul>
            </div>

            <!-- Filtro por periodo (para futuros reportes / no pagaron semana pasada) -->
            <div class="btn-group btn-group-sm rounded-pill shadow-sm overflow-hidden" role="group">
                <button type="button" class="btn btn-light period-filter active" data-period="all">Todo</button>
                <button type="button" class="btn btn-light period-filter" data-period="this_week">Esta semana</button>
                <button type="button" class="btn btn-light period-filter" data-period="last_week">Semana pasada</button>
            </div>

            <!-- Buscador -->
            <div class="clients-search-wrapper">
                <span class="clients-search-icon">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control form-control-sm clients-search-input"
                    placeholder="Buscar por nombre o teléfono" id="clients_search_input">
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Columna principal: grid de clientes -->
        <div class="col-12 col-xl-9">
            <div class="clients-grid container-responsive-350" id="clients_grid">
                <?php
                if (!empty($customers)):
                    foreach ($customers as $customer):
                        // Ejemplo de estructura: ajusta a tu query
                        $customer_id       = $customer['id'];
                        $customer_name     = $customer['name'];
                        $customer_phone    = $customer['phone'] ?? '';
                        $customer_email    = $customer['email'] ?? '';
                        $customer_status   = $customer['status'] ?? 'active';
                        $customer_balance  = $customer['balance'] ?? 0; // saldo actual
                        $last_payment_date = $customer['last_payment_date'] ?? null;
                        $weekly_score      = $customer['weekly_score'] ?? 0; // % cumplimiento abonos semana

                        include '../components/card/client_card.php';
                    endforeach;
                ?>
                <?php else: ?>
                    <div class="clients-empty-state text-center p-5">
                        <div class="clients-empty-icon mb-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <h2 class="h5 mb-1">Aún no tienes clientes registrados</h2>
                        <p class="text-muted mb-3">
                            Empieza agregando tus primeros clientes para llevar control de sus compras y saldos.
                        </p>
                        <button class="btn btn-primary rounded-pill px-4" id="btn_empty_new_client">
                            <i class="bi bi-person-plus me-1"></i>
                            Registrar primer cliente
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna lateral: resumen / KPIs -->
        <div class="col-12 col-xl-3 d-none d-xl-block">
            <aside class="clients-sidebar card border-0 shadow-sm rounded-4 p-3 p-xl-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h6 mb-0 fw-semibold">Resumen de clientes</h3>
                    <span class="badge rounded-pill clients-badge-light">Hoy</span>
                </div>

                <div class="clients-kpi-card mb-3">
                    <div class="small text-muted mb-1">Total clientes</div>
                    <div class="d-flex justify-content-between align-items-end">
                        <span class="fw-semibold h4 mb-0" id="kpi_total_clients">0</span>
                    </div>
                </div>

                <div class="clients-kpi-grid mb-3">
                    <div class="clients-kpi-item">
                        <span class="small text-muted d-block">Con deuda</span>
                        <span class="fw-semibold" id="kpi_debtor_clients">0</span>
                    </div>
                    <div class="clients-kpi-item">
                        <span class="small text-muted d-block">Sin deuda</span>
                        <span class="fw-semibold" id="kpi_clear_clients">0</span>
                    </div>
                </div>

                <div class="clients-kpi-card mb-3">
                    <div class="small text-muted mb-1">Saldo total por cobrar</div>
                    <div class="fw-semibold h5 mb-0" id="kpi_total_balance">$0.00</div>
                </div>

                <div class="clients-kpi-card mb-3">
                    <div class="small text-muted mb-1">Clientes sin abono semana pasada</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold" id="kpi_missed_last_week">0</span>
                        <button class="btn btn-outline-secondary btn-xs rounded-pill px-3" id="btn_view_missed">
                            Ver lista
                        </button>
                    </div>
                </div>

                <div class="clients-tip mt-3">
                    <p class="small mb-1 fw-semibold">Tip rápido</p>
                    <p class="small text-muted mb-0">
                        Usa los filtros de periodo para detectar quién dejó de abonar y priorizar tus cobros.
                    </p>
                </div>
            </aside>
        </div>
    </div>
    <button class="btn btn-primary shadow-lg clients-fab-new-client"
        id="clients_fab_new_client">
        <i class="bi bi-plus-lg"></i>
    </button>

</div>